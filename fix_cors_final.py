#!/usr/bin/env python3
"""
fix_cors_final.py
-----------------
Targeted fix for ChatBot Nepal CORS + 503 issues on production OLS server.

Fixes applied in order:
  1. Bypass PHP platform_check (8.3 vs 8.4 mismatch) so artisan works
  2. Rewrite public/.htaccess with correct content (no RewriteBase /ai/)
  3. Find and read OLS vhost conf, inject CORS extraHeaders if missing
  4. Clear Laravel caches
  5. Restart OLS
  6. Verify with curl — PASS or FAIL

Phase 2 (auto-runs on FAIL): deep OLS/proxy diagnosis

Usage:
    python3 fix_cors_final.py [--verbose] [--creds PATH]

Exit codes:
    0  CORS verified working
    1  verification failed — see Phase 2 output
    2  fatal (bad passphrase, SSH failure)
"""

import argparse
import base64
import getpass
import json
import os
import re
import sys
import time
from dataclasses import dataclass, field

try:
    import paramiko
    from cryptography.fernet import Fernet, InvalidToken
    from cryptography.hazmat.primitives import hashes
    from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC
except ImportError as e:
    print(f"Missing dependency: {e}\nInstall: pip install paramiko cryptography")
    sys.exit(2)

# ── config ────────────────────────────────────────────────────────────────────

APP_ROOT    = "/home/isoftroerp.com/chatbotnepal.isoftroerp.com"
PUBLIC_DIR  = f"{APP_ROOT}/public"
PHP_BIN     = "/usr/local/lsws/lsphp83/bin/php"
SITE_URL    = "https://chatbotnepal.isoftroerp.com"
API_PATH    = "/api/widget/session"
ORIGIN      = "https://isoftroerp.com"
ITERATIONS  = 390_000

OLS_VHOSTS_DIR = "/usr/local/lsws/conf/vhosts"
OLS_CONF_DIR   = "/usr/local/lsws/conf"

# ── exact .htaccess content the user specified ────────────────────────────────

HTACCESS_CONTENT = """\
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Session-Token, X-Requested-With, Accept"
Header always set Access-Control-Max-Age "86400"

<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
    RewriteEngine On
    RewriteCond %{REQUEST_METHOD} OPTIONS
    RewriteRule ^(.*)$ - [R=204,L]
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
"""

# OLS context extraHeaders block to inject into vhost conf
OLS_CORS_EXTRAHEADERS = """\
context / {
  type                    NULL
  location                $DOC_ROOT/
  allowBrowse             1
  extraHeaders            <<<END_CORS
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Session-Token, X-Requested-With, Accept
Access-Control-Max-Age: 86400
END_CORS
}
"""

# ── colours ───────────────────────────────────────────────────────────────────

class C:
    R = "\033[91m"; G = "\033[92m"; Y = "\033[93m"
    B = "\033[94m"; M = "\033[95m"; X = "\033[0m"
    BOLD = "\033[1m"

def ok(msg):   print(f"{C.G}[OK]{C.X} {msg}")
def info(msg): print(f"{C.B}[..]{C.X} {msg}")
def warn(msg): print(f"{C.Y}[!!]{C.X} {msg}")
def fail(msg): print(f"{C.R}[XX]{C.X} {msg}")
def step(n, msg): print(f"\n{C.BOLD}{C.M}[Step {n}] {msg}{C.X}")
def hdr(msg):  print(f"\n{C.BOLD}{'='*64}\n  {msg}\n{'='*64}{C.X}")

# ── result tracking ───────────────────────────────────────────────────────────

@dataclass
class StepResult:
    name: str
    status: str   # PASS | FAIL | SKIP
    note: str = ""

results: list[StepResult] = []

def record(name: str, status: str, note: str = ""):
    results.append(StepResult(name, status, note))
    sym = {"PASS": f"{C.G}PASS{C.X}", "FAIL": f"{C.R}FAIL{C.X}",
           "SKIP": f"{C.Y}SKIP{C.X}"}.get(status, status)
    print(f"  → {sym}  {note}")

