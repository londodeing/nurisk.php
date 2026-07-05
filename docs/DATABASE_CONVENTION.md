# DATABASE_CONVENTION.md — NURISK

> **Platform:** NURISK — Sistem Kebencanaan NU Berbasis Laravel Monolith  
> **Database:** MySQL 8.x, Engine InnoDB  
> **Versi Dokumen:** 1.0  
> **Tanggal:** 2026-06-16  
> **Status:** Freeze — dilarang mengubah konvensi tanpa persetujuan lead engineer

---

## 1. Naming Convention

### 1.1 Format Umum

| Aspek | Aturan | Contoh |
|---|---|---|
| Nama tabel | `snake_case`, Bahasa Indonesia, wajib prefix domain | `operasi_insiden`, `logistik_stok` |
| Nama kolom | `snake_case`, Bahasa Indonesia | `dibuat_pada`, `status_insiden` |
| Primary key | `id_{nama_entitas}` | `id_insiden`, `id_pleno` |
| Foreign key | `id_{entitas_referensi}` | `id_insiden`, `id_pengguna` |
| Kolom timestamp | `dibuat_pada`, `diperbarui_pada`, `dihapus_pada` | — |
| Kolom boolean | Prefiks `is_` atau `ada_` | `is_valid`, `ada_koordinator` |
| Kolom enum/status | Prefiks `status_` atau nama deskriptif | `status_insiden`, `kondisi_fisik` |

### 1.2 Ketentuan Bahasa

- Semua nama tabel dan kolom menggunakan **Bahasa Indonesia penuh**.
- Tidak ada singkatan bahasa Inggris kecuali yang sudah menjadi konvensi Laravel bawaan (`id`, `cache`, `jobs`, `migrations`, `pivot`).
- Tidak menggunakan singkatan ambigu. Gunakan nama lengkap jika perlu.

```
BENAR  : operasi_insiden, laporan_kejadian, logistik_mutasi
SALAH  : operation_incident, incident_log, logistics_mutation
```

---

## 2. Prefix Domain Tabel

Setiap tabel wajib memiliki prefix domain yang mencerminkan konteks fungsionalnya. Prefix dipisah dengan garis bawah (`_`).

### 2.1 Daftar Prefix Resmi

| Prefix | Domain | Tabel yang Termasuk |
|---|---|---|
| `auth_` | Autentikasi dan akun pengguna | `auth_users`, `auth_roles`, `auth_pengguna_profil`, `auth_keahlian_master`, `auth_pengguna_keahlian` |
| `operasi_` | Operasi lapangan dan manajemen insiden | `operasi_insiden`, `operasi_sitrep`, `operasi_pleno`, `operasi_pleno_keputusan`, `operasi_pleno_peserta`, `operasi_eskalasi`, `operasi_penugasan`, `operasi_mobilisasi_personil`, `operasi_jurnal`, `operasi_klaster`, `operasi_klaster_koordinator`, `operasi_aktivasi`, `operasi_otoritas_kontekstual`, `operasi_periode`, `operasi_posaju`, `operasi_posaju_komandan`, `operasi_master_klaster`, `operasi_master_indikator` |
| `assessment_` | Kajian dan assessment lapangan | `assessment_utama`, `assessment_dampak_manusia`, `assessment_kebutuhan_mendesak` |
| `aset_` | Aset operasional dan inventaris | `aset_unit`, `aset_penggunaan`, `aset_master_jenis`, `aset_master_kategori`, `aset_master_status` |
| `logistik_` | Logistik, gudang, dan stok | `logistik_stok`, `logistik_mutasi`, `logistik_gudang`, `logistik_barang_katalog`, `logistik_kategori`, `logistik_permintaan`, `logistik_perencanaan` |
| `relawan_` | Data dan penugasan relawan | `relawan_pendaftaran`, `relawan_penugasan` |
| `bencana_` | Master data jenis bencana | `bencana_master_jenis` |
| `laporan_` | Laporan publik dan warga | `laporan_kejadian` |
| `dokumen_` | Surat dan dokumen resmi | `operasi_surat_keluar`, `dokumen_surat_paraf`, `dokumen_surat_tembusan` |
| `master_` | Master data umum lintas domain | `master_satuan`, `master_surat_jenis`, `master_surat_template`, `master_jabatan_penandatangan`, `master_penerima_manfaat` |
| `jabatan_` | Struktur jabatan organisasi | `master_jabatan` |
| `riwayat_` | Histori dan audit perubahan status | `riwayat_status_insiden` |
| `sistem_` | Konfigurasi dan pengaturan sistem | `sistem_*` (seluruh tabel konfigurasi sistem) |

### 2.2 Catatan Khusus: Tabel Tanpa Domain Prefix

Tabel infrastruktur Laravel berikut **tidak diberi prefix domain** karena merupakan konvensi framework bawaan:

| Tabel | Keterangan |
|---|---|
| `cache` | Laravel Cache driver |
| `cache_locks` | Laravel Cache locking |
| `jobs` | Laravel Queue jobs |
| `job_batches` | Laravel Queue job batches |
| `failed_jobs` | Laravel Queue failed jobs |
| `migrations` | Laravel Migration tracker |
| `model_has_permissions` | Spatie Permission pivot |
| `model_has_roles` | Spatie Permission pivot |

### 2.3 Catatan Khusus: `auth_pengguna_profil`

