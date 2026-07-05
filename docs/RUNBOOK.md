# RUNBOOK — NURISK Production Operations

**App**: NURISK (Laravel + Sanctum + SQLite/MySQL)  
**Domain**: `https://nurisk.or.id`  
**Server**: `/var/www/nurisk/current` (symlink)  
**PHP**: 8.3, **DB**: MySQL (`nurisk`) or SQLite, **Queue**: Database  
**Supervisor**: `nurisk-queue-pdf` (2 workers), `nurisk-queue-default` (1 worker)  
**Tables**: `auth_users`, `personal_access_tokens`, `mobile_devices`, `sync_cursors`, `jobs`, `failed_jobs`, `job_batches`

---

## Daftar Isi

1. [Antrean Macet (Queue Stuck)](#1-antrean-macet-queue-stuck)
2. [Database Turun (DB Down)](#2-database-turun-db-down)
3. [Penyimpanan Penuh (Storage Full)](#3-penyimpanan-penuh-storage-full)
4. [Kebocoran Token (Token Leak)](#4-kebocoran-token-token-leak)
5. [Backup & Restore](#5-backup--restore)
6. [Rollback Deployment](#6-rollback-deployment)
7. [Health Check Endpoint](#7-health-check-endpoint)

---

## 1. Antrean Macet (Queue Stuck)

**Symptoms**: Jobs stay in `jobs` table with `reserved_at` set but never complete. PDFs not generated. Supervisor workers show `RUNNING` but consume no CPU.

### 1.1 Cek panjang antrean

```bash
# Total pending jobs (reserved_at IS NULL)
php artisan tinker --execute="echo DB::table('jobs')->whereNull('reserved_at')->count() . ' pending\n';"

# Jobs stuck in reserved state (reserved_at IS NOT NULL)
php artisan tinker --execute="echo DB::table('jobs')->whereNotNull('reserved_at')->count() . ' stuck\n';"

# Per-queue breakdown
php artisan tinker --execute="print_r(DB::table('jobs')
    ->select('queue', DB::raw('count(*) as total'), DB::raw('SUM(CASE WHEN reserved_at IS NOT NULL THEN 1 ELSE 0 END) as stuck'))
    ->groupBy('queue')->get()->toArray());"
```

**Expected output** (when healthy):
```
default     | 0  | 0
pdf-generation | 0  | 0
```

### 1.2 Periksa status Supervisor

```bash
sudo supervisorctl status | grep nurisk

# Expected:
# nurisk-queue-pdf:pdf_00     RUNNING   pid 12345, uptime 2:30:00
# nurisk-queue-pdf:pdf_01     RUNNING   pid 12346, uptime 2:30:00
# nurisk-queue-default:default_00  RUNNING   pid 12347, uptime 2:30:00
```

### 1.3 Cek log worker untuk error

```bash
# PDF queue logs
tail -100 /var/www/nurisk/storage/logs/queue-pdf.log

# Default queue logs
tail -100 /var/www/nurisk/storage/logs/queue-default.log

# Laravel log
tail -100 /var/www/nurisk/storage/logs/laravel.log
```

### 1.4 Lepas semua job yang stuck (reserved)

```php
# Lepas semua job yang terkunci: set reserved_at = NULL, kurangi attempts agar bisa diproses ulang
php artisan tinker
```

```php
DB::table('jobs')->whereNotNull('reserved_at')->update([
    'reserved_at' => null,
    'attempts' => DB::raw('attempts - 1'),
]);
```

**Expected output**: `= 5` (jumlah baris yang diupdate)

### 1.5 Restart semua worker (graceful)

```bash
# Kirim sinyal restart — worker selesai job aktif lalu restart
php artisan queue:restart
sleep 5
sudo supervisorctl restart all
sudo supervisorctl status | grep nurisk
```

### 1.6 Hapus job gagal

```bash
# Lihat jumlah failed jobs
php artisan queue:failed-table
php artisan tinker --execute="echo DB::table('failed_jobs')->count() . ' failed jobs\n';"

# Flush semua failed jobs
php artisan queue:flush

# Atau hapus per queue
php artisan queue:forget <uuid>

# Verifikasi
php artisan tinker --execute="echo DB::table('failed_jobs')->count() . ' remaining\n';"
```

### 1.7 Hapus job tertentu dari queue

```php
# Hapus semua job di queue 'pdf-generation'
php artisan tinker --execute="DB::table('jobs')->where('queue', 'pdf-generation')->delete();"
```

---

## 2. Database Turun (DB Down)

**Symptoms**: 500 errors, health check returns `database: fail`.  
**Possible causes**: MySQL service down, SQLite file corruption, disk full, connection limit exceeded.

### 2.1 Diagnosa

```bash
# MySQL — cek service
sudo systemctl status mysql

# Test koneksi via artisan
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'DB OK\n'; } catch (\Exception \$e) { echo 'DB FAIL: ' . \$e->getMessage() . '\n'; }"
```

**Expected output** (healthy): `DB OK`

```bash
# MySQL — cek koneksi langsung
mysql -h 127.0.0.1 -u nurisk_app -p -e "SELECT 1 AS test"
```

```bash
# SQLite — cek file
ls -lh "$(php -r 'echo config("database.connections.sqlite.database");')"
sqlite3 "$(php -r 'echo config("database.connections.sqlite.database");')" "SELECT 1;" 2>&1
```

### 2.2 Perbaikan MySQL

```bash
# Restart service
sudo systemctl restart mysql

# Cek error log
sudo tail -100 /var/log/mysql/error.log

# Cek koneksi maksimal
mysql -u nurisk_app -p -e "SHOW VARIABLES LIKE 'max_connections';"
mysql -u nurisk_app -p -e "SHOW STATUS LIKE 'Threads_connected';"

# Jika koneksi habis, naikkan batas (edit /etc/mysql/mariadb.conf.d/50-server.cnf)
# [mysqld]
# max_connections = 500
# Atau set global tanpa restart:
mysql -u root -p -e "SET GLOBAL max_connections = 500;"
```

### 2.3 Perbaikan SQLite

```bash
# Cek integritas database
DB_PATH="$(php -r 'echo config("database.connections.sqlite.database");')"
sqlite3 "$DB_PATH" "PRAGMA integrity_check;"

# Jika korupsi: restore dari backup
cp /backup/nurisk/sqlite/nurisk-$(date +%Y-%m-%d).sqlite "$DB_PATH"

# Jika error "database is locked":
# Matikan semua proses yang akses file
sudo lsof "$DB_PATH"
# Kill proses yang mengunci
sudo kill -9 <PID>
```

### 2.4 Aktifkan mode read-only (saat DB down)

```php
# Buat maintenance page dengan akses read-only
# Laravel tidak punya "read-only mode" built-in. Pendekatan:

# 1. Aktifkan maintenance mode dengan retry
php artisan down --retry=60 --message="Database maintenance — silakan coba lagi dalam 1 menit"

# 2. Atau gunakan .env sementara untuk switch ke SQLite cadangan
#    (hanya jika main DB adalah MySQL)
```

### 2.5 Recovery penuh

```bash
# 1. Pastikan service hidup
sudo systemctl restart mysql

# 2. Verifikasi koneksi
php artisan tinker --execute="DB::connection()->getPdo(); echo 'RECOVERED\n';"

# 3. Matikan maintenance mode
php artisan up
```

---

## 3. Penyimpanan Penuh (Storage Full)

**Symptoms**: 503 errors, failed PDF generation, SQLite WAL files grow unbounded, health check returns `disk.degraded`.

### 3.1 Cek penggunaan disk

```bash
# Overview
df -h /

# Per direktori
du -sh /var/www/nurisk/storage/* | sort -rh

# Periksa inode
df -i /
```

**Expected output** warning at: `used_pct > 85`

### 3.2 Temukan file besar

```bash
# 10 file terbesar di storage
find /var/www/nurisk/storage -type f -exec ls -lhS {} + | head -20

# File .log > 100MB
find /var/www/nurisk/storage/logs -name "*.log" -size +100M -exec ls -lh {} +

# SQLite WAL / SHM files
find /var/www/nurisk/database -name "*.sqlite-wal" -o -name "*.sqlite-shm" | xargs ls -lh

# PDF output sementara
find /var/www/nurisk/storage -name "*.pdf" -size +10M -exec ls -lh {} +
```

### 3.3 Truncate log dengan aman

```bash
# Laravel log — kosongkan tanpa hapus file (biarkan logrotate jalan)
sudo truncate -s 0 /var/www/nurisk/storage/logs/laravel.log
sudo truncate -s 0 /var/www/nurisk/storage/logs/queue-pdf.log
sudo truncate -s 0 /var/www/nurisk/storage/logs/queue-default.log

# Nginx logs
sudo truncate -s 0 /var/log/nginx/nurisk.access.log
sudo truncate -s 0 /var/log/nginx/nurisk.error.log

# Paksa logrotate
sudo logrotate -f /etc/logrotate.d/nurisk
```

**Catatan**: Jangan `rm` file log. `truncate -s 0` aman karena Laravel dan Nginx tetap bisa write ke file yang sama.

### 3.4 Bersihkan SQLite WAL/SHM

```bash
# SQLite WAL journal — checkpoint agar WAL diserap ke main file
DB_PATH="$(php -r 'echo config("database.connections.sqlite.database");')"
sqlite3 "$DB_PATH" "PRAGMA wal_checkpoint(TRUNCATE);"

# Hapus WAL/SHM secara manual (hanya jika tidak ada proses aktif)
php artisan down --retry=30
rm -f "${DB_PATH}-wal" "${DB_PATH}-shm" 2>/dev/null || true
php artisan up

# Atau jika file SQLite cache terpisah:
ls -lh /var/www/nurisk/storage/framework/cache/*.sqlite*
```

### 3.5 Archive PDF lama

```bash
# Pindahkan PDF > 30 hari ke backup storage
ARCHIVE_DIR="/backup/nurisk/pdf-archive"
mkdir -p "$ARCHIVE_DIR"
find /var/www/nurisk/storage/app/public/pdfs -type f -name "*.pdf" -mtime +30 -exec mv {} "$ARCHIVE_DIR/" \;

# Hapus dari archive > 90 hari
find "$ARCHIVE_DIR" -type f -name "*.pdf" -mtime +90 -delete
```

### 3.6 Bersihkan cache Laravel

```bash
# File cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

# Hapus compiled templates
rm -rf /var/www/nurisk/storage/framework/views/*.php
rm -rf /var/www/nurisk/storage/framework/cache/data/*
```

---

## 4. Kebocoran Token (Token Leak)

**Symptoms**: Unauthorized access detected, suspicious API calls from unknown devices, token leaked in logs or client-side storage.

### 4.1 Temukan token per user

```sql
-- Semua token aktif per user
SELECT 
    pat.id,
    pat.tokenable_id AS id_pengguna,
    au.no_hp AS pengguna_nohp,
    pat.name AS token_name,
    pat.device_uuid,
    pat.last_used_at,
    pat.expires_at,
    pat.created_at
FROM personal_access_tokens pat
LEFT JOIN auth_users au ON au.id_pengguna = pat.tokenable_id
WHERE (pat.expires_at IS NULL OR pat.expires_at > NOW())
  AND pat.tokenable_type = 'App\\Models\\AuthUser'
ORDER BY pat.last_used_at DESC NULLS LAST;
```

**Expected output**:
```
 id | id_pengguna | pengguna_nohp | token_name    | device_uuid | last_used_at
----+-------------+---------------+----------------+-------------+---------------------
 42 | 7           | 6281234567890 | Mobile-Device | abc123...   | 2026-06-20 10:30:00
```

### 4.2 Temukan token tanpa device_uuid (tidak terikat device)

```sql
SELECT id, tokenable_id, name, device_uuid, last_used_at, expires_at
FROM personal_access_tokens
WHERE device_uuid IS NULL
  AND (expires_at IS NULL OR expires_at > NOW())
  AND tokenable_type = 'App\\Models\\AuthUser';
```

### 4.3 Cabut semua token untuk satu user

```bash
# Revoke semua token user dengan id_pengguna = 7
php artisan tinker
```

```php
use App\Models\AuthUser;
$user = AuthUser::find(7);
$revoked = $user->tokens()->delete();
echo "Revoked {$revoked} tokens for user {$user->id_pengguna}\n";
```

### 4.4 Cabut token berdasarkan device_uuid

```php
// Di tinker
$deviceUuid = 'abc123-def456';
$deleted = DB::table('personal_access_tokens')
    ->where('device_uuid', $deviceUuid)
    ->delete();
echo "Revoked {$deleted} tokens for device {$deviceUuid}\n";
```

```bash
# Juga nonaktifkan device di mobile_devices
php artisan tinker --execute="
DB::table('mobile_devices')
    ->where('uuid_device', 'abc123-def456')
    ->update(['status' => 'revoked']);
echo 'Device revoked\n';
"
```

### 4.5 Cabut semua token untuk user by no_hp

```bash
php artisan tinker
```

```php
use App\Models\AuthUser;
$user = AuthUser::where('no_hp', '6281234567890')->first();
if ($user) {
    $count = $user->tokens()->delete();
    echo "Revoked {$count} tokens for {$user->no_hp}\n";
}
```

### 4.6 Prune token yang expired

```bash
# Sanctum built-in: hapus semua token expired
php artisan sanctum:prune-expired --hours=0

# Verifikasi
php artisan tinker --execute="
echo DB::table('personal_access_tokens')
    ->where('expires_at', '<', now())
    ->count() . ' expired tokens remaining\n';
"
```

**Expected output**: `0 expired tokens remaining`

### 4.7 Audit log pencabutan

```sql
SELECT * FROM sync_audit_logs 
WHERE dibuat_pada > NOW() - INTERVAL 1 HOUR
ORDER BY dibuat_pada DESC 
LIMIT 20;
```

---

## 5. Backup & Restore

### 5.1 Backup Database MySQL

```bash
# Backup dengan mysqldump — single-transaction agar tidak lock
BACKUP_DIR="/backup/nurisk/sql"
mkdir -p "$BACKUP_DIR"
DATE=$(date +%Y-%m-%d)

mysqldump \
    --user=nurisk_backup \
    --password="${NURISK_BACKUP_PASSWORD}" \
    --host=127.0.0.1 \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --routines \
    --triggers \
    nurisk \
    | gzip > "$BACKUP_DIR/nurisk-${DATE}.sql.gz"

# Cek hasil
ls -lh "$BACKUP_DIR/nurisk-${DATE}.sql.gz"
```

**Expected output**: `-rw-r--r-- ... nurisk-2026-06-20.sql.gz (size)`

### 5.2 Backup Database SQLite

```bash
# SQLite — cukup cp file (pastikan tidak ada write saat backup)
BACKUP_DIR="/backup/nurisk/sqlite"
mkdir -p "$BACKUP_DIR"

php artisan down --retry=30
DB_PATH="$(php -r 'echo config("database.connections.sqlite.database");')"
cp "$DB_PATH" "$BACKUP_DIR/nurisk-$(date +%Y-%m-%d).sqlite"
php artisan up

# Compress
gzip -f "$BACKUP_DIR/nurisk-$(date +%Y-%m-%d).sqlite"
ls -lh "$BACKUP_DIR/nurisk-$(date +%Y-%m-%d).sqlite.gz"
```

### 5.3 Backup .env dan file konfigurasi

```bash
BACKUP_DIR="/backup/nurisk/config"
mkdir -p "$BACKUP_DIR"
cp /var/www/nurisk/shared/.env "$BACKUP_DIR/.env.$(date +%Y-%m-%d)"
cp /var/www/nurisk/shared/.env "$BACKUP_DIR/.env.$(date +%Y-%m-%d).bak"

# Juga backup nginx + supervisor config
sudo cp /etc/nginx/sites-available/nurisk "$BACKUP_DIR/nginx.nurisk.$(date +%Y-%m-%d).conf"
sudo cp /etc/supervisor/conf.d/nurisk-queue.conf "$BACKUP_DIR/"
```

### 5.4 Restore Database MySQL

```bash
# Cari backup terbaru
RESTORE_FILE=$(ls -t /backup/nurisk/sql/nurisk-*.sql.gz | head -1)
echo "Restoring from: $RESTORE_FILE"

# Buat database fresh
mysql -h 127.0.0.1 -u root -p \
    -e "DROP DATABASE IF EXISTS nurisk_restore; CREATE DATABASE nurisk_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# Restore
gunzip -c "$RESTORE_FILE" | mysql -h 127.0.0.1 -u root -p nurisk_restore

# Ganti .env dengan DB sementara, lalu jalankan migration check
cd /var/www/nurisk/current
cp shared/.env shared/.env.backup
```

```bash
# Update .env untuk pakai DB restore:
# DB_DATABASE=nurisk_restore

# Cek migration status
php artisan migrate --status

# Jika aman, ganti DB asli:
mysql -h 127.0.0.1 -u root -p \
    -e "DROP DATABASE nurisk; CREATE DATABASE nurisk CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

gunzip -c "$RESTORE_FILE" | mysql -h 127.0.0.1 -u root -p nurisk

# Kembalikan .env asli
cp shared/.env.backup shared/.env

# Verifikasi
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK\n';"
```

### 5.5 Restore Database SQLite

```bash
php artisan down --retry=30

RESTORE_FILE=$(ls -t /backup/nurisk/sqlite/nurisk-*.sqlite.gz | head -1)
gunzip -c "$RESTORE_FILE" > /tmp/nurisk-restored.sqlite

DB_PATH="$(php -r 'echo config("database.connections.sqlite.database");')"
cp /tmp/nurisk-restored.sqlite "$DB_PATH"
rm /tmp/nurisk-restored.sqlite

php artisan migrate --status
php artisan up
```

### 5.6 Cron backup otomatis

```cron
# /etc/cron.d/nurisk-backup — backup setiap hari jam 03:00
0 3 * * * root /opt/nurisk/scripts/backup.sh >> /var/log/nurisk-backup.log 2>&1
```

**Retensi**: 30 hari (diatur di `backup.sh` via `find ... -mtime +30 -delete`)

---

## 6. Rollback Deployment

**Prerequisites**: Deployment menggunakan `releases/` structure (`/var/www/nurisk/releases/release-*`).

### 6.1 Rollback penuh (script otomatis)

```bash
# Rollback 1 release ke belakang
sudo -u www-data bash /opt/nurisk/scripts/rollback.sh

# Rollback 2 release ke belakang
sudo -u www-data bash /opt/nurisk/scripts/rollback.sh 2
```

### 6.2 Rollback manual — langkah demi langkah

```bash
APP_DIR="/var/www/nurisk"
RELEASES_DIR="$APP_DIR/releases"

# 1. Mode maintenance
cd "$APP_DIR/current"
php artisan down --retry=60 --message="Rollback deployment — akan selesai dalam 1 menit"

# 2. Identifikasi release saat ini dan target
echo "Current: $(readlink -f "$APP_DIR/current")"
ls -td "$RELEASES_DIR"/* | head -5

# 3. Git revert (jika perlu rollback kode)
git log --oneline -5
git revert --no-commit HEAD
git revert --no-commit HEAD~1  # jika perlu rollback 2 commit
```

**Atau** (jika tidak perlu git history bersih):
```bash
# Reset ke commit sebelumnya
git reset --hard HEAD~1
```

```bash
# 4. Install dependensi
cd "$(readlink -f "$APP_DIR/current")"
composer install --no-dev --optimize-autoloader --no-interaction

# 5. Rollback migration (jika migrasi bermasalah)
php artisan migrate:rollback --step=1 --force
# Atau rollback ke batch tertentu:
php artisan migrate:rollback --batch=1 --force

# 6. Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart queue (graceful)
php artisan queue:restart
sleep 5

# 8. Switch symlink ke release sebelumnya
TARGET=$(ls -td "$RELEASES_DIR"/* | head -2 | tail -1)
ln -sfn "$TARGET" "$RELEASES_DIR/current.tmp"
mv -Tf "$RELEASES_DIR/current.tmp" "$APP_DIR/current"
echo "Switched to: $TARGET"

# 9. Reload PHP-FPM
sudo systemctl reload php8.3-fpm

# 10. Restart supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart all

# 11. Health check
sleep 3
curl -s -o /dev/null -w "Health: HTTP %{http_code}\n" http://127.0.0.1/health

# 12. Matikan maintenance
php artisan up
```

### 6.3 Rollback emergency (tanpa git, langsung ke release lama)

```bash
# Jika release target sudah ada di releases/
APP_DIR="/var/www/nurisk"
TARGET="$APP_DIR/releases/release-20260617-030000"

php artisan down --retry=30
ln -sfn "$TARGET" "$APP_DIR/current"
sudo systemctl reload php8.3-fpm
php artisan queue:restart
sleep 3
curl -sf http://127.0.0.1/health && php artisan up
```

---

## 7. Health Check Endpoint

Endpoint: `GET /health` (no auth required, public)

**Route**: `routes/web.php:10` → `App\Http\Controllers\Api\HealthCheckController`

### 7.1 Cek health

```bash
# Full health check
curl -s http://127.0.0.1/health | jq .

# Cek HTTP status saja
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1/health
```

**Expected output** (healthy, HTTP 200):
```json
{
  "status": "healthy",
  "version": "1.0.0",
  "git_sha": "a1b2c3d4e5f6...",
  "time": "2026-06-20T10:30:00+07:00",
  "database": "ok",
  "cache": "ok",
  "storage": "ok",
  "queue": {
    "status": "ok",
    "pending": 0,
    "failed": 0
  },
  "disk": {
    "status": "ok",
    "used_pct": 35
  },
  "migration": "current",
  "sync": {
    "status": "ok",
    "last_sync_at": "2026-06-20T10:29:00+07:00",
    "sync_duration_ms": 123.45,
    "entities_synced": 42,
    "conflicts_count": 0,
    "total_sync_requests": 1500
  }
}
```

### 7.2 Cek spesifik — database

```php
// Di tinker
try {
    DB::connection()->getPdo();
    DB::select('SELECT 1');
    echo "database: ok\n";
} catch (\Throwable $e) {
    echo "database: fail — {$e->getMessage()}\n";
}
```

### 7.3 Cek spesifik — queue status

```php
// Di tinker
$pending = DB::table('jobs')->count();
$reserved = DB::table('jobs')->whereNotNull('reserved_at')->count();
$failed = DB::table('failed_jobs')->count();
echo "pending={$pending} reserved={$reserved} failed={$failed}\n";
```

### 7.4 Cek spesifik — disk usage

```bash
php artisan tinker --execute="
\$free = disk_free_space(storage_path());
\$total = disk_total_space(storage_path());
\$used = \$total - \$free;
\$pct = round((\$used / \$total) * 100);
echo \"disk: {\$pct}% used (\" . round(\$free / 1073741824, 2) . \" GB free)\n\";
"
```

### 7.5 Cek spesifik — Sanctum token table integrity

```sql
-- Via mysql CLI
SELECT 
    COUNT(*) AS total_tokens,
    COUNT(CASE WHEN expires_at IS NOT NULL AND expires_at < NOW() THEN 1 END) AS expired,
    COUNT(CASE WHEN device_uuid IS NULL THEN 1 END) AS no_device_uuid,
    COUNT(DISTINCT tokenable_id) AS unique_users
FROM personal_access_tokens
WHERE tokenable_type = 'App\\Models\AuthUser';
```

### 7.6 Monitoring endpoint (Nagios / Prometheus compatible)

```bash
# Script ping health
#!/bin/bash
# /opt/nurisk/scripts/health-ping.sh
HEALTH=$(curl -sf http://127.0.0.1/health 2>/dev/null)
if [ $? -eq 0 ] && echo "$HEALTH" | grep -q '"status":"healthy"'; then
    echo "OK - NURISK healthy"
    exit 0
else
    echo "CRITICAL - NURISK unhealthy"
    exit 2
fi
```

---

## Lampiran: Perintah Cepat

| Tujuan | Perintah |
|--------|----------|
| Cek queue pending | `php artisan tinker --execute="echo DB::table('jobs')->whereNull('reserved_at')->count()"` |
| Lepas job stuck | `php artisan tinker --execute="DB::table('jobs')->whereNotNull('reserved_at')->update(['reserved_at'=>null,'attempts'=>DB::raw('attempts-1')]);"` |
| Flush failed jobs | `php artisan queue:flush` |
| Restart queue | `sudo supervisorctl restart all` |
| Cek DB connection | `php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK\n';"` |
| Maintenance ON | `php artisan down --retry=60` |
| Maintenance OFF | `php artisan up` |
| Revoke all user tokens | `php artisan tinker --execute="AuthUser::find(7)->tokens()->delete();"` |
| Prune expired tokens | `php artisan sanctum:prune-expired --hours=0` |
| Backup MySQL | `mysqldump --single-transaction nurisk \| gzip > /backup/nurisk/sql/nurisk-\$(date +%Y-%m-%d).sql.gz` |
| Health check | `curl -s http://127.0.0.1/health \| jq .` |
| Cek disk | `df -h / && du -sh /var/www/nurisk/storage/* \| sort -rh` |
| Truncate SQLite WAL | `sqlite3 \$DB_PATH "PRAGMA wal_checkpoint(TRUNCATE);"` |
| Check release list | `ls -td /var/www/nurisk/releases/* \| head -5` |
| Rollback 1 step | `sudo -u www-data bash /opt/nurisk/scripts/rollback.sh` |
