# PUBLIC DOMAIN FIX PLAN

**Objective**: Mengubah Public Domain dari kumpulan screen menjadi sistem operasional yang utuh.
**Strategy**: Kerjakan dalam 3 gelombang — Critical → High → Medium. **Tidak ada fitur baru.**

---

## Gelombang 1: CRITICAL (Harus selesai pertama)

| ID | Task | Module | File(s) | Effort |
|----|------|--------|---------|--------|
| C1 | Hapus baris kedua `toggleLayer` di live update timer | COP Map | `cop_map_screen.dart:42` | 1 menit |
| C2 | Integrasi AppLifecycleObserver ke live update timer COP Map | COP Map | `cop_map_screen.dart` | 15 menit |
| C3 | Pindahkan submit laporan dari raw Dio ke `laporanRepositoryProvider` | Report | `report_wizard_screen.dart:273-343` | 30 menit |
| C4 | Refactor tracking fetch dari raw Dio ke `laporanRemoteDatasourceProvider` | Tracking | `report_tracking_screen.dart:50-51` | 20 menit |
| C5 | Pindahkan map config fetch dari raw Dio ke repository layer | COP Map | `cop_map_screen.dart:97-113` | 30 menit |

**Total effort**: ~1.5 jam

---

## Gelombang 2: HIGH

| ID | Task | Module | File(s) | Effort |
|----|------|--------|---------|--------|
| H1 | Fix Dashboard KPI field mapping (sync with backend schema) | Dashboard | `dashboard_kpi_model.dart` | 15 menit |
| H2 | Fix Incident Model — jangan hardcode severity/isVerified | Incident | `incident_model.dart` | 15 menit |
| H3 | Ganti placeholder `Scaffold(Text('Resource'))` dengan `ResourceScreen` sebenarnya | Router | `app_router.dart:155` | 5 menit |
| H4 | Ganti placeholder route `/report/:trackingCode` dengan `ReportTrackingScreen` sebenarnya | Router | `app_router.dart:123-130` | 10 menit |
| H5 | Tambahkan error handling ke `AuthStateNotifier._loadState()` | Auth | `auth_state_provider.dart:63-81` | 10 menit |
| H6 | Integrasi AppLifecycleObserver ke Tracking polling timer | Tracking | `report_tracking_screen.dart:31-37` | 15 menit |
| H7 | Tambahkan retry mechanism ke laporan FutureProviders | Report | `laporan_provider.dart` | 20 menit |
| H8 | Resolve duplicate error boundary init (keep one, remove other) | Core | `error_boundary.dart` + `error_handler.dart` | 15 menit |
| H9 | Fix WarningProvider timer/observer registration order | Warning | `warning_provider.dart:15-22` | 5 menit |
| H10 | Fix splash navigation race condition | Splash | `splash_screen.dart` | 15 menit |
| H11 | Dashboard refresh: handle partial failure | Dashboard | `dashboard_kpi_provider.dart:refresh()` | 15 menit |
| H12 | Fix Config LocalDatasource — implement actual cache | Config | `config_local_datasource.dart` | 20 menit |

**Total effort**: ~2.5 jam

---

## Gelombang 3: MEDIUM

| ID | Task | Effort |
|----|------|--------|
| M1 | Tambah reverse geocode di Report Wizard step 2 | 30 menit |
| M2 | Report success: navigasi ke Tracking screen dengan kode | 15 menit |
| M3 | COP Map: tambahkan loading/error/retry state | 30 menit |
| M4 | COP Map: tambahkan map style switching (theme-aware) | 20 menit |
| M5 | COP Map: fix potential double bottom sheet | 10 menit |
| M6 | News: change from CircularProgressIndicator to skeleton | 15 menit |
| M7 | News: add pagination | 30 menit |
| M8 | Dashboard orchestrator: fix anti-pattern (don't wrap Ref in Provider) | 15 menit |
| M9 | Resource: add pagination | 20 menit |
| M10 | Guest Profile: implement Donasi and Lacak Laporan callbacks | 15 menit |
| M11 | Guest Profile: add About/Privacy/Terms/Version | 20 menit |
| M12 | Fix hardcoded `/public/news/` navigation to named route | 5 menit |
| M13 | Fix hardcoded 'demo-uuid' in profile action mapping | 5 menit |
| M14 | Fix bottom nav back handling edge cases | 15 menit |

**Total effort**: ~4 jam

---

## Total Estimasi

| Gelombang | Tasks | Effort |
|-----------|-------|--------|
| Critical | 5 | 1.5 jam |
| High | 12 | 2.5 jam |
| Medium | 14 | 4 jam |
| **Total** | **31** | **~8 jam** |

---

## Aturan Pengerjaan

1. **Commit setelah SELESAI SATU GELOMBANG**. Jangan commit partial.
2. **Setiap perubahan** harus masuk ke `PUBLIC_DOMAIN_IMPLEMENTATION.md`.
3. **Tidak boleh** membuat file baru di luar yang sudah ada.
4. **Runtime QA-F0 s/d F2 LOCKED** — jangan sentuh.
5. **Jangan modifikasi** `main.dart`, `runtime_state.dart`, `runtime_initializer.dart`.
6. Setelah semua selesai, jalankan `flutter analyze` — harus 0 error baru.