# AUTHORIZATION_DOMAIN_AUDIT.md — Audit Mendalam Otorisasi NURISK
# Laporan Audit Arsitektur Keamanan Lanjutan (Tahap 2) — Principal Software Architect

> Versi: 2.5 — Tanggal: 16 Juni 2026
> Status: **FROZEN & VERIFIED** (Pemberlakuan Keras Tanpa Pengecualian)

---

## 1. STRUKTUR DETAIL & MULTI-JABATAN (MULTI-POSITION)
* **Pertanyaan Verifikasi**: Apakah `pengguna_jabatan` mendukung skenario multi-jabatan secara bersamaan (e.g., Fulan adalah Pimpinan PCNU Kudus sekaligus Komandan Posko Grobogan)?
* **Analisis SQL**:
  * Tabel `pengguna_jabatan` memiliki PK `id_pengguna_jabatan` (bigint) dan kombinasi FK `id_pengguna` ke `auth_users` serta `id_jabatan_posisi` ke `master_jabatan`.
  * Tidak terdapat UNIQUE constraint pada pasangan `(id_pengguna, id_jabatan_posisi)`.
  * Status keaktifan dikontrol oleh kolom `status_aktif` (tinyint) dan batas temporal `berakhir_pada` (datetime, nullable).
* **Kesimpulan**: **Mendukung Penuh**. Seorang pengguna dapat memiliki lebih dari satu baris aktif (`status_aktif = 1` dan `berakhir_pada IS NULL`) pada tabel `pengguna_jabatan` dengan `id_jabatan_posisi` dan `id_lingkup` yang berbeda.

---

## 2. KELAYAKAN LINTAS WILAYAH (CROSS-REGION OPERATIONALS)
* **Pertanyaan Verifikasi**: Bagaimana relawan asal Kabupaten A ditugaskan di posko bencana Kabupaten B?
* **Analisis SQL**:
  * Tabel `auth_users` melacak asal organisasi user (`id_unit` & `default_scope_id`), yang mencerminkan domisili resmi keanggotaannya.
  * Tabel `operasi_penugasan` (transaksional insiden) memiliki FK `id_insiden` dan `id_pengguna`.
  * Kolom `asal_lingkup` dan `tujuan_lingkup` bertipe varchar(100) mencatat pergerakan geografis/organisasional tersebut secara audit log.
* **Mekanisme Policy**:
  * Laravel Policy untuk pengisian assessment mengecek:
    * Apakah user memiliki role `pcnu`/`pwnu` dengan scope sewilayah? (Iya -> Approve).
    * ATAU, apakah user memiliki baris aktif di `operasi_penugasan` untuk `id_insiden` tersebut? (Iya -> Approve).
  * Dengan demikian, otorisasi menulis data lapangan berbasis **Assignment kontekstual** (Lapis 4), tanpa perlu mengubah yurisdiksi kepengurusan permanen user (Lapis 3) di `auth_users`.

---

## 3. GARANSI TIDAK ADA PELEDAKAN PERAN (ROLE EXPLOSION)
* **Audit Integritas Data `auth_roles`**:
  * Nilai ENUM/Data pada tabel `auth_roles` terkunci mutlak pada 5 baris:
    * `1` -> `super_admin`
    * `2` -> `pwnu`
    * `3` -> `pcnu`
    * `4` -> `relawan`
    * `5` -> `publik`
  * Seluruh penamaan spesifik struktural seperti 'Koordinator TRC PCNU' (ID: 9) atau 'Komandan Pos Aju' (ID: 11) **hanya boleh tersimpan** di tabel `master_jabatan`.
  * Tidak ada tabel `roles` dinamis di luar `auth_roles` yang mewakili jabatan-jabatan ini. Ini menjamin level sistem tetap bersih dan minim overhead otorisasi.

---

## 4. PEMISAHAN TEGAS ANTARA JABATAN DAN ASSIGNMENT OPERASIONAL
* **Audit Desain Kunci**:
  * **Jabatan Struktural** (`pengguna_jabatan`): Mewakili posisi formal dalam hierarki organisasi NU (bersifat administratif jangka menengah/panjang).
  * **Assignment Operasional** (`operasi_penugasan`): Hanya berlaku sementara untuk penanganan darurat di lapangan per insiden tertentu (bersifat taktis jangka pendek).
  * Kolom enum `peran_otoritas` pada tabel `operasi_penugasan` berisi peran taktis: `'komandan_insiden'`, `'trc'`, `'relawan'`, `'medis'`, `'logistik'`, `'operator'`. Jabatan-jabatan ini tidak tercampur ke dalam `master_jabatan` NU.

---

## 5. DAFTAR GAP SPESIFIK & TINDAKAN ARSITEKTUR

| Kategori | Deskripsi Celah / Dampak | Keputusan Arsitektur | Tindakan Mitigasi Terpilih |
|---|---|---|---|
| **Menengah** | Inkonsistensi istilah data seeder SQL `master_jabatan` dengan domain PRD. | **Diperbaiki** | Seeder `master_jabatan` (pada `AUTH-003`) hanya akan menggunakan data terverifikasi (15 jabatan default dari baris 1936-1950 SQL dump). |
| **Minor** | Validasi status keaktifan user-jabatan yang tumpang tindih. | **Ditunda** | Logika tumpang tindih waktu penugasan akan ditangani di level Validation Logic Service `M03`. |

---

## 6. STATUS KESIAPAN KODE AKHIR

### **STATUS: READY**

Seluruh kriteria kepatuhan otorisasi 4-layer (Role -> Jabatan -> Scope -> Assignment) telah terbukti sinkron secara fisik dan konseptual dengan skema SQL v37 Frozen. Pengerjaan **`AUTH-003`** (Seeder Peran dan Akun Default) disetujui untuk dieksekusi.
