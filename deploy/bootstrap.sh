#!/usr/bin/env bash
set -euo pipefail

# GrimbaNews — One-time VPS provisioning.
#
# Run this ONCE on the VPS before the first `./deploy.sh production`
# from your laptop. It assumes the VPS is the shared Iboga host
# (209.74.88.135) with php-fpm, nginx, and composer already installed
# (NobuReach / Incognito / BraightLegal all live here, so the runtime
# is battle-tested).
#
# Usage on the VPS:
#   wget -q https://raw.githubusercontent.com/darkvaderfr/grimbanews/main/deploy/bootstrap.sh -O /tmp/gn-bootstrap.sh
#   sudo bash /tmp/gn-bootstrap.sh
#
# Or — easier — scp this file up and run it:
#   scp deploy/bootstrap.sh root@209.74.88.135:/tmp/
#   ssh root@209.74.88.135 'bash /tmp/bootstrap.sh'
#
# What it does:
#   1. Create /var/www/grimbanews/{releases,current}
#   2. Clone darkvaderfr/grimbanews public-read into current
#      (or accept a tarball already uploaded at /tmp/grimbanews-seed.tar.gz)
#   3. composer install --no-dev
#   4. cp .env.example → .env, fill prod values, generate APP_KEY
#   5. touch SQLite DB, migrate, seed RSS feeds
#   6. First poll to warm the draft queue
#   7. Install nginx vhost in sites-available ONLY (NOT symlinked into
#      sites-enabled — Vader enables manually once DNS points here)
#   8. Install crontab for schedule:run
#   9. Fix permissions (www-data ownership)
#
# SAFETY: won't touch /opt/incognito, /opt/grimbacare, /opt/iboga/*,
# or /var/www/nobureach. New paths only.

APP_NAME="grimbanews"
APP_PATH="/var/www/${APP_NAME}/current"
REPO_URL="https://github.com/darkvaderfr/grimbanews.git"

echo "═══ GrimbaNews — First-time provisioning ═══"

if [ -d "$APP_PATH" ]; then
    echo "ERROR: $APP_PATH already exists. Bootstrap has already run (or a prior attempt left debris)."
    echo "  Review manually before re-running."
    exit 1
fi

# 1. Directory tree
mkdir -p "/var/www/${APP_NAME}"
cd "/var/www/${APP_NAME}"

# 2. Pull code — preferred path: tarball already uploaded. Fallback: git clone.
if [ -f /tmp/grimbanews-seed.tar.gz ]; then
    echo "=== Seeding from /tmp/grimbanews-seed.tar.gz ==="
    mkdir -p current
    tar -xzf /tmp/grimbanews-seed.tar.gz -C current
    rm -f /tmp/grimbanews-seed.tar.gz
elif command -v git &> /dev/null; then
    echo "=== Cloning ${REPO_URL} ==="
    git clone "${REPO_URL}" current
else
    echo "ERROR: neither /tmp/grimbanews-seed.tar.gz nor git available."
    exit 1
fi

cd "$APP_PATH"

# 3. Composer install
echo "=== Composer install ==="
composer install --no-dev --optimize-autoloader --no-interaction

# 4. .env + APP_KEY
echo "=== Writing .env ==="
if [ -f .env ]; then
    echo "  (.env already present — leaving untouched)"
else
    cat > .env <<ENV
APP_NAME=GrimbaNews
APP_ENV=production
APP_DEBUG=false
APP_URL=https://grimbanews.com
APP_LOCALE=fr
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=sqlite
DB_DATABASE=database/grimbanews.sqlite
DB_FOREIGN_KEYS=true

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log

FILESYSTEM_DISK=public
BROADCAST_CONNECTION=log
ENV
    php artisan key:generate --force
fi

# 5. SQLite DB + migrations + seeders
echo "=== SQLite DB + migrations ==="
touch database/grimbanews.sqlite
php artisan migrate --force
php artisan db:seed --class='Database\Seeders\RssFeedsSeeder' --force

# 6. First poll to warm the queue
echo "=== First RSS poll (populates draft queue) ==="
php artisan grimba:poll-feeds || true

# 7. storage link + permissions
echo "=== storage:link + perms ==="
php artisan storage:link || true
chown -R www-data:www-data "$APP_PATH"
chmod -R 775 "$APP_PATH/storage" "$APP_PATH/bootstrap/cache"
chmod 664 "$APP_PATH/database/grimbanews.sqlite"

# 8. nginx vhost (to sites-available only — NOT symlinked)
echo "=== Installing nginx vhost to sites-available ==="
if [ -f "$APP_PATH/deploy/grimbanews.nginx.conf" ]; then
    cp "$APP_PATH/deploy/grimbanews.nginx.conf" /etc/nginx/sites-available/grimbanews
    echo "  Installed at /etc/nginx/sites-available/grimbanews"
    echo "  NOT symlinked yet. When DNS is ready:"
    echo "    ln -s /etc/nginx/sites-available/grimbanews /etc/nginx/sites-enabled/"
    echo "    nginx -t && systemctl reload nginx"
else
    echo "  WARN: deploy/grimbanews.nginx.conf not found in tarball — wire nginx manually."
fi

# 9. Crontab entry
echo "=== Installing root crontab entry (idempotent) ==="
CRON_LINE='* * * * * cd /var/www/grimbanews/current && sudo -u www-data php artisan schedule:run >> /var/log/grimbanews-cron.log 2>&1'
(crontab -l 2>/dev/null | grep -v 'grimbanews/current && sudo -u www-data php artisan schedule:run' ; echo "$CRON_LINE") | crontab -
echo "  Scheduler: every minute → schedule:run → grimba:poll-feeds (every 30 min) + grimba:cleanup-slugs (03:15 daily)"

echo ""
echo "═══ Bootstrap complete ═══"
echo "  Path:   $APP_PATH"
echo "  Vhost:  /etc/nginx/sites-available/grimbanews (DISABLED until DNS + symlink)"
echo "  Cron:   installed; tail /var/log/grimbanews-cron.log after next minute"
echo ""
echo "Next: point DNS → $(hostname -I | awk '{print $1}'), then:"
echo "  ln -s /etc/nginx/sites-available/grimbanews /etc/nginx/sites-enabled/"
echo "  nginx -t && systemctl reload nginx"
echo "  certbot --nginx -d grimbanews.com -d www.grimbanews.com"
