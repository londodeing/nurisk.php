# ARCH-006: Bulk Operations Report

This report presents the implementation and rules of the transactional-safe bulk endpoints designed for Flutter mobile optimization.

---

## 1. Bulk Endpoint List

All bulk endpoints use `POST` and accept batch processing arrays:
- **`POST /api/v1/penugasan/bulk`**: Fully active, writes directly to the `operasi_penugasan` table.
- **`POST /api/v1/logistik/bulk`**: Stub endpoint active, returns uuid mapped responses.
- **`POST /api/v1/mobilisasi/bulk`**: Stub endpoint active, returns uuid mapped responses.

---

## 2. Bulk Execution Specifications

1. **Transaction Safety (Per-Item isolation)**: In `PenugasanApiController::bulk`, each record is processed inside its own isolated database transaction. A failure in one item does not roll back the entire request batch, ensuring partial successes are safely committed.
2. **Duplicate Protection**: Before processing an assignment item, the system queries the database to ensure no active assignment (`status_penugasan = 'aktif'`) already exists for the target user under the same incident. If a duplicate is found, that item is registered as a failure and skipped.
3. **Partial Failure Reporting**: The bulk response returns a `207 Multi-Status` or `200 OK` (if all succeed), reporting a detailed list of successes (with their UUIDs) and failures (with error messages).

### Response Schema Example
```json
{
  "success": false,
  "message": "Proses bulk selesai",
  "data": {
    "processed": 2,
    "success_count": 1,
    "failed_count": 1,
    "successes": [
      {
        "index": 0,
        "uuid": "e2a4c6e8-0b1d-3f5a-7c9e-1a2b3c4d5e6f",
        "message": "Penugasan berhasil dibuat"
      }
    ],
    "failures": [
      {
        "index": 1,
        "error": "Duplicate: Pengguna sudah memiliki penugasan aktif di insiden ini."
      }
    ]
  }
}
```
