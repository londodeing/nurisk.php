# MODULE_DEPENDENCY_MAP.md — NURISK
# Peta Dependensi Modul Resmi — Software Architect

> Versi: 1.0 — Tanggal: 16 Juni 2026
> Sumber: SQL Dump v37 Frozen Final (100 tabel, FK diaudit langsung dari SQL)
>
> Dokumen ini adalah **peta dependensi resmi** seluruh proyek NURISK.
> AI Agent WAJIB membaca dokumen ini sebelum memulai implementasi modul apapun.
>
> **Sumber kebenaran foreign key:** SQL Dump v37 — semua relasi di bawah ini
> berasal dari perintah `ADD CONSTRAINT ... FOREIGN KEY ... REFERENCES` di SQL.

---

## DIAGRAM DEPENDENSI LINIER

```
[M00] INFRASTRUKTUR DATABASE (seed data, master data)
       ↓
[M01] AUTH & ROLES
       ↓
[M02] ORGANISASI & WILAYAH
       ↓
[M03] MASTER JABATAN & KEAHLIAN
       ↓
[M04] INSIDEN & LAPORAN KEJADIAN
       ├──→ [M05] ASSESSMENT
       │         ↓
       │    [M06] SITREP
       │
       ├──→ [M07] PENGUNGSIAN & POS PENGUNGSIAN
       │
       ├──→ [M08] PENUGASAN (ASSIGNMENT)
       │         ↓
       │    [M09] KLASTER & TUGAS OPERASI
       │         ↓
       │    [M10] MOBILISASI & SHIFT PERSONEL
       │         ↓
       │    [M11] RELAWAN & KEBUTUHAN RELAWAN
       │
       ├──→ [M12] POS AJU
       │         ↓
       │    [M13] LOGISTIK
       │
       ├──→ [M14] PLENO & ESKALASI
       │         ↓
       │    [M15] SURAT MENYURAT
       │         ↓
       │    [M16] ASET
       │
       └──→ [M17] FEEDBACK KLASTER
                 ↓
            [M18] GAP KEBUTUHAN

[M19] AUDIT & SISTEM LOG (berjalan paralel dengan M04+)
[M20] COMMAND CENTER & DASHBOARD (bergantung pada M04–M18)
```

---

## DIAGRAM DEPENDENSI PENUH (GRAPH)

```
                    ┌─────────────────────────────────────────┐
                    │         M00: INFRASTRUKTUR DB            │
                    │  (bencana_master_jenis, master_satuan,   │
                    │   logistik_kategori, aset_master_*,     │
                    │   auth_keahlian_master, operasi_master_  │
                    │   klaster, master_surat_jenis/template) │
                    └──────────────────┬──────────────────────┘
                                       │
                    ┌──────────────────▼──────────────────────┐
                    │              M01: AUTH                   │
                    │  auth_users, auth_roles,                │
                    │  auth_pengguna_profil,                  │
                    │  model_has_roles, roles, permissions    │
                    └──────────────────┬──────────────────────┘
                                       │
                    ┌──────────────────▼──────────────────────┐
                    │         M02: ORGANISASI & WILAYAH        │
                    │  organisasi_unit, organisasi_pcnu,      │
                    │  organisasi_mwc, organisasi_ranting,    │
                    │  wilayah_kabupaten, wilayah_kecamatan,  │
                    │  wilayah_desa                           │
                    └──────────────────┬──────────────────────┘
                                       │
                    ┌──────────────────▼──────────────────────┐
                    │       M03: JABATAN & KEAHLIAN           │
                    │  master_jabatan, pengguna_jabatan,      │
                    │  master_jabatan_penandatangan,          │
                    │  auth_pengguna_keahlian                 │
                    └──────────────────┬──────────────────────┘
                                       │
            ┌──────────────────────────▼────────────────────────────┐
            │                   M04: INSIDEN                        │
            │  laporan_kejadian, operasi_insiden,                   │
            │  riwayat_status_insiden, bencana_master_jenis         │
            └───┬───────┬──────────┬──────────┬──────────┬──────────┘
                │       │          │          │          │
                ▼       ▼          ▼          ▼          ▼
            [M05]   [M07]      [M08]      [M12]      [M14]
          Assessment Pengungsian Assignment Pos Aju   Pleno
                │                  │          │         │
                ▼                  │          ▼         ▼
            [M06]                  │       [M13]     [M15]
            Sitrep                 │      Logistik   Surat
                                   │                  │
                              ┌────▼────┐             ▼
                              │  M09   │           [M16]
                              │Klaster │            Aset
                              └────┬───┘
                                   │
                         ┌─────────┴──────────┐
                         │                    │
                         ▼                    ▼
                      [M10]               [M11]
                    Mobilisasi           Relawan
                    & Shift

[M17] Feedback ─── bergantung pada M08 (Assignment) + M09 (Klaster)
[M18] Gap Kebutuhan ─── bergantung pada M17 (Feedback)
[M19] Audit/Log ─── berjalan paralel, menulis dari semua modul
[M20] Dashboard ─── membaca dari semua modul
```

---

---

## DETAIL PER MODUL

---

### M00 — INFRASTRUKTUR DATABASE (Master Data & Seed)

#### 1. Modul Induk
Tidak ada — ini adalah titik awal mutlak.

#### 2. Modul yang Bergantung pada M00
**Semua modul** — semua tabel master adalah FK target dari tabel transaksional.

| Tabel Master | Digunakan oleh |
|---|---|
| `bencana_master_jenis` | `operasi_insiden`, `laporan_kejadian` |
| `master_satuan` | `logistik_barang_katalog`, `logistik_permintaan` |
| `logistik_kategori` | `logistik_barang_katalog` |
| `aset_master_jenis` | `aset_unit` |
| `aset_master_kategori` | `aset_master_jenis` |
| `aset_master_status` | `aset_unit`, `aset_penggunaan` |
| `auth_keahlian_master` | `auth_pengguna_keahlian`, `relawan_kebutuhan` |
| `operasi_master_klaster` | `operasi_klaster` |
| `operasi_master_indikator` | `operasi_klaster` |
| `master_surat_jenis` | `operasi_surat_keluar`, `master_surat_template` |
| `master_surat_template` | `operasi_surat_keluar` |
| `master_jabatan_penandatangan` | `operasi_surat_keluar` |

#### 3. Relasi Tabel
Semua tabel master bersifat **lookup** — tidak memiliki FK keluar, hanya menerima FK masuk.

#### 4. Workflow yang Terkait
- `DatabaseSeeder` harus menjalankan semua seeder master terlebih dahulu
- Data master dari SQL dump harus di-import sebelum sprint apapun dimulai

#### 5. Policy yang Terkait
- CRUD master data: hanya `super_admin` via middleware, tidak memerlukan Policy complex

#### 6. Dampak jika Belum Tersedia
- **FATAL**: Semua migration yang memiliki FK ke tabel master akan gagal
- Form dropdown insiden, logistik, aset tidak memiliki data
- Semua seeder domain akan error karena FK constraint

---

### M01 — AUTH & ROLES

#### 1. Modul Induk
- M00 (Infrastruktur DB) — `auth_roles` harus ada dan terisi (5 role PRD)

#### 2. Modul yang Bergantung pada M01
**Semua modul** — `auth_users.id_pengguna` adalah FK universal sistem.

FK keluar dari tabel auth ke modul lain:
- `auth_users.id_peran` → `auth_roles.id_peran`
- `auth_users.id_unit` → `organisasi_unit.id_unit` *(butuh M02)*
- `auth_pengguna_profil.id_desa_domisili` → `wilayah_desa.id_desa` *(butuh M02)*

