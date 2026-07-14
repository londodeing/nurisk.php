# SPRINT QA PLAN — NURISK Mobile

> **Runtime Platform v1.0 — Architecture Freeze (QA-F0..F2 ✅ LOCKED)**
>
> NURISK adalah **Disaster Command Center**, bukan Flutter Framework.
> Runtime **cukup** — prioritas berikutnya: **selesaikan seluruh domain bisnis sesuai PRD**.

---

## EXECUTION ORDER

```
QA-F0 ✅ LOCKED (Runtime Foundation)
   ↓
QA-F1 ✅ LOCKED (Navigation & Lifecycle)
   ↓
QA-F2 ✅ LOCKED (Native Plugin + HTTP Elimination)
   ↓
QA-IF1 — Integration Freeze (validasi, bukan coding)
   ↓
P2.1 — Dashboard Public Completion
P2.2 — COP Completion
P2.3 — Report Domain Completion
P2.4 — Tracking Completion
P2.5 — News + Resource
P2.6 — Guest Profile
   ↓
AUTH-1 — Authentication Domain (Login, Logout, Refresh Token, OTP, PIN, Session, Remember Me)
AUTH-2 — Mandate Domain (Mandate Picker, Role, Permission, Policy)
   ↓
ROLE-1 — Role Engine (TRC, Ketua, Sekretaris, Operator, Relawan, Guest)
   ↓
GOV-1 — Governance (Draft, Approval, Signature, Execution, Timeline, Notification)
   ↓
VOL-1 — Volunteer
LOG-1 — Logistic
   ↓
QA-F3 — Offline & Cache Layer
QA-F4 — Performance & Battery
QA-F5 — Crash Analytics & Production Hardening
   ↓
RC1 — Release Candidate
```

---

## QA-F0: Runtime Foundation

**Status**: ✅ LOCKED (9.5/10)
**Goal**: Build Application Runtime Layer — 8 services yang menjadi fondasi seluruh aplikasi.
**Dependency**: None

### Deliverables

| Service | File | Status |
|---------|------|--------|
| `RuntimeLogger` — structured logging with metadata | `core/diagnostics/runtime_logger.dart` | ✅ |
| `ErrorBoundary` — FlutterError + PlatformDispatcher + Zone | `core/runtime/error_boundary.dart` | ✅ |
| `PermissionService` — unified permission request/check/openSettings | `core/services/permission_service.dart` | ✅ |
| `AppLifecycleService` — single WidgetsBindingObserver, observer pattern | `core/runtime/app_lifecycle_service.dart` | ✅ |
| `NavigationService` — centralized go/push/pop, deep link, auth redirect | `core/services/navigation_service.dart` | ✅ |
| `RuntimeState` — Riverpod state for runtime | `core/runtime/runtime_state.dart` | ✅ |
| `RuntimeInitializer` — 3-phase bootstrap | `core/runtime/runtime_initializer.dart` | ✅ |
| `main.dart` rewrite — ≈89 lines | `main.dart` | ✅ |

---

## QA-F1: Navigation & Lifecycle Stability

**Status**: ✅ LOCKED (9.8/10 F1.0 + 10/10 F1.5)
**Goal**: Replace scattered navigation calls with NavigationService. Add lifecycle awareness.
**Dependency**: QA-F0

### Deliverables

| Task | Outcome | Status |
|------|---------|--------|
| 12 direct navigation calls → NavigationService | login, register, mandate, profile, splash, bottom nav | ✅ |
| 13 mounted violations fixed | report_wizard, register, pin_verification, layer_control | ✅ |
| 14 hardcoded route strings → RoutePaths constants | public_bottom_nav, profile_screen | ✅ |
| Navigation stack assessment | Guest flow correct, Login flow correct, Executive→Back→Dashboard | ✅ |
| Session recovery | SplashScreen waits auth, navigates to executive or home | ✅ |
| Navigation analytics | NavigationAnalyticsObserver logs didPush/didPop/didReplace | ✅ |
| Deep link | Route names, `nurisk://` intent filter, singleTask | ✅ |
| Navigation restore | restorationScopeId on GoRouter + MaterialApp.router | ✅ |
| Route guard | GoRouter redirect, public/auth/protected route sets | ✅ |

---

## QA-F2: Native Plugin Integration

**Status**: ✅ LOCKED
**Goal**: All native plugins accessed via Platform Services (MediaService, GeoService, PermissionService), not from UI.
**Dependency**: QA-F0

### What Was Built