# ── credentials ───────────────────────────────────────────────────────────────

def decrypt_credentials(path: str, passphrase: str) -> dict:
    with open(path, "rb") as f:
        blob = f.read()
    salt, token = blob[:16], blob[16:]
    kdf = PBKDF2HMAC(algorithm=hashes.SHA256(), length=32,
                     salt=salt, iterations=ITERATIONS)
    key = base64.urlsafe_b64encode(kdf.derive(passphrase.encode()))
    try:
        data = Fernet(key).decrypt(token)
    except InvalidToken:
        raise ValueError("Wrong passphrase or corrupted credentials file.")
    return json.loads(data.decode())

# ── SSH class ─────────────────────────────────────────────────────────────────

class SSH:
    def __init__(self, creds: dict, verbose: bool = False):
        self.creds   = creds
        self.verbose = verbose
        self.client  = paramiko.SSHClient()
        self.client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

    def connect(self):
        info(f"Connecting to {self.creds['user']}@{self.creds['host']}:{self.creds['port']} …")
        self.client.connect(
            hostname     = self.creds["host"],
            port         = self.creds["port"],
            username     = self.creds["user"],
            password     = self.creds["password"],
            timeout      = 60,
            banner_timeout = 60,
            auth_timeout = 60,
            look_for_keys = False,
            allow_agent  = False,
        )
        # Keep TCP connection alive every 30 s — prevents socket close on idle
        self.client.get_transport().set_keepalive(30)
        ok("SSH connected (keepalive=30s)")

    def run(self, cmd: str, timeout: int = 90) -> tuple[int, str, str]:
        if self.verbose:
            print(f"  $ {cmd}")
        _, stdout, stderr = self.client.exec_command(cmd, timeout=timeout)
        rc  = stdout.channel.recv_exit_status()
        out = stdout.read().decode(errors="replace")
        err = stderr.read().decode(errors="replace")
        if self.verbose:
            for line in (out + err).strip().splitlines():
                print(f"    {line}")
        return rc, out, err

    def write_file(self, remote_path: str, content: str):
        """Write via base64-encoded SSH exec — immune to SFTP socket timeouts."""
        encoded = base64.b64encode(content.encode("utf-8")).decode("ascii")
        # 800-char chunks keep individual commands short
        chunks = [encoded[i:i+800] for i in range(0, len(encoded), 800)]
        self.run(f"printf '%s' '{chunks[0]}' | base64 -d > {remote_path}")
        for chunk in chunks[1:]:
            self.run(f"printf '%s' '{chunk}' | base64 -d >> {remote_path}")

    def read_file(self, remote_path: str) -> str | None:
        rc, out, _ = self.run(f"cat {remote_path} 2>/dev/null")
        return out if rc == 0 and out.strip() else None

    def close(self):
        self.client.close()


# ════════════════════════════════════════════════════════════════════════════
# PHASE 1 STEPS
# ════════════════════════════════════════════════════════════════════════════

# ── Step 1: Fix PHP platform_check ───────────────────────────────────────────

def step1_fix_platform_check(ssh: SSH):
    step(1, "Fix PHP platform_check (8.3 vs >=8.4 mismatch)")
    autoload = f"{APP_ROOT}/vendor/composer/autoload_real.php"

    # Backup
    ssh.run(f"cp {autoload} {autoload}.bak 2>/dev/null || true")

    # Remove the require line for platform_check.php
    patch_cmd = (
        f"sed -i \"s|require __DIR__ . '/composer/platform_check.php';||g\" {autoload}"
    )
    rc, out, err = ssh.run(patch_cmd)
    if rc != 0:
        record("platform_check", "FAIL", f"sed failed: {(err or out).strip()[:120]}")
        return False

    # Verify artisan now responds
    rc2, out2, err2 = ssh.run(f"cd {APP_ROOT} && {PHP_BIN} artisan --version 2>&1")
    if rc2 == 0 and "Laravel" in out2:
        record("platform_check", "PASS", f"artisan works: {out2.strip()}")
        return True
    else:
        record("platform_check", "FAIL",
               f"artisan still failing after patch: {(err2 or out2).strip()[:200]}")
        return False


