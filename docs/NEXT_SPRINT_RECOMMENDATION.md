# NEXT SPRINT RECOMMENDATION — NURISK

Dokumen ini memuat status prioritas teknis dan rekomendasi urutan pengerjaan modul-modul NURISK, disusun berdasarkan peta dependensi modul (*Module Dependency Map*) dan temuan audit arsitektur terbaru.

---

## 1. URUTAN IMPLEMENTASI AKTIF & DIREKOMENDASIKAN

Berdasarkan dependensi data dan integritas hubungan *Foreign Key* (FK) di database, berikut adalah urutan pengembangan selanjutnya:

```
[1] ARCH-002: Migrasi Fisik & Konsolidasi (ACTIVE)
      │
      ▼
[2] Assessment (API - NEXT)
      │
      ▼
[3] Laporan Situasi / Sitrep (API)
      │
      ▼
[4] Logistik & Gudang (API)
      │
      ▼
[5] Aset Operasional (API)
      │
      ▼
[6] Governance (Pleno & Surat - Web/API)
      │
      ▼
[7] Command Center & Dashboard (Web UI)
```

---

## 2. STATUS PRIORITAS TEKNIS SAAT INI

### Langkah Aktif: ARCH-002 — Migration Consolidation
- **Deskripsi**: Menulis berkas migrasi fisik untuk seluruh tabel dasar sistem (Auth, Organisasi, Wilayah) serta tabel operasional lapangan (Pos Aju, Klaster, Tugas, dan Relawan).
- **Status**: **ACTIVE (Sedang Dikerjakan)**
- **Detail Pekerjaan**: 
  - Memindahkan seluruh deklarasi skema tabel yang sebelumnya didefinisikan dinamis di dalam `CreatesOperasiSchema` dan `CreatesRelawanSchema` ke dalam berkas migrasi Laravel fisik (`database/migrations/`).
  - Mengosongkan helper pengujian dinamis agar seluruh test suite dipaksa berjalan menggunakan database hasil migrasi fisik.
  - Memastikan perintah `php artisan migrate:fresh --seed` dan `vendor/bin/phpunit` berhasil dieksekusi dengan hasil 100% hijau.
- **Tujuan**: Menghilangkan blocker staging/produksi tertinggi agar aplikasi siap dideploy ke server.

### Langkah Berikutnya: Domain Assessment (API-First)
- **Deskripsi**: Membuat model, service, request layer, API controller, dan API resources untuk pengisian kaji cepat dampak bencana (`assessment_utama`, `assessment_dampak_manusia`, `assessment_kebutuhan_mendesak`).
- **Alasan Dependensi**: Domain Assessment bergantung pada Insiden (`operasi_insiden`) yang sudah selesai. Assessment harus diselesaikan terlebih dahulu karena datanya menjadi basis utama pengisian Laporan Situasi (Sitrep).

### Langkah 4: Domain Laporan Situasi / Sitrep (API-First)
- **Deskripsi**: Implementasi model, request validation, API controller, trigger `tr_auto_snapshot_sitrep` (SQLite & MySQL), hitung hash integrity SHA-256 untuk Sitrep Final, serta generator PDF.
- **Alasan Dependensi**: Sitrep membutuhkan data dari domain Assessment sebagai basis snapshot dampak kejadian bencana. Sitrep yang berstatus `final` nantinya menjadi referensi penting bagi pleno eskalasi dan lampiran surat tugas.

### Langkah 5: Domain Logistik & Gudang (API-First)
- **Deskripsi**: Membuat katalog barang, mutasi stok, request pengadaan, trigger pencegah stok negatif, dan mutasi otomatis (`logistik_stok`, `logistik_mutasi`, `logistik_gudang`, dll.).
- **Alasan Dependensi**: Perencanaan logistik bergantung langsung pada data `assessment_kebutuhan_mendesak`. Stok logistik lapangan juga harus dikaitkan ke lokasi fisik `operasi_posaju` yang valid.

### Langkah 6: Domain Aset Operasional (API-First)
- **Deskripsi**: Pengelolaan unit aset, peminjaman, pencegahan double-booking via database trigger, dan pengembalian aset (`aset_unit`, `aset_penggunaan`).
- **Alasan Dependensi**: Penggunaan aset operasional harus dikaitkan dengan `operasi_insiden` aktif dan bertanggung jawab kepada `auth_users` tertentu, sehingga harus diimplementasikan setelah domain penugasan selesai.

### Langkah 7: Domain Governance (Pleno & Surat - Web/API Hybrid)
- **Deskripsi**: Implementasi rapat pleno, persetujuan voting peserta pleno, eskalasi wilayah, aktivasi darurat, draf surat keluar, paraf berantai, dan tanda tangan digital/PDF (`operasi_pleno`, `operasi_surat_keluar`, dll.).
- **Alasan Dependensi**: Surat menyurat dan pleno adalah mekanisme governance formal. Surat tugas relawan membutuhkan data dari `relawan_penugasan` (langkah 2), sedangkan surat perintah tugas klaster membutuhkan data `operasi_tugas` (langkah 1). Pleno eskalasi juga membutuhkan data situasi terkini dari `operasi_sitrep` (langkah 4).

### Langkah 8: Command Center & Dashboard (Web UI SSR)
- **Deskripsi**: Agregasi data read-only dari seluruh domain (Insiden, Pos Aju, Relawan, Logistik, Aset, Sitrep) ke dalam satu dashboard visual terpadu menggunakan Blade SSR dan Leaflet.js map.
- **Alasan Dependensi**: CC adalah modul agregator murni. CC hanya dapat dibangun secara utuh jika seluruh domain transaksional di bawahnya sudah selesai diimplementasikan dan datanya tersedia di database.
