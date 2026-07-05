#!/bin/bash
# /opt/nurisk/scripts/restore.sh
# Disaster Recovery — restore database dari backup terbaru + smoke test
# sudo chmod +x /opt/nurisk/scripts/restore.sh
#
# Usage:
#   ./restore.sh                          # restore dari backup terbaru
#   ./restore.sh /backup/nurisk/sql/nurisk-2026-06-18.sql.gz   # restore spesifik
#
# Workflow:
#   1. Cari backup terbaru (atau pakai argumen)
#   2. Buat database kosong (nurisk_restore)
#   3. Restore dump
#   4. Update .env sementara
#   5. Jalankan php artisan migrate --status
#   6. Jalankan smoke test (health endpoint)
#   7. Laporan hasil

set -euo pipefail

BACKUP_DIR="/backup/nurisk"
APP_DIR="/var/www/nurisk"
RESTORE_DB="nurisk_restore"
TIMESTAMP=$(date +%Y-%m-%d_%H-%M-%S)
LOG_FILE="/var/log/nurisk-restore-${TIMESTAMP}.log"

# === KONFIGURASI — isi sesuai environment ===
DB_HOST="127.0.0.1"
DB_PORT=3306
DB_USER="nurisk_app"
DB_PASS="${NURISK_APP_PASSWORD:?NURISK_APP_PASSWORD not set}"
BACKUP_USER="nurisk_backup"
BACKUP_PASS="${NURISK_BACKUP_PASSWORD:?NURISK_BACKUP_PASSWORD not set}"
# ==============================================

echo "=== NURISK RESTORE DRILL: $TIMESTAMP ===" | tee "$LOG_FILE"

# --- Langkah 1: Tentukan file backup ---
if [ $# -ge 1 ]; then
    DUMP_FILE="$1"
else
    DUMP_FILE=$(ls -t "$BACKUP_DIR/sql"/nurisk-*.sql.gz 2>/dev/null | head -1)
fi

if [ -z "$DUMP_FILE" ] || [ ! -f "$DUMP_FILE" ]; then
    echo "ERROR: Backup file tidak ditemukan." | tee -a "$LOG_FILE"
    echo "Cari: $DUMP_FILE" | tee -a "$LOG_FILE"
    exit 1
fi

DUMP_SIZE=$(du -sh "$DUMP_FILE" | cut -f1)
echo "[1/7] Backup file: $DUMP_FILE ($DUMP_SIZE)" | tee -a "$LOG_FILE"

# --- Langkah 2: Buat database restore ---
echo "[2/7] Creating restore database: $RESTORE_DB ..." | tee -a "$LOG_FILE"
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$BACKUP_USER" -p"$BACKUP_PASS" \
    -e "DROP DATABASE IF EXISTS $RESTORE_DB; CREATE DATABASE $RESTORE_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
echo "      Database $RESTORE_DB created." | tee -a "$LOG_FILE"

# --- Langkah 3: Restore dump ---
echo "[3/7] Restoring dump..." | tee -a "$LOG_FILE"
RESTORE_START=$(date +%s)
if gunzip -c "$DUMP_FILE" | mysql -h "$DB_HOST" -P "$DB_PORT" -u "$BACKUP_USER" -p"$BACKUP_PASS" "$RESTORE_DB"; then
    RESTORE_END=$(date +%s)
    RESTORE_SECS=$((RESTORE_END - RESTORE_START))
    echo "      Restore selesai dalam ${RESTORE_SECS}s." | tee -a "$LOG_FILE"
else
    echo "ERROR: Restore failed!" | tee -a "$LOG_FILE"
    exit 1
fi

# --- Langkah 4: Hitung tabel yang ter-restore ---
TABLE_COUNT=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$BACKUP_USER" -p"$BACKUP_PASS" \
    -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$RESTORE_DB';")
echo "[4/7] Tables restored: $TABLE_COUNT" | tee -a "$LOG_FILE"

# --- Langkah 5: Migration status (gunakan .env sementara) ---
echo "[5/7] Checking migration status..." | tee -a "$LOG_FILE"
cd "$APP_DIR"

