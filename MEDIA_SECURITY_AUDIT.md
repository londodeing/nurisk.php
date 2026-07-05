# MEDIA SECURITY AUDIT

## Current Security Measures

| Check | Status | Location |
|-------|--------|----------|
| MIME validation | ✅ | `'image'` rule validates MIME type |
| Extension validation | ✅ | `'mimes:jpeg,png,jpg'` |
| Size validation | ⚠️ | `'max:2048'` but PHP `upload_max_filesize` may silently discard first |
| Filename sanitization | ✅ | Laravel `store()` auto-generates UUID filename |
| Directory traversal | ✅ | `store()` prevents path traversal |
| Double extension | ✅ | UUID filenames prevent this |
| Executable upload | ✅ | MIME check rejects executables |
| Virus scanning | ❌ **NONE** | No ClamAV or similar |
| CSRF protection | ✅ | `@csrf` in form |
| Auth check | ⚠️ | Public form has no auth (intentional, but media is public) |

## Vulnerabilities

### 1. Silent File Discard Bypasses Validation
**Risk:** MEDIUM  
**Issue:** When PHP discards a file > `upload_max_filesize`, Laravel's validation rules never execute. The `nullable` rule passes. No error is shown to user.  
**Fix:** Increase `upload_max_filesize` to match or exceed Laravel validation limit.

### 2. No File Content Validation
**Risk:** LOW  
**Issue:** Only MIME type is checked via `image` rule. A polyglot file (valid image + hidden payload) would pass.  
**Fix:** Add server-side image re-encoding (GD/Imagick) to strip non-image data.

### 3. Public Upload, No Rate Limiting
**Risk:** MEDIUM  
**Issue:** `POST /lapor` has no rate limiting. An attacker could upload thousands of files to fill disk space.  
**Fix:** Add `throttle:10,60` middleware to public upload route.

### 4. Public File Access
**Risk:** LOW  
**Issue:** Files stored on `public` disk with `visibility = public`. Any uploaded image is publicly accessible if the symlink exists.  
**Note:** This is intentional for a disaster reporting platform, but should be documented.

### 5. No Deletion Cleanup
**Risk:** MEDIUM  
**Issue:** There is no mechanism to delete orphaned files or files belonging to deleted records. 12 orphaned files already exist.  
**Fix:** Implement a cleanup command as part of the standard media pipeline.

## Security Score

| Category | Score (0-10) |
|----------|--------------|
| Upload Validation | 6/10 |
| Storage Security | 7/10 |
| Input Sanitization | 8/10 |
| Error Handling | 3/10 |
| Cleanup & Housekeeping | 2/10 |
| **Overall** | **5.2/10** |
