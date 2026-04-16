#!/usr/bin/env python3
"""
diagnose_chatbotnepal.py
------------------------
Deep-diagnosis script for ChatBot Nepal HTTP 500 where Laravel never
boots far enough to write laravel.log.

Uses the same credentials.enc / paramiko approach as fix_chatbotnepal.py.

Usage:
    python3 diagnose_chatbotnepal.py [--verbose] [--creds credentials.enc]
"""

import argparse
import base64
import getpass
import json
import os
import sys
import time

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
LSWS_ERROR_LOG = "/usr/local/lsws/logs/error.log"
LSWS_STDERR_LOG = "/usr/local/lsws/logs/stderr.log"
LARAVEL_LOG = f"{APP_ROOT}/storage/logs/laravel.log"
ITERATIONS = 390_000

REQUIRED_ENV_KEYS = [
    "APP_NAME", "APP_ENV", "APP_KEY", "APP_URL", "APP_DEBUG",
    "DB_CONNECTION", "DB_HOST", "DB_PORT", "DB_DATABASE",
    "DB_USERNAME", "DB_PASSWORD",
]

# ---------- colors ----------

class C:
    R = "\033[91m"; G = "\033[92m"; Y = "\033[93m"
    B = "\033[94m"; M = "\033[95m"; X = "\033[0m"
    BOLD = "\033[1m"

def ok(msg):   print(f"{C.G}[OK]{C.X} {msg}")
def info(msg): print(f"{C.B}[..]{C.X} {msg}")
def warn(msg): print(f"{C.Y}[!!]{C.X} {msg}")
def fail(msg): print(f"{C.R}[XX]{C.X} {msg}")
def step(msg): print(f"\n{C.BOLD}{C.M}>>> {msg}{C.X}")
def show(text):
    for line in text.strip().splitlines():
        print(f"    {line}")

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

class SSH:
    def __init__(self, creds: dict, verbose=False):
        self.creds = creds
        self.verbose = verbose
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

    def run(self, cmd: str, timeout=60) -> tuple[int, str, str]:
        if self.verbose:
            print(f"  $ {cmd}")
        stdin, stdout, stderr = self.client.exec_command(cmd, timeout=timeout)
        rc = stdout.channel.recv_exit_status()
        out = stdout.read().decode(errors="replace")
        err = stderr.read().decode(errors="replace")
        return rc, out, err

    def close(self):
        self.client.close()

# ---------- diagnostic steps ----------

findings = []

def record(msg):
    findings.append(msg)

def step1_lsws_logs(ssh: SSH):
    step("1. OpenLiteSpeed error logs (last 80 lines each)")
    for log in (LSWS_ERROR_LOG, LSWS_STDERR_LOG):
        rc, out, _ = ssh.run(f"test -f {log} && tail -n 80 {log} || echo 'FILE_NOT_FOUND'")
        print(f"\n{C.Y}--- {log} ---{C.X}")
        if "FILE_NOT_FOUND" in out:
            print("    (file does not exist)")
        else:
            show(out)
            if any(k in out for k in ("PHP Fatal", "PHP Parse", "PHP Warning", "chatbotnepal", "lsphp")):
                record(f"Errors found in {log} — see output above")


def step2_env_check(ssh: SSH):
    step("2. Check .env completeness")
    keys_pattern = "|".join(f"^{k}=" for k in REQUIRED_ENV_KEYS)
    rc, out, _ = ssh.run(f"grep -E '({keys_pattern})' {APP_ROOT}/.env 2>/dev/null || echo 'ENV_NOT_FOUND'")

    if "ENV_NOT_FOUND" in out:
        fail(f".env not found at {APP_ROOT}/.env")
        record(".env file is missing")
        return {}

    present = {}
    for line in out.strip().splitlines():
        if "=" in line:
            k, _, v = line.partition("=")
            present[k.strip()] = v.strip()

    missing = [k for k in REQUIRED_ENV_KEYS if k not in present]
    empty   = [k for k, v in present.items() if not v]

    for k, v in present.items():
        # Mask password values in display
        display = "***" if "PASSWORD" in k or "KEY" in k else v
        print(f"    {k}={display}")

    if missing:
        fail(f"Missing .env keys: {', '.join(missing)}")
        record(f".env is missing keys: {', '.join(missing)}")
    if empty:
        warn(f"Empty .env keys: {', '.join(empty)}")
        record(f".env keys are empty: {', '.join(empty)}")
    if not missing and not empty:
        ok("All required .env keys present and non-empty")

    return present


