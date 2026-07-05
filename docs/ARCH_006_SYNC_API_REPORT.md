# ARCH-006: Unified Sync API Report

This report documents the design and interface contract of the unified synchronization API endpoint.

---

## 1. API Endpoint Contract

* **Endpoint**: `POST /api/v1/sync`
* **Content-Type**: `application/json`

### Request Payload Example
```json
{
  "device_uuid": "android-device-123",
  "cursor": 15897,
  "changes": [
    {
      "table": "operasi_penugasan",
      "action": "upsert",
      "data": {
        "uuid_penugasan": "a3f5a7d9-8b2c-4e8f-9a1b-0c2d3e4f5a6b",
        "id_insiden": 1,
        "id_pengguna": 2,
        "peran_otoritas": "trc",
        "status_penugasan": "aktif",
        "waktu_mulai": "2026-06-17 12:00:00",
        "sync_version": 1
      }
    }
  ]
}
```

### Response Payload Example (Success)
```json
{
  "success": true,
  "message": "Sync completed",
  "data": {
    "server_cursor": 15901,
    "changes": [
      {
        "table": "klaster",
        "cursor": 15899,
        "data": {
          "uuid_klaster_operasi": "e2a4c6e8-0b1d-3f5a-7c9e-1a2b3c4d5e6f",
          "id_insiden": 1,
          "status_klaster": "aktif",
          "sync_version": 2
        }
      }
    ],
    "tombstones": [
      {
        "entity_type": "penugasan",
        "uuid_entity": "d5e7f9a1-2b3c-4d5e-6f7a-8b9c0d1e2f3a",
        "deleted_at": "2026-06-17 13:00:00",
        "cursor": 15900
      }
    ],
    "conflicts": []
  }
}
```

---

## 2. API Internal Properties

1. **Transaction Safety**: All changes sent by the client are wrapped in a single database transaction. If any database constraint or conflict occurs, the entire block is rolled back.
2. **Device Authentication Validation**: Checks the status of the device. If the device status is `revoked` or `inactive`, the request is denied with a `403 Forbidden` response.
3. **Monotonic Event Logging**: Outputs insertions, updates, and deletes relative to the client's `cursor` offset, ensuring that the client remains up to date in correct order of events.
