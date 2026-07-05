# ARCH-006: Baseline Audit Report

This report presents the baseline audit of existing operational domains (M05, M06, M08, M09) for NURISK to verify sync compliance prior to implementing the Offline Sync Infrastructure.

---

## 1. Compliance Checklist

| Operational Domain | Table Name | Model Class | Public UUID | Soft Delete | Timestamps | API Support (V1) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **M05: Assessment** | `assessment_utama` | `AssessmentUtama` | `uuid_assessment` | Yes | NURISK (`dibuat_pada`, `diperbarui_pada`) | index, store |
| **M06: Sitrep** | `operasi_sitrep` | `OperasiSitrep` | `uuid_sitrep` | Yes | NURISK (`dibuat_pada`, `diperbarui_pada`) | index, store |
| **M08: Penugasan** | `operasi_penugasan` | `OperasiPenugasan` | `uuid_penugasan` | Yes | NURISK (`dibuat_pada`, `diperbarui_pada`) | index, show, store, status, destroy |
| **M09: Klaster** | `operasi_klaster` | `OperasiKlaster` | `uuid_klaster_operasi` | Yes | NURISK (`dibuat_pada`, `diperbarui_pada`) | index, show, store, update, destroy |

---

## 2. API v1 Endpoints & Capabilities

All current active endpoints under the `v1` prefix have been verified:

1. **GET /api/v1/assessment**
   - Supports: Pagination (Custom Meta), Filtering (`jenis_laporan`, `id_pembuat`), Sorting (`waktu_assesment`, `dibuat_pada`), Sync (`updated_since`).
2. **GET /api/v1/sitrep**
   - Supports: Pagination (Custom Meta), Filtering (`id_pembuat`, `nomor_sitrep`), Sorting (`waktu_sitrep`, `dibuat_pada`), Sync (`updated_since`).
3. **GET /api/v1/penugasan**
   - Supports: Pagination (Custom Meta), Filtering (`status_penugasan`, `peran_otoritas`, `id_pengguna`), Sorting (`waktu_mulai`, `waktu_selesai`, `dibuat_pada`), Sync (`updated_since`).
4. **GET /api/v1/klaster**
   - Supports: Pagination (Custom Meta), Filtering (`status_klaster`, `prioritas`, `id_master_klaster`), Sorting (`waktu_aktivasi`, `waktu_ditutup`, `dibuat_pada`), Sync (`updated_since`).

---

## 3. Findings & Recommendations
- **Status**: 100% compliant with standard NURISK architecture patterns.
- All transactional tables have UUIDs, SoftDeletes, and custom timestamps.
- **Next Step**: Proceed with Phase 2: Device Registry.
