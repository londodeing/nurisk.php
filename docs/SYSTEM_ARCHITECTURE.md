# SYSTEM_ARCHITECTURE.md — NURISK

> **Versi Dokumen:** 1.0.0  
> **Tanggal:** 2026-06-16  
> **Status:** Draft Pra-Produksi  
> **Bahasa:** Bahasa Indonesia (Teknikal)

---

## 1. Arsitektur Utama

NURISK dibangun sebagai **Laravel Monolith** — satu codebase, satu deployment, satu proses aplikasi. Tidak ada pemisahan layanan, tidak ada API gateway, tidak ada microservice. Semua domain berjalan di dalam satu proses Laravel yang sama.

### 1.1 Stack Teknologi

| Layer | Teknologi | Keterangan |
|---|---|---|
| Backend Framework | Laravel (PHP) | Monolith, satu codebase |
| Rendering | Blade SSR | Server-Side Rendering, bukan SPA |
| UI Framework | Bootstrap 5.3 | CSS utility, komponen HTML server-rendered |
| Database | MySQL InnoDB | Relasional, transaksional, foreign key aktif |
| Peta Interaktif | Leaflet.js | Embedded di Blade view, data dari controller |
| Job Async | Laravel Queue (PHP Queue) | Job ringan: notifikasi, ekspor laporan, kirim email |
| Autentikasi | Laravel Session-based Auth + Sanctum (opsional API) | Session cookie untuk web, Sanctum untuk token API jika dibutuhkan |
| Role & Permission | Spatie Laravel Permission | Model: `model_has_roles`, `model_has_permissions` |

### 1.2 Prinsip Arsitektur

- **Satu Codebase, Satu Deployment**: Semua domain dikompilasi dan dijalankan dari satu proses `artisan serve` atau `php-fpm`.
- **Blade SSR**: Setiap halaman dirender di server. Data dikirim dari Controller ke Blade view via `compact()` atau `->with()`. Tidak ada fetch API client-side untuk rendering halaman utama.
- **Bootstrap 5.3**: Seluruh antarmuka menggunakan komponen Bootstrap (grid, card, modal, badge, tabel). Tidak ada custom CSS framework lain.
- **MySQL InnoDB**: Semua tabel menggunakan engine InnoDB. Foreign key constraint aktif. Transaksi database digunakan untuk operasi multi-tabel.
- **Leaflet.js**: Digunakan untuk visualisasi titik bencana, lokasi pos aju, dan sebaran pengungsi. Data koordinat diambil dari kolom `koordinat_lat`/`koordinat_lng` pada tabel terkait, dikirim ke view sebagai JSON dari controller.
- **PHP Queue**: Digunakan untuk job async ringan seperti pengiriman notifikasi, ekspor PDF sitrep, dan sinkronisasi data non-kritis. Menggunakan driver `database` (tabel `jobs`, `failed_jobs`, `job_batches`).
- **Autentikasi Session-based & Token-based**: Login web menggunakan session cookie, sedangkan aplikasi Flutter mobile menggunakan Laravel Sanctum untuk stateless API token-based auth.

### 1.3 Official Architecture Decision — Hybrid Monolith Architecture

NURISK secara resmi menggunakan pola **Hybrid Monolith Architecture**. Dalam arsitektur ini, Laravel Backend bertindak sebagai *single source of truth* yang melayani dua jenis *consumer* utama secara paralel: antarmuka web berbasis Blade SSR dan aplikasi mobile berbasis Flutter Client via REST API. 

Pola ini dirancang untuk memaksimalkan efisiensi pengembangan tanpa mengorbankan fleksibilitas akses lapangan. REST API bukan lagi dianggap sebagai penyimpangan atau deviasi arsitektur, melainkan **keputusan arsitektur resmi** yang diintegrasikan penuh ke dalam sistem NURISK.

Arsitektur ini terbagi menjadi tiga komponen utama:

#### A. Web Layer (Server-Side Rendering)
Web Layer berbasis Blade SSR + Bootstrap 5.3 digunakan untuk fungsi-fungsi administratif strategis, governance, dan monitoring terpusat, antara lain:
- **Admin Panel & User Management**: Pengelolaan akun, peran (*roles*), dan penugasan jabatan struktural.
- **Master Data**: Seeders dan katalog referensi (jenis bencana, satuan logistik, kategori aset, dll.).
- **Governance**: Rapat pleno pengambilan keputusan resmi, alur penandatanganan dan paraf berantai surat dinas keluar.
- **Command Center & Dashboard**: Visualisasi ringkasan peta bencana terpadu (Leaflet.js) dan grafik analitik secara read-only.

#### B. API Layer (REST API)
API Layer menyediakan *endpoint* RESTful stateless berbasis JSON menggunakan Laravel Resource. API Layer wajib diimplementasikan untuk seluruh domain operasional lapangan guna mendukung mobilitas tinggi, meliputi:
- **Operasi**: Manajemen dinamis Pos Aju, Klaster, dan Tugas Mikro.
- **Relawan**: Kebutuhan relawan, registrasi mandiri, seleksi/approval, penugasan, dan shift.
- **Assessment**: Pengisian kaji cepat dampak manusia dan kebutuhan mendesak di lokasi bencana.
- **Sitrep**: Pembuatan laporan situasi berkala dan snapshots.
- **Logistik**: Permintaan barang, mutasi stok antar gudang, dan pemantauan kartu stok.
- **Aset**: Peminjaman dan pengembalian unit aset operasional.

#### C. Mobile Layer (Flutter Mobile Client)
Aplikasi mobile berbasis Flutter bertindak sebagai *consumer* utama REST API. Flutter client digunakan oleh relawan, TRC, dan komandan posko di lapangan untuk melakukan input data secara langsung (*real-time*), sinkronisasi keahlian, pelaporan insiden, tracking mobilisasi, sensus harian pengungsian, serta manajemen logistik di lokasi bencana.

---


## 2. Boundary Domain Sistem (18 Domain)

Setiap domain memiliki batas tanggung jawab yang jelas. Lintas domain dilakukan melalui foreign key atau service class sederhana (bukan repository pattern atau event sourcing).

---

### 2.1 Domain: `auth`

**Fungsi:** Manajemen identitas pengguna, autentikasi, otorisasi berbasis role, dan pengelolaan profil serta keahlian pengguna.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `auth_users` | Akun pengguna: email, password, status akun, scope wilayah default |
| `auth_roles` | Definisi 5 role sistem |
| `auth_pengguna_profil` | Data profil personal: nama lengkap, NIK, no. HP, foto |
| `auth_keahlian_master` | Katalog keahlian yang tersedia (SAR, medis, logistik, dll.) |
| `auth_pengguna_keahlian` | Relasi many-to-many: pengguna ↔ keahlian |
| `model_has_roles` | Spatie: binding role ke user (polymorphic) |
| `model_has_permissions` | Spatie: binding permission ke user (polymorphic) |
| `pengguna_jabatan` | Jabatan aktif pengguna dalam struktur organisasi NU |
| `master_jabatan` | Katalog posisi/jabatan yang tersedia |

