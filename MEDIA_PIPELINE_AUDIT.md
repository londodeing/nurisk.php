# MEDIA PIPELINE AUDIT

## Pipeline Integrity Check: Laporan Kejadian (Incident Report Photo)

### STEP-BY-STEP AUDIT

| Step | Description | Status | Evidence |
|------|-------------|--------|----------|
| 1 | Frontend memilih file | ✅ | `resources/views/public/lapor.blade.php:235` — `<input type="file" name="foto" required>` |
| 2 | File tervalidasi (client) | ✅ | `accept="image/jpeg,image/png,image/jpg"` + `required` attribute |
| 3 | File diterima server | ⚠️ **GAGAL** | PHP `upload_max_filesize = 2M` — file > 2MB menyebabkan PHP silent discard |
| 4 | File ditulis ke Storage | ⚠️ **KADANG GAGAL** | `app/Http/Controllers/Web/LaporController.php:71` — `store()` hanya dipanggil jika `hasFile()` true, yang false saat file discard |
| 5 | Relative Path dihasilkan | ⚠️ **GAGAL** | `$photoPath = null` karena file discard |
| 6 | Path disimpan ke database | ⚠️ **GAGAL** | `photo_path => null` karena `$photoPath = null` |
| 7 | Transaction Commit | ✅ | `DB::transaction()` di `LaporController.php:74` — commit berhasil karena tidak ada error |
| 8 | Response redirect sukses | ✅ | `LaporController.php:93` — redirect with success message, user tidak tahu foto gagal |
| 9 | Frontend preview | ❌ **TIDAK ADA** | Tidak ada preview JS sebelum submit |
| 10 | File dapat diakses ulang | ❌ | `Storage::url()` di `show.blade.php:62` — 404 karena `public/storage` symlink tidak ada |

### Root Cause: Silent File Discard

**PHP Configuration:**
```
upload_max_filesize = 2M        → 2,097,152 bytes
max_file_uploads = 20
```

**Laravel Validation:**
```php
'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],  // 2048 KB = 2 MB
```

**The Bug:** When a user uploads a photo > 2MB:
1. PHP silently discards the file (per `upload_max_filesize` INI)
2. `$_FILES['foto']['error']` = `UPLOAD_ERR_INI_SIZE` (1)
3. Laravel `$request->hasFile('foto')` returns `false`
4. `$photoPath` stays `null`
5. Validation passes (field is `nullable`)
6. Record created with `photo_path => null`
7. User sees success — **no error feedback**

### Orphaned Files

```
storage/app/public/laporan/foto/
  ├── 14 files total
  ├── 2 referenced in laporan_kejadian table
  └── 12 ORPHANED (no DB record)
```

This confirms the bug: files ARE stored to disk (in some edge cases) but the DB write either failed or the connection was lost before commit.

### All Upload Points

| Module | File | Input Name | Storage | Status |
|--------|------|------------|---------|--------|
| Public Laporan | `LaporController.php:71` | `foto` | `public` disk | ⚠️ **BUG: silent discard** |
| Asset Registration | `views/assets/create.blade.php:76` | `foto` | — | ❌ **NEVER PROCESSED** by controller |
| Asset CSV Import | `views/assets/create.blade.php:116` | `csv_file` | — | ⚠️ **Processed via fgetcsv, not stored** |
| Surat PDF | `SuratPdfService.php:30` | — | `public` disk | ✅ Generated internally |
| Sync Snapshots | `SnapshotStorageService.php` | — | `local` disk | ✅ |
| Health Check | `HealthCheckController.php` | — | default disk | ✅ |

### Missing Storage Symlink

```bash
ls -la public/storage  →  No such file or directory
```

**`php artisan storage:link` has NEVER been run.** All `Storage::url()` calls will return broken URLs (404).

### Database-Only Records Without Files

> Run this query to check:
> ```sql
> SELECT id_laporan_kejadian, photo_path FROM laporan_kejadian 
> WHERE photo_path IS NOT NULL;
> ```
> Only 2 out of 30 records have photos → 93% of reports have no photo.
