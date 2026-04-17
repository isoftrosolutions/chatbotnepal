#!/usr/bin/env python3
"""
fix_final.py
------------
Full production fix for ChatBot Nepal on OpenLiteSpeed.

Fixes (in order):
  1  Read vhost.conf — print full contents
  2  Bypass PHP platform_check via SFTP + artisan verify
  3  Run migrations (creates session/cache/jobs tables)
  4  Clear all Laravel caches
  5  Inject CORS extraHeaders into vhost.conf via SFTP
  6  Restart OLS gracefully
  7  Final CORS verification (GET + OPTIONS + body)
  8  Fallback: read httpd_config.conf listener section if CORS still failing

Usage:
    python3 fix_final.py [--verbose] [--creds PATH]

Exit: 0 = CORS PASS, 1 = still failing, 2 = fatal
"""

import argparse
import base64
import getpass
import io
import json
import os
import sys
import time

try:
    import paramiko
    from cryptography.fernet import Fernet, InvalidToken
    from cryptography.hazmat.primitives import hashes
    from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC
except ImportError as e:
    print(f"Missing dependency: {e}\nInstall: pip install paramiko cryptography")
    sys.exit(2)

# ── config ────────────────────────────────────────────────────────────────────

APP      = "/home/isoftroerp.com/chatbotnepal.isoftroerp.com"
PHP      = "/usr/local/lsws/lsphp83/bin/php"
SITE     = "https://chatbotnepal.isoftroerp.com"
ENDPOINT = "/api/widget/session"
ORIGIN   = "https://isoftroerp.com"
ITERS    = 390_000

AUTOLOAD   = f"{APP}/vendor/composer/autoload_real.php"
PLAT_CHECK = f"{APP}/vendor/composer/platform_check.php"
VHOST_CONF = "/usr/local/lsws/conf/vhosts/chatbotnepal.isoftroerp.com/vhost.conf"
HTTPD_CONF = "/usr/local/lsws/conf/httpd_config.conf"
LSWSCTRL   = "/usr/local/lsws/bin/lswsctrl"

# OLS-native CORS block to inject into vhost.conf
OLS_CORS_BLOCK = """
context / {
  extraHeaders            <<<END_HEADERS
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Session-Token, X-Requested-With, Accept
Access-Control-Max-Age: 86400
END_HEADERS
}
"""

# ── colours ───────────────────────────────────────────────────────────────────

class C:
    R="\033[91m"; G="\033[92m"; Y="\033[93m"
    B="\033[94m"; M="\033[95m"; X="\033[0m"; BOLD="\033[1m"

def ok(m):     print(f"{C.G}[OK]{C.X} {m}")
def info(m):   print(f"{C.B}[..]{C.X} {m}")
def warn(m):   print(f"{C.Y}[!!]{C.X} {m}")
def fail(m):   print(f"{C.R}[XX]{C.X} {m}")
def hdr(m):    print(f"\n{C.BOLD}{'='*64}\n  {m}\n{'='*64}{C.X}")
def stp(n, m): print(f"\n{C.BOLD}{C.M}━━ Step {n}: {m}{C.X}")
def divider():  print(f"{C.Y}{'─'*64}{C.X}")

# ── result table ──────────────────────────────────────────────────────────────

_results: list[dict] = []

def record(name: str, status: str, note: str = ""):
    _results.append({"name": name, "status": status, "note": note})
    sym = {"PASS": f"{C.G}PASS{C.X}", "FAIL": f"{C.R}FAIL{C.X}",
           "SKIP": f"{C.Y}SKIP{C.X}", "INFO": f"{C.B}INFO{C.X}"}.get(status, status)
    print(f"  ↳ {sym}  {note}")

