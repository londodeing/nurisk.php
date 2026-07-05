# COMMAND CENTER DATA DICTIONARY

> Audit hasil pembacaan seluruh model, migrasi, dan service untuk Phase 15A.
> Tanggal: 20 Juni 2026

---

## 1. OperasiInsiden

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_insiden` |
| PK | `id_insiden` (BIGINT, AI) |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | `dibuat_pada`, `diperbarui_pada` |

**Foreign Keys:**
- `id_pcnu` → `organisasi_pcnu.id_pcnu`
- `id_jenis_bencana` → `bencana_master_jenis.id_jenis`
- `id_laporan_asal` → referensi laporan eksternal

**Status Fields:**
- `status_insiden`: `draft`, `terverifikasi`, `respon`, `pemulihan`, `selesai`, `dibatalkan`
- `status_operasi`: status operasional tambahan
- `prioritas`: tingkat prioritas penanganan
- `is_locked`: boolean — mengunci data agar tidak bisa diedit

**Timestamp Fields (reporting-critical):**
- `waktu_mulai`, `waktu_selesai`
- `waktu_verifikasi`
- `waktu_respon_dimulai`
- `waktu_pemulihan_dimulai`
- `waktu_ditutup`

**Eloquent Scopes:**
- `scopeAktif()` → `whereNotIn('status_insiden', ['selesai', 'dibatalkan'])`
- `scopeByPcnu(int $idPcnu)` → filter wilayah
- `scopeTidakTerkunci()` → `where('is_locked', false)`

**Helpers:**
- `labelStatus()` → label Indonesia
- `warnaBadgeStatus()` → CSS class Tailwind
- `isTerkunci()`, `isSelesai()`

**Relasi:**
- `jenisBencana()` → BelongsTo BencanaMasterJenis
- `pcnu()` → BelongsTo OrganisasiPcnu
- `riwayatStatus()` → HasMany RiwayatStatusInsiden

**Model juga dimiliki oleh (FK masuk):**
- OperasiPenugasan, OperasiPosaju, OperasiSitrep, OperasiMobilisasi, OperasiJurnal, OperasiPleno, RelawanKebutuhan, OperasiSuratKeluar via `id_insiden`

---

## 2. OperasiPenugasan

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_penugasan` |
| PK | `id_penugasan` (BIGINT, AI) |
| UUID | `uuid_penugasan` (unique) |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | `dibuat_pada`, `diperbarui_pada` |
| Eager Load | `$with = ['insiden']` |

**Foreign Keys:**
- `id_insiden` → `operasi_insiden.id_insiden` (CASCADE)
- `id_pengguna` → `auth_users.id_pengguna` (CASCADE)
- `id_klaster_operasi` → `operasi_klaster.id_klaster_operasi` (NULL)
- `ditugaskan_oleh` → `auth_users.id_pengguna` (CASCADE)

**Status Field:**
- `status_penugasan`: `aktif`, `selesai`, `dibatalkan` (varchar 50, default 'aktif')
- `peran_otoritas`: string peran dalam operasi

**Timestamp Fields (reporting-critical):**
- `waktu_mulai`, `waktu_selesai`

**Relasi:**
- `insiden()` → BelongsTo OperasiInsiden
- `pengguna()` → BelongsTo AuthUser (relawan)
- `pemberiTugas()` → BelongsTo AuthUser (ditugaskan_oleh)
- `klasterOperasi()` → BelongsTo OperasiKlaster

---

## 3. OperasiPosaju (Posko)

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_posaju` |
| PK | `id_posaju` (BIGINT, AI) |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | `dibuat_pada` (`UPDATED_AT = null`) |

**Foreign Keys:**
- `id_insiden` → `operasi_insiden.id_insiden`
- `id_periode_operasi` → periode
- `id_pleno_pendirian` → `operasi_pleno.id_pleno`
- `id_surat_pendirian` → `operasi_surat_keluar.id_surat`
- `pj_posaju` → `auth_users.id_pengguna` (Penanggung Jawab)

**Status Field:**
- `status_alur`: status alur pendirian posko

**Timestamp Fields:**
- `waktu_diaktifkan`, `diperpanjang_hingga`, `waktu_ditutup`

**Geolocation:**
- `latitude`, `longitude` (float)

**Relasi:**
- `insiden()` → BelongsTo OperasiInsiden
- `pj()` → BelongsTo AuthUser
- `suratPendirian()` → BelongsTo OperasiSuratKeluar
- `kebutuhanRelawan()` → HasMany RelawanKebutuhan

**FK Masuk:**
- RelawanKebutuhan, OperasiTugas, RelawanPenugasan via `id_posaju`

---

## 4. OperasiJurnal (Activity Log)

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_jurnal` |
| PK | `id_jurnal` (BIGINT, AI) |
| Timestamps | Tidak ada (`$timestamps = false`) |
| Soft Delete | Tidak |

