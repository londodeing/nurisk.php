# UI_RULES.md — Standar Antarmuka Pengguna NURISK

> **Status**: Freeze — tidak boleh diubah tanpa persetujuan arsitektur.
> **Berlaku sejak**: 2026-06-16
> **Scope**: Seluruh modul NURISK (Laravel Monolith, Blade SSR)

---

## 1. Teknologi UI yang Difreeze

### 1.1 Wajib Digunakan

| Teknologi | Versi | Fungsi |
|---|---|---|
| Bootstrap | 5.3 | Satu-satunya CSS framework yang diizinkan |
| Laravel Blade | (bundled Laravel) | Template engine, Server-Side Rendering |
| Leaflet.js | ≥ 1.9 | Peta interaktif insiden |
| Vanilla JavaScript | ES6+ | Interaksi ringan tanpa framework |
| Chart.js | ≥ 4.x | Grafik statistik pada dashboard |
| Leaflet.markercluster | latest | Clustering marker peta |

### 1.2 Dilarang Digunakan

- **JavaScript Framework SPA**: React, Vue, Angular, Svelte, Alpine.js kompleks
- **CSS Framework alternatif**: Tailwind CSS, Bulma, Foundation
- **Real-time kompleks**: WebSocket (Pusher, Laravel Echo), Socket.io
- **Enterprise GIS berbayar**: ArcGIS, Mapbox Premium, Google Maps API berbayar
- **Plugin tabel berat**: DataTables.js (gunakan pagination Laravel native)
- **3D Map**: CesiumJS, deck.gl

> **Alasan freeze**: Sistem harus dapat dioperasikan di jaringan lambat (2G/3G), perangkat keras lama milik relawan, dan tanpa kebutuhan build process Node.js yang kompleks.

---

## 2. Struktur Layout Blade

### 2.1 Layout Master Internal (`app.blade.php`)

**File**: `resources/views/layouts/app.blade.php`

Digunakan oleh seluruh halaman yang memerlukan autentikasi.

**Komponen wajib dalam layout ini**:

```
┌─────────────────────────────────────────────────────┐
│ TOP NAVBAR                                          │
│ [Logo NURISK] [Scope Wilayah Aktif] [User] [Notif] │
├──────────────┬──────────────────────────────────────┤
│              │                                      │
│   SIDEBAR    │   MAIN CONTENT AREA                  │
│   (menu per  │   @yield('content')                  │
│    role)     │                                      │
│              │                                      │
├──────────────┴──────────────────────────────────────┤
│ FOOTER                                              │
└─────────────────────────────────────────────────────┘
```

**Sidebar**: Render menu berdasarkan role aktif pengguna (`auth()->user()->getRoleNames()`). Menu yang tidak sesuai role tidak boleh dirender (bukan hanya disembunyikan via CSS).

**Top Navbar wajib menampilkan**:
- Logo NURISK
- Nama scope wilayah aktif (nama PCNU atau "PWNU Jawa Tengah") — diambil dari `auth_pengguna_profil.scope_type` dan relasi terkait
- Nama lengkap pengguna (`auth_pengguna_profil.nama_lengkap`)
- Badge notifikasi (jika ada)
- Tombol logout

### 2.2 Layout Publik (`public.blade.php`)

**File**: `resources/views/layouts/public.blade.php`

Digunakan oleh halaman tanpa autentikasi.

**Komponen wajib**:
- Header: Logo NURISK / NU, tagline singkat
- Main content area: `@yield('content')`
- Footer: Nama lembaga, kontak darurat, tahun

### 2.3 Aturan Pemisahan Layout

- Route group internal (prefix: `/`) menggunakan `app.blade.php`
- Route group publik (prefix: `/publik` atau tanpa prefix untuk halaman landing) menggunakan `public.blade.php`
- Keduanya **tidak boleh dicampur** dalam satu route group
- Middleware `auth` dan `role` **wajib** dipasang di route group internal

---

## 3. Command Center (Dashboard Utama Internal)

**Route**: `/dashboard` (dilindungi autentikasi)
**Akses**: `super_admin`, `pwnu`, `pcnu`, relawan yang memiliki penugasan aktif

### 3.1 Tata Letak Command Center

