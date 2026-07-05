# M10_MOBILISASI_READINESS

## 1. Scope
Readiness Assessment for the transition to M10 Mobilisasi (Mobile App Integration and Deployment).

## 2. Mobilisasi Pre-Requisites Checklist

### 2.1 Backend / API Pre-Requisites (PENDING)
Before the Flutter Mobile App (M10) can safely integrate with the Laravel API, the following critical architectural debts must be resolved:

- [ ] **Strict UUID Governance:** Refactor all public-facing REST endpoints (`SitrepApiController`, `AssessmentApiController`, `KlasterApiController`) and their corresponding Form Requests to strictly accept and return UUIDs (`uuid_insiden`, `uuid_assessment`, etc.). All traces of internal integer IDs (`id_insiden`) must be removed from the request payloads to prevent IDOR vulnerabilities.
- [ ] **Unit Test Remediation:** Fix the 5 broken tests in `OfflineSyncTest`, `TombstoneSyncTest`, `DeviceRegistryTest`, and `ConflictResolutionTest` by updating their mocked payloads to include `request_id` and `cursors`.

### 2.2 Flutter Integration Directives (READY)
The Flutter Development Team must be given the following directives:

- [x] **Sync Exclusivity:** The Flutter App MUST NOT use standard REST index endpoints (`/api/v1/assessment`, `/api/v1/sitrep`) for offline hydration. It must exclusively use `POST /api/v1/sync` for both pulling data and pushing offline queues.
- [x] **Cursor Management:** The Flutter App must implement a local persistent store (e.g., Hive or SQLite) to save and track the hybrid Per-Entity Cursors returned by the Sync endpoint.
- [x] **Idempotency Tracking:** Every offline mutation pushed to the server must include a mathematically unique `request_id` to prevent double-processing during network retries.

## 3. Verdict
The Backend is **CONDITIONALLY READY** for M10 Mobilisasi. The sync infrastructure is brilliant and robust, but the traditional REST APIs require an immediate UUID refactor patch to ensure security compliance before handing the endpoints over to the mobile developers.
