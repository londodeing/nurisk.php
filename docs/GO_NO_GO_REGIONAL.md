# GO / NO-GO Decision — Regional Production Deployment

**Project:** Nurisk (Sistem Informasi Penanggulangan Bencana NU)
**Date:** 2026-06-20
**Phase:** Post Phase 14 — Production Hardening
**Scope:** Regional deployment (multiple PWNU/PCNU regions, production MySQL, queue workers, Sentry monitoring)

---

## 1. Executive Summary

**Decision: ✅ GO FOR REGIONAL (with noted caveats)**
**Confidence Level: HIGH (85/100)**

All Phase 13 security findings are closed. Phase 14 production hardening is complete: query profiling reduced sync overhead from 32→24 queries, device/token lifecycle endpoints are implemented, Sentry integration is configured, deployment infrastructure (nginx, supervisor, cron, backup/restore) is fully documented and scripted, and the production readiness checklist has been established. The sync endpoint query count (24 vs 15 target) is a known P2 non-blocker with a realistic floor of ~21 queries given the current architecture.

No P0 blockers exist. The system is ready for regional deployment with the caveats documented below.

---

## 2. Readiness Scorecard

| Category | Score | Rationale |
|---|---|---|
| Query Performance | **70** | Sync endpoint at 24 queries (vs 15 target, down from 32 baseline). All other endpoints within target. Realistic floor ~21 given auth+sync architecture. |
| Security Hardening | **95** | All Phase 13 findings closed: RoleMiddleware, ScopeMiddleware, AuthUserObserver, Sanctum token expiry, mass assignment hardening, rate limiting. 16/16 regression tests pass. |
| Monitoring & Observability | **90** | Sentry SDK integrated with DSN config, structured JSON logging, health endpoint (DB/cache/storage/queue/disk/migration/sync), correlation ID middleware. Observability gaps documented but non-blocking. |
| Device & Token Lifecycle | **90** | Endpoints implemented (index, destroy, logoutAll), device_uuid column on personal_access_tokens, MobileDevice model with status lifecycle, Sanctum 30-day expiry. |
| Operational Readiness | **90** | Zero-downtime deploy script, supervisor queue config (pdf-generation + default), nginx with SSL + security headers, cron jobs for backup/health/disk/queue alerts, logrotate, runbook referenced. |
| Data Integrity | **95** | Offline sync benchmarked at ~376 ch/s (1K-50K changes), tombstone tracking, conflict detection, 14/14 sync tests pass, cursor-based pull sync verified. |
| Backup & Recovery | **85** | SQLite DR drill: RTO 0.06s, RPO 0. MySQL backup script documented (mysqldump + rsync, 30-day retention). MySQL DR not yet live-verified in production environment. |
| **Overall Score** | **88** | Weighted average (equal weight across 7 categories). |

---

## 3. P0 Findings — MUST Fix Before Regional

**None.** All critical and high-severity items from Phase 13 (CR-01 token escalation, CR-02 token expiry, HR-02 mass assignment, HR-05 brute-force protection) are closed and verified. No remaining P0 findings.

---

## 4. P1 Findings — SHOULD Fix Before Regional

| # | Finding | Category | Evidence |
|---|---|---|---|
| 1 | **Production load testing pending** — Load test conducted only on `php artisan serve` (single-threaded dev server). P95 > 5s at 250 VU. Production with Octane/RoadRunner + MySQL will differ. | Performance | `docs/reports/load-test-report.md` |
| 2 | **MySQL DR not verified in production** — Disaster recovery drill passed on SQLite (RTO 0.06s). MySQL backup script exists but restore procedure not live-tested against a production-like MySQL instance. | Operations | `docs/reports/disaster-recovery-drill.md` |
| 3 | **`device_uuid` not set at token creation** — Sanctum tokens do not carry `device_uuid` unless retroactively batch-updated. Device revocation depends on token deletion rather than token-to-device FK linkage. | Device Lifecycle | `docs/reports/device-token-audit.md:84-99` |

---

## 5. P2 Findings — Nice-to-Have

