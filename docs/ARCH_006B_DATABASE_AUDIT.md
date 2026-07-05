# ARCH_006B_DATABASE_AUDIT

## 1. Scope
This document confirms the database enhancements required for ARCH-006B sync readiness.

## 2. Table Modifications
- `mobile_devices`: 
  - Added `trust_score` INT DEFAULT 100 for anomaly detection.
  - Added `device_token` VARCHAR for non-JWT auth.
  - Added `token_expires_at` TIMESTAMP.

## 3. New Tables
- `mobile_sync_queues`
  - Purpose: Tracks the lifecycle of a sync request.
  - Indexing: `request_id` (UNIQUE), `device_uuid`, `status`.
- `sync_audit_logs`
  - Purpose: Tracks performance metrics of the sync system.
  - Indexing: `device_uuid`, `request_id`.
- `sync_conflicts`
  - Purpose: Archives payloads when a client attempts to overwrite a newer server record.
  - Indexing: `entity_type`, `uuid_entity`.

## 4. Compliance Verification
All schema updates adhere to the standard NURISK `DATABASE_CONVENTION.md` by relying on Laravel Eloquent standards and ensuring columns like `dibuat_pada` and `diperbarui_pada` are correctly integrated without hard deletes where audit trails are necessary.
