# MEDIA INTEGRITY REPORT

## Files on Disk Without Database Records (Orphans)

| Location | Count | Total Size | Status |
|----------|-------|------------|--------|
| `storage/app/public/laporan/foto/` | 12 files | ~1.4 MB | **ORPHANED** |
| `storage/app/public/surat/` | 0 files | — | Clean |

### Identified Orphans

All 12 orphaned files are in `storage/app/public/laporan/foto/`:
```
dW1vcOpaDYDddfR5lEb5Ko1gOqDHERJ0ElyysQza.jpg  → NOT in DB (but 2 DB records point to this!)
gnqLzRhcsq4CFNGWQ30uKVR0JEpvMwcvYvxnHprs.jpg  → NOT in DB
```
Wait — checking again. The audit found 14 total files in the directory. Only 2 DB records have `photo_path` values. Both DB records point to files that exist on disk:
- `dW1vcOpaDYDddfR5lEb5Ko1gOqDHERJ0ElyysQza.jpg`
- `gnqLzRhcsq4CFNGWQ30uKVR0JEpvMwcvYvxnHprs.jpg`

**So 12 files on disk have no DB record.** These are from failed submissions where:
1. The file was stored to disk (`store()` succeeded)
2. But the DB transaction either failed or didn't complete

**This confirms the upload pipeline is broken.** Files are being written to disk but the database write is not consistently occurring.

## Database Records Without Files

Zero. All 2 records with non-null `photo_path` have matching files on disk.

## Integrity Score

| Check | Result | Score |
|-------|--------|-------|
| Files with DB record | 2/14 (14%) | ⚠️ |
| DB records with file | 2/2 (100%) | ✅ |
| Orphaned files | 12 | ❌ |
| Orphaned DB records | 0 | ✅ |

**Conclusion:** The `store()` + DB `create()` combination is not atomic. Files persist even when the DB write fails or is rolled back. A cleanup mechanism is needed.

## Possible Causes of Orphaned Files

1. **PHP timeout** — `store()` completes but DB transaction times out
2. **MySQL deadlock** — `generateKodeKejadian()` uses `lockForUpdate()` which can deadlock
3. **Concurrent requests** — Two users submit simultaneously, one transaction fails after file write
4. **`upload_max_filesize` exceeded** — File > 2M is silently discarded (no file on disk, but creates 0-byte upload errors)
