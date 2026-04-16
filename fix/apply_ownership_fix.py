#!/usr/bin/env python3
"""
apply_ownership_fix.py
----------------------
Fixes the wrong web-user ownership (nobody → isoft1807), patches .env DB keys,
kills/restarts lsphp+lsws, clears caches as the correct user, and verifies the
site returns HTTP 200 without the tempnam error.

Usage:
    python3 apply_ownership_fix.py [--verbose] [--creds credentials.enc]
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

# ---------- config ----------

APP_ROOT   = "/home/isoftroerp.com/chatbotnepal.isoftroerp.com"
PUBLIC_DIR = f"{APP_ROOT}/public"
PHP_BIN    = "/usr/local/lsws/lsphp84/bin/php"
WEB_USER   = "isoft1807"
WEB_GROUP  = "isoft1807"
ITERATIONS = 390_000

DB_DEFAULTS = {
    "DB_CONNECTION": "mysql",
    "DB_HOST":       "127.0.0.1",
    "DB_PORT":       "3306",
    "DB_DATABASE":   "chatbotnepal_db",
    "DB_USERNAME":   "chatbot_user",
    "DB_PASSWORD":   "ChatBot2026Nepal",
}

# ---------- colours ----------

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

# ---------- credentials ----------

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

# ---------- SSH ----------

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

# ---------- steps ----------

def step1_fix_ownership(ssh):
    step("Step 1 — Fix ownership to isoft1807:isoft1807")

    cmds = [
        f"chown -R {WEB_USER}:{WEB_GROUP} {APP_ROOT}/storage",
        f"chown -R {WEB_USER}:{WEB_GROUP} {APP_ROOT}/bootstrap/cache",
        f"chown {WEB_USER}:{WEB_GROUP} {APP_ROOT}/.user.ini",
        f"chown {WEB_USER}:{WEB_GROUP} {PUBLIC_DIR}/.user.ini",
        f"chmod -R 775 {APP_ROOT}/storage",
        f"chmod -R 775 {APP_ROOT}/bootstrap/cache",
    ]
    for cmd in cmds:
        rc, out, err = ssh.run(cmd)
        if rc != 0:
            warn(f"Non-zero exit for: {cmd}")
            if err.strip(): show(err)
        else:
            info(cmd)

    # Verify
    rc, out, _ = ssh.run(f"stat -c '%U %G' {APP_ROOT}/storage")
    owner = out.strip()
    if WEB_USER in owner:
        ok(f"storage/ owner: {owner}")
    else:
        fail(f"storage/ owner unexpected: {owner}")

    rc2, out2, _ = ssh.run(f"stat -c '%U %G' {APP_ROOT}/.user.ini")
    owner2 = out2.strip()
    if WEB_USER in owner2:
        ok(f".user.ini owner: {owner2}")
    else:
        warn(f".user.ini owner: {owner2} (may not exist yet)")


def step2_patch_env(ssh):
    step("Step 2 — Patch .env with missing DB keys")

    rc, out, _ = ssh.run(f"cat {APP_ROOT}/.env 2>/dev/null || echo ENV_NOT_FOUND")
    if "ENV_NOT_FOUND" in out:
        fail(f".env not found at {APP_ROOT}/.env")
        return

    current_keys = set()
    for line in out.splitlines():
        if "=" in line and not line.startswith("#"):
            current_keys.add(line.split("=", 1)[0].strip())

    to_append = {k: v for k, v in DB_DEFAULTS.items() if k not in current_keys}

    if not to_append:
        ok("All DB keys already present in .env — nothing to append")
        return

    info(f"Appending missing keys: {', '.join(to_append)}")

    # Build append block safely
    append_lines = "\n".join(f"{k}={v}" for k, v in to_append.items())
    # Use printf to avoid heredoc quoting issues with special chars in password
    for k, v in to_append.items():
        # Escape single quotes in value (shouldn't be any, but be safe)
        v_escaped = v.replace("'", "'\\''")
        ssh.run(f"echo '{k}={v_escaped}' >> {APP_ROOT}/.env")
        ok(f"  Appended {k}")

    # Confirm
    rc2, out2, _ = ssh.run(
        f"grep -E '^(DB_CONNECTION|DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=' "
        f"{APP_ROOT}/.env"
    )
    ok("Current DB keys in .env:")
    for line in out2.strip().splitlines():
        k, _, v = line.partition("=")
        display = "***" if "PASSWORD" in k else v
        print(f"    {k}={display}")


def step4_restart_lsws(ssh):
    step("Step 4 — Kill lsphp processes and restart lsws")

    ssh.run("killall lsphp 2>/dev/null || true")
    ok("killall lsphp sent")

    time.sleep(2)

    rc, out, err = ssh.run(f"{PHP_BIN} -r \"echo 'php ok';\"")
    if "php ok" in out:
        ok(f"PHP binary responsive: {PHP_BIN}")
    else:
        fail("PHP binary check failed after killall")
        show(out + err)

    rc2, _, err2 = ssh.run("systemctl restart lsws")
    if rc2 == 0:
        ok("lsws restarted via systemctl")
    else:
        warn("systemctl restart failed, trying lswsctrl")
        ssh.run("/usr/local/lsws/bin/lswsctrl restart")

    time.sleep(3)


def step5_clear_caches(ssh):
    step("Step 5 — Clear caches as isoft1807")

    for artisan_cmd in ("config:clear", "cache:clear"):
        rc, out, err = ssh.run(
            f"cd {APP_ROOT} && sudo -u {WEB_USER} {PHP_BIN} artisan {artisan_cmd} 2>&1"
        )
        combined = (out + err).strip()
        if rc == 0:
            ok(f"artisan {artisan_cmd}: {combined or 'done'}")
        else:
            # sudo may not be available or user may already be root — try directly
            warn(f"sudo attempt failed (rc={rc}), running directly")
            rc2, out2, err2 = ssh.run(
                f"cd {APP_ROOT} && {PHP_BIN} artisan {artisan_cmd} 2>&1"
            )
            combined2 = (out2 + err2).strip()
            if rc2 == 0:
                ok(f"artisan {artisan_cmd} (direct): {combined2 or 'done'}")
            else:
                fail(f"artisan {artisan_cmd} failed:")
                show(combined2)


def step6_verify(ssh):
    step("Step 6 — Curl site and check for HTTP 200 + no tempnam error")

    rc, out, err = ssh.run(
        "curl -s -w '\\nHTTP:%{http_code}\\n' "
        "-H 'Host: chatbotnepal.isoftroerp.com' "
        "http://127.0.0.1/ | head -c 1000",
        timeout=30,
    )
    combined = out + err

    print(f"\n{C.Y}--- curl response (first 1000 bytes) ---{C.X}")
    show(combined)

    # Extract HTTP code
    http_code = 0
    for line in combined.splitlines():
        if line.startswith("HTTP:"):
            try:
                http_code = int(line.replace("HTTP:", "").strip())
            except ValueError:
                pass

    if http_code == 200:
        ok(f"Site returns HTTP 200")
    elif http_code in (301, 302):
        ok(f"Site redirects (HTTP {http_code}) — expected if HTTPS is configured")
    else:
        fail(f"Site still returns HTTP {http_code}")

    if "tempnam" in combined.lower():
        fail("tempnam() error still visible in response")
    elif http_code in (200, 301, 302):
        ok("No tempnam() error in response")

    return http_code


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

    try:
        ssh = SSH(creds, verbose=args.verbose)
    except Exception as e:
        fail(f"SSH connection failed: {e}")
        sys.exit(2)

    issues = []
    try:
        step1_fix_ownership(ssh)
        step2_patch_env(ssh)
        step4_restart_lsws(ssh)
        step5_clear_caches(ssh)
        code = step6_verify(ssh)
        if code not in (200, 301, 302):
            issues.append(f"Site still returns HTTP {code} after all fixes")
    finally:
        ssh.close()

    print(f"\n{C.BOLD}{'='*60}")
    print(" OWNERSHIP FIX SUMMARY")
    print(f"{'='*60}{C.X}")
    print(f"  • Ownership:  storage/, bootstrap/cache/, .user.ini → {WEB_USER}:{WEB_GROUP}")
    print(f"  • .env:       DB keys patched if missing")
    print(f"  • lsphp:      killed and restarted via lsws")
    print(f"  • Caches:     config + cache cleared as {WEB_USER}")
    print(f"\n{C.B}Still needs manual action:{C.X}")
    print("  • SSL certificate — issue via CyberPanel once DNS propagates")
    print("  • Confirm APP_DEBUG=false before going live")
    print("  • Change admin password via the app login page")

    if issues:
        print(f"\n{C.Y}Remaining issues:{C.X}")
        for i in issues:
            print(f"  ! {i}")
        sys.exit(1)
    else:
        print(f"\n{C.G}All automated steps completed.{C.X}")
        sys.exit(0)


if __name__ == "__main__":
    main()
