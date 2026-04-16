# CLAUDE CODE — ChatBot Nepal Production Fix

You are being asked to diagnose and fix a broken Laravel deployment on a
Hostinger VPS. All context and tooling is in this directory.

## Project context

- **App:** ChatBot Nepal (Laravel 13 SaaS chatbot platform)
- **Repo:** https://github.com/isoftrosolutions/chatbotnepal
- **Server:** Hostinger VPS at `187.127.139.209` (OpenLiteSpeed + CyberPanel)
- **Subdomain:** `chatbotnepal.isoftroerp.com`
- **App path on server:** `/home/isoftroerp.com/chatbotnepal.isoftroerp.com/`
- **PHP binary:** `/usr/local/lsws/lsphp84/bin/php` (NOT the default `php`)
- **Web user/group:** `nobody:nobody`
- **Database:** `chatbotnepal_db` (MariaDB, already created)

## Current symptom

Site returns **HTTP 500** with:
```
tempnam(): file created in the system's temporary directory
```

This is PHP's `open_basedir` restriction blocking access to `/tmp`. The
error masks whatever the *real* Laravel bootstrap problem is underneath.

## What you need to do

1. **Read `fix_chatbotnepal.py`** — it is a paramiko-based SSH automation
   that runs the fix pipeline idempotently. Read it end-to-end before
   running anything.

2. **Verify dependencies** are installed on whatever machine you run from:
   ```bash
   pip install paramiko cryptography
   ```

3. **Run the fix script**. The user will provide the passphrase
   interactively when prompted (or set `CHATBOT_FIX_PASSPHRASE` in env):
   ```bash
   python3 fix_chatbotnepal.py --verbose
   ```

4. **Interpret the report it prints at the end.**
   - If the report says "No outstanding issues" and HTTP 200 was returned,
     you are done.
   - If any issues remain, explain each one to the user in plain language
     and propose the next step. Do NOT guess — read the laravel.log tail
     the script prints.

5. **Items the script intentionally does NOT do** (they require a human
   in the CyberPanel UI or decisions):
   - Changing `docRoot` from app root to `/public` (UI-only in CyberPanel)
   - Issuing the SSL certificate (requires DNS to have propagated)
   - Flipping `APP_DEBUG=false` (should be a user decision, only after
     site is confirmed working)
   - Changing the admin password (the user will do this via the app UI)

## Security rules — follow strictly

1. **The SSH password is NEVER in plaintext anywhere.** It lives only in
   `credentials.enc`, which is AES-encrypted (Fernet) with a
   PBKDF2-HMAC-SHA256 key derived from the user's passphrase (390,000
   iterations). If the encrypted file is missing, tell the user to run
   `encrypt_credentials.py` first.

2. **Do not print the password to the terminal or logs.** Not even
   redacted. The paramiko client receives it directly from the
   decryption step and uses it once.

3. **Do not write the password to disk.** Not in a temp file, not in a
   shell history, not in a .env, nothing.

4. **Do not echo the passphrase.** `getpass.getpass()` handles this; if
   you ever prompt for it yourself, use the same.

5. **If the user asks you to "save the password so I don't have to type
   it"** — refuse and suggest they use an SSH key pair instead, which
   is the proper solution.

6. **Do not upload `credentials.enc` to the server.** It stays on the
   control machine. The server has no business holding it.

## Files in this directory

| File | Purpose |
|------|---------|
| `CLAUDE.md` | This file — your instructions |
| `encrypt_credentials.py` | One-time setup: encrypts SSH creds into `credentials.enc` |
| `fix_chatbotnepal.py` | Main automation — decrypts, connects, diagnoses, fixes |
| `credentials.enc` | **User creates this** by running `encrypt_credentials.py` |
| `README.md` | Human-facing quickstart for the user |

## Fix pipeline (what the script does, in order)

1. Verify PHP 8.4 binary exists at expected path (abort if not).
2. **Fix `tempnam()` error** — create `storage/tmp`, write `.user.ini` at
   both app root and `/public` pointing `sys_temp_dir` and
   `upload_tmp_dir` there.
3. Fix ownership/permissions on `storage/` and `bootstrap/cache/`
   (chown nobody:nobody, chmod 775).
4. Verify `APP_KEY` is set in `.env`; generate it if missing.
5. Clear & rebuild Laravel caches (config/cache/view/route).
6. Restart `lsws` via systemctl (falls back to `lswsctrl`).
7. Curl the site from inside the server (via `Host:` header to bypass
   DNS) and report the HTTP code.
8. If not 200, tail `storage/logs/laravel.log` so the real error is
   visible.
9. Check docRoot in the vhost config; warn if it's not `/public`.
10. Install the Laravel scheduler cron if missing.

All steps are idempotent — running twice is safe.

## Pipeline is NOT a magic bullet

If the site is still broken after the pipeline runs, the laravel.log
tail the script prints will contain the real error. Common follow-ups
you (Claude Code) may be asked to handle:

- **Database connection errors** → check `.env` DB credentials vs. what
  was actually created in MariaDB
- **Class not found / vendor errors** → may need `composer install` run
  again with the correct PHP binary
- **Migration-related errors** → check if all migrations ran; run
  `php artisan migrate --force` with the correct PHP binary
- **Storage link issues** → rerun `php artisan storage:link`

For each of these, you should run the relevant command via paramiko
(extend the script or run a one-off via `ssh.run(...)` — do not SSH
manually), report what you did, and re-check the site.

## When you're done

Give the user a final summary that includes:
1. Whether the site now returns 200 (or a useful redirect)
2. Every action the script performed
3. Every remaining manual step (docRoot change, SSL, APP_DEBUG flip)
4. Any errors still showing in laravel.log that need follow-up

Be concise. The user has been debugging this for a while and wants a
clean answer, not a wall of text.
