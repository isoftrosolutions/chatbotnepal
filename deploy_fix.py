#!/usr/bin/env python3
"""
deploy_fix.py
-------------
1. Fix .htaccess merge conflict (git checkout --theirs)
2. git pull origin main
3. composer install --no-dev --optimize-autoloader --ignore-platform-reqs
4. php artisan migrate --force
5. Clear all Laravel caches
6. Restart lsws
7. Verify CORS on /api/widget/session

Usage:
    python deploy_fix.py [--creds PATH] [--verbose]
"""

import argparse, base64, getpass, io, json, os, sys, time

try:
    import paramiko
    from cryptography.fernet import Fernet, InvalidToken
    from cryptography.hazmat.primitives import hashes
    from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC
except ImportError as e:
    print(f"Missing dependency: {e}\nInstall: pip install paramiko cryptography")
    sys.exit(2)

APP      = "/home/isoftroerp.com/chatbotnepal.isoftroerp.com"
PHP      = "/usr/local/lsws/lsphp83/bin/php"
SITE     = "https://chatbotnepal.isoftroerp.com"
ENDPOINT = "/api/widget/session"
ORIGIN   = "https://isoftroerp.com"
ITERS    = 390_000

class C:
    R="\033[91m"; G="\033[92m"; Y="\033[93m"
    B="\033[94m"; X="\033[0m"; BOLD="\033[1m"

def ok(m):   print(f"{C.G}[OK]{C.X} {m}")
def info(m): print(f"{C.B}[..]{C.X} {m}")
def warn(m): print(f"{C.Y}[!!]{C.X} {m}")
def fail(m): print(f"{C.R}[XX]{C.X} {m}")
def hdr(m):  print(f"\n{C.BOLD}{'='*60}\n  {m}\n{'='*60}{C.X}")
def stp(n,m):print(f"\n{C.BOLD}{C.Y}━━ Step {n}: {m}{C.X}")
def div():   print(f"{C.Y}{'─'*60}{C.X}")

_results: list[dict] = []

def record(name, status, note=""):
    _results.append({"name": name, "status": status, "note": note})
    sym = {"PASS": f"{C.G}PASS{C.X}", "FAIL": f"{C.R}FAIL{C.X}",
           "SKIP": f"{C.Y}SKIP{C.X}", "INFO": f"{C.B}INFO{C.X}"}.get(status, status)
    print(f"  ↳ {sym}  {note}")

def print_summary():
    hdr("FINAL SUMMARY")
    w = max(len(r["name"]) for r in _results) + 2
    for r in _results:
        sym = {"PASS": f"{C.G}PASS{C.X}", "FAIL": f"{C.R}FAIL{C.X}",
               "SKIP": f"{C.Y}SKIP{C.X}", "INFO": f"{C.B}INFO{C.X}"}.get(r["status"], r["status"])
        print(f"  {r['name']:<{w}} {sym:<18}  {r['note'][:55]}")
    print()
    failed = [r["name"] for r in _results if r["status"] == "FAIL"]
    if not failed:
        print(f"{C.G}{C.BOLD}All steps PASS.{C.X}")
    else:
        print(f"{C.R}{C.BOLD}Still failing: {', '.join(failed)}{C.X}")

def decrypt_credentials(path, passphrase):
    with open(path, "rb") as f:
        blob = f.read()
    salt, token = blob[:16], blob[16:]
    kdf = PBKDF2HMAC(algorithm=hashes.SHA256(), length=32, salt=salt, iterations=ITERS)
    key = base64.urlsafe_b64encode(kdf.derive(passphrase.encode()))
    try:
        data = Fernet(key).decrypt(token)
    except InvalidToken:
        raise ValueError("Wrong passphrase or corrupted credentials file.")
    return json.loads(data.decode())