Tabel profil pengguna menggunakan prefix `auth_` meskipun secara semantik mencakup data profil, karena relasinya langsung kepada `auth_users` (FK `id_pengguna`) dan merupakan ekstensi satu-ke-satu dari entitas autentikasi. Prefix ini tidak diubah.

---

## 3. Primary Key Convention

### 3.1 Format

```
id_{nama_entitas}
```

### 3.2 Tipe Data

| Konteks | Tipe | Keterangan |
|---|---|---|
| Tabel transaksional | `BIGINT UNSIGNED AUTO_INCREMENT` | `operasi_insiden`, `logistik_mutasi`, dll. |
| Tabel master/referensi | `INT UNSIGNED AUTO_INCREMENT` | `bencana_master_jenis`, `master_satuan`, dll. |

### 3.3 Contoh PK Nyata per Domain

| Tabel | Primary Key |
|---|---|
| `auth_users` | `id_pengguna` |
| `operasi_insiden` | `id_insiden` |
| `operasi_sitrep` | `id_sitrep` |
| `operasi_pleno` | `id_pleno` |
| `operasi_pleno_keputusan` | `id_keputusan` |
| `operasi_pleno_peserta` | `id_peserta` |
| `operasi_eskalasi` | `id_eskalasi` |
| `operasi_penugasan` | `id_incident_assignment` |
| `operasi_mobilisasi_personil` | `id_mobilisasi` |
| `operasi_jurnal` | `id_jurnal` |
| `operasi_klaster` | `id_klaster` |
| `operasi_aktivasi` | `id_aktivasi` |
| `operasi_posaju` | `id_posaju` |
| `assessment_utama` | `id_assessment_utama` |
| `assessment_dampak_manusia` | `id_dampak_manusia` |
| `assessment_kebutuhan_mendesak` | `id_kebutuhan_mendesak` |
| `logistik_stok` | `id_stok` |
| `logistik_mutasi` | `id_mutasi` |
| `logistik_gudang` | `id_gudang` |
| `logistik_barang_katalog` | `id_barang` |
| `logistik_permintaan` | `id_permintaan` |
| `logistik_perencanaan` | `id_perencanaan` |
| `aset_unit` | `id_aset` |
| `aset_penggunaan` | `id_penggunaan` |
| `relawan_pendaftaran` | `id_pendaftaran` |
| `relawan_penugasan` | `id_penugasan_relawan` |
| `operasi_surat_keluar` | `id_surat` |
| `laporan_kejadian` | `id_laporan` |
| `riwayat_status_insiden` | `id_riwayat` |
| `master_jabatan` | `id_posisi` |

> **Aturan keras:** PK tidak pernah menggunakan nama generik `id`. Selalu gunakan `id_{nama_entitas}`.

---

## 4. Foreign Key Convention

### 4.1 Format

```
id_{entitas_referensi}
```

Nama FK di tabel anak menggunakan nama yang **sama** dengan PK di tabel induk.

### 4.2 Contoh Kolom FK Nyata

| Tabel | Kolom FK | Referensi ke |
|---|---|---|
| `operasi_sitrep` | `id_insiden` | `operasi_insiden.id_insiden` |
| `operasi_sitrep` | `id_pengguna` | `auth_users.id_pengguna` |
| `operasi_pleno_peserta` | `id_pleno` | `operasi_pleno.id_pleno` |
| `operasi_pleno_peserta` | `id_pengguna` | `auth_users.id_pengguna` |
| `operasi_pleno_keputusan` | `id_pleno` | `operasi_pleno.id_pleno` |
| `operasi_eskalasi` | `id_insiden` | `operasi_insiden.id_insiden` |
| `operasi_penugasan` | `id_insiden` | `operasi_insiden.id_insiden` |
| `operasi_penugasan` | `id_pengguna` | `auth_users.id_pengguna` |
| `assessment_utama` | `id_insiden` | `operasi_insiden.id_insiden` |
| `assessment_dampak_manusia` | `id_assessment_utama` | `assessment_utama.id_assessment_utama` |
| `assessment_kebutuhan_mendesak` | `id_assessment_utama` | `assessment_utama.id_assessment_utama` |
| `logistik_mutasi` | `id_stok` | `logistik_stok.id_stok` |
| `logistik_stok` | `id_gudang` | `logistik_gudang.id_gudang` |
| `logistik_stok` | `id_barang` | `logistik_barang_katalog.id_barang` |
| `logistik_permintaan` | `id_insiden` | `operasi_insiden.id_insiden` |
| `aset_unit` | `id_insiden` | `operasi_insiden.id_insiden` |
| `aset_penggunaan` | `id_aset` | `aset_unit.id_aset` |
| `operasi_insiden` | `id_jenis_bencana` | `bencana_master_jenis.id_jenis_bencana` |
| `auth_users` | `id_peran` | `auth_roles.id_peran` |
| `auth_pengguna_profil` | `id_pengguna` | `auth_users.id_pengguna` |
| `dokumen_surat_paraf` | `id_surat` | `operasi_surat_keluar.id_surat` |
| `dokumen_surat_tembusan` | `id_surat` | `operasi_surat_keluar.id_surat` |
| `riwayat_status_insiden` | `id_insiden` | `operasi_insiden.id_insiden` |

### 4.3 Penamaan Constraint FK

Setiap FK wajib memiliki nama constraint eksplisit di `ALTER TABLE`. Format penamaan constraint:

