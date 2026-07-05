# Risk Register — Nurisk Pilot Deployment

**Date:** 2026-06-20  
**Owner:** Production Readiness Team  
**Classification:** CRITICAL / HIGH / MEDIUM / LOW

---

## Critical Risks

| ID | Risk | Impact | Likelihood | Mitigation | Owner |
|----|------|--------|------------|------------|-------|
| **CR-01** | **API routes have no role/scope middleware** — any authenticated user can access all API endpoints | Unauthorized data access, privilege escalation | High | Add `role` and `scope` middleware to all API route groups before pilot | Security Team |
| **CR-02** | **Sanctum tokens never expire** (`expiration => null`) | Leaked tokens grant permanent access | Medium | Set `expiration` to 7 days; implement token rotation on device refresh | Platform Team |

---

## High Risks

| ID | Risk | Impact | Likelihood | Mitigation | Owner |
|----|------|--------|------------|------------|-------|
| **HR-01** | **Sync POST endpoint exceeds query target** (32 queries/request vs 20 max) | DB load under concurrent sync; risk of connection pool exhaustion | High | Implement request-scoped auth caching; batch cursor queries; consolidate grouped queries | Backend Team |
| **HR-02** | **`kata_sandi`, `id_peran`, `status_akun` in AuthUser `$fillable`** | Mass assignment vulnerability could allow privilege escalation | Medium | Remove sensitive fields from `$fillable`; use dedicated service methods | Security Team |
| **HR-03** | **No automated backup verification** — backup exists but is not tested weekly | Silent backup failure discovered only during actual disaster | Medium | Automate weekly restore drill in staging; alert on backup size zero | Platform Team |
| **HR-04** | **Built-in PHP server in load test** — P95 latency exceeds target due to single-threaded bottleneck | Risk that production performance is underestimated | Low | Deploy with Octane/RoadRunner; re-run load test in production-like environment | Platform Team |
| **HR-05** | **No token revocation on account deactivation/password change** | Deactivated users retain valid API tokens | Medium | Add event listener to revoke tokens on `status_akun` change or password update | Security Team |

---

## Medium Risks

| ID | Risk | Impact | Likelihood | Mitigation | Owner |
|----|------|--------|------------|------------|-------|
| **MR-01** | **Role middleware uses flat string comparison** — `level_otoritas` hierarchy not enforced | Role-based access bypass if role names are inconsistent | Low | Implement hierarchical permission model using `level_otoritas` | Backend Team |
| **MR-02** | **Correlation ID not propagated to log context** | Cannot correlate logs across requests during debugging | Medium | Add `Log::withContext()` in CorrelationIdMiddleware | Backend Team |
| **MR-03** | **No request duration logging** — no observability into slow requests | Cannot identify performance regression without APM tool | Medium | Implement `RequestDurationMiddleware` as recommended in Task 13.6 | Platform Team |
| **MR-04** | **No slow query logging** — queries >100ms undetected | Production performance degradation goes unnoticed | Medium | Enable `DB::whenQueryingForLongerThan(100)` in AppServiceProvider | Platform Team |
| **MR-05** | **Queue job duration not tracked** — no per-job processing time visibility | Cannot detect stuck or slow queue workers | Low | Add before/after timing to job `handle()` method | Backend Team |
| **MR-06** | **MySQL production backup not tested** — current drill only validates SQLite | MySQL dump/restore procedure may have unknown issues | Medium | Adapt `drill:backup-restore` command for MySQL target before pilot | Platform Team |
| **MR-07** | **`X-Request-ID` response header is dead code** — correlation middleware sets `_correlation_id` but checks `_request_id` | Inconsistent header behavior | Low | Fix the variable name in CorrelationIdMiddleware | Backend Team |

---

## Low Risks

| ID | Risk | Impact | Likelihood | Mitigation | Owner |
|----|------|--------|------------|------------|-------|
| **LR-01** | **CORS `max_age=0`** — preflight sent on every cross-origin request | Increased network overhead for SPA/mobile clients | Low | Set `max_age=86400` in `config/cors.php` | Frontend Team |
| **LR-02** | **Sanctum `token_prefix` empty** — reduced protection against secret scanning | Tokens in logs could be detected without prefix | Low | Set a non-empty `token_prefix` in environment | Security Team |
| **LR-03** | **No per-endpoint rate limiting** — sync and read endpoints share same 60 req/min limit | Sync operations may hit limit during batch upload | Low | Add separate rate limiters for sync vs read endpoints | Backend Team |
| **LR-04** | **Index write overhead** — 11 new indexes added; `operasi_insiden` now has 9 indexes | INSERT/UPDATE performance impact on write-heavy tables | Low | Monitor slow query log after deployment; remove redundant indexes if needed | Platform Team |
| **LR-05** | **Soft-delete filtering not indexed** — `WHERE dihapus_pada IS NULL` scans | Query degradation on tables with many soft-deleted rows | Low | Consider composite indexes ending with `dihapus_pada` if queries become slow | Backend Team |

---

## Risk Summary

| Severity | Count | Status |
|----------|-------|--------|
| Critical | 2 | Unmitigated — must be resolved before pilot |
| High | 5 | One partially mitigated (load test baseline); others need action |
| Medium | 7 | Documented for post-pilot backlog |
| Low | 5 | Acceptable risk for pilot |

### Critical Path to Pilot

The following must be completed before GO decision:

1. **CR-01**: Add role/scope middleware to API routes
2. **CR-02**: Set Sanctum token expiration
3. **HR-02**: Remove sensitive fields from AuthUser `$fillable`
4. **HR-05**: Add token revocation hooks
5. **HR-01**: Optimize sync endpoint queries (auth caching + batch cursors)

---

*Generated from Phase 13 audit findings (Tasks 13.1–13.8)*
