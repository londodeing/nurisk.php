# FLUTTER NAVIGATION CONSTITUTION

> Konstitusi navigasi NURISK — berlaku untuk seluruh mobile codebase.

---

## I. Navigation Philosophy

### 1.1 Persistent Bottom Navigation

NURISK menggunakan **Persistent Bottom Navigation**, bukan Page Replacement Navigation.

```
BENAR:   StatefulShellRoute.indexedStack + goBranch()
SALAH:   ShellRoute + context.go()
```

### 1.2 Semua Tab adalah Root

Setiap tab bottom navigation adalah root destination. Masing-masing:

- Memiliki **navigator sendiri**
- Memiliki **history sendiri**
- Tidak terpengaruh navigasi tab lain
- Tidak di-recycle saat tab switch

### 1.3 Satu Aplikasi

Setelah login, user tetap berada di aplikasi yang sama. Tidak ada "aplikasi kedua" untuk pengguna terautentikasi.

---

## II. Route Hierarchy

```
/splash                          → SplashScreen (tanpa bottom nav)
/auth/login                      → LoginScreen (tanpa bottom nav)
/auth/register                   → RegisterScreen (tanpa bottom nav)
/auth/mandate                    → MandatePickerScreen (tanpa bottom nav)
/g/executive                     → ExecutiveDashboardScreen (tanpa bottom nav)

# Deep Link Routes
/incident/:id                    → IncidentDetailScreen
/report/:trackingCode            → ReportTrackingScreen
/governance/approval/:id         → ApprovalDetailScreen

# Bottom Navigation Shell (StatefulShellRoute)
/p/home                          → PublicDashboardScreen
/p/map                           → CopMapScreen
/p/report                        → ReportWizardScreen
/p/resource                      → ResourceScreen
/p/profile                       → ProfileScreen
```

### 2.1 Route Constants

Semua route path didefinisikan sebagai konstanta:

```dart
class RoutePaths {
  static const splash = '/splash';
  static const login = '/auth/login';
  static const register = '/auth/register';
  static const mandate = '/auth/mandate';
  static const executive = '/g/executive';
  static const home = '/p/home';
  static const map = '/p/map';
  static const report = '/p/report';
  static const resource = '/p/resource';
  static const profile = '/p/profile';
  static const incidentDetail = '/incident/:id';
  static const reportTracking = '/report/:trackingCode';
  static const approvalDetail = '/governance/approval/:id';
}
```

---

## III. Bottom Navigation Rules

### 3.1 Tab Switching

```
Home  ←→  Map  ←→  Lapor  ←→  Resource  ←→  Profile
```

Tab switching menggunakan `StatefulNavigationShell.goBranch(index)`.

**DILARANG**: `context.go()`, `pushReplacement()`, `pushAndRemoveUntil()` untuk tab switching.

### 3.2 Back dari tab non-Home

```
[Tab non-Home] → Back → [Tab Home]
```

Back dari tab selain Home harus kembali ke tab Home.

### 3.3 Back dari tab Home

```
[Home] → Back → Snackbar "Tekan sekali lagi untuk keluar"
[Home] → Back lagi (< 2 detik) → Exit aplikasi
```

### 3.4 Back dengan nested navigation

```
[Tab] → [Push sub-route] → Back → [Pop sub-route]
```

Jika tab memiliki sub-route (nested navigation), Back akan mempop sub-route terlebih dahulu, bukan pindah tab.

---

## IV. Nested Navigation

Setiap tab memiliki navigator sendiri melalui `StatefulShellBranch`.

### 4.1 Home Tab Nested Routes

```
/p/home                     → Dashboard
/p/home/incident/:id        → Incident Detail
/p/home/incident/:id/assessment → Assessment
```

### 4.2 Map Tab Nested Routes

```
/p/map                      → Map
/p/map/incident/:id         → Incident Popup/Timeline
/p/map/incident/:id/photo   → Photo Detail
```

### 4.3 Report Tab Nested Routes

```
/p/report                   → Wizard Step 1
/p/report/tracking/:code    → Tracking Screen
```

---

## V. Navigation Stack Preservation

### 5.1 Tab State Preservation

Semua tab menggunakan `IndexedStack` (built into `StatefulShellRoute.indexedStack`):

- Widget tidak di-dispose saat tab switch
- State tetap alive
- Tidak ada reload/recreate
- Tidak ada fetch ulang

### 5.2 Map State

