# Operations Dashboard — Phase 15.7

**App**: NURISK (Laravel 11 + Sanctum + SQLite/MySQL)  
**Queue**: Database-driven (`jobs` table), Supervisor workers  
**Auth**: Sanctum tokens (30-day expiry, `nurisk_` prefix)  
**Error Tracking**: Sentry  
**Sync**: 24 queries/request, per-request `X-Correlation-ID`  
**Offline-First**: Bootstrap (15 queries), Status (11), Metrics (10), Assessment (7), Penugasan (11)

---

## Dashboard Index

| # | Page | Refresh | Live Q | Daily Q | Est. Impact |
|---|------|---------|--------|---------|------------|
| 1 | Health | 30s | 6 | 0 | Very Low |
| 2 | Sync | 60s | 5 | 3 | Low |
| 3 | Queue | 30s | 5 | 1 | Very Low |
| 4 | PDF | 60s | 4 | 1 | Low |
| 5 | Conflict | 60s | 5 | 1 | Low |
| 6 | Security | 60s | 5 | 0 | Low |
| 7 | Backup | 300s | 4 | 0 | Very Low |
| 8 | Regional | 300s | 2 | 3 | Low |
| | **Total** | | **36** | **9** | **~45 q/refresh** |

---

## 1. Health

**Purpose**: At-a-glance system health. Operations team checks this first when any alert fires. Must be fast (<200ms) and never load the application context.

### Widget 1.1 — DB Connection Status
Test MySQL and SQLite connections with a simple probe.

```sql
-- MySQL probe
SELECT 1 AS ping;

-- SQLite probe
SELECT 1 AS ping;
```
- **Refresh**: 30s (real-time)
- **Display**: Green/Red badge per connection ("MySQL: OK", "SQLite: OK")
- **Alert**: Any connection fails → **PAGE (P1)**
- **Source**: Live

### Widget 1.2 — Queue Worker Status
Check if Supervisor workers are alive by probing `artisan queue:monitor` equivalent.

```sql
-- Check for any alive worker heartbeat; workers touch a cache key on start
SELECT COUNT(*) AS active_workers
FROM jobs
WHERE queue IN ('pdf-generation', 'default')
  AND reserved_at IS NOT NULL
  AND reserved_at > UNIX_TIMESTAMP(NOW() - INTERVAL 5 MINUTE);
```
- **Refresh**: 30s (real-time)
- **Display**: "2/3 workers active" with per-queue breakdown
- **Alert**: Any expected worker missing >1 min → **PAGE (P2)**
- **Note**: Relies on `php artisan queue:monitor` or `supervisorctl status` upstream. SQL above is fallback heuristic.
- **Source**: Live

### Widget 1.3 — Disk Usage
Storage consumed by app logs, database files, and WAL journals.

```sql
-- Not a SQL query — system command
df -h /
du -sh /var/www/nurisk/storage/logs
du -sh /var/www/nurisk/database/*.sqlite*
du -sh /var/www/nurisk/storage/app
```
- **Refresh**: 30s (real-time)
- **Display**: Gauge per path (e.g. "Logs: 1.2 GB / 10 GB")
- **Alert**: Any path >85% → **WARN**; >95% → **PAGE (P2)**
- **Source**: Live (shell)

### Widget 1.4 — Sentry Error Rate (24h)

```sql
-- Not a SQL query — uses Sentry API
-- GET /api/0/projects/{org}/{project}/stats/?resolution=1h&since=<24h-ago>
-- Count events with level=error
```
- **Refresh**: 60s
- **Display**: Sparkline of errors/hour over last 24h; total count
- **Alert**: >50 errors in any hour → **WARN**; >200 → **PAGE (P2)**
- **Source**: Sentry API

### Widget 1.5 — Last Sync Timestamp

```sql
SELECT MAX(last_sync_at) AS last_sync_at,
       COUNT(*) AS devices_synced_5min
FROM mobile_devices
WHERE last_sync_at >= NOW() - INTERVAL 5 MINUTE;
```
- **Refresh**: 30s (real-time)
- **Display**: "Last sync: 30s ago (12 devices in 5min)"
- **Alert**: No sync in 5 min → **WARN**; >15 min → **PAGE (P2)**
- **Source**: Live

### Quick Query Count & Source

| Widget | Source | Queries |
|--------|--------|---------|
| DB Connection | Live | 2 |
| Queue Worker Status | Live | 1 |
| Disk Usage | Shell | 0 |
| Sentry Rate | Sentry API | 0 |
| Last Sync | Live | 1 |
| **Total** | | **4 SQL + 1 shell + 1 HTTP** |

---

## 2. Sync

**Purpose**: Monitor sync pipeline health — throughput, latency, and error rates for the offline-first sync engine.

