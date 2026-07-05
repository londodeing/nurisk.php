# Conflict Analysis & Dashboard Design — Phase 15.2

**Date:** 2026-06-20
**Phase:** Sprint 15.2 — Conflict Analytics & Monitoring
**Status:** DRAFT

---

## 1. Current Conflict Architecture

### 1.1 Last-Write-Wins (LWW) Mechanism

The sync system (`SyncApiController.php:189-219`) implements **Last-Write-Wins** conflict resolution:

1. On each sync request, the mobile client sends its last known `sync_version` for the record.
2. The server compares `client_version` against the current `server_version`.
3. If `client_version < server_version`, a conflict record is created in `sync_conflicts`.
4. **Regardless of the conflict**, the server applies the client's update (`last-write-wins`) and increments the version counter.

```php
// SyncApiController.php:193-216
if ($clientVersion < $serverVersion) {
    $conflict = SyncConflict::create([...]);
    // Logged for audit, but update still applied
}
// Always apply — last-write-wins
$data['sync_version'] = max($serverVersion, $clientVersion) + 1;
```

### 1.2 Captured Data Per Conflict

| Field | Type | Description |
|-------|------|-------------|
| `id` | BIGINT PK | Auto-increment |
| `device_uuid` | VARCHAR | Device that sent the conflicting update |
| `entity_type` | VARCHAR | Table name (e.g. `operasi_penugasan`) |
| `uuid_entity` | VARCHAR | UUID of the conflicting record |
| `id_pcnu` | INT, nullable | PCNU wilayah scope FK (no actual FK constraint) |
| `client_version` | INT | Version sent by client |
| `server_version` | INT | Version at server before update |
| `client_data` | JSON | Full row data as sent by the client |
| `server_data` | JSON | Full row data as stored on the server |
| `resolved_at` | TIMESTAMP, nullable | When the conflict was resolved (not currently set) |
| `scope_type` | VARCHAR | Auth scope type (e.g. `pcnu`, `pwnu`) |
| `scope_id` | INT | Auth scope ID |
| `dibuat_pada` | TIMESTAMP | Record creation timestamp |

### 1.3 What Is NOT Captured

The current schema has significant gaps for root-cause analysis:

| Missing | Impact |
|---------|--------|
| **`conflicted_by`** — user who sent the conflicting update | Cannot identify "siapa pengguna A" without JOIN to mobile_devices |
| **`server_owned_by`** — user who created the server version | Cannot identify "siapa pengguna B" (only derivable from server_data JSON via id_pengguna FK in the data) |
| **`field_diffs`** — computed field-level diff | Cannot answer "field mana yang konflik" without comparing two JSON blobs at query time |
| **`resolved_by`** — user who resolved the conflict | No audit trail for resolution accountability |
| **`resolution`** — text description of resolution action | No record of what decision was made (overwrite, merge, discard) |

---

## 2. Gap Analysis vs. Requirements

| Requirement | Status | Notes |
|-------------|--------|-------|
| **siapa pengguna A** (who sent the conflicting update) | ❌ NOT captured | Only `device_uuid` stored. User is derivable via `device_uuid → mobile_devices.id_pengguna → auth_users` but requires a JOIN. Not directly queryable. |
| **siapa pengguna B** (who owns the server version) | ❌ NOT captured | Not explicitly stored. The `server_data` JSON may contain an `id_pengguna` FK field, but it is not extracted into a dedicated column. |
| **entity** (which record conflicted) | ✅ Captured | `entity_type` + `uuid_entity` identify the exact record. |
| **field** (which specific field(s) conflicted) | ❌ NOT captured | Only full row JSON blobs (`client_data` / `server_data`) are stored. No field-level diff is computed at conflict creation time. Analysis requires comparing two JSON documents per conflict row. |
| **timestamp** (when conflict occurred) | ⚠️ Partial | `dibuat_pada` captures when the conflict was logged, but there is no separate `conflicted_at` field (the `dibuat_pada` serves this purpose adequately). |

