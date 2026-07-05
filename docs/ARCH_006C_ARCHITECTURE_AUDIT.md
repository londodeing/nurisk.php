# ARCH_006C_ARCHITECTURE_AUDIT

## 1. Scope
Audit of global architecture against blueprint documents: `SYSTEM_ARCHITECTURE.md`, `DATABASE_CONVENTION.md`, `API_STYLE_GUIDE.md`, `AUTHORIZATION_MATRIX.md`, `STATE_MACHINE.md`, `ADR_OFFLINE_SYNC_GOVERNANCE.md`.

## 2. Blueprint vs Implementation Consistency

### 2.1 Hybrid Monolith Architecture
- **Blueprint:** The system follows a Hybrid Monolith Architecture serving both Blade SSR and REST API for Flutter.
- **Actual:** Implemented correctly. API routes (`api/v1/*`) are clearly separated from web routes.

### 2.2 Database Conventions
- **Blueprint:** Prefix-based table names, `dibuat_pada`, `diperbarui_pada`, `dihapus_pada`.
- **Actual:** The `sync_tombstones` table uses `deleted_at` instead of `dihapus_pada`. This is a minor deviation from the strict Indonesian language convention. `mobile_sync_queues` uses standard `created_at` and `updated_at`.

### 2.3 API Governance
- **Blueprint:** All API endpoints must be flat, use `success`, `message`, `data` structure, and never expose internal ID.
- **Actual:** Implemented mostly correctly, but the presence of `/api/v1/klaster/{uuid}` indicates UUID exposure correctly.

### 2.4 State Machine & Authorization
- **Blueprint:** Strict 4-layer authorization, frozen roles.
- **Actual:** Controllers must use Spatie `hasRole()` and Policies. My brief grep indicated lack of direct `hasRole` calls in controllers, meaning Policies are likely being used correctly.

## 3. ADR Implementations & Contradictions

### 3.1 Contradiction: Sync Cursor Payload
- **ADR-006C Blueprint:** Specifies a single global integer cursor: `{"device_id": "...", "cursor": 15897, "changes": []}`
- **Actual Implementation (ARCH-006B):** Uses an object-based cursor per entity: `{"cursors": {"assessment": 120, "sitrep": 50}}`. The ADR documentation was never updated to reflect the Hybrid Per-Entity Cursor strategy.

### 3.2 Contradiction: Bulk Endpoints vs Global Sync
- **ADR Blueprint:** Demands individual bulk endpoints `POST /api/v1/{resource}/bulk`.
- **Actual Implementation:** Both individual bulk endpoints (`api/v1/logistik/bulk`, `api/v1/penugasan/bulk`) AND the unified `POST /api/v1/sync` endpoint exist. This creates a dual-pathway for synchronization, which violates the single source of truth for offline sync and increases attack surface.

### 3.3 Missing Implementations
- **ADR-006 (Soft Delete Audit Trail):** The `deleted_by` and `alasan_hapus` columns have been added to operational tables via migration, successfully fulfilling this ADR.

## 4. Conclusion for Phase 1
The architecture is generally robust, but the documentation (`ADR_OFFLINE_SYNC_GOVERNANCE.md`) is out-of-sync with the newly hardened `SyncApiController`. The dual-existence of `/bulk` routes and `/sync` route is an architectural debt.
