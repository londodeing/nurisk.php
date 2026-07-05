# Pilot Dashboard Readiness Review

**System:** NURISK — Laravel/Sanctum mobile app backend (sync-based offline-first)
**Audience:** Operations Team
**Purpose:** Dashboard metrics & SQL queries for pilot monitoring

---

## 1. Active Users

### Metrics
| Metric | Source | SQL |
|--------|--------|-----|
| Concurrent users (last 5 min) | `auth_users.terakhir_masuk` | See below |
| New registrations (24h / 7d) | `auth_users.dibuat_pada` | See below |
| Role distribution | `auth_roles.nama_peran` | See below |
| Last activity per user | `auth_users.terakhir_masuk` | See below |

### Queries

**Concurrent users (active in last 5 min):**
```sql
SELECT COUNT(*) AS concurrent_users
FROM auth_users
WHERE status_akun = 'aktif'
  AND terakhir_masuk >= NOW() - INTERVAL 5 MINUTE;
```

**New registrations today:**
```sql
SELECT COUNT(*) AS registered_today
FROM auth_users
WHERE dibuat_pada >= CURDATE();
```

**New registrations this week:**
```sql
SELECT COUNT(*) AS registered_this_week
FROM auth_users
WHERE dibuat_pada >= CURDATE() - INTERVAL 7 DAY;
```

**Role distribution:**
```sql
SELECT r.nama_peran, COUNT(u.id_pengguna) AS user_count
FROM auth_roles r
LEFT JOIN auth_users u ON u.id_peran = r.id_peran
GROUP BY r.id_peran, r.nama_peran
ORDER BY user_count DESC;
```

**Last activity for all users:**
```sql
SELECT u.id_pengguna, p.nama_lengkap, r.nama_peran, u.terakhir_masuk, u.status_akun
FROM auth_users u
JOIN auth_roles r ON r.id_peran = u.id_peran
LEFT JOIN auth_pengguna_profil p ON p.id_pengguna = u.id_pengguna
ORDER BY u.terakhir_masuk DESC;
```

### Artisan Command
```bash
php artisan tinker --execute="
echo 'Concurrent (5m): ' . \App\Models\AuthUser::where('status_akun', 'aktif')
    ->where('terakhir_masuk', '>=', now()->subMinutes(5))->count() . PHP_EOL;
echo 'New today: ' . \App\Models\AuthUser::whereDate('dibuat_pada', today())->count() . PHP_EOL;
echo 'New this week: ' . \App\Models\AuthUser::where('dibuat_pada', '>=', now()->subDays(7))->count() . PHP_EOL;
echo PHP_EOL . 'Role distribution:' . PHP_EOL;
\App\Models\AuthRole::withCount('pengguna')->get()->each(fn(\$r) => echo \"  \$r->nama_peran: \$r->pengguna_count\" . PHP_EOL);
"
```

### What to Watch For

- **Concurrent users < 80% of expected pilot cohort:** Indicates login issues or device registration failures.
- **New registrations spiking or flatlining:** Sudden spike may indicate bot activity; flatlining during onboarding means the registration flow is broken.
- **Unknown role appearing:** Check for seed data pollution or unauthorized role assignment via API.
- **`terakhir_masuk` is NULL for active users:** Means users registered but never completed login — investigate auth flow.

---

## 2. Devices

### Metrics
| Metric | Source | SQL |
|--------|--------|-----|
| Active devices | `mobile_devices.status = 'active'` | See below |
| Revoked devices | `mobile_devices.status = 'revoked'` | See below |
| Inactive devices | `mobile_devices.status = 'inactive'` | See below |
| Platform distribution | `mobile_devices.platform` | See below |
| Trust score distribution | `mobile_devices.trust_score` | See below |

### Queries

**Device status counts:**
```sql
SELECT status, COUNT(*) AS count
FROM mobile_devices
GROUP BY status;
```

**Platform distribution:**
```sql
SELECT platform, COUNT(*) AS count
FROM mobile_devices
WHERE status = 'active'
GROUP BY platform
ORDER BY count DESC;
```

**Trust score buckets:**
```sql
SELECT
  CASE
    WHEN trust_score >= 90 THEN '90-100 (good)'
    WHEN trust_score >= 70 THEN '70-89 (fair)'
    WHEN trust_score >= 50 THEN '50-69 (low)'
    ELSE '0-49 (critical)'
  END AS trust_bucket,
  COUNT(*) AS count
FROM mobile_devices
WHERE status = 'active'
GROUP BY trust_bucket
ORDER BY MIN(trust_score);
```

