#!/bin/bash
# /opt/nurisk/scripts/backup.sh
# Production backup script — database + files
# sudo chmod +x /opt/nurisk/scripts/backup.sh
# Cron: 0 3 * * * root /opt/nurisk/scripts/backup.sh >> /var/log/nurisk-backup.log 2>&1
#
# RPO: 24 jam (backup harian)
# RTO: <2 jam (restore dari backup terakhir)
# Retensi: 30 hari

set -euo pipefail

BACKUP_DIR="/backup/nurisk"
DATE=$(date +%Y-%m-%d)
APP_DIR="/var/www/nurisk"
STORAGE_DIR="/var/nurisk-assets"

# === KONFIGURASI — isi sesuai environment ===
DB_NAME="nurisk"
DB_USER="nurisk_backup"
DB_PASS="${NURISK_BACKUP_PASSWORD:?NURISK_BACKUP_PASSWORD not set}"
RETENTION_DAYS=30
# ==============================================

echo "=== NURISK BACKUP START: $(date +%Y-%m-%d_%H-%M-%S) ==="

# 1. Buat direktori backup
mkdir -p "$BACKUP_DIR/sql"
mkdir -p "$BACKUP_DIR/files"

# 2. Backup database — single-transaction agar tidak lock tabel
echo "[$(date +%H:%M:%S)] Dumping database..."
if mysqldump \
    --user="$DB_USER" \
    --password="$DB_PASS" \
    --host=127.0.0.1 \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --routines \
    --triggers \
    "$DB_NAME" \
    | gzip > "$BACKUP_DIR/sql/nurisk-$DATE.sql.gz"
then
    SQL_SIZE=$(du -sh "$BACKUP_DIR/sql/nurisk-$DATE.sql.gz" | cut -f1)
    echo "[$(date +%H:%M:%S)] Database OK: $SQL_SIZE"
else
    echo "[$(date +%H:%M:%S)] Database FAILED"
    exit 1
fi

# 3. Backup files — rsync dari storage
echo "[$(date +%H:%M:%S)] Syncing files..."
if rsync -avz --delete \
    "$STORAGE_DIR/" \
    "$BACKUP_DIR/files/"
then
    echo "[$(date +%H:%M:%S)] Files OK"
else
    echo "[$(date +%H:%M:%S)] Files rsync FAILED"
    exit 1
fi

# 4. Hapus backup lebih dari RETENTION_DAYS
find "$BACKUP_DIR/sql" -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
echo "[$(date +%H:%M:%S)] Cleanup: backup > $RETENTION_DAYS hari dihapus."

# 5. Cek disk usage
DISK_PCT=$(df "$BACKUP_DIR" | tail -1 | awk '{print $5}' | tr -d '%')
if [ "$DISK_PCT" -gt 80 ]; then
    echo "WARNING: Disk backup $DISK_PCT% penuh!"
fi
echo "[$(date +%H:%M:%S)] Disk usage: ${DISK_PCT}%"

echo "=== NURISK BACKUP SELESAI: $(date +%Y-%m-%d_%H-%M-%S) ==="
