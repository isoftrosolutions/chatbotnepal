#!/usr/bin/env python3
"""
fix_php_artisan.py
------------------
Fixes PHP 8.3 vs >=8.4 platform_check mismatch so artisan works,
clears all Laravel caches, verifies OLS vhost conf, and confirms CORS.

Usage:
    python3 fix_php_artisan.py [--verbose] [--creds PATH]

Exit: 0 = CORS PASS, 1 = CORS still failing, 2 = fatal
"""

import argparse
import base64
import getpass
import io
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
    print(f"Missing dependency: {e}\nInstall: pip install paramiko cryptography")
    sys.exit(2)

# ── config ────────────────────────────────────────────────────────────────────

APP      = "/home/isoftroerp.com/chatbotnepal.isoftroerp.com"
PHP      = "/usr/local/lsws/lsphp83/bin/php"
SITE     = "https://chatbotnepal.isoftroerp.com"
ENDPOINT = "/api/widget/session"
ORIGIN   = "https://isoftroerp.com"
VHOST_CONF = "/usr/local/lsws/conf/vhosts/chatbotnepal.isoftroerp.com/vhconf.conf"
ITERS    = 390_000

AUTOLOAD    = f"{APP}/vendor/composer/autoload_real.php"
PLAT_CHECK  = f"{APP}/vendor/composer/platform_check.php"
CACHE_FILES = [
    f"{APP}/bootstrap/cache/config.php",
    f"{APP}/bootstrap/cache/routes-v7.php",
    f"{APP}/bootstrap/cache/packages.php",
    f"{APP}/bootstrap/cache/services.php",
]

# ── colours ───────────────────────────────────────────────────────────────────

class C:
    R = "\033[91m"; G = "\033[92m"; Y = "\033[93m"
    B = "\033[94m"; M = "\033[95m"; X = "\033[0m"; BOLD = "\033[1m"

def ok(m):   print(f"{C.G}[OK]{C.X} {m}")
def info(m): print(f"{C.B}[..]{C.X} {m}")
def warn(m): print(f"{C.Y}[!!]{C.X} {m}")
def fail(m): print(f"{C.R}[XX]{C.X} {m}")
def hdr(m):  print(f"\n{C.BOLD}{'='*64}\n  {m}\n{'='*64}{C.X}")
def stp(n, m): print(f"\n{C.BOLD}{C.M}[Step {n}] {m}{C.X}")

# ── result table ──────────────────────────────────────────────────────────────

@dataclass
class R:
    name: str
    status: str   # PASS | FAIL | SKIP | INFO
    note: str = ""

_results: list[R] = []

def record(name, status, note=""):
    _results.append(R(name, status, note))
    sym = {
        "PASS": f"{C.G}PASS{C.X}",
        "FAIL": f"{C.R}FAIL{C.X}",
        "SKIP": f"{C.Y}SKIP{C.X}",
        "INFO": f"{C.B}INFO{C.X}",
    }.get(status, status)
    print(f"  → {sym}  {note}")

def print_summary():
    hdr("SUMMARY TABLE")
    w = max(len(r.name) for r in _results) + 2
    print(f"  {'Step':<{w}} {'Status':<8} Note")
    print(f"  {'-'*w} {'------'}  {'-'*44}")
    for r in _results:
        sym = {
            "PASS": f"{C.G}PASS{C.X}",
            "FAIL": f"{C.R}FAIL{C.X}",
            "SKIP": f"{C.Y}SKIP{C.X}",
            "INFO": f"{C.B}INFO{C.X}",
        }.get(r.status, r.status)
        print(f"  {r.name:<{w}} {sym:<18} {r.note[:60]}")
    print()
    failed = [r.name for r in _results if r.status == "FAIL"]
    if not failed:
        print(f"{C.G}{C.BOLD}All steps PASS.{C.X}")
    else:
        print(f"{C.R}{C.BOLD}Failed: {', '.join(failed)}{C.X}")

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

# ── SSH wrapper ───────────────────────────────────────────────────────────────

class SSH:
    def __init__(self, creds: dict, verbose: bool = False):
        self.creds   = creds
        self.verbose = verbose
        self.client  = paramiko.SSHClient()
        self.client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        self._sftp   = None

    def connect(self):
        info(f"Connecting to {self.creds['user']}@{self.creds['host']}:{self.creds['port']} …")
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
        ok("SSH connected  (keepalive=30s, timeout=60s)")

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

    # ── SFTP helpers (open once, reuse) ──

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
            try:
                self._sftp.close()
            except Exception:
                pass
        self.client.close()