**Devices with no recent sync:**
```sql
SELECT d.uuid_device, d.platform, d.app_version, d.last_sync_at, u.id_pengguna
FROM mobile_devices d
JOIN auth_users u ON u.id_pengguna = d.id_pengguna
WHERE d.status = 'active'
  AND (d.last_sync_at IS NULL OR d.last_sync_at < NOW() - INTERVAL 1 HOUR)
ORDER BY d.last_sync_at ASC;
```

**Sanctum tokens expiring within 7 days:**
```sql
SELECT COUNT(*) AS tokens_expiring_soon
FROM personal_access_tokens
WHERE expires_at IS NOT NULL
  AND expires_at BETWEEN NOW() AND NOW() + INTERVAL 7 DAY;
```

### Artisan Command
```bash
php artisan tinker --execute="
echo 'Device status breakdown:' . PHP_EOL;
\App\Models\MobileDevice::selectRaw('status, COUNT(*) AS count')
    ->groupBy('status')->pluck('count', 'status')->each(fn(\$c, \$s) => echo \"  \$s: \$c\" . PHP_EOL);
echo PHP_EOL . 'Active platform distribution:' . PHP_EOL;
\App\Models\MobileDevice::selectRaw('platform, COUNT(*) AS count')
    ->where('status', 'active')->groupBy('platform')
    ->pluck('count', 'platform')->each(fn(\$c, \$p) => echo \"  \$p: \$c\" . PHP_EOL);
echo PHP_EOL . 'Low trust devices (trust_score < 70): ' .
    \App\Models\MobileDevice::where('status', 'active')->where('trust_score', '<', 70)->count() . PHP_EOL;
"
```

### What to Watch For

- **Revoked devices > 5% of total:** Indicates widespread token theft or forced logout. Investigate security incidents.
- **Trust score < 50 for any active device:** Suspicious behavior detected (tampered app, unusual sync patterns). High risk.
- **Platform 100% single-OS:** Expected in a controlled pilot, but 0% of the other platform means the build may be broken.
- **Devices not syncing for > 1 hour:** Offline queue may be growing unbounded. Check connectivity or app crash rate.
- **Tokens expiring > 10% of total in next 7 days:** Users will get 401s. Ensure refresh flow works or extend expiry.

---

## 3. Sync Metrics

### Metrics
| Metric | Source | SQL |
|--------|--------|-----|
| Requests per minute | `sync_audit_logs.dibuat_pada` | See below |
| Changes per request (P50/P95/P99) | `sync_audit_logs.entities_synced` | See below |
| Sync duration (P50/P95/P99) | `sync_audit_logs.duration_ms` | See below |
| Conflict rate | `sync_conflicts.dibuat_pada` | See below |
| Tombstones generated (24h) | `sync_tombstones.deleted_at` | See below |

### Queries

**Sync requests per minute (last 60 min):**
```sql
SELECT
  DATE_FORMAT(dibuat_pada, '%Y-%m-%d %H:%i') AS minute,
  COUNT(*) AS requests
FROM sync_audit_logs
WHERE dibuat_pada >= NOW() - INTERVAL 60 MINUTE
GROUP BY minute
ORDER BY minute;
```

**Changes per request percentiles:**
```sql
SELECT
  COUNT(*) AS total_requests,
  ROUND(AVG(entities_synced), 1) AS avg_changes,
  MAX(entities_synced) AS max_changes
FROM sync_audit_logs
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR;
```

**Sync duration percentiles (using ordered subquery):**
```sql
SET @row_num = 0;
SET @total_rows = (SELECT COUNT(*) FROM sync_audit_logs
  WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR AND duration_ms > 0);

SELECT
  MAX(CASE WHEN @row_num <= @total_rows * 0.50 THEN duration_ms END) AS p50_ms,
  MAX(CASE WHEN @row_num <= @total_rows * 0.95 THEN duration_ms END) AS p95_ms,
  MAX(CASE WHEN @row_num <= @total_rows * 0.99 THEN duration_ms END) AS p99_ms
FROM (
  SELECT duration_ms, @row_num := @row_num + 1 AS row_num
  FROM sync_audit_logs
  WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR AND duration_ms > 0
  ORDER BY duration_ms ASC
) AS ordered;
```

