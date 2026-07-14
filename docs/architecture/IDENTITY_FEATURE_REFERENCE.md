# Identity Feature Reference Implementation

## F2-004 Deliverable

Pipeline lengkap dari tap toggle sampai widget terupdate, termasuk optimistic update dan integration test.

---

## Flow

```
Tap Toggle ("Tersedia"/"Tidak Tersedia")
  │
  ▼
SduiContainer.onTap
  │  reads node.actions['on_tap'] (Map)
  ▼
RuntimeAction.fromJson()
  │  parses type, payload, confirm, on_success, optimistic
  ▼
ActionDispatcher.dispatch()
  │
  ├── Konfirmasi dialog (jika ada confirm block)
  │     user: "Tandai Tidak Tersedia?" → Ya
  │
  ├── Handler ditemukan: CustomActionHandler
  │
  ├── OPTIMISTIC: toggle UI segera
  │     patchProps('profile_toggle_text', {text: "Tidak Tersedia"})
  │     patchProps('profile_toggle_dot', {background: "text_muted"})
  │     patchProps('profile_toggle_container', {background: "text_muted"})
  │     setState() → rebuild widget tree
  │
  ├── HTTP POST /api/v1/profil/toggle-tersedia
  │     Header: Authorization: Bearer eyJ...
  │     Body: {id_pengguna: 123}
  │
  │   ┌─ Backend ──────────────────────────────────────┐
  │   │                                                 │
  │   │  ProfilController::toggleTersedia()             │
  │   │    ├── Auth::guard('sanctum')->user()           │
  │   │    ├── ToggleTersediaService::toggle($user)     │
  │   │    │     $user->update(['is_tersedia' => !$old])│
  │   │    │     Log::info('[ToggleTersedia] ...')      │
  │   │    └── response: {type: reload_scene,           │
  │   │         scene_id: akun,                         │
  │   │         is_tersedia_before: true,               │
  │   │         is_tersedia_after: false}               │
  │   └─────────────────────────────────────────────────┘
  │
  ├── Response sukses (200)
  │
  ├── on_success: ["type": "reload"]
  │
  │   ReloadHandler.execute()
  │     ├── context.refreshScene()
  │     └── AccountHomeNotifier.refresh()
  │           GET /api/account/home (with Bearer)
  │           AccountHomeData.fromJson()
  │           FlutterCertificationEngine.certify()
  │           state = AsyncValue.data(...)
  │
  └── Widget Re-render
        SduiScreen.didUpdateWidget()
          _rootNode = new widget.rootNode
          setState() → SduiRenderer → primitives
          "Tidak Tersedia" dengan dot abu-abu

  ── ON FAILURE ──
  catch (DioException)
    ├── context.revertOptimistic()
    │     _rootNode = widget.rootNode (kembali ke state server)
    │     setState()
    └── context.showToast("Terjadi kesalahan: ...")
```

---

## File Manifest

| Layer | File | Role |
|-------|------|------|
| Scene Composer | `app/Services/Sdui/Runtime/Sections/IdentitySection.php:98-135` | Build toggle container + action definition |
| Route | `routes/api.php:280` | `POST /api/v1/profil/toggle-tersedia` with `auth:sanctum` |
| Controller | `app/Http/Controllers/Api/ProfilController.php` | Entry point, auth check, delegasi ke service |
| Service | `app/Services/Profil/ToggleTersediaService.php` | Business logic: flip is_tersedia, log perubahan |
| Model | `app/Models/AuthUser.php:51,64` | `is_tersedia` column, cast to boolean |
| Migration | `database/migrations/2026_06_16_000002_create_auth_users_table.php:22` | `$table->boolean('is_tersedia')->default(true)` |
| Test | `tests/Feature/Profil/ToggleTersediaTest.php` | 4 tests, 19 assertions: auth, flip 1→0, flip 0→1, all roles |
| Action model | `mobile/app/lib/core/runtime/actions/runtime_action.dart` | `fromJson()`, `payload`, `onSuccess`, `optimistic` via payload |
| Dispatcher | `mobile/app/lib/core/runtime/actions/action_dispatcher.dart` | Confirm dialog, find handler, execute, chain on_success |
| Handler | `mobile/app/lib/core/runtime/actions/handlers/custom_action_handler.dart` | Optimistic apply, HTTP call, revert on failure |
| Screen | `mobile/app/lib/core/sdui/sdui_screen.dart` | StatefulWidget: `_rootNode` untuk optimistic patches |
| Context | `mobile/app/lib/core/runtime/actions/runtime_context.dart` | `applyOptimistic()`, `revertOptimistic()`, `refreshScene()` |
| Auth Dio | `mobile/app/lib/core/api/auth_api_client.dart` | Bearer token interceptor, scope/role headers |
| Refresh | `mobile/app/lib/features/account/presentation/screens/account_home_screen.dart:28` | `onRefresh: () => ref.read(...).refresh()` |
| Notifier | `mobile/app/lib/features/account/presentation/notifiers/account_home_provider.dart:218` | `refresh()` re-fetches `GET /api/account/home` |
| Node | `mobile/app/lib/core/sdui/sdui_node.dart` | `copyWith()`, `patchProps()` untuk optimistic tree mutation |