### Widget 2.1 — Sync Success Rate

**From `operasi_metrics_daily` (last 7d):**

```sql
SELECT tanggal,
       sync_success,
       sync_failed,
       ROUND(sync_success * 100.0 / NULLIF(sync_success + sync_failed, 0), 1) AS success_pct
FROM operasi_metrics_daily
WHERE tanggal >= CURDATE() - INTERVAL 7 DAY
ORDER BY tanggal DESC;
```

**From `sync_audit_logs` (last 1h — live):**

```sql
SELECT
  COUNT(*) AS total,
  SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success,
  SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed,
  ROUND(SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS rate_pct
FROM sync_audit_logs
WHERE dibuat_pada >= NOW() - INTERVAL 1 HOUR;
```
- **Refresh**: 60s
- **Display**: Rate sparkline (7d daily bars) + current 1h rate as big number
- **Alert**: 1h rate <95% → **WARN**; <80% → **PAGE (P2)**
- **Source**: Daily (7d chart) + Live (1h current)

### Widget 2.2 — Sync Requests Per Minute

```sql
SELECT
  COUNT(*) AS requests_total,
  ROUND(COUNT(*) / 60.0, 1) AS requests_per_minute
FROM sync_audit_logs
WHERE dibuat_pada >= NOW() - INTERVAL 1 HOUR;
```
- **Refresh**: 60s
- **Display**: Big number ("24.3 req/min")
- **Alert**: <1 req/min for 10 min → **WARN**; 0 for 30 min → **PAGE (P3)**
- **Source**: Live

### Widget 2.3 — Average Sync Duration (P50/P95/P99)

```sql
SELECT
  ROUND(AVG(duration_ms), 1) AS avg_ms,
  ROUND(AVG(CASE WHEN percentile = 50 THEN duration_ms END), 1) AS p50,
  ROUND(AVG(CASE WHEN percentile = 95 THEN duration_ms END), 1) AS p95,
  ROUND(AVG(CASE WHEN percentile = 99 THEN duration_ms END), 1) AS p99
FROM (
  SELECT duration_ms,
    NTILE(100) OVER (ORDER BY duration_ms) AS percentile
  FROM sync_audit_logs
  WHERE dibuat_pada >= NOW() - INTERVAL 1 HOUR
) sub;
```
- **Refresh**: 60s
- **Display**: "P50: 245ms / P95: 890ms / P99: 2.1s"
- **Alert**: P95 >5s → **WARN**; P95 >10s → **PAGE (P3)**
- **Source**: Live

### Widget 2.4 — Top Devices by Sync Count (24h)

```sql
SELECT device_uuid,
       COUNT(*) AS sync_count,
       ROUND(AVG(duration_ms), 1) AS avg_duration_ms,
       MAX(dibuat_pada) AS last_sync
FROM sync_audit_logs
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR
GROUP BY device_uuid
ORDER BY sync_count DESC
LIMIT 10;
```
- **Refresh**: 60s
- **Display**: Table (device, count, avg duration, last seen)
- **Alert**: Single device >1000 req/24h → **WARN** (possible DoS)
- **Source**: Live

### Widget 2.5 — Bootstrap Requests (Count & Avg Duration)

```sql
SELECT
  COUNT(*) AS bootstrap_count,
  ROUND(AVG(duration_ms), 1) AS avg_duration_ms
FROM sync_audit_logs
WHERE status = 'bootstrap'
  AND dibuat_pada >= NOW() - INTERVAL 24 HOUR;
```
- **Refresh**: 60s
- **Display**: "42 bootstraps today, avg 3.2s"
- **Alert**: None (informational)
- **Source**: Live

### Quick Query Count & Source

| Widget | Source | Queries |
|--------|--------|---------|
| Sync Success Rate (7d) | Daily | 1 |
| Sync Success Rate (1h) | Live | 1 |
| Sync Req/min | Live | 1 |
| Sync Duration (P50/95/99) | Live | 1 |
| Top Devices | Live | 1 |
| Bootstrap | Live | 1 |
| **Total** | | **5 live + 1 daily** |

---

## 3. Queue

**Purpose**: Monitor the database-backed queue pipeline. NURISK uses `jobs` table (Laravel database queue) with 2 PDF workers and 1 default worker via Supervisor.

### Widget 3.1 — Queue Backlog (Jobs Waiting)

```sql
SELECT queue,
       COUNT(*) AS pending,
       SUM(CASE WHEN reserved_at IS NOT NULL THEN 1 ELSE 0 END) AS reserved
FROM jobs
GROUP BY queue
ORDER BY pending DESC;
```
- **Refresh**: 30s (real-time)
- **Display**: Bar chart per queue ("pdf-generation: 12 pending, 0 reserved")
- **Alert**: Any queue >500 pending → **WARN**; >2000 → **PAGE (P2)**
- **Source**: Live