# Backup .env asli
cp .env ".env.backup-${TIMESTAMP}"

# Buat .env sementara untuk konek ke restore database
cat > .env << EOF
APP_ENV=local
APP_DEBUG=false
APP_KEY=$(grep ^APP_KEY .env.backup-${TIMESTAMP} | head -1 | cut -d= -f2-)
DB_CONNECTION=mysql
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_DATABASE=$RESTORE_DB
DB_USERNAME=$BACKUP_USER
DB_PASSWORD=$BACKUP_PASS
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
EOF

MIGRATE_OUTPUT=$(php artisan migrate --status --no-interaction 2>&1 || true)
echo "$MIGRATE_OUTPUT" | tee -a "$LOG_FILE"

# Check if any migration is pending
if echo "$MIGRATE_OUTPUT" | grep -q "\[N\]"; then
    echo "WARNING: ${RESTORE_DB} has pending migrations." | tee -a "$LOG_FILE"
    echo "         Jalankan: php artisan migrate --force" | tee -a "$LOG_FILE"
    MIGRATION_STATUS="pending"
else
    echo "OK: All migrations are current." | tee -a "$LOG_FILE"
    MIGRATION_STATUS="current"
fi

# --- Langkah 6: Smoke test (health endpoint) ---
echo "[6/7] Smoke test..." | tee -a "$LOG_FILE"
php artisan config:clear --quiet 2>/dev/null
php artisan route:clear --quiet 2>/dev/null

php artisan serve --port=9876 --no-reload &>/dev/null &
SERVE_PID=$!
sleep 2

HEALTH=$(curl -sf http://127.0.0.1:9876/health 2>&1 || echo "FAILED")
kill "$SERVE_PID" 2>/dev/null || true

if [ "$HEALTH" = "FAILED" ]; then
    echo "SMOKE TEST: FAILED — health endpoint tidak merespon." | tee -a "$LOG_FILE"
    SMOKE_STATUS="fail"
else
    echo "SMOKE TEST: OK — $HEALTH" | tee -a "$LOG_FILE"
    SMOKE_STATUS="pass"
fi

# --- Langkah 7: Restore .env asli + cleanup ---
mv ".env.backup-${TIMESTAMP}" .env
echo "[7/7] Cleanup: .env restored, temporary config removed." | tee -a "$LOG_FILE"

# --- Laporan ---
TOTAL_SECS=$(($(date +%s) - $(date -d "$(echo $TIMESTAMP | tr '_' ' ')" +%s 2>/dev/null || echo 0)))
echo "" | tee -a "$LOG_FILE"
echo "========================================" | tee -a "$LOG_FILE"
echo "  RESTORE DRILL COMPLETE" | tee -a "$LOG_FILE"
echo "========================================" | tee -a "$LOG_FILE"
echo "  Backup file:  $DUMP_FILE" | tee -a "$LOG_FILE"
echo "  Restore DB:   $RESTORE_DB" | tee -a "$LOG_FILE"
echo "  Tables:       $TABLE_COUNT" | tee -a "$LOG_FILE"
echo "  Migrations:   $MIGRATION_STATUS" | tee -a "$LOG_FILE"
echo "  Smoke test:   $SMOKE_STATUS" | tee -a "$LOG_FILE"
echo "  Duration:     ${TOTAL_SECS}s" | tee -a "$LOG_FILE"
echo "  RTO target:   < 30 menit" | tee -a "$LOG_FILE"
echo "========================================" | tee -a "$LOG_FILE"

if [ "$SMOKE_STATUS" != "pass" ]; then
    echo "ERROR: Restore drill gagal — smoke test tidak lulus." | tee -a "$LOG_FILE"
    exit 1
fi

if [ "$MIGRATION_STATUS" = "pending" ]; then
    echo "WARNING: Restore berhasil tapi ada migration pending." | tee -a "$LOG_FILE"
fi

echo "RESTORE DRILL PASSED." | tee -a "$LOG_FILE"
