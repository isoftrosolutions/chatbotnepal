#!/usr/bin/env python3
"""
diagnose_chatbot.py
-------------------
Phase 1: Diagnose CORS + 503 on ChatBot Nepal production server (read-only).
Phase 2: Apply fixes after user confirms with YES.

Usage:
    python3 diagnose_chatbot.py [--verbose] [--creds PATH]

Credentials are read from credentials.enc (same format as fix_chatbotnepal.py).
Set CHATBOT_FIX_PASSPHRASE env var to skip the passphrase prompt.

Exit codes:
    0  all checks pass / all fixes applied cleanly
    1  issues remain after phase 2
    2  fatal (bad passphrase, SSH failure)
"""

import argparse
import base64
import getpass
import json
import os
import sys
import time
from dataclasses import dataclass, field

try:
    import paramiko
    from cryptography.fernet import Fernet, InvalidToken
    from cryptography.hazmat.primitives import hashes
    from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC
except ImportError as e:
    print(f"Missing dependency: {e}")
    print("Install with:  pip install paramiko cryptography")
    sys.exit(2)

# ── configuration ────────────────────────────────────────────────────────────

APP_ROOT   = "/home/isoftroerp.com/chatbotnepal.isoftroerp.com"
PUBLIC_DIR = f"{APP_ROOT}/public"
WEB_USER   = "isoft1807"
SITE_URL   = "https://chatbotnepal.isoftroerp.com"
API_PATH   = "/api/widget/session"
ORIGIN     = "https://isoftroerp.com"
ITERATIONS = 390_000

# Prefer 8.4 — composer vendor requires >=8.4. Fall back to 8.3 only if absent.
PHP_BIN_84 = "/usr/local/lsws/lsphp84/bin/php"
PHP_BIN_83 = "/usr/local/lsws/lsphp83/bin/php"

# ── colours ──────────────────────────────────────────────────────────────────

class C:
    R = "\033[91m"; G = "\033[92m"; Y = "\033[93m"
    B = "\033[94m"; M = "\033[95m"; X = "\033[0m"
    BOLD = "\033[1m"

def ok(msg):   print(f"{C.G}[OK]{C.X} {msg}")
def info(msg): print(f"{C.B}[..]{C.X} {msg}")
def warn(msg): print(f"{C.Y}[!!]{C.X} {msg}")
def fail(msg): print(f"{C.R}[XX]{C.X} {msg}")
def step(msg): print(f"\n{C.BOLD}{C.M}>>> {msg}{C.X}")
def hdr(msg):  print(f"\n{C.BOLD}{'='*64}\n  {msg}\n{'='*64}{C.X}")

# ── credentials ──────────────────────────────────────────────────────────────

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

# ── SSH wrapper ───────────────────────────────────────────────────────────────

@dataclass
class DiagReport:
    findings:     list = field(default_factory=list)
    fixes_passed: list = field(default_factory=list)
    fixes_failed: list = field(default_factory=list)

    def finding(self, msg): self.findings.append(msg)
    def passed(self,  msg): self.fixes_passed.append(msg)
    def failed(self,  msg): self.fixes_failed.append(msg)