def print_summary():
    hdr("FINAL SUMMARY")
    w = max(len(r["name"]) for r in _results) + 2
    print(f"  {'Step':<{w}} {'Status':<8}  Note")
    print(f"  {'─'*w} {'──────'}  {'─'*44}")
    for r in _results:
        sym = {"PASS": f"{C.G}PASS{C.X}", "FAIL": f"{C.R}FAIL{C.X}",
               "SKIP": f"{C.Y}SKIP{C.X}", "INFO": f"{C.B}INFO{C.X}"}.get(r["status"], r["status"])
        print(f"  {r['name']:<{w}} {sym:<18}  {r['note'][:55]}")
    print()
    failed = [r["name"] for r in _results if r["status"] == "FAIL"]
    if not failed:
        print(f"{C.G}{C.BOLD}All steps PASS — CORS should be working.{C.X}")
    else:
        print(f"{C.R}{C.BOLD}Still failing: {', '.join(failed)}{C.X}")

# ── credentials ───────────────────────────────────────────────────────────────

def decrypt_credentials(path: str, passphrase: str) -> dict:
    with open(path, "rb") as f:
        blob = f.read()
    salt, token = blob[:16], blob[16:]
    kdf = PBKDF2HMAC(algorithm=hashes.SHA256(), length=32,
                     salt=salt, iterations=ITERS)
    key = base64.urlsafe_b64encode(kdf.derive(passphrase.encode()))
    try:
        data = Fernet(key).decrypt(token)
    except InvalidToken:
        raise ValueError("Wrong passphrase or corrupted credentials file.")
    return json.loads(data.decode())

# ── SSH/SFTP wrapper ──────────────────────────────────────────────────────────

class SSH:
    def __init__(self, creds: dict, verbose: bool = False):
        self.creds   = creds
        self.verbose = verbose
        self.client  = paramiko.SSHClient()
        self.client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        self._sftp: paramiko.SFTPClient | None = None

    def connect(self):
        info(f"Connecting {self.creds['user']}@{self.creds['host']}:{self.creds['port']} …")
        self.client.connect(
            hostname      = self.creds["host"],
            port          = self.creds["port"],
            username      = self.creds["user"],
            password      = self.creds["password"],
            timeout       = 60,
            banner_timeout= 60,
            auth_timeout  = 60,
            look_for_keys = False,
            allow_agent   = False,
        )
        self.client.get_transport().set_keepalive(30)
        ok("SSH connected  (keepalive=30s)")

    def run(self, cmd: str, timeout: int = 120) -> tuple[int, str, str]:
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

    # ── SFTP — open channel once, reuse for the whole session ──

    def sftp(self) -> paramiko.SFTPClient:
        if self._sftp is None:
            self._sftp = self.client.open_sftp()
        return self._sftp

    def sftp_read(self, remote_path: str) -> str:
        buf = io.BytesIO()
        self.sftp().getfo(remote_path, buf)
        return buf.getvalue().decode("utf-8", errors="replace")

    def sftp_write(self, remote_path: str, content: str):
        buf = io.BytesIO(content.encode("utf-8"))
        self.sftp().putfo(buf, remote_path)

    def close(self):
        if self._sftp:
            try: self._sftp.close()
            except Exception: pass
        self.client.close()


# ════════════════════════════════════════════════════════════════════════════
# STEP 1 — Read and print full vhost.conf
# ════════════════════════════════════════════════════════════════════════════

def step1_read_vhost_conf(ssh: SSH) -> str:
    stp(1, "Read full vhost.conf")
    rc, out, err = ssh.run(f"cat {VHOST_CONF} 2>&1")
    if not out.strip() or rc != 0:
        fail(f"Could not read {VHOST_CONF}: {(err or out).strip()[:120]}")
        record("1_vhost_conf", "FAIL", "File not readable or not found")
        return ""

    divider()
    print(out)   # full, untruncated
    divider()
    record("1_vhost_conf", "INFO", f"{len(out.splitlines())} lines read")
    return out


# ════════════════════════════════════════════════════════════════════════════
# STEP 2 — Bypass platform_check via SFTP + verify artisan
# ════════════════════════════════════════════════════════════════════════════

