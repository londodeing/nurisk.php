# NURISK Flutter Mobile Offline Certification

This document certifies that the NURISK Backend Sync API and schemas are certified as "Flutter-Ready" for offline-first replication and data consistency.

---

## 1. Sync Scenarios & Certifications

### Scenario 1: Offline Creation
- **Action**: Device A is offline and inserts a new assignment locally.
- **Verification**: Device A generates a local UUID and sets `sync_version = 1`. Upon reconnecting, Device A sends the upsert change. The server inserts it successfully with `sync_version = 1`.
- **Status**: **PASS**

### Scenario 2: Offline Update & Reconnection
- **Action**: Device A is offline and updates a local assignment.
- **Verification**: Device A keeps track of the local modification and sets `client_sync_version = 1`. Upon reconnecting, Device A sends the upsert change. The server updates the database record and sets `sync_version = 2`.
- **Status**: **PASS**

### Scenario 3: Offline Deletion (Tombstone Propagation)
- **Action**: Device B is online and soft-deletes a record. Device A is offline.
- **Verification**: Server soft-deletes the record, logging the tombstone event into `sync_tombstones`. When Device A reconnects and syncs with its last cursor, the server returns the tombstone, letting Device A purge its local storage.
- **Status**: **PASS**

### Scenario 4: Concurrent Modifications (Conflict Detection)
- **Action**: Device A and Device B both modify the same record.
- **Verification**: Server applies Device B's changes first, incrementing version to `2`. Device A sends its modifications with stale version `1`. Server rejects Device A's sync with `409 Conflict` containing server version details.
- **Status**: **PASS**

### Scenario 5: Clock Drift Independence
- **Action**: Device A has incorrect local timezone/time configurations.
- **Verification**: The server uses a monotonic `sync_cursor` log index rather than timestamps for delta tracking, making sync entirely clock-drift safe.
- **Status**: **PASS**

---

## 2. Integration Guidelines for Flutter Developers

1. **Local DB Keys**: Flutter developers must use the `uuid_` keys as the primary identifiers for local cache tables (e.g. Hive, Sqflite, ObjectBox). Never use or store integer IDs.
2. **Conflict Recovery**: Catch `409 Conflict` responses. Prompt users to choose between overwriting server data (retry with server version) or discarding local modifications (fetch latest).
3. **Queue Processing**: Send offline queues using `POST /api/v1/sync` in bulk to save client bandwidth.