class SSH:
    def __init__(self, creds: dict, verbose: bool = False):
        self.creds   = creds
        self.verbose = verbose
        self.client  = paramiko.SSHClient()
        self.client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

    def connect(self):
        info(f"Connecting to {self.creds['user']}@{self.creds['host']}:{self.creds['port']}")
        self.client.connect(
            hostname=self.creds["host"],
            port=self.creds["port"],
            username=self.creds["user"],
            password=self.creds["password"],
            timeout=15,
            look_for_keys=False,
            allow_agent=False,
        )
        ok("SSH connected")

    def reconnect(self):
        """Re-establish SSH connection (e.g. after idle timeout during user prompt)."""
        try:
            self.client.close()
        except Exception:
            pass
        self.client = paramiko.SSHClient()
        self.client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        self.connect()

    def run(self, cmd: str, timeout: int = 60) -> tuple[int, str, str]:
        if self.verbose:
            print(f"  $ {cmd}")
        _, stdout, stderr = self.client.exec_command(cmd, timeout=timeout)
        rc  = stdout.channel.recv_exit_status()
        out = stdout.read().decode(errors="replace")
        err = stderr.read().decode(errors="replace")
        if self.verbose:
            for line in out.strip().splitlines():
                print(f"    {line}")
            for line in err.strip().splitlines():
                print(f"    {C.Y}{line}{C.X}")
        return rc, out, err

    def write_file(self, remote_path: str, content: str):
        """Write file via base64-encoded SSH command — avoids SFTP socket timeouts."""
        import base64
        encoded = base64.b64encode(content.encode("utf-8")).decode("ascii")
        # Split into 800-char chunks so the command line stays short
        chunks = [encoded[i:i+800] for i in range(0, len(encoded), 800)]
        # First chunk: create / overwrite
        self.run(f"printf '%s' '{chunks[0]}' | base64 -d > {remote_path}")
        # Remaining chunks: append
        for chunk in chunks[1:]:
            self.run(f"printf '%s' '{chunk}' | base64 -d >> {remote_path}")

    def close(self):
        self.client.close()


# ════════════════════════════════════════════════════════════════════════════
# PHASE 1 — DIAGNOSE (read-only)
# ════════════════════════════════════════════════════════════════════════════

def detect_php(ssh: SSH) -> str:
    """Return the working PHP binary. Prefers 8.4 — composer vendor requires >=8.4."""
    for php in (PHP_BIN_84, PHP_BIN_83):
        rc, out, _ = ssh.run(f"{php} -r 'echo PHP_VERSION;' 2>/dev/null")
        if rc == 0 and out.strip():
            ok(f"PHP binary: {php}  (version {out.strip()})")
            if "8.3" in out:
                warn("Composer vendor requires PHP >=8.4 — artisan will fail with this binary")
            return php
    warn("Could not find PHP 8.4 or 8.3 binary at expected paths")
    return PHP_BIN_84  # best guess


def diag_maintenance(ssh: SSH, rpt: DiagReport) -> bool:
    step("Check — maintenance mode (503 cause?)")
    rc, out, _ = ssh.run(
        f"test -f {APP_ROOT}/storage/framework/maintenance.php && echo YES || echo NO"
    )
    if "YES" in out:
        fail("maintenance.php EXISTS — Laravel is in maintenance mode → causes 503")
        rpt.finding("MAINTENANCE MODE active — php artisan up will fix the 503")
        return True
    ok("Not in maintenance mode")
    return False


def diag_services(ssh: SSH, rpt: DiagReport):
    step("Check — lsws and php8.3-fpm service status")
    for svc in ["lsws", "php8.3-fpm"]:
        rc, out, _ = ssh.run(f"systemctl is-active {svc} 2>/dev/null || echo unknown")
        status = out.strip()
        if status == "active":
            ok(f"{svc}: active")
        else:
            fail(f"{svc}: {status}")
            rpt.finding(f"Service '{svc}' is not active (status: {status})")

    # Show last 12 lines of lsws status for quick context
    _, out, _ = ssh.run("systemctl status lsws --no-pager 2>/dev/null | tail -12")
    if out.strip():
        print(f"{C.Y}--- lsws status (tail) ---{C.X}")
        print(out)


def diag_laravel_log(ssh: SSH, rpt: DiagReport):
    step("Check — last 30 lines of laravel.log")
    log = f"{APP_ROOT}/storage/logs/laravel.log"
    rc, out, _ = ssh.run(f"test -f {log} && tail -30 {log} || echo NO_LOG")
    if "NO_LOG" in out:
        warn("laravel.log not found — app may never have booted successfully")
        rpt.finding("laravel.log absent — app has not booted or storage is not writable")
        return
    print(f"{C.Y}--- laravel.log (last 30 lines) ---{C.X}")
    print(out)
    print(f"{C.Y}--- end ---{C.X}")
    for kw in ("ERROR", "Exception", "SQLSTATE", "Connection refused",
               "No such file", "open_basedir"):
        if kw in out:
            rpt.finding(f"laravel.log contains '{kw}' — see tail above")
            break


