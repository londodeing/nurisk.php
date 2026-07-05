# ARCH-006: Sync Cursor Infrastructure Report

This report presents the design and implementation of the monotonic, sequential, timezone-independent `sync_cursors` infrastructure.

---

## 1. Schema Specifications

Table: `sync_cursors`

| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id_cursor` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique, sequential log index. |
| `entity_type` | VARCHAR(255) | NOT NULL, INDEX | Type of the entity (e.g., `assessment`, `sitrep`, `penugasan`, `klaster`). |
| `uuid_entity` | VARCHAR(255) | NOT NULL, INDEX | Public UUID of the entity. |
| `cursor_value` | BIGINT | NOT NULL, INDEX | Sequential cursor value matching `id_cursor`. |
| `action` | VARCHAR(255) | NOT NULL | Type of change: `create`, `update`, `delete`. |
| `dibuat_pada` | TIMESTAMP | NOT NULL | Creation timestamp. |
| `diperbarui_pada`| TIMESTAMP | NULLABLE | Update timestamp. |

---

## 2. Observer Behavior & Event Flow

1. **Automation**: Using `SyncObserver`, every addition or modification to monitored operational tables automatically writes a log record in `sync_cursors`.
2. **Sequential Generation**: The `cursor_value` increments sequentially using the auto-incrementing key `id_cursor`.
3. **Monotonic Progression**: Cursors are generated synchronously inside database transactions, guaranteeing that every commit receives a monotonic incremented number. This avoids precision, network latency, or server-to-client clock drift issues.
