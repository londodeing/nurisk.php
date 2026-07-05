# M06 — SITREP DEPENDENCY ANALYSIS

## Assessment Field → Sitrep Consumer Matrix

| Assessment Field | Source Table | Sitrep Consumer Field | Status |
| --- | --- | --- | --- |
| `id_assessment_utama` | `assessment_utama` | `operasi_sitrep.id_assessment_basis` | READY |
| `jenis_laporan` | `assessment_utama` | Contextual reference for Sitrep | READY |
| `cakupan_wilayah_deskripsi` | `assessment_utama` | `operasi_sitrep.snapshot_dampak` (Location string) | READY |
| `latitude`, `longitude` | `assessment_utama` | `operasi_sitrep.snapshot_dampak` | READY |
| `meninggal` | `assessment_dampak_manusia` | `operasi_sitrep.snapshot_dampak.meninggal` | READY |
| `hilang` | `assessment_dampak_manusia` | `operasi_sitrep.snapshot_dampak.hilang` | READY |
| `luka_berat` | `assessment_dampak_manusia` | `operasi_sitrep.snapshot_dampak.luka_berat` | READY |
| `luka_ringan` | `assessment_dampak_manusia` | `operasi_sitrep.snapshot_dampak.luka_ringan` | READY |
| `menderita_mengungsi` | `assessment_dampak_manusia`| `operasi_sitrep.snapshot_dampak.menderita_mengungsi` | READY |
| `nama_kebutuhan` (List) | `assessment_kebutuhan_mendesak` | `operasi_sitrep.snapshot_dampak.kebutuhan` | READY |

## Analysis

### 1. Missing Fields
- No missing domain fields. The fundamental raw data needed to generate the `snapshot_dampak` JSON blob for Sitrep is completely captured.

### 2. Duplicated Fields
- None natively in the database, but Sitrep will duplicate this data into its `snapshot_dampak` JSON column as an immutable historical record. This is intentional.

### 3. Future Dependencies
- **BR-ASSESSMENT-008 Hardening**: If Sitrep M06 relies on `id_assessment_basis`, the Assessment Policy MUST absolutely block the deletion of that assessment record to maintain relational integrity. Currently, it does not.
- **Trigger `tr_auto_snapshot_sitrep`**: Sitrep will need a database trigger to auto-populate the JSON snapshot. That trigger will explicitly depend on the schema structure of `assessment_dampak_manusia`. If we rename PKs/FKs in ARCH-003B, the M06 Sitrep trigger must use the new names.

## Sitrep Readiness Verdict
**NEEDS HARDENING** (Data is ready, but safety constraints are lacking).