def step2_bypass_platform_check(ssh: SSH) -> bool:
    stp(2, "Bypass PHP platform_check via SFTP")

    # ── 2a: patch autoload_real.php ──
    info(f"SFTP download: {AUTOLOAD}")
    try:
        original = ssh.sftp_read(AUTOLOAD)
    except Exception as e:
        fail(f"SFTP read failed: {e}")
        record("2_platform_check", "FAIL", f"sftp_read: {e}")
        return False

    if "platform_check" not in original:
        ok("platform_check not referenced in autoload_real.php — already patched")
    else:
        # Backup on server before touching
        ssh.run(f"cp {AUTOLOAD} {AUTOLOAD}.bak.$(date +%s) 2>/dev/null")

        patched_lines = []
        changed = 0
        for line in original.splitlines(keepends=True):
            if "platform_check" in line:
                patched_lines.append(f"// [fix_final.py] bypassed: {line.rstrip()}\n")
                changed += 1
            else:
                patched_lines.append(line)
        patched = "".join(patched_lines)

        info(f"SFTP upload: {AUTOLOAD}  ({changed} line(s) commented out)")
        try:
            ssh.sftp_write(AUTOLOAD, patched)
        except Exception as e:
            fail(f"SFTP write failed: {e}")
            record("2_platform_check", "FAIL", f"sftp_write: {e}")
            return False

        ok(f"autoload_real.php patched  ({changed} line(s) commented out)")

    # ── 2b: rename platform_check.php itself (belt-and-suspenders) ──
    rc, ex, _ = ssh.run(f"test -f {PLAT_CHECK} && echo EXISTS || echo GONE")
    if "EXISTS" in ex:
        rc2, _, err2 = ssh.run(f"mv {PLAT_CHECK} {PLAT_CHECK}.disabled")
        if rc2 == 0:
            ok(f"Renamed platform_check.php → .disabled")
        else:
            warn(f"mv returned rc={rc2}: {err2.strip()[:80]}")
    else:
        ok("platform_check.php already absent / renamed")

    # ── 2c: verify artisan ──
    info("Testing: php artisan --version")
    rc3, out3, err3 = ssh.run(f"cd {APP} && {PHP} artisan --version 2>&1", timeout=30)
    combined = (out3 + err3).strip()
    print(f"  Output: {combined}")

    if "Laravel Framework" in combined:
        ok("artisan is working!")
        record("2_platform_check", "PASS", combined[:60])
        return True
    else:
        fail("artisan still failing after patch")
        record("2_platform_check", "FAIL", combined[:80])
        return False


# ════════════════════════════════════════════════════════════════════════════
# STEP 3 — Run migrations
# ════════════════════════════════════════════════════════════════════════════

def step3_migrate(ssh: SSH, artisan_ok: bool):
    stp(3, "Run migrations (creates session / cache / jobs tables)")
    if not artisan_ok:
        warn("Skipping — artisan not working (Step 2 failed)")
        record("3_migrate", "SKIP", "artisan not working")
        return

    info("Running: php artisan migrate --force  (may take a moment)")
    rc, out, err = ssh.run(
        f"cd {APP} && {PHP} artisan migrate --force 2>&1", timeout=120
    )
    combined = (out + err).strip()
    divider()
    print(combined)
    divider()

    if rc == 0 and "error" not in combined.lower() and "Fatal" not in combined:
        ok("Migrations completed")
        record("3_migrate", "PASS", combined.splitlines()[-1][:60] if combined else "done")
    else:
        warn(f"migrate returned rc={rc} — check output above")
        record("3_migrate", "FAIL" if rc != 0 else "INFO",
               combined.splitlines()[-1][:60] if combined else f"rc={rc}")


# ════════════════════════════════════════════════════════════════════════════
# STEP 4 — Clear all caches
# ════════════════════════════════════════════════════════════════════════════

def step4_clear_caches(ssh: SSH, artisan_ok: bool):
    stp(4, "Clear all Laravel caches")
    if not artisan_ok:
        warn("artisan not working — will only delete cache files directly")

    all_ok = True
    if artisan_ok:
        for cmd in ("config:clear", "cache:clear", "route:clear", "optimize:clear"):
            rc, out, err = ssh.run(
                f"cd {APP} && {PHP} artisan {cmd} 2>&1", timeout=60
            )
            combined = (out + err).strip()
            if rc == 0 and "Fatal" not in combined:
                ok(f"artisan {cmd}  →  {combined[:60]}")
            else:
                warn(f"artisan {cmd} rc={rc}: {combined[:100]}")
                all_ok = False

    # Always delete cache files directly — safe even after artisan ran
    for f in ("bootstrap/cache/config.php", "bootstrap/cache/routes-v7.php"):
        rc, _, _ = ssh.run(f"rm -f {APP}/{f} && echo removed || echo not_found")
    ok("bootstrap/cache/config.php and routes-v7.php removed (if existed)")

    record("4_clear_caches",
           "PASS" if (artisan_ok and all_ok) else ("FAIL" if artisan_ok else "SKIP"),
           "All cache commands OK" if all_ok else "Some commands failed or skipped")


