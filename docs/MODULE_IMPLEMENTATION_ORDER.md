# MODULE_IMPLEMENTATION_ORDER.md — NURISK
# Urutan Implementasi Modul — FROZEN

> **DOKUMEN INI DIFREEZE.**
> Urutan implementasi tidak boleh diubah tanpa keputusan eksplisit.
> Setiap modul bergantung pada modul sebelumnya. JANGAN lompat domain.

---

## Prinsip Urutan Implementasi

1. **Dependency order** — domain fondasi harus selesai sebelum domain yang bergantung padanya
2. **Selesai berarti tuntas** — setiap modul harus memiliki: Migration + Model + Policy + FormRequest + Controller + Blade + Feature Test minimal
3. **Tidak ada modul parsial** — jangan mulai modul baru jika modul sebelumnya belum memiliki feature test
4. **Konfirmasi tabel terlebih dahulu** — jika tabel belum terkonfirmasi di SQL dump, TUNGGU konfirmasi sebelum implementasi

---

## FASE 1 — FONDASI SISTEM

> Fase ini adalah prasyarat absolut. Tidak ada fase lain yang bisa dimulai sebelum Fase 1 selesai.

---

### Modul 1: AUTH

**Dependency:** Tidak ada
**Estimasi:** Fondasi seluruh sistem

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `auth_users` | Akun pengguna, login via `no_hp` |
| `auth_roles` | 5 role PRD (sudah di-seed di SQL) |
| `auth_pengguna_profil` | Data profil: `nik`, `nama_lengkap`, `email`, `id_desa_domisili` |
| `auth_keahlian_master` | Master keahlian relawan |
| `auth_pengguna_keahlian` | Pivot pengguna ↔ keahlian |
| `model_has_roles` | Spatie role sync (diisi via trigger `tr_sync_user_role_insert`) |
| `model_has_permissions` | Spatie permission |

**Catatan Kritis:**
- `auth_users.id_pengguna` adalah primary key (bukan `id`)
- Kolom timestamp: `dibuat_pada`, `diperbarui_pada` (BUKAN `created_at`, `updated_at`)
- Password: `kata_sandi` (BUKAN `password`)
- Login menggunakan `no_hp`, bukan email
- Trigger `tr_sync_user_role_insert` dan `tr_sync_user_role_update` otomatis sync ke `model_has_roles`
- `status_akun`: `menunggu` (default setelah register), `aktif`, `nonaktif`, `suspend`
- Pengguna dengan `status_akun != 'aktif'` DILARANG login

**Output yang wajib dihasilkan:**
- [ ] Migration: `auth_users`, `auth_pengguna_profil`, `auth_keahlian_master`, `auth_pengguna_keahlian`
- [ ] Model: `AuthUser`, `AuthPenggunaProfil`, `AuthKeahlianMaster`, `AuthPenggunaKeahlian`
- [ ] `AuthUser` wajib override: `$table`, `$primaryKey`, `const CREATED_AT`, `const UPDATED_AT`, `getAuthPassword()` → `kata_sandi`
- [ ] `AuthController`: `showLoginForm()`, `login()`, `logout()`
- [ ] `RegisterPublikController`: registrasi akun publik (status_akun = 'menunggu')
- [ ] Middleware: `CheckAkunAktif` (cek status_akun = 'aktif')
- [ ] Blade: `auth/login.blade.php`, `auth/register.blade.php`
- [ ] Feature test: login berhasil, login gagal, akun menunggu ditolak, redirect sesuai role

---

### Modul 2: AUTHORIZATION INFRASTRUCTURE

**Dependency:** Modul 1 (AUTH)

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `master_jabatan` | Daftar jabatan struktural (15 jabatan default sudah di SQL) |
| `pengguna_jabatan` | Mapping pengguna ke jabatan aktif |

**Catatan Kritis:**
- Jabatan adalah posisi struktural, BUKAN role
- Satu pengguna dapat punya beberapa jabatan di periode berbeda
- Policy mengacu ke `auth_users.id_peran` (role) DAN `master_jabatan` (untuk aksi tanda tangan)
- `default_scope_type` dan `default_scope_id` di `auth_users` menentukan scope wilayah