**Foreign Keys:**
- `id_insiden` → `operasi_insiden.id_insiden`
- `id_pengguna` → `auth_users.id_pengguna`

**Content Fields:**
- `kategori_event`: string — kategori aktivitas (contoh: 'insiden_dibuat', 'penugasan_dibuat', 'sitrep_dibuat')
- `judul_event`: string — judul pendek
- `deskripsi_event`: text — deskripsi lengkap
- `id_referensi`: nullable — ID objek terkait
- `tabel_referensi`: nullable — nama tabel objek terkait

**Relasi:**
- `insiden()` → BelongsTo OperasiInsiden
- `pengguna()` → BelongsTo AuthUser

---

## 5. OperasiSitrep (Situation Report)

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_sitrep` |
| PK | `id_sitrep` (BIGINT, AI) |
| UUID | `uuid_sitrep` (tidak di fillable, boot) |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | `dibuat_pada`, `diperbarui_pada` |
| Eager Load | `$with = ['insiden']` |

**Foreign Keys:**
- `id_insiden` → `operasi_insiden.id_insiden`
- `id_assessment_basis` → `assessment_utama.id_assessment_utama`
- `id_pembuat` → `auth_users.id_pengguna`

**Content Fields:**
- `nomor_sitrep`: string — format SITREP-{kode_kejadian}-{nomor_urut}
- `periode_sitrep`: string — periode pelaporan
- `waktu_sitrep`: datetime — waktu pembuatan
- `catatan`: text
- `jumlah_personel`: integer — agregat (dari migration add_aggregates)
- `jumlah_klaster_aktif`: integer — agregat

**Relasi:**
- `insiden()` → BelongsTo OperasiInsiden
- `assessmentBasis()` → BelongsTo AssessmentUtama
- `pembuat()` → BelongsTo AuthUser
- `dampak()` → HasOne OperasiSitrepDampak (snapshot korban)
- `kebutuhan()` → HasMany OperasiSitrepKebutuhan (snapshot kebutuhan)

---

## 6. OperasiSitrepDampak (Impact Snapshot)

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_sitrep_dampak` |
| PK | `id_sitrep_dampak` (BIGINT, AI) |
| Soft Delete | Tidak |
| Timestamps | Tidak |

**Foreign Keys:**
- `id_sitrep` → `operasi_sitrep.id_sitrep` (CASCADE, UNIQUE = 1:1)

**Fields (semua integer, default 0):**
- `meninggal`, `hilang`, `luka_berat`, `luka_ringan`, `mengungsi`

---

## 7. OperasiSitrepKebutuhan (Supply Needs Snapshot)

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_sitrep_kebutuhan` |
| PK | `id_sitrep_kebutuhan` (BIGINT, AI) |
| Soft Delete | Tidak |
| Timestamps | Tidak |

**Foreign Keys:**
- `id_sitrep` → `operasi_sitrep.id_sitrep` (CASCADE)

**Fields:**
- `nama_kebutuhan`: string — nama barang/logistik
- `jumlah`: integer — jumlah dibutuhkan
- `satuan`: string — satuan (kg, liter, unit, dll)

---

## 8. OperasiMobilisasi (Resource Movement)

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_mobilisasi` |
| PK | `id_mobilisasi` (BIGINT, AI) |
| UUID | `uuid_mobilisasi` (unique) |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | `dibuat_pada`, `diperbarui_pada` |