### Widget 3.2 — Failed Jobs (Last 24h)

**From `failed_jobs` table:**

```sql
SELECT COUNT(*) AS failed_24h,
       queue
FROM failed_jobs
WHERE failed_at >= NOW() - INTERVAL 24 HOUR
GROUP BY queue;
```

**From `operasi_metrics_daily` (trend):**

```sql
SELECT tanggal, queue_backlog_max
FROM operasi_metrics_daily
WHERE tanggal >= CURDATE() - INTERVAL 7 DAY
ORDER BY tanggal DESC;
```
- **Refresh**: 60s
- **Display**: Big number ("3 failed in 24h") + 7d sparkline of backlog
- **Alert**: >10 failed in 1h → **WARN**; >50 → **PAGE (P2)**
- **Source**: Live + Daily

### Widget 3.3 — Queue Processing Rate (Jobs/Min)

```sql
SELECT
  COUNT(*) AS jobs_total,
  ROUND(COUNT(*) / 60.0, 1) AS jobs_per_minute
FROM jobs
WHERE created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 1 HOUR);
```
- **Refresh**: 60s
- **Display**: "12.4 jobs/min"
- **Alert**: 0 jobs processed in 15 min with pending >0 → **PAGE (P2)**
- **Source**: Live

### Widget 3.4 — Oldest Pending Job Age (Minutes)

```sql
SELECT
  queue,
  TIMESTAMPDIFF(MINUTE, FROM_UNIXTIME(MIN(created_at)), NOW()) AS oldest_minutes,
  MIN(created_at) AS oldest_created_at
FROM jobs
WHERE reserved_at IS NULL
GROUP BY queue
ORDER BY oldest_minutes DESC;
```
- **Refresh**: 30s (real-time)
- **Display**: "pdf-generation: 47 min old"
- **Alert**: Any job >30 min → **WARN**; >60 min → **PAGE (P2)**
- **Source**: Live

### Widget 3.5 — Worker Count and Status

```sql
-- No direct SQL — relies on supervisorctl status or cache heartbeat
-- Cache keys set by worker health check script:
--   nurisk:queue:worker:pdf-generation → heartbeat timestamp
--   nurisk:queue:worker:default → heartbeat timestamp
SELECT COUNT(*) AS expected_workers;  -- from config, not DB
```
- **Refresh**: 30s (real-time)
- **Display**: "pdf-generation: 2/2 RUNNING | default: 1/1 RUNNING"
- **Alert**: Any worker down >30s → **PAGE (P1)**
- **Source**: Supervisor/Cache (not SQL)

### Quick Query Count & Source

| Widget | Source | Queries |
|--------|--------|---------|
| Queue Backlog | Live | 1 |
| Failed Jobs | Live | 1 |
| Backlog Trend | Daily | 1 |
| Processing Rate | Live | 1 |
| Oldest Job Age | Live | 1 |
| Worker Status | Supervisor | 0 |
| **Total** | | **4 live + 1 daily** |

---

## 4. PDF

**Purpose**: Monitor the PDF generation subsystem, which runs on the dedicated `pdf-generation` queue.

### Widget 4.1 — PDF Success Rate

**From `operasi_metrics_daily` (daily view):**

```sql
SELECT tanggal,
       pdf_success,
       pdf_failed,
       ROUND(pdf_success * 100.0 / NULLIF(pdf_success + pdf_failed, 0), 1) AS success_pct
FROM operasi_metrics_daily
WHERE tanggal >= CURDATE() - INTERVAL 7 DAY
ORDER BY tanggal DESC;
```

**From `failed_jobs` (live, filtered to PDF queue):**

```sql
SELECT COUNT(*) AS failed,
       (SELECT COUNT(*) FROM jobs WHERE queue = 'pdf-generation') AS queued
FROM failed_jobs
WHERE queue = 'pdf-generation'
  AND failed_at >= NOW() - INTERVAL 24 HOUR;
```
- **Refresh**: 60s
- **Display**: "98.2% success (last 7d)"
- **Alert**: Daily success <90% → **WARN**; <75% → **PAGE (P3)**
- **Source**: Daily (chart) + Live (current)

### Widget 4.2 — PDF Generation Duration (Average)

```sql
-- From jobs table payload metadata (decode JSON)
-- Jobs store processing metadata in a custom table or payload
-- Fallback: use queue processing lag as proxy
SELECT
  AVG(TIMESTAMPDIFF(SECOND, FROM_UNIXTIME(created_at), NOW())) AS avg_processing_lag_seconds
FROM jobs
WHERE queue = 'pdf-generation'
  AND reserved_at IS NOT NULL
  AND created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 1 HOUR);
```
- **Refresh**: 60s
- **Display**: "Avg: 12.3s"
- **Alert**: Avg >60s → **WARN**; >300s → **PAGE (P3)**
- **Source**: Live