---

## 3. Conflict Analytics Dashboard Design

### 3.1 Top Entities by Conflict Count (30 days)

```sql
SELECT
    entity_type,
    COUNT(*) AS total_conflicts
FROM sync_conflicts
WHERE dibuat_pada >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY entity_type
ORDER BY total_conflicts DESC;
```

**Dashboard widget:** Bar chart — top 10 entity types.

### 3.2 Top Wilayah by Conflict Count (30 days)

```sql
SELECT
    scope_type,
    scope_id,
    COUNT(*) AS total_conflicts
FROM sync_conflicts
WHERE dibuat_pada >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY scope_type, scope_id
ORDER BY total_conflicts DESC;
```

**Dashboard widget:** Bar chart — top 10 scope regions.

### 3.3 Top Users by Conflict Count (30 days)

Requires JOIN to `mobile_devices` and `auth_users`:

```sql
SELECT
    u.id_pengguna,
    u.no_hp,
    COUNT(*) AS total_conflicts
FROM sync_conflicts c
JOIN mobile_devices d ON c.device_uuid = d.uuid_device
JOIN auth_users u ON d.id_pengguna = u.id_pengguna
WHERE c.dibuat_pada >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY u.id_pengguna
ORDER BY total_conflicts DESC;
```

**Dashboard widget:** Leaderboard — top 20 users with conflict counts.

### 3.4 Conflict Rate Over Time (30 days)

Comparing conflicts to total sync requests:

```sql
SELECT
    DATE(c.dibuat_pada) AS conflict_date,
    COUNT(DISTINCT c.id) AS total_conflicts,
    COUNT(DISTINCT a.id) AS total_sync_requests,
    ROUND(COUNT(DISTINCT c.id) * 100.0 / NULLIF(COUNT(DISTINCT a.id), 0), 2) AS conflict_rate_pct
FROM sync_audit_logs a
LEFT JOIN sync_conflicts c ON DATE(c.dibuat_pada) = DATE(a.dibuat_pada)
WHERE a.dibuat_pada >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY conflict_date
ORDER BY conflict_date;
```

**Dashboard widget:** Line chart — daily conflict rate %.

### 3.5 Unresolved Conflicts Aging

```sql
SELECT
    id,
    entity_type,
    uuid_entity,
    device_uuid,
    TIMESTAMPDIFF(HOUR, dibuat_pada, NOW()) AS hours_unresolved
FROM sync_conflicts
WHERE resolved_at IS NULL
ORDER BY dibuat_pada ASC;
```

**Dashboard widget:** Table — oldest unresolved conflicts first.

---

## 4. Proposed Schema Extension

### 4.1 Migration: Add Conflict Analytics Columns

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sync_conflicts', function (Blueprint $table) {
            // User who sent the conflicting update (User A)
            $table->unsignedBigInteger('conflicted_by')->nullable()->after('device_uuid');
            $table->foreign('conflicted_by')
                  ->references('id_pengguna')
                  ->on('auth_users')
                  ->nullOnDelete();

            // User who created the server version (User B) — extracted from server_data JSON
            $table->unsignedBigInteger('server_owned_by')->nullable()->after('conflicted_by');
            $table->foreign('server_owned_by')
                  ->references('id_pengguna')
                  ->on('auth_users')
                  ->nullOnDelete();

            // Computed field-level diff between client_data and server_data
            $table->json('field_diffs')->nullable()->after('server_data');

            // User who resolved the conflict
            $table->unsignedBigInteger('resolved_by')->nullable()->after('resolved_at');
            $table->foreign('resolved_by')
                  ->references('id_pengguna')
                  ->on('auth_users')
                  ->nullOnDelete();

            // Resolution description
            $table->text('resolution')->nullable()->after('resolved_by');

            // Performance index for dashboard queries
            $table->index(['scope_type', 'scope_id', 'dibuat_pada'], 'idx_conflicts_scope_date');
        });
    }

    public function down(): void
    {
        Schema::table('sync_conflicts', function (Blueprint $table) {
            $table->dropForeign(['conflicted_by']);
            $table->dropForeign(['server_owned_by']);
            $table->dropForeign(['resolved_by']);
            $table->dropIndex('idx_conflicts_scope_date');
            $table->dropColumn([
                'conflicted_by',
                'server_owned_by',
                'field_diffs',
                'resolved_by',
                'resolution',
            ]);
        });
    }
};
```

### 4.2 Updated SyncConflict Model Fillable & Casts

```php
protected $fillable = [
    'device_uuid',
    'conflicted_by',
    'server_owned_by',
    'entity_type',
    'uuid_entity',
    'id_pcnu',
    'client_version',
    'server_version',
    'client_data',
    'server_data',
    'field_diffs',
    'resolved_at',
    'resolved_by',
    'resolution',
    'scope_type',
    'scope_id',
];

