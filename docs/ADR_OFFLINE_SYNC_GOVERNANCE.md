# Architectural Decision Record (ADR): Offline Sync & Mobile Governance

This document outlines the formal governance standards for NURISK API design, database schema conventions, bulk transaction processing, audit trails, and data synchronization for the Flutter Mobile consumer layer.

---

## 1. RULE-UUID-001: UUID Consistency Standard

All transactional and operational tables in NURISK must adhere to the following strict database design rules:

1. **Internal Primary Key**: Use an auto-incrementing `BIGINT` as the internal primary key (e.g. `id_penugasan`). This key handles relations and indexing internally within the Laravel monolith.
2. **Public Identifier**: Use a `UUID` as the public unique identifier (e.g., `uuid_penugasan`).
3. **Indexing**: The UUID column must be marked as `UNIQUE` and indexed at the migration level.
4. **API Exclusivity**: The API layer (Form Requests, Controllers, and JSON Resources) must only expose the `UUID` key. Under no circumstances should the internal integer Primary Key be leaked to client endpoints.
5. **Route Binding**: Lookup and updates must target the UUID column (e.g. using `$query->where('uuid_{entity}', $uuid)`).

---

## 2. Sync Versioning & Conflict Resolution

* **Field Definition**: Operational tables must include:
  ```php
  $table->bigInteger('sync_version')->default(1);
  $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
  ```
* **Conflict Prevention**: 
  - Clients send updates specifying their last known `client_sync_version`.
  - The server verifies if `client_sync_version` equals the database's `sync_version`.
  - If a mismatch is detected (the server's version is higher), the server rejects the request with a `409 Conflict` (or `SYNC_CONFLICT` code) to prevent overwriting newer records.

---

## 3. Global Bulk Endpoints

To preserve mobile battery life and minimize connection overhead, all operational tables must support a standardized bulk route pattern:

* **Endpoint Shape**:
  ```http
  POST /api/v1/{resource}/bulk
  ```
* **Processing Rules**:
  - Accepts a batch array of resources to insert or update.
  - Transactions are processed atomically.
  - Returns processed counts and detailed error details for rejected records.

---

## 4. ADR-006: Soft Delete & Tombstone Governance

### ADR-006: Soft Delete Audit Trail
For all operational and transactional models utilizing `SoftDeletes`, audit trails must be preserved:
* **Schema Additions**:
  ```php
  $table->unsignedBigInteger('deleted_by')->nullable();
  $table->text('alasan_hapus')->nullable();
  
  $table->foreign('deleted_by')->references('id_pengguna')->on('auth_users');
  ```
* **Usage**: On deletion, the `deleted_by` user ID and `alasan_hapus` (reason) must be logged.

### ADR-006A: Tombstone Sync (Deleted Records Tracking)
To ensure offline clients are notified of records deleted while they were disconnected:
* **Table**: `sync_tombstones`
* **Schema**:
  ```php
  Schema::create('sync_tombstones', function (Blueprint $table) {
      $table->id();
      $table->string('uuid_entity')->index();
      $table->string('entity_type')->index(); // e.g. 'operasi_penugasan'
      $table->timestamp('deleted_at')->useCurrent();
      $table->unsignedBigInteger('deleted_by')->nullable();
  });
  ```
* **Behavior**: When any transactional entity is soft-deleted or hard-deleted, a hook/observer inserts its UUID and type into `sync_tombstones`. During synchronization, the client queries tombstones updated since their last sync cursor to purge deleted records from local cache.

---

## 5. ADR-006B: Device Registry

To facilitate synchronization auditing, revoke tokens, and debug offline queues:
* **Table**: `mobile_devices`
* **Schema**:
  ```php
  Schema::create('mobile_devices', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('id_pengguna')->index();
      $table->string('device_id')->unique();
      $table->string('platform'); // e.g. 'android', 'ios'
      $table->string('app_version');
      $table->timestamp('last_sync_at')->nullable();
      $table->timestamps();
      
      $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users');
  });
  ```

---

## 6. ADR-006C: Sync Cursor Strategy

To eliminate timezone discrepancy issues and timestamp precision errors between devices:
* **Concept**: Instead of relying solely on `updated_since` timestamps, NURISK will transition to a sequential `sync_cursor` (global transaction log ID or sequential auto-incrementing integer identifier).
* **Payload Interface**:
  ```json
  {
    "device_id": "android-001",
    "cursor": 15897,
    "changes": []
  }
  ```
* **Advantages**:
  - Independent of client/server clock drift or timezone misconfigurations.
  - O(1) database queries on sync states (`WHERE id > cursor`).

---

## 7. ADR-007: Mobile Sync Queue

A unified data synchronization endpoint will be established before initiating mobile domains:

* **Endpoint**:
  ```http
  POST /api/v1/sync
  ```
* **Contract Payload**:
  ```json
  {
    "device_id": "android-001",
    "cursor": 15897,
    "changes": [
      {
        "table": "operasi_penugasan",
        "action": "upsert",
        "data": { ... }
      }
    ]
  }
  ```

---

## 8. Revised Development Roadmap

To ensure robustness in low-connectivity conditions, the roadmap is updated to insert an infrastructure hardening sprint for offline sync before continuing with volunteer mobilization:

```text
  ARCH-005: API Governance & Mobile Foundation (Completed)
  └─► ARCH-006: Offline Sync Infrastructure (Next Sprint)
        ├─► Implement Soft Delete & Tombstone Governance (ADR-006 / ADR-006A)
        ├─► Implement Device Registry (ADR-006B)
        ├─► Implement Sync Cursor database adjustments (ADR-006C)
        ├─► Implement Bulk API controllers & validation layers
        └─► Setup Global Mobile Sync Queue endpoint (/api/v1/sync)
  └─► M10: Mobilisasi
  └─► M11: Relawan
  └─► M12: Pos Aju
  └─► M13: Logistik
```
