# DOMAIN_RULES.md — NURISK
**Platform Kebencanaan NU — Aturan Bisnis Semua Domain**
**Versi:** 1.0.0
**Tanggal:** 2026-06-16
**Status:** Pre-Production Reference

---

> **Catatan Dokumen:** File ini adalah referensi aturan bisnis teknikal yang bersifat normative.
> Setiap implementasi controller, service, policy, dan trigger database HARUS mengikuti
> aturan yang tertulis di dokumen ini. Konflik antara kode dan dokumen ini harus
> diselesaikan dengan menyesuaikan kode — bukan dokumen.

---

## Daftar Isi

- [A. Domain: INSIDEN](#a-domain-insiden)
- [B. Domain: PLENO](#b-domain-pleno)
- [C. Domain: SURAT](#c-domain-surat)
- [D. Domain: ASSESSMENT](#d-domain-assessment)
- [E. Domain: SITREP](#e-domain-sitrep)
- [F. Domain: RELAWAN](#f-domain-relawan)
- [G. Domain: ASSIGNMENT](#g-domain-assignment)
- [H. Domain: MOBILISASI](#h-domain-mobilisasi)
- [I. Domain: SHIFT / PERIODE OPERASI](#i-domain-shift--periode-operasi)
- [J. Domain: LOGISTIK](#j-domain-logistik)
- [K. Domain: POS AJU](#k-domain-pos-aju)
- [L. Domain: PENGUNGSIAN](#l-domain-pengungsian)
- [M. Domain: FEEDBACK KLASTER](#m-domain-feedback-klaster)
- [N. Domain: GAP KEBUTUHAN](#n-domain-gap-kebutuhan)
- [O. Domain: COMMAND CENTER](#o-domain-command-center)
- [P. Domain: ESKALASI](#p-domain-eskalasi)
- [Q. Domain: JURNAL OPERASI](#q-domain-jurnal-operasi)
- [R. Domain: ASET](#r-domain-aset)

---

## A. Domain: INSIDEN

### Tabel Utama

| Tabel | Peran |
|---|---|
| `operasi_insiden` | Record utama insiden bencana |
| `riwayat_status_insiden` | Audit log setiap transisi status insiden |
| `laporan_kejadian` | Sumber laporan warga/publik yang bisa menjadi asal insiden |
| `bencana_master_jenis` | Referensi jenis bencana |

### Kolom Kunci `operasi_insiden`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `kode_kejadian` | `VARCHAR(25)` | `NOT NULL UNIQUE` | Kode identifikasi unik, tidak dapat diubah setelah insert |
| `id_laporan_asal` | `INT` | FK → `laporan_kejadian.id` | Nullable — bisa NULL jika dibuat langsung oleh pcnu/pwnu |
| `id_jenis_bencana` | `INT` | FK → `bencana_master_jenis.id` | Jenis bencana wajib diisi |
| `id_pcnu` | `INT` | `NOT NULL` | Scope wilayah PCNU pengelola insiden |
| `status_insiden` | `ENUM` | Lihat daftar enum di bawah | Dikendalikan state machine |
| `status_operasi` | `ENUM` | — | Status operasional lapangan |
| `is_locked` | `TINYINT(1)` | Default `0` | Set `1` otomatis saat status `selesai` |
| `prioritas` | `ENUM` | — | Tingkat urgensi insiden |
| `waktu_mulai` | `DATETIME` | `NOT NULL` | Waktu resmi insiden dimulai |
| `waktu_selesai` | `DATETIME` | Nullable | Harus ≥ `waktu_mulai` |

### Enum `status_insiden`

```
draft → terverifikasi → respon → pemulihan → selesai
                                           ↘ dibatalkan
```

| Nilai | Keterangan |
|---|---|
| `draft` | Insiden baru dibuat, belum diverifikasi |
| `terverifikasi` | Telah divalidasi oleh pcnu/pwnu |
| `respon` | Operasi tanggap darurat aktif |
| `pemulihan` | Fase pemulihan berjalan |
| `selesai` | Operasi selesai, data terkunci |
| `dibatalkan` | Insiden dibatalkan, tidak dapat diaktifkan kembali |

### Aturan Bisnis

**BR-INSIDEN-001: Sumber Pembuatan Insiden**
- Insiden hanya dapat dibuat dari `laporan_kejadian` yang memiliki `is_valid = 'ya'`.
- Atau dibuat langsung oleh pengguna dengan role `pcnu` atau `pwnu` (tanpa referensi laporan).
- Jika dibuat dari laporan, kolom `id_laporan_asal` di `operasi_insiden` wajib diisi dengan ID laporan tersebut.
- Implementasi: validasi di `InsidenPolicy` sebelum `store()` di controller.

**BR-INSIDEN-002: Immutability `kode_kejadian`**
- `kode_kejadian` ditetapkan saat insert pertama dan **tidak dapat diubah** oleh siapapun.
- Implementasi: trigger database `BEFORE UPDATE` yang melempar error jika nilai `kode_kejadian` berubah, DAN validasi di layer aplikasi (`InsidenRequest`).
- Format kode: ditentukan oleh aplikasi pada waktu insert (contoh: `INC-2026-JATENG-0001`).

**BR-INSIDEN-003: Validasi Temporal**
- `waktu_selesai` tidak boleh bernilai lebih awal dari `waktu_mulai`.
- Implementasi: trigger database `tr_validate_temporal_incident` pada `BEFORE INSERT` dan `BEFORE UPDATE` di tabel `operasi_insiden`.
- Error message: `"waktu_selesai tidak boleh sebelum waktu_mulai"`.

**BR-INSIDEN-004: Pencatatan Transisi Status**
- Setiap perubahan nilai `status_insiden` WAJIB menghasilkan satu record baru di tabel `riwayat_status_insiden`.
- Record di `riwayat_status_insiden` berisi: `id_insiden`, `status_dari`, `status_ke`, `id_pengguna` yang mengubah, `waktu_transisi`, dan `catatan`.
- Implementasi: trigger `AFTER UPDATE` pada `operasi_insiden` untuk kolom `status_insiden`, atau dikelola di service layer sebelum `save()`.

**BR-INSIDEN-005: Lock Setelah Selesai**
- Saat `status_insiden` diubah menjadi `'selesai'`, kolom `is_locked` otomatis diset menjadi `1`.
- Setelah `is_locked = 1`, semua operasi `UPDATE` dan `DELETE` pada record insiden tersebut DITOLAK.
- Implementasi: trigger database `tr_lock_incident_data` pada `BEFORE UPDATE` — jika `is_locked = 1` (lama) dan ada perubahan selain audit field, trigger melempar error.
- Pengecualian: tidak ada pengecualian. `super_admin` sekalipun tidak dapat mengubah data insiden terkunci melalui aplikasi.

**BR-INSIDEN-006: Scope Wilayah via `id_pcnu`**
- Kolom `id_pcnu` menentukan PCNU pemilik dan pengelola insiden.
- Validasi scope dilakukan di `InsidenPolicy`: pengguna dengan role `pcnu` hanya dapat membuat/mengedit insiden yang `id_pcnu`-nya sesuai dengan PCNU mereka sendiri.
- Pengguna `pwnu` dapat mengakses semua insiden dalam wilayah PWNU Jawa Tengah.
- Pengguna `super_admin` dapat mengakses seluruh insiden.

**BR-INSIDEN-007: Prasyarat Assessment**
- `assessment_utama` hanya dapat dibuat untuk insiden yang `status_insiden` = `'terverifikasi'` atau `'respon'`.
- Lihat detail di [Domain Assessment](#d-domain-assessment).

**BR-INSIDEN-008: Larangan Reaktivasi Insiden Dibatalkan**
- Insiden dengan `status_insiden = 'dibatalkan'` tidak dapat dipindahkan ke status apapun.
- Implementasi: validasi di state machine service dan di trigger `BEFORE UPDATE`.
- Jika reaktivasi diperlukan, prosedur yang benar adalah membuat insiden baru dengan referensi baru.

---

## B. Domain: PLENO

### Tabel Utama

| Tabel | Peran |
|---|---|
| `operasi_pleno` | Record pleno/rapat resmi dalam operasi |
| `operasi_pleno_keputusan` | Setiap keputusan yang dihasilkan pleno |
| `operasi_pleno_peserta` | Daftar peserta pleno dan status hadir/setuju/tolak |

### Kolom Kunci `operasi_pleno`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_insiden` | `INT` | FK ke `operasi_insiden.id` |
| `status_pleno` | `ENUM` | Status pleno (lihat di bawah) |
| `id_pemimpin_rapat` | `INT` | FK ke `auth_users.id` |
| `tanggal_pleno` | `DATETIME` | Waktu pelaksanaan pleno |
| `catatan_umum` | `TEXT` | Notulensi umum |

### Kolom Kunci `operasi_pleno_peserta`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_pleno` | `INT` | FK ke `operasi_pleno.id` |
| `id_pengguna` | `INT` | FK ke `auth_users.id` |
| `hak_suara` | `TINYINT(1)` | `1` = memiliki hak suara, `0` = hanya hadir |
| `keputusan_peserta` | `ENUM` | `setuju`, `tolak`, `abstain` |
| `catatan_peserta` | `TEXT` | Wajib diisi jika `keputusan_peserta = 'tolak'` |

### Aturan Bisnis

**BR-PLENO-001: Minimal Penandatangan Berhak Suara**
- Sebuah pleno hanya valid jika terdapat minimal **1 peserta** dengan `hak_suara = 1` di tabel `operasi_pleno_peserta`.
- Validasi dilakukan sebelum pleno dapat diubah dari status `draft` ke status aktif/final.
- Implementasi: validasi di service atau `PlenoPolicy`.

**BR-PLENO-002: Pencatatan Keputusan**
- Setiap keputusan yang dihasilkan dari rapat pleno wajib dimasukkan sebagai satu record di tabel `operasi_pleno_keputusan`.
- Keputusan mencakup: penunjukan komandan, pembukaan pos aju, eskalasi insiden, perpanjangan periode, dll.
- Satu pleno dapat menghasilkan banyak keputusan (`1:N`).

**BR-PLENO-003: Immutability Pleno FINAL**
- Pleno yang `status_pleno = 'final'` tidak dapat diubah atau dihapus.
- Implementasi: validasi di `PlenoPolicy` dan `BEFORE UPDATE` trigger atau observer Laravel.

**BR-PLENO-004: Catatan Wajib untuk Penolakan**
- Jika `operasi_pleno_peserta.keputusan_peserta = 'tolak'`, maka kolom `catatan_peserta` WAJIB diisi (bukan NULL, bukan string kosong).
- Implementasi: validasi di `PlenoPesertaRequest` dan trigger `BEFORE INSERT/UPDATE` di `operasi_pleno_peserta`.

**BR-PLENO-005: Eskalasi Melalui Pleno Resmi**
- Perubahan level eskalasi insiden (tabel `operasi_eskalasi`) hanya dapat dilakukan jika ada record pleno yang statusnya `final` dan memiliki keputusan eskalasi di `operasi_pleno_keputusan`.
- Eskalasi yang tidak memiliki `id_pleno_keputusan` yang valid akan ditolak.

**BR-PLENO-006: Penunjukan Komandan dan Koordinator Klaster via Pleno**
- Pengisian `operasi_posaju_komandan.id_pleno_penunjukan` wajib mereferensi record di `operasi_pleno_keputusan`.
- Penunjukan koordinator klaster di `operasi_klaster_koordinator` juga harus memiliki referensi ke keputusan pleno.
- Penunjukan tanpa backing keputusan pleno ditolak oleh aplikasi.

**BR-PLENO-007: Keputusan Pembukaan Pos Aju**
- Pembukaan pos aju baru di `operasi_posaju` wajib memiliki `id_pleno_keputusan` yang valid.
- Pos aju yang tidak memiliki referensi keputusan pleno tidak dapat diaktifkan (status tidak dapat dipindah dari `draft`).

**BR-PLENO-008: Pleno Lintas Wilayah oleh PWNU**
- Pleno yang melibatkan insiden dari lebih dari satu PCNU hanya dapat dibuat oleh pengguna dengan role `pwnu`.
- Implementasi: `PlenoPolicy` memeriksa apakah `id_insiden` yang terkait milik lintas `id_pcnu`, dan jika ya, memastikan user adalah `pwnu`.

---

## C. Domain: SURAT

### Tabel Utama

| Tabel | Peran |
|---|---|
| `operasi_surat_keluar` | Record utama setiap dokumen surat |
| `dokumen_surat_paraf` | Urutan dan status paraf per penandatangan |
| `dokumen_surat_tembusan` | Daftar pihak yang menerima tembusan surat |
| `master_surat_jenis` | Jenis surat dan format penomoran |
| `master_surat_template` | Template Blade/HTML untuk render PDF |
| `master_jabatan_penandatangan` | Daftar jabatan yang berhak menandatangani |

### Kolom Kunci `operasi_surat_keluar`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `nomor_surat` | `VARCHAR` | Di-generate otomatis, unik per jenis |
| `id_jenis_surat` | `INT` | FK → `master_surat_jenis.id` |
| `id_template` | `INT` | FK → `master_surat_template.id`, nullable |
| `status_surat` | `ENUM` | `draft`, `proses_paraf`, `finalized`, `ditolak` |
| `id_insiden` | `INT` | FK → `operasi_insiden.id`, nullable |
| `id_incident_assignment` | `INT` | FK → `operasi_penugasan.id`, nullable (khusus surat tugas) |
| `file_pdf_path` | `VARCHAR` | Path file PDF setelah finalisasi |

### Kolom Kunci `dokumen_surat_paraf`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_surat` | `INT` | FK → `operasi_surat_keluar.id` |
| `urutan` | `INT` | Urutan paraf (1, 2, 3, ...) |
| `id_pengguna` | `INT` | FK → `auth_users.id` |
| `id_jabatan_penandatangan` | `INT` | FK → `master_jabatan_penandatangan.id` |
| `status_paraf` | `ENUM` | `menunggu`, `disetujui`, `ditolak` |
| `catatan_paraf` | `TEXT` | Alasan penolakan, wajib jika `ditolak` |
| `waktu_paraf` | `DATETIME` | Timestamp saat paraf diberikan |

### Aturan Bisnis

**BR-SURAT-001: Surat Adalah Dokumen Legal**
- Surat dalam NURISK bukan sekadar file upload. Surat adalah dokumen yang di-generate sistem dengan nomor resmi, alur paraf terstruktur, dan output PDF terverifikasi.
- Tidak ada mekanisme upload surat eksternal tanpa melewati alur ini.

**BR-SURAT-002: Penomoran Otomatis**
- Nomor surat di-generate secara otomatis berdasarkan kolom `format_nomor` yang tersimpan di `master_surat_jenis`.
- Format umumnya mengandung komponen: kode organisasi, kode jenis surat, nomor urut, bulan, tahun (contoh: `001/NU-JATENG/SK/VI/2026`).
- Nomor surat bersifat immutable setelah surat keluar dari status `draft`.
- Implementasi: fungsi penomoran di service `SuratNomorService`, dipanggil saat surat pertama kali dibuat.

**BR-SURAT-003: Alur Paraf Berurutan**
- Proses paraf di `dokumen_surat_paraf` berjalan berdasarkan nilai kolom `urutan` secara ascending (1, 2, 3, ...).
- Paraf ke-N baru dapat dilakukan setelah paraf ke-(N-1) berstatus `disetujui`.
- Sistem tidak memperbolehkan lompat urutan paraf.
- Implementasi: validasi di `ParafService` sebelum memproses paraf dari pengguna.

**BR-SURAT-004: Penolakan Paraf Mengembalikan ke DRAFT**
- Jika satu record di `dokumen_surat_paraf` diset `status_paraf = 'ditolak'`, maka status surat induknya di `operasi_surat_keluar` otomatis kembali menjadi `'draft'`.
- Seluruh record paraf yang sudah `disetujui` sebelumnya direset ke `menunggu`.
- Implementasi: observer Laravel `ParafObserver` pada event `updated`, atau trigger `AFTER UPDATE` pada `dokumen_surat_paraf`.
- `catatan_paraf` WAJIB diisi saat menolak paraf.

**BR-SURAT-005: Immutability Surat FINALIZED**
- Surat dengan `status_surat = 'finalized'` tidak dapat diubah, dihapus, atau diparaf ulang.
- Implementasi: `SuratPolicy` menolak semua operasi write pada surat berstatus `finalized`.

**BR-SURAT-006: Generate PDF Saat Finalisasi**
- Saat status surat berubah menjadi `'finalized'`, sistem WAJIB men-generate file PDF.
- Path file PDF yang dihasilkan disimpan di kolom `file_pdf_path` di `operasi_surat_keluar`.
- Jika proses generate PDF gagal, status tidak boleh berubah ke `finalized`.
- Implementasi: `SuratFinalisasiJob` di-dispatch setelah validasi finalisasi berhasil.

**BR-SURAT-007: Pencatatan Tembusan**
- Setiap penerima tembusan surat dicatat sebagai satu record di tabel `dokumen_surat_tembusan`.
- Tembusan dapat berupa internal (referensi ke `auth_users.id`) maupun eksternal (nama bebas).
- Tembusan hanya dapat ditambahkan selama surat berstatus `draft` atau `proses_paraf`.

**BR-SURAT-008: Surat Tugas Terkait Penugasan Operasi**
- Surat tugas operasional harus memiliki kolom `id_incident_assignment` yang terisi dan valid (FK ke `operasi_penugasan.id`).
- Ini membentuk tautan legal antara dokumen surat dan record penugasan operasi.
- Surat dengan `id_jenis_surat` bertipe surat-tugas yang tidak memiliki `id_incident_assignment` ditolak saat validasi.

**BR-SURAT-009: Penggunaan Template**
- Jika `master_surat_template` memiliki template untuk jenis surat tertentu (`id_jenis_surat`), maka surat HARUS menggunakan template tersebut.
- Template di-render via Blade dan hasilnya dikonversi ke PDF.
- Surat tidak boleh melewati template jika template tersedia untuk jenisnya.

**BR-SURAT-010: Validasi Jabatan Penandatangan**
- Pengguna yang melakukan paraf harus memiliki jabatan yang terdaftar di `master_jabatan_penandatangan`.
- Validasi: `id_jabatan_penandatangan` di record `dokumen_surat_paraf` harus ada di tabel `master_jabatan_penandatangan`.
- Pengguna tanpa jabatan yang sesuai tidak dapat di-assign ke slot paraf surat tersebut.

---

## D. Domain: ASSESSMENT

### Tabel Utama

| Tabel | Peran |
|---|---|
| `assessment_utama` | Record utama setiap kali assessment dilakukan |
| `assessment_dampak_manusia` | Detail dampak korban jiwa dan pengungsi |
| `assessment_kebutuhan_mendesak` | Detail kebutuhan mendesak (pangan, medis, dll) |

### Kolom Kunci `assessment_utama`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_insiden` | `INT` | FK → `operasi_insiden.id` |
| `jenis_laporan` | `ENUM` | `kaji_cepat` atau `pendataan_lanjutan` |
| `is_latest` | `TINYINT(1)` | `1` jika ini assessment paling baru untuk insiden tersebut |
| `koordinat_lat` | `DECIMAL` | Latitude lokasi assessment |
| `koordinat_lng` | `DECIMAL` | Longitude lokasi assessment |
| `dihapus_pada` | `TIMESTAMP` | Soft delete timestamp |

### Kolom Kunci `assessment_dampak_manusia`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_assessment` | `INT` | FK → `assessment_utama.id` |
| `meninggal` | `INT` | Jumlah korban meninggal |
| `hilang` | `INT` | Jumlah korban hilang |
| `menderita_mengungsi` | `INT` | Jumlah warga yang menderita/mengungsi |

### Kolom Kunci `assessment_kebutuhan_mendesak`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_assessment_utama` | `INT` | FK → `assessment_utama.id_assessment_utama` |
| `nama_kebutuhan` | `VARCHAR` | Nama spesifik kebutuhan (misal: Air Bersih, Tenda) |
| `jumlah` | `INT` | Kuantitas kebutuhan |
| `satuan` | `VARCHAR` | Satuan pengukuran (misal: liter, unit, paket) |
| `catatan` | `TEXT` | Catatan tambahan kondisional |

### Aturan Bisnis

**BR-ASSESSMENT-001: Prasyarat Status Insiden**
- `assessment_utama` hanya dapat dibuat untuk insiden dengan `status_insiden = 'terverifikasi'` atau `'respon'`.
- Membuat assessment untuk insiden berstatus `draft`, `pemulihan`, `selesai`, atau `dibatalkan` ditolak.
- Implementasi: validasi di `AssessmentPolicy` dan `AssessmentRequest`.

**BR-ASSESSMENT-002: Satu Assessment Aktif per Insiden (`is_latest`)**
- Hanya boleh ada **satu** record `assessment_utama` dengan `is_latest = 1` untuk setiap `id_insiden` pada satu waktu.
- Implementasi: trigger database `tr_single_latest_assessment` pada `BEFORE INSERT` dan `BEFORE UPDATE` — ketika `is_latest` diset ke `1` untuk `id_insiden` tertentu, trigger otomatis mengeset `is_latest = 0` untuk semua record lain dengan `id_insiden` yang sama.

**BR-ASSESSMENT-003: Mekanisme Trigger `tr_single_latest_assessment`**
```sql
-- Pseudocode trigger
BEFORE INSERT ON assessment_utama
IF NEW.is_latest = 1 THEN
    UPDATE assessment_utama
    SET is_latest = 0
    WHERE id_insiden = NEW.id_insiden
      AND is_latest = 1
      AND dihapus_pada IS NULL;
END IF;
```
- Logika yang sama berlaku untuk `BEFORE UPDATE`.

**BR-ASSESSMENT-004: Jenis Laporan**
- `jenis_laporan = 'kaji_cepat'`: assessment awal yang dilakukan segera setelah insiden terverifikasi. Fokus pada data cepat dan kasar.
- `jenis_laporan = 'pendataan_lanjutan'`: assessment mendalam yang dilakukan setelah kaji cepat. Memerlukan data yang lebih terperinci.
- Tidak ada pembatasan berapa kali `pendataan_lanjutan` dapat dibuat, namun hanya satu yang menjadi `is_latest = 1`.

**BR-ASSESSMENT-005: Kelengkapan Data Dampak Manusia**
- Setiap `assessment_utama` HARUS memiliki tepat satu record di `assessment_dampak_manusia`.
- Kolom `meninggal`, `hilang`, dan `menderita_mengungsi` tidak boleh bernilai negatif.
- Implementasi: validasi di `AssessmentRequest` dengan rule `min:0`.

**BR-ASSESSMENT-006: Kelengkapan Data Kebutuhan Mendesak**
- Setiap `assessment_utama` HARUS memiliki tepat satu record di `assessment_kebutuhan_mendesak`.
- Tidak ada nilai wajib (bisa diisi keterangan "tidak ada kebutuhan mendesak"), tetapi record harus ada.

**BR-ASSESSMENT-007: Validasi Koordinat**
- Kolom `koordinat_lat` harus berada dalam rentang `-11.0` hingga `6.0` (batas wilayah Indonesia).
- Kolom `koordinat_lng` harus berada dalam rentang `95.0` hingga `141.0` (batas wilayah Indonesia).
- Koordinat `0,0` (null island) ditolak.
- Implementasi: custom validation rule `IndonesianCoordinate` di Laravel.

**BR-ASSESSMENT-008: Larangan Hapus Assessment Basis Sitrep**
- Assessment yang sudah dijadikan basis pembuatan sitrep — yaitu yang `id`-nya direferensikan oleh kolom `id_assessment_basis` di tabel `operasi_sitrep` — tidak dapat dihapus (baik hard delete maupun soft delete).
- Implementasi: validasi di `AssessmentPolicy@delete` dengan query cek ke `operasi_sitrep`.

**BR-ASSESSMENT-009: Soft Delete**
- Penghapusan assessment menggunakan soft delete melalui pengisian kolom `dihapus_pada`.
- Record dengan `dihapus_pada IS NOT NULL` tidak muncul di query standar (gunakan Laravel `SoftDeletes` trait).
- Hard delete pada `assessment_utama` dilarang dari interface aplikasi.

---

## E. Domain: SITREP

### Tabel Utama

| Tabel | Peran |
|---|---|
| `operasi_sitrep` | Situasi Report periodik selama operasi berlangsung |

### Kolom Kunci `operasi_sitrep`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_insiden` | `INT` | FK → `operasi_insiden.id` |
| `nomor_sitrep` | `INT` | Nomor urut sitrep per insiden (1, 2, 3, ...) |
| `id_assessment_basis` | `INT` | FK → `assessment_utama.id` (wajib) |
| `status_sitrep` | `ENUM` | `draft`, `ditinjau`, `final` |
| `snapshot_dampak` | `JSON` | Snapshot data dampak saat finalisasi |
| `hash_snapshot` | `VARCHAR(64)` | SHA-256 hash dari snapshot untuk audit |
| `file_pdf_path` | `VARCHAR` | Path PDF setelah generate |
| `id_penfinalisasi` | `INT` | FK → `auth_users.id`, wajib saat finalisasi |
| `dihapus_pada` | `TIMESTAMP` | Soft delete timestamp |

### Aturan Bisnis

**BR-SITREP-001: Penomoran Berurutan per Insiden**
- Kolom `nomor_sitrep` harus berurutan mulai dari `1` untuk setiap insiden.
- Nomor berikutnya = `MAX(nomor_sitrep) + 1` untuk `id_insiden` yang sama.
- Implementasi: dihitung di service `SitrepService` saat pembuatan, dilindungi oleh UNIQUE constraint `(id_insiden, nomor_sitrep)` di database.

**BR-SITREP-002: Uniqueness Nomor Sitrep per Insiden**
- Database memiliki UNIQUE constraint pada `(id_insiden, nomor_sitrep)`.
- Tidak boleh ada dua sitrep dengan nomor yang sama dalam satu insiden yang sama.
- Jika terjadi race condition (concurrent insert), constraint database yang menjadi pengaman terakhir.

**BR-SITREP-003: Auto-Populate Snapshot**
- Saat sitrep difinalisasi (`status_sitrep` diubah menjadi `'final'`), trigger `tr_auto_snapshot_sitrep` secara otomatis mengisi kolom-kolom snapshot:
  - `snapshot_dampak`: diambil dari `assessment_dampak_manusia` yang terkait `id_assessment_basis`.
  - Kolom-kolom snapshot lain sesuai yang tersedia di schema.
- Snapshot berfungsi sebagai "foto" kondisi saat sitrep difinalkan.

**BR-SITREP-004: Immutability Snapshot Setelah FINAL**
- Setelah `status_sitrep = 'final'`, kolom-kolom snapshot (`snapshot_dampak`, dll.) tidak dapat diubah.
- Implementasi: trigger `BEFORE UPDATE` yang memeriksa status lama — jika `OLD.status_sitrep = 'final'`, semua update pada kolom snapshot ditolak.

**BR-SITREP-005: Hash Snapshot untuk Audit Integritas**
- Kolom `hash_snapshot` berisi nilai SHA-256 dari konten snapshot yang difinalkan.
- Hash dihitung otomatis saat finalisasi dan disimpan bersama data.
- Sistem dapat memverifikasi integritas data dengan menghitung ulang hash dan membandingkannya.
- Implementasi: dihitung di service layer sebelum penyimpanan (bukan di trigger).

**BR-SITREP-006: Larangan Hapus Sitrep FINAL**
- Sitrep dengan `status_sitrep = 'final'` tidak dapat dihapus, baik hard delete maupun soft delete.
- Implementasi: `SitrepPolicy@delete` memeriksa status sebelum mengizinkan penghapusan.

**BR-SITREP-007: PDF Tersimpan di `file_pdf_path`**
- Setelah proses generate PDF berhasil, path relatif file PDF disimpan di kolom `file_pdf_path`.
- Jika PDF belum di-generate, kolom ini bernilai `NULL`.
- Implementasi: `SitrepPdfJob` di-dispatch setelah finalisasi, mengupdate `file_pdf_path` setelah selesai.

**BR-SITREP-008: `id_penfinalisasi` Wajib Saat Finalisasi**
- Saat status diubah ke `'final'`, kolom `id_penfinalisasi` WAJIB diisi dengan ID pengguna yang melakukan finalisasi.
- Implementasi: diset di service layer (`Auth::id()`) dan divalidasi di `SitrepRequest`.

**BR-SITREP-009: Referensi Assessment Basis Wajib**
- Setiap sitrep HARUS memiliki `id_assessment_basis` yang valid (bukan NULL).
- Sitrep yang dibuat tanpa referensi assessment ditolak di validasi.
- Ini memastikan setiap sitrep memiliki landasan data assessment yang terverifikasi.

**BR-SITREP-010: Soft Delete**
- Penghapusan sitrep non-final menggunakan soft delete via kolom `dihapus_pada`.
- Hard delete pada `operasi_sitrep` dilarang dari interface aplikasi.

---

## F. Domain: RELAWAN

### Tabel Utama

| Tabel | Peran |
|---|---|
| `relawan_pendaftaran` | Pendaftaran relawan ke kebutuhan relawan tertentu |
| `relawan_penugasan` | Penugasan relawan aktif ke insiden |
| `auth_pengguna_keahlian` | Keahlian yang dimiliki pengguna/relawan |
| `auth_keahlian_master` | Master data keahlian yang tersedia |

### Kolom Kunci `relawan_pendaftaran`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_pengguna` | `INT` | FK → `auth_users.id` |
| `id_relawan_kebutuhan` | `INT` | FK ke tabel kebutuhan relawan |
| `status_pendaftaran` | `ENUM` | `menunggu`, `disetujui`, `ditolak` |
| `id_pcnu_validasi` | `INT` | PCNU yang memvalidasi pendaftaran |

### Kolom Kunci `relawan_penugasan`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_pengguna` | `INT` | FK → `auth_users.id` |
| `id_insiden` | `INT` | FK → `operasi_insiden.id` |
| `status_penugasan` | `ENUM` | Status penugasan relawan |
| `dihapus_pada` | `TIMESTAMP` | Soft delete timestamp |

### Aturan Bisnis

**BR-RELAWAN-001: Validasi oleh PCNU Berwenang**
- Pendaftaran relawan di `relawan_pendaftaran` harus divalidasi oleh pengguna dengan role `pcnu` yang `id_pcnu`-nya sesuai dengan scope kebutuhan relawan tersebut.
- Pengguna `pwnu` dapat memvalidasi semua pendaftaran dalam wilayah PWNU Jawa Tengah.
- Implementasi: `RelawanPolicy` memeriksa scope pcnu pengguna yang melakukan validasi.

**BR-RELAWAN-002: Unique Constraint Pendaftaran**
- Terdapat UNIQUE constraint pada kombinasi `(id_pengguna, id_relawan_kebutuhan)` di tabel `relawan_pendaftaran`.
- Seorang pengguna tidak dapat mendaftar lebih dari satu kali untuk kebutuhan relawan yang sama.
- Database constraint ini adalah pengaman terakhir; validasi juga dilakukan di layer aplikasi.

**BR-RELAWAN-003: Prasyarat Verifikasi untuk Penugasan**
- Relawan hanya dapat ditugaskan ke insiden (record baru di `relawan_penugasan`) jika status akun mereka di `auth_pengguna_profil` adalah `'aktif'`.
- Selain itu, pendaftaran mereka di `relawan_pendaftaran` harus berstatus `'disetujui'`.
- Relawan yang belum diverifikasi atau ditolak tidak dapat ditambahkan ke penugasan.

**BR-RELAWAN-004: Cross-PCNU Assignment Tanpa Ubah Organisasi Asal**
- Relawan dapat ditugaskan ke insiden yang `id_pcnu`-nya berbeda dengan PCNU asal relawan.
- Data organisasi asal relawan di `auth_pengguna_profil` tidak berubah karena penugasan lintas PCNU.
- Scope asal relawan tetap mengikuti PCNU tempat relawan terdaftar.

**BR-RELAWAN-005: Keahlian Dicatat di `auth_pengguna_keahlian`**
- Keahlian relawan dicatat sebagai record di `auth_pengguna_keahlian` dengan FK ke `auth_keahlian_master.id`.
- Satu pengguna dapat memiliki banyak keahlian (relasi `1:N`).
- Data keahlian digunakan untuk pencocokan (matching) relawan ke kebutuhan lapangan.

**BR-RELAWAN-006: Soft Delete Penugasan**
- Penghapusan record di `relawan_penugasan` menggunakan soft delete via kolom `dihapus_pada`.
- Record dengan `dihapus_pada IS NOT NULL` dianggap tidak aktif dan tidak muncul di query standar.

---

## G. Domain: ASSIGNMENT (Penugasan Operasi)

### Tabel Utama

| Tabel | Peran |
|---|---|
| `operasi_penugasan` | Pemberian otoritas sementara kepada pengguna dalam insiden |

### Kolom Kunci `operasi_penugasan`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_insiden` | `INT` | FK → `operasi_insiden.id` |
| `id_pengguna` | `INT` | FK → `auth_users.id` (yang ditugaskan) |
| `peran_otoritas` | `ENUM` | Peran yang diberikan dalam operasi (lihat daftar di bawah) |
| `waktu_mulai` | `DATETIME` | Wajib diisi, waktu assignment berlaku |
| `waktu_selesai` | `DATETIME` | Nullable, waktu assignment berakhir |
| `ditugaskan_oleh` | `INT` | FK → `auth_users.id` (yang memberi penugasan), wajib diisi |
| `asal_lingkup` | `VARCHAR` | Scope asal pengguna (misal: PCNU Semarang) |
| `tujuan_lingkup` | `VARCHAR` | Scope tujuan operasi (misal: PCNU Demak) |
| `status_penugasan` | `ENUM` | Default `'aktif'` |
| `dihapus_pada` | `TIMESTAMP` | Soft delete timestamp |

### Enum `peran_otoritas`

| Nilai | Keterangan |
|---|---|
| `komandan_insiden` | Pemimpin operasi keseluruhan |
| `trc` | Tim Reaksi Cepat |
| `relawan` | Relawan operasional |
| `medis` | Tim medis |
| `logistik` | Tim logistik |
| `operator` | Operator sistem/komunikasi |

### Aturan Bisnis

**BR-ASSIGNMENT-001: Fungsi Assignment**
- `operasi_penugasan` memberikan **otoritas sementara** kepada pengguna dalam konteks satu insiden.
- Otoritas ini berbeda dari role permanen di `auth_roles` — assignment bersifat kontekstual dan terikat waktu.
- Contoh: pengguna dengan role `relawan` bisa mendapat assignment `komandan_insiden` untuk insiden tertentu.

**BR-ASSIGNMENT-002: `peran_otoritas` Mengikuti Enum Terdefinisi**
- Nilai `peran_otoritas` HARUS salah satu dari: `komandan_insiden`, `trc`, `relawan`, `medis`, `logistik`, `operator`.
- Penambahan nilai enum baru memerlukan migrasi database dan update dokumentasi ini.

**BR-ASSIGNMENT-003: `waktu_mulai` Wajib**
- Setiap record `operasi_penugasan` harus memiliki `waktu_mulai` yang terisi.
- Assignment tanpa `waktu_mulai` ditolak di validasi.

**BR-ASSIGNMENT-004: Penghentian Assignment**
- Assignment dianggap berakhir saat kolom `waktu_selesai` diisi dengan timestamp yang valid.
- `waktu_selesai` harus ≥ `waktu_mulai`.
- Assignment yang belum berakhir (`waktu_selesai IS NULL`) dianggap masih aktif.

**BR-ASSIGNMENT-005: Multi-Insiden Assignment**
- Satu pengguna dapat memiliki assignment aktif di beberapa insiden yang berbeda secara bersamaan.
- Tidak ada constraint unique `(id_pengguna)` — constraint hanya pada kombinasi relevan jika diperlukan per role.

**BR-ASSIGNMENT-006: `ditugaskan_oleh` Wajib**
- Setiap assignment HARUS mencantumkan `ditugaskan_oleh` — ID pengguna yang memberikan penugasan.
- Field ini tidak boleh NULL dan divalidasi saat insert.

**BR-ASSIGNMENT-007: Soft Delete**
- Pembatalan atau penghapusan assignment menggunakan soft delete via kolom `dihapus_pada`.

**BR-ASSIGNMENT-008: Pencatatan Cross-Region**
- Jika pengguna ditugaskan ke insiden yang berada di luar PCNU asalnya, kolom `asal_lingkup` diisi dengan identifier PCNU asal dan `tujuan_lingkup` diisi dengan identifier PCNU tujuan.
- Ini memungkinkan analitik mobilisasi lintas wilayah.

---

## H. Domain: MOBILISASI

### Tabel Utama

| Tabel | Peran |
|---|---|
| `operasi_mobilisasi_personil` | Pencatatan kehadiran fisik personel di lapangan |

### Kolom Kunci `operasi_mobilisasi_personil`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_incident_assignment` | `INT` | FK → `operasi_penugasan.id` (wajib) |
| `id_pengguna` | `INT` | FK → `auth_users.id` |
| `status_kehadiran` | `ENUM` | `menuju_lokasi`, `di_lokasi`, `kembali`, `izin` |
| `waktu_berangkat` | `DATETIME` | Waktu personel berangkat ke lokasi |
| `waktu_pulang` | `DATETIME` | Waktu personel kembali dari lokasi |

### Aturan Bisnis

**BR-MOBILISASI-001: Mobilisasi adalah Pergerakan Fisik**
- `operasi_mobilisasi_personil` mencatat pergerakan fisik personel ke dan dari lokasi bencana.
- Ini berbeda dari `operasi_penugasan` yang mencatat pemberian otoritas/peran — mobilisasi mencatat kehadiran aktual.

**BR-MOBILISASI-002: Terikat ke Penugasan**
- Setiap record mobilisasi HARUS terhubung ke record `operasi_penugasan` yang valid.
- Mobilisasi tidak dapat berdiri sendiri tanpa adanya penugasan yang mendasarinya.
- FK `id_incident_assignment` adalah NOT NULL.

**BR-MOBILISASI-003: Enum Status Kehadiran**
- `menuju_lokasi`: personel dalam perjalanan ke lokasi bencana.
- `di_lokasi`: personel sudah tiba dan aktif di lokasi.
- `kembali`: personel sudah kembali dari lokasi.
- `izin`: personel sedang izin tidak hadir di lapangan.

**BR-MOBILISASI-004: Pencatatan Waktu**
- `waktu_berangkat`: diisi saat status diubah ke `menuju_lokasi`.
- `waktu_pulang`: diisi saat status diubah ke `kembali`.
- `waktu_pulang` tidak boleh sebelum `waktu_berangkat` jika keduanya diisi.

**BR-MOBILISASI-005: Satu Lokasi Aktif per Pengguna**
- Seorang pengguna hanya dapat berstatus `di_lokasi` untuk **satu insiden aktif** pada satu waktu.
- Implementasi: sebelum mengubah status menjadi `di_lokasi`, query untuk memastikan tidak ada record mobilisasi lain dengan `id_pengguna` yang sama dan `status_kehadiran = 'di_lokasi'` pada insiden yang masih aktif.

---

## I. Domain: SHIFT / PERIODE OPERASI

### Tabel Utama

| Tabel | Peran |
|---|---|
| `operasi_periode` | Periode/fase operasi dalam sebuah insiden |

### Kolom Kunci `operasi_periode`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_insiden` | `INT` | FK → `operasi_insiden.id` |
| `id_pleno_keputusan` | `INT` | FK → `operasi_pleno_keputusan.id` (wajib) |
| `label_periode` | `VARCHAR` | Nama deskriptif periode (wajib) |
| `status_periode` | `ENUM` | `berjalan`, `selesai`, `diperpanjang` |
| `tanggal_mulai` | `DATE` | Tanggal periode dimulai |
| `tanggal_selesai` | `DATE` | Tanggal periode berakhir |

### Aturan Bisnis

**BR-PERIODE-001: Wajib Berdasarkan Keputusan Pleno**
- Setiap periode operasi harus memiliki `id_pleno_keputusan` yang valid dan tidak NULL.
- Pembukaan periode tanpa keputusan pleno ditolak di layer validasi.

**BR-PERIODE-002: `label_periode` Harus Deskriptif**
- Kolom `label_periode` wajib diisi dan harus deskriptif.
- Contoh valid: `'Tanggap Darurat Tahap I'`, `'Pemulihan Fase Pertama'`.
- String kosong atau terlalu pendek (kurang dari 5 karakter) ditolak di validasi.

**BR-PERIODE-003: Enum Status Periode**
- `berjalan`: periode sedang aktif berlangsung.
- `selesai`: periode telah berakhir secara normal.
- `diperpanjang`: periode diperpanjang dari yang semula direncanakan.

**BR-PERIODE-004: Perpanjangan Memerlukan Pleno Baru**
- Jika periode akan diperpanjang (`status_periode` diubah ke `'diperpanjang'`), harus ada keputusan pleno baru.
- Perpanjangan TIDAK dapat dilakukan dengan hanya mengupdate `tanggal_selesai` tanpa keputusan pleno baru.
- Implementasi: saat perpanjangan, `id_pleno_keputusan` diupdate ke keputusan pleno yang baru.

**BR-PERIODE-005: Validasi Temporal**
- `tanggal_selesai` tidak boleh sebelum `tanggal_mulai`.
- Validasi dilakukan di `PeriodeRequest` dan dapat diperkuat dengan trigger database.

---

## J. Domain: LOGISTIK

### Tabel Utama

| Tabel | Peran |
|---|---|
| `logistik_stok` | Catatan stok saat ini per item per gudang/pos |
| `logistik_mutasi` | Log setiap transaksi perubahan stok |
| `logistik_gudang` | Gudang penyimpanan logistik |
| `logistik_barang_katalog` | Katalog barang yang dikelola sistem |
| `logistik_kategori` | Kategori barang logistik |
| `logistik_permintaan` | Permintaan logistik dari lapangan |
| `logistik_perencanaan` | Perencanaan kebutuhan logistik |
| `master_satuan` | Satuan ukur (kg, liter, pcs, dll) |

### Kolom Kunci `logistik_stok`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_barang` | `INT` | FK → `logistik_barang_katalog.id` |
| `id_gudang` | `INT` | FK → `logistik_gudang.id`, nullable (bisa pos aju) |
| `id_posaju` | `INT` | FK → `operasi_posaju.id`, nullable |
| `jumlah_tersedia` | `DECIMAL` | Stok saat ini — tidak boleh negatif |

### Kolom Kunci `logistik_mutasi`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `uuid_mutasi` | `CHAR(36)` | UUID unik per transaksi, UNIQUE |
| `id_stok` | `INT` | FK → `logistik_stok.id` |
| `tipe_mutasi` | `ENUM` | `masuk`, `keluar`, `penyesuaian` |
| `jumlah` | `DECIMAL` | Jumlah perubahan stok |
| `id_insiden` | `INT` | FK → `operasi_insiden.id`, konteks operasi |

### Kolom Kunci `logistik_gudang`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_pcnu` | `INT` | FK ke PCNU pemilik gudang; `NULL` = gudang PWNU |
| `nama_gudang` | `VARCHAR` | Nama gudang |
| `is_aktif` | `TINYINT(1)` | Status aktif gudang |

### Aturan Bisnis

**BR-LOGISTIK-001: Stok Tidak Boleh Negatif**
- Kolom `logistik_stok.jumlah_tersedia` tidak pernah boleh bernilai di bawah nol.
- Implementasi: trigger `tr_logistik_mutasi_integrity_guard` pada `BEFORE INSERT` di `logistik_mutasi` — jika `tipe_mutasi = 'keluar'` dan `jumlah` melebihi `jumlah_tersedia` saat ini, trigger melempar error dan transaksi dibatalkan.

**BR-LOGISTIK-002: Semua Perubahan Stok Melalui `logistik_mutasi`**
- Perubahan stok (penambahan, pengurangan, penyesuaian) WAJIB dilakukan dengan INSERT ke tabel `logistik_mutasi`.
- Tidak ada mekanisme yang boleh melakukan `UPDATE langsung` ke `logistik_stok.jumlah_tersedia` dari layer aplikasi.
- Implementasi penegakan: tidak ada method `updateStock()` langsung di model atau controller.

**BR-LOGISTIK-003: Larangan Update Langsung ke `logistik_stok.jumlah_tersedia`**
- Ini adalah penegasan dari BR-LOGISTIK-002.
- `logistik_stok.jumlah_tersedia` adalah read-only dari perspektif aplikasi — hanya dapat diubah oleh trigger database.
- Jika ditemukan kode yang melakukan `DB::table('logistik_stok')->update(['jumlah_tersedia' => ...])` di luar context trigger, kode tersebut harus dihapus.

**BR-LOGISTIK-004: Trigger Auto-Update Stok**
- Trigger `tr_execute_logistik_stok_update` pada `AFTER INSERT` di `logistik_mutasi` otomatis mengupdate `logistik_stok.jumlah_tersedia` berdasarkan `tipe_mutasi`:
  - `masuk`: `jumlah_tersedia = jumlah_tersedia + NEW.jumlah`
  - `keluar`: `jumlah_tersedia = jumlah_tersedia - NEW.jumlah`
  - `penyesuaian`: `jumlah_tersedia = NEW.jumlah` (set langsung)

**BR-LOGISTIK-005: Scope Gudang**
- Gudang dengan `id_pcnu IS NOT NULL` adalah milik PCNU tersebut dan hanya dapat digunakan untuk insiden dalam scope PCNU yang sama.
- Gudang dengan `id_pcnu IS NULL` adalah gudang PWNU dan dapat digunakan untuk semua insiden dalam wilayah PWNU.

**BR-LOGISTIK-006: Trigger Validasi Kepemilikan Stok**
- Trigger `tr_validate_stock_ownership` pada `BEFORE INSERT` di `logistik_mutasi` (untuk `tipe_mutasi = 'keluar'`) memeriksa apakah gudang asal memiliki scope yang sesuai dengan scope insiden tujuan.
- Suplai dari gudang PCNU-A ke insiden PCNU-B ditolak kecuali ada mekanisme transfer resmi antar gudang.
- Gudang PWNU (`id_pcnu IS NULL`) bebas mensuplai ke insiden PCNU manapun.

**BR-LOGISTIK-007: Validasi Scope Permintaan**
- Permintaan logistik di `logistik_permintaan` harus sesuai scope insiden.
- Trigger `tr_validate_logistik_request_scope` memeriksa apakah `id_insiden` di permintaan sesuai dengan scope gudang yang dituju.

**BR-LOGISTIK-008: Satuan Barang dari `master_satuan`**
- Setiap item di `logistik_barang_katalog` harus memiliki FK ke `master_satuan.id` untuk mendefinisikan satuan ukurnya (kg, liter, pcs, kardus, dll).
- Tidak boleh ada barang katalog tanpa satuan terdefinisi.

**BR-LOGISTIK-009: Perencanaan Sebelum Permintaan**
- Idealnya, `logistik_perencanaan` dibuat terlebih dahulu sebelum `logistik_permintaan` dibuat.
- Permintaan yang melebihi perencanaan yang telah disetujui harus melalui proses eskalasi atau persetujuan tambahan.
- Implementasi: warning (bukan hard block) di UI jika permintaan melebihi perencanaan.

**BR-LOGISTIK-010: UUID Mutasi Wajib Unik**
- Kolom `uuid_mutasi` di `logistik_mutasi` bertipe `CHAR(36)` dan memiliki UNIQUE constraint.
- UUID di-generate oleh aplikasi (bukan database) menggunakan `Str::uuid()` atau `Ramsey\Uuid\Uuid::uuid4()`.
- Setiap transaksi mutasi memiliki UUID yang unik untuk keperluan idempotency dan audit.

---

## K. Domain: POS AJU

### Tabel Utama

| Tabel | Peran |
|---|---|
| `operasi_posaju` | Record pos aju (pos maju lapangan) |
| `operasi_posaju_komandan` | Pencatatan komandan yang ditunjuk untuk pos aju |
| `logistik_stok` | Stok logistik yang berada di pos aju (via `id_posaju`) |

### Kolom Kunci `operasi_posaju`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_insiden` | `INT` | FK → `operasi_insiden.id` |
| `id_pleno_keputusan` | `INT` | FK → `operasi_pleno_keputusan.id` (wajib) |
| `nama_posaju` | `VARCHAR` | Nama identifikasi pos aju |
| `koordinat_lat` | `DECIMAL` | Latitude GPS pos aju (wajib) |
| `koordinat_lng` | `DECIMAL` | Longitude GPS pos aju (wajib) |
| `status_posaju` | `ENUM` | Status operasional pos aju |

### Kolom Kunci `operasi_posaju_komandan`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_posaju` | `INT` | FK → `operasi_posaju.id` |
| `id_pengguna` | `INT` | FK → `auth_users.id` (komandan yang ditunjuk) |
| `id_pleno_penunjukan` | `INT` | FK → `operasi_pleno_keputusan.id` (wajib) |
| `waktu_mulai_tugas` | `DATETIME` | Mulai bertugas sebagai komandan |
| `waktu_selesai_tugas` | `DATETIME` | Akhir masa tugas komandan |

### Aturan Bisnis

**BR-POSAJU-001: Pembukaan Berdasarkan Keputusan Pleno**
- Pos aju hanya dapat dibuka (diaktifkan) jika memiliki `id_pleno_keputusan` yang valid.
- Pos aju dalam status `draft` yang tidak memiliki referensi keputusan pleno tidak dapat diaktifkan.
- Implementasi: validasi di `PosaJuPolicy` saat transisi status.

**BR-POSAJU-002: Penunjukan Komandan via Pleno**
- Setiap record di `operasi_posaju_komandan` HARUS memiliki `id_pleno_penunjukan` yang valid.
- Komandan tidak dapat ditunjuk secara ad-hoc tanpa keputusan pleno yang tercatat.

**BR-POSAJU-003: Tracking Logistik via `logistik_stok`**
- Stok logistik yang berlokasi di pos aju ditrack menggunakan record `logistik_stok` dengan kolom `id_posaju` diisi (bukan `id_gudang`).
- Mutasi stok di pos aju tetap melalui mekanisme `logistik_mutasi` standar (lihat BR-LOGISTIK-002).

**BR-POSAJU-004: Pos Aju Ditutup Tidak Dapat Diaktifkan Kembali**
- Pos aju yang `status_posaju = 'ditutup'` tidak dapat dipindahkan ke status aktif.
- Jika operasi di lokasi yang sama diperlukan kembali, harus membuat record pos aju baru.
- Implementasi: state machine di service layer dan validasi di `PosaJuPolicy`.

**BR-POSAJU-005: Koordinat GPS Wajib**
- `koordinat_lat` dan `koordinat_lng` di `operasi_posaju` adalah wajib (`NOT NULL`).
- Koordinat harus valid (dalam batas wilayah Indonesia — lihat BR-ASSESSMENT-007 untuk rentang nilai).
- Tanpa koordinat GPS yang valid, pos aju tidak dapat disimpan.

**BR-POSAJU-006: Scope Stok Sesuai Scope Insiden**
- Stok yang dipindahkan ke pos aju harus berasal dari gudang yang scope-nya sesuai dengan scope insiden pos aju tersebut.
- Stok dari gudang PCNU-A tidak dapat dipindahkan ke pos aju yang melayani insiden PCNU-B.
- Gudang PWNU dapat mensuplai semua pos aju.

---

## L. Domain: PENGUNGSIAN

### Tabel Utama

| Tabel | Peran |
|---|---|
| `master_penerima_manfaat` | Master data penerima manfaat (pengungsi, posko, dll) |

### Kolom Kunci `master_penerima_manfaat`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `tipe_penerima` | `ENUM` | `individu`, `kk`, `kelompok`, `posko`, `desa`, `lembaga` |
| `nik` | `VARCHAR(16)` | Wajib jika `tipe_penerima = 'individu'` |
| `nama` | `VARCHAR` | Nama penerima manfaat |
| `id_insiden` | `INT` | FK → `operasi_insiden.id` (konteks insiden) |

### Aturan Bisnis

**BR-PENGUNGSIAN-001: Tipe Penerima Manfaat**
- Sistem mendukung enam tipe penerima manfaat: `individu`, `kk` (kepala keluarga), `kelompok`, `posko`, `desa`, dan `lembaga`.
- Setiap tipe dapat menerima bantuan logistik yang dicatat via `logistik_mutasi`.

**BR-PENGUNGSIAN-002: NIK Wajib untuk Individu**
- Jika `tipe_penerima = 'individu'`, kolom `nik` WAJIB diisi dengan NIK valid (16 digit numerik).
- Implementasi: validasi di `PenerimaManfaatRequest` dengan conditional required.

**BR-PENGUNGSIAN-003: Update Data Harian**
- Data pengungsi harus diperbarui setidaknya sekali per hari selama insiden berstatus `respon`.
- Implementasi: sistem memberikan peringatan (warning) di dashboard jika data pengungsi terakhir diupdate lebih dari 24 jam yang lalu.
- Ini adalah aturan operasional, bukan constraint database.

**BR-PENGUNGSIAN-004: Statistik di Dashboard**
- Statistik pengungsi (total pengungsi, total KK terdampak, kebutuhan mendesak) ditampilkan di halaman command center.
- Data dihitung dari agregasi `master_penerima_manfaat` dan `assessment_dampak_manusia`.
- Kalkulasi dilakukan di query level (bukan disimpan terpisah) untuk memastikan data selalu terkini.

---

## M. Domain: FEEDBACK KLASTER

### Tabel Utama

> **Catatan:** Tabel spesifik domain feedback klaster akan dikonfirmasi dari schema dump final.
> Domain ini diasumsikan memiliki tabel `operasi_klaster` dan tabel feedback terkait.

### Kolom Kunci `operasi_klaster`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_insiden` | `INT` | FK → `operasi_insiden.id` |
| `id_master_klaster` | `INT` | FK → `operasi_master_klaster.id` |
| `id_koordinator` | `INT` | FK via `operasi_klaster_koordinator` |

### Aturan Bisnis

**BR-FEEDBACK-001: Feedback adalah Evaluasi Pasca Respon**
- Feedback klaster berbeda dari `assessment_utama` — feedback adalah evaluasi retrospektif terhadap kinerja respon dan identifikasi gap.
- Feedback tidak boleh digunakan sebagai pengganti assessment.

**BR-FEEDBACK-002: Diisi oleh Koordinator Klaster**
- Hanya koordinator yang terdaftar di `operasi_klaster_koordinator` untuk klaster tersebut yang dapat mengisi feedback.
- Implementasi: `FeedbackPolicy` memeriksa apakah user adalah koordinator aktif klaster terkait.

**BR-FEEDBACK-003: Feedback sebagai Sumber Identifikasi Gap**
- Setiap feedback yang menunjukkan ketidaksesuaian antara kebutuhan dan suplai harus menghasilkan record gap kebutuhan.
- Satu feedback dapat menghasilkan banyak gap kebutuhan.

**BR-FEEDBACK-004: Feedback Dikunci**
- Setelah feedback dikunci (difinalisasi), tidak ada perubahan yang diizinkan.
- Mekanisme kunci menggunakan field `is_locked` atau perubahan status ke final — sesuai schema aktual.

---

## N. Domain: GAP KEBUTUHAN

### Aturan Bisnis

**BR-GAP-001: Sumber Gap**
- Gap kebutuhan dapat berasal dari dua sumber:
  1. Feedback klaster (pasca evaluasi periode operasi).
  2. `assessment_utama` dengan `jenis_laporan = 'pendataan_lanjutan'`.

**BR-GAP-002: Prioritas Gap**
- Gap memiliki tingkat prioritas: `darurat`, `mendesak`, `normal`.
- Nilai ini mengikuti enum yang juga digunakan di domain logistik untuk konsistensi.

**BR-GAP-003: Gap sebagai Generator Kebutuhan**
- Satu record gap dapat menghasilkan satu atau lebih dari hal berikut:
  - **Kebutuhan relawan baru** → menghasilkan record kebutuhan relawan yang dapat diisi via `relawan_pendaftaran`.
  - **Kebutuhan logistik baru** → menghasilkan record `logistik_perencanaan` baru.
  - **Kebutuhan assessment lanjutan** → memicu pembuatan `assessment_utama` baru dengan `jenis_laporan = 'pendataan_lanjutan'`.
- Relasi ini harus tercatat (via FK atau kolom referensi) agar dapat ditelusuri.

**BR-GAP-004: Status Gap TERPENUHI**
- Gap berubah status menjadi `TERPENUHI` ketika semua kebutuhan yang dihasilkan dari gap tersebut telah terpenuhi.
- Penentuan "terpenuhi" dilakukan secara manual oleh koordinator atau sistem berdasarkan closure kebutuhan turunan.

**BR-GAP-005: Penutupan Gap**
- Gap dapat ditutup oleh koordinator klaster atau pengguna dengan role `pwnu`.
- Gap yang ditutup tidak harus berstatus `TERPENUHI` — bisa ditutup dengan alasan tertentu (misal: situasi berubah, tidak relevan lagi).

---

## O. Domain: COMMAND CENTER

### Aturan Bisnis

**BR-CC-001: Read-Only Interface**
- Command center adalah tampilan agregasi data — tidak ada operasi write (INSERT/UPDATE/DELETE) yang dipicu dari halaman command center.
- Semua tombol/link di command center yang mengarah ke operasi write HARUS redirect ke modul yang sesuai.

**BR-CC-002: Tidak Ada Write dari Command Center**
- Controller untuk halaman command center hanya boleh memanggil query `SELECT`.
- Implementasi: review code untuk memastikan tidak ada `save()`, `create()`, `update()`, atau `delete()` di `CommandCenterController`.

**BR-CC-003: Data yang Ditampilkan**
- Insiden aktif (status `respon` atau `pemulihan`).
- Peta posisi insiden dan pos aju menggunakan Leaflet.js.
- Stok logistik kritis (stok di bawah threshold minimum).
- Jumlah personel aktif (`status_kehadiran = 'di_lokasi'` dari `operasi_mobilisasi_personil`).
- Ringkasan assessment terakhir (`is_latest = 1`) per insiden.

**BR-CC-004: Kontrol Akses**
- Command center hanya dapat diakses oleh role internal: `super_admin`, `pwnu`, `pcnu`, dan `relawan` yang memiliki assignment aktif.
- Role `publik` TIDAK dapat mengakses command center.
- Implementasi: `CommandCenterPolicy` atau middleware `role:super_admin|pwnu|pcnu|relawan`.

**BR-CC-005: Relawan Tertugas Khusus**
- Pengguna dengan role `relawan` hanya dapat mengakses command center jika memiliki record aktif di `operasi_penugasan` (assignment yang `waktu_selesai IS NULL`).
- Relawan tanpa assignment aktif tidak dapat melihat command center.

**BR-CC-006: Refresh Data Tanpa WebSocket Kompleks**
- Data di command center di-refresh menggunakan salah satu mekanisme:
  1. **HTML meta refresh**: `<meta http-equiv="refresh" content="30">` (setiap 30 detik).
  2. **AJAX ringan**: `fetch()` atau `$.ajax()` ke endpoint API ringan setiap N detik (misal: 30 detik).
- WebSocket (Laravel Echo, Pusher, Soketi) TIDAK digunakan untuk command center.
- Interval refresh default: 30 detik (dapat dikonfigurasi di `.env`).

---

## P. Domain: ESKALASI

### Tabel Utama

| Tabel | Peran |
|---|---|
| `operasi_eskalasi` | Log perubahan level eskalasi insiden |

### Kolom Kunci `operasi_eskalasi`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_insiden` | `INT` | FK → `operasi_insiden.id` |
| `id_pleno_keputusan` | `INT` | FK → `operasi_pleno_keputusan.id` (wajib) |
| `level_sebelumnya` | `ENUM` | Level eskalasi sebelum perubahan |
| `level_baru` | `ENUM` | Level eskalasi baru |
| `alasan_eskalasi` | `TEXT` | Alasan eskalasi, wajib diisi |
| `waktu_eskalasi` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` |

### Enum Level Eskalasi

| Nilai | Scope |
|---|---|
| `lokal` | Tingkat kelurahan/desa |
| `pcnu` | Tingkat PCNU |
| `pwnu` | Tingkat PWNU Jawa Tengah |
| `nasional` | Tingkat nasional/PBNU |

### Aturan Bisnis

**BR-ESKALASI-001: Eskalasi Wajib Melalui Pleno Resmi**
- Record `operasi_eskalasi` hanya dapat dibuat jika memiliki `id_pleno_keputusan` yang valid.
- Eskalasi informal (tanpa pleno) tidak dapat direkam di sistem.

**BR-ESKALASI-002: Level Baru Harus Lebih Tinggi**
- `level_baru` HARUS memiliki hierarki lebih tinggi dari `level_sebelumnya`.
- Urutan hierarki: `lokal` < `pcnu` < `pwnu` < `nasional`.
- Contoh valid: `lokal → pcnu`, `pcnu → pwnu`.
- Contoh TIDAK valid: `pwnu → pcnu` (eskalasi tidak dapat turun).
- Implementasi: validasi di `EskalasiRequest` dengan custom rule yang memeriksa hierarki.

**BR-ESKALASI-003: Eskalasi Tidak Dapat Diturunkan**
- Penurunan level eskalasi (`de-eskalasi`) hanya dapat dilakukan melalui **pleno khusus de-eskalasi**.
- Proses de-eskalasi adalah kasus khusus dan harus didokumentasikan secara terpisah jika diperlukan.
- Secara default, sistem hanya mengizinkan eskalasi naik.

**BR-ESKALASI-004: Akses PWNU Setelah Eskalasi ke PWNU**
- Setelah `level_baru = 'pwnu'` tercatat, pengguna dengan role `pwnu` mendapat akses penuh ke insiden tersebut.
- Implementasi: `InsidenPolicy` memeriksa tabel `operasi_eskalasi` untuk menentukan level akses.

**BR-ESKALASI-005: `alasan_eskalasi` Wajib Diisi**
- Kolom `alasan_eskalasi` tidak boleh NULL dan tidak boleh string kosong.
- Alasan harus minimal N karakter (minimal 20 karakter) untuk memastikan alasan substantif.
- Implementasi: validasi `required|string|min:20` di `EskalasiRequest`.

**BR-ESKALASI-006: Timestamp Otomatis**
- `waktu_eskalasi` menggunakan `DEFAULT CURRENT_TIMESTAMP` — diisi otomatis oleh database saat insert.
- Aplikasi tidak perlu (dan tidak boleh) mengisi kolom ini secara manual.

---

## Q. Domain: JURNAL OPERASI

### Tabel Utama

| Tabel | Peran |
|---|---|
| `operasi_jurnal` | Catatan naratif harian selama operasi berlangsung |

### Kolom Kunci `operasi_jurnal`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_insiden` | `INT` | FK → `operasi_insiden.id` |
| `id_pengguna` | `INT` | FK → `auth_users.id` (penulis jurnal) |
| `kategori_event` | `ENUM` | Kategori kejadian yang dicatat |
| `narasi` | `TEXT` | Isi catatan jurnal |
| `id_referensi` | `INT` | ID entitas yang direferensikan (polymorphic) |
| `tabel_referensi` | `VARCHAR` | Nama tabel entitas yang direferensikan |
| `waktu_event` | `DATETIME` | Waktu kejadian yang dicatat |

### Enum `kategori_event`

| Nilai | Keterangan |
|---|---|
| `sistem` | Event otomatis dari sistem |
| `laporan` | Penerimaan laporan baru |
| `aktivasi` | Aktivasi operasi atau sumber daya |
| `respon` | Tindakan respon di lapangan |
| `penugasan` | Penugasan personel |
| `logistik` | Pergerakan logistik |
| `aset` | Penggunaan atau perubahan status aset |
| `personil` | Mobilisasi atau perubahan status personil |
| `posko` | Aktivitas pos aju/posko |
| `selesai` | Penyelesaian tahap atau operasi |

### Aturan Bisnis

**BR-JURNAL-001: Catatan Naratif Harian**
- Jurnal operasi adalah rekaman naratif yang dibuat oleh personel lapangan untuk mendokumentasikan kejadian penting selama insiden.
- Jurnal dapat dibuat oleh siapapun yang memiliki assignment aktif di insiden tersebut.

**BR-JURNAL-002: Enum Kategori Event**
- Setiap jurnal harus menggunakan salah satu nilai `kategori_event` yang terdefinisi.
- Nilai di luar enum yang terdefinisi ditolak oleh database constraint dan validasi aplikasi.

**BR-JURNAL-003: Tidak Dapat Dihapus**
- Jurnal operasi **tidak memiliki soft delete** dan tidak dapat dihapus secara permanen dari interface aplikasi.
- Tidak ada kolom `dihapus_pada` di `operasi_jurnal`.
- Jurnal yang tercatat adalah rekaman permanen untuk keperluan audit dan pertanggungjawaban.

**BR-JURNAL-004: Referensi Polymorphic**
- Jurnal dapat mereferensikan entitas dari tabel manapun menggunakan kombinasi `id_referensi` dan `tabel_referensi`.
- Contoh: jurnal tentang mutasi logistik mengisi `id_referensi = id_mutasi` dan `tabel_referensi = 'logistik_mutasi'`.
- Tidak ada FK constraint pada `id_referensi` — integritas referensi dijaga secara aplikatif.

**BR-JURNAL-005: Wajib Catat Transisi Status Penting**
- Setiap transisi status insiden yang penting HARUS menghasilkan record baru di `operasi_jurnal`.
- Transisi yang wajib dicatat:
  - Perubahan `status_insiden` di `operasi_insiden`.
  - Pembukaan pos aju baru.
  - Penugasan komandan.
  - Keputusan pleno yang signifikan.
  - Eskalasi insiden.
- Implementasi: observer atau service layer yang otomatis membuat jurnal entry setelah event penting.

---

## R. Domain: ASET

### Tabel Utama

| Tabel | Peran |
|---|---|
| `aset_unit` | Unit aset individual yang dimiliki/dikelola |
| `aset_penggunaan` | Record peminjaman/penggunaan aset untuk insiden |
| `aset_master_jenis` | Jenis aset (kendaraan, peralatan, dll) |
| `aset_master_kategori` | Kategori aset |
| `aset_master_status` | Master status aset |

### Kolom Kunci `aset_unit`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_jenis` | `INT` | FK → `aset_master_jenis.id` |
| `id_kategori` | `INT` | FK → `aset_master_kategori.id` |
| `id_status` | `INT` | FK → `aset_master_status.id` |
| `id_pemilik_unit` | `INT` | FK ke entitas pemilik (scope wilayah) |
| `kondisi_fisik` | `ENUM` | `baik`, `rusak_ringan`, `rusak_berat` |
| `nama_unit` | `VARCHAR` | Nama/identifikasi unit aset |

### Enum `id_status` (mengacu `aset_master_status`)

| ID | Label | Keterangan |
|---|---|---|
| `1` | Tersedia | Aset siap dipinjam |
| `2` | Dalam Tugas | Aset sedang digunakan |
| `3` | Perbaikan/Maintenance | Aset sedang diperbaiki |
| `4` | Rusak | Aset rusak berat |
| `5` | Hilang | Aset tidak diketahui keberadaannya |

### Kolom Kunci `aset_penggunaan`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_aset_unit` | `INT` | FK → `aset_unit.id` |
| `id_insiden` | `INT` | FK → `operasi_insiden.id` (wajib) |
| `id_peminjam` | `INT` | FK → `auth_users.id` |
| `waktu_pinjam` | `DATETIME` | Waktu aset dipinjam |
| `waktu_kembali` | `DATETIME` | Nullable, waktu aset dikembalikan |
| `kondisi_kembali` | `ENUM` | `baik`, `rusak_ringan`, `rusak_berat` |

### Aturan Bisnis

**BR-ASET-001: Prasyarat Status Tersedia**
- Aset hanya dapat dipinjam (INSERT ke `aset_penggunaan`) jika `aset_unit.id_status = 1` (Tersedia).
- Aset dengan status lain (`2`, `3`, `4`, `5`) tidak dapat dipinjam.
- Implementasi: validasi di `AsetPenggunaanPolicy` dan `BEFORE INSERT` trigger.

**BR-ASET-002: Pencegahan Double Booking**
- Trigger `tr_prevent_double_booking_aset` pada `BEFORE INSERT` di `aset_penggunaan` memastikan tidak ada dua peminjaman aktif (dimana `waktu_kembali IS NULL`) untuk aset yang sama.
- Peminjaman kedua pada aset yang sedang aktif dipinjam akan ditolak oleh trigger.

**BR-ASET-003: Auto-Update Status Saat Dipinjam**
- Saat INSERT berhasil ke `aset_penggunaan` (aset dipinjam), trigger `AFTER INSERT` otomatis mengupdate `aset_unit.id_status` menjadi `2` (Dalam Tugas).

**BR-ASET-004: Auto-Restore Status Saat Dikembalikan**
- Saat `aset_penggunaan.waktu_kembali` diisi (tidak NULL lagi), trigger `AFTER UPDATE` otomatis mengupdate `aset_unit.id_status` kembali menjadi `1` (Tersedia).
- Catatan: jika `kondisi_kembali` = `rusak_berat`, status mungkin diubah ke `4` (Rusak) alih-alih `1` — sesuai logika bisnis yang dikonfirmasi dari schema trigger aktual.

**BR-ASET-005: Peminjaman Wajib Terkait Insiden**
- Setiap record `aset_penggunaan` HARUS memiliki `id_insiden` yang terisi dan valid.
- Peminjaman aset tanpa konteks insiden tidak diizinkan dalam sistem NURISK.

**BR-ASET-006: Scope Kepemilikan Aset**
- `id_pemilik_unit` di `aset_unit` menentukan siapa pemilik aset dan scope wilayahnya.
- Aset milik PCNU-A idealnya hanya digunakan untuk insiden dalam scope PCNU-A, kecuali ada mekanisme pinjam lintas wilayah yang disetujui.
- Implementasi: `AsetPolicy` memeriksa kesesuaian `id_pemilik_unit` dengan `id_pcnu` insiden.

---

## Lampiran: Ringkasan Trigger Database

| Nama Trigger | Tabel | Event | Fungsi |
|---|---|---|---|
| `tr_validate_temporal_incident` | `operasi_insiden` | `BEFORE INSERT, UPDATE` | Validasi `waktu_selesai >= waktu_mulai` |
| `tr_lock_incident_data` | `operasi_insiden` | `BEFORE UPDATE` | Tolak perubahan jika `is_locked = 1` |
| `tr_single_latest_assessment` | `assessment_utama` | `BEFORE INSERT, UPDATE` | Set `is_latest = 0` untuk assessment lama |
| `tr_auto_snapshot_sitrep` | `operasi_sitrep` | `BEFORE UPDATE` | Auto-populate snapshot saat finalisasi |
| `tr_logistik_mutasi_integrity_guard` | `logistik_mutasi` | `BEFORE INSERT` | Cegah stok negatif |
| `tr_execute_logistik_stok_update` | `logistik_mutasi` | `AFTER INSERT` | Update `jumlah_tersedia` di `logistik_stok` |
| `tr_validate_stock_ownership` | `logistik_mutasi` | `BEFORE INSERT` | Cegah suplai lintas scope PCNU |
| `tr_validate_logistik_request_scope` | `logistik_permintaan` | `BEFORE INSERT` | Validasi scope permintaan vs scope insiden |
| `tr_prevent_double_booking_aset` | `aset_penggunaan` | `BEFORE INSERT` | Cegah double booking aset |

---

## Lampiran: Ringkasan Enum Global

| Domain | Kolom | Nilai |
|---|---|---|
| Insiden | `status_insiden` | `draft`, `terverifikasi`, `respon`, `pemulihan`, `selesai`, `dibatalkan` |
| Sitrep | `status_sitrep` | `draft`, `ditinjau`, `final` |
| Assessment | `jenis_laporan` | `kaji_cepat`, `pendataan_lanjutan` |
| Aset | `kondisi_fisik` | `baik`, `rusak_ringan`, `rusak_berat` |
| Logistik | `tipe_mutasi` | `masuk`, `keluar`, `penyesuaian` |
| Assignment | `peran_otoritas` | `komandan_insiden`, `trc`, `relawan`, `medis`, `logistik`, `operator` |
| Jurnal | `kategori_event` | `sistem`, `laporan`, `aktivasi`, `respon`, `penugasan`, `logistik`, `aset`, `personil`, `posko`, `selesai` |
| Eskalasi | `level_baru` | `lokal`, `pcnu`, `pwnu`, `nasional` |
| Periode | `status_periode` | `berjalan`, `selesai`, `diperpanjang` |
| Mobilisasi | `status_kehadiran` | `menuju_lokasi`, `di_lokasi`, `kembali`, `izin` |
| Auth | `status_akun` | `menunggu`, `aktif`, `nonaktif`, `suspend` |
| Bencana | `kategori_bencana` | `alam`, `non_alam`, `sosial` |

---

*Dokumen ini dibuat berdasarkan SQL schema NURISK versi pre-production.*
*Setiap perubahan schema yang mempengaruhi aturan di atas harus disertai update pada dokumen ini.*