protected $casts = [
    'client_version' => 'integer',
    'server_version' => 'integer',
    'id_pcnu' => 'integer',
    'scope_id' => 'integer',
    'client_data' => 'array',
    'server_data' => 'array',
    'field_diffs' => 'array',
    'resolved_at' => 'datetime',
    'resolved_by' => 'integer',
    'conflicted_by' => 'integer',
    'server_owned_by' => 'integer',
    'dibuat_pada' => 'datetime',
];
```

### 4.3 Conflict Creation — Updated Logic

In `SyncApiController.php`, enhance the conflict creation block to populate the new fields:

```php
// Resolve user from device
$deviceUser = MobileDevice::where('uuid_device', $deviceUuid)->value('id_pengguna');

// Compute field-level diff
$fieldDiffs = [];
foreach ($data as $field => $clientVal) {
    $serverVal = $record->{$field} ?? null;
    if ($field === 'sync_version') continue;
    if ($clientVal !== $serverVal) {
        $fieldDiffs[$field] = [
            'client' => $clientVal,
            'server' => $serverVal,
        ];
    }
}

// Extract server owner from server record data
$serverOwnedBy = $record->id_pengguna ?? $record->updated_by ?? null;

$conflict = SyncConflict::create([
    'device_uuid' => $deviceUuid,
    'conflicted_by' => $deviceUser,
    'server_owned_by' => $serverOwnedBy,
    'entity_type' => $table,
    'uuid_entity' => $uuidVal,
    'id_pcnu' => $insiden->id_pcnu ?? null,
    'scope_type' => $this->authCtx->getScopeType(),
    'scope_id' => $this->authCtx->getScopeId(),
    'client_version' => $clientVersion,
    'server_version' => $serverVersion,
    'client_data' => $data,
    'server_data' => $record->toArray(),
    'field_diffs' => $fieldDiffs,
]);
```

### 4.4 New Index Strategy

| Index Name | Columns | Purpose |
|------------|---------|---------|
| `idx_conflicts_scope_date` | `scope_type, scope_id, dibuat_pada` | Dashboard queries filtered by scope + date range |
| Existing: `sync_conflicts_entity_type_index` | `entity_type` | Top-entity queries |
| Existing: `sync_conflicts_uuid_entity_index` | `uuid_entity` | Per-record conflict lookup |

---

## 5. Monitoring Recommendations

### 5.1 Alert Rules

| Alert | Condition | Severity | Action |
|-------|-----------|----------|--------|
| **High conflict rate** | Conflict count > 5% of total sync requests in any 15-min window | P1 — Critical | Notify ops team; investigate network/sync logic issues |
| **Entity hotspot** | Any single `entity_type` + `uuid_entity` has > 10 conflicts in 24h | P2 — High | Notify product owner; review offline merge logic for that entity |
| **Stale unresolved conflict** | Conflict with `resolved_at IS NULL` and `dibuat_pada < NOW() - INTERVAL 48 HOUR` | P2 — High | Notify data steward for manual resolution |

### 5.2 Monitoring Query: Conflict Rate Check

```sql
WITH sync_counts AS (
    SELECT
        DATE_FORMAT(dibuat_pada, '%Y-%m-%d %H:%i:00') AS minute_bucket,
        COUNT(*) AS total_sync
    FROM sync_audit_logs
    WHERE dibuat_pada >= NOW() - INTERVAL 15 MINUTE
    GROUP BY minute_bucket
), conflict_counts AS (
    SELECT
        DATE_FORMAT(dibuat_pada, '%Y-%m-%d %H:%i:00') AS minute_bucket,
        COUNT(*) AS total_conflicts
    FROM sync_conflicts
    WHERE dibuat_pada >= NOW() - INTERVAL 15 MINUTE
    GROUP BY minute_bucket
)
SELECT
    COALESCE(s.minute_bucket, c.minute_bucket) AS bucket,
    COALESCE(s.total_sync, 0) AS sync_count,
    COALESCE(c.total_conflicts, 0) AS conflict_count,
    CASE
        WHEN COALESCE(s.total_sync, 0) = 0 THEN 0
        ELSE ROUND(COALESCE(c.total_conflicts, 0) * 100.0 / s.total_sync, 2)
    END AS conflict_rate_pct
