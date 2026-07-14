# Account Home End-to-End Trace

## Request Path

```
Flutter AccountHomeScreen
  → accountHomeProvider.build()
    → accountRepositoryProvider.getAccountHome(dio)
      → accountRemoteDatasource.getAccountHome(dio)
        → dio.get('account/home')
          → authApiClientProvider interceptor adds Bearer token
            → HTTP GET /api/account/home
              → AccountHomeController.index()
                → Auth::guard('sanctum')->user()
                → AccountDashboardService.getCards($user)
                  → Builder classes
                → JSON response
              ← 200 { success: true, data: { cards: [...] } }
            ← Response parsed
          ← AccountHomeData.fromJson()
        ← AccountHomeData
      ← AccountHomeData
    ← AsyncValue.data(AccountHomeData)
  ← AccountCards rendered via CardRenderer
```

## Full Request Trace

### Request
```
GET http://10.0.2.2:8000/api/account/home
Headers:
  Authorization: Bearer 1|abc123...
  Accept: application/json
  Content-Type: application/json
  X-Scope-Id: 1
  X-Scope-Type: pcnu
  X-Role: pcnu
```

### Backend Controller Execution
```
1. AccountHomeController::index()
2.   Auth::guard('sanctum')->user()
3.     → Sanctum reads Authorization header
4.     → Validates token against personal_access_tokens table
5.     → Returns AuthUser model (or null)
6.   AccountDashboardService::getCards($user)
7.     → $user is AuthUser (NOT null → authenticated flow)
8.     → IdentityCardBuilder::build($user)
9.     → MandateCardBuilder::build($user)
10.    → StatisticsCardBuilder::build($user)
11.    → QuickActionCardBuilder::build($user)
12.    → TaskCardBuilder::build($user)
13.    → ApprovalCardBuilder::build($user)
14.    → ResourceCardBuilder::build($user)
15.    → ActivityCardBuilder::build($user)
16.    → NotificationCardBuilder::build($user)
17.    → SettingsCardBuilder::build($user)
18.  Returns JSON with 11 cards
```

### Response
```json
{
  "success": true,
  "data": {
    "cards": [
      { "type": "identity", "title": "Akun Saya", "data": { "name": "...", ... } },
      { "type": "mandate", "title": "Mandat Aktif", "data": { "mandates": [...] } },
      { "type": "statistics", "title": "Ringkasan Hari Ini", "columns": 3, "data": { "data": [...] } },
      { "type": "quick_actions", "title": "Aksi Cepat", "columns": 3, "data": { "data": [...] } },
      { "type": "tasks", "title": "Tugas Aktif", "data": { "data": [...] } },
      { "type": "approvals", "title": "Persetujuan", "data": { ... } },
      { "type": "resources", "title": "Sumber Daya", "columns": 2, "data": { "data": [...] } },
      { "type": "activities", "title": "Aktivitas Terbaru", "data": { "data": [...] } },
      { "type": "notifications", "title": "Notifikasi", "data": { ... } },
      { "type": "settings", "title": "Pengaturan", "data": { ... } }
    ]
  }
}
```

### Flutter Rendering
```
AccountHomeScreen
  → accountAsync.when(
      data: (data) → SingleChildScrollView
        → Column
          → data.cards.map(card → CardRenderer(card))
            → switch(card.type):
                'identity'    → IdentityCard
                'mandate'     → MandateCard
                'statistics'  → StatisticsCard
                'quick_actions' → QuickActionsCard
                'tasks'       → TasksCard
                'approvals'   → ApprovalsCard
                'resources'   → ResourcesCard
                'activities'  → ActivitiesCard
                'notifications' → NotificationsCard
                'settings'    → SettingsCard
      loading: → CircularProgressIndicator
      error: → Error icon + DioExceptionMapper message + Retry button
    )
```

## Failure Modes

### Mode A: Token not sent
- `authApiClientProvider` interceptor fails to read `authStateProvider`
- Request sent without `Authorization` header
- `Auth::guard('sanctum')->user()` returns null
- Backend returns **guest cards** (guest identity + guest menu + public stats)
- Flutter renders guest UI even though user IS logged in

### Mode B: Sanctum token invalid
- Token was revoked (e.g., by `AuthUserObserver`)
- `Auth::guard('sanctum')->user()` returns null
- Same as Mode A — guest cards returned

### Mode C: Account route has no auth middleware
- Request reaches controller regardless of authentication
- Backend never returns 401
- Silent fallback to guest cards
- User sees "Sesi Belum Aktif" despite being logged in