FK masuk ke `auth_users.id_pengguna` dari modul lain:
```
operasi_insiden (dibuat_oleh)
laporan_kejadian (id_pelapor)
assessment_utama (id_petugas_assessment)
operasi_sitrep (id_petugas, id_penfinalisasi)
operasi_pleno (pimpinan_pleno, notulis_pleno, disetujui_oleh, id_penandatangan)
operasi_pleno_peserta (id_pengguna)
operasi_surat_keluar (id_pengguna_ttd)
dokumen_surat_paraf (id_pengguna)
operasi_penugasan (id_pengguna, ditugaskan_oleh)
operasi_mobilisasi_personil (id_pengguna)
logistik_mutasi (id_penginput)
logistik_gudang (pj_gudang)
operasi_posaju (pj_posaju)
operasi_posaju_komandan (id_pengguna)
riwayat_status_insiden (id_pengguna)
operasi_jurnal (id_pengguna)
sistem_log_aktivitas (id_pelaku)
sistem_audit_log (id_pengguna)
relawan_kebutuhan, relawan_pendaftaran, relawan_penugasan (id_pengguna)
operasi_klaster_feedback (dibuat_oleh)
operasi_gap_kebutuhan (direkomendasikan_oleh)
```

#### 3. Relasi Tabel
```
auth_roles ←──── auth_users.id_peran
auth_users ←──── auth_pengguna_profil.id_pengguna (1:1)
auth_users ←──── auth_pengguna_keahlian.id_pengguna (1:N)
auth_keahlian_master ←── auth_pengguna_keahlian.id_keahlian
auth_users ←──── model_has_roles.model_id (via Spatie + trigger)
```

#### 4. Workflow yang Terkait
- `tr_sync_user_role_insert`: INSERT ke `auth_users` → INSERT ke `model_has_roles`
- `tr_sync_user_role_update`: UPDATE `id_peran` → UPDATE `model_has_roles`
- Login flow: `no_hp` + `kata_sandi` → session → redirect per role
- Register publik → `status_akun = 'menunggu'` → aktivasi oleh admin

#### 5. Policy yang Terkait
- `AuthUserPolicy`: view, update, updateRole, activate
- `BaseNuriskPolicy::isSuperAdmin/isPwnu/isPcnu/isRelawan()` — semua helper bergantung pada `auth_users.id_peran`

#### 6. Dampak jika Belum Tersedia
- **FATAL**: Tidak ada user yang bisa login
- Semua middleware `auth` akan redirect ke halaman 404 atau error
- Tidak ada Policy yang bisa dijalankan
- Semua FK ke `auth_users.id_pengguna` di tabel transaksional akan gagal

---

### M02 — ORGANISASI & WILAYAH

#### 1. Modul Induk
- M01 (Auth) — `auth_users.id_unit` FK ke `organisasi_unit.id_unit`

#### 2. Modul yang Bergantung pada M02
| Modul Bergantung | Via FK |
|---|---|
| M01 (Auth — update) | `auth_users.id_unit → organisasi_unit.id_unit` |
| M04 (Insiden) | `operasi_insiden.id_pcnu → organisasi_pcnu.id_pcnu` |
| M13 (Logistik) | `logistik_gudang.id_pcnu → organisasi_pcnu.id_pcnu` |
| Semua scope query | `cekScopeWilayah()` join ke `organisasi_pcnu` |

#### 3. Relasi Tabel
```
organisasi_unit ──→ organisasi_unit.parent_id  (self-referential hierarki)
organisasi_pcnu.id_unit ──→ organisasi_unit.id_unit
organisasi_mwc.id_pcnu ──→ organisasi_pcnu.id_pcnu
organisasi_mwc.id_unit ──→ organisasi_unit.id_unit
organisasi_ranting.id_mwc ──→ organisasi_mwc.id_mwc
organisasi_ranting.id_unit ──→ organisasi_unit.id_unit

wilayah_kabupaten ←── wilayah_kecamatan.id_kab
wilayah_kecamatan ←── wilayah_desa.id_kec
wilayah_desa ←── auth_pengguna_profil.id_desa_domisili
wilayah_desa ←── operasi_insiden.id_wilayah (jika ada)
```

#### 4. Workflow yang Terkait
- Scope wilayah: PCNU hanya akses data dengan `id_pcnu = auth_users.default_scope_id`
- Dropdown bertingkat: kab → kec → desa (AJAX 3-level)
- Register pengguna: pilih domisili desa

#### 5. Policy yang Terkait
- `BaseNuriskPolicy::cekScopeWilayah()` — join ke `organisasi_pcnu` via `auth_users.id_unit`
- Semua scope query menggunakan `organisasi_pcnu.id_pcnu` sebagai filter

#### 6. Dampak jika Belum Tersedia
- **FATAL**: `operasi_insiden.id_pcnu` tidak bisa di-populate dengan benar
- Scope wilayah PCNU tidak berfungsi — data lintas PCNU bisa bocor
- `logistik_gudang` tidak bisa ditempatkan ke PCNU yang benar
- Dropdown wilayah di semua form tidak memiliki data

---

### M03 — JABATAN & KEAHLIAN

#### 1. Modul Induk
- M01 (Auth), M02 (Organisasi)

#### 2. Modul yang Bergantung pada M03
| Modul Bergantung | Via FK |
|---|---|
| M01 (Auth) | `pengguna_jabatan.id_pengguna → auth_users` |
| M15 (Surat) | `operasi_surat_keluar.id_jabatan_ttd → master_jabatan_penandatangan` |
| M11 (Relawan) | `relawan_kebutuhan.id_keahlian_utama → auth_keahlian_master` |
| Gate `sign-surat` | Cek jabatan penandatangan |

#### 3. Relasi Tabel
```
master_jabatan.id_master_jabatan ← pengguna_jabatan.id_master_jabatan
auth_users.id_pengguna ← pengguna_jabatan.id_pengguna
pengguna_jabatan.tipe_lingkup + id_lingkup → scope jabatan per wilayah

master_jabatan_penandatangan.id_jabatan ← operasi_surat_keluar.id_jabatan_ttd

auth_keahlian_master.id_keahlian ← auth_pengguna_keahlian.id_keahlian
auth_keahlian_master.id_keahlian ← relawan_kebutuhan.id_keahlian_utama
```

**Catatan kritis:** `master_jabatan` PK adalah `id_master_jabatan` (bukan `id_jabatan`).

#### 4. Workflow yang Terkait
- Penugasan jabatan kepada pengguna oleh super_admin/pwnu
- Gate `sign-surat`: cek apakah pengguna memiliki jabatan yang tertera di `master_jabatan_penandatangan`
- Kecocokan keahlian relawan dengan kebutuhan operasi

#### 5. Policy yang Terkait
- `JabatanPolicy`: CRUD hanya super_admin
- `SuratPolicy::sign`: cek `pengguna_jabatan` vs `master_jabatan_penandatangan`

#### 6. Dampak jika Belum Tersedia
- Surat tidak bisa ditandatangani (tidak ada jabatan penandatangan)
- Relawan tidak bisa dicocokan dengan kebutuhan berdasarkan keahlian
- Gate `sign-surat` tidak bisa dijalankan

---

### M04 — INSIDEN & LAPORAN KEJADIAN

#### 1. Modul Induk
- M01 (Auth), M02 (Organisasi), M03 (Jabatan), M00 (Master: `bencana_master_jenis`)

#### 2. Modul yang Bergantung pada M04
**Hampir semua modul operasional** — `operasi_insiden.id_insiden` adalah FK paling sering direferensikan.