FROM sync_counts s
LEFT JOIN conflict_counts c ON s.minute_bucket = c.minute_bucket
HAVING conflict_rate_pct > 5;
```

### 5.3 Alert: Entity Hotspot Detection

```sql
SELECT
    entity_type,
    uuid_entity,
    COUNT(*) AS conflict_count
FROM sync_conflicts
WHERE dibuat_pada >= NOW() - INTERVAL 24 HOUR
GROUP BY entity_type, uuid_entity
HAVING conflict_count > 10
ORDER BY conflict_count DESC;
```

### 5.4 Alert: Stale Unresolved Conflicts

```sql
SELECT
    id,
    entity_type,
    uuid_entity,
    device_uuid,
    TIMESTAMPDIFF(HOUR, dibuat_pada, NOW()) AS hours_unresolved
FROM sync_conflicts
WHERE resolved_at IS NULL
  AND dibuat_pada < NOW() - INTERVAL 48 HOUR
ORDER BY dibuat_pada ASC;
```

---

## 6. Example SQL Queries for Operations Team

### 6.1 Today's Conflict Summary

```sql
SELECT
    DATE_FORMAT(dibuat_pada, '%Y-%m-%d %H:00:00') AS hour_bucket,
    entity_type,
    COUNT(*) AS conflicts
FROM sync_conflicts
WHERE DATE(dibuat_pada) = CURDATE()
GROUP BY hour_bucket, entity_type
ORDER BY hour_bucket, conflicts DESC;
```

### 6.2 Unresolved Conflicts Older Than 24 Hours

```sql
SELECT
    id,
    entity_type,
    uuid_entity,
    device_uuid,
    dibuat_pada,
    TIMESTAMPDIFF(HOUR, dibuat_pada, NOW()) AS age_hours,
    JSON_LENGTH(server_data) AS server_fields,
    JSON_LENGTH(client_data) AS client_fields
FROM sync_conflicts
WHERE resolved_at IS NULL
  AND dibuat_pada < NOW() - INTERVAL 24 HOUR
ORDER BY dibuat_pada ASC
LIMIT 50;
```

### 6.3 Users with Most Conflicts (Last 7 Days)

```sql
SELECT
    u.id_pengguna,
    u.no_hp,
    u.nama_lengkap,
    COUNT(*) AS total_conflicts