def step3_db_connection(ssh: SSH, env: dict):
    step("3. Test database connection")
    db_host = env.get("DB_HOST", "127.0.0.1")
    db_user = env.get("DB_USERNAME", "")
    db_pass = env.get("DB_PASSWORD", "")
    db_name = env.get("DB_DATABASE", "")

    if not all([db_user, db_pass, db_name]):
        warn("DB credentials incomplete in .env — skipping connection test")
        return

    rc, out, err = ssh.run(
        f"mysql -h {db_host} -u {db_user} -p{db_pass} {db_name} "
        f"-e 'SELECT 1 AS ok;' 2>&1"
    )
    if rc == 0 and "ok" in out.lower():
        ok(f"MySQL connection to {db_name}@{db_host} succeeded")
    else:
        fail(f"MySQL connection failed (exit {rc})")
        show(out + err)
        record(f"DB connection failed: check DB_HOST/DB_USERNAME/DB_PASSWORD in .env")


def step4_vendor_check(ssh: SSH):
    step("4. Verify composer vendor directory")
    rc, out, err = ssh.run(f"ls {APP_ROOT}/vendor/autoload.php 2>&1")
    if rc != 0:
        fail("vendor/autoload.php is missing — composer install needed")
        record("vendor/autoload.php missing — run: composer install --no-dev")
        return False

    rc2, out2, err2 = ssh.run(
        f"cd {APP_ROOT} && {PHP_BIN} -r \"require 'vendor/autoload.php'; echo 'autoload OK';\" 2>&1"
    )
    if "autoload OK" in out2:
        ok("vendor/autoload.php loads cleanly")
        return True
    else:
        fail("vendor/autoload.php exists but throws errors:")
        show(out2 + err2)
        record("vendor/autoload.php present but fails to load")
        return False


def step5_artisan_boot(ssh: SSH):
    step("5. Boot Laravel via artisan (captures PHP fatal)")
    rc, out, err = ssh.run(
        f"cd {APP_ROOT} && {PHP_BIN} artisan --version 2>&1"
    )
    combined = out + err
    if "Laravel Framework" in combined:
        ok(combined.strip().splitlines()[0])
    else:
        fail("artisan --version failed:")
        show(combined)
        record("artisan --version failed — PHP fatal before Laravel boots")
        return False

    # Try artisan about for deeper config check
    rc2, out2, err2 = ssh.run(
        f"cd {APP_ROOT} && {PHP_BIN} artisan about 2>&1", timeout=30
    )
    combined2 = out2 + err2
    if rc2 == 0:
        ok("artisan about succeeded — framework bootstraps OK from CLI")
        show(combined2[:2000])  # cap output
    else:
        fail("artisan about failed:")
        show(combined2)
        record("artisan about failed — check output above for the real error")
    return True