**Output yang wajib dihasilkan:**
- [ ] Migration: `master_jabatan`, `pengguna_jabatan`
- [ ] Model: `JabatanPosisi`, `PenggunaJabatan`
- [ ] `AuthServiceProvider`: registrasi seluruh Policy ke Model
- [ ] Gate definisi: `finalize-sitrep`, `escalate-insiden`, `finalize-pleno`, `approve-logistik`, `sign-surat`
- [ ] Middleware: `ValidateScopeWilayah` (cek scope request sesuai `default_scope_id`)
- [ ] Base Policy `NuriskPolicy` dengan method helper `cekScopeWilayah(AuthUser $user, int $idPcnu): bool`
- [ ] Seeder: `JabatanPosisiSeeder` (15 jabatan default dari SQL)
- [ ] Feature test: scope enforcement pcnu vs pwnu, gate authorization

---

### Modul 3: MASTER DATA & SEED

**Dependency:** Modul 1, 2
**Keterangan:** Seed seluruh master data yang sudah ada di SQL dump

**Tabel terkait:**
| Tabel | Data |
|---|---|
| `auth_roles` | 5 role PRD (sudah di SQL) |
| `bencana_master_jenis` | 13 jenis bencana (sudah di SQL) |
| `master_satuan` | 29 satuan (sudah di SQL) |
| `logistik_kategori` | 7 kategori logistik (sudah di SQL) |
| `auth_keahlian_master` | 7 keahlian relawan (sudah di SQL) |
| `aset_master_kategori` | 5 kategori aset (sudah di SQL) |
| `aset_master_jenis` | 6 jenis aset (sudah di SQL) |
| `aset_master_status` | 5 status aset (sudah di SQL) |
| `operasi_master_klaster` | 6 klaster (sudah di SQL) |
| `master_surat_jenis` | Diisi manual oleh super_admin |
| `master_jabatan_penandatangan` | Diisi manual oleh super_admin |

**Output yang wajib dihasilkan:**
- [ ] Seeder untuk setiap tabel master yang datanya sudah ada di SQL
- [ ] `DatabaseSeeder` yang memanggil semua seeder secara berurutan
- [ ] CRUD master data di panel `super_admin` untuk data yang bisa berubah
- [ ] Feature test: semua seed data tersedia dan dapat di-query

---

## FASE 2 — OPERASIONAL INTI

> Fase ini membangun domain operasional utama. Dimulai setelah Fase 1 selesai.

---

### Modul 4: INSIDEN

**Dependency:** Modul 1, 2, 3

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `laporan_kejadian` | Laporan awal dari publik/petugas |
| `operasi_insiden` | Entitas insiden utama |
| `riwayat_status_insiden` | Histori setiap transisi status |
| `bencana_master_jenis` | Master jenis bencana (sudah ada dari Modul 3) |

**Catatan Kritis:**
- `kode_kejadian` UNIQUE, tidak dapat diubah setelah dibuat
- `id_pcnu` menentukan scope wilayah — wajib divalidasi di Policy
- Trigger `tr_validate_temporal_incident`: `waktu_selesai` tidak boleh sebelum `waktu_mulai`
- Trigger `tr_lock_incident_data`: jika `is_locked = 1`, semua UPDATE ditolak DB
- Setiap transisi status WAJIB dicatat ke `riwayat_status_insiden`
- Laporan publik (`laporan_kejadian`) memiliki validasi koordinat via trigger `tr_validate_coords_laporan`
- `is_valid` di `laporan_kejadian`: enum `menunggu`, `ya`, `tidak`
- Insiden yang `is_locked = 1` tampilkan banner terkunci di UI, disable semua form