---

## Backend Layer Details

### Route (`routes/api.php:280`)

```php
Route::post('profil/toggle-tersedia', [ProfilController::class, 'toggleTersedia'])
    ->name('profil.toggle-tersedia');
```

Berada di dalam middleware group `auth:sanctum` + role filter.

### Controller (`app/Http/Controllers/Api/ProfilController.php`)

```php
class ProfilController extends Controller
{
    public function __construct(
        private ToggleTersediaService $toggleTersediaService
    ) {}

    public function toggleTersedia(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $newStatus = $this->toggleTersediaService->toggle($user);

        Log::info('[ProfilController] Bearer: ...', [
            'token' => substr($request->bearerToken() ?? 'NONE', 0, 20) . '...',
        ]);

        return response()->json([
            'type' => 'reload_scene',
            'scene_id' => 'akun',
            'is_tersedia_before' => !$newStatus,
            'is_tersedia_after' => $newStatus,
        ]);
    }
}
```

### Service (`app/Services/Profil/ToggleTersediaService.php`)

```php
class ToggleTersediaService
{
    public function toggle(AuthUser $user): bool
    {
        $newStatus = !$user->is_tersedia;
        $user->update(['is_tersedia' => $newStatus]);
        Log::info('[ToggleTersedia] ...', [...]);
        return $newStatus;
    }
}
```

### Response Format

Success (200):
```json
{
    "type": "reload_scene",
    "scene_id": "akun",
    "is_tersedia_before": true,
    "is_tersedia_after": false
}
```

Unauthenticated (401):
```json
{
    "success": false,
    "message": "Unauthorized"
}
```

---

## Action Contract (Backend → Flutter)

Action definition in `IdentitySection.php:105-119`:

```php
'on_tap' => [
    'type' => 'action',
    'action_type' => 'profil.toggle_tersedia',
    'endpoint' => '/api/v1/profil/toggle-tersedia',
    'method' => 'POST',
    'requires_auth' => true,
    'body' => ['id_pengguna' => $profil['id_pengguna']],
    'optimistic' => true,
    'optimistic_patches' => [
        'profile_toggle_dot' => ['background' => 'text_muted'],
        'profile_toggle_text' => ['text' => 'Tidak Tersedia'],
        'profile_toggle_container' => ['background' => 'text_muted'],
    ],
    'confirm' => [
        'title' => 'Tandai Tidak Tersedia?',
        'message' => 'Status Anda akan berubah...',
        'confirm_label' => 'Ya',
        'cancel_label' => 'Batal'
    ],
    'on_success' => ['type' => 'reload']
]
```

### Contract:

| Field | Required | Type | Description |
|-------|----------|------|-------------|
| `type` | ✅ | `"action"` | Selector untuk `CustomActionHandler` |
| `action_type` | ✅ | string | `profil.toggle_tersedia` |
| `endpoint` | ✅ | string | URL backend |
| `method` | ✅ | string | HTTP method |
| `requires_auth` | ✅ | bool | `true` = Bearer token disertakan |
| `body` | optional | object | Request body |
| `optimistic` | optional | bool | `true` = UI berubah sebelum HTTP |
| `optimistic_patches` | conditional | `Map<nodeId, props>` | Prop changes per node ID |
| `confirm` | optional | object | Konfirmasi dialog |
| `on_success` | optional | action | Chain action setelah sukses |

### Message flow for optimistic:

```
Flutter                          Backend
  │                                │
  ├── applyOptimistic(patches)     │
  │     setState → UI berubah      │
  │                                │
  ├── POST /profil/toggle-tersedia─┤
  │                                ├── toggle DB
  │                                ├── Log Bearer
  │   ←── 200 {reload_scene} ──────┤
  │                                │
  ├── reload → GET /account/home ──┤
  │   ←── Scene baru ─────────────┤
  │                                │
  └── Re-render dari server        │
      (confirm the optimistic      │
       change was correct)         │
```

---

## Flutter Layer Details

### Optimistic Update Pattern

1. `RuntimeContext` memiliki `onOptimisticApply` dan `onOptimisticRevert` callbacks
2. `SduiScreen` (StatefulWidget) mengimplementasikan callback:
   - `_applyOptimistic()` → `patchProps()` pada `_rootNode` → `setState()`
   - `_revertOptimistic()` → restore `_rootNode` dari `widget.rootNode`
3. `CustomActionHandler.execute()`:
   - Sebelum HTTP: `context.applyOptimistic(patches)`
   - Setelah HTTP sukses: return success → `on_success: reload` → scene baru dari server
   - Jika HTTP gagal: `context.revertOptimistic()` → UI kembali ke state server