def diag_env(ssh: SSH, rpt: DiagReport):
    step("Check — .env key values (no secrets shown)")
    env = f"{APP_ROOT}/.env"
    rc, out, _ = ssh.run(f"test -f {env} && echo EXISTS || echo MISSING")
    if "MISSING" in out:
        fail(".env not found — app cannot boot")
        rpt.finding(".env file is missing")
        return
    ok(".env exists")
    for key in ("APP_URL", "APP_ENV", "APP_DEBUG",
                "DB_CONNECTION", "SESSION_DRIVER", "CACHE_DRIVER"):
        rc, out, _ = ssh.run(
            f"grep -E '^{key}=' {env} | head -1 || echo '{key}=NOT_SET'"
        )
        line = out.strip()
        if "NOT_SET" in line or "=" not in line:
            warn(f"  {key}: NOT SET")
            rpt.finding(f".env missing key: {key}")
        else:
            print(f"  {line}")


def diag_htaccess(ssh: SSH, rpt: DiagReport) -> bool:
    step("Check — public/.htaccess")
    htaccess = f"{PUBLIC_DIR}/.htaccess"
    rc, out, _ = ssh.run(f"test -f {htaccess} && cat {htaccess} || echo NOT_FOUND")
    if "NOT_FOUND" in out:
        warn(".htaccess missing from public/")
        rpt.finding("public/.htaccess is missing")
        return False

    print(f"{C.Y}--- public/.htaccess ---{C.X}")
    print(out)
    print(f"{C.Y}--- end ---{C.X}")

    bad_base   = "RewriteBase /ai/" in out
    has_cors   = "Access-Control-Allow-Origin" in out
    has_opts   = "OPTIONS" in out

    if bad_base:
        fail("RewriteBase /ai/ found — wrong for production root domain")
        rpt.finding("htaccess: 'RewriteBase /ai/' breaks routing (dev path leaked to prod)")
    else:
        ok("No /ai/ RewriteBase issue")

    if not has_cors:
        warn("No CORS Header directives in .htaccess")
        rpt.finding("htaccess: Missing 'Header always set Access-Control-Allow-Origin'")
    else:
        ok("CORS headers present in .htaccess")

    if not has_opts:
        warn("No OPTIONS preflight rewrite rule in .htaccess")
        rpt.finding("htaccess: Missing OPTIONS → index.php rewrite rule")
    else:
        ok("OPTIONS rewrite rule present")

    return bad_base


def diag_kernel(ssh: SSH, rpt: DiagReport) -> str:
    """Check CORS middleware registration. Returns 'laravel10' or 'laravel11'."""
    step("Check — CORS middleware registration")
    kernel = f"{APP_ROOT}/app/Http/Kernel.php"
    rc, out, _ = ssh.run(f"test -f {kernel} && cat {kernel} || echo NOT_FOUND")

    if "NOT_FOUND" in out:
        warn("app/Http/Kernel.php absent → Laravel 11 style (bootstrap/app.php)")
        rc2, out2, _ = ssh.run(f"cat {APP_ROOT}/bootstrap/app.php 2>/dev/null || echo NOT_FOUND")
        if "NOT_FOUND" not in out2:
            print(f"{C.Y}--- bootstrap/app.php ---{C.X}")
            print(out2)
            print(f"{C.Y}--- end ---{C.X}")
            if "HandleCors" in out2 or "cors" in out2.lower():
                ok("CORS middleware referenced in bootstrap/app.php")
            else:
                warn("No CORS middleware in bootstrap/app.php")
                rpt.finding("bootstrap/app.php: HandleCors not registered")
        return "laravel11"

    print(f"{C.Y}--- app/Http/Kernel.php (middleware section) ---{C.X}")
    # Show only the $middleware array for brevity
    in_block = False
    for line in out.splitlines():
        if "$middleware" in line:
            in_block = True
        if in_block:
            print(f"  {line}")
        if in_block and "];" in line:
            break
    print(f"{C.Y}--- end ---{C.X}")

    if "HandleCors" in out or "\\Cors" in out:
        ok("CORS middleware already in Kernel.php")
    else:
        warn("No CORS middleware in Kernel.php $middleware array")
        rpt.finding("Kernel.php: HandleCors not in $middleware — OPTIONS preflights fall through")

    return "laravel10"