**Output yang wajib dihasilkan:**
- [ ] Migration: `laporan_kejadian`, `operasi_insiden`, `riwayat_status_insiden`
- [ ] Model: `LaporanKejadian`, `OperasiInsiden`, `RiwayatStatusInsiden`
- [ ] Policy: `LaporanKejadianPolicy`, `InsidenPolicy` (with scope wilayah check)
- [ ] FormRequest: `StoreLaporanRequest` (publik), `StoreInsidenRequest`, `UpdateInsidenRequest`, `TransisiStatusInsidenRequest`
- [ ] Controller: `LaporanKejadianController` (store publik, validasi pcnu/pwnu), `InsidenController` (index, show, create, store, edit, update, transisiStatus)
- [ ] Service: `InsidenService::transisiStatus()` — handle transisi + catat riwayat + catat jurnal dalam 1 DB::transaction()
- [ ] Blade: `insiden/index.blade.php`, `insiden/show.blade.php`, `insiden/create.blade.php`, `insiden/edit.blade.php`, `laporan/create.blade.php` (publik)
- [ ] Feature test: CRUD, scope pcnu, transisi status, is_locked enforcement, kode_kejadian unique

---

### Modul 5: ASSESSMENT

**Dependency:** Modul 4 (INSIDEN)

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `assessment_utama` | Kajian lapangan per insiden |
| `assessment_dampak_manusia` | Data korban: `meninggal`, `hilang`, `menderita_mengungsi` |
| `assessment_kebutuhan_mendesak` | Kebutuhan: `kebutuhan_pangan`, `kebutuhan_medis` |

**Catatan Kritis:**
- Trigger `tr_single_latest_assessment`: hanya boleh 1 assessment dengan `is_latest = 1` per insiden
- `jenis_laporan`: `kaji_cepat` (default awal) atau `pendataan_lanjutan`
- `waktu_assesment` (perhatikan typo di SQL — harus mengikuti SQL: `waktu_assesment` bukan `waktu_assessment`)
- Assessment hanya dapat dibuat untuk insiden `status_insiden` ≠ `draft` dan ≠ `selesai`
- Koordinat: `latitude_titik_kaji`, `longitude_titik_kaji` — validasi batas Indonesia
- Soft delete via `dihapus_pada`
- Assessment yang menjadi basis sitrep (`id_assessment_basis` di `operasi_sitrep`) tidak dapat dihapus

**Output yang wajib dihasilkan:**
- [ ] Migration: `assessment_utama`, `assessment_dampak_manusia`, `assessment_kebutuhan_mendesak`
- [ ] Model: `AssessmentUtama`, `AssessmentDampakManusia`, `AssessmentKebutuhanMendesak`
- [ ] Policy: `AssessmentPolicy`
- [ ] FormRequest: `StoreAssessmentRequest` (dengan validasi koordinat)
- [ ] Controller: `AssessmentController` (nested di bawah insiden: `/insiden/{insiden}/assessment`)
- [ ] Blade: `assessment/index.blade.php`, `assessment/create.blade.php`, `assessment/show.blade.php`
- [ ] Feature test: is_latest trigger, koordinat invalid ditolak, assessment untuk insiden draft ditolak

---

### Modul 6: SITREP

**Dependency:** Modul 4, 5

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `operasi_sitrep` | Laporan situasi resmi per insiden |

**Catatan Kritis:**
- `nomor_sitrep` UNIQUE per `id_insiden` — generate otomatis (MAX + 1)
- Trigger `tr_auto_snapshot_sitrep`: snapshot_dampak di-populate otomatis saat INSERT
- Trigger `tr_auto_snapshot_sitrep_update`: update snapshot saat UPDATE (kecuali sudah `final`)
- `hash_snapshot` = SHA2(snapshot_dampak, 256) — untuk audit integrity
- `status_sitrep = 'final'`: `waktu_difinalisasi` dan `id_penfinalisasi` wajib diisi
- Sitrep FINAL: immutable, `file_pdf_path` harus diisi (PDF wajib di-generate)
- Soft delete via `dihapus_pada`
- Snapshot JSON harus valid (`CHECK (json_valid())`)