### Widget 4.3 — PDFs Generated (Last 24h)

```sql
SELECT COUNT(*) AS pdfs_24h
FROM jobs
WHERE queue = 'pdf-generation'
  AND created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 24 HOUR);
```
- **Refresh**: 60s
- **Display**: "342 PDFs in 24h"
- **Alert**: 0 PDFs in 1h during business hours → **WARN**
- **Source**: Live

### Widget 4.4 — Failed PDFs with Error Message

```sql
SELECT id, uuid, queue, LEFT(exception, 500) AS error_preview, failed_at
FROM failed_jobs
WHERE queue = 'pdf-generation'
  AND failed_at >= NOW() - INTERVAL 24 HOUR
ORDER BY failed_at DESC
LIMIT 20;
```
- **Refresh**: 60s
- **Display**: Table (ID, error, timestamp) — expandable rows for full exception
- **Alert**: Same as Widget 4.1 (PDF success rate threshold drives paging)
- **Source**: Live

### Widget 4.5 — PDF Storage Size

```sql
-- Not a SQL query — system command
du -sh /var/www/nurisk/storage/app/public/pdfs
du -sh /var/www/nurisk/storage/app/private/pdfs

-- Also check total PDF count:
SELECT COUNT(*) AS pdf_files
FROM storage_manifest;  -- or use filesystem count
```
- **Refresh**: 300s (5 min)
- **Display**: "1.2 GB (3,450 files)"
- **Alert**: >10 GB → **WARN**; >50 GB → **PAGE (P3)**
- **Source**: Shell

### Quick Query Count & Source

| Widget | Source | Queries |
|--------|--------|---------|
| PDF Success Rate (7d) | Daily | 1 |
| PDF Success Rate (live) | Live | 1 |
| Avg Duration | Live | 1 |
| PDFs Generated | Live | 1 |
| Failed PDFs | Live | 1 |
| Storage Size | Shell | 0 |
| **Total** | | **4 live + 1 daily** |

---

## 5. Conflict

**Purpose**: Monitor sync conflicts between offline devices and the server. Conflicts block data propagation until resolved by a supervisor.

### Widget 5.1 — Conflict Count (Last 24h)

```sql
SELECT COUNT(*) AS conflicts_24h
FROM sync_conflicts
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR;
```
- **Refresh**: 60s
- **Display**: Big number ("47 conflicts today")
- **Alert**: >100 conflicts in 24h → **WARN**; >500 → **PAGE (P3)**
- **Source**: Live

### Widget 5.2 — Conflict Rate (% of Sync Requests)

```sql
SELECT
  COUNT(c.id) AS conflicts,
  COUNT(a.id) AS total_syncs,
  ROUND(COUNT(c.id) * 100.0 / NULLIF(COUNT(a.id), 0), 2) AS conflict_rate_pct
FROM sync_audit_logs a
LEFT JOIN sync_conflicts c ON c.dibuat_pada >= NOW() - INTERVAL 1 HOUR
                           AND c.dibuat_pada <= NOW()
WHERE a.dibuat_pada >= NOW() - INTERVAL 1 HOUR;
```
- **Refresh**: 60s
- **Display**: "0.47% of syncs result in conflict"
- **Alert**: Rate >5% → **WARN**; >15% → **PAGE (P2)**
- **Source**: Live

### Widget 5.3 — Top Conflicted Entities

```sql
SELECT entity_type,
       uuid_entity,
       COUNT(*) AS conflict_count
FROM sync_conflicts
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR
GROUP BY entity_type, uuid_entity
ORDER BY conflict_count DESC
LIMIT 10;
```
- **Refresh**: 60s
- **Display**: Table (entity type, count) — clickable to drill into details
- **Alert**: Any single entity with >10 conflicts in 24h → **WARN**
- **Source**: Live

### Widget 5.4 — Unresolved Conflicts (Older Than 24h)

```sql
SELECT COUNT(*) AS unresolved_old,
       MIN(dibuat_pada) AS oldest_unresolved
FROM sync_conflicts
WHERE resolved_at IS NULL
  AND dibuat_pada < NOW() - INTERVAL 24 HOUR;
```
- **Refresh**: 60s
- **Display**: "3 unresolved conflicts older than 24h (oldest: 2026-06-19)"
- **Alert**: Any conflicts unresolved >48h → **WARN**; >72h → **PAGE (P3)**
- **Source**: Live

### Widget 5.5 — Top Conflict Users

