"""
reset.py — Full project reset script for ChatBot Nepal
Wipes the working directory, re-clones from GitHub, installs deps,
runs migrations, and clears all Laravel caches.

Usage:
    python reset.py
    python reset.py --branch main          (default)
    python reset.py --branch feature/xyz
"""

import os
import sys
import shutil
import subprocess
import argparse

REPO_URL  = "https://github.com/isoftrosolutions/chatbotnepal.git"
TARGET    = r"C:\Apache24\htdocs\ai"
PHP       = "php"            # adjust if php is not on PATH
COMPOSER  = "composer"       # adjust if composer is not on PATH

# Files / dirs to KEEP when wiping (relative to TARGET)
KEEP = {
    ".env",
    ".env.backup",
    "reset.py",
    "vendor",        # optional — remove if you want a full composer install
}


def run(cmd: list[str], cwd: str = TARGET, check: bool = True) -> int:
    print(f"\n>>> {' '.join(cmd)}")
    result = subprocess.run(cmd, cwd=cwd, check=False)
    if check and result.returncode != 0:
        print(f"[ERROR] command failed with exit code {result.returncode}")
        sys.exit(result.returncode)
    return result.returncode


def wipe_directory(path: str, keep: set[str]) -> None:
    print(f"\n[1/6] Wiping {path}  (keeping: {keep})")
    for entry in os.listdir(path):
        if entry in keep:
            print(f"      skipping  {entry}")
            continue
        full = os.path.join(path, entry)
        if os.path.isdir(full):
            shutil.rmtree(full)
            print(f"      removed   {entry}/")
        else:
            os.remove(full)
            print(f"      removed   {entry}")


def clone_into(repo: str, path: str, branch: str) -> None:
    print(f"\n[2/6] Cloning {repo}  (branch: {branch})  into {path}")
    tmp = path + "_tmp_clone"
    if os.path.exists(tmp):
        shutil.rmtree(tmp)

    run(["git", "clone", "--branch", branch, "--single-branch", repo, tmp], cwd=os.path.dirname(path))

    # Move cloned content into TARGET (skip items already in KEEP)
    for entry in os.listdir(tmp):
        src = os.path.join(tmp, entry)
        dst = os.path.join(path, entry)
        if os.path.exists(dst):
            print(f"      conflict — keeping existing  {entry}")
            continue
        shutil.move(src, dst)

    shutil.rmtree(tmp)


def git_pull(path: str, branch: str) -> None:
    print(f"\n[3/6] git pull origin {branch}")
    run(["git", "fetch", "origin"], cwd=path)
    run(["git", "checkout", branch], cwd=path)
    run(["git", "pull", "origin", branch], cwd=path)


def composer_install(path: str) -> None:
    print("\n[4/6] composer install")
    run([COMPOSER, "install", "--no-interaction", "--prefer-dist"], cwd=path)


def artisan(path: str, *args: str) -> None:
    run([PHP, "artisan", *args], cwd=path)


def main() -> None:
    parser = argparse.ArgumentParser(description="Full project reset for ChatBot Nepal")
    parser.add_argument("--branch", default="main", help="Git branch to clone/pull (default: main)")
    parser.add_argument("--skip-wipe", action="store_true", help="Skip the wipe step (useful for quick re-pull)")
    opts = parser.parse_args()

    print("=" * 60)
    print("  ChatBot Nepal — Full Reset")
    print(f"  repo   : {REPO_URL}")
    print(f"  branch : {opts.branch}")
    print(f"  target : {TARGET}")
    print("=" * 60)

    confirm = input("\nThis will DELETE most files in the target directory. Continue? [y/N] ").strip().lower()
    if confirm != "y":
        print("Aborted.")
        sys.exit(0)

    # 1. Wipe
    if not opts.skip_wipe:
        wipe_directory(TARGET, KEEP)

    # 2. Clone
    clone_into(REPO_URL, TARGET, opts.branch)

    # 3. Pull (ensures we are on the right branch and up to date)
    git_pull(TARGET, opts.branch)

    # 4. Composer install
    composer_install(TARGET)

    # 5. Migrate
    print("\n[5/6] php artisan migrate --force")
    artisan(TARGET, "migrate", "--force")

    # 6. Clear all caches
    print("\n[6/6] Clearing Laravel caches")
    for cmd in [
        ["config:clear"],
        ["cache:clear"],
        ["route:clear"],
        ["view:clear"],
        ["event:clear"],
        ["config:cache"],
        ["route:cache"],
    ]:
        artisan(TARGET, *cmd)

    print("\n" + "=" * 60)
    print("  Reset complete.")
    print("=" * 60)


if __name__ == "__main__":
    main()