| # | Finding | Category | Evidence |
|---|---|---|---|
| 1 | **Sync endpoint at 24 queries** (target 15). Realistic floor ~21 with current architecture — recommended revised target of ≤25. | Performance | `docs/reports/query-profile-production.md:111` |
| 2 | **Correlation ID not propagated to log context** — `Log::withContext()` not called. Log entries lack correlation ID field. | Observability | `docs/reports/observability-review.md:25` |
| 3 | **CorrelationIdMiddleware dead code** — Checks `_request_id` but sets `_correlation_id`; `X-Request-ID` response branch is unreachable. | Observability | `docs/reports/observability-review.md:26` |
| 4 | **No request duration logging middleware** — No standardized per-request timing in logs. | Observability | `docs/reports/observability-review.md:61-68` |
| 5 | **No queue job duration tracking** — No Horizon/Pulse; queue health relies on pending/failed counts only. | Observability | `docs/reports/observability-review.md:33` |
| 6 | **`$with = ["insiden"]` on OperasiPenugasan** — Always eager-loads insiden even when not needed. | Performance | `docs/reports/query-profile-production.md:141` |
| 7 | **AuthRoleSeeder duplicate key on re-run** — Low-severity bug, workaround exists. | Code Quality | `docs/GO_NO_GO_PILOT.md:39` |

---

## 6. Regional Deployment Prerequisites

| # | Item | Status | Notes |
|---|---|---|---|
| 1 | Production MySQL database provisioned | ⬜ TODO | `.env.production` template ready (`DB_CONNECTION=mysql`). Schema via `php artisan migrate --force`. |
| 2 | Sentry DSN configured | ⬜ TODO | `SENTRY_LARAVEL_DSN` in `.env`. `config/sentry.php` ready. Add `SENTRY_ENABLE_LOGS=true`. |
| 3 | Queue workers configured with supervisor | ✅ DONE | `deployment/supervisor/nurisk-queue.conf` — 2 pdf-generation workers + 1 default worker. |
| 4 | Backup cron jobs set up | ✅ DONE | `deployment/crontab/nurisk` — daily backup at 03:00, 30-day retention. Script: `deployment/scripts/backup.sh`. |
| 5 | SSL/TLS certificates | ✅ DONE | Let's Encrypt referenced in `deployment/nginx/nurisk.conf`. TLSv1.2 + TLSv1.3 only. |
| 6 | Domain/DNS configuration | ⬜ TODO | `server_name nurisk.or.id www.nurisk.or.id` in nginx config. DNS records must be provisioned. |
| 7 | Load testing completed | ⬜ TODO | **P1.** Requires Octane/RoadRunner + MySQL deployment. Use `load_test.cjs` (k6). Target P95 < 500ms. |

---

## 7. Decision

### ✅ GO FOR REGIONAL

**Rationale:**

The system has undergone two full phases of production hardening (Phase 13 — Security Closure, Phase 14 — Production Hardening). All critical (CR-01, CR-02) and high-severity (HR-02, HR-05) findings are closed and verified by regression tests. The offline sync system is benchmarked and stable (~376 ch/s). Deployment infrastructure (nginx, supervisor, cron, backup, logrotate) is fully scripted and version-controlled. The 62 physical migration files in `database/migrations/` cover the full schema — the earlier migration blocker (ARCH-002) has been resolved.

The sync endpoint query count of 24 (vs target 15) is a known architectural constraint, not a defect — the realistic floor is ~21 queries. This is tracked as P2 and should not block regional rollout. The three P1 findings (production load testing, MySQL DR verification, device_uuid token association) should be addressed during the regional pilot window but do not warrant a NO-GO.

**Risk appetite:** Regional deployment implies controlled rollout to known PWNU/PCNU administrators with rollback capability (zero-downtime deploy, feature flag: `PILOT_MODE=true`). The blast radius of any issue is limited to participating regions.

**Go-live sequence:**
1. Provision MySQL database → `php artisan migrate --force` → `php artisan db:seed`
2. Configure Sentry DSN in `.env`
3. Set up DNS A/AAAA records → verify SSL certificate provisioning
4. Run load test on production infrastructure (k6, target P95 < 500ms)
5. Deploy via `deployment/scripts/deploy.sh`
6. Verify health: `GET /health` returns all checks OK
7. Verify MySQL DR: run restore drill on staging/standby
8. Enable user access for pilot regions