```
operasi_insiden.id_insiden ←── assessment_utama.id_insiden
operasi_insiden.id_insiden ←── operasi_sitrep.id_insiden
operasi_insiden.id_insiden ←── operasi_pleno.id_insiden
operasi_insiden.id_insiden ←── operasi_posaju.id_insiden
operasi_insiden.id_insiden ←── operasi_klaster.id_insiden
operasi_insiden.id_insiden ←── operasi_penugasan.id_insiden
operasi_insiden.id_insiden ←── operasi_mobilisasi_personil.id_insiden
operasi_insiden.id_insiden ←── operasi_shift_personel.id_insiden
operasi_insiden.id_insiden ←── logistik_stok.id_insiden (via posaju)
operasi_insiden.id_insiden ←── logistik_permintaan.id_insiden
operasi_insiden.id_insiden ←── logistik_perencanaan.id_insiden
operasi_insiden.id_insiden ←── relawan_kebutuhan.id_insiden
operasi_insiden.id_insiden ←── operasi_eskalasi.id_insiden
operasi_insiden.id_insiden ←── operasi_aktivasi.id_insiden
operasi_insiden.id_insiden ←── operasi_jurnal.id_insiden
operasi_insiden.id_insiden ←── riwayat_status_insiden.id_insiden
operasi_insiden.id_insiden ←── operasi_surat_keluar.id_insiden
operasi_insiden.id_insiden ←── aset_penggunaan.id_insiden
operasi_insiden.id_insiden ←── operasi_pos_pengungsian.id_insiden
operasi_insiden.id_insiden ←── operasi_klaster_feedback.id_insiden
operasi_insiden.id_insiden ←── operasi_gap_kebutuhan.id_insiden
operasi_insiden.id_insiden ←── operasi_otoritas_kontekstual.id_insiden
operasi_insiden.id_insiden ←── operasi_periode.id_insiden
```

#### 3. Relasi Tabel
```
bencana_master_jenis.id_jenis ←── operasi_insiden.id_jenis_bencana
laporan_kejadian.id_laporan_kejadian ←── operasi_insiden.id_laporan_asal (nullable)
organisasi_pcnu.id_pcnu ←── operasi_insiden.id_pcnu
auth_users.id_pengguna ←── operasi_insiden.dibuat_oleh

operasi_insiden.id_insiden ←── riwayat_status_insiden.id_insiden
operasi_insiden.id_insiden ←── sistem_transisi_status.id_entitas (polymorphic)
```

#### 4. Workflow yang Terkait
- Publik submit `laporan_kejadian` → PCNU validasi → buat `operasi_insiden`
- State machine insiden: `draft → terverifikasi → respon → pemulihan → selesai / dibatalkan`
- Trigger `tr_lock_incident_data`: `is_locked=1` → semua UPDATE diblokir DB
- Trigger `tr_validate_temporal_incident`: `waktu_selesai < waktu_mulai` → SIGNAL error
- Trigger `tr_validate_coords_laporan`: koordinat di luar Indonesia → SIGNAL error
- Setiap transisi status: INSERT ke `riwayat_status_insiden` + `sistem_transisi_status` + `operasi_jurnal`

#### 5. Policy yang Terkait
- `InsidenPolicy`: viewAny (scope), view, create, update, delete, transisiStatus, validasiLaporan
- `LaporanKejadianPolicy`: store (publik, tanpa Policy), validasi (pcnu/pwnu)
- `BaseNuriskPolicy::cekScopeWilayah()` dipanggil di setiap aksi insiden

#### 6. Dampak jika Belum Tersedia
- **FATAL TOTAL**: Seluruh sistem operasional tidak dapat berjalan
- Assessment, Sitrep, Pleno, Pos Aju, Logistik, Relawan semua butuh `id_insiden`
- Dashboard command center tidak memiliki data apapun

---

### M05 — ASSESSMENT

#### 1. Modul Induk
- M04 (Insiden) — `assessment_utama.id_insiden → operasi_insiden.id_insiden`

#### 2. Modul yang Bergantung pada M05
| Modul Bergantung | Via FK |
|---|---|
| M06 (Sitrep) | `operasi_sitrep.id_assessment_basis → assessment_utama.id_assessment_utama` |
| M06 (Sitrep) | `operasi_sitrep_sumber.id_assessment → assessment_utama.id_assessment_utama` |
| Snapshot sitrep | Trigger `tr_auto_snapshot_sitrep` membaca `assessment_dampak_manusia` |

#### 3. Relasi Tabel
```
operasi_insiden.id_insiden ←── assessment_utama.id_insiden
assessment_utama.id_assessment_utama ←── assessment_dampak_manusia.id_assessment (CASCADE)
assessment_utama.id_assessment_utama ←── assessment_kebutuhan_mendesak.id_assessment (CASCADE)
assessment_utama.id_assessment_utama ←── operasi_sitrep.id_assessment_basis (SET NULL)
assessment_utama.id_assessment_utama ←── operasi_sitrep_sumber.id_assessment
```

#### 4. Workflow yang Terkait
- TRC/petugas membuat assessment → trigger `tr_single_latest_assessment` reset `is_latest`
- `is_latest=1` menandai assessment yang digunakan sebagai basis sitrep terbaru
- Trigger `tr_auto_snapshot_sitrep`: baca `assessment_dampak_manusia` saat sitrep di-INSERT
- Assessment tidak boleh dihapus jika menjadi `id_assessment_basis` di sitrep manapun

#### 5. Policy yang Terkait
- `AssessmentPolicy`: create (relawan TRC yang ditugaskan atau pcnu/pwnu)
- `AssessmentPolicy::delete`: ditolak jika assessment menjadi basis sitrep
- Validasi insiden tidak boleh `draft` atau `selesai/is_locked` saat create assessment

#### 6. Dampak jika Belum Tersedia
- Sitrep tidak dapat dibuat (tidak ada data dampak untuk snapshot)
- Trigger `tr_auto_snapshot_sitrep` akan menghasilkan snapshot kosong
- `id_assessment_basis` di sitrep tidak bisa di-populate

---

### M06 — SITREP

#### 1. Modul Induk
- M04 (Insiden), M05 (Assessment)

#### 2. Modul yang Bergantung pada M06
- M20 (Dashboard): statistik korban dari sitrep final
- Public map: aggregate dari `operasi_sitrep` yang `status_sitrep='final'`

#### 3. Relasi Tabel
```
operasi_insiden.id_insiden ←── operasi_sitrep.id_insiden
assessment_utama.id_assessment_utama ←── operasi_sitrep.id_assessment_basis (nullable)
auth_users.id_pengguna ←── operasi_sitrep.id_petugas
auth_users.id_pengguna ←── operasi_sitrep.id_penfinalisasi
operasi_sitrep.id_sitrep ←── operasi_sitrep_sumber.id_sitrep (CASCADE)
assessment_utama.id_assessment_utama ←── operasi_sitrep_sumber.id_assessment
```

#### 4. Workflow yang Terkait
- State machine: `draft → ditinjau → final`
- `draft → final` DILARANG (harus lewat `ditinjau`)
- Trigger `tr_auto_snapshot_sitrep`: INSERT sitrep → auto-populate `snapshot_dampak` JSON
- Trigger `tr_auto_snapshot_sitrep_update`: UPDATE non-final → update snapshot
- Finalisasi: isi `hash_snapshot` (SHA2), `waktu_difinalisasi`, generate PDF
- Sitrep FINAL: immutable — trigger melindungi dari perubahan snapshot

#### 5. Policy yang Terkait
- `SitrepPolicy`: create (pcnu/relawan TRC), finalisasi (Gate `finalize-sitrep`)
- `SitrepPolicy::update`: ditolak jika `status_sitrep='final'`

#### 6. Dampak jika Belum Tersedia
- Tidak ada laporan situasi resmi per insiden
- Dashboard tidak memiliki data korban yang terverifikasi
- Publik tidak dapat melihat perkembangan insiden

---

### M07 — PENGUNGSIAN & POS PENGUNGSIAN

#### 1. Modul Induk
- M04 (Insiden), M01 (Auth)

#### 2. Modul yang Bergantung pada M07
- M20 (Dashboard): statistik pengungsi dari `operasi_pos_pengungsian` + `pengungsian_sensus_harian`
- Public info: kapasitas pos pengungsian aktif

