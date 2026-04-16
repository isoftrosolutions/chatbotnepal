#!/bin/bash
# ChatBot Nepal — VPS Deployment Script
# Run this ONCE after uploading files to the VPS
# Usage: bash deploy.sh

set -e

echo "=== ChatBot Nepal Deployment ==="

# 1. Use production env
cp .env.production .env
echo "✓ .env set to production"

# 2. Swap to production .htaccess (no RewriteBase — app runs at domain root on VPS)
cp public/.htaccess.production public/.htaccess
echo "✓ .htaccess set for production"

# 3. Install PHP dependencies (no dev packages)
composer install --no-dev --optimize-autoloader --no-interaction
echo "✓ Composer dependencies installed"

# 4. Run database migrations
php artisan migrate --force
echo "✓ Database migrated"

# 5. Seed database (admin user + settings)
php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=SettingsSeeder --force
echo "✓ Database seeded"

# 6. Sync KB files to disk
php artisan kb:sync
echo "✓ Knowledge base files synced to disk"

# 7. Cache everything for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Caches built"

# 8. Storage symlink
php artisan storage:link
echo "✓ Storage linked"

# 9. Fix permissions
chmod -R 775 storage bootstrap/cache
echo "✓ Permissions set"

echo ""
echo "=== Deployment complete ==="
echo ""
echo "NEXT STEPS:"
echo "  1. Edit .env and set GROK_API_KEY=your-real-key"
echo "  2. Add cron job:"
echo "     * * * * * cd /home/chatbotnepal.isoftroerp.com/public_html && php artisan schedule:run >> /dev/null 2>&1"
echo "  3. Open https://chatbotnepal.isoftroerp.com and login:"
echo "     Email:    isoftrosolutions@gmail.com"
echo "     Password: Admin@ChatBot2026"
echo ""