**5 Role Sistem (FROZEN):**

| Role | Level | Deskripsi |
|---|---|---|
| `super_admin` | 1 | IT Support & Full System Control |
| `pwnu` | 2 | Pengguna level PWNU Jawa Tengah |
| `pcnu` | 3 | Pengguna level PCNU |
| `relawan` | 4 | Relawan terverifikasi NU |
| `publik` | 5 | Masyarakat umum / warga pelapor |

**Enum Status Akun:** `menunggu`, `aktif`, `nonaktif`, `suspend`  
**Enum Default Scope Type:** `pwnu`, `pcnu`, `mwc`, `ranting`, `lembaga`, `banom`

**Relasi ke Domain Lain:**
- `auth_users.id` direferensikan oleh hampir semua tabel operasional sebagai `user_id`, `dibuat_oleh`, `komandan_id`, dll.
- `auth_users.default_scope_type` + `auth_users.default_scope_id` → menentukan wilayah kerja pengguna untuk domain **organisasi**.
- `auth_pengguna_keahlian` → digunakan domain **relawan** dan **assignment** untuk mencocokkan kompetensi.

**Tanggung Jawab Domain:**
- Registrasi, verifikasi, dan aktivasi akun.
- Login/logout dan manajemen sesi.
- Binding role ke pengguna via Spatie Permission.
- Pengelolaan profil dan keahlian pengguna.

**Batas Domain:**
- Domain `auth` TIDAK mengelola struktur organisasi NU secara langsung — hanya menyimpan `default_scope_type` dan `default_scope_id` sebagai referensi wilayah.
- Domain `auth` TIDAK membuat atau mengelola operasi insiden.

---

### 2.2 Domain: `organisasi`

**Fungsi:** Representasi hierarki organisasi Nahdlatul Ulama sebagai konteks wilayah kerja pengguna dan operasi. Domain ini bersifat **referensial** — tidak memiliki tabel operasional sendiri, namun menjadi acuan scope di seluruh sistem.

**Tabel Terkait:**

> Tabel organisasi struktural (PWNU, PCNU, MWC, Ranting) belum dikonfirmasi dari SQL dump. Scope wilayah saat ini direpresentasikan via kolom `default_scope_type` dan `default_scope_id` di tabel `auth_users`.

| Kolom (di `auth_users`) | Fungsi |
|---|---|
| `default_scope_type` | Jenis wilayah: `pwnu`, `pcnu`, `mwc`, `ranting`, `lembaga`, `banom` |
| `default_scope_id` | ID entitas wilayah yang bersangkutan |

**Relasi ke Domain Lain:**
- Setiap pengguna memiliki scope wilayah yang menentukan data apa saja yang bisa dilihat/dikelola.
- Operasi insiden, logistik, dan relawan dapat difilter berdasarkan scope wilayah ini.
- Domain **pleno** dan **surat_menyurat** menggunakan scope untuk menentukan kewenangan penandatanganan.

**Tanggung Jawab Domain:**
- Menyediakan referensi hierarki wilayah NU (PWNU → PCNU → MWC → Ranting).
- Menjadi dasar filter data berdasarkan wilayah.

**Batas Domain:**
- Domain ini TIDAK mengelola operasi bencana.
- Jika tabel organisasi struktural (mis. `organisasi_wilayah`) belum ada di SQL dump, maka implementasi scope menggunakan enum `default_scope_type` di `auth_users`.

---

### 2.3 Domain: `insiden`

**Fungsi:** Inti sistem — manajemen siklus hidup kejadian bencana dari laporan publik hingga selesai.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `operasi_insiden` | Data induk insiden: judul, lokasi, koordinat, status, jenis bencana |
| `laporan_kejadian` | Laporan awal dari publik atau relawan sebagai pemicu insiden |
| `riwayat_status_insiden` | Log perubahan status insiden (audit trail) |
| `bencana_master_jenis` | Katalog jenis bencana: alam, non_alam, sosial |

**Enum Status Insiden:** `draft` → `terverifikasi` → `respon` → `pemulihan` → `selesai` / `dibatalkan`

**Enum Kategori Bencana:** `alam`, `non_alam`, `sosial`

**Relasi ke Domain Lain:**
- `operasi_insiden.id` → direferensikan oleh hampir semua domain operasional (assessment, sitrep, pleno, assignment, logistik, pos_aju, dll.).
- `laporan_kejadian` → sumber data awal untuk membuat `operasi_insiden`.
- `riwayat_status_insiden` → dibaca oleh domain **audit**.

**Tanggung Jawab Domain:**
- Menerima laporan kejadian dari publik (`laporan_kejadian`).
- Membuat dan mengelola record insiden (`operasi_insiden`).
- Memvalidasi laporan dan mengubah status insiden.
- Menyimpan histori perubahan status ke `riwayat_status_insiden`.

**Batas Domain:**
- Domain `insiden` TIDAK mengelola respons operasional secara langsung — itu tanggung jawab domain **assignment**, **mobilisasi_personel**, **logistik**, dll.
- Domain `insiden` TIDAK menghasilkan sitrep — itu tanggung jawab domain **sitrep**.

---

### 2.4 Domain: `assessment`

**Fungsi:** Kajian lapangan untuk mendapatkan data dampak bencana dan kebutuhan mendesak sebagai dasar pengambilan keputusan.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `assessment_utama` | Header assessment: insiden terkait, jenis, tanggal, petugas |
| `assessment_dampak_manusia` | Rincian dampak: korban jiwa, luka, pengungsi, hilang |
| `assessment_kebutuhan_mendesak` | Kebutuhan prioritas: logistik, medis, shelter, dll. |

**Enum Jenis Assessment:** `kaji_cepat`, `pendataan_lanjutan`

**Relasi ke Domain Lain:**
- `assessment_utama.insiden_id` → FK ke `operasi_insiden.id` (domain **insiden**).
- Data dari `assessment_kebutuhan_mendesak` → menjadi input domain **gap_kebutuhan** dan **logistik**.
- Data assessment → menjadi dasar konten `operasi_sitrep` (domain **sitrep**).

**Tanggung Jawab Domain:**
- Membuat dan mengelola form assessment lapangan.
- Menyimpan data dampak manusia dan kebutuhan mendesak.
- Menyediakan data untuk sitrep dan perencanaan logistik.

