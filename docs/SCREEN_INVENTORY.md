# Screen Inventory Audit

Daftar seluruh layar (*Screens*) yang terdeteksi di dalam aplikasi seluler NURISK beserta klasifikasi kepatuhannya terhadap arsitektur SDUI.

| Nama Layar | Jalur File Flutter | Status | Catatan / Temuan |
| :--- | :--- | :--- | :--- |
| **Splash Screen** | [splash_screen.dart](file:///home/londo/nurisk/mobile/app/lib/core/splash/splash_screen.dart) | **Legacy UI** | Navigasi statis berbasis status autentikasi lokal. |
| **Login Screen** | [login_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/auth/presentation/screens/login_screen.dart) | **Legacy UI** | Statis/Native (sesuai spesifikasi, karena form login & register memang native). |
| **Mandate Picker** | [mandate_picker_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/auth/presentation/screens/mandate_picker_screen.dart) | **Legacy UI** | Layar native statis. |
| **Public Dashboard** | [public_dashboard_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/dashboard/presentation/screens/public_dashboard_screen.dart) | **SDUI** | Menggunakan `WidgetFactory` dinamis untuk merender JSON BFF. |
| **Map Screen** | [map_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/map/presentation/screens/map_screen.dart) | **Legacy UI** | Peta di-render secara lokal. Layer control dan pemetaan legend dipatok mati (*hardcoded*). |
| **Report Wizard** | [report_wizard_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/report/presentation/screens/report_wizard_screen.dart) | **Legacy UI** | Form laporan kebencanaan statis, seluruh input field didefinisikan manual di Flutter. |
| **Report Validation List** | [report_validation_list_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/report/presentation/screens/report_validation_list_screen.dart) | **Legacy UI** | Antrean validasi laporan terprogram kaku di sisi klien. |
| **Report Tracking** | [report_tracking_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/report/presentation/screens/report_tracking_screen.dart) | **Legacy UI** | Rincian pelacakan laporan statis. |
| **Resource Screen** | [resource_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/resource/presentation/screens/resource_screen.dart) | **Legacy UI** | Seluruh data logistik/sumber daya di-render manual menggunakan widget lokal. |
| **Account Home (Profile)** | [account_home_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/account/presentation/screens/account_home_screen.dart) | **Partial SDUI** | Menggunakan renderer kartu dinamis (`CardRenderer`), namun navigasi tab bawah masih statis. |
| **Governance Dashboard** | - | **Missing** | Layar khusus Governance belum dibuat secara terpisah, hanya dilempar ke `AccountHomeScreen`. |
| **Assessment Wizard** | [assessment_wizard_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/operasi/assessment/presentation/screens/assessment_wizard_screen.dart) | **Legacy UI** | Form penilaian TRC didefinisikan secara lokal di Flutter, bukan SDUI. |

---

### Bukti Ketidakpatuhan Terbesar
Di dalam berkas [app_router.dart](file:///home/londo/nurisk/mobile/app/lib/core/router/app_router.dart), semua rute utama ditautkan secara statis tanpa ada *Dynamic Page Router Resolver*:
```dart
GoRoute(
  path: RoutePaths.dashboard,
  builder: (context, state) => const PublicDashboardScreen(),
),
GoRoute(
  path: RoutePaths.map,
  builder: (context, state) => const MapScreen(),
),
```
Hal ini menghalangi kemampuan peladen (*server*) untuk menginstruksikan pembukaan layar dinamis baru di luar layar dasar yang sudah terkompilasi.