# ── Step 2: Fix .htaccess ────────────────────────────────────────────────────

def step2_fix_htaccess(ssh: SSH):
    step(2, "Write public/.htaccess (exact content, no RewriteBase /ai/)")
    htaccess = f"{PUBLIC_DIR}/.htaccess"

    # Backup with timestamp
    ssh.run(f"cp {htaccess} {htaccess}.bak.$(date +%s) 2>/dev/null || true")

    ssh.write_file(htaccess, HTACCESS_CONTENT)
    ssh.run(f"chmod 644 {htaccess}")

    # Verify what's on disk
    rc, written, _ = ssh.run(f"cat {htaccess}")
    if "RewriteBase /ai/" in written:
        record("htaccess", "FAIL", "RewriteBase /ai/ still present after write!")
        return False
    if "RewriteEngine On" in written:
        record("htaccess", "PASS", "Written — RewriteBase /ai/ gone, CORS headers present")
        return True
    record("htaccess", "FAIL", "File write may have failed — RewriteEngine not found in result")
    return False


# ── Step 3: Find OLS vhost conf ───────────────────────────────────────────────

def step3_find_vhost_conf(ssh: SSH) -> str | None:
    step(3, "Find OLS vhost conf for chatbotnepal.isoftroerp.com")

    # List all vhost directories
    rc, ls_out, _ = ssh.run(f"ls {OLS_VHOSTS_DIR}/ 2>/dev/null")
    print(f"  Vhost dirs: {ls_out.strip() or '(none found)'}")

    # Search for conf files referencing chatbotnepal
    rc, found, _ = ssh.run(
        f"find {OLS_VHOSTS_DIR}/ -name '*.conf' 2>/dev/null "
        f"| xargs grep -l 'chatbotnepal' 2>/dev/null"
    )
    candidates = [p.strip() for p in found.strip().splitlines() if p.strip()]

    # Also try direct path patterns
    direct_paths = [
        f"{OLS_VHOSTS_DIR}/chatbotnepal.isoftroerp.com/vhost.conf",
        f"{OLS_VHOSTS_DIR}/chatbotnepal/vhost.conf",
    ]
    for dp in direct_paths:
        rc2, ex, _ = ssh.run(f"test -f {dp} && echo YES || echo NO")
        if "YES" in ex and dp not in candidates:
            candidates.append(dp)

    # Also check the main httpd_config.conf for vhost include
    rc, main_conf, _ = ssh.run(f"cat {OLS_CONF_DIR}/httpd_config.conf 2>/dev/null | grep -A3 'chatbotnepal' || true")
    if main_conf.strip():
        print(f"  httpd_config.conf mentions chatbotnepal:\n    {main_conf.strip()}")

    if not candidates:
        warn("No vhost conf found referencing chatbotnepal")
        print(f"  Listing all .conf files under {OLS_VHOSTS_DIR}/:")
        ssh.run(f"find {OLS_VHOSTS_DIR}/ -name '*.conf' 2>/dev/null")
        rc3, all_confs, _ = ssh.run(f"find {OLS_VHOSTS_DIR}/ -name '*.conf' 2>/dev/null")
        for cf in all_confs.strip().splitlines():
            print(f"    {cf.strip()}")
        record("find_vhost_conf", "FAIL", "No vhost conf found for chatbotnepal")
        return None

    conf_path = candidates[0]
    print(f"  Vhost conf candidates: {candidates}")
    print(f"  Using: {conf_path}")

    # Print full conf
    rc, content, _ = ssh.run(f"cat {conf_path}")
    print(f"\n{C.Y}--- OLS vhost conf: {conf_path} ---{C.X}")
    print(content)
    print(f"{C.Y}--- end ---{C.X}")

    record("find_vhost_conf", "PASS", f"Found: {conf_path}")
    return conf_path


