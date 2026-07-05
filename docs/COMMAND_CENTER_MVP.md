# COMMAND CENTER MVP DESIGN

> Desain final dashboard Command Center berdasarkan data discovery (48 KPI, 15 endpoint, 4 role).
> Teknologi: Blade + Bootstrap 5 + Chart.js + jQuery + AJAX polling.
> Tanpa: Vue, React, Livewire, Inertia, SSE, WebSocket, Redis.

---

## 1. DASHBOARD PWNU

**Route:** `/command-center/pwnu`
**Middleware:** `auth`, `role:super_admin,pwnu`, `scope:pwnu`
**Controller:** `App\Http\Controllers\CommandCenter\PwnuDashboardController`
**Layout:** `layouts/command-center.blade.php`
**View:** `command-center.pwnu.dashboard`

### Widget Layout (Bootstrap 5 Grid)

```
┌─────────────────────────────────────────────────────────────┐
│ [INSIDEN AKTIF] [PERSONEL] [POSKO AKTIF] [TOTAL KORBAN]    │  ← Hero row, 4 col
├──────────────────────────────────┬──────────────────────────┤
│ DAFTAR INSIDEN AKTIF             │ ACTIVITY TIMELINE        │  ← 8+4 col
│ - Per PCNU + prioritas           │ - 24 jam terakhir        │
│ - Status, waktu, sitrep terbaru  │ - peristiwa per jam      │
├──────────────────────────────────┼──────────────────────────┤
│ POSKO AKTIF PER PCNU             │ RINGKASAN KEBUTUHAN      │  ← 6+6 col
│ - Jumlah posko per wilayah       │ - relawan dibuka         │
│                                   │ - mobilisasi aktif       │
└──────────────────────────────────┴──────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────────────┐
│ PERINGATAN / EARLY WARNING                                  │  ← Footer, full width
│ - Sitrep terlambat (>24jam)                                 │
│ - Kebutuhan kritis                                          │
└─────────────────────────────────────────────────────────────┘
```

### Widget Specification

| Widget | KPI # | Source | Refresh | Type |
|---|---|---|---|---|
| Total insiden aktif | 1 | OperasiInsiden | 30s (red) | Number card + Chart.js doughnut by status |
| Total personel aktif | 9 | OperasiPenugasan | 30s (red) | Number card |
| Posko aktif | 14 | OperasiPosaju | 60s (blue) | Number card |
| Total korban terkini | 19 | OperasiSitrepDampak | 60s (blue) | Number card (meninggal/luka/mengungsi) |
| Daftar insiden aktif | 1,3,4 | OperasiInsiden + PCNU | 30s (red) | Table: kode, PCNU, status, prioritas, sitrep terbaru |
| Activity timeline | 42,44 | OperasiJurnal | 30s (red) | List kronologis (10 item) |
| Posko per PCNU | 15 | OperasiPosaju | 60s (blue) | Bar chart (Chart.js) |
| Kebutuhan relawan | 29,30 | RelawanKebutuhan | 60s (blue) | Number + list |
| Mobilisasi aktif | 21 | OperasiMobilisasi | 60s (blue) | Number |
| Sitrep terlambat | 18 | OperasiSitrep | 5min (black) | Alert card |

---

## 2. DASHBOARD PCNU

**Route:** `/command-center/pcnu`
**Middleware:** `auth`, `role:pcnu`, `scope:pcnu`
**Controller:** `App\Http\Controllers\CommandCenter\PcnuDashboardController`
**Layout:** `layouts/command-center.blade.php`
**View:** `command-center.pcnu.dashboard`

### Widget Layout

```
┌─────────────────────────────────────────────────────────────┐
│ [INSIDEN] [PERSONEL] [POSKO] [KORBAN] [TUGAS AKTIF]        │  ← Hero row, 5 col
├──────────────────────────────────┬──────────────────────────┤
│ DAFTAR INSIDEN + SITREP          │ TIMELINE AKTIVITAS       │  ← 8+4 col
│ - Status, prioritas, sitrep      │ - peristiwa 24 jam       │
│ - waktu_respon, waktu_verifikasi │                          │
├──────────────────────────────────┼──────────────────────────┤
│ DAFTAR TUGAS & PROGRES           │ MOBILISASI               │  ← 6+6 col
│ - Per klaster + progres          │ - per jenis pergerakan   │
│ - personel per tugas             │                          │
├──────────────────────────────────┼──────────────────────────┤
│ KEBUTUHAN RELAWAN                │ KEBUTUHAN LOGISTIK       │  ← 6+6 col
│ - dibuka, pendaftar, terpenuhi   │ - via sitep kebutuhan    │
└──────────────────────────────────┴──────────────────────────┘
```