**Foreign Keys:**
- `id_insiden` → `operasi_insiden.id_insiden` (CASCADE)
- `id_pengguna` → `auth_users.id_pengguna` (CASCADE)
- `created_by`, `updated_by`, `deleted_by` → `auth_users.id_pengguna` (NULL)

**Status Field:**
- `status_mobilisasi`: varchar(50) default 'draft'
- `jenis_mobilisasi`: varchar(100) — jenis pergerakan

**Timestamp Fields:**
- `waktu_berangkat`, `waktu_tiba`

**Location Fields:**
- `lokasi_asal`, `lokasi_tujuan`: text — lokasi geografis

**Relasi:**
- `insiden()` → BelongsTo OperasiInsiden
- `pengguna()` → BelongsTo AuthUser
- `pembuat()` → BelongsTo AuthUser (created_by)

---

## 9. OperasiTugas (Task)

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_tugas` |
| PK | `id_tugas` (BIGINT, AI) |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | `dibuat_pada` saja (`UPDATED_AT = null`) |

**Foreign Keys:**
- `id_operasi_klaster` → `operasi_klaster.id_klaster_operasi` (CASCADE)
- `id_posaju` → `operasi_posaju.id_posaju` (NULL)
- `ditugaskan_ke` → `auth_users.id_pengguna` (NULL)
- `id_surat_perintah` → `operasi_surat_keluar.id_surat` (NULL)

**Status Field:**
- `status_tugas`: enum `rencana`, `berjalan`, `tertunda`, `selesai` default 'rencana'

**Content Fields:**
- `judul_tugas`: string
- `target_indikator`: string
- `progres_persen`: decimal(5,2) default 0

**Relasi:**
- `klaster()` → BelongsTo OperasiKlaster
- `posaju()` → BelongsTo OperasiPosaju
- `pelaksana()` → BelongsTo AuthUser
- `suratPerintah()` → BelongsTo OperasiSuratKeluar

---

## 10. RelawanKebutuhan (Volunteer Need)

| Atribut | Nilai |
|---|---|
| Tabel | `relawan_kebutuhan` |
| PK | `id_relawan_kebutuhan` (BIGINT, AI) |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | `dibuat_pada` saja (`$timestamps = false`) |

**Foreign Keys:**
- `id_insiden` → `operasi_insiden.id_insiden` (CASCADE)
- `id_operasi_klaster` → `operasi_klaster.id_klaster_operasi` (NULL)
- `id_posaju` → `operasi_posaju.id_posaju` (NULL)
- `id_keahlian_utama` → `auth_keahlian_master.id_keahlian` (NULL)

**Status Field:**
- `status_rekrutmen`: enum `dibuka`, `terpenuhi`, `dibatalkan`, `ditutup` default 'dibuka'

**Content Fields:**
- `judul_posisi`: string — judul posisi relawan
- `deskripsi_tugas`: text
- `persyaratan`: text
- `jumlah_dibutuhkan`: integer default 1
- `tgl_mulai_tugas`, `tgl_selesai_tugas`: date

**Scopes:**
- `scopeDibuka()` → `where('status_rekrutmen', 'dibuka')`
- `scopeByInsiden(int $idInsiden)`

**Relasi:**
- `insiden()` → BelongsTo OperasiInsiden
- `keahlianUtama()` → BelongsTo AuthKeahlianMaster
- `pendaftaran()` → HasMany RelawanPendaftaran

---

## 11. RelawanPendaftaran (Volunteer Registration)

| Atribut | Nilai |
|---|---|
| Tabel | `relawan_pendaftaran` |
| PK | `id_pendaftaran` (BIGINT, AI) |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | Tidak standar (kolom `waktu_daftar` default current_timestamp) |

**Foreign Keys:**
- `id_relawan_kebutuhan` → `relawan_kebutuhan.id_relawan_kebutuhan` (CASCADE)
- `id_pengguna` → `auth_users.id_pengguna`
- `id_verifikator`, `id_penyaring` → `auth_users.id_pengguna` (NULL)

**Status Field:**
- `status_pendaftaran`: enum `dibuka`, `seleksi`, `diterima`, `ditugaskan`, `selesai`, `ditolak` default 'dibuka'

**Timestamp Fields:**
- `waktu_daftar`, `waktu_verifikasi`, `waktu_penyaringan`, `waktu_penugasan_dimulai`, `waktu_penugasan_selesai`

**Scopes:**
- `scopeDenganStatus(string $status)`
- `scopeByRelawan(int $idPengguna)`

**Relasi:**
- `kebutuhan()` → BelongsTo RelawanKebutuhan
- `relawan()` → BelongsTo AuthUser
- `verifikator()`, `penyaring()` → BelongsTo AuthUser
- `penugasan()` → HasOne RelawanPenugasan

---

## 12. RelawanPenugasan (Volunteer Assignment)

| Atribut | Nilai |
|---|---|
| Tabel | `relawan_penugasan` |
| PK | `id_penugasan_relawan` (BIGINT, AI) |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | Tidak ada |

**Foreign Keys:**
- `id_pendaftaran` → `relawan_pendaftaran.id_pendaftaran` (CASCADE)
- `id_penugasan_insiden` → `operasi_penugasan.id_penugasan` (SET NULL)
- `id_posaju` → `operasi_posaju.id_posaju` (NULL)
- `id_surat_tugas` → `operasi_surat_keluar.id_surat` (SET NULL)

**Status Field:**
- `status_aktif`: boolean default 1

**Fields:**
- `peran_lapangan`: string
- `tgl_mulai_aktif`, `tgl_selesai_aktif`: date

**Relasi:**
- `pendaftaran()` → BelongsTo RelawanPendaftaran
- `posaju()` → BelongsTo OperasiPosaju
- `shift()` → HasMany RelawanShift

---

## 13. AuthUser (User)

| Atribut | Nilai |
|---|---|
| Tabel | `auth_users` |
| PK | `id_pengguna` (BIGINT, AI) |
| Soft Delete | Tidak |
| Timestamps | `dibuat_pada`, `diperbarui_pada` |

**Foreign Keys:**
- `id_peran` → `auth_roles.id_peran` (CASCADE)
- `id_unit` → `organisasi_unit.id_unit` (NULL)

**Status Fields:**
- `status_akun`: enum `menunggu`, `aktif`, `nonaktif`, `suspend` default 'aktif'
- `is_tersedia`: boolean default true (kesediaan bertugas)
- `terakhir_masuk`: timestamp — last login

**Scope Fields (kritis untuk data isolation):**
- `default_scope_type`: enum `pwnu`, `pcnu`, `mwc`, `ranting`, `lembaga`, `banom`
- `default_scope_id`: unsigned BIGINT — ID dari tabel scope terkait

**Relasi:**
- `peran()` → BelongsTo AuthRole
- `profil()` → HasOne AuthPenggunaProfil
- `jabatanPosisi()` → HasMany PenggunaJabatan
- `jabatanAktif()` → HasMany (filtered)
- `keahlian()` → BelongsToMany AuthKeahlianMaster
- `pendaftaranRelawan()` → HasMany RelawanPendaftaran
- `mobileDevices()` → HasMany MobileDevice

---

## 14. AuthRole

| Atribut | Nilai |
|---|---|
| Tabel | `auth_roles` |
| PK | `id_peran` (INTEGER, AI) |
| Timestamps | Tidak (`$timestamps = false`) |

**Fields:**
- `nama_peran`: string — `super_admin`, `pwnu`, `pcnu`, `relawan`
- `deskripsi`: text
- `level_otoritas`: integer — hierarchy level

**Role Hierarchy (dari RoleMiddleware):**
| Role | Level |
|---|---|
| super_admin | 100 |
| pwnu | 80 |
| pcnu | 60 |
| relawan | 40 |

---

## 15. OrganisasiPcnu (PCNU Entity)

| Atribut | Nilai |
|---|---|
| Tabel | `organisasi_pcnu` |
| PK | `id_pcnu` (INTEGER, AI) |
| Timestamps | Tidak (`$timestamps = false`) |

**Foreign Keys:**
- `id_unit` → `organisasi_unit.id_unit` (CASCADE)

**Fields:**
- `nama_pcnu`: string — nama PCNU (kabupaten/kota)

**Relasi:**
- `unit()` → BelongsTo OrganisasiUnit
- `mwc()` → HasMany OrganisasiMwc (MWC di bawah PCNU)

---

## 16. OrganisasiUnit (Organizational Unit)

| Atribut | Nilai |
|---|---|
| Tabel | `organisasi_unit` |
| PK | `id_unit` (INTEGER, AI) |
| Timestamps | Tidak |

**Fields:**
- `parent_id`: integer (nullable) → parent unit
- `nama_unit`: string
- `tipe_unit`: enum `pwnu`, `pcnu`, `mwc`, `ranting`, `lembaga`, `banom`
- `id_wilayah`: char(10) nullable — kode wilayah

---

## 17. OperasiKlaster (Operational Cluster)

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_klaster` |
| PK | `id_klaster_operasi` (BIGINT, AI) |
| UUID | `uuid_klaster_operasi` |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | `dibuat_pada`, `diperbarui_pada` |

