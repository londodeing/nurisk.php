# Capacity Planning — Phase 15.5

> **Project:** NURISK Disaster Response App
> **Date:** 2026-06-20
> **Base Profile:** Laravel + SQLite (dev) → MariaDB (prod), PHP 8.x, Dompdf

---

## Assumptions

| Assumption | Value | Source |
|---|---|---|
| Sync endpoint CPU time per request | ~300 ms | Profiling (SQLite, 24 queries) |
| Sync changes per request (average) | 5 | App logic estimate |
| Peak concurrency ratio (active sync) | 20% of total users | Standard SaaS estimate |
| PDF generation per user per day | 10% | Estimate from pilot |
| PDF generation CPU time | ~72 ms | Stress test (0 failures at 100/500/1000 jobs) |
| PDF peak memory | 50–100 MB | Dompdf known behaviour |
| PDF storage | ~100 KB | Actual output size |
| SyncAuditLog per request | ~200 B | Schema estimate |
| SyncConflict per conflict (JSON blobs) | ~2 KB | Schema estimate |
| SyncCursor per change | ~150 B | Schema estimate |
| SyncTombstone per delete | ~200 B | Schema estimate |
| Concurrent conflict rate | 1% of sync requests | Estimate (offline sync maturity) |
| Log retention | 30 days | Operations standard |
| SQLite → MariaDB cut-over | > 500 users | See Bottleneck Analysis |
| MariaDB InnoDB buffer pool | 70% of available RAM | Standard recommendation |
| PHP-FPM worker memory | 30–50 MB per worker | Observed baseline |
| Queue driver | Database (default) | Current config; upgrade at > 500 users |

---

## 1. CPU

### Formula

| Variable | Expression |
|---|---|
| Peak concurrent sync requests | `users × 0.20` |
| Sync CPU load | `peak_concurrent × 0.300 s` |
| PDF CPU load | `users × 0.10 ÷ 86400 × 0.072 s` → negligible at low tiers |

### Per-tier

| Users | Peak Concurrent Sync | Sync CPU Load (core·s/s) | PDF CPU Load (core·s/s) | **Total CPU Load** | Recommended vCPUs |
|---|---|---|---|---|---|
| 100 | 20 | 6.0 | < 0.001 | 6.0 | **1–2** |
| 500 | 100 | 30.0 | < 0.001 | 30.0 | **2–4** |
| 1000 | 200 | 60.0 | < 0.001 | 60.0 | **4–8** |
| 5000 | 1000 | 300.0 | < 0.001 | 300.0 | **8–16** |

> **Note:** CPU load = core·seconds per second (cores at 100% utilisation). Real-world overhead from PHP-FPM bootstrap, I/O wait, and DB queries adds 20–40%. Recommended vCPU range accounts for this.

---

## 2. RAM

### PHP-FPM Workers

| Users | Peak Concurrent | Workers Needed | Memory per Worker | **Total FPM RAM** |
|---|---|---|---|---|
| 100 | 20 | 25 (buffer) | 40 MB | **1.0 GB** |
| 500 | 100 | 120 (buffer) | 40 MB | **4.8 GB** |
| 1000 | 200 | 240 (buffer) | 40 MB | **9.6 GB** |
| 5000 | 1000 | 1200 (buffer) | 40 MB | **48.0 GB** |

> Workers = `ceil(peak_concurrent × 1.2)` for headroom.

### Queue Workers

| Users | Recommended Workers | Memory per Worker | **Total Queue RAM** |
|---|---|---|---|
| 100 | 1 | 40 MB | 40 MB |
| 500 | 2 | 40 MB | 80 MB |
| 1000 | 4 | 40 MB | 160 MB |
| 5000 | 10 | 40 MB | 400 MB |

### PDF Workers (dedicated)

| Users | Concurrent PDFs | Workers | Memory per Worker (peak) | **Total PDF RAM** |
|---|---|---|---|---|
| 100 | ~1 | 1 | 100 MB | 100 MB |
| 500 | ~3 | 4 | 100 MB | 400 MB |
| 1000 | ~6 | 8 | 100 MB | 800 MB |
| 5000 | ~29 | 32 | 100 MB | 3200 MB |

### MariaDB Buffer Pool

| Users | App Server RAM (estimate) | **Buffer Pool (70% of DB RAM)** |
|---|---|---|
| 100 | 2 GB | 1.4 GB |
| 500 | 8 GB | 5.6 GB |
| 1000 | 16 GB | 11.2 GB |
| 5000 | 64 GB | 44.8 GB |