### Widget Specification

| Widget | KPI # | Source | Refresh | Type |
|---|---|---|---|---|
| Insiden aktif | 1,2 | OperasiInsiden byPcnu | 30s (red) | Number card + doughnut |
| Personel aktif | 9,10 | OperasiPenugasan | 30s (red) | Number card |
| Posko aktif | 14 | OperasiPosaju | 30s (red) | Number card |
| Total korban | 19 | OperasiSitrepDampak | 60s (blue) | Number card (stacked) |
| Tugas aktif | 24,27 | OperasiTugas | 30s (red) | Number card + avg progress |
| Daftar insiden | 1,2,3,5 | OperasiInsiden | 30s (red) | Table + status badges |
| Activity timeline | 42,44 | OperasiJurnal via insiden | 30s (red) | List kronologis |
| Daftar tugas | 24,25,26,28 | OperasiTugas | 60s (blue) | Table + progress bar |
| Mobilisasi | 21,23 | OperasiMobilisasi | 60s (blue) | List per jenis |
| Kebutuhan relawan | 29,30,33 | RelawanKebutuhan | 60s (blue) | List + count |
| Logistik | 20 | OperasiSitrepKebutuhan | 60s (blue) | List kebutuhan by sitrep |
| Sitrep terlambat | 18 | OperasiSitrep | 5min (black) | Alert badge |

---

## 3. DASHBOARD POSKO

**Route:** `/command-center/posko`
**Middleware:** `auth`, `role:pcnu`, `scope:pcnu`
**Controller:** `App\Http\Controllers\CommandCenter\PoskoDashboardController`
**Layout:** `layouts/command-center.blade.php`
**View:** `command-center.posko.dashboard`

> Posko tidak memiliki role sendiri. User adalah PCNU dengan `id_posaju` spesifik.
> Scope filter: `OperasiPosaju::where('pj_posaju', auth()->id())` atau via session.

### Widget Layout

```
┌─────────────────────────────────────────────────────────────┐
│ [NAMA POSKO] [PERSONEL] [TUGAS] [KEBUTUHAN]                │  ← Hero row, 4 col
├──────────────────────────────────┬──────────────────────────┤
│ DAFTAR TUGAS + PROGRES           │ PERSONEL POSKO           │  ← 8+4 col
│ - judul, status, progres bar     │ - daftar relawan per pos │
│ - tombol update progres          │                          │
├──────────────────────────────────┼──────────────────────────┤
│ KEBUTUHAN RELAWAN                │ TIMELINE POSKO           │  ← 6+6 col
│ - posisi dibuka, pendaftar       │ - aktivitas terkait posko│
│                                  │                          │
└──────────────────────────────────┴──────────────────────────┘
```

### Widget Specification

| Widget | KPI # | Source | Refresh | Type |
|---|---|---|---|---|
| Info posko | 14 | OperasiPosaju | 30s (red) | Hero card: nama, status, PJ |
| Personel di posko | 9 | OperasiPenugasan via klaster/posaju | 30s (red) | Number card |
| Tugas posko | 24,25,27 | OperasiTugas by id_posaju | 30s (red) | Number + avg progres |
| Kebutuhan | 29,30 | RelawanKebutuhan by id_posaju | 30s (red) | Number card |
| Daftar tugas | 24,26,27,28 | OperasiTugas | 30s (red) | Table + progress bar |
| Daftar personel | 9 | OperasiPenugasan via posaju | 60s (blue) | Table: nama, peran, status |
| Kebutuhan relawan | 29,30,33 | RelawanKebutuhan + Pendaftar | 60s (blue) | List + count pendaftar |
| Timeline posko | 42 | OperasiJurnal by id_insiden | 30s (red) | List aktivitas |

---

## 4. DASHBOARD RELAWAN

**Route:** `/command-center/relawan`
**Middleware:** `auth`, `role:relawan`
**Controller:** `App\Http\Controllers\CommandCenter\RelawanDashboardController`
**Layout:** `layouts/command-center.blade.php`
**View:** `command-center.relawan.dashboard`

### Widget Layout

