# PRODUCTION READINESS AUDIT — NURISK

Dokumen ini memuat hasil audit kesiapan rilis produksi (*Production Readiness*) untuk sistem NURISK, berfokus pada kelengkapan skema migrasi fisik database.

---

## 1. MIGRATION READINESS STATUS: 🔴 NOT READY (PRODUCTION BLOCKER)

Berdasarkan audit fisik terhadap folder `database/migrations/`, **sistem NURISK TIDAK SIAP untuk dideploy ke lingkungan produksi/staging**. 

Sebagian besar tabel penting yang mendukung operasi dan relawan saat ini **hanya terdefinisi secara dinamis di dalam kelas pengujian (SQLite in-memory)** menggunakan trait `CreatesOperasiSchema` dan `CreatesRelawanSchema`, serta deklarasi langsung di metode `setUp()` pada berbagai *Feature Tests*. Berkas migrasi fisik (`.php`) untuk tabel-tabel tersebut **tidak ada** di repositori.

---

## 2. TABEL YANG BELUM MEMILIKI MIGRATION FISIK (PRODUCTION BLOCKER)

Seluruh tabel berikut diklasifikasikan sebagai **PRODUCTION BLOCKER** karena mutlak dibutuhkan untuk operasional sistem tetapi belum dapat dibuat secara permanen melalui perintah `php artisan migrate`:

### 2.1. Domain: Auth & Profil (M01 & M03)
Tabel-tabel ini mendasari seluruh sistem autentikasi dan otorisasi:
- **`auth_roles`**: Menyimpan 5 role utama PRD (super_admin, pwnu, pcnu, relawan, publik).
- **`auth_users`**: Tabel akun pengguna utama yang menggunakan login `no_hp` dan password `kata_sandi`.
- **`auth_pengguna_profil`**: Data personal fisik pengguna (NIK, Domisili, dll.).
- **`auth_keahlian_master`**: Katalog keahlian yang dimiliki relawan (medis, rescue, dll.).
- **`auth_pengguna_keahlian`**: Tabel pivot relasi many-to-many antara user dan keahlian master.

### 2.2. Domain: Organisasi (M02)
Tabel-tabel hierarki struktural wilayah organisasi NU:
- **`organisasi_unit`**: Tipe-tipe unit structural NU (PWNU, PCNU, MWC, Ranting).
- **`organisasi_pcnu`**: Entitas PCNU cabang yang menjadi basis scope yurisdiksi.
- **`organisasi_mwc`**: (Opsional jika dibutuhkan relasi fisik)
- **`organisasi_ranting`**: (Opsional jika dibutuhkan relasi fisik)

### 2.3. Domain: Wilayah (M02)
Tabel referensi master wilayah administratif Indonesia:
- **`wilayah_kabupaten`**: Kode kabupaten Jawa Tengah (35 kabupaten/kota seeded).
- **`wilayah_kecamatan`**: Kode kecamatan Jawa Tengah.
- **`wilayah_desa`**: Kode kelurahan/desa Jawa Tengah.

### 2.4. Domain: Pos Aju (M08/M15)
Tabel fisik komando taktis lapangan:
- **`operasi_posaju`**: Header pos komando lapangan (PJ, insiden terkait, status, dll.).
- **`operasi_posaju_komandan`**: Penunjukan komandan posko per periode.

### 2.5. Domain: Klaster & Tugas (M10/M11)
Tabel pelacakan progres taktis per klaster fungsional:
- **`operasi_klaster`**: Klaster fungsional di bawah insiden (SAR, Medis, Logistik, dll.).
- **`operasi_tugas`**: Daftar tugas mikro yang diturunkan per klaster.
- **`operasi_penugasan`**: Otoritas/penugasan makro personel pada insiden.

### 2.6. Domain: Relawan (M10/M13)
Tabel rekrutmen dan pendaftaran relawan mandiri:
- **`relawan_kebutuhan`**: Lowongan kebutuhan relawan per insiden.
- **`relawan_pendaftaran`**: Pendaftaran dan seleksi relawan ke kebutuhan tertentu.
- **`relawan_penugasan`**: Penugasan aktif relawan di posko lapangan.
- **`relawan_shift`**: Jadwal shift operasional relawan di lokasi tugas.

---

## 3. ANALISIS PENGGUNAAN SCHEMA DITESTING TRAITS

Saat ini, pengujian fitur berjalan sukses (100% OK) karena test-test tersebut memanggil:
- `Tests\Support\CreatesOperasiSchema::createOperasiSchema()`
- `Tests\Support\CreatesRelawanSchema::createRelawanSchema()`
- Deklarasi skema lokal di `LoginControllerTest::setUp()`, `JabatanTest::setUp()`, `WilayahSeederTest::setUp()`.

Pola pembuatan skema *on-the-fly* ini hanya menutupi kebutuhan SQLite *in-memory testing* dan menyamarkan tidak adanya migrasi fisik. Begitu aplikasi dijalankan pada server staging/produksi (yang menggunakan RDBMS MySQL/PostgreSQL riil), perintah `php artisan migrate` akan gagal membangun relasi dan menyebabkan sistem tidak dapat berjalan.

---

## 4. TINDAKAN REKOMENDASI (MITIGATION PATH)

Untuk menyelesaikan blocker produksi ini, pengembang diwajibkan menulis berkas migrasi fisik Laravel resmi di folder `database/migrations/` dengan urutan dependensi yang benar (untuk mencegah *Foreign Key constraint check failure*):

1. **Migrasi Fondasi Auth & Wilayah**:
   - `create_auth_roles_table`
   - `create_auth_users_table` (FK ke `auth_roles`)
   - `create_auth_pengguna_profil_table` (FK ke `auth_users`)
   - `create_auth_keahlian_master_table`
   - `create_auth_pengguna_keahlian_table` (FK ke `auth_users`, `auth_keahlian_master`)
   - `create_wilayah_tables` (kabupaten, kecamatan, desa)
   - `create_organisasi_tables` (unit, pcnu, dll. FK ke `organisasi_unit`)
2. **Migrasi Operasi Lapangan**:
   - `create_operasi_posaju_table` (FK ke `operasi_insiden`, `auth_users` sebagai PJ)
   - `create_operasi_klaster_table` (FK ke `operasi_insiden`)
   - `create_operasi_penugasan_table` (FK ke `operasi_insiden`, `auth_users`)
   - `create_operasi_tugas_table` (FK ke `operasi_klaster`, `operasi_posaju`, `auth_users` ditugaskan_ke)
3. **Migrasi Relawan**:
   - `create_relawan_kebutuhan_table` (FK ke `operasi_insiden`, `operasi_klaster`, `operasi_posaju`, `auth_keahlian_master`)
   - `create_relawan_pendaftaran_table` (FK ke `relawan_kebutuhan`, `auth_users` relawan, `auth_users` verifikator)
   - `create_relawan_penugasan_table` (FK ke `relawan_pendaftaran`, `operasi_posaju`, `operasi_surat_keluar`)
   - `create_relawan_shift_table` (FK ke `relawan_penugasan`)
