# ChatBot Nepal — Production Fix Toolkit

Encrypted-credential SSH automation to diagnose and fix the Laravel
deployment on the Hostinger VPS.

## What this does

Fixes the current `tempnam()` blocker and everything typically sitting
behind it (permissions, APP_KEY, cache, scheduler cron). Reports any
remaining issues with a clean summary.

## Setup (once)

```bash
pip install paramiko cryptography
python3 encrypt_credentials.py
```

You'll be prompted for:
- SSH host (`187.127.139.209`)
- SSH port (`22`)
- SSH username (`root`)
- SSH password (typed silently, never echoed)
- A passphrase to protect the encrypted file (minimum 8 chars)

This produces `credentials.enc` — safe to keep on your machine.
It does NOT contain the password in any recoverable form without
the passphrase.

## Run the fix

```bash
python3 fix_chatbotnepal.py --verbose
```

Enter your passphrase when prompted. The script walks the full fix
pipeline and prints a report at the end.

### Flags

- `--verbose` — show every remote command and its output
- `--dry-run` — print commands without executing them (safe preview)

### Non-interactive run (CI, automation)

```bash
export CHATBOT_FIX_PASSPHRASE='your-passphrase-here'
python3 fix_chatbotnepal.py
unset CHATBOT_FIX_PASSPHRASE
```

Avoid this unless you have to — interactive passphrase entry is safer.

## What the script fixes automatically

1. **`tempnam()` / `open_basedir`** — creates `storage/tmp` and writes
   `.user.ini` files so PHP uses it.
2. **File permissions** — chowns `storage/` and `bootstrap/cache/` to
   `nobody:nobody` with 775.
3. **Missing `APP_KEY`** — generates one if `.env` doesn't have it.
4. **Stale caches** — clears and rebuilds Laravel's config/cache/view/route.
5. **Web server restart** — restarts `lsws`.
6. **HTTP check** — curls the site from inside the server.
7. **Log tail** — if site is still broken, prints the last 60 lines of
   `laravel.log` so the real error is visible.
8. **Scheduler cron** — installs if missing.

## What the script does NOT do (intentional)

These require human decisions or CyberPanel UI:

- Changing `docRoot` to `/public` in the CyberPanel vhost
- Issuing the SSL certificate (needs DNS propagation first)
- Flipping `APP_DEBUG=false` (do this only after site confirms working)
- Changing the admin password (done via the app login page)

The script will warn you about each of these if it detects they're
still pending.

## Security notes

- **Password storage:** Fernet (AES-128-CBC + HMAC-SHA256) with PBKDF2
  key derivation (SHA-256, 390,000 iterations — OWASP 2023). File
  format: `[16-byte salt][Fernet token]`.
- **File permissions:** `credentials.enc` is written with mode 0600.
- **Runtime:** password is held in memory only during the SSH session
  and zeroed implicitly when the process exits.
- **Better long-term:** move to SSH key auth. The script is a bridge
  while you're still on password auth.

## Files

```
CLAUDE.md                  Instructions for Claude Code
README.md                  This file
encrypt_credentials.py     One-time setup tool
fix_chatbotnepal.py        The fix pipeline
credentials.enc            Your encrypted creds (created by setup)
```

## Troubleshooting

**"Wrong passphrase or corrupted credentials file"**
You typed the passphrase wrong, or `credentials.enc` was modified.
Re-run `encrypt_credentials.py`.

**"SSH connection failed"**
Check your internet. Check the VPS is up (try `ping 187.127.139.209`).
Check if Hostinger has blocked your IP (rare but happens after failed
login bursts).

**"PHP 8.4 binary not found"**
The PHP version was changed on the server. Re-check the real path:
`ssh root@187.127.139.209 'ls /usr/local/lsws/lsphp*/bin/php'`
Then update `PHP_BIN` at the top of `fix_chatbotnepal.py`.

**Site still returns 500 after fix pipeline**
Read the `laravel.log` tail the script printed. The real error is now
visible. Common causes: DB credentials in `.env` don't match what was
created in MariaDB, composer dependencies missing, migration errors.