**Output yang wajib dihasilkan:**
- [ ] Migration: `operasi_sitrep`
- [ ] Model: `OperasiSitrep`
- [ ] Policy: `SitrepPolicy`
- [ ] FormRequest: `StoreSitrepRequest`, `FinalisasiSitrepRequest`
- [ ] Controller: `SitrepController` (index, show, create, store, finalisasi)
- [ ] Service: `SitrepPdfService::generate()` — generate PDF via dompdf, simpan ke `storage/app/public/sitrep/`
- [ ] Blade: `sitrep/index.blade.php`, `sitrep/show.blade.php`, `sitrep/create.blade.php`
- [ ] Feature test: nomor unik, snapshot otomatis, finalisasi immutable, hash_snapshot valid

---

## FASE 3 — GOVERNANCE

> Pleno dan surat adalah entitas governance. Harus diimplementasi setelah insiden berjalan.

---

### Modul 7: PLENO

**Dependency:** Modul 4 (INSIDEN)

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `operasi_pleno` | Rapat pleno |
| `operasi_pleno_keputusan` | Keputusan yang dihasilkan pleno |
| `operasi_pleno_peserta` | Peserta rapat dan hak suara |
| `operasi_eskalasi` | Eskalasi level insiden via pleno |
| `operasi_aktivasi` | Aktivasi operasi tanggap darurat |

**Catatan Kritis:**
- Pleno FINAL: tidak ada keputusan baru, tidak ada perubahan peserta
- Peserta `tolak` wajib isi `catatan_peserta`
- Eskalasi (`operasi_eskalasi`) wajib referensi ke `id_pleno`
- `level_baru` harus lebih tinggi dari `level_sebelumnya` (lokal < pcnu < pwnu < nasional)
- Penunjukan komandan pos aju (`operasi_posaju_komandan.id_pleno_penunjukan`) wajib ada pleno

**Output yang wajib dihasilkan:**
- [ ] Migration: `operasi_pleno`, `operasi_pleno_keputusan`, `operasi_pleno_peserta`, `operasi_eskalasi`, `operasi_aktivasi`
- [ ] Model: `OperasiPleno`, `OperasiPleno_Keputusan`, `OperasiPlenoPeserta`, `OperasiEskalasi`, `OperasiAktivasi`
- [ ] Policy: `PlanoPolicy`, `EskalasiPolicy`
- [ ] Controller: `PlanoController`, `EskalasiController`
- [ ] Blade: `pleno/index.blade.php`, `pleno/show.blade.php`, `pleno/create.blade.php`, `pleno/peserta.blade.php`
- [ ] Feature test: voting peserta, pleno final immutable, eskalasi authority (hanya pwnu), level eskalasi naik

---

### Modul 8: SURAT MENYURAT

**Dependency:** Modul 7 (PLENO)

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `operasi_surat_keluar` | Surat resmi utama |
| `dokumen_surat_paraf` | Daftar paraf berurutan |
| `dokumen_surat_tembusan` | Daftar penerima tembusan |
| `master_surat_jenis` | Jenis surat dan format nomor |
| `master_surat_template` | Template isi surat |
| `master_jabatan_penandatangan` | Jabatan yang berwenang tanda tangan |

**Catatan Kritis:**
- Paraf berurutan (`urutan` ASC) — tidak boleh lompat urutan
- Jika satu paraf `ditolak` → surat kembali ke DRAFT, semua paraf setelahnya di-reset ke `menunggu`
- Nomor surat di-generate otomatis berdasarkan `format_nomor` dari `master_surat_jenis`
- Surat FINALIZED: immutable, PDF wajib di-generate, disimpan ke `storage/app/public/surat/`
- Surat bukan sekadar upload file — surat adalah dokumen legal yang di-generate dari template