#### 3. Relasi Tabel
```
operasi_insiden.id_insiden ←── operasi_pos_pengungsian.id_insiden (RESTRICT)
operasi_pos_pengungsian.id_pos_pengungsian ←── pengungsian_sensus_harian.id_pos_pengungsian (RESTRICT)
auth_users.id_pengguna ←── pengungsian_sensus_harian.id_petugas

master_penerima_manfaat (standalone — tidak FK ke pos_pengungsian secara langsung)
```

#### 4. Workflow yang Terkait
- Buka pos pengungsian → mulai sensus harian
- Sensus harian: satu entri per `(id_pos_pengungsian, tanggal_sensus)` — UNIQUE
- Alert kapasitas: `jml_kk > kapasitas_maksimal_kk` atau `jml_jiwa > kapasitas_maksimal_jiwa`
- Status pos: `aktif → tutup` (tidak dapat dibuka kembali)

#### 5. Policy yang Terkait
- `PosPengungsiPolicy`: create (pcnu/pwnu dalam scope), tutup (pcnu/pwnu)
- `SensusPolicy`: create (petugas yang ditugaskan atau pcnu/pwnu)

#### 6. Dampak jika Belum Tersedia
- Data pengungsi tidak terlacak
- Dashboard tidak memiliki statistik kapasitas pengungsian
- Bantuan tidak dapat didistribusikan secara tepat sasaran

---

### M08 — PENUGASAN (ASSIGNMENT)

#### 1. Modul Induk
- M04 (Insiden), M01 (Auth)

#### 2. Modul yang Bergantung pada M08
| Modul Bergantung | Via FK |
|---|---|
| M09 (Klaster) | `operasi_klaster_koordinator.id_pengguna` |
| M10 (Mobilisasi) | `operasi_mobilisasi_personil.id_incident_assignment → operasi_tugas` |
| M11 (Relawan) | `operasi_klaster_feedback.id_incident_assignment → operasi_penugasan` |
| M17 (Feedback) | `operasi_klaster_feedback.id_incident_assignment → operasi_penugasan` |
| Policy seluruh sistem | `hasAssignment()` query ke tabel ini |

#### 3. Relasi Tabel
```
operasi_insiden.id_insiden ←── operasi_penugasan.id_insiden (RESTRICT)
auth_users.id_pengguna ←── operasi_penugasan.id_pengguna (CASCADE)
auth_users.id_pengguna ←── operasi_penugasan.ditugaskan_oleh
operasi_penugasan.id_incident_assignment ←── operasi_klaster_feedback.id_incident_assignment (SET NULL)
operasi_penugasan.id_incident_assignment ←── relawan_penugasan.? (lihat SQL)

operasi_insiden.id_insiden ←── operasi_otoritas_kontekstual.id_insiden
auth_users.id_pengguna ←── operasi_otoritas_kontekstual.id_pengguna
```

#### 4. Workflow yang Terkait
- PCNU/PWNU menugaskan personel ke insiden dengan `peran_otoritas`
- `peran_otoritas` ENUM: `komandan_insiden`, `trc`, `relawan`, `medis`, `logistik`, `operator`
- Penugasan aktif: `waktu_selesai IS NULL`
- Cross-region: `asal_lingkup` + `tujuan_lingkup` dicatat, `auth_users.id_unit` TIDAK berubah
- `hasAssignment()` digunakan di semua Policy untuk relawan

#### 5. Policy yang Terkait
- `PenugasanPolicy`: create, akhiri
- `BaseNuriskPolicy::hasAssignment()`: dicek di Assessment, Sitrep, Feedback Policy
- Relawan hanya bisa aksi operasional jika memiliki assignment aktif di insiden tersebut

#### 6. Dampak jika Belum Tersedia
- Relawan tidak dapat membuat assessment, sitrep, atau feedback
- `hasAssignment()` selalu return false → relawan tidak bisa aksi apapun
- Cross-region personel tidak terlacak

---

### M09 — KLASTER & TUGAS OPERASI

#### 1. Modul Induk
- M04 (Insiden), M08 (Assignment)

#### 2. Modul yang Bergantung pada M09
| Modul Bergantung | Via FK |
|---|---|
| M10 (Mobilisasi/Shift) | `operasi_shift_personel.id_operasi_klaster → operasi_klaster` |
| M11 (Relawan) | `relawan_kebutuhan.id_operasi_klaster → operasi_klaster` |
| M17 (Feedback) | `operasi_klaster_feedback.id_klaster → operasi_klaster` |
| M18 (Gap) | `operasi_gap_kebutuhan.id_klaster → operasi_klaster` |
| M13 (Logistik) | `logistik_perencanaan.id_operasi_klaster → operasi_klaster` |

#### 3. Relasi Tabel
```
operasi_insiden.id_insiden ←── operasi_klaster.id_insiden
operasi_master_klaster.id_klaster ←── operasi_klaster.id_klaster_master
operasi_klaster.id_operasi_klaster ←── operasi_klaster_koordinator.id_operasi_klaster
auth_users.id_pengguna ←── operasi_klaster_koordinator.id_pengguna
operasi_pleno.id_pleno ←── operasi_klaster_koordinator.id_pleno_penunjukan

operasi_klaster.id_operasi_klaster ←── operasi_tugas.id_operasi_klaster (CASCADE)
operasi_posaju.id_posaju ←── operasi_tugas.id_posaju (nullable)
operasi_surat_keluar.id_surat ←── operasi_tugas.id_surat_perintah (nullable)

operasi_klaster.id_operasi_klaster ←── operasi_master_indikator.id_klaster
```

#### 4. Workflow yang Terkait
- 6 klaster default dibuat saat insiden baru (dari `operasi_master_klaster` seed)
- Koordinator klaster WAJIB ditunjuk via pleno (`id_pleno_penunjukan`)
- Tugas per klaster: state machine `rencana → berjalan → tertunda → selesai`
- `progres_persen` di klaster diupdate oleh koordinator
- Klaster `selesai` tidak dapat diubah ke `aktif`

#### 5. Policy yang Terkait
- `KlasterPolicy`: update progres hanya koordinator klaster atau pcnu/pwnu
- `TugasPolicy`: create/update oleh koordinator klaster
- Penunjukan koordinator: WAJIB via pleno (divalidasi di Service)

#### 6. Dampak jika Belum Tersedia
- Relawan kebutuhan tidak bisa dikaitkan ke klaster
- Feedback klaster tidak bisa disubmit (FK ke `operasi_klaster`)
- Gap kebutuhan tidak bisa dikaitkan ke klaster
- Perencanaan logistik tidak bisa dikaitkan ke klaster

---

### M10 — MOBILISASI & SHIFT PERSONEL

#### 1. Modul Induk
- M08 (Assignment), M09 (Klaster)

#### 2. Modul yang Bergantung pada M10
- M20 (Dashboard): jumlah personel aktif (`status_kehadiran = 'di_lokasi'`)

#### 3. Relasi Tabel
```
auth_users.id_pengguna ←── operasi_mobilisasi_personil.id_pengguna (CASCADE)
operasi_tugas.id_tugas ←── operasi_mobilisasi_personil.id_incident_assignment (CASCADE)

operasi_insiden.id_insiden ←── operasi_shift_personel.id_insiden (RESTRICT)
auth_users.id_pengguna ←── operasi_shift_personel.id_pengguna
operasi_klaster.id_operasi_klaster ←── operasi_shift_personel.id_operasi_klaster

relawan_penugasan.id_penugasan_relawan ←── relawan_shift.id_penugasan_relawan (CASCADE)
```

#### 4. Workflow yang Terkait
- Status kehadiran mobilisasi: `menuju_lokasi → di_lokasi → kembali / izin`
- Shift personel: `rencana → aktif → selesai / izin`
- Relawan shift: pasangan `waktu_mulai` dan `waktu_selesai` per assignment
- Validasi shift: `mulai_shift < selesai_shift` dan tidak overlap per pengguna

#### 5. Policy yang Terkait
- `MobilisasiPolicy`: update status kehadiran oleh personel sendiri atau komandan
- `ShiftPolicy`: CRUD shift oleh koordinator klaster atau pcnu/pwnu

