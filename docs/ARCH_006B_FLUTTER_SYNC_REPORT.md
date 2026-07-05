# ARCH_006B_FLUTTER_SYNC_REPORT

## 1. Summary
This report outlines how the Flutter engineering team must adapt to the newly deployed ARCH-006B endpoints.

## 2. API Contract Changes

### Sync Request Payload (`POST /api/v1/sync`)
The mobile client must now construct requests matching the following shape:
```json
{
  "request_id": "c1386523-28f8-45b6-96b5-0c7f217ecfb1",
  "device_uuid": "device-uuid",
  "cursors": {
    "assessment": 120,
    "sitrep": 50,
    "klaster": 0,
    "penugasan": 12
  },
  "changes": [
    {
      "table": "sitrep",
      "action": "upsert",
      "data": { ... }
    }
  ]
}
```

### Sync Response Data
```json
{
  "success": true,
  "data": {
    "server_cursors": {
      "assessment": 125,
      "sitrep": 52,
      "klaster": 0,
      "penugasan": 15
    },
    "changes": [ ... ],
    "tombstones": [
      {
        "entity_type": "sitrep",
        "uuid_entity": "uuid-here",
        "deleted_at": "2026-06-17T12:00:00Z"
      }
    ],
    "conflicts": [ ... ]
  }
}
```

## 3. Required Flutter Actions
- **Idempotency Mapping**: Flutter must generate a v4 UUID for every *logical* sync attempt and persist it. If the HTTP request times out, retries must use the *exact same* `request_id`.
- **Tombstone Processing**: The app must iterate the `tombstones` array and execute physical `DELETE` statements on local SQLite.
- **Conflict Handling**: If `conflicts` is non-empty, halt background sync for those specific entity UUIDs and trigger a local notification to prompt manual resolution by the user.