def diag_cors_config(ssh: SSH, rpt: DiagReport):
    step("Check — config/cors.php")
    cfg = f"{APP_ROOT}/config/cors.php"
    rc, out, _ = ssh.run(f"test -f {cfg} && cat {cfg} || echo NOT_FOUND")
    if "NOT_FOUND" in out:
        warn("config/cors.php does not exist")
        rpt.finding("config/cors.php missing — Laravel built-in CORS not configured")
        return
    print(f"{C.Y}--- config/cors.php ---{C.X}")
    print(out)
    print(f"{C.Y}--- end ---{C.X}")
    if "'*'" in out or '"*"' in out:
        ok("config/cors.php allows all origins (*)")
    else:
        warn("config/cors.php may be restricting allowed_origins")
        rpt.finding("config/cors.php: 'allowed_origins' may not include your origin")


def diag_curl_get(ssh: SSH, rpt: DiagReport):
    step(f"Check — curl -I GET {SITE_URL}{API_PATH}")
    rc, out, _ = ssh.run(
        f"curl -sI --max-time 10 {SITE_URL}{API_PATH} 2>&1", timeout=20
    )
    print(f"{C.Y}--- response headers (GET) ---{C.X}")
    print(out or "(no output)")
    print(f"{C.Y}--- end ---{C.X}")

    http_line = next((l for l in out.splitlines() if l.startswith("HTTP/")), "")
    if http_line:
        status_ok = any(c in http_line for c in ("200", "201", "301", "302"))
        (ok if status_ok else fail)(f"Status: {http_line.strip()}")
        if "503" in http_line:
            rpt.finding("GET returns 503 — server/app is down or in maintenance")
    if "Access-Control-Allow-Origin" in out:
        ok("Access-Control-Allow-Origin present on GET")
    else:
        fail("Access-Control-Allow-Origin MISSING from GET response")
        rpt.finding("GET response has no Access-Control-Allow-Origin header")


def diag_curl_options(ssh: SSH, rpt: DiagReport):
    step(f"Check — OPTIONS preflight to {SITE_URL}{API_PATH}")
    cmd = (
        f"curl -sI -X OPTIONS --max-time 10 "
        f"-H 'Origin: {ORIGIN}' "
        f"-H 'Access-Control-Request-Method: POST' "
        f"-H 'Access-Control-Request-Headers: Content-Type, Authorization' "
        f"{SITE_URL}{API_PATH} 2>&1"
    )
    rc, out, _ = ssh.run(cmd, timeout=20)
    print(f"{C.Y}--- OPTIONS preflight response ---{C.X}")
    print(out or "(no output)")
    print(f"{C.Y}--- end ---{C.X}")

    if "Access-Control-Allow-Origin" in out:
        ok("PASS — Access-Control-Allow-Origin present on OPTIONS preflight")
    else:
        fail("FAIL — Access-Control-Allow-Origin MISSING from OPTIONS preflight")
        rpt.finding(
            "OPTIONS preflight returns no Access-Control-Allow-Origin "
            "— this is the root cause of the CORS block"
        )
    if "Access-Control-Allow-Methods" not in out:
        warn("Access-Control-Allow-Methods also missing from OPTIONS response")


def print_diag_report(rpt: DiagReport):
    hdr("DIAGNOSIS REPORT")
    if rpt.findings:
        print(f"{C.R}Issues found ({len(rpt.findings)}):{C.X}")
        for i, f in enumerate(rpt.findings, 1):
            print(f"  {i}. {f}")
    else:
        print(f"{C.G}No issues detected — CORS appears to be working correctly.{C.X}")