**Alternative duration percentiles (no variables — app-level):**
```sql
SELECT
  COUNT(*) AS total_syncs,
  ROUND(AVG(duration_ms), 1) AS avg_ms,
  MAX(duration_ms) AS max_ms
FROM sync_audit_logs
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR;
```

**Conflict rate (last 24h):**
```sql
SELECT
  COUNT(*) AS total_conflicts,
  COUNT(DISTINCT device_uuid) AS affected_devices,
  COUNT(DISTINCT entity_type) AS affected_entities
FROM sync_conflicts
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR;
```

**Conflicts by entity type:**
```sql
SELECT entity_type, COUNT(*) AS count
FROM sync_conflicts
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR
GROUP BY entity_type
ORDER BY count DESC;
```

**Tombstones generated (24h):**
```sql
SELECT
  entity_type,
  COUNT(*) AS tombstone_count
FROM sync_tombstones
WHERE deleted_at >= NOW() - INTERVAL 24 HOUR
GROUP BY entity_type
ORDER BY tombstone_count DESC;
```

**Total tombstone and cursor volume:**
```sql
SELECT 'sync_cursors' AS tbl, COUNT(*) AS total_rows, MAX(cursor_value) AS max_cursor
FROM sync_cursors
UNION ALL
SELECT 'sync_tombstones', COUNT(*), MAX(cursor_value)
FROM sync_tombstones;
```

### Artisan Command
```bash
php artisan tinker --execute="
use App\Models\SyncAuditLog;
use App\Models\SyncConflict;
use App\Models\SyncTombstone;
use Illuminate\Support\Facades\DB;

\$since = now()->subHours(24);
echo 'Sync requests (24h): ' . SyncAuditLog::where('dibuat_pada', '>=', \$since)->count() . PHP_EOL;
echo 'Avg changes/request: ' . round(SyncAuditLog::where('dibuat_pada', '>=', \$since)->avg('entities_synced') ?? 0, 1) . PHP_EOL;
echo 'Avg duration (ms): ' . round(SyncAuditLog::where('dibuat_pada', '>=', \$since)->where('duration_ms', '>', 0)->avg('duration_ms') ?? 0, 1) . PHP_EOL;
echo 'Conflicts (24h): ' . SyncConflict::where('dibuat_pada', '>=', \$since)->count() . PHP_EOL;
echo 'Tombstones (24h): ' . SyncTombstone::where('deleted_at', '>=', \$since)->count() . PHP_EOL;

echo PHP_EOL . 'Cursor & tombstone totals:' . PHP_EOL;
echo '  sync_cursors: ' . DB::table('sync_cursors')->count() . PHP_EOL;
echo '  sync_tombstones: ' . DB::table('sync_tombstones')->count() . PHP_EOL;
"
```

### What to Watch For

- **Sync requests/minute declining:** Users may be abandoning the app or experiencing auth failures.
- **P95 duration > 2000ms:** Sync endpoint is too slow for the mobile UX. Review query counts (24+ queries).
- **Conflict rate > 5% of total sync requests:** Offline merge logic is producing too many conflicts. Investigate entity-level conflict patterns.
- **Tombstones growing faster than cursors:** More deletes than creates/updates. Expected during cleanup but a red flag if persistent.
- **`entities_synced` consistently 0:** Sync is running but delivering no data. Check cursor logic on the client side.
- **Cursor volume > 100K rows:** May need tombstone pruning (see `SyncPruneTombstones` command).

---

## 4. Queue

### Metrics
| Metric | Source | SQL |
|--------|--------|-----|
| Job backlog | `jobs` table | See below |
| Processing rate | `job_batches` | See below |
| Failure rate (total + PDF-specific) | `failed_jobs` + job name filter | See below |
| Avg processing time | `job_batches` | See below |

### Queries

**Job backlog by queue:**
```sql
SELECT queue, COUNT(*) AS pending_count
FROM jobs
WHERE reserved_at IS NULL
  AND available_at <= UNIX_TIMESTAMP()
GROUP BY queue
ORDER BY pending_count DESC;
```

**Total jobs waiting + reserved:**
```sql
SELECT
  queue,
  SUM(CASE WHEN reserved_at IS NULL THEN 1 ELSE 0 END) AS waiting,
  SUM(CASE WHEN reserved_at IS NOT NULL THEN 1 ELSE 0 END) AS processing,
  COUNT(*) AS total
FROM jobs
GROUP BY queue;
```

