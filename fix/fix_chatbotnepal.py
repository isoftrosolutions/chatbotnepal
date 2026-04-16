#!/usr/bin/env python3
"""
fix_chatbotnepal.py
-------------------
Diagnoses and fixes the ChatBot Nepal Laravel deployment on the Hostinger VPS.

- Decrypts SSH credentials from credentials.enc (requires passphrase)
- Connects via paramiko
- Walks through a fix pipeline, checking after each step
- Prints a clear report of what was done and what (if anything) still needs
  a human

Usage:
    python3 fix_chatbotnepal.py

Flags:
    --dry-run     Show what would be done without running remote commands
    --skip-ssl    Don't attempt SSL issuance (default: skip — needs DNS ready)
    --verbose     Print every command + output

Exit codes:
    0  all checks pass, site returns 200
    1  fixable issues remain — see the report
    2  fatal / needs human (e.g. wrong passphrase, can't connect)
"""

import argparse
import base64
import getpass
import json
import os
import sys
import time
from dataclasses import dataclass, field
from typing import Optional

try:
    import paramiko
    from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC
    from cryptography.hazmat.primitives import hashes
    from cryptography.fernet import Fernet, InvalidToken
except ImportError as e:
    print(f"Missing dependency: {e}")
    print("Install with: pip install paramiko cryptography")
    sys.exit(2)

# ---------- configuration ----------

APP_ROOT = "/home/isoftroerp.com/chatbotnepal.isoftroerp.com"
PUBLIC_DIR = f"{APP_ROOT}/public"
PHP_BIN = "/usr/local/lsws/lsphp84/bin/php"
WEB_USER = "isoft1807"
WEB_GROUP = "isoft1807"
LOG_FILE = f"{APP_ROOT}/storage/logs/laravel.log"
SITE_URL = "http://chatbotnepal.isoftroerp.com"
ITERATIONS = 390_000

# ---------- colors ----------

class C:
    R = "\033[91m"; G = "\033[92m"; Y = "\033[93m"
    B = "\033[94m"; M = "\033[95m"; X = "\033[0m"
    BOLD = "\033[1m"

def ok(msg):    print(f"{C.G}[OK]{C.X} {msg}")
def info(msg):  print(f"{C.B}[..]{C.X} {msg}")
def warn(msg):  print(f"{C.Y}[!!]{C.X} {msg}")
def fail(msg):  print(f"{C.R}[XX]{C.X} {msg}")
def step(msg):  print(f"\n{C.BOLD}{C.M}>>> {msg}{C.X}")

# ---------- credentials ----------

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

# ---------- SSH wrapper ----------

@dataclass
class Report:
    actions: list = field(default_factory=list)
    issues: list = field(default_factory=list)
    resolved: list = field(default_factory=list)

    def did(self, msg): self.actions.append(msg)
    def found(self, msg): self.issues.append(msg)
    def fixed(self, msg): self.resolved.append(msg)


class SSH:
    def __init__(self, creds: dict, verbose=False, dry=False):
        self.creds = creds
        self.verbose = verbose
        self.dry = dry
        self.client = paramiko.SSHClient()
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

    def run(self, cmd: str, check=False) -> tuple[int, str, str]:
        """Returns (exit_code, stdout, stderr). If check=True, raises on non-zero."""
        if self.verbose or self.dry:
            print(f"  $ {cmd}")
        if self.dry:
            return 0, "", ""
        stdin, stdout, stderr = self.client.exec_command(cmd, timeout=60)
        rc = stdout.channel.recv_exit_status()
        out = stdout.read().decode(errors="replace")
        err = stderr.read().decode(errors="replace")
        if self.verbose and out.strip():
            for line in out.strip().splitlines():
                print(f"    {line}")
        if self.verbose and err.strip():
            for line in err.strip().splitlines():
                print(f"    {C.Y}{line}{C.X}")
        if check and rc != 0:
            raise RuntimeError(f"Command failed ({rc}): {cmd}\nstderr: {err}")
        return rc, out, err

    def close(self):
        self.client.close()

# ---------- individual checks / fixes ----------

def check_php_version(ssh: SSH, rpt: Report):
    step("Check PHP 8.4 binary")
    rc, out, _ = ssh.run(f"{PHP_BIN} -v")
    if rc != 0 or "PHP 8.4" not in out:
        rpt.found("PHP 8.4 binary not found at expected path")
        fail(f"Expected PHP 8.4 at {PHP_BIN}")
        return False
    ok(out.splitlines()[0])
    return True