```
fk_{tabel_anak}_{kolom_fk}
```

Contoh:

```sql
ALTER TABLE operasi_sitrep
  ADD CONSTRAINT fk_operasi_sitrep_id_insiden
  FOREIGN KEY (id_insiden) REFERENCES operasi_insiden(id_insiden)
  ON DELETE CASCADE;

ALTER TABLE assessment_dampak_manusia
  ADD CONSTRAINT fk_assessment_dampak_manusia_id_assessment_utama
  FOREIGN KEY (id_assessment_utama) REFERENCES assessment_utama(id_assessment_utama)
  ON DELETE CASCADE;
```

---

## 5. Kolom Standar Wajib

Setiap tabel **transaksional** wajib memiliki kolom berikut:

### 5.1 `dibuat_pada`

```sql
dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
```

- Dicatat otomatis saat baris pertama kali dibuat.
- Tidak boleh diubah setelah insert.
- Digunakan sebagai dasar sorting default (`ORDER BY dibuat_pada DESC`).

### 5.2 `diperbarui_pada`

```sql
diperbarui_pada TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
```

- Diperbarui otomatis oleh MySQL setiap kali baris di-`UPDATE`.
- Nilai awal `NULL` jika belum pernah diperbarui.

### 5.3 `dihapus_pada` (Soft Delete — Selektif)

```sql
dihapus_pada TIMESTAMP NULL DEFAULT NULL
```

- Hanya ditambahkan pada tabel yang masuk daftar governance soft delete (lihat §6).
- Tabel yang tidak masuk daftar **tidak** menambahkan kolom ini.
- Ketika baris dihapus secara logis, `dihapus_pada` diisi timestamp saat ini.
- Query standar wajib menyertakan `WHERE dihapus_pada IS NULL` atau menggunakan scope `SoftDeletes` Eloquent.

### 5.4 Tabel Master

Tabel master/referensi (prefix `bencana_master_`, `aset_master_`, `logistik_kategori`, `master_`, `jabatan_`) tetap wajib memiliki `dibuat_pada` dan `diperbarui_pada`, tetapi **opsional** untuk `dihapus_pada`.

---

## 6. Soft Delete

### 6.1 Tabel yang Memiliki `dihapus_pada`

Berikut adalah tabel yang secara eksplisit menggunakan soft delete (kolom `dihapus_pada`):

| Tabel | Alasan Soft Delete |
|---|---|
| `assessment_utama` | Data kajian perlu diaudit meskipun dibatalkan |
| `operasi_insiden` | Insiden tidak boleh dihapus permanen; histori wajib dipertahankan |
| `operasi_sitrep` | Laporan situasi perlu dipertahankan untuk audit |
| `operasi_penugasan` | Penugasan yang dibatalkan tetap perlu tercatat |
| `riwayat_status_insiden` | Histori perubahan status tidak boleh dihapus |
| `logistik_permintaan` | Permintaan yang dibatalkan perlu dipertahankan untuk rekonsiliasi |

> **Catatan:** Daftar ini dapat bertambah berdasarkan SQL dump aktual. Setiap penambahan tabel ke daftar soft delete wajib didokumentasikan di sini dan di `CHANGELOG.md`.