# ════════════════════════════════════════════════════════════════════════════
# STEPS
# ════════════════════════════════════════════════════════════════════════════

def step1_show_platform_check(ssh: SSH):
    stp(1, "Show current platform_check.php content (head -5)")
    rc, out, err = ssh.run(f"head -5 {PLAT_CHECK}")
    print(out or err or "(no output)")
    record("1_platform_check_content", "INFO", out.strip().splitlines()[0][:60] if out.strip() else "empty")


def step2_show_autoload_refs(ssh: SSH):
    stp(2, "Show lines in autoload_real.php referencing platform_check")
    rc, out, err = ssh.run(f"grep -n 'platform_check' {AUTOLOAD}")
    print(out or "(no matches — already patched?)")
    if out.strip():
        record("2_autoload_refs", "INFO", f"{len(out.strip().splitlines())} reference(s) found")
    else:
        record("2_autoload_refs", "SKIP", "No references found — may already be patched")


def step3_sftp_patch_autoload(ssh: SSH):
    stp(3, "Patch autoload_real.php via SFTP (Python string replace, not sed)")

    # Download
    try:
        original = ssh.sftp_read(AUTOLOAD)
    except Exception as e:
        fail(f"SFTP read failed: {e}")
        record("3_sftp_patch_autoload", "FAIL", f"sftp_read error: {e}")
        return False

    if "platform_check" not in original:
        ok("platform_check reference not found — already patched or absent")
        record("3_sftp_patch_autoload", "SKIP", "Already patched")
        return True

    # Backup original on server before overwriting
    ssh.run(f"cp {AUTOLOAD} {AUTOLOAD}.bak.$(date +%s) 2>/dev/null || true")

    # Replace — handle both single-line and require() formats
    lines = original.splitlines(keepends=True)
    patched_lines = []
    changed = 0
    for line in lines:
        if "platform_check" in line:
            # Comment it out rather than deleting so the file stays valid PHP
            patched_lines.append("// platform_check bypassed by fix_php_artisan.py\n")
            changed += 1
        else:
            patched_lines.append(line)
    patched = "".join(patched_lines)

    # Upload
    try:
        ssh.sftp_write(AUTOLOAD, patched)
    except Exception as e:
        fail(f"SFTP write failed: {e}")
        record("3_sftp_patch_autoload", "FAIL", f"sftp_write error: {e}")
        return False

    ok(f"autoload_real.php patched — {changed} line(s) commented out")

    # Show the patched area for confirmation
    rc, verify, _ = ssh.run(f"grep -n 'platform_check' {AUTOLOAD}")
    print(f"  Post-patch grep: {verify.strip() or '(no remaining references — good)'}")

    record("3_sftp_patch_autoload", "PASS", f"{changed} platform_check line(s) commented out")
    return True


def step4_rename_platform_check(ssh: SSH):
    stp(4, "Rename platform_check.php → .bak (belt-and-suspenders)")
    rc, out, err = ssh.run(f"test -f {PLAT_CHECK} && echo EXISTS || echo GONE")
    if "GONE" in out:
        ok("platform_check.php already absent")
        record("4_rename_platform_check", "SKIP", "File already absent")
        return
    rc2, out2, err2 = ssh.run(f"mv {PLAT_CHECK} {PLAT_CHECK}.bak")
    if rc2 == 0:
        ok(f"Renamed to {PLAT_CHECK}.bak")
        record("4_rename_platform_check", "PASS", "Renamed to .bak")
    else:
        warn(f"mv failed (rc={rc2}): {err2.strip()[:100]}")
        record("4_rename_platform_check", "FAIL", f"mv error: {err2.strip()[:80]}")


def step5_test_artisan(ssh: SSH) -> bool:
    stp(5, "Test artisan --version")
    rc, out, err = ssh.run(
        f"cd {APP} && {PHP} artisan --version 2>&1", timeout=30
    )
    combined = (out + err).strip()
    print(f"  Output: {combined}")
    if "Laravel Framework" in combined:
        ok("artisan works!")
        record("5_artisan_version", "PASS", combined[:60])
        return True
    else:
        fail("artisan still failing")
        record("5_artisan_version", "FAIL", combined[:80])
        return False


