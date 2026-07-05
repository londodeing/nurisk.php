# ARCH_006B_FLUTTER_COMPATIBILITY

## 1. Overview
This document evaluates the compatibility of the proposed ARCH-006B backend changes with the Flutter Offline First mobile client requirements.

## 2. Compatibility Assessment

### 2.1 Entity Cursors
- **Change:** Transition from `cursor: integer` to `cursors: { entity: integer }`.
- **Impact on Flutter:** Flutter's local SQLite database must be updated to store sync cursors per entity table rather than a single global `SharedPreferences` integer. 
- **Compatibility:** **Excellent**. This prevents a failure in one domain (e.g., `sitrep`) from blocking the synchronization of a completely unrelated domain (e.g., `assessment`).

### 2.2 Device Authentication & Tokens
- **Change:** Introduction of `device_token`, `trust_score`, and the `/api/v1/device/refresh-token` endpoint.
- **Impact on Flutter:** The Flutter HTTP interceptor must be configured to capture token expiry and automatically call the refresh endpoint transparently. Long-lived device tokens fit well with the offline-first approach, reducing forced logouts while in low-connectivity areas.
- **Compatibility:** **Excellent**.

### 2.3 Idempotency via Request ID
- **Change:** Every sync request must include a unique `request_id` (UUID).
- **Impact on Flutter:** The Flutter sync queue must assign a UUID to a payload upon creation. When retrying a failed HTTP request (e.g., due to a timeout), Flutter must reuse the same `request_id`.
- **Compatibility:** **Required/Excellent**. This allows Flutter to safely retry operations without fearing data duplication.

### 2.4 Tombstone Processing
- **Change:** Server sends `tombstones` containing `uuid_entity` and `entity_type`.
- **Impact on Flutter:** During the sync response processing phase, Flutter must iterate over the tombstones array and execute `DELETE FROM {entity_type} WHERE uuid = {uuid_entity}` in the local SQLite database.
- **Compatibility:** **Excellent**. Ensures local cache coherence.

### 2.5 Conflict Handling
- **Change:** Server returns `409 Conflict` with `server_version` and `client_version`.
- **Impact on Flutter:** Flutter must intercept `409` responses, halt the automatic sync queue for that specific entity, and trigger a UI prompt for manual conflict resolution.
- **Compatibility:** **Moderate Effort Required**. The Flutter team will need to build conflict resolution UI flows.

## 3. Checklist for Flutter Team
To be compatible with ARCH-006B, the Flutter team must:
- [ ] Migrate local cursor storage from a single integer to an entity map.
- [ ] Inject `request_id` (UUID) into the `/api/v1/sync` payload.
- [ ] Handle 409 Conflict responses and prompt users.
- [ ] Handle `tombstones` array and perform local SQLite deletions.
- [ ] Implement Token Refresh Interceptor for `/api/v1/device/refresh-token`.

## 4. Conclusion
The ARCH-006B API Contract significantly bolsters safety for the Flutter client, particularly via Idempotency and Tombstones. The changes are strictly necessary to pass the `FLUTTER_OFFLINE_CERTIFICATION`.