```sql
SELECT md.id_pengguna,
       md.uuid_device,
       COUNT(sc.id) AS conflict_count
FROM sync_conflicts sc
JOIN mobile_devices md ON md.uuid_device = sc.device_uuid
WHERE sc.dibuat_pada >= NOW() - INTERVAL 24 HOUR
GROUP BY md.id_pengguna, md.uuid_device
ORDER BY conflict_count DESC
LIMIT 10;
```
- **Refresh**: 60s
- **Display**: Table (user, device, conflict count)
- **Alert**: Single user >20 conflicts in 24h → **WARN**
- **Source**: Live

### Quick Query Count & Source

| Widget | Source | Queries |
|--------|--------|---------|
| Conflict Count | Live | 1 |
| Conflict Rate | Live | 1 |
| Top Entities | Live | 1 |
| Unresolved Old | Live | 1 |
| Top Users | Live | 1 |
| **Total** | | **5 live + 0 daily** |

---

## 6. Security

**Purpose**: Authentication and authorization health — token usage, revoked devices, rate limiting, and suspicious patterns.

### Widget 6.1 — Active Tokens Count

```sql
SELECT
  COUNT(*) AS total_active,
  COUNT(DISTINCT tokenable_id) AS unique_users,
  COUNT(DISTINCT device_uuid) AS unique_devices
FROM personal_access_tokens
WHERE (expires_at IS NULL OR expires_at > NOW())
  AND tokenable_type = 'App\\Models\\AuthUser';
```
- **Refresh**: 60s
- **Display**: "1,245 active tokens across 342 users, 289 devices"
- **Alert**: Total tokens >5000 → **WARN** (possible token leak); sudden 10x spike → **PAGE (P2)**
- **Source**: Live

### Widget 6.2 — Revoked Devices (Last 24h)

```sql
SELECT COUNT(*) AS revoked_24h
FROM mobile_devices
WHERE status = 'revoked'
  AND diperbarui_pada >= NOW() - INTERVAL 24 HOUR;
```
- **Refresh**: 60s
- **Display**: "2 devices revoked today"
- **Alert**: >10 revocations in 24h → **WARN** (possible compromise)
- **Source**: Live

### Widget 6.3 — Failed Auth Attempts (401 Responses)

```sql
-- From application logs or a dedicated auth_attempts table
-- NURISK logs failed logins via LoginController
-- Fallback: Sentry events tagged with 401 status
SELECT COUNT(*) AS failed_attempts
FROM sync_audit_logs  -- or dedicated auth_log table
WHERE status = 'auth_failed'
  AND dibuat_pada >= NOW() - INTERVAL 24 HOUR;
```
- **Refresh**: 60s
- **Display**: "142 failed attempts in 24h"
- **Alert**: >1000 in 1h → **WARN**; >5000 in 1h → **PAGE (P1)** (brute force)
- **Source**: Live

### Widget 6.4 — Rate Limit Hits (429 Responses)

```sql
-- From application rate limiter logs
-- Laravel throttle middleware logs hits in cache
-- Fallback: count 429 responses from access logs
SELECT COUNT(*) AS rate_limited_24h
FROM sync_audit_logs  -- or access_logs parsing
WHERE status = 'rate_limited'
  AND dibuat_pada >= NOW() - INTERVAL 24 HOUR;
```
- **Refresh**: 60s
- **Display**: "23 rate-limited requests today"
- **Alert**: >100 in 1h → **WARN**; >500 in 1h → **PAGE (P3)**
- **Source**: Live

### Widget 6.5 — Suspicious Activity Log

```sql
-- Combines multiple signals
SELECT 'failed_auth' AS signal_type, COUNT(*) AS count, '24h' AS period
FROM sync_audit_logs
WHERE status = 'auth_failed' AND dibuat_pada >= NOW() - INTERVAL 24 HOUR
UNION ALL
SELECT 'rate_limited', COUNT(*), '24h'
FROM sync_audit_logs
WHERE status = 'rate_limited' AND dibuat_pada >= NOW() - INTERVAL 24 HOUR
UNION ALL
SELECT 'revoked_devices', COUNT(*), '24h'
FROM mobile_devices
WHERE status = 'revoked' AND diperbarui_pada >= NOW() - INTERVAL 24 HOUR
UNION ALL
SELECT 'low_trust_score', COUNT(*), 'all_time'
FROM mobile_devices
WHERE trust_score < 50 AND status = 'active';
```
- **Refresh**: 60s
- **Display**: Summary table of all security signals
- **Alert**: Composite score based on sum of weighted signals > threshold → **PAGE (P2)**
- **Source**: Live

### Quick Query Count & Source