**Batas Domain:**
- Domain `assessment` TIDAK membuat sitrep — hanya menyediakan data sumber.
- Domain `assessment` TIDAK mengelola distribusi logistik.

---

### 2.5 Domain: `sitrep`

**Fungsi:** Laporan Situasi (Sitrep) resmi per periode operasi — dokumen formal yang merangkum kondisi terkini insiden.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `operasi_sitrep` | Data sitrep: nomor sitrep, insiden terkait, konten, status, pembuat |

**Enum Status Sitrep:** `draft`, `ditinjau`, `final`

**Relasi ke Domain Lain:**
- `operasi_sitrep.insiden_id` → FK ke `operasi_insiden.id` (domain **insiden**).
- Konten sitrep mengacu pada data dari domain **assessment**, **logistik**, dan **mobilisasi_personel**.
- Sitrep `final` dapat menjadi lampiran domain **surat_menyurat**.
- Sitrep menjadi bahan rapat domain **pleno**.

**Tanggung Jawab Domain:**
- Membuat, mengedit, dan memfinalisasi laporan sitrep.
- Penomoran sitrep berurutan per insiden.
- Transisi status: `draft` → `ditinjau` → `final`.

**Batas Domain:**
- Domain `sitrep` TIDAK mendistribusikan laporan — itu tanggung jawab domain **surat_menyurat**.
- Domain `sitrep` TIDAK membuat keputusan — itu tanggung jawab domain **pleno**.

---

### 2.6 Domain: `pleno`

**Fungsi:** Rapat pengambilan keputusan resmi dalam struktur komando bencana NU. Menghasilkan keputusan yang bersifat mengikat dan menjadi dasar eskalasi atau aktivasi operasi.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `operasi_pleno` | Header pleno: tanggal, insiden, agenda, status |
| `operasi_pleno_keputusan` | Daftar keputusan yang dihasilkan dari pleno |
| `operasi_pleno_peserta` | Daftar peserta yang hadir dalam pleno |
| `operasi_eskalasi` | Record eskalasi status insiden yang diputuskan lewat pleno |
| `operasi_aktivasi` | Record aktivasi klaster atau operasi yang diputuskan lewat pleno |

**Relasi ke Domain Lain:**
- `operasi_pleno.insiden_id` → FK ke `operasi_insiden.id` (domain **insiden**).
- Keputusan pleno (`operasi_pleno_keputusan`) → menjadi dasar pembuatan surat resmi di domain **surat_menyurat**.
- Eskalasi (`operasi_eskalasi`) → mengubah status di `operasi_insiden` (domain **insiden**).
- Aktivasi (`operasi_aktivasi`) → memicu domain **mobilisasi_personel** dan **logistik**.

**Tanggung Jawab Domain:**
- Membuat jadwal dan agenda pleno.
- Mencatat peserta dan keputusan.
- Memproses eskalasi dan aktivasi sebagai output pleno.

**Batas Domain:**
- Domain `pleno` TIDAK mengelola detail operasional lapangan.
- Keputusan pleno bersifat politis/strategis — implementasi teknis dilakukan domain lain.

---

### 2.7 Domain: `surat_menyurat`

**Fungsi:** Pengelolaan dokumen legal resmi — surat keluar, nota dinas, SK, dan dokumen formal lain yang dikeluarkan oleh PWNU/PCNU dalam konteks penanganan bencana.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `operasi_surat_keluar` | Data induk surat: nomor, perihal, tanggal, status |
| `dokumen_surat_paraf` | Daftar paraf pejabat pada surat |
| `dokumen_surat_tembusan` | Daftar penerima tembusan surat |
| `master_surat_jenis` | Katalog jenis surat yang tersedia |
| `master_surat_template` | Template konten surat per jenis |
| `master_jabatan_penandatangan` | Daftar jabatan yang berwenang menandatangani |

**Relasi ke Domain Lain:**
- Surat dapat merujuk ke `operasi_pleno_keputusan` (domain **pleno**) sebagai dasar hukum.
- Surat dapat merujuk ke `operasi_insiden.id` sebagai konteks kejadian.
- Surat ditandatangani oleh pejabat yang terdaftar di `pengguna_jabatan` (domain **auth**).

**Tanggung Jawab Domain:**
- Pembuatan surat dari template.
- Alur paraf digital (approval workflow).
- Penomoran surat otomatis.
- Pengelolaan tembusan dan distribusi.

**Batas Domain:**
- Domain `surat_menyurat` TIDAK membuat keputusan — hanya memformalisasi keputusan yang sudah ada.
- Domain ini TIDAK mengelola logistik atau personel.

---

### 2.8 Domain: `logistik`

**Fungsi:** Manajemen bantuan, peralatan, dan kebutuhan material untuk operasi bencana — dari perencanaan, stok, permintaan, hingga distribusi.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `logistik_barang_katalog` | Katalog barang: nama, kode, satuan, kategori |
| `logistik_kategori` | Kategori barang logistik |
| `logistik_gudang` | Data gudang/titik penyimpanan |
| `logistik_stok` | Stok barang per gudang atau pos aju |
| `logistik_mutasi` | Record pergerakan barang: masuk, keluar, penyesuaian |
| `logistik_permintaan` | Permintaan barang dari lapangan ke gudang |
| `logistik_perencanaan` | Rencana kebutuhan logistik per insiden atau klaster |
| `master_satuan` | Satuan barang: kg, liter, unit, karton, dll. |

**Enum Tipe Mutasi:** `masuk`, `keluar`, `penyesuaian`

**Relasi ke Domain Lain:**
- `logistik_stok` dapat terikat ke `operasi_posaju.id` (domain **pos_aju**) untuk stok di lapangan.
- `logistik_permintaan` dapat merujuk ke `operasi_insiden.id` (domain **insiden**).
- `logistik_perencanaan` mengacu data dari `assessment_kebutuhan_mendesak` (domain **assessment**).
- Mutasi logistik dicatat di `operasi_jurnal` (domain **audit**) untuk keperluan audit.

**Tanggung Jawab Domain:**
- Pengelolaan katalog barang dan satuan.
- Manajemen stok per gudang dan pos lapangan.
- Pencatatan setiap mutasi barang dengan tipe dan referensi.
- Pengelolaan permintaan dan perencanaan logistik.

**Batas Domain:**
- Domain `logistik` TIDAK mengelola aset tetap — itu tanggung jawab domain (aset, di luar 18 domain ini atau belum didefinisikan).
- Domain `logistik` TIDAK membuat keputusan distribusi strategis — itu lewat domain **pleno**.

---

### 2.9 Domain: `relawan`