### 6.2 Implementasi di Eloquent Model

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class OperasiInsiden extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_insiden';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';
}
```

### 6.3 Larangan

- **Dilarang** menggunakan `DELETE` fisik (hard delete) pada tabel yang masuk daftar soft delete.
- **Dilarang** melakukan `forceDelete()` tanpa otorisasi `super_admin`.

---

## 7. Seluruh ENUM yang Difreeze

Nilai-nilai ENUM berikut adalah **freeze**. Dilarang menambah atau menghapus nilai tanpa melalui proses perubahan skema formal dan pembaruan dokumen ini.

### 7.1 `auth_users`

**Kolom `status_akun`:**

| Nilai | Keterangan |
|---|---|
| `menunggu` | Akun terdaftar, menunggu verifikasi admin |
| `aktif` | Akun aktif dan dapat login |
| `nonaktif` | Akun dinonaktifkan secara administratif |
| `suspend` | Akun disuspend karena pelanggaran |

**Kolom `default_scope_type`:**

| Nilai | Keterangan |
|---|---|
| `pwnu` | Scope default di level PWNU Jawa Tengah |
| `pcnu` | Scope default di level PCNU kabupaten/kota |
| `mwc` | Scope default di level MWC kecamatan |
| `ranting` | Scope default di level Ranting desa/kelurahan |
| `lembaga` | Scope default untuk lembaga otonom NU |
| `banom` | Scope default untuk badan otonom NU |

---

### 7.2 `operasi_insiden`

**Kolom `status_insiden`:**

| Nilai | Keterangan |
|---|---|
| `draft` | Insiden dilaporkan, belum diverifikasi |
| `terverifikasi` | Insiden terverifikasi oleh operator/TRC |
| `respon` | Operasi respons aktif berjalan |
| `pemulihan` | Fase pemulihan pascabencana |
| `selesai` | Operasi dinyatakan selesai |
| `dibatalkan` | Insiden dibatalkan (salah lapor, dll.) |

**Kolom `status_operasi`:**

| Nilai | Keterangan |
|---|---|
| `monitoring` | Status pemantauan awal |
| `siaga` | Status siaga, belum respons penuh |
| `tanggap_darurat` | Tanggap darurat aktif |
| `pemulihan` | Fase pemulihan |
| `selesai` | Operasi selesai |

**Kolom `prioritas`:**

| Nilai | Keterangan |
|---|---|
| `rendah` | Dampak minimal, tidak mendesak |
| `sedang` | Dampak moderat |
| `tinggi` | Dampak signifikan, perlu respons cepat |
| `kritis` | Dampak masif, respons segera |

---

### 7.3 `operasi_sitrep`

**Kolom `status_sitrep`:**

| Nilai | Keterangan |
|---|---|
| `draft` | Sitrep masih disusun |
| `ditinjau` | Sitrep dalam proses review |
| `final` | Sitrep difinalisasi dan dipublikasikan |

---

### 7.4 `operasi_penugasan`

**Kolom `peran_otoritas`:**

| Nilai | Keterangan |
|---|---|
| `komandan_insiden` | Komandan Insiden (pimpinan operasi) |
| `trc` | Tim Reaksi Cepat |
| `relawan` | Relawan lapangan |
| `medis` | Tenaga medis/kesehatan |
| `logistik` | Koordinator logistik |
| `operator` | Operator sistem dan komunikasi |

> **Catatan:** `status_penugasan` pada tabel ini memiliki default `aktif`. Tidak ada enum lain yang terdokumentasi di skema.

---

### 7.5 `operasi_eskalasi`

**Kolom `level_sebelumnya` dan `level_baru`:**

| Nilai | Keterangan |
|---|---|
| `lokal` | Level penanganan ranting/desa |
| `pcnu` | Level PCNU kabupaten/kota |
| `pwnu` | Level PWNU provinsi |
| `nasional` | Level nasional |

---

### 7.6 `operasi_klaster`

**Kolom `status_klaster`:**

| Nilai | Keterangan |
|---|---|
| `nonaktif` | Klaster belum diaktifkan |
| `aktif` | Klaster sedang berjalan |
| `selesai` | Klaster dinyatakan selesai |

**Kolom `prioritas`:** Sama dengan `operasi_insiden.prioritas`: `rendah`, `sedang`, `tinggi`, `kritis`.

---

### 7.7 `operasi_aktivasi`

**Kolom `status_darurat`:**

| Nilai | Keterangan |
|---|---|
| `siaga` | Status siaga ditetapkan |
| `tanggap_darurat` | Tanggap darurat aktif |
| `pemulihan` | Fase pemulihan |
| `selesai` | Status darurat dicabut |

---

### 7.8 `aset_unit`

**Kolom `kondisi_fisik`:**

| Nilai | Keterangan |
|---|---|
| `baik` | Aset dalam kondisi baik, siap digunakan |
| `rusak_ringan` | Aset mengalami kerusakan ringan |
| `rusak_berat` | Aset mengalami kerusakan berat, tidak dapat digunakan |

> **Catatan:** Status aset menggunakan tabel terpisah `aset_master_status` dengan nilai integer: `1` = Tersedia, `2` = Dalam Tugas, `3` = Perbaikan/Maintenance, `4` = Rusak, `5` = Hilang.

---

### 7.9 `assessment_utama`

**Kolom `jenis_laporan`:**

| Nilai | Keterangan |
|---|---|
| `kaji_cepat` | Kajian cepat awal (rapid assessment) |
| `pendataan_lanjutan` | Pendataan lanjutan yang lebih mendalam |

---

### 7.10 `logistik_mutasi`

**Kolom `tipe_mutasi`:**

| Nilai | Keterangan |
|---|---|
| `masuk` | Barang masuk ke gudang |
| `keluar` | Barang keluar dari gudang |
| `penyesuaian` | Penyesuaian stok (koreksi, audit fisik) |

---

### 7.11 `logistik_permintaan`

**Kolom `prioritas`:**

| Nilai | Keterangan |
|---|---|
| `biasa` | Permintaan tidak mendesak |
| `mendesak` | Permintaan perlu ditangani segera |
| `darurat` | Permintaan darurat, harus dipenuhi segera |

**Kolom `status_permintaan`:**

| Nilai | Keterangan |
|---|---|
| `draft` | Permintaan masih disusun |
| `diajukan` | Permintaan diajukan ke gudang |
| `disetujui` | Permintaan disetujui |
| `ditolak` | Permintaan ditolak |
| `dikirim` | Barang sudah dikirimkan |
| `selesai` | Permintaan selesai dipenuhi |

---

### 7.12 `logistik_perencanaan`

**Kolom `prioritas`:**

| Nilai | Keterangan |
|---|---|
| `darurat` | Kebutuhan darurat, segera disediakan |
| `mendesak` | Kebutuhan mendesak dalam waktu dekat |
| `normal` | Kebutuhan reguler/rutin |

---

### 7.13 `bencana_master_jenis`

**Kolom `kategori`:**

| Nilai | Keterangan |
|---|---|
| `alam` | Bencana alam (gempa, banjir, longsor, dll.) |
| `non_alam` | Bencana non-alam (kebakaran industri, wabah, dll.) |
| `sosial` | Bencana sosial (konflik, kerusuhan, dll.) |

---

### 7.14 `laporan_kejadian`

**Kolom `is_valid`:**

| Nilai | Keterangan |
|---|---|
| `menunggu` | Laporan belum diverifikasi |
| `ya` | Laporan valid dan dikonfirmasi |
| `tidak` | Laporan tidak valid (salah/spam) |

---

### 7.15 `dokumen_surat_paraf`

**Kolom `status_paraf`:**

| Nilai | Keterangan |
|---|---|
| `menunggu` | Menunggu paraf dari pejabat |
| `disetujui` | Surat diparaf/disetujui |
| `ditolak` | Surat ditolak, perlu revisi |

---

### 7.16 `operasi_pleno_peserta`

**Kolom `status_kehadiran`:**

| Nilai | Keterangan |
|---|---|
| `hadir` | Peserta hadir dalam rapat pleno |
| `izin` | Peserta tidak hadir dengan izin |
| `tanpa_keterangan` | Peserta tidak hadir tanpa keterangan |

**Kolom `status_persetujuan`:**

| Nilai | Keterangan |
|---|---|
| `setuju` | Peserta menyetujui keputusan pleno |
| `tolak` | Peserta menolak keputusan pleno |
| `abstain` | Peserta abstain (tidak memilih) |

---

### 7.17 `operasi_mobilisasi_personil`

**Kolom `status_kehadiran`:**

| Nilai | Keterangan |
|---|---|
| `menuju_lokasi` | Personil dalam perjalanan ke lokasi |
| `di_lokasi` | Personil sudah tiba di lokasi |
| `kembali` | Personil dalam perjalanan kembali |
| `izin` | Personil izin tidak hadir |

---

### 7.18 `master_satuan`

**Kolom `kategori_satuan`:**

| Nilai | Keterangan |
|---|---|
| `berat` | Satuan berbasis berat (kg, ton, gram) |
| `volume` | Satuan berbasis volume (liter, ml, m³) |
| `panjang_luas` | Satuan panjang atau luas (m, km, ha) |
| `kemasan_logistik` | Satuan kemasan (karton, dus, sak, peti) |
| `personil_waktu` | Satuan personil atau waktu (orang, hari, jam) |
| `informal_lapangan` | Satuan informal lapangan (ikat, bungkus, genggam) |

---

### 7.19 `master_surat_jenis`

**Kolom `kategori`:**

| Nilai | Keterangan |
|---|---|
| `UMUM` | Surat umum organisasi |
| `OPERASI` | Surat terkait operasi kebencanaan |
| `LOGISTIK` | Surat terkait logistik |
| `ASET` | Surat terkait aset |
| `ORGANISASI` | Surat internal organisasi NU |

> **Catatan:** Kolom `kategori` di tabel ini menggunakan huruf kapital. Konsistensi ini wajib dipertahankan sesuai SQL dump.

---

### 7.20 `master_penerima_manfaat`

**Kolom `tipe_penerima`:**

| Nilai | Keterangan |
|---|---|
| `individu` | Penerima manfaat perorangan |
| `kk` | Penerima manfaat per kepala keluarga |
| `kelompok` | Penerima manfaat kelompok |
| `posko` | Penerima manfaat berbasis posko |
| `desa` | Penerima manfaat berbasis desa/kelurahan |
| `lembaga` | Penerima manfaat lembaga/organisasi |

---

### 7.21 `operasi_jurnal`

**Kolom `kategori_event`:**

| Nilai | Keterangan |
|---|---|
| `sistem` | Event otomatis dari sistem |
| `laporan` | Event dari pelaporan/sitrep |
| `aktivasi` | Event aktivasi status darurat |
| `respon` | Event respons operasional |
| `penugasan` | Event penugasan personil |
| `logistik` | Event pergerakan logistik |
| `aset` | Event penggunaan/perubahan aset |
| `personil` | Event mobilisasi personil |
| `posko` | Event terkait pos aju lapangan |
| `selesai` | Event penyelesaian operasi |

---

### 7.22 `operasi_periode`

**Kolom `status_periode`:**

| Nilai | Keterangan |
|---|---|
| `berjalan` | Periode operasi sedang berjalan |
| `selesai` | Periode operasi telah selesai |
| `diperpanjang` | Periode operasi diperpanjang |

---

## 8. Relasi Eloquent Model

Semua model Eloquent wajib menggunakan nama PK dan timestamp yang sudah dikonvensikan.

### 8.1 Konfigurasi Dasar Model

```php
// Contoh konfigurasi wajib pada setiap Model
class OperasiInsiden extends Model
{
    protected $primaryKey = 'id_insiden';
    public $incrementing  = true;
    protected $keyType    = 'int';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
}
```

### 8.2 `OperasiInsiden` — Relasi Utama

```php
class OperasiInsiden extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_insiden';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    // Satu insiden memiliki banyak sitrep
    public function sitreps(): HasMany
    {
        return $this->hasMany(OperasiSitrep::class, 'id_insiden', 'id_insiden');
    }

    // Satu insiden memiliki banyak assessment
    public function assessments(): HasMany
    {
        return $this->hasMany(AssessmentUtama::class, 'id_insiden', 'id_insiden');
    }

    // Satu insiden memiliki banyak penugasan
    public function penugasans(): HasMany
    {
        return $this->hasMany(OperasiPenugasan::class, 'id_insiden', 'id_insiden');
    }

    // Satu insiden termasuk dalam satu jenis bencana
    public function jenisBencana(): BelongsTo
    {
        return $this->belongsTo(BencanaMasterJenis::class, 'id_jenis_bencana', 'id_jenis_bencana');
    }

    // Satu insiden memiliki banyak jurnal aktivitas
    public function jurnals(): HasMany
    {
        return $this->hasMany(OperasiJurnal::class, 'id_insiden', 'id_insiden');
    }

    // Satu insiden memiliki satu catatan aktivasi
    public function aktivasi(): HasOne
    {
        return $this->hasOne(OperasiAktivasi::class, 'id_insiden', 'id_insiden');
    }

    // Satu insiden memiliki banyak riwayat status
    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusInsiden::class, 'id_insiden', 'id_insiden');
    }
}
```

### 8.3 `AssessmentUtama` — Relasi Detail

```php
class AssessmentUtama extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_assessment_utama';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    // Assessment induk berada di bawah satu insiden
    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    // Satu assessment memiliki banyak data dampak manusia
    public function dampakManusias(): HasMany
    {
        return $this->hasMany(AssessmentDampakManusia::class, 'id_assessment_utama', 'id_assessment_utama');
    }

    // Satu assessment memiliki banyak data kebutuhan mendesak
    public function kebutuhanMendesaks(): HasMany
    {
        return $this->hasMany(AssessmentKebutuhanMendesak::class, 'id_assessment_utama', 'id_assessment_utama');
    }
}
```

### 8.4 `LogistikMutasi` dan `LogistikStok`

```php
class LogistikMutasi extends Model
{
    protected $primaryKey = 'id_mutasi';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    // Setiap mutasi mengacu pada satu stok
    public function stok(): BelongsTo
    {
        return $this->belongsTo(LogistikStok::class, 'id_stok', 'id_stok');
    }
}