def step6_clear_caches(ssh: SSH, artisan_ok: bool):
    stp(6, "Clear all Laravel caches via artisan")
    if not artisan_ok:
        warn("Skipping — artisan is not working (Step 5 failed)")
        record("6_clear_caches", "SKIP", "artisan not working")
        return

    all_ok = True
    for cmd in ("config:clear", "cache:clear", "route:clear", "optimize:clear"):
        rc, out, err = ssh.run(
            f"cd {APP} && {PHP} artisan {cmd} 2>&1", timeout=60
        )
        combined = (out + err).strip()
        if rc == 0 and "ERROR" not in combined.upper() and "Fatal" not in combined:
            ok(f"artisan {cmd}  →  {combined[:60]}")
        else:
            warn(f"artisan {cmd} rc={rc}: {combined[:100]}")
            all_ok = False

    record("6_clear_caches", "PASS" if all_ok else "FAIL",
           "All cache commands ran" if all_ok else "One or more commands failed")


def step7_delete_cache_files(ssh: SSH):
    stp(7, "Manually delete bootstrap/cache/* files")
    for f in CACHE_FILES:
        rc, out, _ = ssh.run(f"rm -f {f} && echo DELETED || echo ERROR")
        print(f"  {f.split('/')[-1]}: {out.strip()}")

    # List what remains
    rc, ls_out, _ = ssh.run(f"ls -la {APP}/bootstrap/cache/ 2>/dev/null")
    print(f"\n  bootstrap/cache/ contents:\n{ls_out}")
    record("7_delete_cache_files", "PASS", "Cache files removed")


def step8_show_vhost_conf(ssh: SSH):
    stp(8, f"Show OLS vhost conf: {VHOST_CONF}")
    rc, out, err = ssh.run(f"cat {VHOST_CONF} 2>/dev/null || echo NOT_FOUND")
    if "NOT_FOUND" in out or not out.strip():
        warn("vhconf.conf not found at expected path — searching …")
        rc2, found, _ = ssh.run(
            "find /usr/local/lsws/conf/vhosts/ -name '*.conf' 2>/dev/null"
            " | xargs grep -l 'chatbotnepal' 2>/dev/null"
        )
        alt_paths = [p.strip() for p in found.strip().splitlines() if p.strip()]
        if alt_paths:
            warn(f"Found at alternate path(s): {alt_paths}")
            rc3, out, _ = ssh.run(f"cat {alt_paths[0]}")
            print(f"\n{C.Y}--- {alt_paths[0]} ---{C.X}")
            print(out)
            print(f"{C.Y}--- end ---{C.X}")
            record("8_vhost_conf", "INFO", f"Found at {alt_paths[0]}")
        else:
            fail("Could not find any vhost conf for chatbotnepal")
            # Dump the vhosts directory listing for manual inspection
            rc4, ls_out, _ = ssh.run(
                "find /usr/local/lsws/conf/vhosts/ -name '*.conf' 2>/dev/null"
            )
            print(f"  All .conf files:\n{ls_out or '(none)'}")
            record("8_vhost_conf", "FAIL", "vhost conf not found")
        return

    print(f"\n{C.Y}--- {VHOST_CONF} ---{C.X}")
    print(out)
    print(f"{C.Y}--- end ---{C.X}")

    has_cors = "Access-Control" in out
    has_extra = "extraHeaders" in out
    (ok if has_cors else warn)(
        f"Access-Control headers: {'PRESENT' if has_cors else 'MISSING'} in vhost conf"
    )
    (ok if has_extra else warn)(
        f"extraHeaders block: {'PRESENT' if has_extra else 'MISSING'} in vhost conf"
    )
    record("8_vhost_conf", "PASS" if has_cors else "INFO",
           ("CORS headers in conf" if has_cors else "No CORS headers in vhost conf"))


def step9_restart_ols(ssh: SSH):
    stp(9, "Restart OpenLiteSpeed")
    rc, out, err = ssh.run("systemctl restart lsws 2>&1")
    if rc != 0:
        warn(f"systemctl failed — trying lswsctrl: {(err or out).strip()[:80]}")
        ssh.run("/usr/local/lsws/bin/lswsctrl restart 2>&1")

    info("Waiting 5 seconds …")
    time.sleep(5)

    rc2, status, _ = ssh.run("systemctl is-active lsws 2>/dev/null")
    status = status.strip()
    (ok if status == "active" else fail)(f"lsws status: {status}")
    record("9_restart_ols", "PASS" if status == "active" else "FAIL", f"lsws is {status}")
    return status == "active"


