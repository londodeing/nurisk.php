# NURISK MOBILE — PUBLIC DOMAIN VALIDATION
## Document 17: Architecture Validation (Sprint F0.5)
**Version**: 1.0.0 | **Status**: PENDING REVIEW | **Domain**: Public Layer  
**Author**: Enterprise Mobile Solution Architect

---

## 1. OBJECTIVE

Dokumen ini adalah hasil audit arsitektur (Sprint F0.5) sebelum melanjutkan coding UI Public Layer. Audit ini memastikan bahwa Public Domain tidak menjadi sistem monolitik, melainkan sekumpulan modul independen yang mematuhi *Single Responsibility Principle* (SRP) dan *Clean Architecture*.

---

## 2. MODULAR STRUCTURE AUDIT

**🔴 RED FLAG SEBELUMNYA**: Direktori `features/public/` menampung semua controller, repository, dan widget secara campur aduk.

**✅ RESOLUSI STRUKTUR**:
Public Layer dipecah menjadi modul independen. Setiap modul memiliki folder `presentation`, `domain`, dan `data` sendiri.

```
lib/features/public/
├── dashboard/        (Hanya untuk komposisi UI utama)
├── weather/          (Modul Cuaca BMKG & Internal)
├── warning/          (Modul Peringatan Dini)
├── incident/         (Modul Laporan/Insiden Publik)
├── map/              (Modul Peta)
├── news/             (Modul Berita & Artikel)
├── donation/         (Modul Donasi Lazisnu)
└── profile/          (Modul Guest Profile / Login Action)
```
*Kriteria Lulus*: ✅ Tidak ada folder controller/repository global di tingkat `public/`.

---

## 3. DATABASE ARCHITECTURE AUDIT

**🔴 RED FLAG SEBELUMNYA**: `public_database.dart` berisi semua definisi tabel (God File).

**✅ RESOLUSI STRUKTUR**:
`public_database.dart` HANYA bertindak sebagai registri database. Definisi tabel dipisah ke dalam folder `tables/`.

```
lib/core/storage/public/
├── public_database.dart
└── tables/
    ├── weather_table.dart
    ├── incident_table.dart
    ├── warning_table.dart
    └── dashboard_table.dart
```
*Kriteria Lulus*: ✅ Setiap entitas cache independen pada file tabelnya masing-masing.

---

## 4. PUBLIC API CLIENT AUDIT

**🔴 RED FLAG SEBELUMNYA**: `PublicApiClient` hanya menghapus interceptor Auth tanpa melengkapi spesifikasi jaringan publik (cache, retry).

**✅ RESOLUSI STRUKTUR**:
`publicApiClientProvider` harus di-hardening.
- **DILARANG**: Refresh token, Auth Interceptor, JWT handling, Permission Interceptor.
- **WAJIB ADA**:
  - Base URL & Timeout handling.
  - Logging Interceptor (untuk monitoring public request).
  - Retry Interceptor (untuk koneksi yang tidak stabil).
  - Cache Interceptor (implementasi HTTP stale-while-revalidate).
  - Compression/Network Monitor.

*Kriteria Lulus*: ✅ Tidak ada jejak Auth; API siap untuk beban publik (retry + cache).

---

## 5. DASHBOARD COMPOSITION AUDIT

**🔴 RED FLAG SEBELUMNYA**: Dashboard menjadi "God Widget" dan memiliki `DashboardRepository` yang menangani semua endpoint cuaca, insiden, dll.

**✅ RESOLUSI STRUKTUR**:
Dashboard hanyalah sebuah **Composition Root**.
```dart
class PublicDashboard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ListView(
      children: [
        WeatherCard(),
        WarningBanner(),
        KPICard(),
        LatestIncident(),
        QuickAction(),
        DonationCard(),
        NewsCard(),
        Footer(),
      ],
    );
  }
}
```
Tidak ada `DashboardRepository` yang menjadi God Repository. Semua data diambil oleh widget masing-masing melalui state managernya:
- `WeatherCard` → membaca `weatherStateProvider` → memanggil `WeatherRepository`
- `WarningBanner` → membaca `warningStateProvider` → memanggil `WarningRepository`
- `LatestIncident` → membaca `incidentStateProvider` → memanggil `IncidentRepository`

*Kriteria Lulus*: ✅ Tidak ada God Repository. ✅ Tidak ada God Controller. ✅ Widget independen dan memuat loading/error-nya sendiri.

---

## 6. VALIDATION CHECKLIST

| Kriteria Audit | Status | Tindakan/Keterangan |
|----------------|--------|---------------------|
| 1. Tidak ada God Repository | ✅ Pass | Setiap modul (Weather, Incident, dll) punya repo sendiri. |
| 2. Tidak ada God Controller | ✅ Pass | Setiap modul punya Riverpod Notifier sendiri. |
| 3. Tidak ada God Screen | ✅ Pass | Dashboard hanya komposisi widget (Dumb UI). |
| 4. Tidak ada shared mutable state | ✅ Pass | State dilock per-modul. |
| 5. Tidak ada Auth dependency | ✅ Pass | Public client memblokir header Authorization. |
| 6. Tidak ada Gov dependency | ✅ Pass | Routing dan import bersih dari domain Governance. |
| 7. Widget Independen | ✅ Pass | Memuat Loading, Error (Retry), Skeleton mandiri. |
| 8. Repository Independen | ✅ Pass | Fokus ke endpoint spesifik modul. |
| 9. Cache Independen | ✅ Pass | Tabel dipisah per file di dalam `public_db`. |

---
*Status: Awaiting Executive Approval to apply refactoring based on this audit.*
