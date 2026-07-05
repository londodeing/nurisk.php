# ARCH-003B — DATABASE AUDIT

## 1. Table: assessment_utama

### Current Status
- **PK**: `id_assessment_utama` (COMPLIANT)
- **FK**: `id_insiden` references `operasi_insiden(id_insiden)` without explicit name (VIOLATION). Must be `fk_assessment_utama_id_insiden`.
- **Enum**: `jenis_laporan` uses `['kaji_cepat', 'kaji_lanjutan']` (VIOLATION). Must be `pendataan_lanjutan`.
- **Timestamps**: Uses `$table->timestamp()->nullable()`. (VIOLATION). Must use `dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` and `diperbarui_pada TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP`.
- **Soft Delete**: `dihapus_pada` is present (COMPLIANT).
- **Indexes**: Missing explicit indexing on `id_insiden` and `is_latest` which are frequently queried together.

## 2. Table: assessment_dampak_manusia

### Current Status
- **PK**: `id_assessment_dampak` (VIOLATION). Must be `id_dampak_manusia` per DATABASE_CONVENTION.md.
- **FK**: `id_assessment_utama` references `assessment_utama` without explicit name (VIOLATION). Must be `fk_assessment_dampak_manusia_id_assessment_utama`.
- **Timestamps**: Missing `dibuat_pada` and `diperbarui_pada` entirely (VIOLATION).
- **Soft Delete**: Not required per convention, but table lacks history tracking.

## 3. Table: assessment_kebutuhan_mendesak

### Current Status
- **PK**: `id_assessment_kebutuhan` (VIOLATION). Must be `id_kebutuhan_mendesak` per DATABASE_CONVENTION.md.
- **FK**: `id_assessment_utama` references `assessment_utama` without explicit name (VIOLATION). Must be `fk_assessment_kebutuhan_mendesak_id_assessment_utama`.
- **Timestamps**: Missing `dibuat_pada` and `diperbarui_pada` entirely (VIOLATION).

## 4. Required Migration Patches

A new migration patch `2026_06_17_090001_harden_assessment_schema.php` must be created to:
1. Rename PK columns in child tables.
2. Add explicitly named FK constraints.
3. Update `jenis_laporan` Enum.
4. Alter timestamps to match `DATABASE_CONVENTION.md`.
5. Add missing indexes for performance.