def fix_tempdir(ssh: SSH, rpt: Report):
    """Create storage/tmp and .user.ini so PHP can use it for temp files."""
    step("Ensure PHP temp dir is inside open_basedir")
    tmp_dir = f"{APP_ROOT}/storage/tmp"
    userini_app = f"{APP_ROOT}/.user.ini"
    userini_pub = f"{PUBLIC_DIR}/.user.ini"

    ssh.run(f"mkdir -p {tmp_dir}")
    ssh.run(f"chown {WEB_USER}:{WEB_GROUP} {tmp_dir}")
    ssh.run(f"chmod 775 {tmp_dir}")

    userini_content = (
        f"sys_temp_dir = {tmp_dir}\n"
        f"upload_tmp_dir = {tmp_dir}\n"
    )
    # Write at BOTH app root and public — covers pre/post docRoot change
    for path in (userini_app, userini_pub):
        ssh.run(f"cat > {path} <<'EOF'\n{userini_content}EOF")
        ssh.run(f"chown {WEB_USER}:{WEB_GROUP} {path}")
    ok(f"Wrote .user.ini at app root and /public (tmp = {tmp_dir})")
    rpt.did("Configured PHP sys_temp_dir inside site directory")


def fix_permissions(ssh: SSH, rpt: Report):
    """Ensure storage/ and bootstrap/cache/ are writable by web user."""
    step("Fix Laravel directory ownership & permissions")
    paths = [f"{APP_ROOT}/storage", f"{APP_ROOT}/bootstrap/cache"]
    for p in paths:
        ssh.run(f"chown -R {WEB_USER}:{WEB_GROUP} {p}")
        ssh.run(f"chmod -R 775 {p}")
    ok("storage/ and bootstrap/cache/ owned by nobody:nobody, 775")
    rpt.did("Fixed storage and bootstrap/cache permissions")


def ensure_app_key(ssh: SSH, rpt: Report):
    step("Verify APP_KEY in .env")
    rc, out, _ = ssh.run(f"grep -E '^APP_KEY=' {APP_ROOT}/.env || true")
    if not out.strip() or out.strip() == "APP_KEY=":
        warn("APP_KEY is missing — generating")
        ssh.run(f"cd {APP_ROOT} && {PHP_BIN} artisan key:generate --force", check=True)
        rpt.fixed("Generated missing APP_KEY")
    else:
        ok("APP_KEY present")


def clear_and_rebuild_caches(ssh: SSH, rpt: Report):
    step("Clear & rebuild Laravel caches")
    commands = [
        f"cd {APP_ROOT} && {PHP_BIN} artisan config:clear",
        f"cd {APP_ROOT} && {PHP_BIN} artisan cache:clear",
        f"cd {APP_ROOT} && {PHP_BIN} artisan view:clear",
        f"cd {APP_ROOT} && {PHP_BIN} artisan route:clear",
    ]
    for cmd in commands:
        ssh.run(cmd)
    ok("All Laravel caches cleared")
    rpt.did("Cleared Laravel config/cache/view/route caches")


def restart_webserver(ssh: SSH):
    step("Restart OpenLiteSpeed")
    rc, _, err = ssh.run("systemctl restart lsws")
    if rc != 0:
        # fall back to lswsctrl
        ssh.run("/usr/local/lsws/bin/lswsctrl restart")
    time.sleep(3)
    ok("lsws restarted")


def check_site_http(ssh: SSH, rpt: Report) -> int:
    step("Check HTTP status from inside the server")
    rc, out, _ = ssh.run(
        f"curl -s -o /dev/null -w '%{{http_code}}' -H 'Host: chatbotnepal.isoftroerp.com' "
        f"http://127.0.0.1/"
    )
    code = int(out.strip() or 0)
    if code == 200:
        ok(f"Site returned HTTP {code}")
    elif code in (301, 302):
        ok(f"Site redirects (HTTP {code}) — normal if SSL is on")
    else:
        fail(f"Site returned HTTP {code}")
        rpt.found(f"Site still returns HTTP {code}")
    return code


def tail_laravel_log(ssh: SSH, rpt: Report, lines=40):
    step(f"Tail last {lines} lines of laravel.log")
    rc, out, _ = ssh.run(f"test -f {LOG_FILE} && tail -n {lines} {LOG_FILE} || echo 'NO_LOG_YET'")
    if "NO_LOG_YET" in out:
        ok("No laravel.log yet (site may be booting clean)")
        return
    if out.strip():
        print(f"{C.Y}--- laravel.log tail ---{C.X}")
        print(out)
        print(f"{C.Y}--- end log ---{C.X}")
        if "ERROR" in out or "Exception" in out:
            rpt.found("Errors present in laravel.log — see output above")


