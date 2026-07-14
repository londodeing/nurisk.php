# NURISK — DASHBOARD COMPOSITION ENGINE SPECIFICATION
## Document 22: Public Layer Orchestration Strategy
**Version**: 1.0.0 | **Status**: PENDING REVIEW | **Domain**: Public Layer  
**Author**: Enterprise Mobile Solution Architect

---

## 1. OBJECTIVE

Dashboard Publik NURISK bukan sekadar daftar *scrollable widget*, melainkan sebuah **Composition Engine** (Mesin Orkestrasi) yang cerdas. 

Dokumen ini mendefinisikan standar bagaimana berbagai modul independen (Weather, Warning, KPI, Incident, News, Donation) dirakit tanpa saling tumpang tindih, dengan kebijakan *loading*, *error isolation*, dan *refresh* yang terpusat.

---

## 2. ORCHESTRATION ARCHITECTURE

Dashboard akan dipimpin oleh satu *Root Notifier*, yaitu `DashboardProvider`.
Tugasnya adalah memicu dan mengatur jadwal pemuatan sub-provider, sehingga Widget hanya bertugas melakukan *rendering*.

```text
DashboardProvider (Orchestrator)
│
├─ Phase 1 (Immediate Render - Above Fold)
│   ├─ WarningProvider (TTL: 30 detik)
│   ├─ WeatherProvider (TTL: 15 menit)
│   └─ KPIProvider (TTL: 60 detik)
│
├─ Phase 2 (Secondary Render)
│   └─ IncidentFeedProvider (Manual Pull / Fallback Cache)
│
├─ Phase 3 (Lazy Render - Below Fold)
│   ├─ OrgSummaryProvider (Static Cache)
│   ├─ NewsProvider (TTL: 10 menit)
│   └─ DonationProvider (TTL: 5 menit)
│
└─ Static Components
    ├─ CTA Login / Volunteer Registration
    └─ Footer
```

---

## 3. CORE POLICIES

### 3.1 Phased Loading Policy
Memuat 8 API secara serentak akan mencekik thread Flutter dan memori perangkat. 
- **Phase 1**: Dimuat secara paralel di awal.
- **Phase 2 & 3**: Baru akan di-*trigger* pemuatannya setelah Phase 1 selesai me-render data atau error (menggunakan `Future.wait` atau penundaan *event loop*).

### 3.2 Error Isolation
Kegagalan pada salah satu modul (misal: API BMKG down, sehingga Weather gagal) **TIDAK BOLEH** merusak modul lain. 
Setiap modul di-render menggunakan `provider.when(error: ...)` di mana *error state* akan di-*contain* di dalam kartu widget tersebut tanpa melempar *exception* ke root Dashboard.

### 3.3 Centralized Pull-To-Refresh
Hanya ada SATU `RefreshIndicator` di akar Dashboard (melingkupi `CustomScrollView`).
Saat ditarik, UI akan memanggil `ref.read(dashboardProvider.notifier).refreshAll()`.
Fungsi ini kemudian akan memanggil `.refresh()` pada masing-masing sub-provider yang secara logis memang membutuhkan pembaruan, menghormati *TTL* masing-masing agar tidak boros kuota.

### 3.4 Lazy Rendering & Memory Management
Daftar modul akan dirender menggunakan Slivers (contoh: `SliverList` dan `SliverToBoxAdapter`). Hal ini memastikan modul di "bawah lipatan layar" (*below the fold*) seperti Berita dan Donasi tidak akan dibangun (*built*) di memori sampai *user* benar-benar melakukan *scroll* ke bawah.

---

## 4. CALL TO ACTION (CTA) LAYER

Di antara modul-modul ini, Dashboard Engine harus menyisipkan blok statis penggerak aksi (*Call To Action*), yang berfungsi sebagai gerbang konversi dari publik ke ranah tata kelola (Governance).
- **Banner Pendaftaran**: "Ingin menjadi Relawan NU Peduli? [Daftar]"
- **Banner Login**: "Masuk sebagai Pengurus [Login]"

---

## 5. DASHBOARD SCREEN STRUCTURE (MOCKUP)

```dart
CustomScrollView(
  slivers: [
    SliverToBoxAdapter(child: Header()),
    SliverToBoxAdapter(child: WarningBanner()), // Phase 1
    SliverToBoxAdapter(child: WeatherCard()),   // Phase 1
    SliverToBoxAdapter(child: KpiCards()),      // Phase 1
    
    SliverToBoxAdapter(child: SectionTitle('Laporan Insiden')),
    IncidentFeedList(),                         // Phase 2 (Sliver)
    
    SliverToBoxAdapter(child: CtaVolunteer()),  // Static
    
    SliverToBoxAdapter(child: OrgSummary()),    // Phase 3
    SliverToBoxAdapter(child: CtaLogin()),      // Static
    
    // ... News & Donation
  ]
)
```
*Dengan desain ini, F1.10 siap untuk ditranslasikan menjadi kode Flutter yang solid.*