```
┌────────────────────────────────────────────────────────────┐
│ TOP STATS BAR: [Insiden Aktif] [Personel Lapangan] [Stok] │
├──────────────────────────┬─────────────────────────────────┤
│                          │ PANEL KANAN:                    │
│   PETA LEAFLET.JS        │ - Daftar insiden terkini       │
│   (main content ~60%)    │ - Stok kritis gudang           │
│                          │ - Pos aju aktif                │
└──────────────────────────┴─────────────────────────────────┘
```

### 3.2 Data yang Ditampilkan

| Widget | Sumber Data | Keterangan |
|---|---|---|
| Marker insiden di peta | `laporan_kejadian.latitude`, `laporan_kejadian.longitude` | Filter: insiden dengan status `respon` atau `pemulihan` |
| Jumlah insiden per status | `operasi_insiden.status_insiden` | Hitung berdasarkan enum: `draft`, `terverifikasi`, `respon`, `pemulihan`, `selesai`, `dibatalkan` |
| Personel di lapangan | `operasi_mobilisasi_personil` | Hitung record dengan status `aktif` |
| Stok kritis gudang | `logistik_stok.jumlah_tersedia` | Tampilkan baris dengan nilai di bawah threshold |
| Pos aju aktif | `operasi_posaju` | Tampilkan daftar pos aju yang sedang beroperasi |

### 3.3 Refresh Data

- Mekanisme: **AJAX polling** menggunakan `setInterval` Vanilla JavaScript
- Interval: setiap **30 detik**
- Endpoint: route terpisah yang mengembalikan JSON ringkas
- Animasi refresh: spinner Bootstrap saat request berlangsung
- **Dilarang** menggunakan WebSocket, Pusher, atau Laravel Echo

---

## 4. Aturan Peta (Leaflet.js)

### 4.1 Konfigurasi Wajib

- **Base layer**: OpenStreetMap (`https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`)
- **Marker clustering**: wajib menggunakan plugin `Leaflet.markercluster`
- **Lazy loading marker**: hanya memuat marker yang berada dalam viewport atau bounding box peta saat ini (request ke endpoint API yang menerima parameter `bounds`)
- **Custom icon**: setiap jenis bencana menggunakan ikon dari kolom `bencana_master_jenis.ikon_map`
- **Layer control**: filter tampilan marker berdasarkan kolom `operasi_insiden.status_insiden`

### 4.2 Konten Popup Marker

Setiap marker insiden menampilkan popup dengan isi:

```
Kode Kejadian : [laporan_kejadian.kode_kejadian]
Status        : [operasi_insiden.status_insiden] (badge berwarna)
Jenis Bencana : [bencana_master_jenis.nama_jenis]
Waktu Mulai   : [operasi_insiden.waktu_mulai]
[Tombol → Detail Insiden]
```

### 4.3 Hal yang Dilarang di Peta

- Menggunakan tile provider berbayar (Google Maps API, Mapbox berbayar)
- GPS tracking real-time (live telemetry)
- Render 3D terrain atau bangunan
- Load seluruh marker sekaligus tanpa pagination/lazy loading

---

## 5. Dashboard Publik

**URL**: `/publik`
**Akses**: Tanpa login (role `publik` atau anonymous)

### 5.1 Konten yang Ditampilkan

| Konten | Sumber Data | Keterangan |
|---|---|---|
| Peta insiden aktif | `laporan_kejadian` + `operasi_insiden` | Filter: `status_insiden` = `respon` atau `pemulihan` |
| Statistik korban | `assessment_dampak_manusia` via `operasi_sitrep` | Hanya dari sitrep dengan `status_sitrep` = `final` |
| Informasi pos aju | `operasi_posaju` | Hanya nama dan lokasi, tanpa detail operasional |
| Form laporan kejadian | `laporan_kejadian` | Formulir pelaporan untuk masyarakat umum |

### 5.2 Data yang TIDAK Boleh Ditampilkan di Halaman Publik

- Data logistik (`logistik_stok`, `logistik_mutasi`, `logistik_permintaan`)
- Data personel operasi (`operasi_mobilisasi_personil`, `operasi_penugasan`)
- Detail internal operasi (`operasi_klaster`, `operasi_jurnal`, `operasi_pleno`)
- Koordinat detail yang dapat mengekspos lokasi sensitif

---

## 6. Aturan UI Modul Insiden

### 6.1 Halaman List Insiden