class SSH:
    def __init__(self, creds, verbose=False):
        self.creds   = creds
        self.verbose = verbose
        self.client  = paramiko.SSHClient()
        self.client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

    def connect(self):
        info(f"Connecting {self.creds['user']}@{self.creds['host']}:{self.creds['port']} ...")
        self.client.connect(
            hostname=self.creds["host"], port=self.creds["port"],
            username=self.creds["user"], password=self.creds["password"],
            timeout=60, banner_timeout=60, auth_timeout=60,
            look_for_keys=False, allow_agent=False,
        )
        self.client.get_transport().set_keepalive(30)
        ok("SSH connected")

    def run(self, cmd, timeout=120):
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

    def close(self):
        self.client.close()


# ── Step 1: Fix .htaccess merge conflict + git pull ──────────────────────────

def step1_git_pull(ssh: SSH):
    stp(1, "Fix .htaccess merge conflict and git pull")

    # Check current git status
    rc, status, _ = ssh.run(f"cd {APP} && git status --short 2>&1")
    info(f"git status:\n{status.strip()}")

    # If there's a merge conflict on .htaccess, resolve with theirs
    rc2, _, _ = ssh.run(
        f"cd {APP} && git ls-files -u public/.htaccess 2>&1 | head -3"
    )

    # Strategy: stash any local changes, pull, then restore
    # Or: checkout theirs for .htaccess then continue merge
    if "UU" in status or "AA" in status or "conflict" in status.lower():
        info("Merge conflict detected — resolving .htaccess with --theirs")
        ssh.run(f"cd {APP} && git checkout --theirs public/.htaccess 2>&1")
        ssh.run(f"cd {APP} && git add public/.htaccess 2>&1")
        rc3, out3, err3 = ssh.run(f"cd {APP} && git merge --continue --no-edit 2>&1")
        info(f"merge --continue: {(out3+err3).strip()[:120]}")
    else:
        # No active merge conflict — just pull
        info("No active merge conflict — running git pull")

    rc4, out4, err4 = ssh.run(f"cd {APP} && git pull origin main 2>&1", timeout=60)
    combined = (out4 + err4).strip()
    div()
    print(combined)
    div()

    if rc4 == 0 and ("Already up to date" in combined or "fast-forward" in combined.lower()
                     or "Updating" in combined):
        ok("git pull succeeded")
        record("1_git_pull", "PASS", combined.splitlines()[-1][:60] if combined else "done")
        return True
    else:
        # Even if pull shows warnings but rc=0, treat as ok
        if rc4 == 0:
            ok("git pull rc=0")
            record("1_git_pull", "PASS", combined.splitlines()[-1][:60] if combined else "rc=0")
            return True
        warn(f"git pull rc={rc4}")
        record("1_git_pull", "FAIL", combined.splitlines()[-1][:60] if combined else f"rc={rc4}")
        return False


# ── Step 2: composer install ──────────────────────────────────────────────────

def step2_composer_install(ssh: SSH):
    stp(2, "composer install --no-dev --optimize-autoloader --ignore-platform-reqs")

    # Find composer binary
    rc, composer_path, _ = ssh.run("which composer 2>/dev/null || echo ''")
    composer_path = composer_path.strip()
    if not composer_path:
        # Try common locations
        for p in ("/usr/local/bin/composer", "/usr/bin/composer", f"{APP}/composer.phar"):
            rc2, ex, _ = ssh.run(f"test -f {p} && echo YES || echo NO")
            if "YES" in ex:
                composer_path = p
                break
    if not composer_path:
        warn("composer binary not found — skipping")
        record("2_composer", "SKIP", "composer not found")
        return

    info(f"Using composer: {composer_path}")
    cmd = (f"cd {APP} && {PHP} {composer_path} install "
           f"--no-dev --optimize-autoloader --ignore-platform-reqs --no-interaction 2>&1")
    rc3, out3, err3 = ssh.run(cmd, timeout=300)
    combined = (out3 + err3).strip()
    div()
    print(combined[-3000:] if len(combined) > 3000 else combined)
    div()

    if rc3 == 0 and "error" not in combined.lower().split("warning")[0]:
        ok("composer install completed")
        record("2_composer", "PASS", combined.splitlines()[-1][:60] if combined else "done")
    else:
        warn(f"composer install rc={rc3} — check output above")
        record("2_composer", "FAIL" if rc3 != 0 else "INFO",
               combined.splitlines()[-1][:60] if combined else f"rc={rc3}")