| ID | Deliverable | Key Outcomes |
|----|------------|--------------|
| F2-01 | `PermissionService` — hardened for iOS limited/restricted, mic+bluetooth | Typed `PermissionResult` |
| F2-02 | `MediaService` — camera + gallery, typed `MediaResult` | 20MB limit, `preferredCameraDevice` |
| F2-03 | `GeoService` — GPS with typed `GeoResult` | Mock detection, accuracy <100m, timeout |
| F2-04 | Plugin Isolation | Zero `image_picker`/`geolocator`/`permission_handler` in `features/` |
| F2-05 | Typed Result pattern | Deterministic: `if(result.isCancelled){...}` |
| F2-06 | Mounted audit — 13 violations | All `await+setState` guarded with `if (!mounted) return` |
| F2-07 | Legacy HTTP Elimination | 0 `package:http` in `lib/`, 100% Dio |

---

## QA-IF1: Integration Freeze

**Status**: 🟡 ACTIVE
**Goal**: Bukan sprint coding — sprint validasi. Seluruh domain diuji sebagai satu aplikasi.
**Dependency**: QA-F0..F2 ✅ LOCKED

### Gates

| Gate | Flow | Acceptance |
|------|------|------------|
| Gate 1 — Guest | Install APK → Splash → Dashboard → Map → Report → Tracking → Profile | ✅ Lulus |
| Gate 2 — Auth | Login → Mandate → Executive Dashboard → Logout → Guest | ✅ Lulus |
| Gate 3 — Report | GPS mati → Camera denied → Gallery cancel → Retry → Submit | ✅ No crash |
| Gate 4 — COP | Map → Layer → Legend → Filter → Marker → Bottom Sheet → Timeline → Back | ✅ Lulus |
| Gate 5 — Lifecycle | Background → Foreground → Rotate → Background → Kill → Launch | ✅ State preserved |
| Gate 6 — Low-end | Android 10-11, RAM 2-3 GB, CPU SD 450/460, storage near full | ✅ No ANR |

### Prerequisites
- `flutter analyze` = **0 error** ✅
- Architecture Review = **LOCKED/FROZEN** ✅

---

## PUBLIC DOMAIN COMPLETION

### P2.1 — Dashboard Public

**Goal**: Semua KPI dari Read Model, cache, refresh, skeleton, retry, error boundary, empty state.

| Task | Effort |
|------|--------|
| Audit semua KPI — pastikan berasal dari Read Model, bukan hardcoded | 2h |
| Cache layer untuk data dashboard (TTL) | 2h |
| Pull-to-refresh + loading skeleton | 2h |
| Retry on failure + error boundary per KPI card | 2h |
| Empty state untuk setiap widget | 1h |

### P2.2 — COP Completion

**Goal**: COP bukan sekadar renderer — operational workflow, mission tracking, live resource, dispatch, tactical overlay, animation, replay.

| Task | Effort |
|------|--------|
| Mission tracking — create, update, status timeline | 4h |
| Live resource display — personel, unit, asset di peta | 3h |
| Dispatch workflow — assign, acknowledge, complete | 4h |
| Tactical overlay — symbols, zones, labels | 3h |
| Animation — marker movement, status transitions | 2h |
| Replay — time slider untuk melihat history operasi | 3h |

### P2.3 — Report Domain Completion

**Goal**: Report Wizard benar-benar production-ready.

| Task | Effort |
|------|--------|
| Audit GPS mati → graceful error, retry | 2h |
| Audit GPS lambat (timeout > 30s) → fallback | 1h |
| Audit GPS mock → warning + reject untuk TRC | 1h |
| Audit Camera cancel → return to wizard, no crash | 1h |
| Audit Gallery cancel → return to wizard, no crash | 1h |
| Audit Upload gagal → retry mechanism | 2h |
| Resume draft — save partial report, continue later | 3h |
| Retry upload — manual + auto on reconnect | 2h |
| Offline queue — store, sync when online | 3h |

### P2.4 — Tracking Completion

**Goal**: Real-time tracking — polling, SSE, WebSocket, timeline sinkron.

| Task | Effort |
|------|--------|
| Audit realtime tracking — current implementation | 2h |
| Polling strategy — interval, backoff, stop on background | 2h |
| SSE/WebSocket integration (jika didukung backend) | 4h |
| Timeline sinkron — pastikan data tracking sequence benar | 2h |
| Empty state, error state, loading state | 1h |

### P2.5 — News + Resource

**Goal**: Fitur News dan Resource selesai secara fungsional.

| Task | Effort |
|------|--------|
| News — list, detail, share, notifikasi | 3h |
| Resource — list, filter, detail, status, request | 3h |
| Error boundary + empty state + loading untuk keduanya | 2h |

### P2.6 — Guest Profile

**Goal**: Profile guest tidak lagi "sangat dasar".

