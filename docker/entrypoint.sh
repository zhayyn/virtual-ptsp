#!/bin/bash
# ============================================================
# Virtual PTSP - Entrypoint Script
# Built with ❤️ by zhayyn (+6281317361689)
# ============================================================

set -e

echo "============================================================"
echo "  🚀 Virtual PTSP Starting..."
echo "  Built with ❤️ by zhayyn (+6281317361689)"
echo "============================================================"

# ============================================================
# Wait for MySQL to be ready
# ============================================================
if [ -n "$DB_HOST" ]; then
    echo "⏳ Waiting for MySQL at $DB_HOST..."
    until php artisan migrate:status --no-ansi 2>/dev/null | grep -q '\[OK\]' || php artisan migrate:status 2>/dev/null; do
        echo "   MySQL not ready yet. Waiting..."
        sleep 2
    done
    echo "✅ MySQL is ready!"
fi

# ============================================================
# Run Migrations
# ============================================================
echo "🔄 Running migrations..."
php artisan migrate --force --no-interaction || echo "⚠️ Migrations may have already been run"

# ============================================================
# Cache Configuration
# ============================================================
echo "⚡ Optimizing application..."
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

# ============================================================
# Set Permissions
# ============================================================
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# ============================================================
# License Check
# ============================================================
if [ -n "$LICENSE_SERVER_URL" ]; then
    echo "🔐 Validating license..."
    # License validation logic will be handled by Laravel middleware
fi

# ============================================================
# Start PHP-FPM
# ============================================================
echo "🎯 Starting PHP-FPM..."
exec php-fpm