---

## 3. Storage

### Daily Growth Rates

| Table / Item | Per-unit Size | 100 users | 500 users | 1000 users | 5000 users |
|---|---|---|---|---|---|
| **SyncAuditLog** (200 B × syncs) | 200 B | ~40 KB | ~200 KB | ~400 KB | ~2 MB |
| **SyncConflict** (2 KB × 1% conflicts) | 2 KB | ~4 KB | ~20 KB | ~40 KB | ~200 KB |
| **SyncCursor** (150 B × 5 changes) | 150 B | ~150 KB | ~750 KB | ~1.5 MB | ~7.5 MB |
| **SyncTombstone** (200 B × 5% deletes) | 200 B | ~10 KB | ~50 KB | ~100 KB | ~500 KB |
| **PDF storage** (100 KB × 10% users) | 100 KB | ~1 MB | ~5 MB | ~10 MB | ~50 MB |
| **Nginx access log** | ~1 KB/req | ~20 MB/month | ~100 MB/month | ~200 MB/month | ~1 GB/month |
| **Nginx error log** | — | ~50 MB/month | ~100 MB/month | ~200 MB/month | ~500 MB/month |
| **PHP-FPM slow/error log** | — | ~50 MB/month | ~100 MB/month | ~200 MB/month | ~500 MB/month |
| **SQLite WAL** (queue/cache) | — | ~100 MB steady | ~500 MB steady | N/A (migrated) | N/A (migrated) |

### Monthly Storage Summary

| Users | DB + PDF Data | Logs (30-day) | **Total Monthly Growth** |
|---|---|---|---|
| 100 | ~6 MB | ~120 MB | **~130 MB** |
| 500 | ~30 MB | ~300 MB | **~330 MB** |
| 1000 | ~60 MB | ~600 MB | **~660 MB** |
| 5000 | ~300 MB | ~2000 MB | **~2.3 GB** |

---

## 4. MariaDB

### Connection Pool

| Users | Max FPM Workers | Queue Workers | PDF Workers | Overhead | **Total Connections** |
|---|---|---|---|---|---|
| 100 | 25 | 1 | 1 | 5 | **32** |
| 500 | 120 | 2 | 4 | 10 | **136** |
| 1000 | 240 | 4 | 8 | 15 | **267** |
| 5000 | 1200 | 10 | 32 | 25 | **1267** |

> MariaDB default `max_connections` = 151. Must be raised at 500+ users.

### InnoDB Buffer Pool

| Users | DB Server RAM | **InnoDB Buffer Pool (70%)** |
|---|---|---|
| 100 | 2 GB | 1.4 GB |
| 500 | 8 GB | 5.6 GB |
| 1000 | 16 GB | 11.2 GB |
| 5000 | 64 GB | 44.8 GB |

### Disk IOPS Estimate

| Users | Sync Req/s (peak) | Queries per Sync | IOPS per Req | **Peak IOPS** |
|---|---|---|---|---|
| 100 | 20 | 24 | ~10 | **~200** |
| 500 | 100 | 24 | ~10 | **~1000** |
| 1000 | 200 | 24 | ~10 | **~2000** |
| 5000 | 1000 | 24 | ~10 | **~10000** |

> IOPS per request ≈ 10 after factoring index seeks, WAL writes, and buffer pool hits. A standard SSD (5000–10000 IOPS) suffices up to 1000 users. At 5000 users, provision a high-IOPS SSD or NVMe array.

---

## 5. Queue

### Database Queue (default)

| Users | Daily Jobs | `jobs` Table Rows (peak) | Recommended Workers | Notes |
|---|---|---|---|---|
| 100 | ~1000 | 10–50 | 1 | Adequate; use SQLite or MariaDB |
| 500 | ~5000 | 50–200 | 2 | Adequate; monitor `jobs` table size |
| 1000 | ~10000 | 100–500 | 4 | Consider Redis upgrade at this tier |
| 5000 | ~50000 | 500–2500 | 10 | Redis strongly recommended |

### Redis Upgrade Threshold

| Condition | Threshold | Action |
|---|---|---|
| `jobs` table consistently > 1000 rows | > 1000 users | Migrate queue to Redis |
| Queue latency > 5 s during peak | > 1000 users | Migrate queue to Redis |
| PDF queue backlog > 100 pending | > 500 users | Add dedicated PDF worker |

---

## 6. PDF Generation

