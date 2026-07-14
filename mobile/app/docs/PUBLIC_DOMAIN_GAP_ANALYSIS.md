# PUBLIC DOMAIN GAP ANALYSIS — NURISK Mobile

> Perbandingan implementasi saat ini vs PRD (Pra-Produksi)
> Date: 2026-07-08

---

## 1. DASHBOARD PUBLIC

| PRD Requirement | Current Implementation | Gap | Severity |
|----------------|----------------------|-----|----------|
| Landing page for ALL users (no login) | ✅ Splash → Dashboard for guest | None | — |
| WeatherCard — current weather, 15-min refresh | ✅ `WeatherNotifier` with polling | None | — |
| WarningBanner — BMKG/EWS alerts, 30-sec refresh | ✅ `WarningNotifier` with lifecycle-aware polling | None | — |
| KPICards — 4 KPIs (Kejadian Aktif, Tervalidasi, Wilayah Terdampak, Relawan Bertugas) | ✅ `DashboardKpiNotifier` with 4 KPI model | KPI field mapping vs backend response needs verification | MEDIUM |
| LatestIncident — scrollable feed | ✅ `IncidentNotifier` with pagination | None | — |
| QuickAction — static nav buttons | ✅ CTA widgets exist | None | — |
| Offline resilience: stale-while-revalidate | ❌ Not implemented | No offline cache fallback for dashboard | **HIGH** |
| Error isolation: one widget cannot crash others | ❌ No per-widget ErrorBoundary | Single error crashes entire dashboard | **HIGH** |
| Phased loading (above-fold first) | ❌ Not implemented | All widgets load simultaneously | MEDIUM |
| Pull-to-refresh | ❌ Not implemented | DashboardOrchestrator exists but no pull-to-refresh | MEDIUM |
| DonationCard | ❌ Not implemented | No DonationCard widget | MEDIUM |
| NewsCard | ❌ Not implemented | No NewsCard widget | MEDIUM |
| Cold start < 2s, First Paint < 1s | ❌ Not measured | No performance baseline | MEDIUM |

### KPI Backend Dependency
| KPI | Status |
|-----|--------|
| `GET /api/public/dashboard/kpi` | ⚠️ Endpoint defined but backend implementation unknown |
| Redis cache 60s TTL | ❌ Unknown if implemented |
| Anti-hoax: data below VERIFIED excluded | ❌ Unknown if backend filters |

---

## 2. COP MAP

| PRD Requirement | Current Implementation | Gap | Severity |
|----------------|----------------------|-----|----------|
| MapLibre GL renderer | ✅ Implemented | None | — |
| Layer control with checkboxes | ✅ `LayerControlBottomSheet` | None | — |
| Marker clustering | ❌ Not implemented | GeoJSON rendered directly, no clustering | MEDIUM |
| Marker tap → Bottom Sheet → Detail | ✅ `OperationalBottomSheet` | None | — |
| Timeline renderer | ✅ `TimelineRenderer` | None | — |
| Spatial filter | ✅ `SpatialFilterBottomSheet` | None | — |
| SSE real-time updates (Vision) | ❌ Not implemented | Uses polling (MVP compromise) | LOW |
| Personnel real-time location | ❌ Phase 2 | Not expected yet | — |
| Max 500 markers, 50px cluster | ❌ No clustering | Performance risk with many markers | MEDIUM |
| Auto-pan on new P0 event | ❌ Not implemented | No SSE so no auto-pan | MEDIUM |
| OpenStreetMap tiles | ✅ MapLibre default | None | — |

---

## 3. REPORT WIZARD

| PRD Requirement | Current Implementation | Gap | Severity |
|----------------|----------------------|-----|----------|
| Public report submission (no auth) | ✅ Single-step form exists | Step indicator is visual only | — |
| Photo upload (camera + gallery) | ✅ Via MediaService | None | — |
| POST /api/laporan | ✅ Via Dio + LaporanRepository | Endpoint path needs verification | MEDIUM |
| GPS integration | ✅ Via GeoService | None | — |
| GPS mati handling | ⚠️ Partial | Try/catch catches error but no dedicated guidance | MEDIUM |
| GPS mock detection | ❌ Not implemented | No `GeoResult.isMocked` check in Report Wizard | **HIGH** |
| GPS lambat (timeout > 30s) | ❌ No timeout feedback | User sees indefinite loading | MEDIUM |
| Upload failure → retry | ❌ Not implemented | Shows generic error, no retry button | **HIGH** |
| Resume draft (save partial) | ❌ Not implemented | No partial report persistence | MEDIUM |
| Offline queue (store and sync) | ❌ QA-F3 | Not expected yet | — |
| Rate limiting 10 req/min/IP | ❌ Backend concern | Not a Flutter issue | — |
| Photo size validation | ⚠️ 20MB limit in MediaService | Ok, but no compression | LOW |

---

## 4. TRACKING

| PRD Requirement | Current Implementation | Gap | Severity |
|----------------|----------------------|-----|----------|
| Report status timeline | ✅ `report_tracking_screen.dart` | None | — |
| Lifecycle: REPORTED → ... → CLOSED | ✅ Status labels present | None | — |
| Active status polling | ✅ Timer-based refresh | None | — |
| Real-time SSE updates | ❌ Not implemented | Uses polling (acceptable for MVP) | LOW |
| Volunteer deployment tracking | ❌ Phase 2 | Not expected yet | — |
| Task tracking with progress | ❌ Phase 2 | Not expected yet | — |
| Public tracking detail (timeline) | ✅ OK | None | — |

---

## 5. NEWS

