# ARCH_006D_UUID_REMEDIATION

## 1. Scope
Remediation of the IDOR risk identified in Phase 1 by refactoring the Operasi domain APIs to exclusively accept and return `uuid_insiden` instead of the internal integer primary key `id_insiden`.

## 2. Refactoring Actions

### 2.1 API Resources Updated
The following resources were modified to map the output JSON key `uuid_insiden` to the loaded relation (`$this->insiden->uuid_insiden`), and the internal `id_insiden` was removed:
- `AssessmentResource.php`
- `SitrepResource.php`
- `KlasterResource.php`
- `PenugasanResource.php`
- `OperasiKlasterResource.php` (Fixed `uuid` mapping)
- `OperasiPosajuResource.php` (Fixed `uuid` mapping)

### 2.2 Form Requests Updated
The validation rules in the following requests were updated from `id_insiden => 'required|exists:operasi_insiden,id_insiden'` to `uuid_insiden => 'required|uuid|exists:operasi_insiden,uuid_insiden'`:
- `StoreAssessmentRequest.php`
- `StoreSitrepRequest.php`
- `StoreKlasterRequest.php`
- `StorePenugasanRequest.php`
- `StorePosajuRequest.php`
- `StoreRelawanKebutuhanRequest.php`

### 2.3 Controllers Refactored
The following controllers were updated to accept `uuid_insiden` from requests. The controllers now securely resolve this UUID back to the internal `OperasiInsiden` model before invoking the Service layer, which continues to operate safely using internal IDs:
- `AssessmentApiController.php` (index & store)
- `SitrepApiController.php` (index & store)
- `KlasterApiController.php` (index & store)
- `PenugasanApiController.php` (index, store & bulk)

## 3. Conclusion for Phase 2
The Operasi REST APIs are now securely decoupled from internal database architecture. The Flutter app (M10) will only ever see and interact with `uuid_insiden`, fully satisfying the `RULE-UUID-001` blueprint and eliminating the enumeration/IDOR attack surface.