| Widget | Source | Queries |
|--------|--------|---------|
| Active Tokens | Live | 1 |
| Revoked Devices | Live | 1 |
| Failed Auth | Live | 1 |
| Rate Limit Hits | Live | 1 |
| Suspicious Activity | Live | 1 |
| **Total** | | **5 live + 0 daily** |

---

## 7. Backup

**Purpose**: Monitor backup freshness, size, integrity, and estimated restore time. Ensures disaster recovery readiness.

### Widget 7.1 — Last Backup Timestamp

```sql
-- Query backup manifest table (backup_logs or equivalent)
-- If no dedicated table, check filesystem
SELECT MAX(created_at) AS last_backup_at,
       DATEDIFF(NOW(), MAX(created_at)) AS hours_since_backup
FROM backup_logs;  -- hypothetical backup tracking table

-- Or via filesystem:
-- ls -lt /backup/nurisk/sql/*.sql.gz | head -1
-- ls -lt /backup/nurisk/sqlite/*.sqlite.gz | head -1
```
- **Refresh**: 300s (5 min)
- **Display**: "Last backup: 2026-06-20 03:00 (12h ago)"
- **Alert**: >26h since last backup → **WARN**; >48h → **PAGE (P2)**
- **Source**: Log table or filesystem

### Widget 7.2 — Backup Size

```sql
SELECT
  ROUND(SUM(file_size_bytes) / 1073741824, 2) AS total_gb,
  COUNT(*) AS file_count
FROM backup_logs
WHERE created_at >= NOW() - INTERVAL 30 DAY;
```
- **Refresh**: 300s (5 min)
- **Display**: "4.2 GB (30 files, 30-day retention)"
- **Alert**: Backup size >20 GB → **WARN** (storage cost concern)
- **Source**: Log table or filesystem

### Widget 7.3 — Backup Status (Success/Failed)

```sql
SELECT
  COUNT(*) AS total_backups,
  SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS successful,
  SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed,
  SUM(CASE WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) < 24 THEN 1 ELSE 0 END) AS last_24h
FROM backup_logs
WHERE created_at >= NOW() - INTERVAL 7 DAY;
```
- **Refresh**: 300s (5 min)
- **Display**: "30/30 successful this week (100%)"
- **Alert**: Any failed backup in last 7 days → **WARN**; last backup failed → **PAGE (P2)**
- **Source**: Log table

### Widget 7.4 — Estimated Time to Restore

```sql
-- Based on backup size and measured throughput
-- Stored as metadata in backup_logs or computed from last restore drill
SELECT
  ROUND(file_size_bytes / 1048576, 1) AS size_mb,
  ROUND(file_size_bytes / (50 * 1048576), 1) AS estimated_minutes  -- assuming 50 MB/s throughput
FROM backup_logs
ORDER BY created_at DESC
LIMIT 1;
```
- **Refresh**: 300s (5 min)
- **Display**: "~2.5 min at 50 MB/s"
- **Alert**: Estimated restore >30 min → **WARN**; >120 min → **PAGE (P3)**
- **Source**: Computed

### Widget 7.5 — Next Scheduled Backup

```sql
-- Cron schedule: 0 3 * * * (daily at 03:00)
-- Computed: if now < 03:00, today at 03:00; else tomorrow at 03:00
SELECT
  CASE
    WHEN CURTIME() < '03:00:00'
    THEN CONCAT(CURDATE(), ' 03:00:00')
    ELSE CONCAT(CURDATE() + INTERVAL 1 DAY, ' 03:00:00')
  END AS next_scheduled_backup;
```
- **Refresh**: 300s (5 min)
- **Display**: "Next: 2026-06-21 03:00 (in 14h 23m)"
- **Alert**: None (informational)
- **Source**: Computed

### Quick Query Count & Source

| Widget | Source | Queries |
|--------|--------|---------|
| Last Backup | Live/Filesystem | 1 |
| Backup Size | Live | 1 |
| Backup Status | Live | 1 |
| Est. Restore | Live | 1 |
| Next Scheduled | Computed | 1 |
| **Total** | | **4 live + 0 daily** |

---

## 8. Regional Statistics

**Purpose**: Operations visibility into per-PCNU engagement. Helps identify which wilayah are active, which are struggling with sync, and where field support is needed.

### Widget 8.1 — Active PCNUs (Wilayah with Recent Activity)