**Failed jobs (last 24h) — all:**
```sql
SELECT queue, COUNT(*) AS failure_count
FROM failed_jobs
WHERE failed_at >= NOW() - INTERVAL 24 HOUR
GROUP BY queue
ORDER BY failure_count DESC;
```

**Failed jobs (last 24h) — PDF-specific:**
```sql
SELECT
  COUNT(*) AS pdf_failures,
  COUNT(DISTINCT queue) AS queues_affected
FROM failed_jobs
WHERE failed_at >= NOW() - INTERVAL 24 HOUR
  AND (payload LIKE '%GenerateSuratPdfJob%' OR payload LIKE '%BenchmarkPdfJob%');
```

**Job batch summary (recent batches):**
```sql
SELECT
  id AS batch_id, name, total_jobs, pending_jobs, failed_jobs,
  FROM_UNIXTIME(created_at) AS created_at,
  FROM_UNIXTIME(finished_at) AS finished_at,
  CASE
    WHEN finished_at IS NOT NULL
      THEN ROUND((finished_at - created_at) / GREATEST(total_jobs, 1), 2)
    ELSE NULL
  END AS avg_seconds_per_job
FROM job_batches
WHERE created_at >= UNIX_TIMESTAMP() - 86400
ORDER BY created_at DESC;
```

**Mobile sync queue backlog:**
```sql
SELECT status, COUNT(*) AS count
FROM mobile_sync_queues
GROUP BY status;
```

**Stale mobile sync queue items (pending > 30 min):**
```sql
SELECT COUNT(*) AS stale_pending
FROM mobile_sync_queues
WHERE status = 'pending'
  AND dibuat_pada < NOW() - INTERVAL 30 MINUTE;
```

### Artisan Command
```bash
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

echo 'Job backlog:' . PHP_EOL;
\$jobs = DB::table('jobs')
    ->select('queue', DB::raw('COUNT(*) AS count'))
    ->whereNull('reserved_at')
    ->where('available_at', '<=', time())
    ->groupBy('queue')
    ->pluck('count', 'queue');
foreach (\$jobs as \$q => \$c) echo \"  \$q: \$c\" . PHP_EOL;

echo PHP_EOL . 'Failed jobs (24h): ' .
    DB::table('failed_jobs')->where('failed_at', '>=', now()->subHours(24))->count() . PHP_EOL;

echo 'PDF failures (24h): ' .
    DB::table('failed_jobs')
        ->where('failed_at', '>=', now()->subHours(24))
        ->where(function (\$q) {
            \$q->where('payload', 'like', '%GenerateSuratPdfJob%')
              ->orWhere('payload', 'like', '%BenchmarkPdfJob%');
        })->count() . PHP_EOL;

echo PHP_EOL . 'Mobile sync queue status:' . PHP_EOL;
\App\Models\MobileSyncQueue::selectRaw('status, COUNT(*) AS count')
    ->groupBy('status')
    ->pluck('count', 'status')
    ->each(fn(\$c, \$s) => echo \"  \$s: \$c\" . PHP_EOL);
"
```

### What to Watch For

- **Backlog > 100 jobs on `pdf-generation` queue:** PDF generation is CPU-bound (Dompdf). Scale workers or add rate limiting.
- **Failure rate > 5% on any queue:** Investigate `failed_jobs.exception` for root cause. Common PDF failures: memory exhaustion, missing template, corrupted data.
- **PDF failures > 10 in 24h:** Critical — blocked document workflow. Check `SuratPdfService` for blade template errors or Dompdf crashes.
- **Stale mobile_sync_queues > 50:** Indicates the sync processing pipeline is stuck. Check worker health and queue driver.
- **`pending_jobs` stays flat or grows in `job_batches`:** Worker is not picking up jobs. Check `php artisan queue:status` and Supervisor config.

---

## 5. Latency & Performance

### Endpoint Query Profile Table

| Endpoint | Method | Avg Queries/Req |
|---|---|---|
| `POST /api/v1/sync` | POST | 24 |
| `POST /api/v1/bootstrap` | POST | 15 |
| `GET /api/v1/sync/status` | GET | 11 |
| `GET /api/v1/sync/metrics` | GET | 10 |
| `GET /api/v1/assessment` | GET | 7 |
| `GET /api/v1/penugasan` | GET | 11 |