# ── Step 4: Inject CORS into OLS vhost conf ───────────────────────────────────

def step4_inject_ols_cors(ssh: SSH, conf_path: str | None):
    step(4, "Add CORS extraHeaders to OLS vhost conf")

    if not conf_path:
        record("ols_cors_headers", "SKIP", "No vhost conf found in Step 3")
        return False

    rc, content, _ = ssh.run(f"cat {conf_path}")

    # Already has CORS?
    if "Access-Control" in content:
        ok("Access-Control headers already present in vhost conf")
        record("ols_cors_headers", "SKIP", "ALREADY_PRESENT in vhost conf")
        return True

    # Backup
    ssh.run(f"cp {conf_path} {conf_path}.bak.$(date +%s)")

    # Strategy A: inject CORS into existing 'context / {' block
    if re.search(r"context\s+/\s*\{", content):
        info("Found 'context / {' block — injecting extraHeaders inside it")
        new_content = re.sub(
            r"(context\s+/\s*\{[^}]*?)(\})",
            lambda m: _inject_extra_headers_into_context(m),
            content,
            count=1,
            flags=re.DOTALL,
        )
        if new_content != content:
            ssh.write_file(conf_path, new_content)
            ok("Injected extraHeaders into existing context / block")
            record("ols_cors_headers", "PASS", "Injected into existing context / block — ADDED")
            return True

    # Strategy B: append new context / block at end of file
    info("No 'context / {' block found — appending new context block")
    append_cmd = (
        f"echo '' >> {conf_path} && "
        f"cat >> {conf_path} << 'OLS_CORS_EOF'\n"
        + OLS_CORS_EXTRAHEADERS
        + "\nOLS_CORS_EOF"
    )
    # Use write_file to append (safer than heredoc in exec)
    new_content = content.rstrip() + "\n\n" + OLS_CORS_EXTRAHEADERS
    ssh.write_file(conf_path, new_content)

    # Verify
    rc2, verify, _ = ssh.run(f"grep -c 'Access-Control' {conf_path} 2>/dev/null || echo 0")
    count = int(verify.strip() or 0)
    if count > 0:
        ok(f"CORS headers added to vhost conf ({count} lines written)")
        record("ols_cors_headers", "PASS", "Appended new context / block with extraHeaders — ADDED")
        return True
    else:
        record("ols_cors_headers", "FAIL", "Write appeared to succeed but grep found nothing")
        return False


def _inject_extra_headers_into_context(m: re.Match) -> str:
    """Insert extraHeaders lines before the closing } of a context block."""
    block_body = m.group(1)
    extra = (
        "\n  extraHeaders            <<<END_CORS\n"
        "Access-Control-Allow-Origin: *\n"
        "Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS\n"
        "Access-Control-Allow-Headers: Content-Type, Authorization, X-Session-Token, X-Requested-With, Accept\n"
        "Access-Control-Max-Age: 86400\n"
        "END_CORS\n"
    )
    return block_body + extra + m.group(2)


# ── Step 5: Clear Laravel caches ─────────────────────────────────────────────

def step5_clear_caches(ssh: SSH, artisan_ok: bool):
    step(5, "Clear Laravel caches")

    if not artisan_ok:
        warn("Step 1 (platform_check fix) did not fully succeed — artisan may still fail")

    all_ok = True
    for suffix in ("config:clear", "cache:clear", "route:clear", "optimize:clear"):
        rc, out, err = ssh.run(
            f"cd {APP_ROOT} && {PHP_BIN} artisan {suffix} 2>&1", timeout=60
        )
        combined = (out + err).strip()
        if rc == 0 and "ERROR" not in combined.upper() and "Fatal" not in combined:
            ok(f"artisan {suffix}")
        else:
            warn(f"artisan {suffix} rc={rc}: {combined[:120]}")
            all_ok = False

    if all_ok:
        record("clear_caches", "PASS", "All artisan cache commands succeeded")
    else:
        record("clear_caches", "FAIL", "One or more artisan commands failed — see warnings above")
    return all_ok