- Tampilan: tabel dengan kolom `kode_insiden`, `jenis_bencana`, `lokasi`, `status_insiden`, `waktu_mulai`, `aksi`
- Filter tersedia: status insiden, jenis bencana, rentang tanggal, scope wilayah
- Pagination: wajib (15 record per halaman, default)
- Urutan default: `waktu_mulai` DESC

### 6.2 Halaman Detail Insiden

Gunakan **tab-based layout** Bootstrap (komponen `nav-tabs`) dengan tab berikut:

| Tab | Konten |
|---|---|
| Info Umum | Data dasar insiden, peta lokasi mini, status, kronologis |
| Assessment | Daftar `assessment_utama` dan detail `assessment_dampak_manusia` |
| Sitrep | Daftar `operasi_sitrep` per insiden |
| Klaster | Daftar `operasi_klaster` dan koordinator (`operasi_klaster_koordinator`) |
| Logistik | Permintaan dan mutasi logistik terkait insiden |
| Personel | Daftar `operasi_mobilisasi_personil` yang ditugaskan |
| Jurnal | Daftar `operasi_jurnal` chronologis |

### 6.3 Badge Status Insiden

Warna badge menggunakan class Bootstrap:

| Status (`status_insiden`) | Warna Bootstrap Class | Label Tampil |
|---|---|---|
| `draft` | `badge bg-secondary` | Draft |
| `terverifikasi` | `badge bg-primary` | Terverifikasi |
| `respon` | `badge bg-warning text-dark` | Respon Aktif |
| `pemulihan` | `badge bg-warning text-dark` | Pemulihan |
| `selesai` | `badge bg-success` | Selesai |
| `dibatalkan` | `badge bg-danger` | Dibatalkan |

> Catatan: `respon` dan `pemulihan` menggunakan warna yang berbeda (`bg-orange` custom atau `bg-warning`) — tim frontend mendefinisikan satu custom CSS class `badge-respon` dan `badge-pemulihan` jika diperlukan.

### 6.4 Kontrol Transisi Status

- Tombol transisi status **hanya dirender** jika:
  1. Role pengguna memiliki izin transisi state tersebut
  2. Status insiden saat ini memang memungkinkan transisi ke state berikutnya (state machine)
- Tombol tidak boleh hanya disembunyikan via CSS — tidak dirender sama sekali jika tidak relevan

### 6.5 Insiden Terkunci (`is_locked = 1`)

- Tampilkan banner full-width di atas konten: **`⚠ INSIDEN TERKUNCI — Tidak dapat dimodifikasi`** (warna `alert-danger`)
- Semua form input dalam halaman detail harus dalam kondisi `disabled` dan `readonly`
- Tombol aksi (edit, tambah data) tidak dirender

---

## 7. Aturan UI Modul Sitrep

### 7.1 Halaman List Sitrep

- Diakses dari tab Sitrep pada halaman detail insiden
- Urutan: `nomor_sitrep` DESC (sitrep terbaru di atas)
- Kolom: `nomor_sitrep`, `status_sitrep`, `dibuat_pada`, `dibuat_oleh`, `aksi`

### 7.2 Badge Status Sitrep

| Status (`status_sitrep`) | Warna | Label |
|---|---|---|
| `draft` | `badge bg-secondary` | Draft |
| `ditinjau` | `badge bg-warning text-dark` | Sedang Ditinjau |
| `final` | `badge bg-success` + ikon gembok 🔒 | Final |

### 7.3 Sitrep Berstatus Final

- Seluruh field ditampilkan dalam mode **readonly** (bukan form input)
- Tampilkan tombol **`Download PDF`** (memanggil route generate PDF)
- Tampilkan nilai `hash_snapshot` dalam box kode kecil (`<code>`) untuk keperluan audit
- Tidak ada tombol edit atau hapus yang dirender

### 7.4 Form Pengisian Sitrep

Field yang wajib tersedia di form sitrep:

| Field | Kolom | Tipe Input |
|---|---|---|
| Kondisi Umum | `operasi_sitrep.kondisi_umum` | `<textarea>` |
| Upaya Penanganan | `operasi_sitrep.upaya_penanganan` | `<textarea>` |
| Kendala Lapangan | `operasi_sitrep.kendala_lapangan` | `<textarea>` |
| Kebutuhan Mendesak | `assessment_kebutuhan_mendesak` (relasi) | Komponen repeater sederhana |