*Note: Query counts from profile run (SQLite). Production MySQL/MariaDB may differ due to optimizer plan changes.*

### Queries

**Average response time per endpoint (from sync_audit_logs):**
```sql
SELECT
  DATE_FORMAT(dibuat_pada, '%Y-%m-%d %H:00:00') AS hour,
  COUNT(*) AS requests,
  ROUND(AVG(duration_ms), 1) AS avg_ms,
  MAX(duration_ms) AS max_ms
FROM sync_audit_logs
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR
GROUP BY hour
ORDER BY hour;
```

**Slowest sync requests (P95+):**
```sql
SELECT sal.*, md.platform, md.app_version
FROM sync_audit_logs sal
JOIN mobile_devices md ON md.uuid_device = sal.device_uuid
WHERE sal.dibuat_pada >= NOW() - INTERVAL 24 HOUR
  AND sal.duration_ms > 0
ORDER BY sal.duration_ms DESC
LIMIT 20;
```

**Sanctum auth query overhead:**
```sql
SELECT COUNT(*) AS auth_queries_per_request
FROM personal_access_tokens
WHERE last_used_at >= NOW() - INTERVAL 24 HOUR;
```

**Application-level slow query candidates (from information_schema):**
```sql
SELECT * FROM information_schema.processlist
WHERE time > 5
ORDER BY time DESC;
```

### Artisan Command
```bash
php artisan tinker --execute="
use App\Models\SyncAuditLog;

\$since = now()->subHours(24);
\$durations = SyncAuditLog::where('dibuat_pada', '>=', \$since)
    ->where('duration_ms', '>', 0)
    ->pluck('duration_ms')
    ->sort()
    ->values();

\$n = \$durations->count();
echo 'Sync requests (24h): ' . \$n . PHP_EOL;
echo 'Avg duration: ' . round(\$durations->avg(), 1) . ' ms' . PHP_EOL;
echo 'P50: ' . round(\$durations->get((int) (\$n * 0.50)) ?? 0, 1) . ' ms' . PHP_EOL;
echo 'P95: ' . round(\$durations->get((int) (\$n * 0.95)) ?? 0, 1) . ' ms' . PHP_EOL;
echo 'P99: ' . round(\$durations->get((int) (\$n * 0.99)) ?? 0, 1) . ' ms' . PHP_EOL;
echo 'Max: ' . round(\$durations->last() ?? 0, 1) . ' ms' . PHP_EOL;
"
```

### What to Watch For

- **P95 response time > 3000ms on sync endpoint:** With 24 queries/request, auth overhead (Sanctum + AuthorizationContextService) dominates. Enable query log on a single request to identify the top N slow queries.
- **P95 > 1000ms on status/metrics endpoints:** Even at 10–11 queries, these should be fast. Check for missing indexes or full table scans on `sync_cursors` / `sync_tombstones`.
- **Database processlist showing `Creating sort index` on sync tables:** The `ORDER BY cursor_value ASC` without a covering index will cause filesorts. The composite indexes added in Sprint 13 (`idx_sync_cursors_scope_cursor`, etc.) should address this.
- **Any query appearing in `information_schema.processlist` for > 5 seconds:** Immediate investigation required. Likely missing index or lock contention.

---

## 6. System Health

### Metrics
| Metric | Source | SQL / Command |
|--------|--------|---------------|
| Disk usage | OS | `df -h` |
| DB connections | `SHOW STATUS` | See below |
| WAL file size (SQLite) | OS | `ls -lh` on DB file |
| InnoDB buffer pool (MySQL) | `SHOW STATUS` | See below |
| Sentry error rate | Sentry API | Dashboard link |

### Queries

**Active database connections (MySQL/MariaDB):**
```sql
SHOW STATUS LIKE 'Threads_connected';
SHOW STATUS LIKE 'Max_used_connections';
```

**InnoDB buffer pool hit rate:**
```sql
SELECT
  (1 - (SUM(IF(variable_name LIKE '%innodb_buffer_pool_reads%', variable_value, 0))
    / NULLIF(SUM(IF(variable_name LIKE '%innodb_buffer_pool_read_requests%', variable_value, 0)), 0)
  )) * 100 AS buffer_pool_hit_rate
FROM performance_schema.global_status
WHERE variable_name LIKE '%innodb_buffer_pool_read%';
```

