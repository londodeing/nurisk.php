# Atomic Fix Plan

> JANGAN kode. Hanya rencana.
> Setiap fix ≤ 30 menit.

---

## FIX 001 — Register `POST /api/auth/mandate` route

**File**: `routes/api.php`
**Change**: Add route for `AuthApiController::selectMandate`
**Line to add** (inside `Route::prefix('auth')` group, after line 165):
```php
Route::post('mandate', [\App\Http\Controllers\Api\AuthApiController::class, 'selectMandate'])->name('api.auth.mandate');
```
**Time**: 2 minutes

---

## FIX 002 — Include mandates array in login response

**File**: `app/Http/Controllers/Api/Auth/AuthenticationApiController.php`
**Change**: After `$user->load(['profil', 'peran'])`, add mandate resolution:
```php
$mandates = $user->jabatanAktif()->with('posisi')->get()->map(fn($j) => [
    'id' => $j->id_pengguna_jabatan,
    'role' => $j->posisi?->nama_jabatan ?? 'Anggota',
    'territory' => $j->tipe_lingkup . ' ' . $j->id_lingkup,
])->values();
```
Include `'mandates' => $mandates` in the response `'data'` key.
**Note**: This adds real mandate data from the `pengguna_jabatan` table.
**Time**: 15 minutes

---

## FIX 003 — Change MandatePickerScreen to use authApiClientProvider

**File**: `mobile/app/lib/features/auth/presentation/screens/mandate_picker_screen.dart:30`
**Change**: Replace `publicApiClientProvider` with `authApiClientProvider`:
```dart
// Before:
final dio = ref.read(publicApiClientProvider);
// After:
final dio = ref.read(authApiClientProvider);
```
**Time**: 2 minutes

---

## FIX 004 — Navigate to mandate picker after login

**File**: `mobile/app/lib/features/auth/presentation/screens/login_screen.dart`
**Change**: Replace `goHome()` with conditional navigation:
- If `data['data']['mandates']` exists and has items → navigate to mandate picker with mandates list
- Else if `role` is set → navigate to executive (`goExecutive()`)
- Else → `goHome()`
**Time**: 15 minutes

---

## FIX 005 — Add auth:sanctum middleware to account/home route

**File**: `routes/api.php`
**Change**: Add `auth:sanctum` middleware to the account prefix group:
```php
Route::middleware('auth:sanctum')->prefix('account')->group(function () {
    Route::get('home', [AccountHomeController::class, 'index']);
});
```
**Time**: 2 minutes

---

## FIX 006 — Update AccountHomeController to use $request->user()

**File**: `app/Http/Controllers/Api/AccountHomeController.php`
**Change**: After adding `auth:sanctum` middleware, use `$request->user()` instead of `Auth::guard('sanctum')->user()`:
```php
public function index(Request $request): JsonResponse
{
    $user = $request->user();
    $cards = $this->dashboardService->getCards($user);
    ...
}
```
Also remove unused `Auth` import.
**Time**: 5 minutes

---

## FIX 007 — Add `/auth/mandate` to RoutePaths auth prefixes

**File**: `mobile/app/lib/core/router/app_router.dart`
**Change**: Add mandate to auth prefixes:
```dart
static const _authPrefixes = ['/auth/login', '/auth/register', '/auth/mandate'];
```
**Time**: 2 minutes

---

## FIX 008 — Make accountHomeProvider watch authStateProvider

**File**: `mobile/app/lib/features/account/presentation/notifiers/account_home_provider.dart`
**Change**: In `build()`, watch `authStateProvider` to trigger rebuild on auth changes:
```dart
Future<AccountHomeData> build() async {
  ref.watch(authStateProvider);  // Rebuild when auth state changes
  final repository = ref.read(accountRepositoryProvider);
  final dio = ref.read(authApiClientProvider);
  return repository.getAccountHome(dio: dio);
}
```
**Time**: 3 minutes

---

## FIX 009 — Fix Navbar Account label

Already done in Phase 2.2 — label says "Akun". Verify alignment.
**Time**: 1 minute (verification)

---

## FIX 010 — Add pull-to-refresh to AccountHomeScreen

**File**: `mobile/app/lib/features/account/presentation/screens/account_home_screen.dart`
**Change**: Wrap `SingleChildScrollView` in `RefreshIndicator`:
```dart
RefreshIndicator(
  onRefresh: () => ref.read(accountHomeProvider.notifier).refresh(),
  child: SingleChildScrollView(...),
)
```
**Time**: 5 minutes

---

## FIX 011 — Fix SplashScreen to navigate to mandate picker if mandates exist

**File**: `mobile/app/lib/core/splash/splash_screen.dart`
**Change**: In `_tryNavigate()`, check if user has multiple mandates. If yes, navigate to mandate picker instead of executive.
**Time**: 10 minutes

---

## Summary

| Fix | Area | Time | Priority |
|-----|------|------|----------|
| 001 | Backend route | 2m | 🔴 CRITICAL |
| 002 | Backend login response | 15m | 🔴 CRITICAL |
| 003 | Flutter Dio client | 2m | 🔴 CRITICAL |
| 004 | Flutter post-login nav | 15m | 🔴 CRITICAL |
| 005 | Backend route middleware | 2m | 🔴 CRITICAL |
| 006 | Backend controller | 5m | 🔴 CRITICAL |
| 007 | Flutter route guard | 2m | 🟡 HIGH |
| 008 | Flutter Riverpod | 3m | 🟡 HIGH |
| 009 | Flutter UI label | 1m | 🟢 LOW |
| 010 | Flutter UX | 5m | 🟢 LOW |
| 011 | Flutter splash | 10m | 🟡 HIGH |

**Total critical fixes**: 6 (FIX 001-006) — estimated 41 minutes
**Total high fixes**: 2 (FIX 007-008, 011) — estimated 15 minutes
**Total all fixes**: ~62 minutes
