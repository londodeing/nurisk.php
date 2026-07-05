# ADR-001: Assessment Endpoint Architecture

## Status
Proposed

## Context & Problem Statement
Currently, the REST API for creating assessments uses a nested endpoint structure: `POST /api/insiden/{id}/assessment`. However, the official `API_CONTRACT.md` dictates a flat endpoint architecture: `POST /api/assessment` with the `id_insiden` sent as part of the JSON payload. We must determine which architectural pattern to officially adopt moving forward, especially considering that the Flutter Mobile App will aggressively consume these endpoints.

## Options Considered

### Option 1: Nested Route Architecture (Current Implementation)
- **Endpoint**: `POST /api/insiden/{id}/assessment`
- **Payload**: `{"jenis_laporan": "kaji_cepat", ...}`

**Pros:**
- Adheres closely to strict RESTful hierarchical relationships (an assessment belongs to an incident).
- Prevents tampering with the `id_insiden` payload property.

**Cons:**
- Deeply nested URLs become brittle and cumbersome for Mobile API clients.
- Harder to manage route definitions and middleware in Laravel for decoupled operations.

### Option 2: Flat Endpoint Architecture (Contract Specification)
- **Endpoint**: `POST /api/assessment`
- **Payload**: `{"id_insiden": 12, "jenis_laporan": "kaji_cepat", ...}`

**Pros:**
- Follows `API_CONTRACT.md` strictly.
- Much easier for Flutter mobile state management (e.g., passing a generic map of data to a single `/api/assessment` endpoint provider without needing dynamic URL string interpolation).
- Scales well for batch processing or offline-sync patterns where multiple records with different `id_insiden` might be synced to a bulk endpoint.
- Flatter API surfaces are preferred in modern mobile-first backend designs.

**Cons:**
- Requires the `id_insiden` to be validated explicitly within the FormRequest, adding slight overhead to the Validation layer.

## Final Decision
**Option 2: Flat Endpoint Architecture**

## Justification
The project is adopting a "Flutter-First API Design" strategy. Flutter's data layer (e.g., using Dio or http) handles JSON payload serialization natively. Constructing nested URL paths dynamically requires extra string manipulation and makes generic network repository classes harder to abstract. By flattening the endpoint to `POST /api/assessment` and supplying the `id_insiden` in the payload, we ensure high maintainability, adherence to `API_CONTRACT.md`, and optimal developer experience for the mobile team. This standard will be enforced across all subsequent domains.