### 7.5 Informasi Snapshot

Setelah sitrep berhasil disubmit (status berubah ke `final`), tampilkan informasi:
- Nilai `hash_snapshot` yang baru di-generate oleh trigger database
- Pesan konfirmasi bahwa data telah dibekukan

---

## 8. Aturan UI Modul Surat

### 8.1 Halaman List Surat

- Filter: jenis surat (`master_surat_jenis.nama_jenis`), status, rentang tanggal
- Kolom: nomor surat, jenis, perihal, status, tanggal dibuat, aksi

### 8.2 Halaman Detail Surat

- Tampilan preview surat menggunakan layout yang menyerupai format surat resmi (letterhead, kop surat, badan surat, tanda tangan)
- Data diambil dari `operasi_surat_keluar` dan template dari `master_surat_template`
- Tembusan ditampilkan di bagian bawah preview (dari tabel `dokumen_surat_tembusan`)

### 8.3 Badge dan Progress Status Surat

Status surat ditampilkan sebagai **progress bar bertahap** (step indicator horizontal):

```
[Draft] → [Pengajuan] → [Paraf] → [Penandatanganan] → [Final]
```

Gunakan Bootstrap `nav nav-pills` atau custom step indicator. Highlight tahap aktif saat ini.

### 8.4 Timeline Paraf

- Tampilkan timeline vertikal approval paraf dari tabel `dokumen_surat_paraf`
- Setiap entry timeline menampilkan: nama pemaraf, jabatan, waktu paraf, status paraf
- Timeline menggunakan list group Bootstrap dengan ikon status

### 8.5 Surat Berstatus Finalized

- Seluruh konten ditampilkan dalam mode **readonly**
- Tampilkan **watermark teks "FINAL"** secara diagonal pada preview surat (via CSS `position: absolute; opacity: 0.1; transform: rotate(-45deg)`)
- Tombol **`Generate PDF`** hanya dirender jika `status` = `FINALIZED`
- Tombol edit dan hapus tidak dirender

---

## 9. Aturan UI Modul Logistik

### 9.1 Dashboard Stok per Gudang

- Tampilan tabel per gudang (`logistik_gudang`)
- Kolom: nama barang (`logistik_barang_katalog.nama_barang`), kategori, jumlah tersedia (`logistik_stok.jumlah_tersedia`), satuan (`master_satuan.nama_satuan`), kondisi
- **Alert stok kritis**: baris dengan `jumlah_tersedia` di bawah nilai threshold disorot dengan background merah muda (`table-danger`) dan badge `⚠ Stok Kritis`
- Nilai threshold ditentukan per barang atau global (dikonfigurasi di sistem)

### 9.2 Log Mutasi Logistik

- Tampilan tabel chronologis dari `logistik_mutasi`
- Kolom: tanggal, barang, tipe mutasi, jumlah, asal/tujuan, keterangan, dicatat oleh
- Tipe mutasi ditampilkan sebagai badge:
  - `masuk`: `badge bg-success`
  - `keluar`: `badge bg-danger`
  - `penyesuaian`: `badge bg-info`

### 9.3 Form Mutasi Logistik

Field yang wajib tersedia:

| Field | Kolom | Tipe Input |
|---|---|---|
| Barang | `logistik_mutasi.barang_id` | `<select>` (dari `logistik_barang_katalog`) |
| Tipe Mutasi | `logistik_mutasi.tipe_mutasi` | Radio button: `masuk` / `keluar` / `penyesuaian` |
| Jumlah | `logistik_mutasi.jumlah` | `<input type="number">` |
| Asal/Tujuan | `logistik_mutasi.asal_tujuan` | `<input type="text">` |
| Keterangan | `logistik_mutasi.keterangan` | `<textarea>` |

### 9.4 Manajemen Permintaan Logistik

- Tampilan: **Kanban board sederhana** (kolom per status permintaan)
- Kolom Kanban: `draft` → `diajukan` → `disetujui` → `dikirim` → `selesai`
- Setiap card menampilkan: nama barang, jumlah, pemohon, tanggal
- Implementasi Kanban: menggunakan grid Bootstrap, bukan library drag-and-drop eksternal
- Perpindahan status: via tombol aksi (bukan drag-and-drop) untuk memastikan kompatibilitas jaringan lambat