**Fungsi:** Manajemen daur hidup relawan NU — dari pendaftaran, verifikasi, hingga penugasan ke operasi bencana.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `relawan_pendaftaran` | Data pendaftaran relawan: identitas, keahlian, wilayah, status verifikasi |
| `relawan_penugasan` | Record penugasan relawan ke insiden atau klaster tertentu |

**Relasi ke Domain Lain:**
- `relawan_pendaftaran.user_id` → FK ke `auth_users.id` (domain **auth**).
- `relawan_penugasan` terhubung ke `operasi_insiden.id` dan/atau `operasi_klaster.id` (domain **insiden** dan **mobilisasi_personel**).
- Keahlian relawan di `auth_pengguna_keahlian` menjadi dasar pencocokan untuk `relawan_penugasan`.

**Tanggung Jawab Domain:**
- Form pendaftaran relawan.
- Verifikasi dan aktivasi status relawan.
- Pencatatan penugasan relawan per insiden atau klaster.

**Batas Domain:**
- Domain `relawan` TIDAK sama dengan domain **assignment** — `relawan_penugasan` adalah penugasan khusus relawan terdaftar, sedangkan `operasi_penugasan` (domain **assignment**) mencakup semua personel termasuk staf struktural.
- Domain `relawan` TIDAK mengelola shift atau rotasi — itu domain **shift_operasional**.

---

### 2.10 Domain: `assignment`

**Fungsi:** Penugasan resmi personel (staf, relawan, koordinator) ke operasi insiden — menetapkan otoritas dan peran dalam struktur komando.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `operasi_penugasan` | Record penugasan: user, insiden, peran, status, tanggal |
| `operasi_otoritas_kontekstual` | Otoritas khusus yang diberikan dalam konteks insiden tertentu |

**Enum Status Penugasan:** `aktif` (default)  
**Enum Peran Otoritas Operasi:** `komandan_insiden`, `trc`, `relawan`, `medis`, `logistik`, `operator`

**Relasi ke Domain Lain:**
- `operasi_penugasan.insiden_id` → FK ke `operasi_insiden.id` (domain **insiden**).
- `operasi_penugasan.user_id` → FK ke `auth_users.id` (domain **auth**).
- `operasi_otoritas_kontekstual` → memberikan akses sementara untuk tindakan tertentu di domain lain.
- Penugasan menjadi prasyarat sebelum personel masuk ke domain **mobilisasi_personel**.

**Tanggung Jawab Domain:**
- Menetapkan personel ke insiden dengan peran tertentu.
- Mengelola otoritas kontekstual sementara.
- Menjadi gerbang akses untuk domain operasional lain.

**Batas Domain:**
- Domain `assignment` TIDAK mengelola pergerakan fisik personel — itu domain **mobilisasi_personel**.
- Domain `assignment` TIDAK mengelola jadwal shift — itu domain **shift_operasional**.

---

### 2.11 Domain: `mobilisasi_personel`

**Fungsi:** Pencatatan perpindahan personel ke dan dari lapangan — termasuk pengelompokan dalam klaster fungsional dan penunjukan koordinator klaster.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `operasi_mobilisasi_personil` | Record mobilisasi: siapa, ke mana, kapan, dengan apa |
| `operasi_klaster` | Kelompok fungsional dalam operasi (SAR, Medis, Logistik, dll.) |
| `operasi_klaster_koordinator` | Penunjukan koordinator per klaster |
| `operasi_master_klaster` | Katalog jenis klaster yang tersedia |
| `operasi_master_indikator` | Indikator kinerja per klaster |

**Relasi ke Domain Lain:**
- `operasi_mobilisasi_personil.insiden_id` → FK ke `operasi_insiden.id` (domain **insiden**).
- `operasi_klaster` terhubung ke `operasi_insiden.id`.
- Koordinator klaster diambil dari `auth_users` yang sudah ditugaskan di domain **assignment**.
- Klaster terhubung ke domain **shift_operasional** melalui `operasi_periode`.
- Klaster terhubung ke domain **pos_aju** melalui lokasi operasional.

**Tanggung Jawab Domain:**
- Mencatat mobilisasi personel ke lapangan.
- Membentuk dan mengelola klaster fungsional.
- Menunjuk koordinator klaster.
- Memantau indikator kinerja klaster.

**Batas Domain:**
- Domain ini TIDAK mengelola jadwal harian — itu domain **shift_operasional**.
- Domain ini TIDAK mengelola stok logistik — itu domain **logistik**.

---

### 2.12 Domain: `shift_operasional`

**Fungsi:** Pembagian waktu operasional harian di lapangan — mengatur rotasi personel dan periode aktif per pos atau klaster.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `operasi_periode` | Periode shift: nama, tanggal mulai, tanggal selesai, klaster/pos terkait |

**Relasi ke Domain Lain:**
- `operasi_periode.insiden_id` → FK ke `operasi_insiden.id` (domain **insiden**).
- `operasi_periode` dapat terhubung ke `operasi_klaster.id` (domain **mobilisasi_personel**).
- `operasi_periode` dapat terhubung ke `operasi_posaju.id` (domain **pos_aju**).

**Tanggung Jawab Domain:**
- Mendefinisikan periode operasional (shift pagi, siang, malam).
- Menghubungkan shift ke klaster atau pos aju.
- Menjadi kerangka waktu untuk pelaporan sitrep dan jurnal.

**Batas Domain:**
- Domain `shift_operasional` TIDAK mengelola konten laporan — hanya kerangka waktu.
- Domain ini TIDAK mengelola penugasan personel per shift secara individual (jika fitur tersebut ada, perlu tabel tambahan).

---

### 2.13 Domain: `pos_aju`

**Fungsi:** Manajemen Pos Komando Lapangan (Pos Aju) — titik operasional di lokasi bencana yang memiliki komandan, personel, dan stok logistik sendiri.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `operasi_posaju` | Data pos aju: nama, lokasi, koordinat, insiden terkait, status |
| `operasi_posaju_komandan` | Penunjukan komandan pos aju per periode |
| `logistik_stok` | Stok logistik yang berlokasi di pos aju (shared dengan domain logistik) |

**Relasi ke Domain Lain:**
- `operasi_posaju.insiden_id` → FK ke `operasi_insiden.id` (domain **insiden**).
- `operasi_posaju_komandan.user_id` → FK ke `auth_users.id` (domain **auth**).
- `logistik_stok` dapat merujuk ke `operasi_posaju.id` sebagai lokasi stok.
- Koordinat pos aju ditampilkan di Leaflet.js pada domain **command_center**.

**Tanggung Jawab Domain:**
- Membuat dan mengelola data pos aju.
- Menunjuk komandan pos aju.
- Memantau stok logistik di lapangan melalui `logistik_stok`.