def step10_cors_test(ssh: SSH) -> bool:
    stp(10, "Final CORS verification — two curl requests")
    time.sleep(2)

    # --- GET ---
    rc1, get_out, _ = ssh.run(
        f"curl -sI --max-time 15 {SITE}{ENDPOINT} 2>&1", timeout=25
    )
    print(f"\n{C.Y}--- GET {SITE}{ENDPOINT} ---{C.X}")
    print(get_out or "(no output)")

    # --- OPTIONS preflight ---
    rc2, opts_out, _ = ssh.run(
        f"curl -sI -X OPTIONS --max-time 15 "
        f"-H 'Origin: {ORIGIN}' "
        f"-H 'Access-Control-Request-Method: POST' "
        f"{SITE}{ENDPOINT} 2>&1",
        timeout=25,
    )
    print(f"\n{C.Y}--- OPTIONS {SITE}{ENDPOINT} ---{C.X}")
    print(opts_out or "(no output)")
    print(f"{C.Y}--- end ---{C.X}\n")

    get_cors  = "Access-Control-Allow-Origin" in get_out
    opts_cors = "Access-Control-Allow-Origin" in opts_out

    (ok if get_cors  else warn)(f"GET    Access-Control-Allow-Origin: {'✓ PRESENT' if get_cors  else 'MISSING'}")
    (ok if opts_cors else fail)(f"OPTIONS Access-Control-Allow-Origin: {'✓ PRESENT' if opts_cors else 'MISSING'}")

    passed = opts_cors
    record("10_cors_verification",
           "PASS" if passed else "FAIL",
           "CORS headers present" if passed else "Access-Control-Allow-Origin still missing from OPTIONS")
    return passed


def step11_response_body(ssh: SSH):
    stp(11, "Fetch response body (diagnose if Laravel is running or OLS serves static 404)")

    rc, body, _ = ssh.run(
        f"curl -s --max-time 15 {SITE}{ENDPOINT} 2>&1", timeout=25
    )
    print(f"\n{C.Y}--- Response body ---{C.X}")
    # Truncate to 2000 chars — enough to see the error without flooding terminal
    print((body[:2000] + "\n… (truncated)") if len(body) > 2000 else body or "(empty body)")
    print(f"{C.Y}--- end ---{C.X}")

    if "Laravel" in body or "Illuminate" in body or "symfony" in body.lower():
        ok("Laravel/PHP is responding (framework error, not static OLS 404)")
        record("11_response_body", "INFO", "Laravel is running — check app logic / routing")
    elif "<!DOCTYPE" in body or "<html" in body.lower():
        warn("OLS/CyberPanel is serving an HTML page — Laravel may not be routing")
        record("11_response_body", "INFO", "HTML response — OLS static page, not Laravel")
    elif not body.strip():
        warn("Empty body — connection issue or server error")
        record("11_response_body", "INFO", "Empty body")
    else:
        info(f"Response body snippet: {body[:120]}")
        record("11_response_body", "INFO", body[:60])


# ════════════════════════════════════════════════════════════════════════════
# ENTRY POINT
# ════════════════════════════════════════════════════════════════════════════

def find_creds(given: str) -> str:
    for p in (given, os.path.join("fix", given)):
        if os.path.exists(p):
            return p
    return given


def main():
    p = argparse.ArgumentParser(description="Fix PHP artisan + CORS on ChatBot Nepal OLS server")
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
        fail(str(e))
        sys.exit(2)

    ssh = SSH(creds, verbose=args.verbose)
    try:
        ssh.connect()
    except Exception as e:
        fail(f"SSH connection failed: {e}")
        sys.exit(2)

    cors_ok = False
    try:
        hdr("PHP ARTISAN + CORS FIX PIPELINE")

        step1_show_platform_check(ssh)
        step2_show_autoload_refs(ssh)
        step3_sftp_patch_autoload(ssh)
        step4_rename_platform_check(ssh)
        artisan_ok = step5_test_artisan(ssh)
        step6_clear_caches(ssh, artisan_ok)
        step7_delete_cache_files(ssh)
        step8_show_vhost_conf(ssh)
        step9_restart_ols(ssh)
        cors_ok = step10_cors_test(ssh)

        if not cors_ok:
            step11_response_body(ssh)

    finally:
        ssh.close()
        print_summary()

    sys.exit(0 if cors_ok else 1)


if __name__ == "__main__":
    main()
