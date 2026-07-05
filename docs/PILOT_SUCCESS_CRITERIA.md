# Pilot Success Criteria — Phase 15.8

> **Project:** NURISK — Offline-First Disaster Response Platform  
> **Phase:** 15.8 — Pilot Deployment  
> **Baseline Metrics (from profiling):** Sync 24 qu/310ms | Bootstrap 15 qu | PDF ~72ms avg | Token 30d expiry | Rate 60 req/min | MobileDevice trust_score default 100 | 14/14 sync tests passing

---

## 1. Sync Reliability

| # | KPI | Target | Measurement Method | Priority | Consequence of Failure |
|---|-----|--------|-------------------|----------|------------------------|
| SR-1 | Sync success rate | > 99% of all sync requests | Log aggregation (Grafana/Loki) on `sync_logs` table — count success vs total | P0 | Pilot is blocked: offline data loss renders app unreliable |
| SR-2 | Sync average duration | < 1 sec (P95 < 3 sec) | Query profiling on `sync_logs.duration_ms` — `AVG()` and percentile via SQL | P0 | User experience degrades; app feels unresponsive |
| SR-3 | Bootstrap success rate | > 99% of all bootstrap requests | Log aggregation on `bootstrap_logs` — success / total | P1 | New devices or re-installs fail to initialise; pilot users locked out |
| SR-4 | Bootstrap average duration | < 30 seconds | Query on `bootstrap_logs.duration_ms` — `AVG(duration_ms)` | P1 | UI hangs on first launch; user perceives app as broken |
| SR-5 | Cursor consistency | Server cursor ≥ client cursor for every sync | Sync integration test (`cursor_consistency_test`) — no regressions allowed | P0 | Data loss or missed updates; violates offline-first contract |

## 2. Queue Reliability

| # | KPI | Target | Measurement Method | Priority | Consequence of Failure |
|---|-----|--------|-------------------|----------|------------------------|
| QR-1 | Queue success rate | > 99% of all jobs | Horizon / Laravel queue monitor — `failed_jobs` vs total dispatched | P0 | Background work (PDF, sync) piles up; core features break |
| QR-2 | Queue backlog | < 100 jobs waiting at any time | `SELECT COUNT(*) FROM jobs` (or Horizon dashboard) | P1 | Latency spikes; users wait minutes for PDF exports |
| QR-3 | PDF generation success rate | > 99% | `failed_jobs` filtered by PDF job class / Sentry | P1 | Reports fail silently; field teams lose documentation |
| QR-4 | PDF generation duration | < 5 seconds per job (P95) | `monitored_jobs` table or Sentry spans on PDF job | P1 | UI feedback stalls; user retries, causing cascading enqueues |

## 3. Conflict Management

| # | KPI | Target | Measurement Method | Priority | Consequence of Failure |
|---|-----|--------|-------------------|----------|------------------------|
| CM-1 | Conflict resolution time | 100% resolved within 24 hours | `SELECT ... FROM conflict_log WHERE resolved_at IS NULL AND created_at < NOW() - INTERVAL 24 HOUR` | P0 | Stale conflicts cause silent data divergence |
| CM-2 | Conflict rate | < 5% of sync requests | `conflict_count / sync_count * 100` from daily logs | P1 | High conflict = weak CRDT or bad cursor logic; offline trust erodes |
| CM-3 | Unresolved old conflicts | Zero conflicts unresolved > 72 hours | Same query as CM-1 but with 72 h threshold — must return 0 rows | P1 | Abandoned conflicts = unrecoverable data corruption |

## 4. System Availability

| # | KPI | Target | Measurement Method | Priority | Consequence of Failure |
|---|-----|--------|-------------------|----------|------------------------|
| SA-1 | API endpoint availability | > 99% uptime (monthly) | Uptime monitor (e.g. Oh Dear / Pingdom) hitting `/health` + `/api/*` every 60 s | P0 | App is offline for field workers; daily ops halt |
| SA-2 | Health endpoint response | < 500 ms (P99) | Monitoring on `/health` — response time percentile | P1 | Monitoring false-positives; ops team loses confidence |
| SA-3 | Queue worker uptime | No downtime > 5 consecutive minutes | Supervisor/systemd logs + Horizon heartbeat check | P1 | Silent queue backlog; PDFs and sync jobs delayed unbounded |