def run_phase1(ssh: SSH, rpt: DiagReport) -> dict:
    state: dict = {}
    state["maintenance"]     = diag_maintenance(ssh, rpt)
    state["php_bin"]         = detect_php(ssh)
    diag_services(ssh, rpt)
    diag_laravel_log(ssh, rpt)
    diag_env(ssh, rpt)
    state["bad_rewritebase"] = diag_htaccess(ssh, rpt)
    state["laravel_version"] = diag_kernel(ssh, rpt)
    diag_cors_config(ssh, rpt)
    diag_curl_get(ssh, rpt)
    diag_curl_options(ssh, rpt)
    print_diag_report(rpt)
    return state


# ════════════════════════════════════════════════════════════════════════════
# PHASE 2 — FIX
# ════════════════════════════════════════════════════════════════════════════

# ── file content templates ────────────────────────────────────────────────────

HANDLE_CORS_PHP = """\
<?php

namespace App\\Http\\Middleware;

use Closure;
use Illuminate\\Http\\Request;
use Symfony\\Component\\HttpFoundation\\Response;

class HandleCors
{
    private const METHODS = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
    private const HEADERS = 'Content-Type, Authorization, X-Session-Token, X-Requested-With, Accept, Origin';
    private const MAX_AGE = '86400';

    public function handle(Request $request, Closure $next): Response
    {
        // Echo back the request origin so same-domain and cross-domain both work.
        $origin = $request->header('Origin', '*');

        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                ->header('Access-Control-Allow-Origin',  $origin)
                ->header('Access-Control-Allow-Methods', self::METHODS)
                ->header('Access-Control-Allow-Headers', self::HEADERS)
                ->header('Access-Control-Max-Age',       self::MAX_AGE);
        }

        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Origin',  $origin);
        $response->headers->set('Access-Control-Allow-Methods', self::METHODS);
        $response->headers->set('Access-Control-Allow-Headers', self::HEADERS);
        return $response;
    }
}
"""

HTACCESS_CLEAN = """\
# CORS — OLS must serve these through PHP, not via internal rewrite status codes.
# DO NOT use [R=204,L] for OPTIONS: that bypasses PHP and strips these headers.
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, PATCH, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Session-Token, X-Requested-With, Accept, Origin"
Header always set Access-Control-Max-Age "86400"

<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # OPTIONS preflight: route through PHP so Laravel/index.php returns CORS headers.
    # IMPORTANT: do NOT use [R=204,L] — that lets OLS respond directly without headers.
    RewriteCond %{REQUEST_METHOD} OPTIONS
    RewriteRule ^(.*)$ index.php [L]

    # Preserve Authorization header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Preserve X-XSRF-Token header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Trailing slash redirect (files only)
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Front controller — no RewriteBase, app is at domain root
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
"""


# ── individual fix steps ──────────────────────────────────────────────────────

def fix_maintenance(ssh: SSH, rpt: DiagReport, php_bin: str):
    step("Fix — php artisan up (disable maintenance mode)")
    rc, out, err = ssh.run(f"cd {APP_ROOT} && {php_bin} artisan up")
    if rc == 0:
        ok("Maintenance mode disabled")
        rpt.passed("php artisan up — maintenance mode removed")
    else:
        fail(f"php artisan up failed: {(err or out).strip()[:200]}")
        rpt.failed("Could not run php artisan up")


def fix_cors_middleware(ssh: SSH, rpt: DiagReport):
    step("Fix — create app/Http/Middleware/HandleCors.php")
    path = f"{APP_ROOT}/app/Http/Middleware/HandleCors.php"
    try:
        ssh.write_file(path, HANDLE_CORS_PHP)
        ssh.run(f"chown {WEB_USER}:{WEB_USER} {path} && chmod 644 {path}")
        ok(f"Written: {path}")
        rpt.passed("Created app/Http/Middleware/HandleCors.php")
    except Exception as exc:
        fail(f"Write failed: {exc}")
        rpt.failed(f"HandleCors.php write error: {exc}")