**Batas Domain:**
- Domain `pos_aju` TIDAK mengelola stok secara mandiri — selalu melalui domain **logistik**.
- Domain ini TIDAK membuat keputusan strategis — itu domain **pleno**.

---

### 2.14 Domain: `pengungsian`

**Fungsi:** Manajemen data pengungsi — pencatatan jumlah, lokasi, kebutuhan, dan distribusi bantuan ke penerima manfaat.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `master_penerima_manfaat` | Katalog kategori penerima manfaat: lansia, balita, ibu hamil, disabilitas, dll. |

> **⚠️ CATATAN SINKRONISASI SQL:** Tabel utama pengungsian (misalnya `pengungsian_lokasi`, `pengungsian_penghuni`, atau sejenisnya) **belum dikonfirmasi** dari SQL dump yang tersedia. Yang terkonfirmasi hanya `master_penerima_manfaat` sebagai tabel referensi. Perlu verifikasi tabel operasional pengungsian dari dump lengkap.

**Relasi ke Domain Lain:**
- Data pengungsi terhubung ke `operasi_insiden.id` (domain **insiden**).
- Kebutuhan pengungsi menjadi input untuk domain **logistik** (distribusi bantuan).
- Data pengungsi menjadi konten sitrep di domain **sitrep**.

**Tanggung Jawab Domain:**
- Pencatatan lokasi dan kapasitas titik pengungsian.
- Pencatatan jumlah pengungsi per kategori (berdasarkan `master_penerima_manfaat`).
- Distribusi bantuan ke pengungsi.

**Batas Domain:**
- Domain `pengungsian` TIDAK mengelola stok logistik secara langsung — hanya mencatat permintaan dan distribusi.

---

### 2.15 Domain: `feedback_klaster`

**Fungsi:** Evaluasi pasca-respons per klaster fungsional — mengumpulkan umpan balik dari koordinator klaster tentang efektivitas operasi.

**Tabel Terkait:**

> **⚠️ CATATAN SINKRONISASI SQL:** Tabel untuk domain ini (misalnya `sistem_feedback` atau `operasi_feedback_klaster`) **belum dikonfirmasi** dari SQL dump. Domain ini ada dalam rancangan PRD tetapi belum memiliki tabel yang teridentifikasi. Perlu verifikasi dari dump lengkap atau keputusan desain apakah domain ini diimplementasi dalam fase ini.

**Relasi ke Domain Lain:**
- Terhubung ke `operasi_klaster.id` (domain **mobilisasi_personel**).
- Terhubung ke `operasi_insiden.id` (domain **insiden**).
- Output feedback menjadi input domain **gap_kebutuhan**.

**Tanggung Jawab Domain:**
- Form evaluasi per klaster pasca-respons.
- Penilaian efektivitas koordinasi dan distribusi sumber daya.

---

### 2.16 Domain: `gap_kebutuhan`

**Fungsi:** Identifikasi kesenjangan antara kebutuhan aktual di lapangan dengan sumber daya yang tersedia — sebagai dasar perencanaan logistik dan mobilisasi lanjutan.

**Tabel Terkait:**

> **⚠️ CATATAN SINKRONISASI SQL:** Tabel untuk domain ini (misalnya `sistem_gap` atau `operasi_gap_kebutuhan`) **belum dikonfirmasi** dari SQL dump. Domain ini ada dalam rancangan PRD tetapi belum memiliki tabel yang teridentifikasi. Perlu verifikasi dari dump lengkap atau keputusan desain apakah domain ini diimplementasi dalam fase ini.

**Relasi ke Domain Lain:**
- Sumber data: domain **assessment** (`assessment_kebutuhan_mendesak`) dan domain **logistik** (`logistik_stok`).
- Output: rekomendasi ke domain **logistik** (`logistik_perencanaan`) dan domain **pleno**.
- Terhubung ke `operasi_insiden.id` (domain **insiden**).

**Tanggung Jawab Domain:**
- Membandingkan kebutuhan teridentifikasi dengan stok tersedia.
- Menghasilkan laporan gap per kategori kebutuhan.
- Menjadi dasar perencanaan pengadaan logistik.

---

### 2.17 Domain: `command_center`

**Fungsi:** Dashboard operasional real-time — agregasi read-only dari semua domain untuk memberikan gambaran situasi menyeluruh kepada komando.

**Tabel Terkait:**

> Domain ini bersifat **read-only aggregation** — tidak memiliki tabel sendiri. Semua data diambil dari tabel domain lain melalui Eloquent query atau query builder.

**Sumber Data Agregasi:**

| Data yang Ditampilkan | Sumber Tabel |
|---|---|
| Peta titik bencana aktif | `operasi_insiden` (koordinat) |
| Peta pos aju | `operasi_posaju` (koordinat) |
| Jumlah personel aktif | `operasi_penugasan` (aktif) |
| Status stok logistik | `logistik_stok` per gudang/pos |
| Sitrep terbaru | `operasi_sitrep` (final) |
| Status klaster | `operasi_klaster` |
| Ringkasan pengungsi | Tabel pengungsian (pending konfirmasi) |

**Teknologi:**
- Halaman dirender via Blade SSR.
- Peta menggunakan **Leaflet.js** dengan data koordinat dari controller.
- Refresh data menggunakan auto-reload atau AJAX ringan (bukan WebSocket kompleks).

**Tanggung Jawab Domain:**
- Menyajikan agregasi data operasional secara visual.
- Menyediakan akses cepat ke domain lain (drill-down).

**Batas Domain:**
- Domain ini TIDAK menyimpan data.
- Domain ini TIDAK mengubah data domain lain.
- Tidak ada logika bisnis di domain ini — hanya query dan presentasi.

---

### 2.18 Domain: `audit`

**Fungsi:** Audit trail dan jurnal operasional sistem — mencatat semua perubahan signifikan dan catatan harian operasi untuk keperluan akuntabilitas dan pelaporan.

**Tabel Terkait:**

| Tabel | Fungsi |
|---|---|
| `operasi_jurnal` | Catatan harian operasi: isi jurnal, waktu, penulis, insiden terkait |
| `riwayat_status_insiden` | Log perubahan status insiden: status lama, status baru, waktu, user |

**Relasi ke Domain Lain:**
- `operasi_jurnal.insiden_id` → FK ke `operasi_insiden.id` (domain **insiden**).
- `riwayat_status_insiden.insiden_id` → FK ke `operasi_insiden.id` (domain **insiden**).
- Data audit dapat diakses oleh domain **command_center** untuk tampilan histori.

**Tanggung Jawab Domain:**
- Mencatat semua perubahan status insiden secara otomatis.
- Menyimpan jurnal harian yang ditulis manual oleh operator.
- Menyediakan trail audit untuk keperluan pelaporan dan akuntabilitas.

