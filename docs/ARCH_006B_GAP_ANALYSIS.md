# ARCH_006B_GAP_ANALYSIS

## 1. Executive Summary
This document provides a gap analysis of the current NURISK Offline Sync implementation (ARCH-006A) against the new requirements specified in the ARCH-006B Offline Sync Hardening Execution Prompt. 

## 2. Component Analysis

### 2.1 Device Authentication
- **Current State:** The `mobile_devices` table exists with `uuid_device`, `platform`, `app_version`, etc. It lacks specific token management columns.
- **Required State:** Needs `device_token` (long-lived), `token_expires_at`, and `trust_score`. A new endpoint `POST /api/v1/device/refresh-token` must be created.
- **Gap:** Missing 3 columns in `mobile_devices` and 1 authentication endpoint.

### 2.2 Sync Processing Strategy
- **Current State:** `SyncApiController` processes sync requests directly and synchronously in the main thread.
- **Required State:** Hybrid Strategy. Requests must be saved to `mobile_sync_queues`, validated for idempotency, processed synchronously, and marked as processed/failed.
- **Gap:** Missing `mobile_sync_queues` table, missing idempotency validation logic, missing request lifecycle tracking.

### 2.3 Conflict Resolution Strategy
- **Current State:** `SyncApiController` rejects updates with a 409 Conflict if the client version is lower than the server version, but does not track the conflict details.
- **Required State:** Strategy is `manual_merge`. Conflicts must be saved to a new `sync_conflicts` table for traceability.
- **Gap:** Missing `sync_conflicts` table and logic to record conflict payloads before returning the 409 response.

### 2.4 Entity Cursor Support
- **Current State:** The API payload strictly accepts a single global `cursor` integer (`"cursor": 123`).
- **Required State:** The API must accept a map of cursors per entity (`"cursors": { "assessment": 123, "sitrep": 456 }`) and return `"server_cursors": { ... }`.
- **Gap:** Payload contract mismatch. `SyncApiController` must be refactored to handle array mapping of cursors.

### 2.5 API Contract Idempotency
- **Current State:** No `request_id` is passed or checked.
- **Required State:** `request_id` must be passed. If it's a duplicate, the system must return the previously cached response from `mobile_sync_queues`.
- **Gap:** Missing idempotency guard block in `SyncApiController`.

### 2.6 Observability
- **Current State:** No observability endpoints exist.
- **Required State:** Need `GET /api/v1/sync/status` (for clients) and `GET /api/v1/sync/metrics` (for admins). Requires a new `sync_audit_logs` table.
- **Gap:** Missing 2 endpoints and 1 audit table.

### 2.7 Tombstone Tracking
- **Current State:** `SyncTombstone` model and migration exist. However, explicit logging into this table on delete is missing or relying entirely on observers not present in the current review scope.
- **Required State:** Reliable insert into `sync_tombstones` upon any hard/soft delete.
- **Gap:** Ensure observer/service logic correctly populates `sync_tombstones` and binds to the monotonically increasing `sync_cursor`.

## 3. Database Schema Gaps
The following tables/columns must be created via a new migration:
1. **`mobile_sync_queues`**: id, request_id, device_uuid, payload, response, status, processed_at.
2. **`sync_audit_logs`**: id, device_uuid, request_id, entities_synced, duration_ms, status.
3. **`sync_conflicts`**: id, device_uuid, entity_type, uuid_entity, client_version, server_version, client_data, server_data, resolved_at.
4. **`mobile_devices` (Alter)**: Add `trust_score`, `device_token`, `token_expires_at`.

## 4. Conclusion
The current implementation forms a decent baseline (ADR-006A) but is highly vulnerable to duplicate requests, lacks conflict traceability, and uses an inefficient global cursor strategy. A major refactor of `SyncApiController` and the addition of 3 new tables + 1 alter table are required to achieve ARCH-006B.
