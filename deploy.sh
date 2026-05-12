#!/usr/bin/env bash
set -euo pipefail

# GrimbaNews — Deploy to VPS
# Usage: ./deploy.sh [production]
#
# Pipeline (mirrors the NobuReach deploy 2026-04-23):
#   1. git archive HEAD on the local machine (uses local GitHub auth,
#      so the VPS does NOT need a GitHub deploy key for the private
#      repo darkvaderfr/grimbanews)
#   2. scp the tarball to the VPS
#   3. SSH in and extract into /var/www/grimbanews/current, preserving
#      .env, storage/, bootstrap/cache, vendor/, and the live SQLite DB
#   4. Composer install (no-dev, optimize-autoloader)
#   5. php artisan migrate --force + RssFeedsSeeder (idempotent)
#   6. Clear + rewarm view/route caches (NOT config — Botble's installer
#      middleware reads env() at request time, same gotcha as NobuReach)
#   7. Reset OPcache + reload php-fpm
#
# First-time provisioning: run deploy/bootstrap.sh on the VPS once
# BEFORE the first ./deploy.sh call. It creates the directory tree,
# clones the repo, writes .env + APP_KEY, installs the nginx vhost
# (to sites-available only, so DNS wiring is a separate step),
# and installs the crontab.
#
# No secrets embedded — SSH key must be available in ssh-agent or as a
# configured ~/.ssh/id_*.

ENV="${1:-production}"
VPS_HOST="${VPS_HOST:-209.74.88.135}"
VPS_USER="${VPS_USER:-root}"
VPS_PATH="/var/www/grimbanews/current"

if ! command -v git &> /dev/null; then
    echo "ERROR: git is required"
    exit 1
fi

BRANCH=$(git rev-parse --abbrev-ref HEAD)
SHA=$(git rev-parse --short HEAD)
TS=$(date -u '+%Y%m%d%H%M%S')
TARBALL="/tmp/grimbanews-${SHA}-${TS}.tar.gz"
REMOTE_TARBALL="/tmp/grimbanews-deploy.tar.gz"

echo "═══ GrimbaNews — Deploying ($ENV) ═══"
echo "  Branch: ${BRANCH}"
echo "  SHA:    ${SHA}"
echo "  Target: ${VPS_USER}@${VPS_HOST}:${VPS_PATH}"
echo ""

echo "=== Creating tarball of tracked files at HEAD ==="
git archive --format=tar.gz -o "$TARBALL" HEAD
ls -lh "$TARBALL" | awk '{print "  Size:", $5}'

echo ""
echo "=== Uploading to VPS ==="
scp -q -o StrictHostKeyChecking=no "$TARBALL" "${VPS_USER}@${VPS_HOST}:${REMOTE_TARBALL}"

echo ""
echo "=== Running remote deploy ==="
ssh -o StrictHostKeyChecking=no "${VPS_USER}@${VPS_HOST}" "DEPLOY_SHA='${SHA}' bash -s" << 'REMOTE_SCRIPT'
set -euo pipefail

APP_PATH="/var/www/grimbanews/current"
TARBALL="/tmp/grimbanews-deploy.tar.gz"

if [ ! -d "$APP_PATH" ]; then
    echo "ERROR: $APP_PATH does not exist."
    echo "  Run deploy/bootstrap.sh on the VPS first (first-time provisioning)."
    exit 1
fi

cd "$APP_PATH"

echo "=== Backing up SQLite DB (best effort, gzipped, keeps last 5) ==="
if [ -f database/grimbanews.sqlite ]; then
    mkdir -p database/backups
    BACKUP_FILE="database/backups/grimbanews.$(date -u '+%Y%m%d%H%M%S').sqlite"
    if command -v sqlite3 >/dev/null 2>&1; then
        sqlite3 database/grimbanews.sqlite ".timeout 5000" ".backup '$BACKUP_FILE'" || cp database/grimbanews.sqlite "$BACKUP_FILE"
    else
        cp database/grimbanews.sqlite "$BACKUP_FILE"
    fi
    gzip -9f "$BACKUP_FILE" 2>/dev/null || true
    find database/backups -maxdepth 1 -type f -name 'grimbanews.*.sqlite' -exec gzip -9f {} \; 2>/dev/null || true
    find database/backups -maxdepth 1 -type f \( -name 'grimbanews.*.sqlite.gz' -o -name 'grimbanews.*.sqlite' \) -size -1024k -delete 2>/dev/null || true
    find database/backups -maxdepth 1 -type f \( -name 'grimbanews.*.sqlite.gz' -o -name 'grimbanews.*.sqlite' \) \
        -printf '%T@ %p\n' 2>/dev/null | sort -rn | tail -n +6 | cut -d' ' -f2- | xargs -r rm -f
    du -sh database/backups 2>/dev/null | awk '{print "  Backup store:", $1}'
