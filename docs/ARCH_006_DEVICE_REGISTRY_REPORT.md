# ARCH-006: Device Registry Report

This report documents the design and schema of the `mobile_devices` table and its status lifecycle.

---

## 1. Schema Specifications

Table: `mobile_devices`

| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id_device` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Internal primary key. |
| `uuid_device` | VARCHAR(255) | UNIQUE, INDEX | Public UUID for the device. |
| `id_pengguna` | BIGINT | FOREIGN KEY, INDEX | User identifier referencing `auth_users.id_pengguna`. |
| `platform` | VARCHAR(255) | NOT NULL | OS platform (e.g. `android`, `ios`). |
| `app_version` | VARCHAR(255) | NOT NULL | Client app version string. |
| `last_sync_at` | TIMESTAMP | NULLABLE | Timestamp of the last successful sync transaction. |
| `push_token` | VARCHAR(255) | NULLABLE | Push notification token. |
| `status` | VARCHAR(255) | DEFAULT 'active' | Device lifecycle state: `active`, `revoked`, `inactive`. |
| `dibuat_pada` | TIMESTAMP | NOT NULL | Standard NURISK creation timestamp. |
| `diperbarui_pada`| TIMESTAMP | NULLABLE | Standard NURISK update timestamp. |

---

## 2. Business Rules & Lifecycle

1. **Multi-device Capability**: One user can register multiple devices (e.g., phone and tablet).
2. **Device Revocation**: When a device's session or token is revoked, its status changes to `revoked`. No sync operations are allowed from a revoked device.
3. **Deactivation**: A device status can be updated to `inactive` administratively.
4. **Foreign Key Integrity**: Cascades deletions on `auth_users`.