## 5. Security

| # | KPI | Target | Measurement Method | Priority | Consequence of Failure |
|---|-----|--------|-------------------|----------|------------------------|
| SE-1 | Privilege escalation incidents | Zero (0) | Auth audit log + penetration test report | P0 | Regulatory/legal liability; immediate pilot abort |
| SE-2 | Token revocation speed | < 1 sec per device | Integration test (`tokens:revoke` command timed) | P1 | Stolen tokens usable too long; data exposure window |
| SE-3 | Route middleware coverage | 100% of API routes protected by role + scope | Automated route audit script (`routes:audit`) | P1 | Unprotected endpoint = vector for data leak |
| SE-4 | Rate-limit false positives | Zero legitimate traffic blocked | Rate-limit logs + alert count vs traffic analysis | P2 | Frustrated users abandon pilot; field ops disrupted |

## 6. Operational Readiness

| # | KPI | Target | Measurement Method | Priority | Consequence of Failure |
|---|-----|--------|-------------------|----------|------------------------|
| OR-1 | RUNBOOK coverage | All documented scenarios executable step-by-step | Manual review of RUNBOOK.md + dry-run walk-through with ops | P0 | On-call cannot recover from outage; pilot downtime becomes indefinite |
| OR-2 | Daily backup completion | 100% of daily backups successful | Backup monitoring tool / cron log — `exit code 0` every day | P1 | Data loss on failure; restore impossible |
| OR-3 | Quarterly restore drill | Passes at least once per quarter | Restore from backup into staging; all smoke tests green | P2 | Backup may be silently corrupt until disaster strikes |

---

## Scoring System

Each KPI receives a **pass (1)** or **fail (0)** boolean.

### Weights
| Priority | Weight |
|----------|--------|
| P0       | 3×     |
| P1       | 2×     |
| P2       | 1×     |

### Calculation

| KPI | Priority | Weight | Example |
|-----|----------|--------|---------|
| SR-1 | P0 | 3 | — |
| SR-2 | P0 | 3 | — |
| SR-3 | P1 | 2 | — |
| SR-4 | P1 | 2 | — |
| SR-5 | P0 | 3 | — |
| QR-1 | P0 | 3 | — |
| QR-2 | P1 | 2 | — |
| QR-3 | P1 | 2 | — |
| QR-4 | P1 | 2 | — |
| CM-1 | P0 | 3 | — |
| CM-2 | P1 | 2 | — |
| CM-3 | P1 | 2 | — |
| SA-1 | P0 | 3 | — |
| SA-2 | P1 | 2 | — |
| SA-3 | P1 | 2 | — |
| SE-1 | P0 | 3 | — |
| SE-2 | P1 | 2 | — |
| SE-3 | P1 | 2 | — |
| SE-4 | P2 | 1 | — |
| OR-1 | P0 | 3 | — |
| OR-2 | P1 | 2 | — |
| OR-3 | P2 | 1 | — |

**Total possible score** = (7 × 3) + (12 × 2) + (2 × 1) = 21 + 24 + 2 = **47**

**Pass threshold** = 85% × 47 ≈ **40 points**

> A criterion with weight 3 that fails loses 3 points; the pilot passes only if the weighted score ≥ 40.

---

## Stretch Goals (Aspirational — Not Required for Pilot)

| # | Goal | Rationale |
|---|------|-----------|
| SG-1 | Sync average duration < 500 ms (P95 < 1.5 s) | Sub-second sync across weak-signal areas |
| SG-2 | Queue backlog < 10 jobs at peak | Near-real-time PDF generation |
| SG-3 | Conflict rate < 1% of sync requests | Full CRDT maturity |
| SG-4 | Backup restore drill fully automated via CI pipeline | Zero-touch disaster recovery |
| SG-5 | 100% API route response < 200 ms (P99) | Performance headroom for regional scale-out |

---

## Sign-Off

| Role | Name | Date | Decision | Notes |
|------|------|------|----------|-------|
| Operational Lead | | | Pass / Fail / Conditional Pass | |
| Security Lead | | | Pass / Fail / Conditional Pass | |
| Technical Lead | | | Pass / Fail / Conditional Pass | |
