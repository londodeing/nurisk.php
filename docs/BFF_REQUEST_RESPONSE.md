# BFF Request/Response Audit

## Endpoint: GET /api/account/home

### Route Definition (current)
```php
Route::prefix('account')->group(function () {
    Route::get('home', [AccountHomeController::class, 'index']);
});
```

### How it should be defined (expected)
```php
Route::middleware('auth:sanctum')->prefix('account')->group(function () {
    Route::get('home', [AccountHomeController::class, 'index']);
});
```

OR for optional auth (guest mode support):
```php
Route::prefix('account')->group(function () {
    Route::get('home', [AccountHomeController::class, 'index']);
});
```
(Current setup IS optional auth — but this means no 401 enforcement)

---

## Existing BFF Endpoint: GET /api/profile

### Route Definition
```php
Route::prefix('profile')->group(function () {
    Route::get('/', [ProfileApiController::class, 'index']);
});
```

### Key Differences from account/home
| Aspect | /api/profile | /api/account/home |
|--------|-------------|-------------------|
| Auth middleware | None | None |
| Auth guard | `Auth::guard('sanctum')->user()` | `Auth::guard('sanctum')->user()` |
| Guest mode | Returns hardcoded guest data | Returns guest cards from builders |
| Auth user data | `$user->load(['profil', 'peran'])` | Uses model relationships directly |
| Mandate handling | Hardcoded position string | Reads from `jabatanAktif()` |
| Quick actions | Role-based with fallback | Via QuickActionService |
| Statistics | Role-based manual queries | Builder pattern |
| Tasks | DecisionQueueService | DecisionQueueService + direct penugasan |
| Approval | Hardcoded role check | ApprovalCardBuilder |
| Resources | Empty array (mock) | DB queries |

---

## Request/Response Examples

### Unauthenticated Request
```
GET /api/account/home
Headers: Accept: application/json
```

Response (200):
```json
{
  "success": true,
  "data": {
    "cards": [
      { "type": "guest", "title": "Akun Saya", "data": { "name": "Tamu", ... } },
      { "type": "statistics", "title": "Informasi Bencana", ... },
      { "type": "quick_actions", "title": "Aksi Cepat", ... },
      { "type": "guest_menu", "title": "Menu Publik & Informasi", ... }
    ]
  }
}
```

### Authenticated Request (PCNU role)
```
GET /api/account/home
Headers:
  Authorization: Bearer 1|valid-sanctum-token
  Accept: application/json
  X-Scope-Id: 1
  X-Scope-Type: pcnu
  X-Role: pcnu
```

Response (200):
```json
{
  "success": true,
  "data": {
    "cards": [
      { "type": "identity", "title": "Akun Saya", "data": { "name": "Nama User", ... } },
      { "type": "mandate", "title": "Mandat Aktif", "data": { "mandates": [...] } },
      { "type": "statistics", "title": "Ringkasan Hari Ini", "data": { "data": [...] } },
      { "type": "quick_actions", "title": "Aksi Cepat", "data": { "data": [...] } },
      { "type": "approvals", "title": "Persetujuan", "data": { "count": 5, ... } },
      ...
    ]
  }
}
```

### Authenticated Request (Relawan role)
```
GET /api/account/home
Headers:
  Authorization: Bearer 2|valid-sanctum-token
  X-Role: relawan
```

Response (200):
```json
{
  "success": true,
  "data": {
    "cards": [
      { "type": "identity", ... },
      { "type": "mandate", ... },
      { "type": "statistics", "data": { "data": [
        {"label": "Tugas", "value": "2"},
        {"label": "Misi", "value": "0"},
        {"label": "Point", "value": "0"}
      ]}},
      { "type": "quick_actions", "data": { "data": [
        {"title": "Check-in", "action_type": "join_mission"},
        {"title": "Check-out", "action_type": "upload_evidence"},
        {"title": "Update Progres", "action_type": "map"}
      ]}},
      { "type": "tasks", ... },
      { "type": "resources", ... },
      ...
    ]
  }
}
```