```dart
// custom_action_handler.dart — optimistic section
if (optimistic) {
  final patches = /* parse dari payload */;
  if (patches.isNotEmpty) {
    await context.applyOptimistic(patches);
  }
}

try {
  final res = await context.httpClient.post(endpoint, data: body);
  if (res.statusCode == 200) return ActionResult.success();
  await context.revertOptimistic();
  throw Exception(...);
} catch (e) {
  await context.revertOptimistic();
  rethrow;
}
```

### onRefresh Wiring

Sebelum F2-004, `SduiScreen.onRefresh` tidak diisi → `context.refreshScene()` adalah no-op.

Sekarang di `AccountHomeScreen`:

```dart
SduiScreen(
  rootNode: data.root!,
  onRefresh: () => ref.read(accountHomeProvider.notifier).refresh(),
  ...
)
```

`refresh()` → `GET /api/account/home` → `AccountHomeData.fromJson()` → `CertificationEngine.certify()` → `state = AsyncValue.data(...)` → widget rebuild dengan scene baru.

---

## Test Matrix

| Test | File | Status | Deskripsi |
|------|------|--------|-----------|
| Unauthenticated | `ToggleTersediaTest.php:25` | ✅ Pass | POST tanpa auth → 401 |
| Flip 1→0 | `ToggleTersediaTest.php:31` | ✅ Pass | `is_tersedia` dari 1 ke 0, cek response + DB |
| Flip 0→1 | `ToggleTersediaTest.php:55` | ✅ Pass | `is_tersedia` dari 0 ke 1, cek response + DB |
| All roles | `ToggleTersediaTest.php:79` | ✅ Pass | super_admin, pwnu, pcnu, relawan, trc |
| Refresh DB sebelum/sesudah | `ToggleTersediaTest.php:44,68` | ✅ Pass | `$user->fresh()->is_tersedia` diverifikasi |
| Legacy action test | `AkunSceneComposerTest.php:353` | ✅ Pass | Old `/api/v1/action` route masih work |
| Logout sets false | `AkunSceneComposerTest.php:371` | ✅ Pass | Logout → `is_tersedia` = 0 |
| Route canonical | — | ✅ Done | `/incident/detail/{id}` → `/incident/{id}` |
| Auth pipeline | `auth_api_client.dart` | ✅ Integrated | Bearer token verified via log on server |

### Visual Test Matrix

| Test | Result |
|------|--------|
| Endpoint work | ✅ |
| Bearer terkirim | ✅ (logged on server) |
| DB berubah | ✅ (fresh() + assertEquals) |
| Reload wired | ✅ (onRefresh di SduiScreen) |
| Scene berubah | ✅ (reload → GET → cert → render) |
| UI berubah | ✅ (optimistic + reload) |
| Rollback (optimistic) | ✅ (catch → revertOptimistic) |

---

## Architecture Decisions

### Why not just use the existing `POST /api/v1/action`?

The old generic action dispatcher (`SduiActionController`) uses a `switch` statement for all action types. This creates a single controller with unbounded responsibility. For the reference implementation, we use a dedicated controller with constructor injection, a named service class, and a singular responsibility.

### Why `optimistic_patches` as nodeId → props map?

The SDUI tree is immutable (`SduiNode` has all `final` fields). Optimistic update modifies specific node props by node ID. The `SduiNode.patchProps()` method traverses the tree, finds the target node by ID, and returns a new tree with merged props. This avoids coupling the handler to tree structure.

### Why make `SduiScreen` a StatefulWidget?

`StatelessWidget` cannot hold mutable state. Optimistic update requires:
1. Holding a local `_rootNode` that differs from `widget.rootNode`
2. Calling `setState()` to trigger rebuild with patched tree
3. Reverting `_rootNode` to `widget.rootNode` on failure

---

## Pattern untuk Feature Lain

### Assignment (toggle status penugasan)

```php
'on_tap' => [
    'type' => 'action',
    'action_type' => 'penugasan.toggle_status',
    'endpoint' => '/api/v1/penugasan/toggle-status',
    'method' => 'POST',
    'requires_auth' => true,
    'body' => ['id_penugasan' => ...],
    'optimistic' => true,
    'optimistic_patches' => [
        'penugasan_status_text' => ['text' => 'Selesai'],
        'penugasan_status_badge' => ['background' => 'success'],
    ],
    'on_success' => ['type' => 'reload'],
]
```

### Setiap feature harus menyertakan:

1. **Route** → Controller → Service (bisa inject via constructor)
2. **Integration test** dengan `RefreshDatabase` + factory
3. **Action contract** dengan `endpoint`, `method`, `requires_auth`, `body`
4. **on_success: reload** untuk re-render scene
5. **optimistic** patches jika UX membutuhkan perubahan UI segera
6. **onRefresh** di `SduiScreen` (jika tidak diisi, reload adalah no-op)

