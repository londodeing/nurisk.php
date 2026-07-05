# Query Profile Report — Production Hardening

**Date:** 2026-06-20  
**Target:** Phase 14.1 — Measure & optimize database query counts for all sync/API endpoints.  
**Method:** Single iteration per isolated test method using `DB::enableQueryLog()`. Each test creates minimal seed data (3 rows per entity) and one sync change. SQLite with `:memory:` database.

---

## 1. Summary

| Endpoint | Queries | Success | Target |
|---|---|---|---|
| POST /api/v1/sync | **24** | ❌ ABOVE TARGET | ≤15 |
| GET /api/v1/sync/status | **11** | ✅ PASS | ≤20 |
| GET /api/v1/sync/metrics | **10** | ✅ PASS | ≤20 |
| POST /api/v1/bootstrap | **15** | ✅ PASS | ≤30 |
| GET /api/v1/assessment | **7** | ✅ PASS | ≤20 |
| GET /api/v1/penugasan | **11** | ✅ PASS | ≤20 |

### Trend (Sync endpoint)

| Phase | Queries | Change |
|---|---|---|
| Before Phase 4 (per-cursor N+1) | ~32+ | Baseline |
| After Phase 4 (batch cursor/entity) | ~32 | Initial |
| After `validateRecordScope` caching | 28 | −4 |
| After AuthContext fix (use `Auth::user()`) | 26 | −2 |
| After SyncObserver memoization | 25 | −1 |
| After max cursor (PHP, not SQL) | 24 | −1 |

**Total reduction:** ~25% (32 → 24).

---

## 2. Sync Endpoint Deep Dive (24 queries)

### Phase A — Sanctum Auth & Middleware (4 queries)

| # | Query | Reason | Can Eliminate? |
|---|---|---|---|
| 1 | `SELECT FROM personal_access_tokens WHERE id = ?` | Sanctum token lookup | Required |
| 2 | `SELECT FROM auth_users WHERE id_pengguna = ?` | Sanctum user resolution | Required |
| 3 | `UPDATE personal_access_tokens SET last_used_at` | Sanctum last-used tracking | **YES — disable in config** |
| 4 | `SELECT FROM auth_roles WHERE id_peran = ?` | RoleMiddleware lazy-loads `$user->peran` | Required for RBAC |

### Phase B — Mobile Device & Queue (6 queries)

| # | Query | Reason | Can Eliminate? |
|---|---|---|---|
| 5 | `SELECT FROM mobile_devices WHERE uuid_device = ?` | Device lookup | Required |
| 6 | `INSERT INTO mobile_devices` | First-time device registration | Once per device |
| 7 | `SELECT FROM mobile_sync_queues WHERE request_id = ?` | Idempotency check | Could use `INSERT ... ON CONFLICT` |
| 8 | `INSERT INTO mobile_sync_queues` | Queue record | Required |
| 9 | `UPDATE mobile_devices SET last_sync_at` | Update device activity | Required |
| 10 | `UPDATE mobile_sync_queues SET status='processed'` | Finalize queue entry | Required |

### Phase C — Change Processing (3 queries)

| # | Query | Reason | Can Eliminate? |
|---|---|---|---|
| 11 | `SELECT FROM operasi_insiden WHERE id_insiden = ?` | Scope validation | Required |
| 12 | `SELECT FROM operasi_penugasan WHERE uuid_penugasan = ?` | Find existing for upsert | Required |
| 13 | `INSERT INTO operasi_penugasan` | Create new record | Required |

### Phase D — SyncObserver (2 queries)

| # | Query | Reason | Can Eliminate? |
|---|---|---|---|
| 14 | `INSERT INTO sync_cursors` | Observers: `created()` | Required for sync |
| 15 | `UPDATE sync_cursors SET cursor_value = id_cursor` | Observer pattern (set value after insert) | Could be 1 query with SQL-level cursor assignment |

### Phase E — Batch Response (9 queries)