#### 6. Dampak jika Belum Tersedia
- Tidak ada tracking fisik personel di lapangan
- Dashboard tidak memiliki data "personel aktif di lokasi"

---

### M11 — RELAWAN & KEBUTUHAN RELAWAN

#### 1. Modul Induk
- M01 (Auth), M04 (Insiden), M08 (Assignment), M09 (Klaster)

#### 2. Modul yang Bergantung pada M11
- M20 (Dashboard): statistik relawan aktif, kecocokan keahlian

#### 3. Relasi Tabel
```
operasi_insiden.id_insiden ←── relawan_kebutuhan.id_insiden (RESTRICT)
operasi_klaster.id_operasi_klaster ←── relawan_kebutuhan.id_operasi_klaster (CASCADE)
operasi_posaju.id_posaju ←── relawan_kebutuhan.id_posaju (SET NULL)
auth_keahlian_master.id_keahlian ←── relawan_kebutuhan.id_keahlian_utama

relawan_kebutuhan.id_relawan_kebutuhan ←── relawan_pendaftaran.id_relawan_kebutuhan (CASCADE)
auth_users.id_pengguna ←── relawan_pendaftaran.id_pengguna
auth_users.id_pengguna ←── relawan_pendaftaran.id_penyaring (nullable)

relawan_penugasan.id_penugasan_relawan ←── relawan_shift.id_penugasan_relawan (CASCADE)
```

**UNIQUE CONSTRAINT di `relawan_pendaftaran`**: `(id_pengguna, id_relawan_kebutuhan)`

#### 4. Workflow yang Terkait
- PCNU buka `relawan_kebutuhan` per klaster/posaju
- Relawan mendaftar → status `menunggu` → PCNU approve → status `aktif`
- Relawan aktif dapat dijadwalkan shift via `relawan_shift`
- Kecocokan: `relawan_kebutuhan.id_keahlian_utama` vs `auth_pengguna_keahlian`
- Status rekrutmen: `dibuka → terpenuhi / dibatalkan / ditutup`

#### 5. Policy yang Terkait
- `RelawanKebutuhanPolicy`: create (pcnu/pwnu), view (semua login)
- `RelawanPendaftaranPolicy`: create (semua login), approve (pcnu/pwnu scope)
- Relawan belum `aktif` tidak bisa di-assign ke `operasi_penugasan`

#### 6. Dampak jika Belum Tersedia
- Relawan tidak bisa mendaftar ke kebutuhan spesifik
- `relawan_pendaftaran` tidak bisa dibuat (FK ke `relawan_kebutuhan`)

---

### M12 — POS AJU

#### 1. Modul Induk
- M04 (Insiden), M14 (Pleno — untuk `id_pleno_pendirian` dan `id_pleno_penunjukan`)

> ⚠️ **CATATAN SPRINT:** FK ke pleno bersifat nullable di SQL (`ON DELETE SET NULL`).
> Pos aju secara teknis BISA dibuat tanpa pleno (FK nullable).
> Namun secara DOMAIN RULE wajib ada pleno — validasi di Service/Policy level.

#### 2. Modul yang Bergantung pada M12
| Modul Bergantung | Via FK |
|---|---|
| M13 (Logistik) | `logistik_stok.id_posaju → operasi_posaju` |
| M11 (Relawan) | `relawan_kebutuhan.id_posaju → operasi_posaju` |
| M09 (Tugas) | `operasi_tugas.id_posaju → operasi_posaju` |
| M14 (Pleno) | Tidak ada FK balik |

#### 3. Relasi Tabel
```
operasi_insiden.id_insiden ←── operasi_posaju.id_insiden (RESTRICT)
operasi_pleno.id_pleno ←── operasi_posaju.id_pleno_pendirian (SET NULL)
operasi_surat_keluar.id_surat ←── operasi_posaju.id_surat_pendirian (nullable)
operasi_periode.id_periode_operasi ←── operasi_posaju.id_periode_operasi (SET NULL)
auth_users.id_pengguna ←── operasi_posaju.pj_posaju

operasi_posaju.id_posaju ←── operasi_posaju_komandan.id_posaju (CASCADE)
auth_users.id_pengguna ←── operasi_posaju_komandan.id_pengguna
operasi_pleno.id_pleno ←── operasi_posaju_komandan.id_pleno_penunjukan

operasi_posaju.id_posaju ←── logistik_stok.id_posaju
```

#### 4. Workflow yang Terkait
- Pos aju dibuka berdasarkan keputusan pleno (`id_pleno_pendirian`)
- Komandan ditunjuk via pleno (`id_pleno_penunjukan` di `operasi_posaju_komandan`)
- Status pos aju: `aktif → tutup` (tidak bisa diaktifkan kembali)
- Stok pos aju dikelola via `logistik_stok.id_posaju`
- Marker pos aju tampil di peta command center

#### 5. Policy yang Terkait
- `PosajuPolicy`: create (pcnu/pwnu dalam scope insiden), tutup (pcnu/pwnu atau komandan insiden)
- Pos aju `tutup`: semua aksi edit diblokir

#### 6. Dampak jika Belum Tersedia
- Stok logistik tidak bisa dikaitkan ke pos aju
- Relawan kebutuhan tidak bisa dikaitkan ke pos aju spesifik
- Tugas operasi tidak bisa ditempatkan di pos aju

---

### M13 — LOGISTIK

#### 1. Modul Induk
- M04 (Insiden), M12 (Pos Aju), M01 (Auth), M02 (Organisasi), M00 (Master satuan/kategori)

#### 2. Modul yang Bergantung pada M13
- M20 (Dashboard): stok kritis dari `logistik_stok`
- M16 (Aset): tidak langsung, tapi aset dan logistik sering bersamaan di pos aju

#### 3. Relasi Tabel
```
organisasi_pcnu.id_pcnu ←── logistik_gudang.id_pcnu
logistik_gudang.id_gudang ←── logistik_gudang.parent_id (self-referential sub-gudang)
auth_users.id_pengguna ←── logistik_gudang.pj_gudang

logistik_kategori.id_kategori ←── logistik_barang_katalog.id_kategori
master_satuan.id_satuan ←── logistik_barang_katalog.id_satuan

logistik_barang_katalog.id_katalog ←── logistik_stok.id_katalog (SET NULL)
operasi_posaju.id_posaju ←── logistik_stok.id_posaju
logistik_stok.id_stok ←── logistik_mutasi.id_stok (CASCADE)

operasi_insiden.id_insiden ←── logistik_permintaan.id_insiden (RESTRICT)
operasi_insiden.id_insiden ←── logistik_perencanaan.id_insiden (RESTRICT)
logistik_barang_katalog.id_katalog ←── logistik_perencanaan.id_katalog
operasi_klaster.id_operasi_klaster ←── logistik_perencanaan.id_operasi_klaster (SET NULL)
```

**Trigger kritis (JANGAN BYPASS):**
- `tr_execute_logistik_stok_update`: INSERT `logistik_mutasi` → UPDATE `logistik_stok.jumlah_tersedia`
- `tr_logistik_mutasi_integrity_guard`: keluar > stok → SIGNAL error
- `tr_validate_stock_ownership`: gudang PCNU-A tidak suplai insiden PCNU-B → SIGNAL error
- `tr_validate_logistik_request_scope`: posaju tujuan harus dalam insiden yang sama → SIGNAL error

#### 4. Workflow yang Terkait
- **ATURAN MUTLAK**: Semua perubahan stok WAJIB via INSERT ke `logistik_mutasi`
- `tipe_mutasi`: `masuk`, `keluar`, `penyesuaian`
- `uuid_mutasi`: auto-generate di Model boot, UNIQUE per transaksi
- Permintaan barang: `draft → diajukan → disetujui → dikirim → selesai / ditolak`
- Perencanaan: kebutuhan proyeksi per klaster per insiden