**Slow query log (if enabled):**
```sql
SELECT * FROM mysql.slow_log
WHERE start_time >= NOW() - INTERVAL 24 HOUR
ORDER BY query_time DESC
LIMIT 20;
```

**Table sizes:**
```sql
SELECT
  table_name,
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
  table_rows AS row_estimate
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_type = 'BASE TABLE'
ORDER BY size_mb DESC;
```

**Sync-related table growth (row counts):**
```sql
SELECT 'sync_cursors' AS tbl, COUNT(*) AS row_count FROM sync_cursors
UNION ALL
SELECT 'sync_tombstones', COUNT(*) FROM sync_tombstones
UNION ALL
SELECT 'sync_audit_logs', COUNT(*) FROM sync_audit_logs
UNION ALL
SELECT 'sync_conflicts', COUNT(*) FROM sync_conflicts
UNION ALL
SELECT 'mobile_sync_queues', COUNT(*) FROM mobile_sync_queues
UNION ALL
SELECT 'mobile_devices', COUNT(*) FROM mobile_devices
UNION ALL
SELECT 'personal_access_tokens', COUNT(*) FROM personal_access_tokens
UNION ALL
SELECT 'jobs', COUNT(*) FROM jobs
UNION ALL
SELECT 'failed_jobs', COUNT(*) FROM failed_jobs;
```

### Artisan Command
```bash
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

\$tables = [
    'sync_cursors', 'sync_tombstones', 'sync_audit_logs',
    'sync_conflicts', 'mobile_sync_queues', 'mobile_devices',
    'personal_access_tokens', 'jobs', 'failed_jobs', 'auth_users',
];

echo 'Table row counts:' . PHP_EOL;
foreach (\$tables as \$table) {
    try {
        \$count = DB::table(\$table)->count();
        echo sprintf(\"  %-30s %s\\n\", \$table, number_format(\$count));
    } catch (\Throwable \$e) {
        echo sprintf(\"  %-30s ERROR: %s\\n\", \$table, \$e->getMessage());
    }
}

echo PHP_EOL . 'Sync cursor max value:' . PHP_EOL;
\$maxCursor = DB::table('sync_cursors')->max('cursor_value');
echo '  max cursor_value: ' . number_format(\$maxCursor ?? 0) . PHP_EOL;

\$maxTombCursor = DB::table('sync_tombstones')->max('cursor_value');
echo '  max tombstone cursor_value: ' . number_format(\$maxTombCursor ?? 0) . PHP_EOL;
"
```

### System Commands
```bash
# Disk usage
df -h /

# Project storage
du -sh storage/logs storage/app

# Database file size (SQLite)
ls -lh database/nurisk.sqlite 2>/dev/null || echo "MySQL — check via SQL"

# PHP-FPM status (if available)
php artisan tinker --execute="echo 'PHP memory: ' . ini_get('memory_limit') . PHP_EOL; \
  echo 'Max execution: ' . ini_get('max_execution_time') . 's' . PHP_EOL; \
  echo 'Upload max: ' . ini_get('upload_max_filesize') . PHP_EOL;"
```

### What to Watch For

- **Disk usage > 80%:** Logs and SQLite WAL can grow quickly. Enable log rotation and schedule `VACUUM` for SQLite.
- **Threads_connected approaching `max_connections`:** Workers are holding connections too long. Add connection pooling or reduce queue worker concurrency.
- **Buffer pool hit rate < 95%:** InnoDB buffer pool too small for the working set. Increase `innodb_buffer_pool_size`.
- **Sync table growth > 10K rows/hour:** Set up tombstone pruning (the `SyncPruneTombstones` command) on a daily cron. Target: keep 7 days of tombstones.
- **Sentry error rate > 1% of requests:** Critical. Check for unhandled exceptions, `QueryException`, or `ModelNotFoundException`.
- **`personal_access_tokens` table > 100K rows:** Token bloat. Existing tokens never expire (or 30-day expiry is too long for pilot volume). Add `token_id` indexing and a cleanup cron.

---

## Appendix: Pilot Dashboard SQL Queries (All-in-One)

