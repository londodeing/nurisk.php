# ARCH_006D_ROUTE_BINDING_AUDIT

## 1. Scope
Audit of API route definitions (`routes/api.php`) to ensure that all dynamic resource bindings exclusively use `{uuid}` instead of `{id}`.

## 2. Findings

### 2.1 Route Definitions
- **Penugasan:**
  - `GET /api/v1/penugasan/{uuid}` (show)
  - `PATCH /api/v1/penugasan/{uuid}` (update)
  - `PATCH /api/v1/penugasan/{uuid}/status` (status)
  - `DELETE /api/v1/penugasan/{uuid}` (destroy)
- **Klaster:**
  - `GET /api/v1/klaster/{uuid}` (show)
  - `PATCH /api/v1/klaster/{uuid}` (update)
  - `DELETE /api/v1/klaster/{uuid}` (destroy)
- **Assessment & Sitrep:**
  - No dynamic bindings exist (`/api/v1/assessment/{uuid}` and `/api/v1/sitrep/{uuid}` do not exist). This is intentional and correct as Assessments and Sitreps are strictly immutable (append-only via POST) as verified in ARCH-006C Phase 5.

### 2.2 Controller Verification
The corresponding controllers (`KlasterApiController` and `PenugasanApiController`) correctly define their method signatures to expect `$uuid` instead of `$id`:
```php
public function show(string $uuid): JsonResponse
public function update(..., string $uuid): JsonResponse
public function destroy(string $uuid): JsonResponse
```
Inside these methods, manual resolution is correctly performed (e.g., `OperasiPenugasan::where('uuid_penugasan', $uuid)->firstOrFail()`).

## 3. Conclusion for Phase 3
The route bindings are **COMPLIANT**. No standard integer bindings (`{id}`) are exposed in the `v1` REST API for the Operasi domain. The use of explicit `{uuid}` string parameters correctly mitigates IDOR enumeration at the routing level.