#### 5. Policy yang Terkait
- `LogistikGudangPolicy::view`: PCNU hanya akses gudang scope sendiri
- `LogistikMutasiPolicy::create`: pcnu/pwnu atau relawan `peran_otoritas = 'logistik'`
- `LogistikPermintaanPolicy::approve`: pcnu/pwnu dalam scope

#### 6. Dampak jika Belum Tersedia
- Distribusi bantuan tidak terlacak
- Stok pos aju tidak dapat dikelola
- Dashboard stok kritis tidak memiliki data

---

### M14 — PLENO & ESKALASI

#### 1. Modul Induk
- M04 (Insiden), M01 (Auth)

#### 2. Modul yang Bergantung pada M14
| Modul Bergantung | Via FK |
|---|---|
| M15 (Surat) | `operasi_surat_keluar.id_insiden` + `relasi_surat_pleno.id_pleno` |
| M12 (Pos Aju) | `operasi_posaju.id_pleno_pendirian` (nullable) |
| M09 (Klaster) | `operasi_klaster_koordinator.id_pleno_penunjukan` |
| M10 (Periode) | `operasi_periode.id_pleno_keputusan` |

#### 3. Relasi Tabel
```
operasi_insiden.id_insiden ←── operasi_pleno.id_insiden (RESTRICT)
auth_users.id_pengguna ←── operasi_pleno.pimpinan_pleno
auth_users.id_pengguna ←── operasi_pleno.notulis_pleno
auth_users.id_pengguna ←── operasi_pleno.disetujui_oleh
auth_users.id_pengguna ←── operasi_pleno.id_penandatangan

operasi_pleno.id_pleno ←── operasi_pleno_keputusan.id_pleno
operasi_pleno.id_pleno ←── operasi_pleno_peserta.id_pleno (CASCADE)
auth_users.id_pengguna ←── operasi_pleno_peserta.id_pengguna

operasi_insiden.id_insiden ←── operasi_eskalasi.id_insiden
operasi_pleno.id_pleno ←── operasi_eskalasi.id_pleno
operasi_insiden.id_insiden ←── operasi_aktivasi.id_insiden
operasi_pleno.id_pleno ←── operasi_aktivasi.id_pleno (?) [konfirmasi dari SQL]

operasi_pleno_keputusan.id_keputusan ←── operasi_periode.id_pleno_keputusan
```

#### 4. Workflow yang Terkait
- `jenis_pleno` ENUM: `aktivasi_operasi`, `evaluasi_rutin`, `perpanjangan_operasi`, `penutupan_operasi`, `eskalasi_wilayah`, `khusus`
- State machine pleno: `draft → ditinjau → disetujui → ditandatangani → final / dibatalkan`
- Eskalasi level: `lokal < pcnu < pwnu < nasional` — hanya naik, tidak bisa turun
- Eskalasi WAJIB ada pleno (FK ke `operasi_pleno`)
- Pleno FINAL: `hash_dokumen` terisi, immutable

#### 5. Policy yang Terkait
- `PlanoPolicy::finalisasi`: Gate `finalize-pleno` — hanya id_peran ∈ [1,2]
- `EskalasiPolicy::create`: Gate `escalate-insiden` — hanya id_peran ∈ [1,2]
- PCNU tidak dapat finalisasi pleno atau eskalasi mandiri

#### 6. Dampak jika Belum Tersedia
- Pos aju tidak dapat penunjukan komandan resmi
- Koordinator klaster tidak dapat ditunjuk secara formal
- Surat tidak dapat dikaitkan ke keputusan pleno
- Eskalasi insiden tidak dapat dilakukan

---

### M15 — SURAT MENYURAT

#### 1. Modul Induk
- M04 (Insiden), M14 (Pleno), M03 (Jabatan), M01 (Auth)

#### 2. Modul yang Bergantung pada M15
| Modul Bergantung | Via FK |
|---|---|
| M12 (Pos Aju) | `operasi_posaju.id_surat_pendirian → operasi_surat_keluar` |
| M09 (Tugas) | `operasi_tugas.id_surat_perintah → operasi_surat_keluar` |

#### 3. Relasi Tabel
```
master_surat_jenis.id_jenis_surat ←── operasi_surat_keluar.id_jenis_surat
auth_users.id_pengguna ←── operasi_surat_keluar.id_pengguna_ttd
master_jabatan_penandatangan.id_jabatan ←── operasi_surat_keluar.id_jabatan_ttd (SET NULL)
operasi_insiden.id_insiden ←── operasi_surat_keluar.id_insiden

operasi_surat_keluar.id_surat ←── dokumen_surat_paraf.id_surat (CASCADE)
auth_users.id_pengguna ←── dokumen_surat_paraf.id_pengguna (CASCADE)
operasi_surat_keluar.id_surat ←── dokumen_surat_tembusan.id_surat (CASCADE)

operasi_surat_keluar.id_surat ←── relasi_surat_pleno.id_surat (CASCADE)
operasi_pleno.id_pleno ←── relasi_surat_pleno.id_pleno

operasi_surat_keluar.id_surat ←── relasi_surat_aktivasi.id_surat (CASCADE)
operasi_aktivasi.id_aktivasi ←── relasi_surat_aktivasi.id_aktivasi

operasi_surat_keluar.id_surat ←── relasi_surat_tugas.id_surat (CASCADE)
operasi_tugas.id_tugas ←── relasi_surat_tugas.id_tugas
```

**Nama tabel utama: `operasi_surat_keluar` (BUKAN `operasi_surat_keluar`)**

#### 4. Workflow yang Terkait
- Status surat 6 nilai: `draft → review_paraf → siap_tanda_tangan → ditandatangani → ditolak → arsip`
- Paraf berurutan: `urutan` ASC — tidak bisa lompat
- Paraf ditolak → surat kembali ke `draft`, semua paraf setelahnya di-reset ke `menunggu`
- Paraf semua disetujui → status `siap_tanda_tangan`
- Penandatanganan: `id_pengguna_ttd` + `id_jabatan_ttd` wajib ada
- `isi_surat_snapshot` LONGTEXT — di-snapshot saat `ditandatangani` (immutable)
- `sistem_log_alur_kerja` menulis setiap aksi paraf

#### 5. Policy yang Terkait
- `SuratPolicy`: create, update (hanya draft), paraf (cek urutan aktif), finalisasi ([1,2])
- Gate `sign-surat`: cek `pengguna_jabatan` vs `master_jabatan_penandatangan`

#### 6. Dampak jika Belum Tersedia
- Pos aju tidak dapat dikaitkan ke surat pendirian resmi
- Tugas operasi tidak dapat memiliki surat perintah
- Surat menyurat tidak dapat diterbitkan

---

### M16 — ASET

#### 1. Modul Induk
- M04 (Insiden), M00 (Master: `aset_master_jenis`, `aset_master_kategori`, `aset_master_status`)

#### 2. Modul yang Bergantung pada M16
- M20 (Dashboard): aset tersedia dari view `v_aset_siap_pakai`, `v_aset_operasional_ready`

#### 3. Relasi Tabel
```
aset_master_jenis.id_jenis_aset ←── aset_unit.id_jenis_aset
aset_master_status.id_status ←── aset_unit.id_status
aset_master_jenis.id_kategori_aset ←── aset_master_jenis.id_kategori_aset (via join ke aset_master_kategori)

aset_unit.id_unit_aset ←── aset_penggunaan.id_unit_aset
operasi_insiden.id_insiden ←── aset_penggunaan.id_insiden
```

**Trigger kritis:**
- `tr_prevent_double_booking_aset`: INSERT `aset_penggunaan` saat `id_status != 1` → SIGNAL error; jika iya → set `id_status = 2`
- `tr_aset_return_to_available`: UPDATE `waktu_kembali` NULL→NOT NULL → set `id_status = 1`