```sql
-- PCNUs that have had sync, bootstrap, or conflict activity in last 24h
SELECT
  o.id_pcnu,
  p.nama_pcnu,
  COUNT(DISTINCT s.device_uuid) AS active_devices,
  MAX(s.dibuat_pada) AS last_activity
FROM organisasi_pcnu p
JOIN operasi_insiden o ON o.id_pcnu = p.id_pcnu
JOIN sync_audit_logs s ON s.scope_type = 'pcnu' AND s.scope_id = p.id_pcnu
WHERE s.dibuat_pada >= NOW() - INTERVAL 24 HOUR
GROUP BY o.id_pcnu, p.nama_pcnu
HAVING active_devices > 0
ORDER BY active_devices DESC;
```
- **Refresh**: 300s (5 min)
- **Display**: "25 active PCNUs of 40 total"
- **Alert**: Active PCNUs drop to <50% of total → **WARN**
- **Source**: Live

### Widget 8.2 — Syncs Per PCNU (Last 7 Days)

```sql
-- From daily aggregated metrics, grouped by PCNU
-- Requires per-PCNU metrics; falls back to sync_audit_logs scope analysis
SELECT
  p.id_pcnu,
  p.nama_pcnu,
  COUNT(s.id) AS total_syncs_7d
FROM organisasi_pcnu p
LEFT JOIN sync_audit_logs s ON s.scope_type = 'pcnu' AND s.scope_id = p.id_pcnu
  AND s.dibuat_pada >= NOW() - INTERVAL 7 DAY
GROUP BY p.id_pcnu, p.nama_pcnu
ORDER BY total_syncs_7d DESC;
```
- **Refresh**: 300s (5 min)
- **Display**: Bar chart (y-axis: PCNU, x-axis: sync count)
- **Alert**: PCNU with historically >100 syncs/week drops to 0 for 7 days → **WARN**
- **Source**: Live

### Widget 8.3 — Conflicts Per PCNU

```sql
SELECT
  p.id_pcnu,
  p.nama_pcnu,
  COUNT(c.id) AS total_conflicts_7d,
  COUNT(c.id) * 100.0 / NULLIF(
    (SELECT COUNT(*) FROM sync_audit_logs s2
     WHERE s2.scope_type = 'pcnu' AND s2.scope_id = p.id_pcnu
       AND s2.dibuat_pada >= NOW() - INTERVAL 7 DAY), 0
  ) AS conflict_rate_pct
FROM organisasi_pcnu p
LEFT JOIN sync_conflicts c ON c.scope_type = 'pcnu' AND c.scope_id = p.id_pcnu
  AND c.dibuat_pada >= NOW() - INTERVAL 7 DAY
GROUP BY p.id_pcnu, p.nama_pcnu
HAVING total_conflicts_7d > 0
ORDER BY total_conflicts_7d DESC;
```
- **Refresh**: 300s (5 min)
- **Display**: Table sorted by conflict count descending
- **Alert**: Any PCNU with >10% conflict rate → **WARN**
- **Source**: Live

### Widget 8.4 — Devices Per PCNU

```sql
SELECT
  p.id_pcnu,
  p.nama_pcnu,
  COUNT(DISTINCT md.uuid_device) AS total_devices,
  COUNT(DISTINCT md.id_pengguna) AS total_users,
  SUM(CASE WHEN md.status = 'active' THEN 1 ELSE 0 END) AS active_devices,
  SUM(CASE WHEN md.last_sync_at >= NOW() - INTERVAL 7 DAY THEN 1 ELSE 0 END) AS devices_synced_7d
FROM organisasi_pcnu p
LEFT JOIN auth_users u ON u.id_unit = p.id_unit  -- or via scope mapping
LEFT JOIN mobile_devices md ON md.id_pengguna = u.id_pengguna
GROUP BY p.id_pcnu, p.nama_pcnu
ORDER BY total_devices DESC;
```
- **Refresh**: 300s (5 min)
- **Display**: Table (PCNU, total devices, active, synced in 7d)
- **Alert**: PCNU with rapidly dropping active device count → **WARN**
- **Source**: Live

### Widget 8.5 — Top 10 Active PCNUs

```sql
SELECT
  p.id_pcnu,
  p.nama_pcnu,
  COUNT(DISTINCT s.device_uuid) AS unique_devices,
  COUNT(s.id) AS total_syncs,
  ROUND(AVG(s.duration_ms), 1) AS avg_sync_ms,
  MAX(s.dibuat_pada) AS last_sync
FROM organisasi_pcnu p
JOIN sync_audit_logs s ON s.scope_type = 'pcnu' AND s.scope_id = p.id_pcnu
WHERE s.dibuat_pada >= NOW() - INTERVAL 24 HOUR
GROUP BY p.id_pcnu, p.nama_pcnu
ORDER BY total_syncs DESC
LIMIT 10;
```
- **Refresh**: 300s (5 min)
- **Display**: Top 10 leaderboard (rank, name, devices, syncs, avg latency)
- **Alert**: None (informational — used for weekly report)
- **Source**: Live

### Quick Query Count & Source