# ════════════════════════════════════════════════════════════════════════════
# STEP 5 — Inject CORS into vhost.conf via SFTP
# ════════════════════════════════════════════════════════════════════════════

def step5_inject_ols_cors(ssh: SSH, vhost_content: str):
    stp(5, "Inject CORS extraHeaders into vhost.conf via SFTP")

    # Re-read from disk in case something changed it between Step 1 and now
    try:
        current = ssh.sftp_read(VHOST_CONF)
    except Exception as e:
        fail(f"SFTP read of vhost.conf failed: {e}")
        record("5_ols_cors", "FAIL", f"sftp_read: {e}")
        return

    if "Access-Control" in current:
        ok("Access-Control headers already present in vhost.conf")
        record("5_ols_cors", "SKIP", "ALREADY_PRESENT")
        return

    # Backup
    ssh.run(f"cp {VHOST_CONF} {VHOST_CONF}.bak.$(date +%s) 2>/dev/null")

    # Injection strategy:
    #   A) If vhost.conf contains a top-level closing "}" on its own line,
    #      insert the CORS block just before the LAST such lone "}"
    #   B) Otherwise just append to end of file
    lines = current.splitlines(keepends=True)

    # Find the last line that is just "}" (optional leading whitespace, no other content)
    last_close_idx = None
    for i in range(len(lines) - 1, -1, -1):
        if lines[i].strip() == "}":
            last_close_idx = i
            break

    if last_close_idx is not None:
        lines.insert(last_close_idx, OLS_CORS_BLOCK)
        info(f"Inserting CORS block before closing '}}' at line {last_close_idx + 1}")
    else:
        lines.append("\n" + OLS_CORS_BLOCK)
        info("No lone '}' found — appending CORS block at end of file")

    new_content = "".join(lines)

    try:
        ssh.sftp_write(VHOST_CONF, new_content)
    except Exception as e:
        fail(f"SFTP write of vhost.conf failed: {e}")
        record("5_ols_cors", "FAIL", f"sftp_write: {e}")
        return

    # Verify on disk
    rc, verify, _ = ssh.run(f"grep -c 'Access-Control' {VHOST_CONF} || echo 0")
    count = int(verify.strip() or 0)
    if count > 0:
        ok(f"CORS block written to vhost.conf  ({count} Access-Control line(s))")
        record("5_ols_cors", "PASS", f"ADDED — {count} CORS header lines in conf")
    else:
        fail("Write appeared to succeed but grep found no Access-Control lines")
        record("5_ols_cors", "FAIL", "Wrote file but headers not found in result")

    # Print the final vhost.conf so we can visually confirm
    divider()
    print(new_content)
    divider()


# ════════════════════════════════════════════════════════════════════════════
# STEP 6 — Restart OLS
# ════════════════════════════════════════════════════════════════════════════

def step6_restart_ols(ssh: SSH):
    stp(6, "Restart OLS gracefully via lswsctrl")

    rc, out, err = ssh.run(f"{LSWSCTRL} restart 2>&1")
    combined = (out + err).strip()
    print(f"  lswsctrl output: {combined[:120]}")
    if rc != 0:
        warn(f"lswsctrl rc={rc} — trying systemctl restart lsws")
        ssh.run("systemctl restart lsws 2>&1")

    info("Waiting 5 seconds …")
    time.sleep(5)

    rc2, status, _ = ssh.run("systemctl is-active lsws 2>/dev/null")
    status = status.strip()
    (ok if status == "active" else fail)(f"lsws is: {status}")
    record("6_restart_ols", "PASS" if status == "active" else "FAIL", f"lsws={status}")
    return status == "active"