#### 4. Workflow yang Terkait
- Status aset: 1=Tersedia, 2=Dalam Tugas, 3=Perbaikan, 4=Rusak, 5=Hilang
- Peminjaman: INSERT ke `aset_penggunaan` → trigger set status ke 2
- Pengembalian: UPDATE `waktu_kembali` → trigger reset status ke 1
- `kondisi_fisik`: `baik`, `rusak_ringan`, `rusak_berat`

#### 5. Policy yang Terkait
- `AsetPolicy`: view (scope `id_pemilik_unit`), pinjam (status = 1), kembalikan
- Double-booking dicegah oleh trigger DB — Policy sebagai lapisan pertama

#### 6. Dampak jika Belum Tersedia
- Kendaraan, peralatan, dan perlengkapan tidak terlacak
- Dashboard `v_aset_siap_pakai` tidak memiliki data

---

### M17 — FEEDBACK KLASTER

#### 1. Modul Induk
- M04 (Insiden), M08 (Assignment), M09 (Klaster)

#### 2. Modul yang Bergantung pada M17
- M18 (Gap Kebutuhan) — `operasi_gap_kebutuhan.id_feedback → operasi_klaster_feedback.id_feedback`

#### 3. Relasi Tabel
```
operasi_insiden.id_insiden ←── operasi_klaster_feedback.id_insiden (RESTRICT)
operasi_klaster.id_operasi_klaster ←── operasi_klaster_feedback.id_klaster (RESTRICT)
operasi_penugasan.id_incident_assignment ←── operasi_klaster_feedback.id_incident_assignment (SET NULL)
auth_users.id_pengguna ←── operasi_klaster_feedback.dibuat_oleh (RESTRICT)

operasi_klaster_feedback.id_feedback ←── operasi_gap_kebutuhan.id_feedback (RESTRICT)
```

#### 4. Workflow yang Terkait
- Relawan/TRC submit feedback kondisi lapangan per klaster
- `status_lokasi`: `membaik`, `stagnan`, `memburuk`
- `perlu_tindak_lanjut = 1` → otomatis buka form create gap kebutuhan
- Feedback dengan koordinat GPS → tampil di peta klaster

#### 5. Policy yang Terkait
- `FeedbackPolicy`: create (relawan dengan assignment aktif di insiden + klaster, atau pcnu/pwnu)
- Feedback tidak dapat diedit setelah 24 jam (business rule — implementasi di Service)

#### 6. Dampak jika Belum Tersedia
- Gap kebutuhan tidak dapat dibuat (FK ke feedback)
- Kondisi lapangan per klaster tidak terlacak
- Evaluasi pasca respon tidak tersedia

---

### M18 — GAP KEBUTUHAN

#### 1. Modul Induk
- M17 (Feedback), M04 (Insiden), M09 (Klaster)

#### 2. Modul yang Bergantung pada M18
- M20 (Dashboard): gap terbuka dari `v_command_center_summary`

#### 3. Relasi Tabel
```
operasi_insiden.id_insiden ←── operasi_gap_kebutuhan.id_insiden (RESTRICT)
operasi_klaster_feedback.id_feedback ←── operasi_gap_kebutuhan.id_feedback (RESTRICT)
operasi_klaster.id_operasi_klaster ←── operasi_gap_kebutuhan.id_klaster (RESTRICT)
auth_users.id_pengguna ←── operasi_gap_kebutuhan.direkomendasikan_oleh (RESTRICT)
```

**Gap selalu berasal dari feedback (`id_feedback` NOT NULL)** — tidak ada gap tanpa feedback.

#### 4. Workflow yang Terkait
- Gap dibuat dari feedback `perlu_tindak_lanjut = 1`
- `kategori_gap` ENUM: `relawan`, `logistik`, `medis`, `alat`, `shelter`, `assessment`, `keamanan`, `lainnya`
- Status gap: `terbuka → diproses → terpenuhi / ditutup`
- `dipenuhi_pada`: diisi saat status berubah ke `terpenuhi`
- Gap terpenuhi: `jumlah_dibutuhkan` dan `satuan` harus ada

#### 5. Policy yang Terkait
- `GapPolicy`: create (dari feedback, otomatis atau manual oleh pcnu/pwnu)
- `GapPolicy::penuhi`: pcnu/pwnu atau koordinator klaster terkait

#### 6. Dampak jika Belum Tersedia
- Kebutuhan aktual lapangan tidak terlacak secara sistematis
- Dashboard tidak dapat menampilkan gap terbuka per kategori

---

### M19 — AUDIT & SISTEM LOG

#### 1. Modul Induk
- M01 (Auth) — semua log mencatat `id_pengguna`

#### 2. Modul yang Bergantung pada M19
Tidak ada — M19 adalah konsumen dari semua modul, bukan produsen.

#### 3. Relasi Tabel
```
auth_users.id_pengguna ←── sistem_log_aktivitas.id_pelaku (SET NULL)
auth_users.id_pengguna ←── sistem_audit_log.id_pengguna (nullable)
auth_users.id_pengguna ←── sistem_log_alur_kerja.id_approver
auth_users.id_pengguna ←── sistem_transisi_status.diubah_oleh (SET NULL)
```

Semua tabel log bersifat **append-only** — tidak ada UPDATE, tidak ada DELETE.

**Struktur log:**
- `sistem_log_aktivitas`: audit INSERT/UPDATE/DELETE per record (nilai_lama + nilai_baru)
- `sistem_audit_log`: audit trail login, aksi HTTP (ip_address, endpoint, method)
- `sistem_log_alur_kerja`: approval workflow log (paraf surat, persetujuan pleno)
- `sistem_transisi_status`: transisi status semua entitas (polymorphic: `tipe_entitas + id_entitas`)
- `operasi_jurnal`: narasi event operasional per insiden (human-readable)

#### 4. Workflow yang Terkait
- `JurnalService::catat()`: dipanggil dari semua Service domain — best-effort (di luar transaction)
- `sistem_transisi_status`: dipanggil dari `InsidenService`, `SitrepService`, `PlanoService`, `SuratService`
- `sistem_log_alur_kerja`: dipanggil dari `SuratService::prosesParaf()`
- Tidak ada route DELETE untuk semua tabel log

#### 5. Policy yang Terkait
- `JurnalPolicy`: view (pcnu/pwnu scope), create (semua login), DELETE tidak ada
- Tabel sistem_* tidak memiliki Policy — hanya dibaca via Controller khusus

#### 6. Dampak jika Belum Tersedia
- **Non-fatal untuk operasional** — sistem tetap berjalan
- Audit trail tidak tersedia → risiko compliance
- Timeline jurnal per insiden kosong

---

### M20 — COMMAND CENTER & DASHBOARD

#### 1. Modul Induk
Bergantung pada semua modul M01–M18 (minimal M01, M04, M06, M08, M13).

#### 2. Modul yang Bergantung pada M20
Tidak ada — M20 adalah endpoint akhir (read-only consumer).

#### 3. Relasi Tabel (READ ONLY)
Menggunakan 10 view SQL yang sudah tersedia:
```
v_command_center_summary          ← aggregasi dari semua domain
v_incident_timeline_comprehensive ← timeline per insiden
v_logistik_distribution_audit     ← audit distribusi logistik
v_aset_operasional_ready          ← aset siap operasional
v_aset_siap_pakai                 ← aset tersedia
v_relawan_domisili_check          ← cek domisili relawan
v_user_access_control             ← access control summary
v_alert_insiden_baru              ← alert insiden belum ditangani
v_wilayah_blank_spot              ← wilayah tanpa cobertura PCNU
v_audit_surat_orphans             ← surat tanpa relasi
```

**Gunakan view SQL via `DB::table('v_nama_view')->get()`** — JANGAN buat aggregasi manual.