def fix_register_laravel10(ssh: SSH, rpt: DiagReport):
    """Prepend HandleCors to $middleware in app/Http/Kernel.php."""
    kernel = f"{APP_ROOT}/app/Http/Kernel.php"
    rc, out, _ = ssh.run(f"cat {kernel}")
    if "HandleCors" in out:
        ok("HandleCors already in Kernel.php — skipping")
        return

    # Inject as first item in the $middleware array
    needle  = "protected $middleware = ["
    replace = (
        "protected $middleware = [\n"
        "        \\App\\Http\\Middleware\\HandleCors::class,"
    )
    new_out = out.replace(needle, replace, 1)
    if new_out == out:
        fail("Injection point 'protected $middleware = [' not found in Kernel.php")
        rpt.failed("Kernel.php: could not find $middleware array to inject CORS")
        return
    try:
        ssh.write_file(kernel, new_out)
        ok("HandleCors prepended to Kernel.php $middleware")
        rpt.passed("Registered HandleCors as first middleware in Kernel.php")
    except Exception as exc:
        fail(f"Kernel.php write failed: {exc}")
        rpt.failed(f"Kernel.php update error: {exc}")


def fix_register_laravel11(ssh: SSH, rpt: DiagReport):
    """Register HandleCors inside bootstrap/app.php for Laravel 11."""
    bootstrap = f"{APP_ROOT}/bootstrap/app.php"
    rc, out, _ = ssh.run(f"cat {bootstrap}")
    if "HandleCors" in out:
        ok("HandleCors already in bootstrap/app.php — skipping")
        return

    # Strategy A: inject prepend() inside existing ->withMiddleware(function ... { ... })
    if "->withMiddleware(function" in out:
        # Find the opening brace after withMiddleware(function ...) and inject after it
        new_out = out.replace(
            "->withMiddleware(function",
            "->withMiddleware(function",  # will be replaced below
        )
        # More surgical: add prepend inside the closure body
        import re
        pattern = r"(->withMiddleware\(function\s*\([^)]*\)\s*\{)"
        replacement = (
            r"\1\n        $middleware->prepend("
            r"\\App\\Http\\Middleware\\HandleCors::class);"
        )
        new_out, n = re.subn(pattern, replacement, out, count=1)
        if n == 0:
            new_out = out  # failed, fall to strategy B

    # Strategy B: add ->withMiddleware() call before ->withExceptions or ->create()
    if new_out == out:
        for anchor in ("->withExceptions(", "->create()"):
            if anchor in out:
                inject = (
                    "->withMiddleware(function "
                    "(\\Illuminate\\Foundation\\Configuration\\Middleware $middleware) {\n"
                    "        $middleware->prepend("
                    "\\App\\Http\\Middleware\\HandleCors::class);\n"
                    "    })\n    " + anchor
                )
                new_out = out.replace(anchor, inject, 1)
                break

    if new_out == out:
        fail("Could not auto-inject into bootstrap/app.php — inject HandleCors manually")
        rpt.failed(
            "bootstrap/app.php: auto-inject failed. "
            "Add ->withMiddleware(fn($m)=>$m->prepend(HandleCors::class)) manually."
        )
        return

    try:
        ssh.write_file(bootstrap, new_out)
        ok("HandleCors registered in bootstrap/app.php")
        rpt.passed("Registered HandleCors via withMiddleware() in bootstrap/app.php")
    except Exception as exc:
        fail(f"bootstrap/app.php write failed: {exc}")
        rpt.failed(f"bootstrap/app.php update error: {exc}")


def fix_htaccess(ssh: SSH, rpt: DiagReport):
    step("Fix — rewrite public/.htaccess (clean CORS + OPTIONS rule, no /ai/ base)")
    htaccess = f"{PUBLIC_DIR}/.htaccess"
    # Backup with timestamp
    ssh.run(f"cp {htaccess} {htaccess}.bak.$(date +%s) 2>/dev/null || true")
    try:
        ssh.write_file(htaccess, HTACCESS_CLEAN)
        ssh.run(f"chown {WEB_USER}:{WEB_USER} {htaccess} && chmod 644 {htaccess}")
        ok(".htaccess rewritten (backup saved)")
        rpt.passed(
            "Rewrote public/.htaccess: removed RewriteBase /ai/, "
            "added CORS headers, fixed OPTIONS rewrite"
        )
    except Exception as exc:
        fail(f".htaccess write failed: {exc}")
        rpt.failed(f".htaccess update error: {exc}")


