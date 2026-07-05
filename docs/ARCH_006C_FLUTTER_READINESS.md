# ARCH_006C_FLUTTER_READINESS

## 1. Scope
Audit of API endpoints to verify readiness for Flutter Offline-First integration as per M10 Mobilisasi requirements.

## 2. API Endpoint Analysis

### 2.1 UUID Governance Violation
- **Rule:** APIs must strictly expose and accept UUID public identifiers. Internal Integer IDs must be obscured.
- **Finding:** Most index and store endpoints (`/api/v1/assessment`, `/api/v1/sitrep`, `/api/v1/klaster`) still demand the internal integer ID `id_insiden` for foreign key relationships:
  ```php
  $request->validate(['id_insiden' => 'required|exists:operasi_insiden,id_insiden']);
  ```
- **Risk:** High. Exposing internal integer IDs increases attack surface (IDOR) and violates global blueprint rules. Flutter developers will struggle if they are forced to track internal integer IDs instead of standard UUIDs.

### 2.2 Offline Sync Paradigm Contradiction
- **Rule:** APIs must support Cursor-based Sync.
- **Finding:** The traditional REST endpoints (`AssessmentApiController`, `SitrepApiController`, `KlasterApiController`) still rely on timestamp-based incremental sync:
  ```php
  if ($request->has('updated_since')) { ... }
  ```
- **Finding 2:** They do not return Tombstones or Sync Cursors inline.

### 2.3 The Hybrid Sync Pathway
- The newly implemented `POST /api/v1/sync` handler resolves the issues mentioned above. It fully utilizes the `SyncCursor` table, generates tombstones, and provides an offline-first ready payload.
- **Implication:** The standard REST API is fundamentally **NOT READY** for direct offline-first sync operations. The Flutter application **MUST** exclusively use the unified `POST /api/v1/sync` endpoint for hydration and pushes.

## 3. Pagination, Filtering, and Sorting
- **Finding:** Pagination, filtering, and sorting conventions are correctly implemented across individual REST endpoints.
- **Finding:** Allowed sort columns and directions are safely hardcoded to prevent SQL injection.

## 4. Conclusion for Phase 2
The Flutter team should be explicitly instructed to **IGNORE** the individual index REST endpoints for syncing purposes. All offline-first hydration must be routed through `POST /api/v1/sync`. For simple single-resource fetching and mutations, the REST endpoints are functional, but require refactoring to use UUIDs for foreign keys (`id_insiden` -> `uuid_insiden`) before they can be safely consumed by the mobile application.