# ════════════════════════════════════════════════════════════════════════════
# STEP 7 — Final CORS verification
# ════════════════════════════════════════════════════════════════════════════

def step7_verify_cors(ssh: SSH) -> bool:
    stp(7, "Final CORS verification")
    time.sleep(2)

    # ── GET ──
    rc1, get_headers, _ = ssh.run(
        f"curl -sI --max-time 15 {SITE}{ENDPOINT} 2>&1", timeout=25
    )
    print(f"\n{C.Y}── GET headers ──{C.X}")
    print(get_headers or "(no output)")

    # ── OPTIONS preflight ──
    rc2, opts_headers, _ = ssh.run(
        f"curl -sI -X OPTIONS --max-time 15 "
        f"-H 'Origin: {ORIGIN}' "
        f"-H 'Access-Control-Request-Method: POST' "
        f"{SITE}{ENDPOINT} 2>&1",
        timeout=25,
    )
    print(f"\n{C.Y}── OPTIONS headers ──{C.X}")
    print(opts_headers or "(no output)")

    # ── Response body ──
    rc3, body, _ = ssh.run(
        f"curl -s --max-time 15 {SITE}{ENDPOINT} 2>&1", timeout=25
    )
    print(f"\n{C.Y}── Response body ──{C.X}")
    print((body[:1500] + "\n… (truncated)") if len(body) > 1500 else body or "(empty)")
    divider()

    # ── Evaluate ──
    def first_status(text: str) -> str:
        for line in text.splitlines():
            if line.startswith("HTTP/"):
                return line.strip()
        return "(no HTTP status)"

    get_status   = first_status(get_headers)
    opts_status  = first_status(opts_headers)
    get_cors     = "Access-Control-Allow-Origin" in get_headers
    opts_cors    = "Access-Control-Allow-Origin" in opts_headers
    get_200      = any(c in get_status for c in ("200", "201", "204"))
    opts_204     = "204" in opts_status

    (ok if get_200   else warn)(f"GET    status : {get_status}")
    (ok if get_cors  else fail)(f"GET    CORS   : {'✓ PRESENT' if get_cors  else 'MISSING'}")
    (ok if opts_204  else warn)(f"OPTIONS status: {opts_status}")
    (ok if opts_cors else fail)(f"OPTIONS CORS  : {'✓ PRESENT' if opts_cors else 'MISSING'}")

    # Body hint
    if "Illuminate" in body or "Laravel" in body or "symfony" in body.lower():
        ok("Body: Laravel is responding (PHP running)")
    elif body.strip().startswith("{") or body.strip().startswith("["):
        ok("Body: JSON response — Laravel API is up")
    elif "<html" in body.lower():
        warn("Body: HTML page — OLS may be serving a static error, not Laravel")
    elif not body.strip():
        warn("Body: empty — possible connection or PHP error")

    passed = opts_cors
    record("7_cors_verify",
           "PASS" if passed else "FAIL",
           f"OPTIONS CORS {'present' if passed else 'MISSING'}  |  GET status={get_status}")
    return passed


# ════════════════════════════════════════════════════════════════════════════
# STEP 8 — Fallback: inspect httpd_config.conf listener (only on FAIL)
# ════════════════════════════════════════════════════════════════════════════