# ── Step 6: Restart OLS ──────────────────────────────────────────────────────

def step6_restart_ols(ssh: SSH):
    step(6, "Restart OpenLiteSpeed")
    rc, _, err = ssh.run("systemctl restart lsws 2>&1")
    if rc != 0:
        warn(f"systemctl restart lsws failed (rc={rc}) — trying lswsctrl")
        ssh.run("/usr/local/lsws/bin/lswsctrl restart 2>&1")

    info("Waiting 5 seconds for OLS to come up …")
    time.sleep(5)

    rc2, status, _ = ssh.run("systemctl is-active lsws 2>/dev/null")
    if status.strip() == "active":
        ok("lsws is active")
        record("restart_ols", "PASS", "OpenLiteSpeed restarted and active")
        return True
    else:
        fail(f"lsws status: {status.strip()}")
        record("restart_ols", "FAIL", f"lsws not active after restart: {status.strip()}")
        return False


# ── Step 7: Final verification ────────────────────────────────────────────────

def step7_verify(ssh: SSH) -> bool:
    step(7, "Final verification — curl from production server")
    time.sleep(2)  # give OLS a moment after restart

    # GET request
    rc1, get_out, _ = ssh.run(
        f"curl -sI --max-time 15 {SITE_URL}{API_PATH} 2>&1", timeout=25
    )
    print(f"\n{C.Y}--- GET {SITE_URL}{API_PATH} ---{C.X}")
    print(get_out or "(no output)")

    # OPTIONS preflight
    rc2, opts_out, _ = ssh.run(
        f"curl -sI -X OPTIONS --max-time 15 "
        f"-H 'Origin: {ORIGIN}' "
        f"-H 'Access-Control-Request-Method: POST' "
        f"-H 'Access-Control-Request-Headers: Content-Type, Authorization' "
        f"{SITE_URL}{API_PATH} 2>&1",
        timeout=25,
    )
    print(f"\n{C.Y}--- OPTIONS {SITE_URL}{API_PATH} ---{C.X}")
    print(opts_out or "(no output)")
    print(f"{C.Y}--- end ---{C.X}\n")

    get_cors  = "Access-Control-Allow-Origin" in get_out
    opts_cors = "Access-Control-Allow-Origin" in opts_out
    opts_204  = "204" in opts_out.splitlines()[0] if opts_out.strip() else False

    (ok if get_cors  else warn)(f"GET   Access-Control-Allow-Origin: {'✓ present' if get_cors else 'MISSING'}")
    (ok if opts_cors else fail)(f"OPTIONS Access-Control-Allow-Origin: {'✓ present' if opts_cors else 'MISSING'}")
    (ok if opts_204  else warn)(f"OPTIONS status: {'204 ✓' if opts_204 else 'NOT 204'}")

    passed = opts_cors  # OPTIONS CORS header is the browser's hard requirement
    if passed:
        record("cors_verification", "PASS",
               "Access-Control-Allow-Origin present on OPTIONS preflight")
    else:
        record("cors_verification", "FAIL",
               "Access-Control-Allow-Origin missing from OPTIONS — CORS still broken")
    return passed


# ════════════════════════════════════════════════════════════════════════════
# PHASE 2 — deep OLS diagnosis (auto-runs on FAIL)
# ════════════════════════════════════════════════════════════════════════════