| Users | Daily PDFs | Peak Concurrent | Dompdf Workers | Peak Memory | Storage/Day |
|---|---|---|---|---|---|
| 100 | 10 | ~1 | 1 | 100 MB | 1 MB |
| 500 | 50 | ~3 | 4 | 400 MB | 5 MB |
| 1000 | 100 | ~6 | 8 | 800 MB | 10 MB |
| 5000 | 500 | ~29 | 32 | 3.2 GB | 50 MB |

> Dompdf is single-threaded and memory-intensive. At 5000 users, consider switching to a headless browser (Chrome Puppeteer/Browsershot) for better memory stability and performance.

---

## 7. Summary Table

| Metric | 100 users | 500 users | 1000 users | 5000 users |
|---|---|---|---|---|
| **App Server** | 2 vCPU, 2 GB RAM | 4 vCPU, 8 GB RAM | 8 vCPU, 16 GB RAM | 16 vCPU, 64 GB RAM |
| **DB Server** | 2 vCPU, 2 GB RAM, 50 GB SSD | 4 vCPU, 8 GB RAM, 100 GB SSD | 8 vCPU, 16 GB RAM, 200 GB SSD | 16 vCPU, 64 GB RAM, 500 GB NVMe |
| **Daily Storage Growth** | ~130 MB/month | ~330 MB/month | ~660 MB/month | ~2.3 GB/month |
| **Peak Sync QPS** | 20 | 100 | 200 | 1000 |
| **Recommended FPM Workers** | 25 | 120 | 240 | 1200 |
| **Recommended Queue Workers** | 1 | 2 | 4 | 10 |
| **Recommended PDF Workers** | 1 | 4 | 8 | 32 |
| **MariaDB max_connections** | 32 | 136 | 267 | 1267 |
| **InnoDB Buffer Pool** | 1.4 GB | 5.6 GB | 11.2 GB | 44.8 GB |

---

## 8. Bottleneck Analysis

### By Tier

| Tier | First Bottleneck | Symptoms | Mitigation |
|---|---|---|---|
| **100 users** | None (over-provisioned) | — | Single server OK. SQLite acceptable for dev/staging. |
| **500 users** | PHP-FPM workers (CPU) | FPM pool exhaustion under peak sync | Increase `pm.max_children`. Separate MariaDB to dedicated host. Migrate from SQLite to MariaDB. |
| **1000 users** | MariaDB connection pool + IOPS | Slow queries, `max_connections` errors | Raise `max_connections`. Add query caching. Introduce Redis for queue/cache. Tune InnoDB buffers. |
| **5000 users** | FPM RAM + DB IOPS | Memory pressure on app server; disk queue on DB | Horizontally scale app servers behind load balancer. Upgrade DB to NVMe. Consider read replicas. Separate PDF workers to dedicated instances. |

### Migration Triggers

| Migration | Trigger | Notes |
|---|---|---|
| **SQLite → MariaDB** | > 500 users or concurrent writes > 50/s | SQLite WAL mode degrades under write concurrency. MariaDB required for connection pooling. |
| **Redis for Queue** | > 1000 users or `jobs` table > 1000 rows | Database queue polling creates unnecessary load. Redis reduces latency and removes table locks. |
| **Redis for Cache** | > 1000 users | Leverage existing Redis instance. Cache frequent queries (config, lookups) to reduce DB load. |
| **Dedicated PDF Workers** | > 500 users or backlog > 100 | Separate queue + worker process. Prevents PDF memory spikes from starving FPM. |
| **Horizontal Scaling** | > 5000 users | Multiple app servers behind load balancer. Shared Redis + MariaDB. Sticky sessions not required (stateless API). |

---

## 9. Key Recommendations

1. **Start on SQLite, plan for MariaDB at 500 users.** No need to over-engineer early; just have the migration script ready.
2. **Separate DB from app server at 500 users.** Co-located DB competes for FPM RAM.
3. **Introduce Redis at 1000 users.** Solves queue polling overhead and provides cache layer.
4. **Separate PDF workers at 500 users.** Protects FPM workers from memory spikes during PDF generation.
5. **Plan horizontal scaling at 5000 users.** Architecture should treat app servers as ephemeral; offload sessions and queues to Redis, file storage to S3-compatible object store.
6. **Monitor `jobs` table size.** If it consistently exceeds 1000 rows, move queue to Redis regardless of user count.
7. **Monitor FPM `max_children` saturation.** When `pm.max_children` is regularly reached during peak, either increase it (more RAM) or scale out.

---

*End of document.*