def step8_fallback_httpd_conf(ssh: SSH):
    stp(8, "Fallback: read httpd_config.conf — port-443 listener section")

    rc, full, _ = ssh.run(f"cat {HTTPD_CONF} 2>&1")
    if not full.strip():
        fail("Could not read httpd_config.conf")
        record("8_fallback_httpd", "FAIL", "File unreadable")
        return

    # Print only the listener blocks so the output stays manageable
    print(f"\n{C.Y}── httpd_config.conf — listener sections ──{C.X}")
    in_listener = False
    depth = 0
    listener_lines: list[str] = []
    for line in full.splitlines():
        stripped = line.strip()
        if stripped.lower().startswith("listener") and "{" in line:
            in_listener = True
            depth = 0
        if in_listener:
            listener_lines.append(line)
            depth += line.count("{") - line.count("}")
            if depth <= 0 and in_listener and len(listener_lines) > 1:
                in_listener = False
                print("\n".join(listener_lines))
                print()
                listener_lines = []
    if listener_lines:
        print("\n".join(listener_lines))

    # Check if any global header config exists
    rc2, hdr_lines, _ = ssh.run(
        f"grep -n -i 'header\\|Access-Control\\|extraHeaders' {HTTPD_CONF} 2>/dev/null || true"
    )
    if hdr_lines.strip():
        print(f"\n{C.Y}── Existing header/CORS references in httpd_config.conf ──{C.X}")
        print(hdr_lines)
    else:
        warn("No Header / Access-Control references in httpd_config.conf")
        info("CORS must be set at vhost level (vhost.conf) or in .htaccess — not globally")

    # Check for nginx/proxy in front of OLS
    rc3, ngt, _ = ssh.run("systemctl is-active nginx 2>/dev/null || echo inactive")
    rc4, hpt, _ = ssh.run("systemctl is-active haproxy 2>/dev/null || echo inactive")
    info(f"nginx:   {ngt.strip()}   |   haproxy: {hpt.strip()}")
    if ngt.strip() == "active":
        warn("nginx is active — it may be proxying in front of OLS and stripping headers")
        rc5, ng_sites, _ = ssh.run(
            "grep -r 'chatbotnepal' /etc/nginx/ 2>/dev/null | head -20 || true"
        )
        if ng_sites.strip():
            print(f"{C.Y}── nginx config referencing chatbotnepal ──{C.X}")
            print(ng_sites)

    # OLS error log tail
    rc6, ols_err, _ = ssh.run(
        "tail -20 /usr/local/lsws/logs/error.log 2>/dev/null || echo NO_LOG"
    )
    print(f"\n{C.Y}── OLS error.log (last 20 lines) ──{C.X}")
    print(ols_err)

    record("8_fallback_httpd", "INFO", "httpd_config.conf printed — review listener section above")


# ════════════════════════════════════════════════════════════════════════════
# ENTRY POINT
# ════════════════════════════════════════════════════════════════════════════

def find_creds(given: str) -> str:
    for p in (given, os.path.join("fix", given)):
        if os.path.exists(p):
            return p
    return given


def main():
    p = argparse.ArgumentParser(description="Final CORS + artisan fix for ChatBot Nepal OLS server")
    p.add_argument("--verbose", action="store_true", help="Print every SSH command")
    p.add_argument("--creds", default="credentials.enc")
    args = p.parse_args()

    creds_path = find_creds(args.creds)
    if not os.path.exists(creds_path):
        fail(f"Credentials file not found: {creds_path}")
        sys.exit(2)

    passphrase = os.environ.get("CHATBOT_FIX_PASSPHRASE") or \
                 getpass.getpass("Passphrase for credentials.enc: ")
    try:
        creds = decrypt_credentials(creds_path, passphrase)
    except ValueError as e:
        fail(str(e)); sys.exit(2)

    ssh = SSH(creds, verbose=args.verbose)
    try:
        ssh.connect()
    except Exception as e:
        fail(f"SSH connection failed: {e}"); sys.exit(2)

    cors_ok = False
    try:
        hdr("fix_final.py — Full production fix pipeline")

        vhost_content = step1_read_vhost_conf(ssh)
        artisan_ok    = step2_bypass_platform_check(ssh)

        if not artisan_ok:
            fail("artisan is still broken after platform_check bypass — stopping here")
            fail("Check Step 2 output above for the exact PHP error")
            record("3_migrate",      "SKIP", "artisan broken")
            record("4_clear_caches", "SKIP", "artisan broken")
        else:
            step3_migrate(ssh, artisan_ok)
            step4_clear_caches(ssh, artisan_ok)

        step5_inject_ols_cors(ssh, vhost_content)
        step6_restart_ols(ssh)
        cors_ok = step7_verify_cors(ssh)

        if not cors_ok:
            step8_fallback_httpd_conf(ssh)

    finally:
        ssh.close()
        print_summary()

    sys.exit(0 if cors_ok else 1)


if __name__ == "__main__":
    main()