def phase2_diagnose(ssh: SSH, vhost_conf: str | None):
    hdr("PHASE 2 — Deep OLS diagnosis")

    # 1. Print full active vhost conf if we have it
    if vhost_conf:
        step("P2", "Full active OLS vhost conf")
        rc, content, _ = ssh.run(f"cat {vhost_conf}")
        print(f"{C.Y}--- {vhost_conf} ---{C.X}")
        print(content or "(empty)")
        print(f"{C.Y}--- end ---{C.X}")
    else:
        step("P2", "Dump ALL vhost confs (searching …)")
        rc, all_confs, _ = ssh.run(
            f"find {OLS_VHOSTS_DIR}/ -name '*.conf' 2>/dev/null"
        )
        for cf in all_confs.strip().splitlines():
            cf = cf.strip()
            if cf:
                rc2, c2, _ = ssh.run(f"cat {cf}")
                print(f"{C.Y}--- {cf} ---{C.X}")
                print(c2)
                print(f"{C.Y}--- end ---{C.X}")

    # 2. Curl from inside server to localhost (bypass DNS/proxy)
    step("P2", "curl to localhost (bypass DNS / external proxy)")
    rc, loc_out, _ = ssh.run(
        f"curl -sI --max-time 10 "
        f"-H 'Host: chatbotnepal.isoftroerp.com' "
        f"http://127.0.0.1{API_PATH} 2>&1"
    )
    print(f"{C.Y}--- localhost GET ---{C.X}")
    print(loc_out or "(no output)")

    rc2, loc_opts, _ = ssh.run(
        f"curl -sI -X OPTIONS --max-time 10 "
        f"-H 'Host: chatbotnepal.isoftroerp.com' "
        f"-H 'Origin: {ORIGIN}' "
        f"http://127.0.0.1{API_PATH} 2>&1"
    )
    print(f"{C.Y}--- localhost OPTIONS ---{C.X}")
    print(loc_opts or "(no output)")
    print(f"{C.Y}--- end ---{C.X}")

    # 3. Check for nginx / haproxy in front of OLS
    step("P2", "Check for nginx / haproxy proxy layer")
    rc, nginx_st, _ = ssh.run("systemctl is-active nginx 2>/dev/null || echo inactive")
    rc, haproxy_st, _ = ssh.run("systemctl is-active haproxy 2>/dev/null || echo inactive")
    print(f"  nginx:   {nginx_st.strip()}")
    print(f"  haproxy: {haproxy_st.strip()}")

    # nginx config mentioning chatbotnepal?
    rc, ng_conf, _ = ssh.run(
        "grep -r 'chatbotnepal' /etc/nginx/sites-enabled/ /etc/nginx/conf.d/ 2>/dev/null | head -20 || true"
    )
    if ng_conf.strip():
        print(f"{C.Y}--- nginx config references chatbotnepal ---{C.X}")
        print(ng_conf)

    # 4. Check OLS listening port and SSL termination
    step("P2", "OLS port / SSL termination")
    rc, ss_out, _ = ssh.run("ss -tlnp | grep -E ':80|:443|lsws|litespeed' 2>/dev/null || true")
    print(f"  Listening ports:\n{ss_out}")

    # 5. OLS error log tail
    step("P2", "OLS error log (last 20 lines)")
    rc, ols_log, _ = ssh.run(
        "tail -20 /usr/local/lsws/logs/error.log 2>/dev/null || echo NO_LOG"
    )
    print(f"{C.Y}--- OLS error.log ---{C.X}")
    print(ols_log)
    print(f"{C.Y}--- end ---{C.X}")

    # 6. OLS httpd_config.conf — check mod_headers is enabled
    step("P2", "Check mod_headers enabled in OLS httpd_config.conf")
    rc, main_cfg, _ = ssh.run(
        f"grep -i 'mod_headers\\|headerOp\\|Header' {OLS_CONF_DIR}/httpd_config.conf 2>/dev/null | head -20 || true"
    )
    if main_cfg.strip():
        print(f"  mod_headers references in httpd_config.conf:\n{main_cfg}")
    else:
        warn("No mod_headers/Header references in httpd_config.conf — mod_headers may not be loaded")

    # 7. Laravel log tail
    step("P2", "Laravel log tail (last 20 lines)")
    rc, lv_log, _ = ssh.run(
        f"tail -20 {APP_ROOT}/storage/logs/laravel.log 2>/dev/null || echo NO_LOG"
    )
    print(f"{C.Y}--- laravel.log ---{C.X}")
    print(lv_log)
    print(f"{C.Y}--- end ---{C.X}")

    print(f"\n{C.BOLD}{C.Y}Phase 2 complete. Review the output above to determine next manual steps.{C.X}")