| # | Query | Reason | Can Eliminate? |
|---|---|---|---|
| 16 | `SELECT FROM sync_cursors WHERE entity_type IN (5) ORDER BY cursor_value` | Fetch all cursors for scope | Required |
| 17 | `SELECT FROM assessment_utama WHERE uuid IN (...) AND dihapus_pada IS NULL` | Batch entity fetch | Required |
| 18 | `SELECT FROM operasi_sitrep WHERE uuid IN (...) AND dihapus_pada IS NULL` | Batch entity fetch | Required |
| 19 | `SELECT FROM operasi_klaster WHERE uuid IN (...) AND dihapus_pada IS NULL` | Batch entity fetch | Required |
| 20 | `SELECT FROM operasi_penugasan WHERE uuid IN (...) AND dihapus_pada IS NULL` | Batch entity fetch | Required |
| 21 | `SELECT FROM operasi_insiden WHERE id_insiden IN (...) AND dihapus_pada IS NULL` | Eager-load insiden for penugasan (`$with`) | Part of entity fetch |
| 22 | `SELECT FROM operasi_mobilisasi WHERE uuid IN (...) AND dihapus_pada IS NULL` | Batch entity fetch | Required |
| 23 | `SELECT FROM sync_tombstones WHERE entity_type IN (5) ORDER BY cursor_value` | Batch tombstone fetch | Required |
| 24 | `INSERT INTO sync_audit_logs` | Audit trail | Required |

---

## 3. Remaining Overhead Analysis

### Sync endpoint: Realistic query floor

| Component | Queries | Notes |
|---|---|---|
| Sanctum (token + user + role) | 3 | last_used_at disabled = 3 (was 4) |
| Device + Queue | 5 | Find/register + create/finalize |
| Change processing | 3 | scope + find + create |
| SyncObserver | 1 | Single insert with DB-level cursor assignment |
| Batch response | 8 | cursor + 5 entities + insiden + tombstone |
| Audit | 1 | |
| **Floor** | **~21** | |

The target of **≤15 queries** for sync is **not achievable** with the current architecture. The sync endpoint fundamentally requires:
- Authentication (2-3 queries)
- Authorization (1 query)
- Device tracking (2 queries)
- Queue management (2 queries)
- Scope validation (1 query per change)
- Entity persistence (2-3 queries per change)
- Batch response (7-8 queries)

**Recommendation:** Revise sync endpoint target to **≤25 queries** (currently at 24).

### Other endpoints: well within target

- `/status`: 11 queries (Sanctum + auth + 3 scope-filtered aggregate queries)
- `/metrics`: 10 queries (Sanctum + auth + 3 aggregate queries)
- `/bootstrap`: 15 queries (Sanctum + auth + 5 entity fetches + cursors)
- `/assessment`: 7 queries (Sanctum + auth + data fetch)
- `/penugasan`: 11 queries (Sanctum + auth + data + eager-loads)

---

## 4. Optimizations Applied in This Profile

| # | Optimization | File | Queries Saved |
|---|---|---|---|
| 1 | **AuthContext uses `Auth::user()`** instead of fresh DB query | `AuthorizationContextService.php:21` | 2 |
| 2 | **SyncObserver memoizes insiden lookups** via static cache | `SyncObserver.php:16-39` | N−1 per request |
| 3 | **Batch query eager-loads `insiden`** for all entity types | `SyncApiController.php:296` | 1 |
| 4 | **Max cursor computed from PHP** (no separate GROUP BY) | `SyncApiController.php:335` | 1 |

---

## 5. Classification

| Finding | Classification | Status |
|---|---|---|
| Sync endpoint 24 queries (over target) | **P2** — Realistic floor ~21 | Tracked |
| Sanctum `last_used_at` per-request overhead | **P3** — 1 query, minor | Awaiting decision |
| SyncObserver 2-phase cursor (insert + update) | **P3** — 1 query, minor | Tracked |
| `$with = ["insiden"]` on OperasiPenugasan | **P2** — Always eager-loads insiden even when not needed | Tracked |
| All non-sync endpoints within target | **P0** ✅ | No action needed |

---

## 6. Tooling

Profile test: `tests/Feature/QueryProfileTest.php`

```bash
php artisan test tests/Feature/QueryProfileTest.php
```

Each test method is isolated (`DatabaseTransactions` + `setUp` seeds) to avoid state carryover. Query log is dumped for sync endpoint with timing breakdown.