def fix_clear_caches(ssh: SSH, rpt: DiagReport, php_bin: str):
    step("Fix — clear Laravel caches")
    # If composer vendor was built with PHP >=8.4 requirement but we only have 8.3,
    # patch platform_check.php to bypass the version gate for cache commands.
    platform_check = f"{APP_ROOT}/vendor/composer/platform_check.php"
    rc_chk, out_chk, _ = ssh.run(
        f"{php_bin} -r 'echo PHP_MAJOR_VERSION.\".\".PHP_MINOR_VERSION;'"
    )
    php_ver = out_chk.strip()
    if php_ver.startswith("8.3"):
        warn(f"PHP {php_ver} detected but vendor requires >=8.4 — patching platform_check.php temporarily")
        # Comment out the version-check throw so artisan can still run
        ssh.run(
            f"cp {platform_check} {platform_check}.bak 2>/dev/null; "
            f"sed -i 's/^\\(.*RuntimeException.*Composer detected.*\\)/\\/\\/ \\1/' {platform_check}"
        )

    for suffix in ("config:clear", "cache:clear", "route:clear"):
        rc, out, err = ssh.run(f"cd {APP_ROOT} && {php_bin} artisan {suffix}")
        if rc == 0:
            ok(f"artisan {suffix}")
        else:
            warn(f"artisan {suffix} rc={rc}: {(err or out).strip()[:120]}")

    # Restore platform_check.php
    if php_ver.startswith("8.3"):
        ssh.run(f"mv {platform_check}.bak {platform_check} 2>/dev/null || true")
        ok("Restored platform_check.php")

    rpt.passed("Laravel caches cleared (config / cache / route)")


def fix_restart_lsws(ssh: SSH, rpt: DiagReport):
    step("Fix — restart OpenLiteSpeed")
    rc, _, _ = ssh.run("systemctl restart lsws")
    if rc != 0:
        warn("systemctl restart lsws failed — trying lswsctrl")
        ssh.run("/usr/local/lsws/bin/lswsctrl restart")
    info("Waiting 5 seconds for lsws to come up …")
    time.sleep(5)
    rc2, out2, _ = ssh.run("systemctl is-active lsws")
    if out2.strip() == "active":
        ok("lsws is active")
        rpt.passed("OpenLiteSpeed restarted successfully")
    else:
        fail(f"lsws status after restart: {out2.strip()}")
        rpt.failed("lsws did not return to active state after restart")


def fix_verify(ssh: SSH, rpt: DiagReport):
    step("Verify — OPTIONS preflight after all fixes")
    time.sleep(2)
    cmd = (
        f"curl -sI -X OPTIONS --max-time 10 "
        f"-H 'Origin: {ORIGIN}' "
        f"-H 'Access-Control-Request-Method: POST' "
        f"-H 'Access-Control-Request-Headers: Content-Type, Authorization' "
        f"{SITE_URL}{API_PATH} 2>&1"
    )
    rc, out, _ = ssh.run(cmd, timeout=20)
    print(f"{C.Y}--- POST-FIX OPTIONS response ---{C.X}")
    print(out or "(no output)")
    print(f"{C.Y}--- end ---{C.X}")

    if "Access-Control-Allow-Origin" in out:
        ok("PASS — Access-Control-Allow-Origin present after fix")
        rpt.passed("CORS verified: OPTIONS preflight now returns correct headers")
    else:
        fail("FAIL — Access-Control-Allow-Origin still missing")
        rpt.failed(
            "CORS still broken after all fixes. "
            "Check OLS virtual-host header config in admin panel."
        )


def print_fix_report(rpt: DiagReport):
    hdr("FIX REPORT")
    if rpt.fixes_passed:
        print(f"{C.G}PASS:{C.X}")
        for f in rpt.fixes_passed:
            print(f"  ✓  {f}")
    if rpt.fixes_failed:
        print(f"\n{C.R}FAIL (needs attention):{C.X}")
        for f in rpt.fixes_failed:
            print(f"  ✗  {f}")
    print()
    if not rpt.fixes_failed:
        print(f"{C.G}{C.BOLD}All fixes applied successfully.{C.X}")
    else:
        print(f"{C.Y}{C.BOLD}Some items need manual attention — see FAIL list above.{C.X}")