FROM sync_conflicts c
JOIN mobile_devices d ON c.device_uuid = d.uuid_device
JOIN auth_users u ON d.id_pengguna = u.id_pengguna
WHERE c.dibuat_pada >= NOW() - INTERVAL 7 DAY
GROUP BY u.id_pengguna
ORDER BY total_conflicts DESC
LIMIT 20;
```

### 6.4 Entities with Most Conflicts, Grouped by Entity Type

```sql
SELECT
    entity_type,
    uuid_entity,
    COUNT(*) AS total_conflicts,
    MIN(dibuat_pada) AS first_conflict,
    MAX(dibuat_pada) AS last_conflict
FROM sync_conflicts
WHERE dibuat_pada >= NOW() - INTERVAL 30 DAY
GROUP BY entity_type, uuid_entity
HAVING total_conflicts > 1
ORDER BY total_conflicts DESC
LIMIT 50;
```

### 6.5 Conflicting Fields Breakdown (After Schema Extension)

After the `field_diffs` column is added, this query shows which fields conflict most often:

```sql
SELECT
    entity_type,
    JSON_UNQUOTE(JSON_EXTRACT(field_diffs, '$."field_name"')) AS conflicted_field,
    COUNT(*) AS occurrence_count
FROM sync_conflicts
WHERE field_diffs IS NOT NULL
  AND dibuat_pada >= NOW() - INTERVAL 30 DAY
-- Note: MySQL JSON_TABLE or application-level aggregation needed for per-field breakdown.
-- This is a simplified example; implement in application code for production use.
GROUP BY entity_type, conflicted_field
ORDER BY occurrence_count DESC;
```

### 6.6 Conflict Resolution Rate (Weekly)

```sql
SELECT
    YEARWEEK(dibuat_pada, 1) AS week_key,
    COUNT(*) AS total_conflicts,
    SUM(CASE WHEN resolved_at IS NOT NULL THEN 1 ELSE 0 END) AS resolved_conflicts,
    ROUND(
        SUM(CASE WHEN resolved_at IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*),
        2
    ) AS resolution_rate_pct
FROM sync_conflicts
WHERE dibuat_pada >= NOW() - INTERVAL 90 DAY
GROUP BY week_key
ORDER BY week_key;
```

### 6.7 Device-Level Conflict Report

```sql
SELECT
    d.uuid_device,
    d.platform,
    d.app_version,
    COUNT(*) AS total_conflicts,
    COUNT(DISTINCT c.entity_type) AS distinct_entities
FROM sync_conflicts c
JOIN mobile_devices d ON c.device_uuid = d.uuid_device
WHERE c.dibuat_pada >= NOW() - INTERVAL 30 DAY
GROUP BY d.uuid_device
ORDER BY total_conflicts DESC
LIMIT 20;
```

---

## Appendix A: Entity Relationship Diagram (Current)

```
sync_conflicts
├── device_uuid ──┐
│                 │
mobile_devices    │
├── uuid_device   │
├── id_pengguna ──┤
│                 │
auth_users        │
├── id_pengguna ◄─┘
├── no_hp
└── nama_lengkap
```

## Appendix B: Entity Relationship Diagram (Proposed)

```
sync_conflicts
├── id (PK)
├── device_uuid ──────────────┐
├── conflicted_by ────────────┤
├── server_owned_by ──────────┤
├── entity_type               │
├── uuid_entity               │
├── id_pcnu                   │
├── client_version            │
├── server_version            │
├── client_data (JSON)        │
├── server_data (JSON)        │
├── field_diffs (JSON) ───────┤
├── resolved_at               │
├── resolved_by ──────────────┤
├── resolution (TEXT) ────────┤
├── scope_type                │
├── scope_id                  │
├── dibuat_pada               │
│                             │
mobile_devices                │
├── uuid_device ◄─────────────┘
├── id_pengguna ──┐
│                 │
auth_users        │
├── id_pengguna ◄─┼── conflicted_by
├── id_pengguna ◄─┼── server_owned_by
├── id_pengguna ◄─┼── resolved_by
├── no_hp         │
└── nama_lengkap  │
```