| Task | Effort |
|------|--------|
| Audit profile screen — informasi yang ditampilkan | 1h |
| Edit profile (nama, kontak, foto) | 2h |
| App settings (theme, language, notification) | 2h |
| Login/Register entry point | 1h |

---

## AUTH DOMAIN

### AUTH-1 — Authentication Domain

**Goal**: Complete auth lifecycle — Login, Logout, Refresh Token, OTP, PIN, Session, Remember Me.

| Task | Effort |
|------|--------|
| Login — validasi, error handling, loading state | 2h |
| Logout — clear session, navigate to guest | 1h |
| Refresh Token — silent refresh, retry on 401 | 3h |
| OTP — send, verify, resend, timer | 3h |
| PIN — set, verify, forgot, max attempt lockout | 3h |
| Session Recovery — restore from secure storage | 2h |
| Remember Me — persist credentials | 1h |
| Idle Timeout — auto-logout after inactivity | 2h |
| Route Guard — auth check on every protected route | ✅ sudah ada |

### AUTH-2 — Mandate Domain

**Goal**: Mandate picker, role assignment, permission enforcement, policy.

| Task | Effort |
|------|--------|
| Mandate Picker — list mandates, select, store | 2h |
| Role assignment — berdasarkan mandate yang dipilih | 2h |
| Permission enforcement — policy check per screen/action | 3h |
| Policy engine — role-based access control rules | 3h |

---

## ROLE DOMAIN

### ROLE-1 — Role Engine

**Goal**: Setiap role (TRC, Ketua, Sekretaris, Operator, Relawan, Guest) diuji satu-satu.

| Task | Effort |
|------|--------|
| Role definitions — TRC, Ketua, Sekretaris, Operator, Relawan, Guest | 2h |
| Permission matrix — screen access, action access per role | 3h |
| Test setiap role — login sebagai setiap role, verifikasi akses | 4h |
| Role switch — ganti role tanpa logout | 2h |

---

## GOVERNANCE DOMAIN

### GOV-1 — Governance

**Goal**: Complete governance workflow — Draft, Approval, Signature, Execution, Timeline, Notification.

| Task | Effort |
|------|--------|
| Draft — create, save, edit governance document | 3h |
| Approval — submit, approve, reject, comments | 3h |
| Signature — digital signature flow | 3h |
| Execution — mark as executed, status tracking | 2h |
| Timeline — audit trail of all governance actions | 2h |
| Notification — alert on approval required, status change | 2h |

---

## VOLUNTEER & LOGISTIC

### VOL-1 — Volunteer

**Goal**: Volunteer management — registration, verification, task assignment.

| Task | Effort |
|------|--------|
| Volunteer registration form | 2h |
| Volunteer list + filter + detail | 2h |
| Task assignment — assign volunteer to mission | 2h |
| Status tracking — active, completed, standby | 2h |

### LOG-1 — Logistic

**Goal**: Logistic management — warehouse, inventory, distribution.

| Task | Effort |
|------|--------|
| Warehouse list + map | 2h |
| Inventory management — add, update, track | 3h |
| Distribution — request, approve, deliver | 3h |
| Status tracking — pending, in-transit, delivered | 2h |

---

## QA-F3: Offline & Cache Layer

**Goal**: App works offline for critical features — dilakukan **setelah semua domain selesai**.

**Dependency**: Seluruh domain di atas ✅

| Task | Effort |
|------|--------|
| Repository Pattern: Remote → Local → Sync Queue | 4h |
| SQLite last-known-state untuk Dashboard, Map, Tracking, Profile | 4h |
| Outbox Pattern — offline queue auto-sync on reconnect | 4h |
| Connectivity check before API calls | 1h |
| Map GeoJSON → SQLite → Offline Render | 3h |
| "Offline" indicator + cached data display | 2h |
| WarningNotifier: pause polling when offline | 1h |

---

## QA-F4: Performance & Battery

**Goal**: Cold start ≤ 2s, tab switch < 100ms, no jank — dilakukan **setelah semua domain selesai**.

**Dependency**: QA-F3

| Task | Effort |
|------|--------|
| Shimmer skeleton loading | 2h |
| GeoJSON clustering untuk ratusan marker | 4h |
| Pause map rendering when tab not visible | 2h |
| Adaptive polling interval | 1h |
| Cold start optimization | 3h |
| 60 FPS profiling + regression fix | 3h |

---

## QA-F5: Crash Analytics & Production Hardening

**Goal**: RuntimeLogger → CrashReporter → Analytics → Production Dashboard.

**Dependency**: QA-F0..F4

