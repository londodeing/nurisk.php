# Phase 3 SDUI Implementation Plan

Rencana jangka menengah dan panjang untuk memindahkan platform NURISK menuju 100% kepatuhan arsitektur Server-Driven UI (SDUI).

---

## Urutan Prioritas Pekerjaan (Priority Roadmap)

### Prioritas 1: Critical (Harus Diselesaikan Segera)
1. **Pemisahan Logika Navigasi Bawah (Dynamic Bottom Nav):**
   - Merombak `app_router.dart` agar susunan tab navigasi bawah dimuat secara dinamis dari API BFF.
   - Mengubah `PublicBottomNav.dart` untuk membaca konfigurasi dari state `configProvider`.
2. **Standardisasi Peta COP (SDUI Map Layers):**
   - Mengubah API `/api/map/layers` agar mengirimkan metadata penggayaan (*styling configurations*).
   - Menghapus pemetaan ikon, warna, dan popup statis di `MapScreen.dart` dan menggantinya dengan parser styling dinamis.

### Prioritas 2: High (Penting untuk Skalabilitas)
1. **Formulir Laporan Kejadian & Assessment Dinamis (Form SDUI):**
   - Membangun parser `FormBuilderWidget` di Flutter yang dapat membuat isian input (`TextField`, `Dropdown`, `DatePicker`) secara dinamis berdasarkan array skema JSON dari peladen.
   - Migrasi layar `ReportWizardScreen` dan `AssessmentWizardScreen` agar menggunakan `FormBuilderWidget`.
2. **Pembuatan BFF Dashboard Khusus Kepemimpinan (Governance BFF):**
   - Membangun `GovernanceBffController` di Laravel untuk melayani dasbor pimpinan PCNU/PWNU secara modular.

### Prioritas 3: Medium (Penyempurnaan Arsitektur)
1. **Pembersihan Modul Profile & Settings:**
   - Menghapus seluruh switch-case penentuan ikon statis di `settings_card.dart`.
   - Menyeragamkan data profil identitas (`IdentityCard`) ke dalam struktur widget generik.
2. **Kalkulator KPI Terpusat (BFF Aggregator):**
   - Memigrasikan widget `kpi_cards_section.dart` untuk menerima data berupa grid `SummaryCard` generik.

---

## Rencana Verifikasi Pengujian (Verification Plan)
Setiap migrasi layar ke SDUI wajib lolos kriteria pengujian berikut:
1. **Zero-Code UI Change:** Mengubah judul, warna, atau ikon widget di Laravel BFF harus langsung tercermin pada emulator Flutter tanpa kompilasi ulang.
2. **Offline Resilience:** Parser SDUI harus memiliki fallback skema jika koneksi internet terputus (menggunakan cache lokal di `config_local_datasource`).
