# Execution Path Audit — Tab Akun

## Ringkasan

**Kesimpulan utama:** Flutter Account tab **SUDAH** menggunakan Runtime NSS pipeline penuh — tidak ada legacy widget path. Namun pengguna melihat layout yang tampak seperti "widget custom" karena **backend API endpoint `/api/account/home`** mengembalikan response yang dirender oleh primitives dengan props yang meniru tampilan legacy.

---

## Diagram Jalur Eksekusi

```
Tap Tab "Akun" (index 4)
  │
  ▼
PublicBottomNav._onItemTapped(4)                              ✅ active
  └─ navigationShell.goBranch(4, initialLocation: true)
  │
  ▼
GoRouter resolves /p/profile                                   ✅ active
  └─ app_router.dart:244
       └─ builder: (context, state) => const AccountHomeScreen()
  │
  ▼
AccountHomeScreen (ConsumerWidget) — build()                   ✅ active
  │
  ├─ ref.watch(accountHomeProvider)  ← AsyncNotifierProvider
  │   │
  │   ▼
  │   AccountHomeNotifier.build()                              ✅ active
  │     │
  │     ├─ ref.watch(authStateProvider) → AuthState
  │     │
  │     ├─ [GUEST] if !auth.isAuthenticated:
  │     │   └─ _guestData()
  │     │       └─ AccountHomeData(root: hardcoded SduiNode)
  │     │          ─────────────────────────────────
  │     │          NO HTTP CALL
  │     │          NO CERTIFICATION ENGINE
  │     │          LANGSUNG KE SduiScreen
  │     │          ─────────────────────────────────
  │     │
  │     └─ [AUTH] if auth.isAuthenticated:
  │         │
  │         ├─ AccountRepositoryImpl                          ✅ active
  │         │   └─ AccountRemoteDatasource
  │         │
  │         ├─ Dio (authApiClientProvider)                     ✅ active
  │         │   ├─ baseUrl: dotenv.env['API_BASE_URL']
  │         │   ├─ +Interceptor: Bearer <token>
  │         │   ├─ +Interceptor: X-Scope-Id
  │         │   └─ +Interceptor: X-Role
  │         │
  │         └─ dio.get('account/home')
  │              → HTTP GET {baseUrl}account/home
  │              │
  │              ▼
  │              [200 OK] → AccountHomeData.fromJson(res.data)
  │              │
  │              ▼
  │              FlutterCertificationEngine.certify(json)       ✅ active
  │                │
  │                ├─ 1. EnvelopeValidator ✅
  │                ├─ 2. SchemaValidator   ✅
  │                ├─ 3. SduiNode.fromJson  ✅ (recursive parse)
  │                ├─ 4. RegistryValidator  ✅ (walk tree, check types)
  │                ├─ 5. ActionValidator    ✅
  │                ├─ 6. StateValidator     ✅
  │                └─ 7. PropertyValidator  ✅
  │
  ▼
AccountHomeScreen build()
  │
  ├─ [loading] → CircularProgressIndicator                    ✅
  ├─ [error]   → Error message + Coba Lagi                    ✅
  └─ [data] →
       │
       ├─ [!isCertified] → CertificationErrorWidget           ✅
       └─ [isCertified]  → SduiScreen                          ✅
            │
            ▼
            SduiScreen.build()
              │
              ├─ RuntimeActionDispatcher                       ✅
              │   ├─ NavigateHandler ✅
              │   ├─ SubmitHandler   ✅
              │   ├─ ReloadHandler   ✅
              │   ├─ ToastHandler    ✅
              │   └─ CustomActionHandler ✅
              │
              ├─ ActionDispatcherScope (InheritedWidget)       ✅
              │
              └─ Scaffold
                   └─ SafeArea
                       └─ Column
                           └─ Expanded
                               └─ SduiRenderer(node: rootNode)  ✅
                                    │
                                    ▼
                                    SduiRegistry.getBuilder(type)
                                      │
                                      ├─ [found] → builder(node)
                                      │   └─ RuntimeStateWidget(
                                      │        state: node.state,
                                      │        child: SduiComponent
                                      │      )
                                      │
                                      └─ [not found] → SduiUnknownComponent
```

---

## Temuan Kunci

### 1. ✅ Tidak ada legacy widget path

Account tab SUDAH 100% melalui Runtime NSS:
- `AccountHomeScreen` → `SduiScreen` → `SduiRenderer` → `SduiRegistry` → primitives
- Tidak ada cabang ke legacy widget factory / DTO-to-Widget converter
- Semua widget adalah `SduiComponent` (extends `ConsumerWidget`)

