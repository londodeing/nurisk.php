# ARCH_006C_TEST_COVERAGE_AUDIT

## 1. Scope
Audit of Automated Test Coverage for NURISK via PHPUnit.

## 2. Test Execution Results
- **Command:** `php vendor/bin/phpunit`
- **Total Tests:** 362
- **Assertions:** 1131
- **Failures:** 5
- **Pass Rate:** 98.6%

## 3. Failure Analysis
All 5 failures originated from the newly hardened Offline Sync module (`ARCH-006B` additions). Specifically:
- `Tests\Feature\Operasi\ConflictResolutionTest`
- `Tests\Feature\Operasi\DeviceRegistryTest`
- `Tests\Feature\Operasi\OfflineSyncTest`
- `Tests\Feature\Operasi\TombstoneSyncTest`

**Root Cause:** 
All tests failed with HTTP 422 Unprocessable Entity instead of their expected 200/409/403 responses. The validation errors were identical:
```json
{
    "request_id": ["The request id field is required."],
    "cursors": ["The cursors field must be present."]
}
```
**Diagnosis:** During the `ARCH-006B` hardening phase, the `POST /api/v1/sync` endpoint's Form Request (`SyncRequest`) was strictly updated to require idempotent `request_id` and hybrid `cursors` payloads. However, the corresponding Feature Tests were **not updated** to reflect this new required payload structure. 

## 4. Conclusion for Phase 7
The core system tests are exceptionally healthy (357 passing tests covering domains like Assessment, Sitrep, Klaster, Wilayah, Auth). However, the Offline Sync test suite is broken due to stale payload mocking. This is technical debt that must be resolved prior to `M10 Mobilisasi`.

*Note: As per constraints, I am restricted to Read/Analyze mode and have not patched these tests.*
