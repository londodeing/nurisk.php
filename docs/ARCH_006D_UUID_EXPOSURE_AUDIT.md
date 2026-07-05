# ARCH_006D_UUID_EXPOSURE_AUDIT

## 1. Scope
Audit of all Controllers, Resources, Routes, Policies, Form Requests, and Models to identify exposure of internal Integer Primary Keys (`id_insiden`, `id_assessment`, `id_sitrep`, `id_penugasan`, `id_klaster_operasi`) on public-facing REST endpoints.

## 2. Findings

### 2.1 API Resources (Response Body Exposure)
| File | Exposed Key | Target Replacement | Severity |
| :--- | :--- | :--- | :--- |
| `AssessmentResource` | `id_insiden` | `uuid_insiden` | High |
| `SitrepResource` | `id_insiden` | `uuid_insiden` | High |
| `KlasterResource` | `id_insiden` | `uuid_insiden` | High |
| `PenugasanResource` | `id_insiden` | `uuid_insiden` | High |
| `OperasiKlasterResource` | `id` (maps to `id_insiden`) | `uuid_insiden` | High |
| `OperasiPosajuResource` | `id` (maps to `id_insiden`) | `uuid_insiden` | High |
*Note: `uuid_assessment`, `uuid_sitrep`, `uuid_penugasan` are already correctly exposed, but the foreign key `id_insiden` must be obscured.*

### 2.2 Form Requests (Input Payload Exposure)
| File | Validated Key | Target Replacement | Severity |
| :--- | :--- | :--- | :--- |
| `StoreAssessmentRequest` | `id_insiden` | `uuid_insiden` | Critical |
| `StoreSitrepRequest` | `id_insiden` | `uuid_insiden` | Critical |
| `StoreKlasterRequest` | `id_insiden` | `uuid_insiden` | Critical |
| `StorePenugasanRequest` | `id_insiden` | `uuid_insiden` | Critical |
| `StorePosajuRequest` | `id_insiden` | `uuid_insiden` | Critical |
| `StoreRelawanKebutuhanRequest`| `id_insiden` | `uuid_insiden` | Critical |

### 2.3 Routes & Controllers
- `api.php`: The `api/v1/klaster/{uuid}` and `api/v1/penugasan/{uuid}` endpoints correctly use UUID path variables.
- Controllers currently map request data directly. Because `FormRequests` demand `id_insiden`, the Controllers inherently leak integer IDs.

## 3. Verdict
The IDOR risk is prevalent across the `Operasi` domain's REST endpoints due to the reliance on `id_insiden`. Phase 2 Remediation must patch all identified Resources and FormRequests, mapping them to `uuid_insiden`.