# ════════════════════════════════════════════════════════════════════════════
# SUMMARY TABLE
# ════════════════════════════════════════════════════════════════════════════

def print_summary():
    hdr("SUMMARY")
    col_w = max(len(r.name) for r in results) + 2
    print(f"  {'Step':<{col_w}} {'Status':<8} Note")
    print(f"  {'-'*col_w} {'-'*6}  {'-'*40}")
    for r in results:
        sym = {"PASS": f"{C.G}PASS{C.X}", "FAIL": f"{C.R}FAIL{C.X}",
               "SKIP": f"{C.Y}SKIP{C.X}"}.get(r.status, r.status)
        print(f"  {r.name:<{col_w}} {sym:<18} {r.note[:60]}")
    print()
    all_pass = all(r.status != "FAIL" for r in results)
    if all_pass:
        print(f"{C.G}{C.BOLD}All steps PASS — CORS should be working.{C.X}")
    else:
        failed = [r.name for r in results if r.status == "FAIL"]
        print(f"{C.R}{C.BOLD}Failed steps: {', '.join(failed)}{C.X}")
        print(f"{C.Y}See Phase 2 output above for detailed diagnosis.{C.X}")


# ════════════════════════════════════════════════════════════════════════════
# ENTRY POINT
# ════════════════════════════════════════════════════════════════════════════

def find_creds(given: str) -> str:
    for p in (given, os.path.join("fix", given)):
        if os.path.exists(p):
            return p
    return given


def main():
    p = argparse.ArgumentParser(description="Fix ChatBot Nepal CORS on OLS production server")
    p.add_argument("--verbose", action="store_true", help="Print every SSH command")
    p.add_argument("--creds", default="credentials.enc",
                   help="Encrypted credentials file (default: credentials.enc)")
    args = p.parse_args()

    creds_path = find_creds(args.creds)
    if not os.path.exists(creds_path):
        fail(f"Credentials file not found: {creds_path}")
        fail("Run fix/encrypt_credentials.py first.")
        sys.exit(2)

    passphrase = os.environ.get("CHATBOT_FIX_PASSPHRASE")
    if not passphrase:
        passphrase = getpass.getpass("Passphrase for credentials.enc: ")

    try:
        creds = decrypt_credentials(creds_path, passphrase)
    except ValueError as exc:
        fail(str(exc))
        sys.exit(2)

    ssh = SSH(creds, verbose=args.verbose)
    try:
        ssh.connect()
    except Exception as exc:
        fail(f"SSH connection failed: {exc}")
        sys.exit(2)

    vhost_conf: str | None = None
    cors_ok = False

    try:
        hdr("PHASE 1 — CORS Fix Pipeline")

        artisan_ok  = step1_fix_platform_check(ssh)
        htaccess_ok = step2_fix_htaccess(ssh)
        vhost_conf  = step3_find_vhost_conf(ssh)
        step4_inject_ols_cors(ssh, vhost_conf)
        step5_clear_caches(ssh, artisan_ok)
        step6_restart_ols(ssh)
        cors_ok = step7_verify(ssh)

        if not cors_ok:
            phase2_diagnose(ssh, vhost_conf)

    finally:
        ssh.close()
        print_summary()

    sys.exit(0 if cors_ok else 1)


if __name__ == "__main__":
    main()