class LogistikStok extends Model
{
    protected $primaryKey = 'id_stok';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    // Satu stok memiliki banyak mutasi
    public function mutasis(): HasMany
    {
        return $this->hasMany(LogistikMutasi::class, 'id_stok', 'id_stok');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(LogistikGudang::class, 'id_gudang', 'id_gudang');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(LogistikBarangKatalog::class, 'id_barang', 'id_barang');
    }
}
```

### 8.5 `AuthUsers` — Relasi Akun

```php
class AuthUsers extends Model
{
    protected $primaryKey = 'id_pengguna';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    // Setiap pengguna memiliki satu peran utama
    public function peran(): BelongsTo
    {
        return $this->belongsTo(AuthRoles::class, 'id_peran', 'id_peran');
    }

    // Relasi Spatie Permission (via model_has_roles)
    public function modelRoles(): HasMany
    {
        return $this->hasMany(ModelHasRoles::class, 'model_id', 'id_pengguna');
    }

    // Profil pengguna (one-to-one)
    public function profil(): HasOne
    {
        return $this->hasOne(AuthPenggunaProfil::class, 'id_pengguna', 'id_pengguna');
    }

    // Keahlian pengguna
    public function keahlians(): HasMany
    {
        return $this->hasMany(AuthPenggunaKeahlian::class, 'id_pengguna', 'id_pengguna');
    }
}
```

### 8.6 `OperasiPleno` — Relasi Rapat

```php
class OperasiPleno extends Model
{
    protected $primaryKey = 'id_pleno';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    // Satu pleno memiliki banyak keputusan
    public function keputusans(): HasMany
    {
        return $this->hasMany(OperasiPlenoKeputusan::class, 'id_pleno', 'id_pleno');
    }

