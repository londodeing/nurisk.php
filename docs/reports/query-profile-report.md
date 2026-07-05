# Database Query Profiling Report

**Task:** 13.2 — Database Query Profiling  
**Date:** 2026-06-20  
**Target:** < 20 queries per page  

---

## Executive Summary

A database query profiling session was conducted across five (5) key authenticated API endpoints of the Nurisk application. The application runs on SQLite (`database/loadtest.sqlite`) with Laravel Sanctum authentication.

**Key Finding:** The `POST /api/v1/sync` endpoint significantly exceeds the target of 20 queries per page, averaging **32 queries per request**. All other endpoints are within or close to the acceptable range (8–10 queries/request). The primary contributors to query volume are authentication layer overhead (Sanctum token validation + authorization context re-fetching) and unoptimized N+1 patterns in the sync cursor processing loop.

---

## Methodology

1. A dedicated Artisan command `query:profile` was created at `app/Console/Commands/QueryProfileCommand.php`.
2. The command creates an authenticated session using an existing `super_admin` user and a Sanctum personal access token.
3. Each endpoint is hit `N` times (N=5 for this report) via the HTTP kernel (simulating real middleware pipeline).
4. `DB::listen()` captures every query with its SQL, bindings, and execution time.
5. Results are aggregated per endpoint: total queries, duplicates, slow queries, and N+1 candidates.

### Endpoints Profiled

| # | Endpoint | Method | Description |
|---|----------|--------|-------------|
| 1 | `/api/v1/sync/status` | GET | Sync status with cursors and tombstone counts |
| 2 | `/api/v1/sync/metrics` | GET | Sync metrics (audit logs, conflicts, devices) |
| 3 | `/api/v1/assessment` | GET | List assessments for an incident (with dampak & kebutuhan eager-loading) |
| 4 | `/api/v1/penugasan` | GET | List penugasan for an incident (with pengguna & klaster eager-loading) |
| 5 | `/api/v1/sync` | POST | Full sync operation (empty changeset, no actual mutations) |

---

## Results

### Summary Table

| Endpoint | Total Queries | Avg/Request | Avg (ms) | Total (ms) | Duplicates | Slow > 50ms | N+1 Count |
|---|---|---|---|---|---|---|---|
| GET /api/v1/sync/status | 48 | 9.6 | 0.13 | 6.32 | 2 | 0 | 1 |
| GET /api/v1/sync/metrics | 40 | 8.0 | 0.04 | 1.76 | 0 | 0 | 0 |
| GET /api/v1/assessment | 45 | 9.0 | 0.06 | 2.55 | 0 | 0 | 0 |
| GET /api/v1/penugasan | 50 | 10.0 | 0.06 | 3.03 | 2 | 0 | 0 |
| POST /api/v1/sync | 160 | 32.0 | 0.61 | 97.64 | 5 | 0 | 5 |

### Target Check: < 20 queries/page

| Endpoint | Avg Queries | Target | Status |
|---|---|---|---|
| GET /api/v1/sync/status | 9.6 | < 20 | ✅ PASS |
| GET /api/v1/sync/metrics | 8.0 | < 20 | ✅ PASS |
| GET /api/v1/assessment | 9.0 | < 20 | ✅ PASS |
| GET /api/v1/penugasan | 10.0 | < 20 | ✅ PASS |
| POST /api/v1/sync | 32.0 | < 20 | ❌ FAIL |

---

## Top Duplicated Queries

### GET /api/v1/sync/status (25 duplicates across 5 requests)

| Count | SQL Pattern | Total Time |
|---|---|---|
| x25 | `select max("cursor_value") as aggregate from "sync_cursors" where "entity_type" = ?` | 1.03ms |
| x6 | `select * from "auth_users" where "auth_users"."id_pengguna" = ? limit 1` | 0.50ms |

### GET /api/v1/penugasan (20 duplicates across 5 requests)

| Count | SQL Pattern | Total Time |
|---|---|---|
| x10 | `select * from "auth_users" where "auth_users"."id_pengguna" = ? limit 1` | 0.50ms |
| x10 | `select * from "auth_roles" where "auth_roles"."id_peran" in (1)` | 0.36ms |

### POST /api/v1/sync (125 duplicates across 5 requests)

| Count | SQL Pattern | Total Time |
|---|---|---|
| x25 | `select * from "auth_users" where "auth_users"."id_pengguna" = ? limit 1` | 1.51ms |
| x25 | `select * from "auth_roles" where "auth_roles"."id_peran" in (1)` | 1.03ms |
| x25 | `select * from "sync_cursors" where "entity_type" = ? and "cursor_value" > ? order by "cursor_value" asc` | 1.44ms |
| x25 | `select * from "sync_tombstones" where "entity_type" = ? and "cursor_value" > ? order by "cursor_value" asc` | 1.34ms |
| x25 | `select max("cursor_value") as aggregate from "sync_cursors" where "entity_type" = ?` | 1.29ms |

---

## Top Slow Queries

No queries exceeded the 50ms threshold during this profiling session. The SQLite database is local and lightweight, so query times are expected to be low. Under production load (MySQL/PostgreSQL with network latency and concurrent connections), slow queries may appear.

---

## N+1 Candidates

### POST /api/v1/sync

The sync endpoint processes five entity types (`assessment`, `sitrep`, `klaster`, `penugasan`, `mobilisasi`). For each entity type, it executes:

