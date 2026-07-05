# MEDIA REFACTOR PLAN

## Bug Inventory

| # | Bug | Location | Root Cause | Risk | Priority |
|---|-----|----------|------------|-----|----------|
| B1 | **File > 2MB silent discard** | `php.ini` `upload_max_filesize=2M` + `LaporController.php:69-72` | PHP discards oversized files before Laravel validation; `hasFile()` returns false, `photo_path = null`, success shown to user | **HIGH** | **P0** |
| B2 | **Missing `storage:link`** | Filesystem | Artisan command never executed | **HIGH** | **P0** |
| B3 | **Asset `foto` upload not processed** | `OrgAssetController.store()` | Controller never reads `foto` from request | **HIGH** | **P1** |
| B4 | **12 orphaned files** | `storage/app/public/laporan/foto/` | `store()` writes file but DB transaction fails, no rollback cleanup | **MEDIUM** | **P1** |
| B5 | **No transaction-safe upload** | `LaporController.php:69-91` | File stored BEFORE DB transaction; if transaction fails, file is orphaned | **MEDIUM** | **P2** |
| B6 | **No API photo upload** | `LaporanKejadianApiController.store()` | Mobile/API users cannot upload photos | **HIGH** | **P2** |
| B7 | **No `update()` for laporan** | All controllers | No way to add/change photo after submission | **MEDIUM** | **P3** |
| B8 | **CSV import not stored** | `OrgAssetController.import()` | No audit trail for imports | **LOW** | **P3** |
| B9 | **No rate limiting on upload** | Route `POST /lapor` | Disk fill attack vector | **MEDIUM** | **P2** |
| B10 | **No image optimization** | System-wide | No thumbnails, no resizing, no compression | **LOW** | **P3** |

## Priority Classification

**P0 — CRITICAL (blocking functionality)**
- B1: Photos can't be saved for files > 2MB
- B2: Photos can't be viewed even when saved

**P1 — HIGH (major data loss)**
- B3: Asset photos uploaded but never stored
- B4: Orphaned files accumulating
- B5: File-DB inconsistency on transaction failure

**P2 — MEDIUM (missing features)**
- B6: Mobile app users can't submit photos
- B9: No upload rate limiting

**P3 — LOW (enhancement)**
- B7: No edit capability
- B8: Import audit trail
- B10: Image optimization

## Implementation Order

### Phase 1: Fix Critical Bugs (P0)

1. **Increase PHP upload limits:**
   - `upload_max_filesize = 10M`
   - `post_max_size = 20M`
   - `memory_limit = 128M`
   
2. **Run `php artisan storage:link`**

3. **Fix silent discard in LaporController:**
   - Add `max:10240` validation (10MB)
   - Show explicit error when file exceeds PHP limit
   - Consider using `$request->file('foto')->isValid()` check

### Phase 2: Fix High-Risk Bugs (P1)

4. **Fix OrgAssetController to process `foto`**

5. **Add cleanup command for orphaned files:**
   ```bash
   php artisan media:cleanup-orphans
   ```

6. **Move file store INSIDE transaction (with before-commit pattern)**

### Phase 3: Complete Missing Features (P2)

7. **Add photo upload to API endpoint**

8. **Add rate limiting to public upload route**

### Phase 4: Enhancements (P3)

9. **Build MediaUploadService** and migrate all modules

10. **Add image optimization with Intervention**

## Effort Estimate

| Phase | Tasks | Estimated Effort |
|-------|-------|-----------------|
| Phase 1 | 3 fixes | 2 hours |
| Phase 2 | 3 fixes | 4 hours |
| Phase 3 | 2 fixes | 4 hours |
| Phase 4 | 2 enhancements | 8 hours |
| **Total** | **10 items** | **~18 hours** |

## Rollback Plan

Each fix is independent. No single change should break existing functionality.
- Phase 1 changes are configuration + symlink (reversible)
- Phase 2-4 changes are additive (new code)
- Orphaned files should be backed up before cleanup
