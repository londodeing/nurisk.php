# ARCH_006B_RISK_ANALYSIS

## 1. Executive Summary
This document outlines the risks associated with upgrading the NURISK offline sync architecture from the baseline ADR-006A to the hardened ARCH-006B standards.

## 2. Identified Risks & Mitigation Strategies

### 2.1 Database Contention & Deadlocks
- **Risk Level:** High
- **Description:** The new requirement for idempotent syncs involves processing transactions through a `mobile_sync_queues` table, verifying uniqueness via `request_id`, and then applying updates synchronously. If multiple devices send updates simultaneously, database locks on shared tables (like `sync_cursors` or entities) may cause deadlocks.
- **Mitigation:** Wrap the entire idempotency check and the application of changes in a single robust database transaction with appropriate lock hints. Ensure that `mobile_sync_queues.request_id` has a unique constraint to automatically bounce duplicate parallel requests.

### 2.2 Cursor Management Overhead
- **Risk Level:** Medium
- **Description:** Moving from a single global cursor to an entity-specific map of cursors (`{ "assessment": 123, "sitrep": 456 }`) significantly increases the complexity of querying for new updates. The server must iterate and filter `sync_cursors` by entity type.
- **Mitigation:** Ensure the `sync_cursors` table has compound indexes on `(entity_type, cursor_value)` to speed up chunked fetching. Limit the max batch size of returned changes per request.

### 2.3 Idempotency Edge Cases
- **Risk Level:** High
- **Description:** When the connection drops after the server successfully processes a sync but before the mobile client receives the HTTP response, the mobile client will retry. If idempotency relies strictly on `request_id`, the server will correctly prevent double-insertion, but it *must* return the exact payload generated during the first successful run.
- **Mitigation:** The `mobile_sync_queues` table stores the generated `response JSON`. When a duplicate `request_id` is detected, the API must immediately fetch and return this JSON payload instead of merely rejecting the request.

### 2.4 Tombstone Leaks
- **Risk Level:** Medium
- **Description:** Deletions that occur outside of the `SyncApiController` (e.g., hard deletes from the Web Admin Panel) might fail to generate a `sync_tombstone` if observers are not strictly enforced. Offline mobile clients will thus retain phantom data.
- **Mitigation:** Implement a strict Eloquent Observer or even a MySQL Trigger to guarantee that *any* hard delete or soft delete automatically inserts into `sync_tombstones` along with a newly generated cursor.

### 2.5 Manual Merge Conflict UX
- **Risk Level:** Low
- **Description:** Implementing `manual_merge` for conflicts solves backend overwrites but shifts the burden to the Flutter application to handle and display conflict resolution UIs.
- **Mitigation:** Ensure the API clearly returns `409 Conflict` along with both `server_data` and `client_data` via the newly requested `sync_conflicts` payload shape, allowing the Flutter developers to easily render a diff view.

## 3. Deployment Risks
Updating the schema of `mobile_devices` and migrating existing cursor logic requires downtime.
- **Action:** Plan for a maintenance window. Existing mobile clients must be forced to upgrade to the new app version that supports the entity cursor map format.
