#!/bin/bash
# ============================================================
# NURISK — Server Production Fix Script
# Jalankan di server produksi via SSH:
#   ssh user@nurisk.org
#   bash /tmp/fix_server.sh
# ============================================================

set -e

SHARED_ENV="/var/www/nurisk/shared/.env"
CURRENT_DIR="/var/www/nurisk/current"

echo "=== NURISK SERVER FIX ==="

# --- 1. Perbaiki .env di server ---
echo "[1/4] Fixing .env on server..."
if [ -f "$SHARED_ENV" ]; then
    # Backup dulu
    cp "$SHARED_ENV" "${SHARED_ENV}.bak.$(date +%Y%m%d%H%M%S)"
    echo "      Backup dibuat."

    # Update APP_URL ke HTTPS
    sed -i 's|^APP_URL=.*|APP_URL=https://nurisk.org|' "$SHARED_ENV"
    # Update APP_ENV ke production
    sed -i 's|^APP_ENV=local|APP_ENV=production|' "$SHARED_ENV"
    # Update APP_DEBUG ke false
    sed -i 's|^APP_DEBUG=true|APP_DEBUG=false|' "$SHARED_ENV"

    echo "      .env diperbaiki:"
    grep -E "^APP_URL|^APP_ENV|^APP_DEBUG" "$SHARED_ENV"
else
    echo "ERROR: File $SHARED_ENV tidak ditemukan!"
    echo "Coba cari di: find /var/www/nurisk -name '.env' -maxdepth 4"
    exit 1
fi

# --- 2. Clear + rebuild config cache ---
echo "[2/4] Clearing and rebuilding config cache..."
cd "$CURRENT_DIR"
php artisan config:clear
php artisan config:cache
echo "      Config cache rebuilt."

# --- 3. Clear route cache ---
echo "[3/4] Rebuilding route cache..."
php artisan route:clear
php artisan route:cache
echo "      Route cache rebuilt."

# --- 4. Verifikasi CORS header ---
echo "[4/4] Verifying CORS header..."
CORS_ORIGIN=$(curl -s -I -H "Origin: https://test.com" http://127.0.0.1/api/public/dashboard | grep -i "access-control-allow-origin" || echo "NOT FOUND")
echo "      CORS: $CORS_ORIGIN"

echo ""
echo "=== SELESAI ==="
echo "Silakan test ulang aplikasi NURISK di HP Anda."