# ── Step 3: artisan migrate ───────────────────────────────────────────────────

def step3_migrate(ssh: SSH):
    stp(3, "php artisan migrate --force")

    rc, out, err = ssh.run(f"cd {APP} && {PHP} artisan migrate --force 2>&1", timeout=120)
    combined = (out + err).strip()
    div()
    print(combined)
    div()

    if rc == 0:
        ok("Migrations completed")
        record("3_migrate", "PASS", combined.splitlines()[-1][:60] if combined else "done")
    else:
        warn(f"migrate rc={rc}")
        record("3_migrate", "FAIL", combined.splitlines()[-1][:60] if combined else f"rc={rc}")


# ── Step 3b: app:setup — seed secrets from .env into settings table ──────────

def step3b_app_setup(ssh: SSH):
    stp("3b", "php artisan app:setup --non-interactive --test-mail")

    rc, out, err = ssh.run(
        f"cd {APP} && {PHP} artisan app:setup --non-interactive --test-mail 2>&1",
        timeout=60,
    )
    combined = (out + err).strip()
    div()
    print(combined)
    div()

    if rc == 0:
        ok("Secrets seeded into settings table")
        record("3b_app_setup", "PASS", combined.splitlines()[-1][:60] if combined else "done")
    else:
        warn(f"app:setup rc={rc} — secrets may not be configured")
        record("3b_app_setup", "FAIL", combined.splitlines()[-1][:60] if combined else f"rc={rc}")


# ── Step 4: clear caches ──────────────────────────────────────────────────────

def step4_clear_caches(ssh: SSH):
    stp(4, "Clear all Laravel caches")
    all_ok = True
    for cmd in ("config:clear", "cache:clear", "route:clear", "view:clear", "optimize:clear"):
        rc, out, err = ssh.run(f"cd {APP} && {PHP} artisan {cmd} 2>&1", timeout=60)
        combined = (out + err).strip()
        if rc == 0:
            ok(f"artisan {cmd}  →  {combined[:80]}")
        else:
            warn(f"artisan {cmd} rc={rc}: {combined[:100]}")
            all_ok = False

    # Also nuke bootstrap cache files directly
    for f in ("bootstrap/cache/config.php", "bootstrap/cache/routes-v7.php",
              "bootstrap/cache/services.php", "bootstrap/cache/packages.php"):
        ssh.run(f"rm -f {APP}/{f} 2>/dev/null")
    ok("Bootstrap cache files removed")

    record("4_clear_caches", "PASS" if all_ok else "FAIL",
           "All OK" if all_ok else "Some commands failed — check above")

    # Production optimize — rebuild caches after clearing
    stp("4b", "Production optimize (config + route + view cache)")
    all_ok2 = True
    for cmd in ("config:cache", "route:cache", "view:cache"):
        rc, out, err = ssh.run(f"cd {APP} && {PHP} artisan {cmd} 2>&1", timeout=60)
        combined = (out + err).strip()
        if rc == 0:
            ok(f"artisan {cmd}  →  {combined[:80]}")
        else:
            warn(f"artisan {cmd} rc={rc}: {combined[:100]}")
            all_ok2 = False
    record("4b_optimize", "PASS" if all_ok2 else "FAIL",
           "All OK" if all_ok2 else "Some cache commands failed")


# ── Step 5: restart lsws ─────────────────────────────────────────────────────

