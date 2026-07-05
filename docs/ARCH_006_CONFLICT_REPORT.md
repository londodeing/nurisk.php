# ARCH-006: Conflict Resolution Report

This report presents the implementation and rules of the `sync_version` conflict resolution mechanism.

---

## 1. Conflict Detection Flow

1. **Version Tracking**: Every transactional model records a `sync_version` field. When a record is initially created, its version is initialized to `1`.
2. **Auto-Increment**: On any update triggered via REST endpoints, the model's `updating` boot lifecycle hook automatically increments `sync_version` by 1.
3. **Conflict Condition**: When the mobile client synchronizes updates via `POST /api/v1/sync`, it sends the last known `sync_version` of its local record.
4. **Validation Logic**:
   - If `client_sync_version < server_sync_version`, the server rejects the request with a `409 Conflict` HTTP status code.
   - If versions match, the server applies the change, and increments the database version to `server_sync_version + 1`.

---

## 2. API Response Contract (409 Conflict)

If a conflict is detected, the API returns the following JSON payload:

```json
{
  "success": false,
  "message": "Conflict detected",
  "data": {
    "server_version": 8,
    "client_version": 5,
    "uuid": "a3f5a7d9-8b2c-4e8f-9a1b-0c2d3e4f5a6b",
    "entity_type": "operasi_penugasan"
  }
}
```
This payload lets the mobile client identify exactly which record caused the conflict and prompt the user to resolve it (overwrite, discard, or merge).