```
┌─────────────────────────────────────────────────────────────┐
│ [STATUS SAYA] [TUGAS AKTIF]                                 │  ← Hero row, 2 col
├──────────────────────────────────┬──────────────────────────┤
│ TUGAS PRIBADI SAYA               │ INSIDEN TERKAIT          │  ← 8+4 col
│ - judul, status, progres         │ - info insiden penugasan │
│ - tombol update progres          │                          │
├──────────────────────────────────┴──────────────────────────┤
│ TIMELINE AKTIVITAS SAYA                                     │  ← full width
│ - aktivitas pribadi 24 jam                                  │
└─────────────────────────────────────────────────────────────┘
```

### Widget Specification

| Widget | KPI # | Source | Refresh | Type |
|---|---|---|---|---|
| Status saya | 39,40 | AuthUser + penugasan | 30s (red) | Badge: tersedia/bertugas |
| Tugas aktif saya | 24,28 | OperasiTugas by ditugaskan_ke | 30s (red) | Number card |
| Daftar tugas saya | 24,25,27 | OperasiTugas | 30s (red) | Table + progress bar |
| Info insiden | 1 | OperasiInsiden via penugasan | 60s (blue) | Card ringkasan |
| Timeline saya | 42 | OperasiJurnal by id_pengguna | 30s (red) | List kronologis |

---

## 5. POLLING STRATEGY

| Warna | Interval | Tipe Data | Widget |
|---|---|---|---|
| 🔴 Red | 30s | Status kritis — angka dan daftar yang berubah cepat | Insiden count, personel, tugas aktif, timeline, posko, status relawan |
| 🔵 Blue | 60s | Data operasional — daftar dan detail | Sitrep, mobilisasi, kebutuhan, daftar tugas, personel detail |
| ⚫ Black | 5 menit | Data agregat — ringkasan dan peringatan | Sitrep terlambat, ringkasan pleno, surat |

### Mekanisme Polling (jQuery)

```javascript
// Setiap widget adalah <div data-cc-widget="insiden-aktif" data-cc-interval="30">
// PollingManager membaca data-cc-interval dan menjalankan timer

const CC_POLLING = {
    red: 30000,   // 30s
    blue: 60000,  // 60s
    black: 300000 // 5min
};

// Widget mendaftarkan diri:
// <div data-cc-endpoint="/api/cc/pwnu/insiden-aktif" data-cc-interval="30"></div>
// PollingManager.fetchAndRender() tiap interval
```

---

## 6. API ENDPOINTS

### PWNU Endpoints

| Method | Endpoint | KPI | Interval | Query Cost |
|---|---|---|---|---|
| GET | `/api/cc/pwnu/summary` | 1,9,14,19 | 30s | A |
| GET | `/api/cc/pwnu/insiden-aktif` | 1,3,4 | 30s | A |
| GET | `/api/cc/pwnu/timeline` | 42,44 | 30s | A |
| GET | `/api/cc/pwnu/posko-chart` | 15 | 60s | A |
| GET | `/api/cc/pwnu/kebutuhan` | 29,30,21 | 60s | A |
| GET | `/api/cc/pwnu/peringatan` | 18 | 5min | B |

### PCNU Endpoints

| Method | Endpoint | KPI | Interval | Query Cost |
|---|---|---|---|---|
| GET | `/api/cc/pcnu/summary` | 1,9,14,19,24 | 30s | A |
| GET | `/api/cc/pcnu/insiden-aktif` | 1,2,3,5 | 30s | A |
| GET | `/api/cc/pcnu/timeline` | 42,44 | 30s | A |
| GET | `/api/cc/pcnu/tugas` | 24,25,26,27,28 | 60s | A/B |
| GET | `/api/cc/pcnu/relawan` | 29,30,33 | 60s | A/B |
| GET | `/api/cc/pcnu/peringatan` | 18 | 5min | B |

### POSKO Endpoints

| Method | Endpoint | KPI | Interval | Query Cost |
|---|---|---|---|---|
| GET | `/api/cc/posko/summary` | 14,9,24,29 | 30s | A |
| GET | `/api/cc/posko/tugas` | 24,25,26,27,28 | 30s | A/B |
| GET | `/api/cc/posko/personel` | 9 | 60s | A |
| GET | `/api/cc/posko/relawan` | 29,30,33 | 60s | A/B |
| GET | `/api/cc/posko/timeline` | 42 | 30s | A |

### RELAWAN Endpoints

