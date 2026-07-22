#!/bin/bash
# /opt/nurisk/scripts/rollback.sh
# Rollback to previous release (releases/ structure)
# sudo chmod +x /opt/nurisk/scripts/rollback.sh
# Usage: ./rollback.sh                    # rollback ke 1 release sebelumnya
#        ./rollback.sh 2                  # rollback ke 2 release sebelumnya
#
# Prerequisites:
#   deploy.sh sudah menggunakan releases/ structure
#   /var/www/nurisk/
#   ├── current -> releases/release-XXXX/   (symlink aktif)
#   ├── releases/
#   │   ├── release-YYYY
#   │   └── release-ZZZZ
#   └── shared/
#
# Rollback aman:
#   1. Identifikasi release target (sebelum current)
#   2. Verifikasi target ada
#   3. Migration rollback (opsional — skip jika hanya kode)
#   4. Switch symlink
#   5. PHP-FPM reload (zero-downtime)
#   6. Health check
#   7. Log hasil

set -euo pipefail

APP_DIR="/var/www/nurisk"
RELEASES_DIR="$APP_DIR/releases"
STEPS_BACK="${1:-1}"
TIMESTAMP=$(date +%Y-%m-%d_%H-%M-%S)

echo "=== NURISK ROLLBACK START: $TIMESTAMP ==="

# --- 1. Cek current release ---
if [ ! -L "$APP_DIR/current" ] && [ ! -d "$APP_DIR/current" ]; then
    echo "ERROR: $APP_DIR/current tidak ditemukan. Bukan releases/ structure."
    exit 1
fi

CURRENT_RELEASE=$(readlink -f "$APP_DIR/current")
CURRENT_NAME=$(basename "$CURRENT_RELEASE")
echo "[1/6] Current: $CURRENT_NAME"

# --- 2. Cari target rollback ---
RELEASES=($(ls -td "$RELEASES_DIR"/* 2>/dev/null || true))
if [ ${#RELEASES[@]} -eq 0 ]; then
    echo "ERROR: Tidak ada release di $RELEASES_DIR."
    exit 1
fi

TARGET_RELEASE=""
TARGET_IDX=-1
for i in "${!RELEASES[@]}"; do
    if [ "$(readlink -f "${RELEASES[$i]}")" = "$CURRENT_RELEASE" ]; then
        TARGET_IDX=$((i + STEPS_BACK))
        break
    fi
done

if [ "$TARGET_IDX" -lt 0 ] || [ "$TARGET_IDX" -ge "${#RELEASES[@]}" ]; then
    echo "ERROR: Tidak ada release ${STEPS_BACK} langkah sebelum $CURRENT_NAME."
    echo "  Releases tersedia:"
    for r in "${RELEASES[@]}"; do
        echo "    - $(basename "$r")"
    done
    exit 1
fi

TARGET_RELEASE="${RELEASES[$TARGET_IDX]}"
TARGET_NAME=$(basename "$TARGET_RELEASE")

echo "[2/6] Target: $TARGET_NAME"

# --- 3. Verifikasi target valid ---
if [ ! -f "$TARGET_RELEASE/artisan" ]; then
    echo "ERROR: Target $TARGET_NAME bukan release Laravel yang valid (artisan tidak ditemukan)."
    exit 1
fi
echo "[3/6] Target valid: $TARGET_NAME"

# --- 4. Optional: Tanya migration rollback ---
# (skipped — production biasanya tidak rollback migration otomatis)
echo "[4/6] Migration rollback: SKIP (manual jika diperlukan)"

# --- 5. Switch symlink (atomic) ---
echo "[5/6] Switching symlink: $TARGET_NAME ..."
ln -sfn "$TARGET_RELEASE" "$RELEASES_DIR/current.tmp"
mv -Tf "$RELEASES_DIR/current.tmp" "$APP_DIR/current"

# PHP-FPM reload
sudo systemctl reload php8.5-fpm

# Queue restart — worker akan pakai kode dari release baru (lama)
php artisan queue:restart --quiet 2>/dev/null || true

echo "      Symlink switched: $CURRENT_NAME -> $TARGET_NAME"

# --- 6. Health check ---
echo "[6/6] Verifying health..."
sleep 3
cd "$TARGET_RELEASE"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1/health)
if [ "$HTTP_STATUS" != "200" ]; then
    echo "WARNING: Health check after rollback: HTTP $HTTP_STATUS"
    echo "         Rollback tetap dijalankan. Periksa log."
fi

echo ""
echo "=== ROLLBACK SELESAI: $TIMESTAMP ==="
echo "  From:    $CURRENT_NAME"
echo "  To:      $TARGET_NAME"
echo "  Health:  HTTP $HTTP_STATUS"
echo ""
echo "  Catatan:"
echo "  - Migration TIDAK di-rollback otomatis."
echo "  - Jika rollback karena migration bermasalah:"
echo "    php artisan migrate:rollback --step=1"
echo "  - Jika rollback karena kode bermasalah: cukup restart queue."