    // Satu pleno memiliki banyak peserta
    public function pesertas(): HasMany
    {
        return $this->hasMany(OperasiPlenoPeserta::class, 'id_pleno', 'id_pleno');
    }

    // Pleno berada dalam konteks satu insiden
    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }
}
```

---

## 9. Polymorphic Reference

### 9.1 Pola yang Digunakan

NURISK menggunakan pola **polymorphic reference manual** (bukan Laravel morphTo bawaan) pada tabel `operasi_jurnal`. Pola ini menggunakan dua kolom:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_referensi` | `BIGINT UNSIGNED NULL` | ID dari entitas yang direferensikan |
| `tabel_referensi` | `VARCHAR NULL` | Nama tabel entitas yang direferensikan |

### 9.2 Contoh Data `operasi_jurnal`

```
id_jurnal | id_insiden | kategori_event | id_referensi | tabel_referensi        | keterangan
1         | 42         | penugasan      | 17           | operasi_penugasan      | Penugasan TRC ditetapkan
2         | 42         | logistik       | 9            | logistik_permintaan    | Permintaan logistik diajukan
3         | 42         | sistem         | NULL         | NULL                   | Status insiden diperbarui
```

### 9.3 Tujuan

- `operasi_jurnal` berfungsi sebagai **audit trail terpusat** untuk semua aktivitas dalam satu insiden.
- Dengan pola polymorphic ini, satu tabel dapat mencatat referensi ke entitas dari domain manapun (`operasi_*`, `logistik_*`, `aset_*`, dll.) tanpa perlu FK fisik.

### 9.4 Larangan

> **DILARANG** memaksakan FK fisik (`FOREIGN KEY`) untuk kolom `id_referensi` di `operasi_jurnal`. FK fisik tidak kompatibel dengan referensi polimorfik ke banyak tabel berbeda.

### 9.5 Implementasi Eloquent (Manual Resolve)

