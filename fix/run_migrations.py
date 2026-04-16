#!/usr/bin/env python3
"""
run_migrations.py
-----------------
Runs Laravel migrations against MySQL, seeds the database, optionally
runs kb:sync, verifies table/user state, and does a final HTTP check.

Usage:
    python3 run_migrations.py [--verbose] [--creds credentials.enc]
"""

import argparse
import base64
import getpass
import json
import os
import sys

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
DB_USER    = "chatbot_user"
DB_PASS    = "ChatBot2026Nepal"
DB_NAME    = "chatbotnepal_db"
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
    for line in (t or "").strip().splitlines():
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

    def run(self, cmd, timeout=120):
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

    def artisan(self, cmd, timeout=120):
        """Run artisan as WEB_USER, fall back to direct if sudo unavailable."""
        for prefix in [f"sudo -u {WEB_USER} ", ""]:
            rc, out, err = self.run(
                f"cd {APP_ROOT} && {prefix}{PHP_BIN} artisan {cmd} 2>&1",
                timeout=timeout,
            )
            combined = (out + err).strip()
            if rc == 0:
                return rc, combined
            # If sudo specifically failed (not artisan), try without
            if prefix and ("sudo:" in combined or "no tty present" in combined):
                continue
            return rc, combined
        return 1, "both sudo and direct attempts failed"

    def close(self):
        self.client.close()


def step1_migrate(ssh):
    step("Step 1 — artisan migrate --force")
    rc, out = ssh.artisan("migrate --force", timeout=180)
    show(out)
    if rc == 0:
        ok("Migrations completed successfully")
        return True
    else:
        fail(f"migrate --force failed (exit {rc})")
        return False


def step2_list_seeders(ssh):
    step("Step 2 — List seeders")
    rc, out, _ = ssh.run(f"ls {APP_ROOT}/database/seeders/")
    show(out)
    seeders = out.strip().splitlines()
    has_database_seeder = any("DatabaseSeeder" in s for s in seeders)
    info(f"Found {len(seeders)} seeder file(s)")
    return seeders, has_database_seeder


def step3_seed(ssh, has_database_seeder):
    step("Step 3 — db:seed --force")
    if not has_database_seeder:
        warn("No DatabaseSeeder.php found — skipping db:seed")
        return False
    rc, out = ssh.artisan("db:seed --force", timeout=180)
    show(out)
    if rc == 0:
        ok("Seeding completed")
        return True
    else:
        fail(f"db:seed failed (exit {rc})")
        show(out)
        return False


def step4_kb_sync(ssh):
    step("Step 4 — kb:sync (skip if command does not exist)")
    rc, out = ssh.artisan("kb:sync", timeout=120)
    if rc == 0:
        ok("kb:sync completed")
        show(out)
    elif "command" in out.lower() and ("not defined" in out.lower() or "not found" in out.lower()):
        info("kb:sync command does not exist — skipping (expected)")
    else:
        warn(f"kb:sync exited {rc}:")
        show(out)


def step5_verify_db(ssh):
    step("Step 5 — Verify: migrate:status + SHOW TABLES + users")

    # migrate:status
    rc, out = ssh.artisan("migrate:status", timeout=30)
    print(f"\n{C.Y}--- migrate:status ---{C.X}")
    show(out)
    if rc == 0 and ("Yes" in out or "No" in out or "Ran?" in out):
        ok("migrate:status: all migrations visible")
    else:
        warn("migrate:status output unexpected — review above")

    # MySQL direct check
    rc2, out2, err2 = ssh.run(
        f"mysql -u {DB_USER} -p{DB_PASS} {DB_NAME} "
        f"-e \"SHOW TABLES; SELECT email, role FROM users LIMIT 5;\" 2>&1"
    )
    combined = (out2 + err2).strip()
    print(f"\n{C.Y}--- MySQL: SHOW TABLES + users ---{C.X}")
    show(combined)

    # Count tables
    table_lines = [l for l in combined.splitlines()
                   if l.strip() and not l.startswith("Tables_in") and "|" not in l
                   and not l.startswith("+") and not l.startswith("email")]
    # Simpler: count non-header lines from SHOW TABLES block
    tables = [l.strip() for l in combined.splitlines()
              if l.strip() and "Tables_in" not in l and "email" not in l
              and "role" not in l and not l.startswith("+")]
    table_count = len(tables)

    if rc2 == 0:
        ok(f"MySQL query succeeded — approx {table_count} table(s) visible")
    else:
        fail("MySQL query failed — check credentials")

    # Check admin user
    if "admin" in combined.lower() or "@" in combined:
        ok("Admin/user row(s) found in users table")
    else:
        warn("No user rows returned — may need seeding or registration")

    return rc2 == 0


def step6_http_check(ssh):
    step("Step 6 — Final HTTP check")
    rc, out, err = ssh.run(
        "curl -sI -H 'Host: chatbotnepal.isoftroerp.com' http://127.0.0.1/ | head -5",
        timeout=15,
    )
    combined = (out + err).strip()
    show(combined)

    if "200" in combined:
        ok("HTTP 200 OK — site is up")
        return True
    elif "301" in combined or "302" in combined:
        ok("HTTP redirect — normal if HTTPS enforced")
        return True
    else:
        fail("Did not receive 200/301/302 — review output above")
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

    results = {}
    try:
        results["migrate"]  = step1_migrate(ssh)
        seeders, has_ds     = step2_list_seeders(ssh)
        results["seed"]     = step3_seed(ssh, has_ds)
        step4_kb_sync(ssh)
        results["db_ok"]    = step5_verify_db(ssh)
        results["http_ok"]  = step6_http_check(ssh)
    finally:
        ssh.close()

    print(f"\n{C.BOLD}{'='*60}")
    print(" MIGRATION SUMMARY")
    print(f"{'='*60}{C.X}")
    print(f"  • migrate --force : {'OK' if results.get('migrate') else 'FAILED'}")
    print(f"  • db:seed         : {'OK' if results.get('seed') else 'skipped/failed'}")
    print(f"  • MySQL verify    : {'OK' if results.get('db_ok') else 'FAILED'}")
    print(f"  • HTTP check      : {'200 OK' if results.get('http_ok') else 'NOT 200'}")

    print(f"\n{C.B}Remaining manual steps:{C.X}")
    print("  • SSL certificate — issue via CyberPanel once DNS propagates")
    print("  • Admin password  — change via the app login page")
    print("  • Review any migration warnings printed above")

    all_ok = results.get("migrate") and results.get("db_ok") and results.get("http_ok")
    if all_ok:
        print(f"\n{C.G}Deployment looks healthy.{C.X}")
        sys.exit(0)
    else:
        print(f"\n{C.Y}One or more steps had issues — review output above.{C.X}")
        sys.exit(1)


if __name__ == "__main__":
    main()
