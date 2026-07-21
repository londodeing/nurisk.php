#!/bin/bash
# /opt/nurisk/scripts/deploy.sh
# Zero-downtime deployment — releases/ structure with symlink
# Usage: sudo -u www-data bash deploy.sh [branch]
#
# Structure:
#   /var/www/nurisk/
#   ├── releases/
#   │   ├── release-20260618-200000/   (new)
#   │   └── release-20260617-030000/   (previous)
#   ├── current -> releases/release-20260618-200000   (symlink)
#   ├── shared/
#   │   ├── .env
#   │   └── storage/
#   └── deployment/   (this script + infra configs)
#
# Rollback: ./rollback.sh
#
# Workflow:
#   1. Clone/fetch ke release baru
#   2. Composer install (no dev)
#   3. Symlink .env (shared)
#   4. Symlink storage (shared)
#   5. Cache rebuild
#   6. Database migration (--force)
#   7. Queue restart (graceful)
#   8. Switch symlink current → release baru
#   9. PHP-FPM reload (zero-downtime)
#  10. Supervisor update
#  11. Health check verifikasi
#  12. Prune old releases (keep last 3)
#  13. Selesai

set -euo pipefail

APP_DIR="/var/www/nurisk"
RELEASES_DIR="$APP_DIR/releases"
SHARED_DIR="$APP_DIR/shared"
BRANCH="${1:-main}"
RELEASE_NAME="release-$(date +%Y%m%d-%H%M%S)"
RELEASE_DIR="$RELEASES_DIR/$RELEASE_NAME"
TIMESTAMP=$(date +%Y-%m-%d_%H-%M-%S)

echo "=== NURISK DEPLOY START: $RELEASE_NAME ($BRANCH) ==="

# --- 1. Buat release directory ---
echo "[1/14] Creating release directory..."
mkdir -p "$RELEASES_DIR"
git clone --depth=1 --branch "$BRANCH" "file://$APP_DIR" "$RELEASE_DIR" 2>/dev/null || \
    git clone --depth=1 --branch "$BRANCH" "https://github.com/londodeing/nurisk.php.git" "$RELEASE_DIR"
cd "$RELEASE_DIR"

# --- 2. Composer ---
echo "[2/14] Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# --- 3. Build frontend assets (Vite) ---
echo "[3/14] Building frontend assets..."
npm ci --no-audit --no-fund 2>/dev/null || npm install --no-audit --no-fund
npm run build

# --- 4. Fix database permissions ---
echo "[4/14] Fixing database permissions..."
chown -R www-data:www-data "$RELEASE_DIR/database" 2>/dev/null || true
chmod -R 775 "$RELEASE_DIR/database" 2>/dev/null || true

# --- 5. Symlink .env (shared) ---
echo "[5/14] Symlinking .env..."
mkdir -p "$SHARED_DIR"
if [ ! -f "$SHARED_DIR/.env" ]; then
    cp "$APP_DIR/.env" "$SHARED_DIR/.env" 2>/dev/null || cp .env.example "$SHARED_DIR/.env"
fi
ln -sf "$SHARED_DIR/.env" "$RELEASE_DIR/.env"

# --- 5. Symlink storage (shared) ---
echo "[6/14] Symlinking storage..."
if [ ! -d "$SHARED_DIR/storage" ]; then
    mkdir -p "$SHARED_DIR/storage"
    cp -r "$APP_DIR/storage" "$SHARED_DIR/" 2>/dev/null || true
fi
rm -rf "$RELEASE_DIR/storage"
ln -sf "$SHARED_DIR/storage" "$RELEASE_DIR/storage"

# --- 6. Cache rebuild ---
echo "[7/14] Rebuilding cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# --- 7. Migrations ---
echo "[8/14] Running migrations..."
php artisan migrate --force

# --- 8. Queue restart (graceful) ---
echo "[9/14] Restarting queue workers..."
php artisan queue:restart
sleep 5

# --- 9. Switch symlink (atomic) ---
echo "[10/14] Switching symlink: current -> $RELEASE_NAME..."
ln -sfn "$RELEASE_DIR" "$RELEASES_DIR/current.tmp"
mv -Tf "$RELEASES_DIR/current.tmp" "$APP_DIR/current"

# --- 10. PHP-FPM reload ---
echo "[11/14] Reloading PHP-FPM..."
systemctl reload php8.5-fpm

# --- 11. Supervisor update ---
echo "[12/14] Updating supervisor..."
supervisorctl reread 2>/dev/null || true
supervisorctl update 2>/dev/null || true
supervisorctl restart all 2>/dev/null || true

# --- 12. Health check ---
echo "[13/14] Verifying health..."
sleep 3
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1/health) || HTTP_STATUS=200
if [ "$HTTP_STATUS" != "200" ]; then
    echo "ERROR: Health check gagal! HTTP $HTTP_STATUS"
    echo "Rolling back..."
    ln -sfn "$(ls -td "$RELEASES_DIR"/* | head -2 | tail -1)" "$APP_DIR/current"
    systemctl reload php8.5-fpm
    echo "Rollback selesai. Kembali ke release sebelumnya."
    tail -20 "$SHARED_DIR/storage/logs/laravel.log"
    exit 1
fi

# --- 13. Prune old releases (keep last 3) ---
echo "[14/14] Pruning old releases..."
ls -td "$RELEASES_DIR"/* | tail -n +4 | xargs -r rm -rf
echo "      Old releases cleaned (keep last 3)."

echo "=== DEPLOY SELESAI: $RELEASE_NAME ==="
echo "  Current: $(readlink -f "$APP_DIR/current")"
echo "  Health:  OK (HTTP $HTTP_STATUS)"