```php
class OperasiJurnal extends Model
{
    protected $primaryKey = 'id_jurnal';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    /**
     * Resolve entitas referensi secara manual berdasarkan tabel_referensi.
     * Tidak menggunakan morphTo() karena penamaan tidak mengikuti konvensi
     * Laravel morph (morphable_type + morphable_id).
     */
    public function entitasReferensi(): ?Model
    {
        $map = [
            'operasi_penugasan'   => OperasiPenugasan::class,
            'logistik_permintaan' => LogistikPermintaan::class,
            'operasi_sitrep'      => OperasiSitrep::class,
            'aset_unit'           => AsetUnit::class,
            // tambahkan mapping sesuai kebutuhan
        ];

        $class = $map[$this->tabel_referensi] ?? null;

        if (!$class || !$this->id_referensi) {
            return null;
        }

        return $class::find($this->id_referensi);
    }
}
```

---

## 10. Index dan Performance

### 10.1 Aturan Index Wajib

| Kondisi | Aturan |
|---|---|
| Kolom FK | Wajib diindex |
| Kolom `status_*` yang sering difilter | Wajib diindex |
| Kolom `dibuat_pada` | Wajib diindex pada tabel transaksional besar |
| Kolom pencarian teks | Gunakan `FULLTEXT` jika tabel besar, hindari `LIKE '%...%'` tanpa index |
| Kombinasi filter umum | Pertimbangkan composite index |

### 10.2 Contoh Index Nyata

```sql
-- operasi_insiden: filter status dan sorting
CREATE INDEX idx_operasi_insiden_status   ON operasi_insiden(status_insiden);
CREATE INDEX idx_operasi_insiden_dibuat   ON operasi_insiden(dibuat_pada);
CREATE INDEX idx_operasi_insiden_bencana  ON operasi_insiden(id_jenis_bencana);

-- operasi_sitrep: filter per insiden dan status
CREATE INDEX idx_operasi_sitrep_insiden   ON operasi_sitrep(id_insiden);
CREATE INDEX idx_operasi_sitrep_status    ON operasi_sitrep(status_sitrep);

-- logistik_stok: filter per gudang dan barang
CREATE INDEX idx_logistik_stok_gudang     ON logistik_stok(id_gudang);
CREATE INDEX idx_logistik_stok_barang     ON logistik_stok(id_barang);

-- logistik_mutasi: filter per stok dan tipe
CREATE INDEX idx_logistik_mutasi_stok     ON logistik_mutasi(id_stok);
CREATE INDEX idx_logistik_mutasi_tipe     ON logistik_mutasi(tipe_mutasi);

-- assessment_utama: filter per insiden
CREATE INDEX idx_assessment_utama_insiden ON assessment_utama(id_insiden);

-- operasi_jurnal: filter per insiden dan kategori
CREATE INDEX idx_operasi_jurnal_insiden   ON operasi_jurnal(id_insiden);
CREATE INDEX idx_operasi_jurnal_kategori  ON operasi_jurnal(kategori_event);

-- auth_users: filter status akun
CREATE INDEX idx_auth_users_status        ON auth_users(status_akun);
```

### 10.3 Composite Index

Gunakan composite index untuk kombinasi filter yang sering dipakai bersamaan:

```sql
-- Cari sitrep berdasarkan insiden + status + urutan waktu
CREATE INDEX idx_sitrep_insiden_status_waktu
  ON operasi_sitrep(id_insiden, status_sitrep, dibuat_pada);

-- Cari mutasi berdasarkan stok + tipe
CREATE INDEX idx_mutasi_stok_tipe
  ON logistik_mutasi(id_stok, tipe_mutasi);
```

### 10.4 Anti-Pattern yang Dilarang

```sql
-- DILARANG: wildcard kiri pada LIKE tanpa fulltext index
WHERE nama_insiden LIKE '%banjir%'  -- tidak dapat menggunakan index

-- BENAR: gunakan FULLTEXT atau filter dengan kondisi yang dapat diindex
WHERE status_insiden = 'respon' AND dibuat_pada >= '2026-01-01'
```

---

## 11. Migration Rules

### 11.1 Timestamp Non-Standar Laravel

Laravel bawaan menggunakan `created_at` dan `updated_at`. NURISK menggunakan nama kolom berbahasa Indonesia. Oleh karena itu, **dilarang** menggunakan `$table->timestamps()` secara langsung.

```php
// DILARANG — menghasilkan kolom created_at dan updated_at
$table->timestamps();

// WAJIB — menggunakan nama kolom NURISK
$table->timestamp('dibuat_pada')->useCurrent();
$table->timestamp('diperbarui_pada')->nullable()->useCurrentOnUpdate();
```

### 11.2 Soft Delete Non-Standar Laravel

```php
// DILARANG — menghasilkan kolom deleted_at
$table->softDeletes();

// WAJIB — gunakan nama kolom NURISK
$table->softDeletes('dihapus_pada');
```

### 11.3 Foreign Key dengan `foreignId`

```php
// WAJIB: gunakan foreignId dengan constrained eksplisit
$table->foreignId('id_insiden')
      ->constrained('operasi_insiden', 'id_insiden')
      ->onDelete('cascade');

$table->foreignId('id_pengguna')
      ->constrained('auth_users', 'id_pengguna')
      ->onDelete('restrict');

$table->foreignId('id_stok')
      ->constrained('logistik_stok', 'id_stok')
      ->onDelete('cascade');
```

### 11.4 Primary Key Non-Standar