| PRD Requirement | Current Implementation | Gap | Severity |
|----------------|----------------------|-----|----------|
| News/Berita module folder | ❌ Does not exist | No `features/public/news/` directory | **HIGH** |
| NewsCard on dashboard | ❌ Not implemented | No widget | **HIGH** |
| Article list screen | ❌ Not implemented | No `/p/artikel` route | **HIGH** |
| Article detail screen | ❌ Not implemented | No `/p/artikel/:slug` route | **HIGH** |
| 30-min cache refresh | ❌ No module | N/A | — |
| Backend endpoint | ❌ Not specified in API contract | No `GET /api/public/news` | — |

---

## 6. RESOURCE

| PRD Requirement | Current Implementation | Gap | Severity |
|----------------|----------------------|-----|----------|
| Resource screen | ❌ Does not exist | No `features/public/resource/` directory | **HIGH** |
| DonationCard on dashboard | ❌ Not implemented | No widget | MEDIUM |
| Resource list | ❌ Not implemented | No route or screen | MEDIUM |
| Emergency contacts | ❌ Not implemented | No display on incident detail | MEDIUM |
| Backend endpoint | ❌ Not specified | No `GET /api/public/resources` | — |

---

## 7. GUEST PROFILE

| PRD Requirement | Current Implementation | Gap | Severity |
|----------------|----------------------|-----|----------|
| Profile tab in bottom nav | ✅ `profile_screen.dart` | None | — |
| "Masuk" button (not logged in) | ✅ Present | None | — |
| "Daftar Relawan" link | ✅ Present | None | — |
| Quick access (About, Help, etc.) | ✅ Present | None | — |
| User summary (when logged in) | ✅ ProfileNotifier loads user data | None | — |
| Login is ACTION, not route | ✅ Navigates to login screen | None | — |
| Return-to parameter | ❌ Not implemented | Login loses context | MEDIUM |

---

## 8. NAVIGATION

| PRD Requirement | Current Implementation | Gap | Severity |
|----------------|----------------------|-----|----------|
| Public Bottom Nav (5 tabs) | ✅ Beranda, Peta, Lapor, Info, Akun | None | — |
| Governance Bottom Nav (5 tabs) | ✅ Via GoRouter shell routes | None | — |
| `/p/*` public (no auth) | ✅ Route guard allows all | None | — |
| `/g/*` governance (auth+mandate) | ✅ Route guard checks auth | None | — |
| `/auth/*` auth flow | ✅ Route guard redirects if authenticated | None | — |
| Double-back to exit | ✅ `public_bottom_nav.dart` handles back | `SystemNavigator.pop()` bypasses NavigationService | MEDIUM |
| Deep link support | ✅ Route names, intent filter, singleTask | None | — |
| Session recovery | ✅ SplashScreen waits auth state | None | — |

---

## 9. AUTHENTICATION (from Public Domain perspective)

| PRD Requirement | Current Implementation | Gap | Severity |
|----------------|----------------------|-----|----------|
| Login with `no_hp` + `kata_sandi` | ✅ LoginScreen uses phone + password | None | — |
| Sanctum token | ✅ `authApiClientProvider` with Bearer token | Token storage in SharedPreferences — PRD says secure storage | **HIGH** |
| Mandate Picker | ✅ `mandate_picker_screen.dart` | None | — |
| Logout clears session + redirect | ✅ `NavigationService.logout()` | None | — |
| Token in secure storage | ❌ Currently SharedPreferences | Token, mandate, refresh token, role in SharedPreferences | **HIGH** |
| Forgot Password | ❌ Not implemented | No endpoint, no UI | MEDIUM |
| OTP | ❌ Not implemented | No WhatsApp/SMS OTP | LOW |
| Biometric unlock | ❌ Not implemented | No biometric/pin lock screen | LOW |
| Idle timeout (30 min) | ❌ Not implemented | No session timeout | LOW |
| Device UUID binding | ❌ Not implemented | No device binding | LOW |

---

## GAP SUMMARY

| Area | Critical | High | Medium | Low | Total |
|------|----------|------|--------|-----|-------|
| Dashboard | 0 | 2 | 4 | 0 | 6 |
| COP Map | 0 | 0 | 3 | 2 | 5 |
| Report Wizard | 0 | 2 | 3 | 1 | 6 |
| Tracking | 0 | 0 | 0 | 1 | 1 |
| News | 0 | 3 | 0 | 0 | 3 |
| Resource | 0 | 1 | 3 | 0 | 4 |
| Guest Profile | 0 | 0 | 1 | 0 | 1 |
| Navigation | 0 | 0 | 1 | 0 | 1 |
| Authentication | 0 | 2 | 2 | 3 | 7 |
| **Total** | **0** | **10** | **17** | **7** | **34** |

### Priority Matrix

**HIGH priority (must fix before public release):**
1. Remove hardcoded credentials from LoginScreen (A06)
2. Remove magic PIN 123456 fallback (A06)
3. Add ErrorBoundary per widget on Dashboard (P01)
4. Add GPS mock detection in Report Wizard (P03)
5. Add upload failure retry in Report Wizard (P03)
6. Move tokens to secure storage (A09)
7. Add offline cache for dashboard (P01)
8. News module — at minimum NewsCard placeholder (P05)
9. Resource module — at minimum resource list (P06)
10. Remove hardcoded demo data from Executive Dashboard (A06)

**MEDIUM priority (fix post-release but before feature freeze):**
1. Phased loading on dashboard
2. Pull-to-refresh for dashboard
3. Return-to parameter for login
4. GPS timeout feedback in wizard
5. GPS mati dedicated guidance
6. KPI field mapping verification
7. Marker clustering for COP map
8. Zero-cluster performance optimization
9. Resume draft for report wizard
10. Rate limit handling UI