**Output yang wajib dihasilkan:**
- [ ] Migration: `operasi_surat_keluar`, `dokumen_surat_paraf`, `dokumen_surat_tembusan`, `master_surat_jenis`, `master_surat_template`, `master_jabatan_penandatangan`
- [ ] Model: `DokumenSuratUtama`, `DokumenSuratParaf`, `DokumenSuratTembusan`, `MasterSuratJenis`, `MasterSuratTemplate`
- [ ] Policy: `SuratPolicy`
- [ ] FormRequest: `StoreSuratRequest`, `ParafSuratRequest`
- [ ] Controller: `SuratController`, `ParafSuratController`
- [ ] Service: `SuratPdfService::generate()`, `NomorSuratService::generate()`
- [ ] Blade: `surat/index.blade.php`, `surat/show.blade.php`, `surat/create.blade.php`, `surat/paraf.blade.php`, `pdf/surat.blade.php`
- [ ] Feature test: paraf berurutan, paraf ditolak reset ke draft, finalisasi immutable, nomor surat unik

---

## FASE 4 — ASSIGNMENT DAN PERSONEL

---

### Modul 9: ASSIGNMENT

**Dependency:** Modul 4, 7

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `operasi_penugasan` | Penugasan personel ke insiden (otoritas sementara) |
| `operasi_otoritas_kontekstual` | Otoritas kontekstual dalam insiden |

**Catatan Kritis:**
- `peran_otoritas`: `komandan_insiden`, `trc`, `relawan`, `medis`, `logistik`, `operator`
- Assignment berakhir saat `waktu_selesai` diisi
- `ditugaskan_oleh` wajib diisi
- Cross-region: `asal_lingkup` dan `tujuan_lingkup` dicatat tanpa mengubah `auth_users.id_unit`
- Soft delete via `dihapus_pada`

**Output yang wajib dihasilkan:**
- [ ] Migration: `operasi_penugasan`, `operasi_otoritas_kontekstual`
- [ ] Model: `OperasiPenugasan`, `OperasiOtoritasKontekstual`
- [ ] Policy: `PenugasanPolicy`
- [ ] FormRequest: `StorePenugasanRequest`
- [ ] Controller: `PenugasanController`
- [ ] Blade: `penugasan/index.blade.php`, `penugasan/create.blade.php`
- [ ] Feature test: cross-region assignment valid, scope enforcement, assignment aktif check

---

### Modul 10: MOBILISASI PERSONEL DAN KLASTER

**Dependency:** Modul 9

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `operasi_mobilisasi_personil` | Perpindahan fisik personel ke lapangan |
| `operasi_klaster` | Klaster operasi per insiden |
| `operasi_klaster_koordinator` | Koordinator per klaster |
| `operasi_master_klaster` | Master 6 klaster (sudah di SQL) |
| `operasi_master_indikator` | Indikator keberhasilan per klaster |

**Catatan Kritis:**
- `status_kehadiran` di mobilisasi: `menuju_lokasi`, `di_lokasi`, `kembali`, `izin`
- Koordinator klaster ditunjuk via pleno (`id_pleno_penunjukan`)
- `progres_persen` di `operasi_klaster` diupdate manual oleh koordinator

**Output yang wajib dihasilkan:**
- [ ] Migration: semua tabel klaster dan mobilisasi
- [ ] Model terkait
- [ ] Controller: `MobilisasiController`, `KlasterController`
- [ ] Blade: `klaster/index.blade.php`, `klaster/show.blade.php`, `mobilisasi/index.blade.php`
- [ ] Feature test: transisi status kehadiran, klaster aktif/selesai

---

### Modul 11: SHIFT OPERASIONAL

**Dependency:** Modul 7, 9

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `operasi_periode` | Periode/shift operasi |

**Catatan Kritis:**
- `id_pleno_keputusan` wajib ada — periode dibuat berdasarkan keputusan pleno
- `tanggal_selesai` tidak boleh sebelum `tanggal_mulai`
- Perpanjangan periode harus via keputusan pleno baru
- `status_periode`: `berjalan`, `selesai`, `diperpanjang`

**Output yang wajib dihasilkan:**
- [ ] Migration: `operasi_periode`
- [ ] Model: `OperasiPeriode`
- [ ] Controller: `PeriodeController`
- [ ] Feature test: validasi tanggal, wajib keputusan pleno

---

## FASE 5 — LOGISTIK DAN ASET

---

### Modul 12: LOGISTIK

