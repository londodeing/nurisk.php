# ARCH-006: Tombstone Sync Report

This report presents the implementation and rules of the `sync_tombstones` tracking system for deleted records.

---

## 1. Schema Specifications

Table: `sync_tombstones`

| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id_tombstone` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Internal PK. |
| `entity_type` | VARCHAR(255) | NOT NULL, INDEX | Type of entity (e.g. `assessment`, `sitrep`, `penugasan`, `klaster`). |
| `uuid_entity` | VARCHAR(255) | NOT NULL, INDEX | Public UUID of the deleted record. |
| `deleted_at` | TIMESTAMP | NOT NULL | Date and time the deletion was processed. |
| `deleted_by` | BIGINT | FOREIGN KEY, NULLABLE | Reference to user who deleted the record (`auth_users.id_pengguna`). |
| `alasan_hapus` | TEXT | NULLABLE | Reason for deletion. |
| `cursor_value` | BIGINT | NOT NULL, INDEX | Monotonically increasing sync cursor representing deletion. |

---

## 2. Tombstone Rules & Observer Integration

1. **Delete Event Hooking**: In `SyncObserver::deleted(Model $model)`, whenever an observed transactional entity is deleted (soft-delete or force-delete), it is registered inside `sync_tombstones`.
2. **Unified Sequence**: The delete event writes to `sync_cursors` first to generate the latest monotonic cursor, then stores the same cursor value inside `sync_tombstones.cursor_value`.
3. **Purge Propagation**: When clients synchronize with `POST /api/v1/sync` and send their last sync cursor, any tombstone entries newer than that cursor are returned, letting the client purge corresponding records from their local database.