### 2. ⚠ Dua jalur berbeda berdasarkan auth state

| Jalur | Auth | Sumber Data | Cert Engine | HTTP |
|-------|------|-------------|-------------|------|
| Guest | ❌ | `_guestData()` hardcoded | ❌ | ❌ |
| Auth  | ✅ | Backend API `account/home` | ✅ | ✅ |

### 3. ⚠ Guest path melewati certification engine

`_guestData()` mengembalikan `AccountHomeData` dengan `root` langsung di-set. Karena `root != null`, `isCertified` selalu `true`. Tidak ada validasi oleh `FlutterCertificationEngine`. Ini sengaja (data hardcoded, trusted) tapi perlu dicatat.

### 4. ❓ Kenapa layout terlihat seperti "widget custom"?

Meskipun jalur render SUDAH melalui `SduiRenderer` → primitives, backend API endpoint `GET /api/account/home` mengembalikan response dengan struktur primitives yang **meniru** layout lama. Artinya backend serializer memproduksi `Container`/`Column`/`Row`/`Text`/`Icon` dengan props yang menghasilkan tampilan identik dengan legacy. Ini BUKAN masalah Flutter — ini masalah **backend output**.

### 5. ✅ ActionDispatcher terpasang

`SduiScreen` membuat `RuntimeActionDispatcher` dengan 5 handler terdaftar. Actions dari API (seperti `navigate`, `submit`, `reload`, `toast`) akan diproses. Jika tombol "Tidak Tersedia" tidak merespon, kemungkinan:
- Backend tidak mengirimkan `actions` pada node tersebut
- Action type tidak dikenali (tidak ada di 5 handler)

### 6. 🔄 Login error terpisah

Error "Terjadi kesalahan sistem atau kredensial salah" terjadi di `LoginScreen._login()` catch block — artinya DioException (kemungkinan network). Ini TIDAK terkait dengan jalur render Account tab.

---

## Daftar File dalam Execution Path

| # | File | Role | Status |
|---|------|------|--------|
| 1 | `app_router.dart:244` | Route `/p/profile` → `AccountHomeScreen` | ✅ active |
| 2 | `public_bottom_nav.dart` | Tab index 4 = Akun | ✅ active |
| 3 | `account_home_screen.dart` | `ConsumerWidget`, watch `accountHomeProvider` | ✅ active |
| 4 | `account_home_provider.dart` | `AccountHomeNotifier`, auth gate | ✅ active |
| 5 | `account_repository.dart` | `AccountRepositoryImpl` | ✅ active |
| 6 | `account_remote_datasource.dart` | `dio.get('account/home')` | ✅ active |
| 7 | `account_home_data.dart` | `fromJson()` → certification engine | ✅ active |
| 8 | `auth_state_provider.dart` | `AuthStateNotifier`, secure storage | ✅ active |
| 9 | `auth_api_client.dart` | Dio + Bearer + scope headers | ✅ active |
| 10 | `flutter_certification_engine.dart` | 7-step certification pipeline | ✅ active |
| 11 | `sdui_screen.dart` | Scaffold + ActionDispatcher + SduiRenderer | ✅ active |
| 12 | `sdui_renderer.dart` | Registry lookup + RuntimeStateWidget | ✅ active |
| 13 | `sdui_registry.dart` | Singleton: type → builder | ✅ active |
| 14 | `sdui_registry_initializer.dart` | 24 primitives registered | ✅ active |
| 15 | `sdui_node.dart` | Recursive JSON → SduiNode | ✅ active |
| 16 | `runtime_state_widget.dart` | Visible/Loading/Enabled guards | ✅ active |
| 17 | `action_dispatcher.dart` | Dispatch → 5 handlers | ✅ active |
| 18 | `sdui_nss_utils.dart` | Padding/radius/color parsing | ✅ active |
| 19 | `runtime_initializer.dart` | `RuntimeServices` init | ✅ active |
| 20 | `runtime_state.dart` | `RuntimeStateNotifier` | ✅ active |
| 21 | `navigation_service.dart` | `NavigationService` (GoRouter wrapper) | ✅ active |
| 22 | `login_screen.dart` | Login form (error terpisah) | ✅ active |

---

## Kesimpulan

**Flutter Account tab SUDAH runtime penuh.** Tidak ada legacy widget path. Namun:

1. **Backend API** `GET /api/account/home` perlu diaudit — response-nya yang menentukan layout
2. **Login error** adalah masalah terpisah (network/DioException) — tidak terkait render path
3. **Guest path** melewati certification engine — perlu dipertimbangkan apakah akan ditambahkan