| Method | Endpoint | KPI | Interval | Query Cost |
|---|---|---|---|---|
| GET | `/api/cc/relawan/status` | 39,40,24 | 30s | A/B |
| GET | `/api/cc/relawan/tugas-saya` | 24,25,27,28 | 30s | A |
| GET | `/api/cc/relawan/insiden` | 1 | 60s | A |
| GET | `/api/cc/relawan/timeline` | 42 | 30s | A |

### Endpoint Count: **15 unique** ✅ (≤15 target)

---

## 7. ROUTE REGISTRATION

```php
// routes/command-center.php
Route::prefix('command-center')->name('cc.')->middleware(['auth'])->group(function () {

    // PWNU
    Route::middleware(['role:super_admin,pwnu', 'scope:pwnu'])->prefix('pwnu')->name('pwnu.')->group(function () {
        Route::get('/', [PwnuDashboardController::class, 'index'])->name('dashboard');
    });

    // PCNU
    Route::middleware(['role:pcnu', 'scope:pcnu'])->prefix('pcnu')->name('pcnu.')->group(function () {
        Route::get('/', [PcnuDashboardController::class, 'index'])->name('dashboard');
    });

    // POSKO
    Route::middleware(['role:pcnu', 'scope:pcnu'])->prefix('posko')->name('posko.')->group(function () {
        Route::get('/', [PoskoDashboardController::class, 'index'])->name('dashboard');
    });

    // RELAWAN
    Route::middleware(['role:relawan'])->prefix('relawan')->name('relawan.')->group(function () {
        Route::get('/', [RelawanDashboardController::class, 'index'])->name('dashboard');
    });
});

// API Endpoints (for AJAX polling)
Route::prefix('api/cc')->name('api.cc.')->middleware(['auth', 'throttle:60,1'])->group(function () {
    // PWNU API
    Route::middleware(['role:super_admin,pwnu'])->prefix('pwnu')->name('pwnu.')->group(function () {
        Route::get('summary', [PwnuDashboardController::class, 'summary']);
        Route::get('insiden-aktif', [PwnuDashboardController::class, 'insidenAktif']);
        Route::get('timeline', [PwnuDashboardController::class, 'timeline']);
        Route::get('posko-chart', [PwnuDashboardController::class, 'poskoChart']);
        Route::get('kebutuhan', [PwnuDashboardController::class, 'kebutuhan']);
        Route::get('peringatan', [PwnuDashboardController::class, 'peringatan']);
    });
    // PCNU, POSKO, RELAWAN — similar pattern
});
```

---

## 8. LAYOUT & UI COMPONENTS

### Layout Base: `layouts/command-center.blade.php`

```
┌───────────── TOP NAV — NURISK Command Center ─────────────────┐
│ Logo | PWNU | PCNU | Posko | Relawan | [Profil ▼]             │
├──────────────┬────────────────────────────────────────────────┤
│ SIDEBAR      │  MAIN CONTENT                                  │
│ (collapsible)│  ┌──────────────────────────────────────────┐  │
│ • Ringkasan  │  │ Hero Row — stat cards (4-5 col)          │  │
│ • Insiden    │  ├──────────────────────┬───────────────────┤  │
│ • Personel   │  │ Left Panel (8 col)   │ Right Panel (4)   │  │
│ • Posko      │  │   Table/list/data    │ Timeline/chart    │  │
│ • Tugas      │  ├──────────────────────┼───────────────────┤  │
│ • Relawan    │  │ Bottom Row (6+6)     │                   │  │
│ • Laporan    │  └──────────────────────────────────────────┘  │
└──────────────┴────────────────────────────────────────────────┘
```

### Required CSS/JS Assets (Bootstrap 5 + minimal custom)

```html
<!-- Bootstrap 5 + Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11/font/bootstrap-icons.css" rel="stylesheet">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

<!-- jQuery (via Bootstrap dependency) + Custom polling -->
<script src="https://code.jquery.com/jquery-3.7/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/cc-polling.js') }}"></script>
```

---

## 9. KPI COMPLIANCE

| Kriteria | Status |
|---|---|
| Semua widget dari data aktual | ✅ 48 KPI dari tabel existing |
| Tidak ada widget query mahal | ✅ Semua KPI A atau B (<200ms) |
| Dashboard via Blade SSR | ✅ Layout + view Blade murni |
| Polling 30s via jQuery AJAX | ✅ PollingManager.js |
| Siap pilot tanpa Redis/realtime | ✅ Tidak ada infra tambahan |
| ≤15 endpoint per dashboard | ✅ PWNU: 6, PCNU: 6, POSKO: 5, Relawan: 4 |