#### 4. Workflow yang Terkait
- AJAX polling 30 detik (`setInterval`, bukan WebSocket)
- Peta Leaflet.js: marker insiden aktif (status `respon`/`pemulihan`)
- Marker clustering untuk area padat
- Cache 5 menit untuk data summary (bukan real-time)
- Data publik: insiden aktif tanpa data sensitif

#### 5. Policy yang Terkait
- `DashboardPolicy::viewCommandCenter`: id_peran ∈ [1,2,3] atau relawan dengan assignment aktif
- API command center: scope enforcement per role tetap berlaku
- Public API: tidak ada data personel, logistik, atau koordinat presisi

#### 6. Dampak jika Belum Tersedia
- Tidak ada tampilan terpadu — operator harus buka setiap halaman modul
- **Non-fatal**: semua modul tetap bisa diakses langsung

---

---

## TABEL REFERENSI CEPAT

### Semua FK Kritis per Domain (dari SQL)

```
DOMAIN AUTH
└── auth_users.id_peran → auth_roles.id_peran
└── auth_users.id_unit → organisasi_unit.id_unit
└── auth_pengguna_profil.id_pengguna → auth_users.id_pengguna
└── pengguna_jabatan.id_pengguna → auth_users.id_pengguna
└── pengguna_jabatan.id_master_jabatan → master_jabatan.id_master_jabatan

DOMAIN ORGANISASI & WILAYAH
└── organisasi_pcnu.id_unit → organisasi_unit.id_unit
└── organisasi_mwc.id_pcnu → organisasi_pcnu.id_pcnu
└── organisasi_ranting.id_mwc → organisasi_mwc.id_mwc
└── wilayah_kecamatan.id_kab → wilayah_kabupaten.id_kab
└── wilayah_desa.id_kec → wilayah_kecamatan.id_kec

DOMAIN INSIDEN
└── operasi_insiden.id_pcnu → organisasi_pcnu.id_pcnu
└── operasi_insiden.id_jenis_bencana → bencana_master_jenis.id_jenis
└── operasi_insiden.id_laporan_asal → laporan_kejadian.id_laporan_kejadian (SET NULL)

DOMAIN ASSESSMENT
└── assessment_utama.id_insiden → operasi_insiden.id_insiden (RESTRICT)
└── assessment_dampak_manusia.id_assessment → assessment_utama.id_assessment_utama (CASCADE)
└── assessment_kebutuhan_mendesak.id_assessment → assessment_utama.id_assessment_utama (CASCADE)

DOMAIN SITREP
└── operasi_sitrep.id_insiden → operasi_insiden.id_insiden
└── operasi_sitrep.id_assessment_basis → assessment_utama.id_assessment_utama (SET NULL)
└── operasi_sitrep_sumber.id_sitrep → operasi_sitrep.id_sitrep (CASCADE)

DOMAIN POS AJU
└── operasi_posaju.id_insiden → operasi_insiden.id_insiden (RESTRICT)
└── operasi_posaju.id_pleno_pendirian → operasi_pleno.id_pleno (SET NULL)
└── operasi_posaju.id_surat_pendirian → operasi_surat_keluar.id_surat
└── operasi_posaju_komandan.id_posaju → operasi_posaju.id_posaju (CASCADE)
└── operasi_posaju_komandan.id_pleno_penunjukan → operasi_pleno.id_pleno

DOMAIN LOGISTIK
└── logistik_gudang.id_pcnu → organisasi_pcnu.id_pcnu
└── logistik_stok.id_posaju → operasi_posaju.id_posaju
└── logistik_mutasi.id_stok → logistik_stok.id_stok (CASCADE)
└── logistik_permintaan.id_insiden → operasi_insiden.id_insiden (RESTRICT)

DOMAIN PLENO
└── operasi_pleno.id_insiden → operasi_insiden.id_insiden (RESTRICT)
└── operasi_klaster_koordinator.id_pleno_penunjukan → operasi_pleno.id_pleno
└── operasi_eskalasi.id_pleno → operasi_pleno.id_pleno
└── operasi_periode.id_pleno_keputusan → operasi_pleno_keputusan.id_keputusan

DOMAIN SURAT (TABEL: operasi_surat_keluar — BUKAN operasi_surat_keluar)
└── operasi_surat_keluar.id_jenis_surat → master_surat_jenis.id_jenis_surat
└── operasi_surat_keluar.id_jabatan_ttd → master_jabatan_penandatangan.id_jabatan (SET NULL)
└── operasi_surat_keluar.id_insiden → operasi_insiden.id_insiden
└── dokumen_surat_paraf.id_surat → operasi_surat_keluar.id_surat (CASCADE)
└── relasi_surat_pleno.id_pleno → operasi_pleno.id_pleno
└── relasi_surat_tugas.id_tugas → operasi_tugas.id_tugas

DOMAIN RELAWAN
└── relawan_kebutuhan.id_insiden → operasi_insiden.id_insiden (RESTRICT)
└── relawan_kebutuhan.id_operasi_klaster → operasi_klaster.id_operasi_klaster (CASCADE)
└── relawan_kebutuhan.id_posaju → operasi_posaju.id_posaju (SET NULL)
└── relawan_pendaftaran.id_relawan_kebutuhan → relawan_kebutuhan.id_relawan_kebutuhan (CASCADE)

DOMAIN FEEDBACK & GAP
└── operasi_klaster_feedback.id_insiden → operasi_insiden.id_insiden (RESTRICT)
└── operasi_klaster_feedback.id_klaster → operasi_klaster.id_operasi_klaster (RESTRICT)
└── operasi_klaster_feedback.id_incident_assignment → operasi_penugasan.id_incident_assignment (SET NULL)
└── operasi_gap_kebutuhan.id_feedback → operasi_klaster_feedback.id_feedback (RESTRICT)
└── operasi_gap_kebutuhan.id_klaster → operasi_klaster.id_operasi_klaster (RESTRICT)
```

---

## ATURAN DEPENDENCY UNTUK AI AGENT

Sebelum mengimplementasi modul apapun, AI Agent WAJIB:

1. **Verifikasi semua modul induk sudah selesai (Done)** sesuai Definition of Done
2. **Cek semua FK** via SQL dump — jangan percaya dokumentasi lama
3. **Jangan buat migration FK** sebelum tabel referensi ada
4. **Jangan mock tabel** yang belum ada — tunggu modul induk selesai

```bash
# Command untuk cek struktur tabel sebelum implementasi:
SQL_FILE="/home/londo/Downloads/nurisk_37_frozen_final(1).sql"
awk '/CREATE TABLE `nama_tabel`/,/^\) ENGINE/' "$SQL_FILE"

# Command untuk cek semua FK suatu tabel:
grep "REFERENCES \`nama_tabel\`" "$SQL_FILE"
```

---

## TABEL NULLABLE FK (Implementasi Bertahap Diizinkan)

Beberapa FK di SQL bersifat nullable (`ON DELETE SET NULL`) yang memungkinkan implementasi bertahap. Ini adalah "pintu masuk" yang aman untuk sprint paralel:

| Tabel | FK Nullable | Modul Induk | Kapan Diaktifkan |
|---|---|---|---|
| `operasi_posaju.id_pleno_pendirian` | SET NULL | M14 (Pleno) | Sprint 9 |
| `operasi_posaju.id_surat_pendirian` | nullable | M15 (Surat) | Sprint 10 |
| `operasi_posaju_komandan.id_pleno_penunjukan` | nullable | M14 (Pleno) | Sprint 9 |
| `operasi_klaster_feedback.id_incident_assignment` | SET NULL | M08 (Assignment) | Sprint 8 |
| `operasi_tugas.id_surat_perintah` | nullable | M15 (Surat) | Sprint 10 |
| `operasi_tugas.id_posaju` | nullable | M12 (Pos Aju) | Sprint 6 |
| `logistik_stok.id_katalog` | SET NULL | M13 (Logistik) | Sprint 7 |
| `operasi_sitrep.id_assessment_basis` | SET NULL | M05 (Assessment) | Sprint 4 |