**Dependency:** Modul 4, 9

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `logistik_gudang` | Gudang penyimpanan (scope PCNU/PWNU) |
| `logistik_barang_katalog` | Katalog standar barang |
| `logistik_kategori` | Kategori barang (sudah di SQL) |
| `master_satuan` | Satuan barang (sudah di SQL) |
| `logistik_stok` | Stok barang per pos aju/gudang |
| `logistik_mutasi` | Setiap perubahan stok (SATU-SATUNYA cara ubah stok) |
| `logistik_perencanaan` | Perencanaan kebutuhan logistik |
| `logistik_permintaan` | Permintaan barang dari pos aju |

**Catatan Kritis - SANGAT PENTING:**
- **DILARANG UPDATE `logistik_stok.jumlah_tersedia` secara langsung**
- Semua perubahan stok HARUS melalui INSERT ke `logistik_mutasi`
- Trigger `tr_execute_logistik_stok_update` yang akan update stok otomatis
- Trigger `tr_logistik_mutasi_integrity_guard`: `keluar` > `jumlah_tersedia` → SIGNAL error
- Trigger `tr_validate_stock_ownership`: gudang PCNU-A tidak bisa suplai insiden PCNU-B
- Trigger `tr_validate_logistik_request_scope`: posaju tujuan harus dalam insiden yang sama
- `uuid_mutasi` CHAR(36) wajib unik per transaksi

**Output yang wajib dihasilkan:**
- [ ] Migration: semua tabel logistik
- [ ] Model: `LogistikGudang`, `LogistikBarangKatalog`, `LogistikStok`, `LogistikMutasi`, `LogistikPerencanaan`, `LogistikPermintaan`
- [ ] Policy: `LogistikPolicy`, `MutasiPolicy`, `PermintaanPolicy`
- [ ] FormRequest: `StoreMutasiRequest`, `StorePermintaanRequest`
- [ ] Controller: `LogistikGudangController`, `LogistikStokController`, `LogistikMutasiController`, `LogistikPermintaanController`
- [ ] Service: `LogistikMutasiService::catat()` — wrapper aman untuk mutasi, selalu dalam DB::transaction()
- [ ] Blade: `logistik/stok.blade.php`, `logistik/mutasi.blade.php`, `logistik/permintaan.blade.php`
- [ ] Feature test: stok tidak negatif, scope ownership gudang, mutasi keluar melebihi stok ditolak, `uuid_mutasi` unik

---

### Modul 13: RELAWAN

**Dependency:** Modul 1, 4, 9

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `relawan_pendaftaran` | Pendaftaran relawan untuk kebutuhan spesifik |
| `relawan_penugasan` | Penugasan relawan aktif |

**Catatan Kritis:**
- UNIQUE constraint: `(id_pengguna, id_relawan_kebutuhan)` di `relawan_pendaftaran`
- Relawan belum terverifikasi TIDAK dapat ditugaskan
- Cross-region assignment diperbolehkan tanpa mengubah organisasi asal
- Keahlian relawan di `auth_pengguna_keahlian`

**Output yang wajib dihasilkan:**
- [ ] Migration: `relawan_pendaftaran`, `relawan_penugasan`
- [ ] Model: `RelawanPendaftaran`, `RelawanPenugasan`
- [ ] Policy: `RelawanPolicy`
- [ ] Controller: `RelawanPendaftaranController`, `RelawanPenugasanController`
- [ ] Blade: `relawan/index.blade.php`, `relawan/show.blade.php`, `relawan/pendaftaran.blade.php`
- [ ] Feature test: unique constraint, relawan belum terverifikasi ditolak, cross-region valid

---

### Modul 14: ASET

**Dependency:** Modul 4

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `aset_unit` | Unit aset operasional |
| `aset_penggunaan` | Peminjaman aset per insiden |
| `aset_master_jenis` | Master jenis aset (sudah di SQL) |
| `aset_master_kategori` | Master kategori aset (sudah di SQL) |
| `aset_master_status` | Master status aset (sudah di SQL) |