Map harus mempertahankan:

- Camera position
- Zoom level  
- Active layers
- Markers
- Selected objects

### 5.3 Report Wizard State

Report wizard harus mempertahankan:

- Current step
- All form data
- Selected files
- GPS location

---

## VI. State Restoration

### 6.1 Scroll Positions

Gunakan `PageStorageKey` pada semua `ListView`, `GridView`, `SingleChildScrollView`.

### 6.2 Form State

Gunakan `AutomaticKeepAliveClientMixin` atau simpan di Riverpod provider.

### 6.3 Required Restoration Points

| Element | Method |
|---------|--------|
| Scroll position | `PageStorageKey` |
| Map camera | Riverpod `mapStateNotifier` |
| Map layers | Riverpod `mapLayerNotifier` |
| Report draft | Riverpod `laporanProvider` |
| Selected filter | Riverpod `operationalFilterProvider` |
| Active mandate | Riverpod `authStateProvider` |

---

## VII. Back Button Behavior

### 7.1 PopScope (wajib)

Gunakan `PopScope` dengan `canPop: false` di shell wrapper.

```dart
PopScope(
  canPop: false,
  onPopInvoked: (didPop) {
    if (didPop) return;
    // Handle back logic
  },
)
```

### 7.2 Android Predictive Back

Kompatibel Android 14+. `PopScope` secara native mendukung predictive back gesture.

**JANGAN** gunakan `WillPopScope` (deprecated).

### 7.3 Back Decision Tree

```
Back Pressed
├── Current tab has sub-route? → Pop sub-route
├── Current tab != Home? → goBranch(Home)
└── Current tab == Home?
    ├── Last back < 2 detik? → SystemNavigator.pop()
    └── Last back >= 2 detik? → Show snackbar, record timestamp
```

---

## VIII. Authentication Flow

### 8.1 Login Flow

```
Public Screen → [Push] → Login Screen → Success
  ├── User has mandates → MandatePickerScreen → Governance
  └── User no mandates → goBranch(Home)
```

### 8.2 Logout Flow

```
[Any Screen] → Logout → go(Home) → Dashboard Public
```

Logout tidak boleh ke Splash atau Login screen.

### 8.3 Auth Guard

Governance routes memerlukan autentikasi. Implementasi melalui GoRouter `redirect`.

---

## IX. Deep Link Flow

### 9.1 Supported Schemes

```
nurisk://incident/123
nurisk://map
nurisk://report/TRX123
nurisk://governance/approval/55
```

### 9.2 Deep Link Routing

Deep link routes didefinisikan di root GoRouter (outside ShellRoute):

```dart
GoRoute(path: '/incident/:id', ...)
GoRoute(path: '/map', ...)  // Redirect to /p/map
GoRoute(path: '/report/:trackingCode', ...)
GoRoute(path: '/governance/approval/:id', ...)
```

---

## X. Technical Requirements

### 10.1 Wajib Digunakan

- `GoRouter` 17.x — routing framework
- `StatefulShellRoute.indexedStack` — persistent bottom nav
- `StatefulNavigationShell.goBranch()` — tab switching
- `PopScope` — back button handling
- `PageStorageKey` — scroll preservation
- `NavigatorKey` per tab (opsional, built-in StatefulShellBranch)

### 10.2 DILARANG Digunakan

- ❌ `Navigator.pushReplacement()` untuk tab switching
- ❌ `context.go()` untuk tab switching
- ❌ `pushAndRemoveUntil()` untuk tab switching
- ❌ `WillPopScope` (gunakan PopScope)
- ❌ `Navigator.popUntil()` untuk reset stack
- ❌ Reset stack saat tab switch

### 10.3 Dependency

```yaml
dependencies:
  go_router: ^17.3.0
  flutter_riverpod: ^3.3.2
```

---

## XI. Governance & Enforcement

### 11.1 Code Review Checklist

Setiap PR dengan perubahan navigasi WAJIB dicek:

- [ ] Tidak ada `context.go()` untuk tab switching
- [ ] Tidak ada `pushReplacement`
- [ ] `PopScope` terimplementasi
- [ ] State preservation berfungsi
- [ ] Back button behavior sesuai decision tree
- [ ] Tidak ada navigator baru tanpa key

### 11.2 Integration Test Coverage

Minimal test:

- Tab switching preserves state
- Back from tab → Home
- Back from Home → exit confirmation
- Logout → Dashboard Public
- Deep link routing
