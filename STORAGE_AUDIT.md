# STORAGE AUDIT

## Configuration

**File:** `config/filesystems.php`

| Disk | Root | URL | Visibility |
|------|------|-----|------------|
| `local` | `storage/app/private` | — (not served) | private |
| `public` | `storage/app/public` | `APP_URL/storage` | public |
| `s3` | — | — | private |

## Critical Issue: Missing Symlink

```bash
$ ls -la /home/londo/nurisk/public/storage
ls: cannot access 'public/storage': No such file or directory
```

**`php artisan storage:link` has never been run.**

### Impact
All `Storage::url()` calls return URLs that 404:
- `resources/views/operasi/laporan/show.blade.php:62` → `<img src="{{ Storage::url($laporan->photo_path) }}">`
- `resources/views/operasi/insiden/show.blade.php` → same pattern
- `app/Http/Controllers/Governance/SuratController.php:132` → `Storage::disk('public')->download()` (uses internal path, works)
- `public/storage` → would serve `storage/app/public` content

### Files Accessible Despite Missing Symlink
- PDF downloads via `SuratController.download()` use `Storage::disk('public')->download()` which reads directly from filesystem (works without symlink).
- **Photos are BROKEN** — `Storage::url()` generates `/storage/laporan/foto/xxx.jpg` which requires the symlink.

## Disk Contents

### `storage/app/public/laporan/foto/` — 14 files
```
dW1vcOpaDYDddfR5lEb5Ko1gOqDHERJ0ElyysQza.jpg  (115891 bytes)
gnqLzRhcsq4CFNGWQ30uKVR0JEpvMwcvYvxnHprs.jpg  (115891 bytes)
... 12 more orphaned files (all 115891 bytes)
```

### `storage/app/public/surat/2026/06/` — 48 files
```
surat-1.pdf through surat-278.pdf
```

## Permissions

```
storage/              drwxrwxr-x  (775)
storage/app/public/   drwxrwxr-x  (775)
storage/logs/         drwxrwxr-x  (775)
```

Adequate. No permission issues.

## PHP Upload Configuration

```
file_uploads          = On
upload_max_filesize   = 2M        ← TOO SMALL (phone photos: 3-5MB)
max_file_uploads      = 20
post_max_size         = 8M        ← TOO SMALL for multiple photos
```

**Root cause of user's bug:** `upload_max_filesize = 2M` causes PHP to silently discard files > 2MB before Laravel can validate them.

## S3 Configuration (Unused)

```env
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_BUCKET=
```

All empty. Cloud storage not configured.

## Recommendations

| # | Fix | Priority |
|---|-----|----------|
| 1 | Run `php artisan storage:link` | **HIGH** |
| 2 | Increase `upload_max_filesize` to 10M (or 20M for multiple photos) | **HIGH** |
| 3 | Increase `post_max_size` to 20M | **HIGH** |
| 4 | Add `max_size` validation that matches PHP config | **MEDIUM** |
| 5 | Configure S3 as offsite backup for uploaded media | **LOW** |
