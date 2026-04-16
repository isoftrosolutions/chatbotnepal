#!/usr/bin/env python3
"""
encrypt_credentials.py
----------------------
Run this ONCE on your local machine to encrypt your SSH credentials.
It produces `credentials.enc` which Claude Code will decrypt at runtime
using a passphrase you provide interactively.

Usage:
    python3 encrypt_credentials.py

You'll be prompted for:
  - SSH host, port, username, password
  - A passphrase to protect the encrypted file

Output:
  - credentials.enc  (safe to commit or share with Claude Code)
"""

import json
import os
import getpass
import base64
from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC
from cryptography.hazmat.primitives import hashes
from cryptography.fernet import Fernet

ITERATIONS = 390_000  # OWASP 2023 recommendation for PBKDF2-HMAC-SHA256


def derive_key(passphrase: str, salt: bytes) -> bytes:
    kdf = PBKDF2HMAC(
        algorithm=hashes.SHA256(),
        length=32,
        salt=salt,
        iterations=ITERATIONS,
    )
    return base64.urlsafe_b64encode(kdf.derive(passphrase.encode()))


def main():
    print("=== ChatBot Nepal — SSH Credential Encryption ===\n")

    host = input("SSH host [187.127.139.209]: ").strip() or "187.127.139.209"
    port = input("SSH port [22]: ").strip() or "22"
    user = input("SSH username [root]: ").strip() or "root"
    password = getpass.getpass("SSH password: ")
    if not password:
        print("Password cannot be empty.")
        return

    print()
    passphrase = getpass.getpass("Choose a passphrase to encrypt the file: ")
    confirm = getpass.getpass("Confirm passphrase: ")
    if passphrase != confirm:
        print("Passphrases do not match.")
        return
    if len(passphrase) < 8:
        print("Passphrase must be at least 8 characters.")
        return

    payload = json.dumps({
        "host": host,
        "port": int(port),
        "user": user,
        "password": password,
    }).encode()

    salt = os.urandom(16)
    key = derive_key(passphrase, salt)
    token = Fernet(key).encrypt(payload)

    # File format: [16 bytes salt][fernet token]
    with open("credentials.enc", "wb") as f:
        f.write(salt + token)

    os.chmod("credentials.enc", 0o600)
    print("\n[OK] Wrote credentials.enc (chmod 600)")
    print("    Keep your passphrase safe — you'll need it to run the fix script.")


if __name__ == "__main__":
    main()