---

## 10. Aturan UI Modul Pengungsian

**Catatan**: Data pengungsian bersumber dari tabel `assessment_dampak_manusia` yang terhubung dengan `assessment_utama` dan hanya ditampilkan dari sitrep berstatus `final`.

### 10.1 Panel Statistik Korban

Tampilkan dalam card/widget:

| Metrik | Kolom Sumber |
|---|---|
| Meninggal | `assessment_dampak_manusia.meninggal` |
| Hilang | `assessment_dampak_manusia.hilang` |
| Menderita / Mengungsi | `assessment_dampak_manusia.menderita_mengungsi` |
| Luka Berat | `assessment_dampak_manusia.luka_berat` |
| Luka Ringan | `assessment_dampak_manusia.luka_ringan` |

### 10.2 Status Pengungsian

- Tampilkan daftar pos pengungsian aktif dengan badge status
- Kapasitas: indikator progress bar `jumlah_terisi / kapasitas_total`
- Kebutuhan harian: tampilkan dalam format checklist atau tabel per hari

---

## 11. Aturan Umum Form

Semua form di seluruh modul NURISK mengikuti aturan berikut:

### 11.1 Wajib Ada

```blade
<form method="POST" action="{{ route('...') }}">
    @csrf
    {{-- Untuk PUT/PATCH/DELETE --}}
    @method('PUT')

    {{-- Error display per field --}}
    @error('nama_field')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</form>
```

### 11.2 Penanda Field Wajib

- Field yang required diberi tanda **asterisk merah** `<span class="text-danger">*</span>` di sebelah label
- Contoh: `<label>Nama Kejadian <span class="text-danger">*</span></label>`

### 11.3 Submit Button

- Saat form sedang disubmit (loading), tombol submit harus dinonaktifkan (`disabled`) via Vanilla JavaScript
- Tampilkan spinner Bootstrap di dalam tombol saat loading:

```html
<button type="submit" id="btn-submit" class="btn btn-primary">
    <span class="spinner-border spinner-border-sm d-none" id="spinner"></span>
    Simpan
</button>
<script>
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('btn-submit').disabled = true;
    document.getElementById('spinner').classList.remove('d-none');
});
</script>
```

### 11.4 Konfirmasi Hapus

- **Dilarang** menggunakan `window.confirm()` browser native
- Wajib menggunakan **Bootstrap Modal** sebagai dialog konfirmasi hapus
- Modal harus menampilkan nama atau identitas record yang akan dihapus

```blade
<!-- Tombol trigger -->
<button type="button" class="btn btn-danger btn-sm"
    data-bs-toggle="modal"
    data-bs-target="#modal-hapus"
    data-nama="{{ $record->nama }}">
    Hapus
</button>

<!-- Modal konfirmasi -->
<div class="modal fade" id="modal-hapus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
            </div>
            <div class="modal-body">
                Yakin ingin menghapus <strong id="nama-record"></strong>?
                Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="form-hapus" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
```

---

## 12. Aksesibilitas dan Performa

### 12.1 Prinsip Dasar

- UI harus dapat diakses dan fungsional di jaringan lambat (2G/3G, <1 Mbps)
- Target browser: Chrome/Firefox versi 2 tahun terakhir
- Orientasi: **Desktop-first** (tetap responsive untuk tablet, tidak perlu mobile-first)

### 12.2 Aturan Aset

| Jenis Aset | Aturan |
|---|---|
| Gambar | Wajib dikompresi, gunakan format WebP jika memungkinkan |
| Gambar | Wajib menggunakan `loading="lazy"` untuk gambar di luar viewport |
| Video/animasi latar | **Dilarang** |
| Font eksternal | Minimalkan; jika perlu, gunakan Google Fonts dengan `font-display: swap` |
| JavaScript bundle | Gunakan CDN dengan versi yang di-pin; tidak ada build Webpack/Vite besar |

### 12.3 Pagination

- Semua halaman yang menampilkan list data **wajib** menggunakan pagination Laravel
- Default: 15 record per halaman (dapat dikonfigurasi per modul)
- Gunakan `{{ $data->links() }}` dengan view Bootstrap yang sudah dikustomisasi

### 12.4 Loading State