def step6_app_debug_and_curl(ssh: SSH):
    step("6. Enable APP_DEBUG temporarily and curl the site")
    # Read current value
    rc, out, _ = ssh.run(f"grep '^APP_DEBUG=' {APP_ROOT}/.env")
    original_debug = out.strip()

    was_false = "false" in original_debug.lower()

    if was_false:
        info("Setting APP_DEBUG=true temporarily for diagnostics")
        ssh.run(f"sed -i 's/^APP_DEBUG=false/APP_DEBUG=true/' {APP_ROOT}/.env")
        ssh.run(f"cd {APP_ROOT} && {PHP_BIN} artisan config:clear 2>/dev/null")

    # Tail all logs in background, curl, capture
    info("Capturing live logs + curl response...")
    rc, out, err = ssh.run(
        "("
        f"  tail -n 0 -f {LSWS_ERROR_LOG} {LSWS_STDERR_LOG} > /tmp/chatbot_trace.log 2>/dev/null & "
        "  TAIL_PID=$!; "
        "  sleep 1; "
        "  curl -s -i -H 'Host: chatbotnepal.isoftroerp.com' http://127.0.0.1/ -o /tmp/chatbot_response.txt 2>&1; "
        "  sleep 2; "
        "  kill $TAIL_PID 2>/dev/null; "
        "  echo '---TRACE---'; cat /tmp/chatbot_trace.log; "
        "  echo '---RESPONSE---'; cat /tmp/chatbot_response.txt; "
        ")",
        timeout=30,
    )
    combined = out + err

    if "---RESPONSE---" in combined:
        parts = combined.split("---RESPONSE---")
        trace = parts[0].replace("---TRACE---", "").strip()
        response = parts[1].strip()

        print(f"\n{C.Y}--- HTTP Response ---{C.X}")
        show(response[:3000])

        if trace:
            print(f"\n{C.Y}--- Live log trace ---{C.X}")
            show(trace[:3000])
            if any(k in trace for k in ("PHP Fatal", "PHP Parse", "Exception", "Error")):
                record("Fatal/exception found in live log trace — see output above")
    else:
        show(combined[:3000])

    # Restore APP_DEBUG
    if was_false:
        info("Restoring APP_DEBUG=false")
        ssh.run(f"sed -i 's/^APP_DEBUG=true/APP_DEBUG=false/' {APP_ROOT}/.env")
        ssh.run(f"cd {APP_ROOT} && {PHP_BIN} artisan config:clear 2>/dev/null")
        ok("APP_DEBUG restored to false")


def step7_final_http(ssh: SSH):
    step("7. Final HTTP check")
    rc, out, _ = ssh.run(
        "curl -s -o /dev/null -w '%{http_code}' "
        "-H 'Host: chatbotnepal.isoftroerp.com' http://127.0.0.1/"
    )
    code = int(out.strip() or 0)
    if code == 200:
        ok(f"Site now returns HTTP {code}")
    elif code in (301, 302):
        ok(f"Site redirects (HTTP {code}) — normal if HTTPS is configured")
    else:
        fail(f"Site still returns HTTP {code}")
        record(f"Site returns HTTP {code} after all diagnostics")
    return code


def print_summary():
    print(f"\n{C.BOLD}{'='*60}")
    print(" DIAGNOSTIC SUMMARY")
    print(f"{'='*60}{C.X}")
    if findings:
        print(f"\n{C.Y}Issues found:{C.X}")
        for i, f_ in enumerate(findings, 1):
            print(f"  {i}. {f_}")
    else:
        print(f"\n{C.G}No issues detected by automated checks.{C.X}")

    print(f"\n{C.B}Remaining manual steps (regardless of above):{C.X}")
    print("  • SSL certificate: issue via CyberPanel once DNS has propagated")
    print("  • APP_DEBUG=false: confirm it's false before going live")
    print("  • Admin password: change via the app login page")


# ---------- main ----------

def main():
    p = argparse.ArgumentParser()
    p.add_argument("--verbose", action="store_true")
    p.add_argument("--creds", default="credentials.enc")
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

    ssh = SSH(creds, verbose=args.verbose)
    try:
        ssh.connect()
    except Exception as e:
        fail(f"SSH connection failed: {e}")
        sys.exit(2)

    try:
        step1_lsws_logs(ssh)
        env = step2_env_check(ssh)
        step3_db_connection(ssh, env)
        step4_vendor_check(ssh)
        step5_artisan_boot(ssh)
        step6_app_debug_and_curl(ssh)
        step7_final_http(ssh)
    finally:
        ssh.close()
        print_summary()

    sys.exit(1 if findings else 0)


if __name__ == "__main__":
    main()