**Batas Domain:**
- Domain `audit` TIDAK mengubah data domain lain.
- Domain ini hanya **menerima** tulis dari domain lain (via observer atau service call langsung).
- Tidak ada logika bisnis di domain ini.

---

## 3. Relasi Governance

Diagram relasi antar entitas governance dalam sistem:

```
laporan_kejadian
    └─► operasi_insiden [status: draft → terverifikasi → respon → pemulihan → selesai]
            ├─► assessment_utama
            │       ├─► assessment_dampak_manusia
            │       └─► assessment_kebutuhan_mendesak
            │                   └─► logistik_perencanaan
            ├─► operasi_sitrep [status: draft → ditinjau → final]
            ├─► operasi_pleno
            │       ├─► operasi_pleno_keputusan
            │       │       └─► operasi_surat_keluar (formalisasi legal)
            │       ├─► operasi_eskalasi (mengubah status insiden)
            │       └─► operasi_aktivasi (memicu mobilisasi/logistik)
            ├─► operasi_penugasan (assignment personel → peran di insiden)
            │       └─► operasi_otoritas_kontekstual (hak akses sementara)
            ├─► operasi_mobilisasi_personil
            │       └─► operasi_klaster
            │               └─► operasi_klaster_koordinator
            ├─► operasi_periode (shift operasional → kerangka waktu)
            ├─► operasi_posaju
            │       ├─► operasi_posaju_komandan
            │       └─► logistik_stok (stok lapangan)
            ├─► operasi_jurnal (catatan harian → audit)
            └─► riwayat_status_insiden (log status → audit)
```

### 3.1 Relasi Governance Spesifik

| Relasi | Dari | Ke | Mekanisme |
|---|---|---|---|
| Pleno → Keputusan | `operasi_pleno` | `operasi_pleno_keputusan` | One-to-many, parent-child |
| Keputusan → Surat | `operasi_pleno_keputusan` | `operasi_surat_keluar` | Referensi ID keputusan |
| Assignment → Otoritas | `operasi_penugasan` | `operasi_otoritas_kontekstual` | Konteks insiden + user |
| Mobilisasi → Klaster | `operasi_mobilisasi_personil` | `operasi_klaster` | Pengelompokan fungsional |
| Shift → Klaster/Pos | `operasi_periode` | `operasi_klaster` / `operasi_posaju` | Kerangka waktu |
| Sitrep → Final | `operasi_sitrep` | Domain **surat_menyurat** | Sitrep final sebagai lampiran |
| Jurnal → Audit | `operasi_jurnal` | Domain **audit** | Catatan operasional harian |
| Status Change → Audit | `operasi_insiden` | `riwayat_status_insiden` | Laravel Observer / Manual |
| Assessment → Gap | `assessment_kebutuhan_mendesak` | Domain **gap_kebutuhan** | Query perbandingan stok |
| Feedback → Gap | Domain **feedback_klaster** | Domain **gap_kebutuhan** | Input evaluasi klaster |

---

## 4. Flow Data Utama

### 4.1 Alur Lengkap: Laporan Publik → Selesai

```
[1] PELAPORAN
    Publik/Relawan mengisi form laporan
    → INSERT INTO laporan_kejadian (pelapor, lokasi, deskripsi, foto, koordinat)

[2] VALIDASI
    Operator PCNU/PWNU mereview laporan_kejadian
    → Jika valid: INSERT INTO operasi_insiden (status = 'draft')
    → INSERT INTO riwayat_status_insiden (status_lama = NULL, status_baru = 'draft')

[3] VERIFIKASI
    Koordinator memverifikasi insiden
    → UPDATE operasi_insiden SET status = 'terverifikasi'
    → INSERT INTO riwayat_status_insiden (status_baru = 'terverifikasi')

[4] ASSESSMENT
    TRC (Tim Reaksi Cepat) ke lapangan
    → INSERT INTO assessment_utama (insiden_id, jenis = 'kaji_cepat')
    → INSERT INTO assessment_dampak_manusia (assessment_id, korban_jiwa, pengungsi, ...)
    → INSERT INTO assessment_kebutuhan_mendesak (assessment_id, kategori, jumlah, ...)

[5] AKTIVASI RESPON
    Pleno memutuskan aktivasi
    → INSERT INTO operasi_pleno (insiden_id, agenda = 'Aktivasi Respon')
    → INSERT INTO operasi_pleno_keputusan (pleno_id, keputusan = 'Aktivasi Klaster ...')
    → INSERT INTO operasi_aktivasi (pleno_id, insiden_id, klaster_yang_diaktifkan)
    → UPDATE operasi_insiden SET status = 'respon'

[6] MOBILISASI & PENUGASAN
    → INSERT INTO operasi_penugasan (user_id, insiden_id, peran = 'komandan_insiden')
    → INSERT INTO operasi_klaster (insiden_id, jenis_klaster_id)
    → INSERT INTO operasi_klaster_koordinator (klaster_id, user_id)
    → INSERT INTO operasi_mobilisasi_personil (user_id, insiden_id, tujuan)
    → INSERT INTO operasi_posaju (insiden_id, nama, koordinat_lat, koordinat_lng)
    → INSERT INTO operasi_posaju_komandan (posaju_id, user_id)

[7] OPERASI BERJALAN
    → INSERT INTO operasi_periode (insiden_id, nama_shift, mulai, selesai)
    → INSERT/UPDATE logistik_stok (gudang_id/posaju_id, barang_id, jumlah)
    → INSERT INTO logistik_mutasi (tipe = 'keluar', barang_id, jumlah, tujuan)
    → INSERT INTO operasi_jurnal (insiden_id, isi, dibuat_oleh)

[8] SITREP
    Operator membuat laporan situasi
    → INSERT INTO operasi_sitrep (insiden_id, nomor_sitrep, status = 'draft')
    → UPDATE operasi_sitrep SET status = 'ditinjau' → 'final'

[9] EVALUASI & PEMULIHAN
    Pleno memutuskan transisi ke pemulihan
    → INSERT INTO operasi_eskalasi (pleno_id, insiden_id, status_target = 'pemulihan')
    → UPDATE operasi_insiden SET status = 'pemulihan'
    → (Domain feedback_klaster dan gap_kebutuhan diisi — pending konfirmasi tabel)

[10] SURAT RESMI
    Formalisasi keputusan pleno
    → INSERT INTO operasi_surat_keluar (jenis_surat_id, nomor_surat, perihal)
    → INSERT INTO dokumen_surat_paraf (surat_id, jabatan_id, urutan)
    → INSERT INTO dokumen_surat_tembusan (surat_id, penerima)

[11] SELESAI
    Pleno memutuskan penutupan insiden
    → UPDATE operasi_insiden SET status = 'selesai'
    → INSERT INTO riwayat_status_insiden (status_baru = 'selesai')
```

