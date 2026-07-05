# MEDIA PERFORMANCE AUDIT

## Current Performance Issues

### 1. No Image Optimization
All uploaded images are stored as-is. A 2MB JPEG is stored as 2MB on disk and served as 2MB to the browser. No thumbnails, no resizing, no compression.

**Impact:** Slow page loads on mobile connections. High bandwidth usage.

### 2. No Thumbnail Generation
Every photo display loads the full-resolution image. For list/index views, this is wasteful.

**Example:** `resources/views/operasi/laporan/show.blade.php:62` loads the full image even though it's displayed as a preview thumbnail.

### 3. Synchronous Upload
The public lapor form submits synchronously (no AJAX). User waits for the upload to complete before seeing the success message.

### 4. No Client-Side Preview
No `FileReader` or `URL.createObjectURL()` is used for image preview before upload. User cannot see what they uploaded until after the page reloads.

### 5. Single File Upload
Only one `photo_path` field exists. No support for multiple photo uploads. To add multiple photos, the entire schema would need to change.

## Performance Metrics

| Metric | Current Value | Target |
|--------|---------------|--------|
| Max single file size | 2 MB | 10 MB |
| Max uploads per request | 1 | 5+ |
| Image compression | None | WebP with quality 80 |
| Thumbnail generation | None | 150px auto-generated |
| Client preview | None | Show before submit |
| Upload method | Sync form POST | Async AJAX with progress |

## Bottlenecks

1. **PHP `upload_max_filesize = 2M`** — smallest bottleneck, blocks legitimate uploads
2. **No media processing library** — no Intervention Image, no GD integration
3. **No async upload** — form blocks during upload
4. **No CDN** — all media served from same server as application