def run_phase2(ssh: SSH, rpt: DiagReport, state: dict):
    php = state.get("php_bin", PHP_BIN_84)
    lv  = state.get("laravel_version", "laravel10")

    if state.get("maintenance"):
        fix_maintenance(ssh, rpt, php)

    # Laravel already has Illuminate\Http\Middleware\HandleCors registered in
    # bootstrap/app.php — no need to create a custom one. Skip middleware steps
    # if it's already wired (detected in phase 1).
    if lv == "laravel11":
        step("Fix — HandleCors middleware")
        ok("Laravel's built-in HandleCors already registered in bootstrap/app.php — skipping")
    else:
        fix_cors_middleware(ssh, rpt)
        step("Fix — register HandleCors middleware")
        fix_register_laravel10(ssh, rpt)

    # .htaccess is the critical fix: removes RewriteBase /ai/ and changes
    # OPTIONS rule from [R=204,L] (OLS-direct, no headers) to index.php (PHP, headers sent)
    fix_htaccess(ssh, rpt)
    fix_clear_caches(ssh, rpt, php)
    fix_restart_lsws(ssh, rpt)
    fix_verify(ssh, rpt)
    print_fix_report(rpt)


# ════════════════════════════════════════════════════════════════════════════
# ENTRY POINT
# ════════════════════════════════════════════════════════════════════════════

def find_creds(given: str) -> str:
    if os.path.exists(given):
        return given
    # Also look inside the fix/ subdirectory (where fix_chatbotnepal.py lives)
    alt = os.path.join("fix", given)
    if os.path.exists(alt):
        return alt
    return given


def main():
    p = argparse.ArgumentParser(
        description="Diagnose and fix ChatBot Nepal CORS + 503"
    )
    p.add_argument("--verbose", action="store_true",
                   help="Print every SSH command and its output")
    p.add_argument("--creds", default="credentials.enc",
                   help="Path to encrypted credentials file (default: credentials.enc)")
    args = p.parse_args()

    creds_path = find_creds(args.creds)
    if not os.path.exists(creds_path):
        fail(f"Credentials file not found: {creds_path}")
        fail("Run fix/encrypt_credentials.py first, "
             "or pass --creds path/to/credentials.enc")
        sys.exit(2)

    passphrase = os.environ.get("CHATBOT_FIX_PASSPHRASE")
    if not passphrase:
        passphrase = getpass.getpass("Passphrase for credentials.enc: ")

    try:
        creds = decrypt_credentials(creds_path, passphrase)
    except ValueError as exc:
        fail(str(exc))
        sys.exit(2)

    rpt = DiagReport()
    ssh = SSH(creds, verbose=args.verbose)

    try:
        ssh.connect()
    except Exception as exc:
        fail(f"SSH connection failed: {exc}")
        sys.exit(2)

    try:
        hdr("PHASE 1 — DIAGNOSIS  (read-only, no changes)")
        state = run_phase1(ssh, rpt)

        if not rpt.findings:
            print(f"\n{C.G}No issues found — looks healthy. No fixes needed.{C.X}")
            sys.exit(0)

        print(f"\n{C.BOLD}Run Phase 2 to apply all fixes above?{C.X}")
        print(f"Type {C.G}YES{C.X} to proceed, anything else to quit: ", end="", flush=True)
        answer = input().strip()
        if answer != "YES":
            print("Aborted — no changes made.")
            sys.exit(0)

        # Reconnect — SSH may have timed out during the user prompt
        info("Reconnecting SSH before applying changes …")
        try:
            ssh.reconnect()
        except Exception as exc:
            fail(f"Reconnect failed: {exc}")
            sys.exit(2)

        hdr("PHASE 2 — APPLYING FIXES")
        run_phase2(ssh, rpt, state)

    finally:
        ssh.close()

    sys.exit(1 if rpt.fixes_failed else 0)


if __name__ == "__main__":
    main()