### 4.2 Alur Singkat (Ringkasan)

```
laporan_kejadian
    → [validasi] → operasi_insiden (draft)
    → [verifikasi] → operasi_insiden (terverifikasi)
    → [assessment] → assessment_utama → assessment_dampak_manusia + assessment_kebutuhan_mendesak
    → [sitrep] → operasi_sitrep (draft → final)
    → [pleno] → operasi_pleno → operasi_pleno_keputusan → operasi_aktivasi / operasi_eskalasi
    → [operasi] → penugasan + mobilisasi + logistik + pos_aju + shift + jurnal
    → [evaluasi] → feedback_klaster + gap_kebutuhan (pending konfirmasi tabel)
    → [surat] → operasi_surat_keluar
    → [selesai] → operasi_insiden (selesai)
```

---

## 5. Larangan Arsitektur (FROZEN)

Daftar berikut adalah **keputusan arsitektur yang TIDAK BOLEH diubah** tanpa persetujuan eksplisit dari tech lead. Setiap deviasi dari daftar ini dianggap pelanggaran scope arsitektur.

### 5.1 Larangan Infrastruktur

| Larangan | Alasan |
|---|---|
| **Microservice** | NURISK adalah monolith. Pemisahan layanan tidak diizinkan. |
| **Event Sourcing** | Kompleksitas tidak proporsional untuk skala sistem ini. |
| **CQRS** | Tidak diperlukan. Read/write dari satu database MySQL. |
| **WebSocket Kompleks** | Tidak ada real-time bi-directional yang membutuhkan WebSocket. Auto-reload atau AJAX cukup. |
| **Message Broker** (Kafka, RabbitMQ) | Queue sederhana Laravel (driver: database) sudah cukup. |

### 5.2 Larangan Frontend

| Larangan | Alasan |
|---|---|
| **React / Vue SPA** | Blade SSR adalah standar wajib. Tidak ada SPA. |
| **NextJS / Nuxt** | NURISK bukan Node.js application. |
| **GraphQL** | REST via controller sudah cukup. Tidak perlu GraphQL. |
| **TypeScript di frontend** | Tidak ada bundler TypeScript. Gunakan JS vanilla atau Alpine.js minimal. |
| **Custom CSS Framework** | Bootstrap 5.3 adalah satu-satunya UI framework yang diizinkan. |

### 5.3 Larangan Pola Desain

| Larangan | Alasan |
|---|---|
| **Repository Pattern berlebihan** | Eloquent sudah cukup. Tidak perlu lapisan repository abstrak. |
| **Clean Architecture berlebihan** | Controller → Service → Model. Tidak ada lapisan tambahan. |
| **DDD Kompleks** | Tidak ada Aggregate Root, Value Object, Domain Event di luar yang sudah ada. |
| **Service Container Kompleks** | Gunakan binding sederhana. Tidak ada elaborate IoC configuration. |
| **Enterprise Abstraction Layer** | Tidak ada Factory abstrak berlebihan, tidak ada Strategy pattern berlebihan. |

### 5.4 Pola yang DIIZINKAN

| Pola | Keterangan |
|---|---|
| **Service Class sederhana** | Untuk logika bisnis yang lebih dari 1 controller (mis. `InsidenService`). |
| **Laravel Observer** | Untuk auto-fill audit trail (mis. `riwayat_status_insiden`). |
| **Laravel Policy** | Untuk otorisasi berbasis role (digunakan bersama Spatie Permission). |
| **Laravel Resource** | Untuk API response jika Sanctum diaktifkan. |
| **AJAX ringan** | Untuk refresh data parsial di command_center (bukan SPA). |
| **Alpine.js minimal** | Untuk interaksi UI sederhana jika diperlukan. |

---

## 6. Catatan Sinkronisasi SQL dan PRD

Bagian ini mendokumentasikan **ketidaksesuaian atau ketidakpastian** antara rancangan PRD dengan SQL dump yang tersedia. Setiap item harus dikonfirmasi sebelum implementasi domain terkait.

### 6.1 Tabel yang DIKONFIRMASI ada di SQL dump

| Tabel | Domain | Status |
|---|---|---|
| `auth_users` | auth | ✅ Konfirmasi |
| `auth_roles` | auth | ✅ Konfirmasi |
| `auth_pengguna_profil` | auth | ✅ Konfirmasi |
| `auth_keahlian_master` | auth | ✅ Konfirmasi |
| `auth_pengguna_keahlian` | auth | ✅ Konfirmasi |
| `model_has_roles` | auth | ✅ Konfirmasi |
| `model_has_permissions` | auth | ✅ Konfirmasi |
| `pengguna_jabatan` | auth | ✅ Konfirmasi |
| `master_jabatan` | auth | ✅ Konfirmasi |
| `operasi_insiden` | insiden | ✅ Konfirmasi |
| `laporan_kejadian` | insiden | ✅ Konfirmasi |
| `riwayat_status_insiden` | insiden / audit | ✅ Konfirmasi |
| `bencana_master_jenis` | insiden | ✅ Konfirmasi |
| `assessment_utama` | assessment | ✅ Konfirmasi |
| `assessment_dampak_manusia` | assessment | ✅ Konfirmasi |
| `assessment_kebutuhan_mendesak` | assessment | ✅ Konfirmasi |
| `operasi_sitrep` | sitrep | ✅ Konfirmasi |
| `operasi_pleno` | pleno | ✅ Konfirmasi |
| `operasi_pleno_keputusan` | pleno | ✅ Konfirmasi |
| `operasi_pleno_peserta` | pleno | ✅ Konfirmasi |
| `operasi_eskalasi` | pleno | ✅ Konfirmasi |
| `operasi_aktivasi` | pleno | ✅ Konfirmasi |
| `operasi_surat_keluar` | surat_menyurat | ✅ Konfirmasi |
| `dokumen_surat_paraf` | surat_menyurat | ✅ Konfirmasi |
| `dokumen_surat_tembusan` | surat_menyurat | ✅ Konfirmasi |
| `master_surat_jenis` | surat_menyurat | ✅ Konfirmasi |
| `master_surat_template` | surat_menyurat | ✅ Konfirmasi |
| `master_jabatan_penandatangan` | surat_menyurat | ✅ Konfirmasi |
| `logistik_stok` | logistik | ✅ Konfirmasi |
| `logistik_mutasi` | logistik | ✅ Konfirmasi |
| `logistik_gudang` | logistik | ✅ Konfirmasi |
| `logistik_barang_katalog` | logistik | ✅ Konfirmasi |
| `logistik_kategori` | logistik | ✅ Konfirmasi |
| `logistik_permintaan` | logistik | ✅ Konfirmasi |
| `logistik_perencanaan` | logistik | ✅ Konfirmasi |
| `master_satuan` | logistik | ✅ Konfirmasi |
| `relawan_pendaftaran` | relawan | ✅ Konfirmasi |
| `relawan_penugasan` | relawan | ✅ Konfirmasi |
| `operasi_penugasan` | assignment | ✅ Konfirmasi |
| `operasi_otoritas_kontekstual` | assignment | ✅ Konfirmasi |
| `operasi_mobilisasi_personil` | mobilisasi_personel | ✅ Konfirmasi |
| `operasi_klaster` | mobilisasi_personel | ✅ Konfirmasi |
| `operasi_klaster_koordinator` | mobilisasi_personel | ✅ Konfirmasi |
| `operasi_master_klaster` | mobilisasi_personel | ✅ Konfirmasi |
| `operasi_master_indikator` | mobilisasi_personel | ✅ Konfirmasi |
| `operasi_periode` | shift_operasional | ✅ Konfirmasi |
| `operasi_posaju` | pos_aju | ✅ Konfirmasi |
| `operasi_posaju_komandan` | pos_aju | ✅ Konfirmasi |
| `master_penerima_manfaat` | pengungsian | ✅ Konfirmasi |
| `operasi_jurnal` | audit | ✅ Konfirmasi |
| `jobs` | sistem | ✅ Konfirmasi |
| `failed_jobs` | sistem | ✅ Konfirmasi |
| `job_batches` | sistem | ✅ Konfirmasi |
| `cache` | sistem | ✅ Konfirmasi |
| `cache_locks` | sistem | ✅ Konfirmasi |
| `migrations` | sistem | ✅ Konfirmasi |