| Widget | Source | Queries |
|--------|--------|---------|
| Active PCNUs | Live | 1 |
| Syncs per PCNU | Live | 1 |
| Conflicts per PCNU | Live | 1 |
| Devices per PCNU | Live | 1 |
| Top 10 PCNUs | Live | 1 |
| **Total** | | **5 live + 0 daily** |

---

## Caching Strategy

To reduce dashboard DB impact to sustainable levels:

| Scope | Strategy | TTL |
|-------|----------|-----|
| `operasi_metrics_daily` | Pre-aggregated read — no cache needed | — |
| Sync audit live queries (Widgets 2.1–2.5) | Redis cache key `dash:sync:{period}` | 30s |
| Queue status | Cache `dash:queue:*` | 15s |
| Conflict stats | Cache `dash:conflict:*` | 30s |
| Security queries | Cache `dash:security:*` | 60s |
| Regional stats | Cache `dash:regional:*` | 120s |
| Backup status | Cache `dash:backup:*` | 240s |
| Health (DB ping, workers) | No cache — must be real-time | — |

**Cache Invalidation**: None needed — all cache entries are time-based with short TTLs.

## Dashboard Impact Budget

```
Expected queries per full dashboard refresh cycle (all 8 pages):
  Live queries:  36 SQL
  Daily queries:  9 SQL
  Total:         45 SQL

At 60s default refresh → 0.75 QPS average
Peak (30s Health + Queue) → ~1.5 QPS

This is <1% of estimated production query volume (<200 QPS). Acceptable.
```

## Alert Routing

| Priority | Response Time | Channel | Example |
|----------|--------------|---------|---------|
| P1 | Immediate (<5 min) | Phone + PagerDuty | DB down, all workers dead |
| P2 | <15 min | PagerDuty + Slack | Queue backlog >2000, Sentry error spike |
| P3 | <1 hour | Slack only | Conflict rate >15%, backup failed |
| WARN | Next business day | Dashboard badge + email | Disk >85%, single device DoS |

## Implementation Notes

1. **Widgets 1.1, 1.3**: Health checks bypass the Laravel app stack. Use a lightweight PHP script or direct SQL from a monitoring tool (e.g., Nagios, Prometheus exporter).

2. **Widget 1.4**: Sentry API requires an auth token. Store `SENTRY_AUTH_TOKEN` in a secure ops vault, not in `.env`.

3. **Widgets 2.3**: The `NTILE` window function is supported in MySQL 8+ and SQLite 3.25+. For older MySQL (5.7), replace with percentile approximation using `PERCENT_RANK()` or manual subquery.

4. **Widget 6.3, 6.4**: If no `auth_failed`/`rate_limited` status exists in `sync_audit_logs`, create a dedicated `auth_attempts` table:
   ```sql
   CREATE TABLE auth_attempts (
     id BIGINT AUTO_INCREMENT PRIMARY KEY,
     no_hp VARCHAR(20),
     ip_address VARCHAR(45),
     status ENUM('success', 'failed'),
     dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     INDEX idx_auth_attempts_status_dibuat (status, dibuat_pada)
   );
   ```

5. **Widgets 7.1–7.4**: Requires a `backup_logs` table. If not present, create:
   ```sql
   CREATE TABLE backup_logs (
     id BIGINT AUTO_INCREMENT PRIMARY KEY,
     backup_type VARCHAR(20),  -- 'mysql', 'sqlite', 'config'
     file_path VARCHAR(500),
     file_size_bytes BIGINT,
     status ENUM('success', 'failed'),
     error_message TEXT,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     INDEX idx_backup_logs_created (created_at)
   );
   ```

6. **Regional Widgets (8.1–8.5)**: These queries assume `sync_audit_logs` and `sync_conflicts` have `scope_type='pcnu'` and `scope_id` populated. The Phase 15.1 migration added these columns. If PCNU is not populated in these tables, fall back to joining through `mobile_devices → auth_users → organisasi_pcnu`.

7. **Cache warming**: On dashboard page load, warm all cache keys for that page in parallel. This prevents cache-stampede when multiple ops team members open the dashboard simultaneously.

8. **SQLite compatibility**: All queries are written for MySQL 8+. For SQLite:
   - Replace `NOW() - INTERVAL 24 HOUR` with `datetime('now', '-24 hours')`
   - Replace `CURDATE()` with `date('now')`
   - Replace `FROM_UNIXTIME()` with `datetime(created_at, 'unixepoch')`
   - Replace `DATEDIFF()` with `julianday('now') - julianday(date_col)`
   - Replace `TIMESTAMPDIFF()` with `ROUND((julianday('now') - julianday(date_col)) * 1440)`
   - Window functions (NTILE, ROW_NUMBER) work in SQLite 3.25+
