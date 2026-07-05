# API_STYLE_GUIDE.md — NURISK

## 1. Overview
This guide defines the standard JSON response format for the NURISK Hybrid Monolith REST API. All domains must adhere to this standard to ensure seamless and predictable consumption by the Flutter Mobile App client.

## 2. Standard Responses

### 2.1 Success Response (200 OK / 201 Created)
Used when a request is successfully processed.

```json
{
  "success": true,
  "message": "Operasi berhasil dilakukan",
  "data": { ... }
}
```
- `success`: Boolean, always `true`.
- `message`: Human-readable success string.
- `data`: Object containing the requested resource or result. Can be null for simple actions.

### 2.2 Validation Error (422 Unprocessable Entity)
Used when FormRequest validation fails.

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "id_insiden": ["Field id_insiden wajib diisi."],
    "jenis_laporan": ["Nilai jenis laporan tidak valid."]
  }
}
```
- `success`: Boolean, always `false`.
- `message`: General error statement.
- `errors`: Key-value pairs where the key is the field name and the value is an array of error messages.

### 2.3 Forbidden / Unauthorized (401 / 403)
Used for authentication or authorization failures (Layer 1-4 Gate/Policy rejections).

```json
{
  "success": false,
  "message": "Unauthorized" // or "Forbidden"
}
```

### 2.4 Server Errors (500)
Used for unhandled exceptions or Database Constraints triggering.

```json
{
  "success": false,
  "message": "Terjadi kesalahan pada server",
  "error_code": "DB_CONSTRAINT_ERROR"
}
```

### 2.5 Pagination (200 OK)
Used for indexing endpoints returning lists of resources.

```json
{
  "success": true,
  "data": [
    { ... },
    { ... }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

## 3. Mandatory Implementation Guidelines
- **No Direct Eloquent Returns**: Controllers must NEVER return Eloquent Models directly. Use API Resources or custom response formatters that wrap data in the above structure.
- **Flat Endpoints**: Avoid deeply nested routes. Prefer `POST /api/assessment` over `POST /api/insiden/1/assessment`.
- **Consistent Naming**: Use `snake_case` for all keys in JSON payloads and responses, strictly mirroring the database column names when applicable.