**Foreign Keys:**
- `id_insiden` → `operasi_insiden.id_insiden`
- `id_master_klaster` → `master_klaster.id_master_klaster`
- `id_pembuat` → `auth_users.id_pengguna`

**Status Field:**
- `status_klaster`: varchar
- `prioritas`: prioritas klaster

**Fields:**
- `target_cakupan`, `catatan`, `progres_persen`, `dibutuhkan` (boolean), `indikator_keberhasilan`
- `waktu_aktivasi`, `waktu_ditutup`

**Relasi:**
- `insiden()` → BelongsTo OperasiInsiden
- `masterKlaster()` → BelongsTo MasterKlaster
- `pembuat()` → BelongsTo AuthUser
- `penugasan()` → HasMany OperasiPenugasan
- `tugas()` → HasMany OperasiTugas

---

## 18. OperasiPleno (Pleno/Meeting)

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_pleno` |
| PK | `id_pleno` (BIGINT, AI) |
| Soft Delete | Ya (`dihapus_pada`) |
| Timestamps | `dibuat_pada` (`UPDATED_AT = null`) |

**Foreign Keys:**
- `id_insiden` → `operasi_insiden.id_insiden`
- `pimpinan_pleno`, `notulis_pleno` → `auth_users.id_pengguna`
- `disetujui_oleh` → `auth_users.id_pengguna`

**Status Field:**
- `status_pleno`: `draft`, `ditinjau`, `disetujui`, `ditandatangani`, `final`, `dibatalkan`

**Relasi:**
- `keputusan()` → HasMany OperasiPlenoKeputusan
- `peserta()` → HasMany OperasiPlenoPeserta
- `eskalasi()` → HasOne OperasiEskalasi

---

## 19. OperasiSuratKeluar (Outgoing Letter)

| Atribut | Nilai |
|---|---|
| Tabel | `operasi_surat_keluar` |
| PK | `id_surat` (BIGINT, AI) |
| Soft Delete | Ya (`dihapus_pada`) |

**Status Field:**
- `status_surat`: enum `draft`, `review_paraf`, `siap_tanda_tangan`, `ditandatangani`, `ditolak`, `arsip`

---

## DATABASE SUMMARY: TIDAK ADA LOGISTIK MODUL

**Tidak ada model `OperasiLogistik`.** Logistik/tidak ada tabel dedicated inventory.

Data logistik tersebar di:
| Data Logistik | Disimpan Di |
|---|---|
| Kebutuhan barang/supply | `operasi_sitrep_kebutuhan` (snapshot per sitrep) |
| Pergerakan sumber daya | `operasi_mobilisasi` |
| Kebutuhan relawan | `relawan_kebutuhan` |

**Tidak ada SQL View** di codebase saat ini.

---

## DATA ISOLATION MODEL

Isolasi data per role diimplementasikan via:
1. **ScopeMiddleware** — memeriksa `default_scope_type` user vs allowed scopes di route
2. **RoleMiddleware** — memeriksa `level_otoritas` user (100/80/60/40)
3. **AuthorizationContextService::getAccessiblePcnuIds()** — logika:
   - super_admin: null (semua)
   - pwnu: PCNU di bawah unit provinsi
   - pcnu: [id_pcnu milik sendiri]
   - relawan: PCNU dari insiden yang ditugaskan
