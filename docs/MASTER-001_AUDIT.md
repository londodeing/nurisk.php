# MASTER-001_AUDIT.md — Audit Master Wilayah & Organisasi NU
# Laporan Audit Struktur Wilayah Pra-Implementasi — Principal Software Architect

> Versi: 1.0 — Tanggal: 16 Juni 2026
> Status: **FROZEN & APPROVED** (Panduan Implementasi Model Wilayah & Organisasi)

---

## 1. RINGKASAN DOMAIN WILAYAH & ORGANISASI NU
Domain wilayah dan organisasi NURISK dirancang dalam format **Hierarki Terpusat Berjenjang**. Master wilayah bertindak sebagai basis data administratif resmi dari Kementerian Dalam Negeri (Kode BPS/Kemendagri untuk Provinsi Jawa Tengah dengan prefix `33`), sedangkan organisasi kepengurusan NU berjalan ortogonal memetakan struktur kepemimpinan di tingkat masing-masing yurisdiksi.

---

## 2. DAFTAR TABEL & AUDIT STRUKTUR SQL

### A. Tabel Administratif Wilayah (Geografis)
1. **`wilayah_kabupaten`**:
   * PK: `id_kab` (char(4)) -> Kode wilayah Kemendagri (e.g. `3301` untuk Cilacap).
   * Kolom: `nama_kab` (varchar(150)), `tipe` (enum('Kabupaten','Kota')).
2. **`wilayah_kecamatan`**:
   * PK: `id_kec` (char(6)) -> Kode wilayah Kemendagri (e.g. `330101` untuk Kedungreja).
   * FK: `id_kab` (references `wilayah_kabupaten.id_kab` ON DELETE CASCADE).
3. **`wilayah_desa`**:
   * PK: `id_desa` (char(10)) -> Kode wilayah Kemendagri (e.g. `3301012001` untuk Tambakreja).
   * FK: `id_kec` (references `wilayah_kecamatan.id_kec` ON DELETE CASCADE).

### B. Tabel Kepengurusan Organisasi NU (Struktural)
1. **`organisasi_unit`**:
   * PK: `id_unit` (int)
   * FK: `parent_id` (references `organisasi_unit.id_unit` self-relation, nullable).
   * Kolom: `nama_unit` (varchar(150)), `tipe_unit` (enum('pwnu','pcnu','mwc','ranting','lembaga','banom')), `id_wilayah` (char(10), references kode wilayah Kemendagri, nullable).
2. **`organisasi_pcnu`**:
   * PK: `id_pcnu` (int)
   * FK: `id_unit` (references `organisasi_unit.id_unit`).
   * Kolom: `nama_pcnu` (varchar(100)).
3. **`organisasi_mwc`**:
   * PK: `id_mwc` (int)
   * FK: 
     * `id_pcnu` (references `organisasi_pcnu.id_pcnu`)
     * `id_unit` (references `organisasi_unit.id_unit`, nullable)
4. **`organisasi_ranting`**:
   * PK: `id_ranting` (int)
   * FK:
     * `id_mwc` (references `organisasi_mwc.id_mwc`)
     * `id_unit` (references `organisasi_unit.id_unit`, nullable)

---

## 3. DIAGRAM RELASI ENTITAS (MAPPING DOMAIN)

```
[organisasi_unit] ── (Self-Relation Parent-Child)
       │
       ├─► [organisasi_pcnu] ──► [wilayah_kabupaten] (id_wilayah = id_kab)
       │         │ (1)
       │         ▼ (N)
       ├─► [organisasi_mwc]  ──► [wilayah_kecamatan] (id_wilayah = id_kec)
       │         │ (1)
       │         ▼ (N)
       └─► [organisasi_ranting] ─► [wilayah_desa]     (id_wilayah = id_desa)
```

---

## 4. RISIKO & DATA INTEGRITY AUDIT
* **Orphan Records**: Kolom `id_unit` pada `organisasi_mwc` dan `organisasi_ranting` bertipe nullable. Hal ini berisiko jika unit organisasi dihapus tetapi transaksional MWC/Ranting tidak tersinkronisasi.
* *Mitigasi*: Model Eloquent wajib mendefinisikan relasi `belongsTo` secara null-safe dan mengimplementasikan validasi relasional di tingkat Service Layer.

---

## 5. DAFTAR MODEL YANG AKAN DIBUAT
1. `WilayahKabupaten` (`app/Models/WilayahKabupaten.php`)
2. `WilayahKecamatan` (`app/Models/WilayahKecamatan.php`)
3. `WilayahDesa` (`app/Models/WilayahDesa.php`)
4. `OrganisasiUnit` (`app/Models/OrganisasiUnit.php`)
5. `OrganisasiPcnu` (`app/Models/OrganisasiPcnu.php`)
6. `OrganisasiMwc` (`app/Models/OrganisasiMwc.php`)
7. `OrganisasiRanting` (`app/Models/OrganisasiRanting.php`)

---

## 6. STRATEGI FACTORY & SEEDER
* **Factory**: Hanya diperlukan factory untuk `organisasi_unit` untuk menyimulasikan pembuatan unit dinamis (misal lembaga/banom baru) saat testing. Tabel geografis wilayah (Kabupaten, Kecamatan, Desa) **tidak memerlukan factory** karena datanya statis.
* **Seeder**: Data kabupaten Jawa Tengah (35 kota/kabupaten), serta data unit default PWNU dan PCNU sudah ter-dump di dalam SQL v37 Frozen. Kita hanya memerlukan pemanggilan seeder pasif untuk testing jika DB kosong.

---

## 7. DAMPAK KE AUTHORIZATION
* **Scope Enclosure**: Model unit wilayah ini akan dibaca oleh `ScopeEnclosure` untuk mengonfirmasi relasi antara `default_scope_id` pada `auth_users` dengan yurisdiksi wilayah `id_pcnu`, `id_mwc`, atau `id_ranting`. Relasi Eloquent yang bersih menjamin efisiensi performa middleware tersebut.

---

## 8. STATUS KESIAPAN SPRINT

### **STATUS: READY**

**Alasan Teknis**:
1. Seluruh 7 tabel wilayah dan organisasi telah terpetakan dengan baik pada database SQL v37 Frozen.
2. Tidak ada tumpang tindih circular references pada skema database.
3. Kita dapat melanjutkan ke pengerjaan **`MASTER-002` (Implementasi Model Wilayah dan Relasinya)**.