| Task | Effort |
|------|--------|
| Crash context enrichment (screen, route, user, mandate, device) | 3h |
| `Zone.runGuarded()` around all async gaps | 2h |
| Remote crash reporting endpoint | 2h |
| Remove all mock/skeleton code | 1h |
| Fix empty catch blocks | 1h |
| Production readiness checklist sign-off | 2h |

---

## SPRINT TIMELINE

```
Phase 1 — Foundation (✅ COMPLETE)
  QA-F0  Runtime Foundation               Week 1-2
  QA-F1  Navigation & Lifecycle            Week 3-4
  QA-F2  Native Plugin + HTTP Elim         Week 5-7

Phase 2 — Integration Gate (🟡 ACTIVE)
  QA-IF1 Integration Freeze                Week 8

Phase 3 — Public Domain Completion
  P2.1   Dashboard Public                  Week 9
  P2.2   COP Completion                    Week 10-11
  P2.3   Report Domain Completion          Week 12-13
  P2.4   Tracking Completion               Week 14
  P2.5   News + Resource                   Week 15
  P2.6   Guest Profile                     Week 16

Phase 4 — Auth, Role, Governance
  AUTH-1 Authentication                    Week 17-18
  AUTH-2 Mandate Domain                    Week 19
  ROLE-1 Role Engine                       Week 20
  GOV-1  Governance                        Week 21-22

Phase 5 — Volunteer & Logistic
  VOL-1  Volunteer                         Week 23
  LOG-1  Logistic                          Week 24

Phase 6 — Hardening
  QA-F3  Offline & Cache                   Week 25-26
  QA-F4  Performance & Battery             Week 27-28
  QA-F5  Crash Analytics                   Week 29

Phase 7 — Release
  RC1    Release Candidate                 Week 30
```

---

## RISK ASSESSMENT

| Risk | Probability | Impact | Mitigation |
|------|------------|--------|------------|
| Domain completion terlalu besar | HIGH | HIGH | Urutkan per domain kecil (P2.1 → P2.2 → ...) |
| Regression dari refactoring domain | HIGH | HIGH | Regression checklist tiap sprint; QA-IF1 sebagai baseline |
| Offline dikerjakan sebelum domain selesai | MEDIUM | HIGH | Dilarang — F3 hanya setelah semua domain ✅ |
| Performance dioptimasi sebelum fitur stabil | MEDIUM | MEDIUM | Dilarang — F4 hanya setelah semua domain ✅ |
| Pintu masuk bug dari sprint sebelumnya | MEDIUM | MEDIUM | Aturan: sprint baru = 0 error + 100% regression + LOCKED |

---

## RULES

1. **Tidak ada sprint baru** sebelum sprint sebelumnya mencapai:
   - `flutter analyze` = **0 error**
   - Regression checklist = **100% lulus**
   - Architecture Review = **LOCKED/FROZEN**

2. **Runtime Platform v1.0 — Architecture Freeze**:
   - Tidak boleh membuat Runtime Service baru
   - Tidak boleh mengganti Navigation
   - Tidak boleh mengganti Permission Flow
   - Kecuali ditemukan bug

3. **Feature freeze runtime**: tidak ada perubahan di `core/` kecuali bug fix.

4. **Prioritas mutlak**: selesaikan domain bisnis sesuai PRD. Offline, Performance, Analytics hanya setelah semua domain selesai.

---

## SIGN-OFF

| Sprint | Status |
|--------|--------|
| QA-F0 Runtime Foundation | ✅ LOCKED (9.5/10) |
| QA-F1 Navigation & Lifecycle | ✅ LOCKED (9.8+10/10) |
| QA-F2 Native Plugin + HTTP | ✅ LOCKED |
| QA-IF1 Integration Freeze | 🟡 ACTIVE |
| P2.1 Dashboard Public | ⏳ Pending |
| P2.2 COP Completion | ⏳ Pending |
| P2.3 Report Domain Completion | ⏳ Pending |
| P2.4 Tracking Completion | ⏳ Pending |
| P2.5 News + Resource | ⏳ Pending |
| P2.6 Guest Profile | ⏳ Pending |
| AUTH-1 Authentication | ⏳ Pending |
| AUTH-2 Mandate Domain | ⏳ Pending |
| ROLE-1 Role Engine | ⏳ Pending |
| GOV-1 Governance | ⏳ Pending |
| VOL-1 Volunteer | ⏳ Pending |
| LOG-1 Logistic | ⏳ Pending |
| QA-F3 Offline & Cache | ⏳ Pending |
| QA-F4 Performance & Battery | ⏳ Pending |
| QA-F5 Crash Analytics | ⏳ Pending |
| RC1 Release Candidate | ⏳ Pending |