- One `SyncCursor` query
- One `SyncTombstone` query
- One `max(cursor_value)` aggregate
- Per cursor row: one `SELECT` to fetch the entity

This creates a predictable N+1 pattern: for each cursor record, a separate query fetches the entity. This is acceptable for small datasets but will degrade with many cursor entries.

---

## Recommendations

### 1. Reduce Auth Overhead (HIGH PRIORITY)

The `AuthorizationContextService` re-fetches the authenticated user **on every call** inside each controller action. This results in duplicate `auth_users` and `auth_roles` queries per request.

**Fix:** Cache the user in the service instance for the duration of a single request (already partially attempted with `cachedUser`, but the `if(true)` guard always refreshes). Remove the forced refresh or implement proper request-scoped caching:

```php
// In AuthorizationContextService:
public function getCurrentUser(): ?AuthUser
{
    if (!Auth::check()) return null;
    if ($this->cachedUser === null) {
        $this->cachedUser = AuthUser::query()
            ->with('peran')
            ->find(Auth::id());
    }
    return $this->cachedUser;
}
```

**Impact:** Saves 2–4 queries per request across all endpoints.

### 2. Eager-Load Relationships in Sync Cursor Loop (MEDIUM PRIORITY)

In `SyncApiController::sync()`, the cursor loop fetches entities one-by-one:

```php
foreach ($cursorsNewer as $cur) {
    $entity = $modelClass::where($uuidColumn, $cur->uuid_entity)->first();
}
```

**Fix:** Batch-fetch all entity UUIDs from the cursor set in a single `WHERE IN(...)` query.

**Impact:** Reduces per-entity queries from `O(n)` to `O(1)` per entity type.

### 3. Consolidate Sync Status Queries (LOW PRIORITY)

The `status()` method runs separate `max()`, `count()`, and `cursor` queries for each of 5 entity types. These can be combined:

```php
$cursors = SyncCursor::selectRaw('entity_type, MAX(cursor_value) as max_cursor')
    ->when($scopeFilter, fn($q) => $q->where(...))
    ->groupBy('entity_type')
    ->get();
```

**Impact:** Reduces 5 separate queries to 1 grouped query.

### 4. POST /api/v1/sync — Investigate Per-Middleware Query Cost (MEDIUM PRIORITY)

The sync endpoint runs additional middleware queries (CorrelationId, RefreshAuthorizationContext, CheckAccountStatus). Consider an audit of middleware query counts.

### 5. Paginate Lazy-Loaded Resource Collections (LOW PRIORITY)

`AssessmentApiController::index()` and `PenugasanApiController::index()` use `paginate(15)`, which adds a `COUNT(*)` query. For endpoints with large datasets, consider cursor-based pagination for better performance.

---

## Raw Command Output

```
Database Query Profiler
Iterations per endpoint: 5

Profiling GET /api/v1/sync/status...
  -> 48 total queries across 5 requests
Profiling GET /api/v1/sync/metrics...
  -> 40 total queries across 5 requests
Profiling GET /api/v1/assessment...
  -> 45 total queries across 5 requests
Profiling GET /api/v1/penugasan...
  -> 50 total queries across 5 requests
Profiling POST /api/v1/sync...
  -> 160 total queries across 5 requests

=== QUERY PROFILE SUMMARY ===

+--------------------------+-------+---------+---------+-----------+------+-----------+-----+
| Endpoint                 | Total | Avg/Req | Avg(ms) | Total(ms) | Dups | Slow>50ms | N+1 |
+--------------------------+-------+---------+---------+-----------+------+-----------+-----+
| GET /api/v1/sync/status  | 48    | 9.6     | 0.13    | 6.32      | 2    | 0         | 1   |
| GET /api/v1/sync/metrics | 40    | 8       | 0.04    | 1.76      | 0    | 0         | 0   |
| GET /api/v1/assessment   | 45    | 9       | 0.06    | 2.55      | 0    | 0         | 0   |
| GET /api/v1/penugasan    | 50    | 10      | 0.06    | 3.03      | 2    | 0         | 0   |
| POST /api/v1/sync        | 160   | 32      | 0.61    | 97.64     | 5    | 0         | 5   |
+--------------------------+-------+---------+---------+-----------+------+-----------+-----+

--- TOP DUPLICATED QUERIES ---

GET /api/v1/sync/status:
  x25 - select max("cursor_value") as aggregate from "sync_cursors" where "entity_type" = ? (1.03ms)
  x6 - select * from "auth_users" where "auth_users"."id_pengguna" = ? limit 1 (0.50ms)

GET /api/v1/penugasan:
  x10 - select * from "auth_users" where "auth_users"."id_pengguna" = ? limit 1 (0.50ms)
  x10 - select * from "auth_roles" where "auth_roles"."id_peran" in (1) (0.36ms)

POST /api/v1/sync:
  x25 - select * from "auth_users" where "auth_users"."id_pengguna" = ? limit 1 (1.51ms)
  x25 - select * from "auth_roles" where "auth_roles"."id_peran" in (1) (1.03ms)
  x25 - select * from "sync_cursors" where "entity_type" = ? and "cursor_value" > ? order by "cursor_value" asc (1.44ms)
  x25 - select * from "sync_tombstones" where "entity_type" = ? and "cursor_value" > ? order by "cursor_value" asc (1.34ms)
  x25 - select max("cursor_value") as aggregate from "sync_cursors" where "entity_type" = ? (1.29ms)

```
