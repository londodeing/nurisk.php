# ARCH_006C_SECURITY_AUDIT

## 1. Scope
Audit of Security Vectors, specifically Lapis 4 Authorization, Form Requests, Sanctum Authentication, and Device Authentication.

## 2. Authorization (Lapis 4)
- **Rule:** Strict 4-layer authorization using Laravel Policies and Spatie Permissions.
- **Finding:** Policies (`SitrepPolicy`, `KlasterPolicy`, `AssessmentPolicy`, `PenugasanPolicy`) correctly implement role checks via `$this->authCtx()->hasRole('...')`. 
- **Finding:** Controllers strictly delegate authorization to these policies (`$this->authorize('viewAny', ...)`).
- **Verdict:** Implemented correctly. The business logic is securely decoupled from the presentation layer.

## 3. Input Validation (Form Requests)
- **Rule:** Strict validation via FormRequests to prevent mass assignment and injection.
- **Finding:** `StoreAssessmentRequest`, `StoreSitrepRequest`, etc., are successfully implemented. They correctly throw `HttpResponseException` returning standard JSON on validation failure.
- **Vulnerability (IDOR Risk):** Form requests currently accept and validate internal integer keys (`id_insiden`) instead of UUIDs. While protected by Policies (which check if the user has access to that specific `Insiden`), exposing internal integers allows attackers to enumerate database sizes and attempt IDOR attacks. This violates the `RULE-UUID-001` blueprint.

## 4. Authentication (Sanctum & Device Registry)
- **Finding:** Sanctum is used as the primary driver for API authentication.
- **Finding:** Device Registry (`mobile_devices` table) adds an extra layer of security, allowing administrators to revoke sync access for compromised devices without necessarily revoking the underlying user's session token.

## 5. Conclusion for Phase 4
The overall security posture is strong, heavily utilizing Laravel's built-in robust features (Policies, Form Requests, Sanctum). However, the exposure of internal integer Primary Keys (`id_insiden`, etc.) in API requests is a significant architectural flaw that must be patched before M10 Mobilisasi to prevent enumeration and potential IDOR vulnerabilities. The codebase must be refactored to accept only UUIDs on all public interfaces.