fi

echo "=== Extracting release (preserves .env, storage/, vendor/, database/*.sqlite) ==="
# git archive never includes untracked files, so .env + storage + database/*.sqlite
# are safe. tar overwrites by default.
tar --no-overwrite-dir -xzf "$TARBALL"
rm -f "$TARBALL"
printf '%s\n' "$DEPLOY_SHA" > REVISION
chown www-data:www-data REVISION 2>/dev/null || true
chmod 664 REVISION 2>/dev/null || true

echo "=== Ensuring storage + cache dirs exist ==="
mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs bootstrap/cache storage/app/public/og
touch storage/logs/laravel.log
chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

echo "=== Composer install (no-dev, optimized) ==="
# --ignore-platform-req=ext-redis: VPS ships php-redis 5.3.7 but
# symfony/cache v7.4.1 wants >=6.1; we don't use redis at runtime
# (cache/session/queue are all file-based in .env). Safe to ignore.
sudo -u www-data composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --ignore-platform-req=ext-redis 2>&1 | tail -5 || true

echo "=== Running migrations (idempotent) ==="
# Incremental deploys: snapshot application is bootstrap-only. Only
# net-new migrations should run here.
sudo -u www-data php artisan migrate --force 2>&1 | tail -10 || true

echo "=== Seeding RSS feeds (idempotent) ==="
sudo -u www-data php artisan db:seed \
    --class='Database\Seeders\RssFeedsSeeder' \
    --force 2>&1 | tail -5 || true

echo "=== Seeding Grimba categories (idempotent) ==="
sudo -u www-data php artisan db:seed \
    --class='Database\Seeders\GrimbaCategoriesSeeder' \
    --force 2>&1 | tail -5 || true

echo "=== Clearing Laravel caches ==="
sudo -u www-data php artisan view:clear  2>/dev/null || true
sudo -u www-data php artisan config:clear 2>/dev/null || true
sudo -u www-data php artisan cache:clear  2>/dev/null || true
sudo -u www-data php artisan route:clear  2>/dev/null || true

echo "=== Rebuilding view + route caches (NOT config) ==="
# Config cache intentionally SKIPPED — Botble's installer middleware
# reads env() at request time (same gotcha as NobuReach).
sudo -u www-data php artisan view:cache   2>/dev/null || true
sudo -u www-data php artisan route:cache  2>/dev/null || true

echo "=== Re-linking storage (idempotent) ==="
sudo -u www-data php artisan storage:link 2>/dev/null || true

echo "=== Fixing permissions ==="
chown -R www-data:www-data storage bootstrap/cache public database 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chmod 664 database/grimbanews.sqlite 2>/dev/null || true

echo "=== Resetting OPcache ==="
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'opcache cleared\n'; }" || true

systemctl reload php8.3-fpm 2>/dev/null \
    || systemctl reload php8.2-fpm 2>/dev/null \
    || service php-fpm reload 2>/dev/null \
    || true

echo ""
echo "=== Deployment Complete ==="
echo "  Path: $APP_PATH"
echo "  Time: $(date -u '+%Y-%m-%d %H:%M:%S UTC')"
REMOTE_SCRIPT

rm -f "$TARBALL"

echo ""
echo "═══ Deployed to ${VPS_HOST} ═══"
echo "  Verify (when DNS is set): curl -sI https://grimbanews.com/ | head -3"
echo "  Via IP (pre-DNS, with explicit Host): curl -sI -H 'Host: grimbanews.com' http://${VPS_HOST}/ | head -3"
echo "  Logs:    ssh ${VPS_USER}@${VPS_HOST} 'tail -f ${VPS_PATH}/storage/logs/laravel.log'"
echo "  Cron:    ssh ${VPS_USER}@${VPS_HOST} 'tail -20 /var/log/syslog | grep grimba'"