- Tampilkan spinner Bootstrap (`<div class="spinner-border">`) saat menunggu request AJAX
- Gunakan `class="placeholder-glow"` Bootstrap untuk skeleton loading jika relevan

---

## 13. Navigasi Menu per Role

Menu sidebar dirender secara kondisional berdasarkan role aktif. Gunakan `@role('nama_role')` dari package Spatie Laravel-Permission.

### 13.1 `super_admin`

```
├── Dashboard Command Center
├── Manajemen Insiden (semua wilayah)
├── Assessment & Sitrep
├── Logistik (semua gudang)
├── Relawan
├── Pleno
├── Surat Menyurat
├── Eskalasi
├── Laporan
└── [ADMIN]
    ├── Manajemen Pengguna (auth_users, auth_pengguna_profil)
    ├── Manajemen Role & Permission
    ├── Master Data (bencana_master_jenis, logistik_kategori, dst.)
    └── System Settings
```

### 13.2 `pwnu`

```
├── Dashboard Command Center
├── Insiden (semua PCNU di bawah PWNU Jawa Tengah)
├── Pleno
├── Surat Menyurat
├── Logistik (semua gudang)
├── Relawan
├── Eskalasi
└── Laporan & Sitrep
```

### 13.3 `pcnu`

```
├── Dashboard Command Center (scope PCNU sendiri)
├── Insiden (scope PCNU sendiri)
├── Assessment & Sitrep
├── Logistik (gudang PCNU sendiri)
├── Relawan (verifikasi pendaftaran)
└── Pos Aju
```

### 13.4 `relawan`

```
├── Dashboard (insiden yang ditugaskan)
├── Assessment (jika ditugaskan sebagai TRC)
├── Sitrep (jika ditugaskan)
├── Jurnal Operasi
└── Profil Saya
```

### 13.5 `publik`

```
├── Peta Publik (/publik)
└── Laporkan Kejadian (form laporan_kejadian)
```

> **Catatan Teknis**: Menu `publik` tidak menggunakan sidebar. Layout yang digunakan adalah `public.blade.php` dengan navigasi header horizontal sederhana.

---

## 14. Konvensi Penamaan File Blade

Seluruh file view Blade mengikuti konvensi berikut:

```
resources/views/
├── layouts/
│   ├── app.blade.php              # Layout internal (auth)
│   └── public.blade.php           # Layout publik
├── components/
│   ├── alert.blade.php            # Alert/flash message
│   ├── badge-status.blade.php     # Badge status reusable
│   ├── confirm-modal.blade.php    # Modal konfirmasi hapus
│   └── pagination.blade.php       # Wrapper pagination
├── insiden/
│   ├── index.blade.php            # List insiden
│   ├── show.blade.php             # Detail insiden (tab-based)
│   ├── create.blade.php           # Form buat insiden baru
│   └── edit.blade.php             # Form edit insiden
├── sitrep/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── create.blade.php
├── logistik/
│   ├── stok/index.blade.php
│   ├── mutasi/index.blade.php
│   └── permintaan/index.blade.php
├── surat/
│   ├── index.blade.php
│   └── show.blade.php
├── dashboard/
│   ├── command-center.blade.php   # Dashboard internal
│   └── publik.blade.php           # Dashboard publik
└── auth/
    ├── login.blade.php
    └── register.blade.php         # Jika ada self-registration relawan
```

---

## 15. Referensi Warna dan Class Bootstrap Standar

| Konteks | Class Bootstrap |
|---|---|
| Aksi primer (simpan, submit) | `btn btn-primary` |
| Aksi sekunder (batal, kembali) | `btn btn-secondary` |
| Aksi hapus | `btn btn-danger` |
| Aksi download/cetak | `btn btn-outline-secondary` |
| Alert sukses | `alert alert-success` |
| Alert error/gagal | `alert alert-danger` |
| Alert peringatan | `alert alert-warning` |
| Alert informasi | `alert alert-info` |
| Tabel data | `table table-bordered table-hover table-sm` |
| Card widget | `card shadow-sm` |
| Banner terkunci | `alert alert-danger fw-bold` |

---

*Dokumen ini adalah standar teknikal yang bersifat mengikat untuk seluruh pengembang frontend NURISK. Perubahan hanya dapat dilakukan melalui proses review arsitektur.*