def step5_restart_lsws(ssh: SSH):
    stp(5, "Restart lsws")

    LSWSCTRL = "/usr/local/lsws/bin/lswsctrl"
    rc, out, err = ssh.run(f"{LSWSCTRL} restart 2>&1")
    combined = (out + err).strip()
    info(f"lswsctrl: {combined[:120]}")

    if rc != 0:
        warn("lswsctrl failed — trying systemctl restart lsws")
        ssh.run("systemctl restart lsws 2>&1")

    info("Waiting 4 seconds for lsws to come up ...")
    time.sleep(4)

    rc2, status, _ = ssh.run("systemctl is-active lsws 2>/dev/null || echo unknown")
    status = status.strip()
    (ok if status == "active" else fail)(f"lsws status: {status}")
    record("5_restart_lsws", "PASS" if status == "active" else "FAIL", f"lsws={status}")


# ── Step 6: verify CORS ───────────────────────────────────────────────────────

def step6_verify(ssh: SSH):
    stp(6, "CORS verification")
    time.sleep(2)

    # OPTIONS preflight
    rc1, opts_h, _ = ssh.run(
        f"curl -sI -X OPTIONS --max-time 15 "
        f"-H 'Origin: {ORIGIN}' "
        f"-H 'Access-Control-Request-Method: POST' "
        f"{SITE}{ENDPOINT} 2>&1", timeout=25
    )
    print(f"\n{C.Y}── OPTIONS headers ──{C.X}")
    print(opts_h or "(no output)")

    # POST body
    rc2, body, _ = ssh.run(
        f"curl -s -X POST --max-time 15 "
        f"-H 'Content-Type: application/json' "
        f"-d '{{\"site_id\":\"test\"}}' "
        f"{SITE}{ENDPOINT} 2>&1", timeout=25
    )
    print(f"\n{C.Y}── POST body ──{C.X}")
    print(body[:1000] if body else "(empty)")
    div()

    def first_status(text):
        for line in text.splitlines():
            if line.startswith("HTTP/"):
                return line.strip()
        return "(no HTTP status)"

    opts_status = first_status(opts_h)
    opts_cors   = "Access-Control-Allow-Origin" in opts_h
    opts_ok     = "204" in opts_status or "200" in opts_status

    (ok if opts_ok   else warn)(f"OPTIONS status : {opts_status}")
    (ok if opts_cors else fail)(f"OPTIONS CORS   : {'PRESENT' if opts_cors else 'MISSING'}")

    if body.strip().startswith("{"):
        ok("POST body: valid JSON — Laravel is responding")
    elif "Invalid site" in body or "site_id" in body:
        ok("POST body: Laravel responding (expected 'Invalid site' for test ID)")
    elif "<html" in body.lower():
        warn("POST body: HTML — OLS may not be routing to PHP")
    elif not body.strip():
        warn("POST body: empty — possible PHP or DB error")

    passed = opts_cors
    record("6_cors_verify",
           "PASS" if passed else "FAIL",
           f"OPTIONS CORS {'PRESENT' if passed else 'MISSING'} | status={opts_status}")
    return passed


# ── Entry point ───────────────────────────────────────────────────────────────

def find_creds(given):
    for p in (given, os.path.join("fix", given)):
        if os.path.exists(p):
            return p
    return given

def main():
    p = argparse.ArgumentParser()
    p.add_argument("--creds", default="credentials.enc")
    p.add_argument("--verbose", action="store_true")
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

    try:
        hdr("deploy_fix.py — git pull + composer + migrate + setup + optimize + restart")
        step1_git_pull(ssh)
        step2_composer_install(ssh)
        step3_migrate(ssh)
        step3b_app_setup(ssh)
        step4_clear_caches(ssh)
        step5_restart_lsws(ssh)
        passed = step6_verify(ssh)
    finally:
        ssh.close()
        print_summary()

    sys.exit(0 if passed else 1)

if __name__ == "__main__":
    main()
