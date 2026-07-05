# NURISK Phase 19 — Implementation Plan (Web UI)

Implementasi berfokus eksklusif pada pembentukan Web UI menggunakan Laravel Blade, Bootstrap 5, dan jQuery (bebas dari SPA frameworks).

## Sprint 19A: Layout & Shared Components
- **File Dibuat:**
  - `resources/views/layouts/app.blade.php` (Master layout)
  - `resources/views/components/navbar.blade.php`
  - `resources/views/components/sidebar.blade.php`
  - `resources/views/components/quick-actions.blade.php`
- **Controller & Middleware:** Menyesuaikan auth bawaan agar *redirect* mengarah ke masing-masing *Role Dashboard*.
- **Estimasi Effort:** 3 Hari.
- **Risiko:** Konflik gaya Bootstrap jika aset *legacy* sebelumnya tidak dibersihkan.

## Sprint 19B: POSKO UI
- **File Dibuat:**
  - `resources/views/posko/dashboard.blade.php`
  - `resources/views/posko/modals/sitrep.blade.php`
- **Controller:** `App\Http\Controllers\Web\PoskoController`
- **Test:** `PoskoDashboardTest.php` (Memastikan *Widget* merender angka stok > 0).
- **Estimasi Effort:** 5 Hari.

## Sprint 19C: PCNU UI
- **File Dibuat:**
  - `resources/views/pcnu/dashboard.blade.php`
  - `resources/views/pcnu/eskalasi.blade.php`
- **Controller:** `App\Http\Controllers\Web\PcnuController`
- **Estimasi Effort:** 4 Hari.

## Sprint 19D: PWNU UI
- **File Dibuat:**
  - `resources/views/pwnu/dashboard.blade.php`
  - `resources/views/pwnu/kabupaten-kritis.blade.php`
- **Controller:** `App\Http\Controllers\Web\PwnuController`
- **Estimasi Effort:** 3 Hari.

## Sprint 19E: Command Center (Polling Edition)
- **File Dibuat:**
  - `resources/views/command-center/index.blade.php`
  - `public/js/command-center-polling.js` (Menggunakan Fetch API)
- **Controller:** `App\Http\Controllers\Web\CommandCenterController`
- **Estimasi Effort:** 4 Hari.
- **Risiko:** *Overload query* ke *database* jika `setInterval` dibiarkan terlalu cepat (<10 detik) dan indeks DB kurang sempurna.

## Sprint 19F: Human Testing & Refinement
- **Aktivitas:** Menguji prototipe klik langsung ke pengguna lapangan secara tertutup (10 operator).
- **Estimasi Effort:** 3 Hari.
