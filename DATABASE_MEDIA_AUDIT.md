# DATABASE MEDIA AUDIT

## All Tables with File/Media Fields

| Table | Field | Type | Nullable | Model Fillable | Used In |
|-------|-------|------|----------|----------------|---------|
| `laporan_kejadian` | `photo_path` | `varchar(255)` | YES | ✅ `$fillable` includes `photo_path` | `LaporController.store`, `LaporanKejadianApiController` |
| `operasi_surat_keluar` | `file_pdf_path` | `varchar(255)` | YES | ✅ `$fillable` includes `file_pdf_path` | `SuratPdfService`, `SuratController.download` |
| `inventaris_aset` | `foto_utama_path` | `varchar(255)` | YES | ✅ `$guarded = ['id_aset']` (all fillable) | Form exists but controller never processes |
| `inventaris_dokumen` | `file_path` | `varchar(255)` | YES | ✅ `$guarded = ['id_dokumen']` (all fillable) | Unknown |
| `inventaris_kondisi_log` | `foto_path` | `varchar(255)` | YES | Unknown | Unknown |
| `inventaris_pemeliharaan` | `dokumen_path` | `varchar(255)` | YES | Unknown | Unknown |
| `organisasi_sk` | `dokumen_file` | `varchar(255)` | YES | ✅ `$fillable` includes `dokumen_file` | Unknown |

## Issues Found

### 1. Asset Registration — Foto Field Never Saved

**File:** `resources/views/assets/create.blade.php:76`
```html
<input type="file" name="foto" accept="image/*" capture="environment">
```

**Controller:** `app/Http/Controllers/OrgAssetController.php`
- The `store()` method handles `nama_aset`, `kondisi`, `lokasi` etc.
- **But `foto` is NEVER read, validated, or stored.**
- The `foto_utama_path` field in `inventaris_aset` is always `NULL`.

**Risk:** HIGH — Users are uploading photos that are silently discarded.

### 2. Asset CSV Import — File Not Persisted

**File:** `resources/views/assets/create.blade.php:116`
```html
<input type="file" name="csv_file" accept=".csv" required>
```

**Controller:** `app/Http/Controllers/OrgAssetController.php`
- The `import()` method reads CSV line-by-line via `fgetcsv($handle)`
- **The uploaded CSV file is never stored to disk** → no record, no audit trail, no re-import possibility.

**Risk:** MEDIUM — No way to audit or replay imports.

### 3. Laporan Kejadian Photo is `nullable` vs Form `required`

**DB:** `photo_path varchar(255) NULL`
**Validation:** `'foto' => ['nullable', 'image', ...]`
**Form:** `<input ... required>`

**Risk:** MEDIUM — Mismatch allows submissions without photo.

### 4. varchar(255) May Be Too Short

Modern phone camera photos have long filenames when UUID-based. With nested path like `laporan/foto/2026/06/...`, 255 chars is sufficient currently but may be tight with multi-level paths or base64 filenames.

**Risk:** LOW — 255 chars is enough for current path depth.

## Data Integrity Check

```sql
-- Orphaned DB records (have path but file doesn't exist)
SELECT photo_path FROM laporan_kejadian 
WHERE photo_path IS NOT NULL;

-- Only 2 records have photos, both exist on disk. No orphaned DB records.
-- But 12 files on disk have NO DB record → reverse orphans.
```