### 6.2 Tabel yang BELUM DIKONFIRMASI dari SQL dump

| Tabel yang Dibutuhkan | Domain | Status | Catatan |
|---|---|---|---|
| Tabel utama pengungsian (mis. `pengungsian_lokasi`, `pengungsian_hunian`) | pengungsian | ⚠️ Belum Konfirmasi | Hanya `master_penerima_manfaat` yang terkonfirmasi. Tabel operasional pengungsian perlu diverifikasi. |
| `sistem_feedback` atau `operasi_feedback_klaster` | feedback_klaster | ⚠️ Belum Konfirmasi | Domain ada di PRD tapi tidak ada tabel yang teridentifikasi di dump. |
| `sistem_gap` atau `operasi_gap_kebutuhan` | gap_kebutuhan | ⚠️ Belum Konfirmasi | Domain ada di PRD tapi tidak ada tabel yang teridentifikasi di dump. |
| Tabel struktur organisasi wilayah (mis. `organisasi_wilayah`, `wilayah_pcnu`) | organisasi | ⚠️ Belum Konfirmasi | Scope wilayah saat ini hanya via enum di `auth_users`. Tabel hierarki organisasi belum teridentifikasi. |
| Tabel aset (mis. `aset_unit`, `aset_penggunaan`) | aset (domain tambahan?) | ⚠️ Perlu Klarifikasi | Tabel `aset_master_jenis`, `aset_master_kategori`, `aset_master_status`, `aset_penggunaan`, `aset_unit` terkonfirmasi di dump tapi tidak termasuk dalam 18 domain yang didefinisikan. Perlu keputusan apakah domain `aset` adalah domain ke-19. |

### 6.3 Tabel Terkonfirmasi di SQL tapi Belum Didefinisikan di Domain

| Tabel | Keterangan |
|---|---|
| `aset_master_jenis` | Katalog jenis aset — belum ada domain pemilik yang didefinisikan |
| `aset_master_kategori` | Katalog kategori aset — belum ada domain pemilik |
| `aset_master_status` | Status aset (enum: Tersedia, Dalam Tugas, Perbaikan, Rusak, Hilang) — belum ada domain pemilik |
| `aset_penggunaan` | Record penggunaan aset — belum ada domain pemilik |
| `aset_unit` | Data unit aset fisik (kondisi fisik: baik, rusak_ringan, rusak_berat) — belum ada domain pemilik |

> **Rekomendasi:** Definisikan domain ke-19 yaitu `aset` untuk menampung 5 tabel aset yang sudah ada di SQL. Tabel-tabel ini sudah lengkap di dump dan perlu diintegrasikan ke arsitektur.

---

## Lampiran: Ringkasan Domain dan Status

| # | Domain | Tabel Utama | Status Tabel di SQL | Prioritas |
|---|---|---|---|---|
| 1 | auth | auth_users, auth_roles, auth_pengguna_profil | ✅ Lengkap | P1 |
| 2 | organisasi | (scope di auth_users) | ⚠️ Parsial | P2 |
| 3 | insiden | operasi_insiden, laporan_kejadian | ✅ Lengkap | P1 |
| 4 | assessment | assessment_utama, assessment_dampak_manusia | ✅ Lengkap | P1 |
| 5 | sitrep | operasi_sitrep | ✅ Lengkap | P1 |
| 6 | pleno | operasi_pleno, operasi_pleno_keputusan | ✅ Lengkap | P1 |
| 7 | surat_menyurat | operasi_surat_keluar, dokumen_surat_paraf | ✅ Lengkap | P1 |
| 8 | logistik | logistik_stok, logistik_mutasi, logistik_gudang | ✅ Lengkap | P1 |
| 9 | relawan | relawan_pendaftaran, relawan_penugasan | ✅ Lengkap | P1 |
| 10 | assignment | operasi_penugasan, operasi_otoritas_kontekstual | ✅ Lengkap | P1 |
| 11 | mobilisasi_personel | operasi_mobilisasi_personil, operasi_klaster | ✅ Lengkap | P1 |
| 12 | shift_operasional | operasi_periode | ✅ Lengkap | P2 |
| 13 | pos_aju | operasi_posaju, operasi_posaju_komandan | ✅ Lengkap | P1 |
| 14 | pengungsian | master_penerima_manfaat (parsial) | ⚠️ Parsial | P2 |
| 15 | feedback_klaster | (belum ada tabel) | ❌ Belum Ada | P3 |
| 16 | gap_kebutuhan | (belum ada tabel) | ❌ Belum Ada | P3 |
| 17 | command_center | (read-only, tidak ada tabel) | ✅ N/A | P2 |
| 18 | audit | operasi_jurnal, riwayat_status_insiden | ✅ Lengkap | P1 |

---

*Dokumen ini adalah referensi arsitektur pra-produksi. Setiap perubahan pada boundary domain, tabel, atau stack teknologi HARUS didokumentasikan sebagai amandemen dokumen ini.*