**Catatan Kritis:**
- Trigger `tr_prevent_double_booking_aset`: aset yang `id_status ≠ 1` tidak bisa dipinjam
- Trigger `tr_aset_return_to_available`: saat `waktu_kembali` diisi, `id_status` kembali ke 1
- Status aset: 1=Tersedia, 2=Dalam Tugas, 3=Perbaikan/Maintenance, 4=Rusak, 5=Hilang
- `id_pemilik_unit` menentukan scope wilayah aset
- `kondisi_fisik`: `baik`, `rusak_ringan`, `rusak_berat`

**Output yang wajib dihasilkan:**
- [ ] Migration: `aset_unit`, `aset_penggunaan`
- [ ] Model: `AsetUnit`, `AsetPenggunaan`, `AsetMasterJenis`, `AsetMasterKategori`, `AsetMasterStatus`
- [ ] Policy: `AsetPolicy`
- [ ] Controller: `AsetUnitController`, `AsetPenggunaanController`
- [ ] Blade: `aset/index.blade.php`, `aset/show.blade.php`, `aset/pinjam.blade.php`
- [ ] Feature test: double-booking prevention, return trigger, status transition valid

---

## FASE 6 — LAPANGAN DAN PASCA RESPON

---

### Modul 15: POS AJU

**Dependency:** Modul 7, 12

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `operasi_posaju` | Pos komando lapangan |
| `operasi_posaju_komandan` | Histori komandan pos aju |
| `logistik_stok` | Stok yang terkait ke `id_posaju` |

**Catatan Kritis:**
- Pos aju dibuka berdasarkan keputusan pleno
- Komandan ditunjuk via pleno (`id_pleno_penunjukan` wajib)
- Pos aju DITUTUP: tidak dapat diaktifkan kembali
- Stok pos aju di-track melalui `logistik_stok` yang terkait `id_posaju`

**Output yang wajib dihasilkan:**
- [ ] Migration: `operasi_posaju`, `operasi_posaju_komandan`
- [ ] Model: `OperasiPosaju`, `OperasiPosajuKomandan`
- [ ] Policy: `PosajuPolicy`
- [ ] Controller: `PosajuController`
- [ ] Blade: `posaju/index.blade.php`, `posaju/show.blade.php`, `posaju/create.blade.php`
- [ ] Feature test: wajib pleno, komandan wajib, ditutup tidak dapat aktif lagi

---

### Modul 16: PENGUNGSIAN

**Dependency:** Modul 4, 5

**Tabel terkait:**
| Tabel | Keterangan |
|---|---|
| `master_penerima_manfaat` | Data penerima bantuan / pengungsi |

> ⚠️ **PERINGATAN:** Tabel operasional pengungsian belum terkonfirmasi di SQL dump. Implementasikan `master_penerima_manfaat` saja sampai ada konfirmasi tabel tambahan.

**Output yang wajib dihasilkan:**
- [ ] Migration: `master_penerima_manfaat`
- [ ] Model: `MasterPenerimaBanfaat` (perhatikan typo: `banfaat` sesuai nama tabel — konfirmasi ke DBA)
- [ ] Controller: `PengungsiController`
- [ ] Feature test: tipe penerima, NIK wajib untuk individu

---

### Modul 17: FEEDBACK KLASTER

**Dependency:** Modul 10, 11

> ⚠️ **TUNGGU KONFIRMASI TABEL SQL**
> Tabel feedback klaster BELUM terkonfirmasi ada di SQL dump.
> **JANGAN implementasi sebelum tabel dikonfirmasi.**
> Jika tabel belum ada: buat migration baru, BUKAN improvisasi struktur.

**Langkah:**
1. Konfirmasi nama tabel dan kolom ke DBA/owner SQL
2. Dokumentasikan di bagian "Catatan Sinkronisasi" di dokumen terkait
3. Buat migration sesuai konfirmasi
4. Baru implementasi modul ini

---

### Modul 18: GAP KEBUTUHAN

**Dependency:** Modul 17

> ⚠️ **TUNGGU KONFIRMASI TABEL SQL**
> Tabel gap kebutuhan BELUM terkonfirmasi ada di SQL dump.
> **JANGAN implementasi sebelum Modul 17 selesai dan tabel dikonfirmasi.**