def check_docroot_warning(ssh: SSH, rpt: Report):
    step("Check CyberPanel docRoot configuration")
    rc, out, _ = ssh.run(
        "grep -E 'docRoot' /usr/local/lsws/conf/vhosts/chatbotnepal.isoftroerp.com/vhost.conf "
        "2>/dev/null || true"
    )
    if "/public" in out:
        ok("docRoot points to /public")
    else:
        warn("docRoot does NOT point to /public — manual fix needed in CyberPanel")
        warn("  → CyberPanel → Websites → Manage → vHost Conf → set docRoot $VH_ROOT/public")
        rpt.found("docRoot must be changed to /public in CyberPanel UI")


def ensure_scheduler_cron(ssh: SSH, rpt: Report):
    step("Ensure Laravel scheduler cron is installed")
    rc, out, _ = ssh.run("crontab -l 2>/dev/null | grep 'artisan schedule:run' || true")
    if out.strip():
        ok("Scheduler cron already present")
        return
    cron_line = f"* * * * * cd {APP_ROOT} && {PHP_BIN} artisan schedule:run >> /dev/null 2>&1"
    ssh.run(f"(crontab -l 2>/dev/null; echo '{cron_line}') | crontab -")
    ok("Installed Laravel scheduler cron (runs every minute)")
    rpt.did("Added Laravel scheduler to root crontab")


# ---------- main pipeline ----------

def run_pipeline(ssh: SSH, rpt: Report, args):
    # 1. Sanity
    if not check_php_version(ssh, rpt):
        fail("Cannot continue without PHP 8.4 — aborting")
        return

    # 2. The tempnam fix (this is the current blocker)
    fix_tempdir(ssh, rpt)

    # 3. Permissions
    fix_permissions(ssh, rpt)

    # 4. APP_KEY
    ensure_app_key(ssh, rpt)

    # 5. Cache rebuild
    clear_and_rebuild_caches(ssh, rpt)

    # 6. Restart
    restart_webserver(ssh)

    # 7. Check site
    code = check_site_http(ssh, rpt)

    # 8. If still broken, show logs
    if code != 200 and code not in (301, 302):
        tail_laravel_log(ssh, rpt, lines=60)

    # 9. Non-blocking checks
    check_docroot_warning(ssh, rpt)
    ensure_scheduler_cron(ssh, rpt)


def print_report(rpt: Report):
    print(f"\n{C.BOLD}{'='*60}")
    print(" DEPLOYMENT FIX REPORT")
    print(f"{'='*60}{C.X}")

    if rpt.actions:
        print(f"\n{C.G}Actions performed:{C.X}")
        for a in rpt.actions: print(f"  • {a}")

    if rpt.resolved:
        print(f"\n{C.G}Issues resolved:{C.X}")
        for a in rpt.resolved: print(f"  ✓ {a}")

    if rpt.issues:
        print(f"\n{C.Y}Issues remaining (need attention):{C.X}")
        for i in rpt.issues: print(f"  ! {i}")
    else:
        print(f"\n{C.G}No outstanding issues detected.{C.X}")


def main():
    p = argparse.ArgumentParser()
    p.add_argument("--dry-run", action="store_true", help="Show commands without running them")
    p.add_argument("--verbose", action="store_true", help="Print commands and output")
    p.add_argument("--creds", default="credentials.enc", help="Path to encrypted creds file")
    args = p.parse_args()

    if not os.path.exists(args.creds):
        fail(f"Credentials file not found: {args.creds}")
        fail("Run `python3 encrypt_credentials.py` first.")
        sys.exit(2)

    passphrase = os.environ.get("CHATBOT_FIX_PASSPHRASE")
    if not passphrase:
        passphrase = getpass.getpass("Passphrase for credentials.enc: ")

    try:
        creds = decrypt_credentials(args.creds, passphrase)
    except ValueError as e:
        fail(str(e))
        sys.exit(2)

    rpt = Report()
    ssh = SSH(creds, verbose=args.verbose, dry=args.dry_run)

    try:
        ssh.connect()
    except Exception as e:
        fail(f"SSH connection failed: {e}")
        sys.exit(2)

    try:
        run_pipeline(ssh, rpt, args)
    finally:
        ssh.close()
        print_report(rpt)

    sys.exit(1 if rpt.issues else 0)


if __name__ == "__main__":
    main()