```sql
-- ═══════════════════════════════════════════
-- 1. ACTIVE USERS
-- ═══════════════════════════════════════════

-- Concurrent users (last 5 min)
SELECT COUNT(*) AS concurrent_users
FROM auth_users
WHERE status_akun = 'aktif'
  AND terakhir_masuk >= NOW() - INTERVAL 5 MINUTE;

-- New registrations today
SELECT COUNT(*) AS registered_today
FROM auth_users
WHERE dibuat_pada >= CURDATE();

-- New registrations this week
SELECT COUNT(*) AS registered_this_week
FROM auth_users
WHERE dibuat_pada >= CURDATE() - INTERVAL 7 DAY;

-- Role distribution
SELECT r.nama_peran, COUNT(u.id_pengguna) AS user_count
FROM auth_roles r
LEFT JOIN auth_users u ON u.id_peran = r.id_peran
GROUP BY r.id_peran, r.nama_peran
ORDER BY user_count DESC;

-- ═══════════════════════════════════════════
-- 2. DEVICES
-- ═══════════════════════════════════════════

-- Device status counts
SELECT status, COUNT(*) AS count
FROM mobile_devices
GROUP BY status;

-- Platform distribution (active)
SELECT platform, COUNT(*) AS count
FROM mobile_devices
WHERE status = 'active'
GROUP BY platform
ORDER BY count DESC;

-- Trust score buckets
SELECT
  CASE
    WHEN trust_score >= 90 THEN '90-100 (good)'
    WHEN trust_score >= 70 THEN '70-89 (fair)'
    WHEN trust_score >= 50 THEN '50-69 (low)'
    ELSE '0-49 (critical)'
  END AS trust_bucket,
  COUNT(*) AS count
FROM mobile_devices
WHERE status = 'active'
GROUP BY trust_bucket
ORDER BY MIN(trust_score);

-- Devices with no recent sync
SELECT d.uuid_device, d.platform, d.app_version, d.last_sync_at, u.id_pengguna
FROM mobile_devices d
JOIN auth_users u ON u.id_pengguna = d.id_pengguna
WHERE d.status = 'active'
  AND (d.last_sync_at IS NULL OR d.last_sync_at < NOW() - INTERVAL 1 HOUR)
ORDER BY d.last_sync_at ASC;

-- Tokens expiring within 7 days
SELECT COUNT(*) AS tokens_expiring_soon
FROM personal_access_tokens
WHERE expires_at IS NOT NULL
  AND expires_at BETWEEN NOW() AND NOW() + INTERVAL 7 DAY;

-- ═══════════════════════════════════════════
-- 3. SYNC METRICS
-- ═══════════════════════════════════════════

-- Sync requests per minute (last 60 min)
SELECT
  DATE_FORMAT(dibuat_pada, '%Y-%m-%d %H:%i') AS minute,
  COUNT(*) AS requests
FROM sync_audit_logs
WHERE dibuat_pada >= NOW() - INTERVAL 60 MINUTE
GROUP BY minute
ORDER BY minute;

-- Sync aggregate (24h)
SELECT
  COUNT(*) AS total_requests,
  ROUND(AVG(entities_synced), 1) AS avg_changes,
  MAX(entities_synced) AS max_changes,
  ROUND(AVG(CASE WHEN duration_ms > 0 THEN duration_ms END), 1) AS avg_duration_ms,
  MAX(duration_ms) AS max_duration_ms
FROM sync_audit_logs
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR;

-- Conflicts (24h)
SELECT
  COUNT(*) AS total_conflicts,
  COUNT(DISTINCT device_uuid) AS affected_devices,
  COUNT(DISTINCT entity_type) AS affected_entities
FROM sync_conflicts
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR;

-- Conflicts by entity type (24h)
SELECT entity_type, COUNT(*) AS count
FROM sync_conflicts
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR
GROUP BY entity_type
ORDER BY count DESC;

-- Tombstones generated (24h)
SELECT
  entity_type,
  COUNT(*) AS tombstone_count
FROM sync_tombstones
WHERE deleted_at >= NOW() - INTERVAL 24 HOUR
GROUP BY entity_type
ORDER BY tombstone_count DESC;

-- Total cursor & tombstone volume
SELECT 'sync_cursors' AS tbl, COUNT(*) AS total_rows, MAX(cursor_value) AS max_cursor
FROM sync_cursors
UNION ALL
SELECT 'sync_tombstones', COUNT(*), MAX(cursor_value)
FROM sync_tombstones;

-- ═══════════════════════════════════════════
-- 4. QUEUE
-- ═══════════════════════════════════════════

-- Job backlog by queue
SELECT queue, COUNT(*) AS pending_count
FROM jobs
WHERE reserved_at IS NULL
  AND available_at <= UNIX_TIMESTAMP()
GROUP BY queue
ORDER BY pending_count DESC;

-- Waiting vs processing
SELECT
  queue,
  SUM(CASE WHEN reserved_at IS NULL THEN 1 ELSE 0 END) AS waiting,
  SUM(CASE WHEN reserved_at IS NOT NULL THEN 1 ELSE 0 END) AS processing,
  COUNT(*) AS total
FROM jobs
GROUP BY queue;

-- Failed jobs (24h) — all
SELECT queue, COUNT(*) AS failure_count
FROM failed_jobs
WHERE failed_at >= NOW() - INTERVAL 24 HOUR
GROUP BY queue
ORDER BY failure_count DESC;

-- Failed jobs (24h) — PDF-specific
SELECT
  COUNT(*) AS pdf_failures,
  COUNT(DISTINCT queue) AS queues_affected
FROM failed_jobs
WHERE failed_at >= NOW() - INTERVAL 24 HOUR
  AND (payload LIKE '%GenerateSuratPdfJob%' OR payload LIKE '%BenchmarkPdfJob%');

-- Recent job batches
SELECT
  id AS batch_id, name, total_jobs, pending_jobs, failed_jobs,
  FROM_UNIXTIME(created_at) AS created_at,
  FROM_UNIXTIME(finished_at) AS finished_at,
  CASE
    WHEN finished_at IS NOT NULL
      THEN ROUND((finished_at - created_at) / GREATEST(total_jobs, 1), 2)
    ELSE NULL
  END AS avg_seconds_per_job
FROM job_batches
WHERE created_at >= UNIX_TIMESTAMP() - 86400
ORDER BY created_at DESC;

-- Mobile sync queue status
SELECT status, COUNT(*) AS count
FROM mobile_sync_queues
GROUP BY status;

-- Stale pending sync queue items
SELECT COUNT(*) AS stale_pending
FROM mobile_sync_queues
WHERE status = 'pending'
  AND dibuat_pada < NOW() - INTERVAL 30 MINUTE;

-- ═══════════════════════════════════════════
-- 5. LATENCY & PERFORMANCE
-- ═══════════════════════════════════════════

-- Sync response time by hour (24h)
SELECT
  DATE_FORMAT(dibuat_pada, '%Y-%m-%d %H:00:00') AS hour,
  COUNT(*) AS requests,
  ROUND(AVG(duration_ms), 1) AS avg_ms,
  MAX(duration_ms) AS max_ms
FROM sync_audit_logs
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR
GROUP BY hour
ORDER BY hour;

-- Slowest sync requests
SELECT sal.*, md.platform, md.app_version
FROM sync_audit_logs sal
JOIN mobile_devices md ON md.uuid_device = sal.device_uuid
WHERE sal.dibuat_pada >= NOW() - INTERVAL 24 HOUR
  AND sal.duration_ms > 0
ORDER BY sal.duration_ms DESC
LIMIT 20;

-- ═══════════════════════════════════════════
-- 6. SYSTEM HEALTH
-- ═══════════════════════════════════════════

-- Active DB connections (MySQL)
SHOW STATUS LIKE 'Threads_connected';
SHOW STATUS LIKE 'Max_used_connections';

-- Table sizes (MySQL)
SELECT
  table_name,
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
  table_rows AS row_estimate
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_type = 'BASE TABLE'
ORDER BY size_mb DESC;

-- All sync-related table row counts
SELECT 'sync_cursors' AS tbl, COUNT(*) AS row_count FROM sync_cursors
UNION ALL
SELECT 'sync_tombstones', COUNT(*) FROM sync_tombstones
UNION ALL
SELECT 'sync_audit_logs', COUNT(*) FROM sync_audit_logs
UNION ALL
SELECT 'sync_conflicts', COUNT(*) FROM sync_conflicts
UNION ALL
SELECT 'mobile_sync_queues', COUNT(*) FROM mobile_sync_queues
UNION ALL
SELECT 'mobile_devices', COUNT(*) FROM mobile_devices
UNION ALL
SELECT 'personal_access_tokens', COUNT(*) FROM personal_access_tokens
UNION ALL
SELECT 'jobs', COUNT(*) FROM jobs
UNION ALL
SELECT 'failed_jobs', COUNT(*) FROM failed_jobs;
```