---

## FASE 7 — DASHBOARD DAN PUBLIK

---

### Modul 19: COMMAND CENTER DAN DASHBOARD

**Dependency:** Semua modul sebelumnya (minimal Fase 1-5 selesai)

**Keterangan:**
- Command center adalah tampilan **read-only** aggregasi dari semua domain
- Tidak ada write operation dari command center
- Map berbasis Leaflet.js dengan marker insiden aktif

**Fitur wajib:**
- Peta Leaflet.js: marker insiden aktif (status: `respon`, `pemulihan`) dengan koordinat dari `laporan_kejadian`
- Statistik: jumlah insiden aktif per status, total personel di lapangan (`operasi_mobilisasi_personil`)
- Stok kritis: `logistik_stok.jumlah_tersedia` di bawah threshold
- Pos aju aktif dari `operasi_posaju`
- AJAX polling setiap 30 detik (BUKAN WebSocket)

**Output yang wajib dihasilkan:**
- [ ] `DashboardController`: aggregasi per role (pcnu hanya lihat scope sendiri)
- [ ] `CommandCenterController`: data peta + statistik
- [ ] API endpoint AJAX: `/api/command-center/insiden-aktif`, `/api/command-center/statistik`
- [ ] Blade: `dashboard/index.blade.php`, `command-center/index.blade.php`
- [ ] Public map: `publik/peta.blade.php`, `publik/laporan.blade.php`
- [ ] Feature test: data sesuai scope role, publik tidak dapat akses data internal

---

## Ringkasan Urutan dan Status

| # | Modul | Fase | Dependency | Status |
|---|-------|------|------------|--------|
| 1 | Auth | 1 | — | ⬜ Belum |
| 2 | Authorization Infrastructure | 1 | 1 | ⬜ Belum |
| 3 | Master Data & Seed | 1 | 1, 2 | ⬜ Belum |
| 4 | Insiden | 2 | 1-3 | ⬜ Belum |
| 5 | Assessment | 2 | 4 | ⬜ Belum |
| 6 | Sitrep | 2 | 4, 5 | ⬜ Belum |
| 7 | Pleno | 3 | 4 | ⬜ Belum |
| 8 | Surat | 3 | 7 | ⬜ Belum |
| 9 | Assignment | 4 | 4, 7 | ⬜ Belum |
| 10 | Mobilisasi & Klaster | 4 | 9 | ⬜ Belum |
| 11 | Shift Operasional | 4 | 7, 9 | ⬜ Belum |
| 12 | Logistik | 5 | 4, 9 | ⬜ Belum |
| 13 | Relawan | 5 | 1, 4, 9 | ⬜ Belum |
| 14 | Aset | 5 | 4 | ⬜ Belum |
| 15 | Pos Aju | 6 | 7, 12 | ⬜ Belum |
| 16 | Pengungsian | 6 | 4, 5 | ⬜ Belum |
| 17 | Feedback Klaster | 6 | 10, 11 | ⏳ Tunggu SQL |
| 18 | Gap Kebutuhan | 6 | 17 | ⏳ Tunggu SQL |
| 19 | Command Center & Dashboard | 7 | Semua | ⬜ Belum |

---

## Catatan Sinkronisasi SQL dan PRD

| Item | Keterangan |
|---|---|
| Feedback Klaster | Tabel belum ditemukan di SQL dump — perlu konfirmasi |
| Gap Kebutuhan | Tabel belum ditemukan di SQL dump — perlu konfirmasi |
| Pengungsian (operasional) | Hanya `master_penerima_manfaat` yang terkonfirmasi — tabel operasional pengungsian belum ada |
| Organisasi hierarki | Tidak ada tabel hierarki NU (PWNU/PCNU/MWC) di SQL — hanya enum di `auth_users.default_scope_type` |
| `waktu_assesment` | Typo di SQL (`assesment` bukan `assessment`) — IKUTI SQL, jangan koreksi sendiri |