```php
// WAJIB: definisikan PK dengan nama domain
Schema::create('operasi_insiden', function (Blueprint $table) {
    $table->id('id_insiden');               // bigint unsigned auto_increment PK
    // atau untuk tabel master:
    $table->unsignedInteger('id_jenis_bencana')->autoIncrement();
    $table->primary('id_jenis_bencana');
});
```

### 11.5 Contoh Migration Lengkap

```php
Schema::create('assessment_dampak_manusia', function (Blueprint $table) {
    $table->id('id_dampak_manusia');

    $table->foreignId('id_assessment_utama')
          ->constrained('assessment_utama', 'id_assessment_utama')
          ->onDelete('cascade');

    $table->unsignedInteger('meninggal')->default(0);
    $table->unsignedInteger('luka_berat')->default(0);
    $table->unsignedInteger('luka_ringan')->default(0);
    $table->unsignedInteger('hilang')->default(0);
    $table->unsignedInteger('mengungsi')->default(0);
    $table->unsignedInteger('terdampak')->default(0);

    $table->timestamp('dibuat_pada')->useCurrent();
    $table->timestamp('diperbarui_pada')->nullable()->useCurrentOnUpdate();

    $table->index('id_assessment_utama');
});
```

### 11.6 Urutan Eksekusi Migration

Urutan migration harus memperhatikan dependensi FK antar tabel. Urutan yang disarankan:

```
1. Tabel master (bencana_master_jenis, master_satuan, dll.)
2. auth_roles
3. auth_users
4. auth_pengguna_profil
5. operasi_insiden
6. operasi_* (child tables)
7. assessment_*
8. logistik_gudang → logistik_barang_katalog → logistik_stok → logistik_mutasi
9. aset_master_* → aset_unit → aset_penggunaan
10. operasi_surat_keluar → dokumen_surat_paraf → dokumen_surat_tembusan
11. relawan_*
12. laporan_*
13. riwayat_*
```

---

## 12. Catatan Sinkronisasi SQL dan PRD

Bagian ini mendokumentasikan **ketidaksesuaian yang ditemukan** antara SQL dump aktual dengan standar yang dijelaskan dalam dokumen ini.

### 12.1 Ketidaksesuaian yang Diketahui

| # | Tabel/Kolom | Kondisi di SQL Dump | Standar Dokumen ini | Resolusi |
|---|---|---|---|---|
| 1 | `master_surat_jenis.kategori` | Nilai enum menggunakan HURUF KAPITAL (`UMUM`, `OPERASI`, dll.) | Konvensi umum menggunakan `snake_case` | **Ikuti SQL dump.** Nilai ENUM ini difreeze sebagai-is. |
| 2 | `laporan_kejadian.is_valid` | Menggunakan tipe ENUM dengan nilai string (`menunggu`, `ya`, `tidak`) | Nama kolom `is_` umumnya diasumsikan boolean | **Ikuti SQL dump.** Kolom `is_valid` adalah ENUM, bukan TINYINT(1). |
| 3 | `auth_users` + Spatie | `auth_users` menggunakan kolom `id_peran` (FK langsung) sekaligus menggunakan tabel Spatie `model_has_roles` | Dua mekanisme role berjalan paralel | **Dokumentasikan dua-duanya.** `id_peran` adalah peran utama; Spatie digunakan untuk permission granular. |
| 4 | Tabel `pengguna_jabatan` | Berasal dari konvensi asal `user_positions` | Sudah di-rename ke `pengguna_jabatan` di SQL dump | **Gunakan `pengguna_jabatan` seluruhnya.** Tidak menggunakan nama lama. |
| 5 | Tabel `relawan_penugasan` | Berasal dari konvensi asal `volunteer_assignments` | Sudah di-rename ke `relawan_penugasan` di SQL dump | **Gunakan `relawan_penugasan` seluruhnya.** |
| 6 | Kolom soft delete | Belum semua tabel soft-deletable dikonfirmasi via SQL dump | Daftar §6 berdasarkan logika bisnis | **Verifikasi ulang** dengan SQL dump final sebelum produksi. |

### 12.2 Tabel yang Perlu Konfirmasi Lebih Lanjut

Tabel-tabel berikut disebut dalam PRD namun perlu konfirmasi terhadap SQL dump final:

- `operasi_posaju` — pastikan PK adalah `id_posaju`
- `operasi_posaju_komandan` — pastikan FK ke `operasi_posaju.id_posaju`
- `sistem_*` — daftar lengkap tabel `sistem_` belum tersedia di semua versi dump
- `master_jabatan` — konfirmasi apakah terhubung langsung ke `auth_users` atau via `pengguna_jabatan`

### 12.3 Prosedur Pembaruan Dokumen Ini

Jika ditemukan ketidaksesuaian baru antara SQL dump dan dokumen ini:

1. Tambahkan baris baru di tabel §12.1.
2. Cantumkan tanggal penemuan dan nama engineer yang melaporkan.
3. Isi kolom **Resolusi** dengan keputusan yang diambil.
4. Update bagian relevan di dokumen ini jika resolusi mengubah standar.
5. Commit dengan pesan: `docs: sinkronisasi DATABASE_CONVENTION dengan SQL dump vX.X`.

---

*Dokumen ini merupakan referensi teknikal wajib untuk seluruh engineer NURISK. Setiap perubahan pada konvensi database harus direfleksikan di dokumen ini sebelum implementasi dimulai.*
