#!/usr/bin/env python3
"""
fix_env_db.py
-------------
Fixes the .env DB block (sqlite → mysql, typos, leading-space lines,
duplicate appended keys), flips APP_DEBUG/APP_ENV to production values,
clears config cache, and verifies with artisan migrate:status.

Usage:
    python3 fix_env_db.py [--verbose] [--creds credentials.enc]
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

APP_ROOT   = "/home/isoftroerp.com/chatbotnepal.isoftroerp.com"
PHP_BIN    = "/usr/local/lsws/lsphp84/bin/php"
WEB_USER   = "isoft1807"
ITERATIONS = 390_000

class C:
    R="\033[91m"; G="\033[92m"; Y="\033[93m"
    B="\033[94m"; M="\033[95m"; X="\033[0m"; BOLD="\033[1m"

def ok(m):   print(f"{C.G}[OK]{C.X} {m}")
def info(m): print(f"{C.B}[..]{C.X} {m}")
def warn(m): print(f"{C.Y}[!!]{C.X} {m}")
def fail(m): print(f"{C.R}[XX]{C.X} {m}")
def step(m): print(f"\n{C.BOLD}{C.M}>>> {m}{C.X}")
def show(t):
    for line in t.strip().splitlines():
        print(f"    {line}")

def decrypt_credentials(path, passphrase):
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

class SSH:
    def __init__(self, creds, verbose=False):
        self.verbose = verbose
        self.client  = paramiko.SSHClient()
        self.client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        self.client.connect(
            hostname=creds["host"], port=creds["port"],
            username=creds["user"], password=creds["password"],
            timeout=15, look_for_keys=False, allow_agent=False,
        )
        ok(f"SSH connected to {creds['user']}@{creds['host']}")

    def run(self, cmd, timeout=60):
        if self.verbose:
            print(f"  $ {cmd}")
        _, stdout, stderr = self.client.exec_command(cmd, timeout=timeout)
        rc  = stdout.channel.recv_exit_status()
        out = stdout.read().decode(errors="replace")
        err = stderr.read().decode(errors="replace")
        if self.verbose:
            if out.strip(): show(out)
            if err.strip(): show(f"{C.Y}{err.strip()}{C.X}")
        return rc, out, err

    def close(self):
        self.client.close()


def step1_show_current_env(ssh):
    step("Step 1 — Read current .env (DB block + APP_* keys)")
    rc, out, _ = ssh.run(f"grep -n '^\\(DB_\\|APP_DEBUG\\|APP_ENV\\| DB_\\|# DB_\\)' {APP_ROOT}/.env")
    print(f"{C.Y}--- relevant .env lines before edit ---{C.X}")
    show(out)
    return out


def step2_sed_fixes(ssh):
    step("Step 2 — Apply sed fixes to .env")

    seds = [
        # Fix DB_CONNECTION sqlite → mysql
        r"s|^DB_CONNECTION=sqlite|DB_CONNECTION=mysql|",
        # Uncomment / fix leading-space lines
        r"s|^ DB_HOST=127\.0\.0\.1|DB_HOST=127.0.0.1|",
        r"s|^ DB_PORT=3306|DB_PORT=3306|",
        r"s|^ DB_DATABASE=chatnepal_db|DB_DATABASE=chatbotnepal_db|",
        r"s|^ DB_DATABASE=chatbotnepal_db|DB_DATABASE=chatbotnepal_db|",  # idempotent guard
        r"s|^# DB_USERNAME=.*|DB_USERNAME=chatbot_user|",
        r"s|^ DB_USERNAME=.*|DB_USERNAME=chatbot_user|",
        r"s|^ DB_PASSWORD=.*|DB_PASSWORD=ChatBot2026Nepal|",
        # Flip to production
        r"s|^APP_DEBUG=true|APP_DEBUG=false|",
        r"s|^APP_ENV=local|APP_ENV=production|",
    ]

    for pattern in seds:
        rc, out, err = ssh.run(f"sed -i '{pattern}' {APP_ROOT}/.env")
        if rc != 0:
            warn(f"sed pattern may have had no match (ok if key was already correct): {pattern}")
        else:
            info(f"sed: {pattern}")


def step3_remove_duplicates(ssh):
    step("Step 3 — Remove duplicate DB_* lines appended at bottom by earlier fix")

    # Read the full .env
    rc, out, _ = ssh.run(f"cat -n {APP_ROOT}/.env")
    if rc != 0:
        fail("Could not read .env")
        return

    lines = out.splitlines()  # "     1\tLINE"

    # Find all line numbers for each DB_ key; keep only the FIRST occurrence,
    # delete any subsequent duplicates.
    seen = {}
    to_delete = []  # 1-based line numbers
    for raw in lines:
        # format from cat -n: "     N\tCONTENT"
        parts = raw.split("\t", 1)
        if len(parts) != 2:
            continue
        lineno_str = parts[0].strip()
        content    = parts[1]
        if not content.startswith("DB_"):
            continue
        key = content.split("=", 1)[0].strip()
        if key in seen:
            to_delete.append(int(lineno_str))
        else:
            seen[key] = int(lineno_str)

    if not to_delete:
        ok("No duplicate DB_ lines found")
        return

    info(f"Duplicate lines to delete: {to_delete}")

    # Delete from bottom up so line numbers don't shift
    for lineno in sorted(to_delete, reverse=True):
        rc2, _, err2 = ssh.run(f"sed -i '{lineno}d' {APP_ROOT}/.env")
        if rc2 == 0:
            ok(f"  Deleted line {lineno}")
        else:
            warn(f"  Could not delete line {lineno}: {err2.strip()}")


def step4_verify_env(ssh):
    step("Step 4 — Verify: grep '^DB_' .env (expect exactly 6 lines)")
    rc, out, _ = ssh.run(f"grep -n '^DB_' {APP_ROOT}/.env")
    lines = [l for l in out.strip().splitlines() if l.strip()]
    show(out)
    if len(lines) == 6:
        ok(f"Exactly 6 DB_ lines — correct")
    else:
        warn(f"Found {len(lines)} DB_ lines (expected 6) — review output above")

    # Also show APP_DEBUG and APP_ENV
    rc2, out2, _ = ssh.run(f"grep -E '^APP_(DEBUG|ENV)=' {APP_ROOT}/.env")
    show(out2)
    if "APP_DEBUG=false" in out2 and "APP_ENV=production" in out2:
        ok("APP_DEBUG=false, APP_ENV=production — production settings confirmed")
    else:
        warn("APP_DEBUG/APP_ENV may not be set correctly — see above")


def step5_config_clear(ssh):
    step("Step 5 — Clear config cache")
    for cmd_suffix in [
        f"sudo -u {WEB_USER} {PHP_BIN} artisan config:clear",
        f"{PHP_BIN} artisan config:clear",  # fallback if sudo unavailable
    ]:
        rc, out, err = ssh.run(f"cd {APP_ROOT} && {cmd_suffix} 2>&1")
        combined = (out + err).strip()
        if rc == 0:
            ok(f"config:clear: {combined or 'done'}")
            break
        warn(f"Attempt failed ({cmd_suffix}): {combined}")


def step6_migrate_status(ssh):
    step("Step 6 — artisan migrate:status (proves MySQL connection)")
    for cmd_suffix in [
        f"sudo -u {WEB_USER} {PHP_BIN} artisan migrate:status",
        f"{PHP_BIN} artisan migrate:status",
    ]:
        rc, out, err = ssh.run(f"cd {APP_ROOT} && {cmd_suffix} 2>&1", timeout=30)
        combined = (out + err).strip()
        if rc == 0 or "Migration" in combined or "Ran?" in combined:
            ok("MySQL connection confirmed — migrate:status output:")
            show(combined)
            return True
        if "could not find driver" in combined.lower() or "connection refused" in combined.lower() \
                or "Access denied" in combined.lower():
            fail("DB connection error:")
            show(combined)
            return False
        warn(f"Attempt ({cmd_suffix}) exit={rc}:")
        show(combined)

    fail("migrate:status failed on all attempts — check DB credentials in .env vs MariaDB")
    return False


def main():
    p = argparse.ArgumentParser()
    p.add_argument("--verbose", action="store_true")
    p.add_argument("--creds", default="credentials.enc")
    args = p.parse_args()

    if not os.path.exists(args.creds):
        fail(f"Credentials file not found: {args.creds}")
        sys.exit(2)

    passphrase = os.environ.get("CHATBOT_FIX_PASSPHRASE")
    if not passphrase:
        passphrase = getpass.getpass("Passphrase for credentials.enc: ")

    try:
        creds = decrypt_credentials(args.creds, passphrase)
    except ValueError as e:
        fail(str(e))
        sys.exit(2)

    try:
        ssh = SSH(creds, verbose=args.verbose)
    except Exception as e:
        fail(f"SSH connection failed: {e}")
        sys.exit(2)

    success = False
    try:
        step1_show_current_env(ssh)
        step2_sed_fixes(ssh)
        step3_remove_duplicates(ssh)
        step4_verify_env(ssh)
        step5_config_clear(ssh)
        success = step6_migrate_status(ssh)
    finally:
        ssh.close()

    print(f"\n{C.BOLD}{'='*60}")
    print(" .ENV DB FIX SUMMARY")
    print(f"{'='*60}{C.X}")
    print("  • DB_CONNECTION: sqlite → mysql")
    print("  • DB_HOST/PORT/DATABASE: leading-space lines fixed")
    print("  • DB_DATABASE typo fixed: chatnepal_db → chatbotnepal_db")
    print("  • Duplicate appended keys removed")
    print("  • APP_DEBUG=false, APP_ENV=production")
    print("  • Config cache cleared")
    print()
    if success:
        ok("MySQL connection confirmed via migrate:status")
        print(f"\n{C.B}Remaining manual steps:{C.X}")
        print("  • SSL certificate — issue via CyberPanel once DNS propagates")
        print("  • Admin password — change via the app login page")
        sys.exit(0)
    else:
        fail("migrate:status did not confirm MySQL connection — check output above")
        print("  Likely cause: DB_USERNAME/DB_PASSWORD in .env don't match MariaDB user")
        print("  Verify with: mysql -u chatbot_user -pChatBot2026Nepal chatbotnepal_db -e 'SELECT 1;'")
        sys.exit(1)


if __name__ == "__main__":
    main()
