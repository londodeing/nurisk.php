# MODULE_CHECKLIST.md — Checklist Kesiapan Modul NURISK
# Checklist Teknis Implementasi — Technical Lead

> Versi: 2.0 — Tanggal: 17 Juni 2026
> Status: ACTIVE (Diperbarui untuk Arsitektur Hybrid Monolith)

---

## 🏗️ PANDUAN PENGGUNAAN
1. AI Agent **DILARANG** menandai modul sebagai selesai jika ada poin checklist yang belum tercentang `[x]`.
2. Setiap fitur yang didevelop wajib melalui 12 poin inspeksi ini untuk memastikan kepatuhan terhadap standar Laravel dan skema SQL v37 Frozen.

---

## M01 — AUTH & SECURITY
- [x] Model `User` (`auth_users`) & `Role` (`auth_roles`) selesai.
- [x] Profil pengguna (`auth_pengguna_profil`) memiliki relasi 1-to-1 dengan user.
- [x] Autentikasi Laravel session-based dan Middleware Role terpasang.
- [x] Form Request Validation untuk Register & Login selesai.
- [x] Otorisasi berbasis Policy pada operasi user & role (Jabatan & Context Policy terpasang).
- [x] Rute `/login`, `/logout`, `/profile` berfungsi.
- [x] Tampilan Blade: Login, Register, Edit Profile (Bootstrap 5.3).
- [x] Seeder: 5 role default PRD (super_admin, pwnu, pcnu, relawan, publik).
- [x] Uji Fitur (Feature Test) untuk login, registrasi, dan pergantian password.
- [x] Pengujian Otorisasi (mencegah user non-admin memodifikasi role).
- [x] Integritas DB: FK `id_peran` mengarah ke `auth_roles(id_peran)` terpasang.
- [ ] Audit log: Transisi status akun tercatat di `sistem_log_aktivitas` (Web Pending).

---

## M02 — STRUKTUR ORGANISASI & WILAYAH
- [x] Model `OrganisasiPcnu`, `OrganisasiMwc`, `OrganisasiRanting`, `OrganisasiUnit` selesai.
- [x] Model `WilayahKabupaten`, `WilayahKecamatan`, `WilayahDesa` selesai.
- [x] Relasi berjenjang (Ranting -> MWC -> PCNU -> PWNU) terdefinisi di Eloquent.
- [x] Seeder data wilayah Jawa Tengah terisi lengkap dari SQL dump.
- [ ] Form Request Validation untuk penambahan Organisasi/Unit baru (Web Pending).
- [x] Otorisasi Policy: PCNU hanya bisa memodifikasi organisasi di bawah kabupatennya.
- [ ] Tampilan Blade: Struktur Organisasi & Daftar Unit (Bootstrap 5.3) (Web Pending).
- [x] API Endpoint untuk mengambil daftar desa/kecamatan (untuk select dropdown).
- [x] Eager loading diterapkan untuk meminimalkan query wilayah.
- [ ] Uji Fitur: CRUD PCNU & Unit Organisasi NU (Web Pending).
- [x] Integritas DB: FK wilayah terhubung dengan `RESTRICT` pada aksi hapus.
- [ ] Audit log: Log aktivitas CRUD organisasi tercatat (Web Pending).

---

## M03 — MASTER JABATAN & KEAHLIAN
- [x] Model `MasterJabatan` (`master_jabatan`), `PenggunaJabatan` (`pengguna_jabatan`) selesai.
- [x] Model `KeahlianMaster` (`auth_keahlian_master`), `PenggunaKeahlian` (`auth_pengguna_keahlian`) selesai.
- [x] Relasi `master_jabatan.id_jabatan_posisi` ke `pengguna_jabatan` terpasang.
- [x] Logika penentuan jabatan aktif berdasarkan range tanggal (`waktu_mulai` & `waktu_selesai`).
- [x] Form Request Validation untuk asignasi jabatan user.
- [x] Otorisasi Policy: Hanya super_admin/pwnu yang bisa mengelola master jabatan.
- [x] Blade UI: Manajemen Jabatan Pengguna (Bootstrap 5.3).
- [ ] API Endpoint untuk lookup jabatan aktif (API Pending).
- [x] Uji Fitur: CRUD Jabatan & Penugasan Jabatan ke User.
- [x] Uji Otorisasi: Mencegah user biasa mengangkat dirinya sendiri ke jabatan lain.
- [x] DB Transaction diterapkan saat menyimpan data profil sekaligus keahlian baru.
- [x] Seeder: 15 jabatan default organisasi NU terisi.

---

## M04 — MANAJEMEN LAPORAN & INSIDEN
- [x] Model `LaporanKejadian` & `OperasiInsiden` selesai.
- [x] Relasi `LaporanKejadian` ke `OperasiInsiden` (1-to-1 / Nullable) terpasang.
- [x] DB Trigger `tr_lock_incident_data` (mencegah update jika `is_locked = 1`) berjalan (Simulated in Policy/Service).
- [x] Form Request: Pembuatan laporan publik dan pembuatan operasi insiden.
- [x] Otorisasi Policy: Pembatasan akses CRUD insiden berdasarkan `id_pcnu` (Scope Wilayah).
- [x] State Machine: Transisi status insiden tervalidasi di Form Request & Service Layer.
- [x] Riwayat transisi status tersimpan di `riwayat_status_insiden`.
- [x] Blade UI: Dashboard Insiden Aktif, Peta Koordinat (Leaflet.js).
- [ ] API Endpoint: `/api/insiden` (filter status, prioritas, wilayah) (API Pending).
- [x] Eager Loading: `with(['jenisBencana', 'pcnu'])` terpasang.
- [x] Uji Fitur: Transisi status dari draft -> terverifikasi -> respon -> selesai.
- [x] DB Transaction: Validasi laporan publik otomatis men-generate draf insiden dalam satu transaksi.

---

## M05 — DATA ASSESSMENT
- [ ] Model `AssessmentUtama`, `AssessmentDampakManusia`, `AssessmentKebutuhanMendesak` selesai.
- [ ] Relasi 1-to-many ke dampak manusia dan kebutuhan mendesak selesai.
- [ ] DB Trigger `tr_single_latest_assessment` (hanya ada satu `is_latest = 1` per insiden) diuji.
- [ ] Form Request: Kaji cepat dampak & kebutuhan mendesak lapangan.
- [ ] Otorisasi Policy: Hanya personil TRC / Relawan yang ditugaskan di insiden yang bisa mengisi.
- [ ] Blade UI: Form Input Assessment Lapangan, Rekapitulasi Dampak.
- [ ] API Endpoint: `/api/assessment` (JSON response untuk aplikasi mobile).
- [ ] Eager Loading: `with(['dampakManusia', 'kebutuhanMendesak'])`.
- [ ] Uji Fitur: Validasi input dampak (angka negatif harus ditolak).
- [ ] Uji Otorisasi: User luar wilayah dilarang memasukkan data assessment.
- [ ] DB Transaction: Menyimpan assessment induk dan detail kebutuhan dalam satu transaksi.
- [ ] Audit log: Perubahan data assessment tercatat lengkap.

---

## M06 — LAPORAN SITUASI (SITREP)
- [ ] Model `OperasiSitrep` & `OperasiSitrepSumber` selesai.
- [ ] DB Trigger `tr_auto_snapshot_sitrep` (auto snapshot assessment ke JSON) diuji.
- [ ] Logika hitung `hash_snapshot` (SHA-256) saat sitrep berstatus `final` selesai.
- [ ] Form Request: Pembuatan sitrep berkala per insiden.
- [ ] Otorisasi Policy: Komandan insiden / Admin PCNU wilayah terkait yang berwenang.
- [ ] Rantai State Machine: draft -> ditinjau -> final (sitrep final tidak bisa diedit/dihapus).
- [ ] Blade UI: Cetak PDF Sitrep Resmi.
- [ ] API Endpoint: `/api/sitrep` dengan parameter `id_insiden`.
- [ ] Uji Fitur: Validasi nomor sitrep berurutan per insiden (1, 2, 3, dst).
- [ ] Uji Otorisasi: Mencegah relawan mengubah sitrep yang sudah final.
- [ ] DB Transaction: Proses finalisasi sitrep mengunci data snapshot dan membuat file log.
- [ ] File Storage: Menyimpan PDF sitrep yang sudah difinalisasi di folder `/storage/sitrep/`.

---

## M07 — PENUGASAN OPERASIONAL (ASSIGNMENT)
- [x] Model `OperasiPenugasan` (`operasi_penugasan`) selesai.
- [x] Relasi ke `OperasiInsiden` and `AuthUsers` terpasang.
- [x] Logika pengecekan masa aktif penugasan (`waktu_selesai` IS NULL).
- [ ] Form Request: Penunjukan peran (Komandan, TRC, Relawan, Medis, Logistik) (Web/API Pending).
- [ ] Otorisasi Policy: Hanya admin wilayah / PWNU yang berwenang menugaskan.
- [ ] Blade UI: Board Koordinasi Tim Operasi Lapangan (Web Pending).
- [ ] API Endpoint: `/api/insiden/{id}/assignment` untuk daftar tim aktif (API Pending).
- [ ] Eager Loading: User profil ter-eager-load saat mengambil data tim.
- [ ] Uji Fitur: Validasi temporal penugasan (waktu mulai tidak boleh mendahului waktu insiden).
- [ ] Uji Otorisasi: Relawan tidak dapat mengubah penugasan dirinya sendiri.
- [ ] DB Transaction: Penugasan tim operasional dan penentuan peran kontekstual berjalan atomik.
- [ ] Audit log: Log perpindahan tugas personil tercatat.

---

## M08 — POS AJU & PENYELAMATAN
- [x] Model `OperasiPosaju`, `OperasiPosajuKomandan` selesai. `OperasiPosPengungsian` & `PengungsianSensusHarian` belum.
- [x] Relasi Pos Aju ke Insiden selesai.
- [x] Form Request: Pendirian Pos Aju (`StorePosajuRequest`, `UpdatePosajuRequest`, dll).
- [x] Otorisasi Policy: Pengelolaan Pos Aju terbatas pada PCNU / Komandan Insiden (`OperasiPosajuPolicy`).
- [x] Rantai State Machine: direncanakan -> aktif -> ditutup.
- [ ] Blade UI: Panel Kontrol Pos Aju, Dashboard Logistik internal Pos Aju (Web Pending).
- [x] API Endpoint: `/api/operasi/posaju` (untuk input/manage via REST API).
- [x] Eager Loading: Pos aju ter-load beserta data relasi PJ/Insiden.
- [x] Uji Fitur: Validasi data Pos Aju (status, koordinat, perpanjangan, penutupan).
- [x] Uji Otorisasi: Operator luar posko dilarang mengupdate posko terkait.
- [x] DB Transaction: Pembuatan Pos Aju dan penunjukan Komandan Pos Aju dalam satu transaksi.
- [x] Audit log: Perubahan status posko tercatat di riwayat (Simulated).

---

## M09 — LOGISTIK & GUDANG
- [ ] Model `LogistikGudang`, `LogistikBarangKatalog`, `LogistikStok`, `LogistikMutasi`, `LogistikPermintaan`, `LogistikPerencanaan` selesai.
- [ ] DB Trigger `tr_prevent_negative_stock` (stok tidak boleh di bawah 0) diuji.
- [ ] DB Trigger `tr_auto_update_stock_on_mutation` (mutasi masuk/keluar otomatis update `logistik_stok`) diuji.
- [ ] Form Request: Pengajuan mutasi barang dan permintaan logistik.
- [ ] Otorisasi Policy: Hanya divisi logistik PCNU/PWNU yang berwenang menyetujui mutasi.
- [ ] State Machine Permintaan: draft -> diajukan -> disetujui -> dikirim -> selesai.
- [ ] Blade UI: Manajemen Stok Gudang, Kartu Stok Barang, Form Permintaan Logistik.
- [ ] API Endpoint: `/api/logistik/stok` (cek ketersediaan barang).
- [ ] Uji Fitur: Penolakan otomatis mutasi jika stok di gudang pengirim tidak mencukupi.
- [ ] Uji Otorisasi: Relawan dilarang menyetujui permintaan barang dari poskonya sendiri.
- [ ] DB Transaction: Proses approval permintaan otomatis memotong stok gudang asal dan membuat log mutasi keluar.
- [ ] Audit log: Seluruh mutasi barang masuk/keluar tercatat di `logistik_mutasi`.

---

## M10 — RELAWAN & PENDAFTARAN
- [x] Model `RelawanKebutuhan`, `RelawanPendaftaran`, `RelawanPenugasan`, `RelawanShift` selesai.
- [x] Unique Constraint: `(id_pengguna, id_relawan_kebutuhan)` di `relawan_pendaftaran` diuji.
- [x] Relasi dari pendaftaran relawan ke user dan master keahlian selesai.
- [x] Form Request: Pendaftaran relawan mandiri, Verifikasi pendaftar.
- [x] Otorisasi Policy: Hanya divisi relawan PCNU yang berwenang memverifikasi.
- [x] State Machine: menunggu -> diverifikasi -> aktif / ditolak (Status: dibuka -> seleksi -> diterima -> ditugaskan -> selesai / ditolak).
- [ ] Blade UI: Portal Pendaftaran Relawan, Evaluasi Profil Relawan oleh Admin (Web Pending).
- [x] API Endpoint: `/api/relawan/daftar` & `/api/relawan/pendaftaran`.
- [x] Uji Fitur: Validasi usia/NIK relawan saat pendaftaran.
- [x] Uji Otorisasi: Relawan yang belum terverifikasi dilarang masuk ke dalam penugasan insiden.
- [x] DB Transaction: Verifikasi pendaftaran sekaligus pembuatan profil user relawan baru (Dibuat otomatis di RelawanService).
- [x] Audit log: Perubahan status pendaftaran relawan tercatat.

---

## M11 — FEEDBACK KLASTER & GAP KEBUTUHAN
- [x] Model `OperasiKlaster`, `OperasiKlasterKoordinator` selesai. `Feedback` dan `Gap` belum.
- [ ] Relasi klaster feedback ke penugasan insiden dan gap kebutuhan selesai.
- [ ] Form Request: Input evaluasi klaster, Registrasi gap logistik/relawan.
- [x] Otorisasi Policy: Koordinator klaster / PWNU yang berwenang menutup status gap (Klaster/Tugas Policy terpasang).
- [ ] State Machine Gap: terbuka -> diproses -> terpenuhi -> ditutup.
- [ ] Blade UI: Panel Evaluasi Pasca Bencana, Tabel Gap Kebutuhan (Web Pending).
- [x] API Endpoint: `/api/operasi/klaster` & `/api/operasi/tugas`.
- [ ] Uji Fitur: Gap kebutuhan hanya bisa dibuat jika ada feedback klaster yang menyatakannya.
- [x] Uji Otorisasi: Mencegah koordinator klaster A menutup tugas klaster B.
- [x] DB Transaction: Penyimpanan feedback klaster sekaligus inisiasi gap kebutuhan berjalan atomik.
- [x] Audit log: Log perubahan status gap tercatat.

---

## M12 — GOVERNANCE PLENO, SURAT & COMMAND CENTER
- [ ] Model `OperasiPleno`, `OperasiPlenoPeserta`, `OperasiPlenoKeputusan`, `OperasiEskalasi`, `OperasiAktivasi` selesai.
- [x] Model `OperasiSuratKeluar` (`operasi_surat_keluar`) selesai. `DokumenSuratParaf`, `DokumenSuratTembusan`, `MasterSuratJenis`, `MasterSuratTemplate`, `MasterJabatanPenandatangan` belum.
- [ ] Model View: 10 views utama (seperti `v_command_center_summary`) terpetakan ke Eloquent read-only.
- [ ] Logika alur persetujuan paraf berurutan di `DokumenSuratParaf` selesai.
- [ ] Form Request: Pendaftaran rapat pleno, pengajuan draf surat keluar.
- [ ] Otorisasi Policy: Otoritas tanda tangan surat dicocokkan dengan `MasterJabatanPenandatangan` dan `PenggunaJabatan` aktif.
- [ ] State Machine Surat: draft -> review_paraf -> siap_tanda_tangan -> ditandatangani -> ditolak -> arsip.
- [ ] Blade UI: Cetak Dokumen PDF Surat Resmi, Dashboard Command Center (Peta & Grafik) (Web Pending).
- [ ] API Endpoint: `/api/surat` dan `/api/command-center` (API Pending).
- [ ] Uji Fitur: Paraf ke-3 ditolak otomatis membalikkan status surat di `operasi_surat_keluar` kembali ke `draft`.
- [ ] Uji Otorisasi: User biasa dilarang melakukan tanda tangan surat atau mengubah pleno yang berstatus `final`.
- [ ] DB Transaction: Finalisasi pleno secara atomik membuat entri keputusan, mengunci draf eskalasi, dan men-generate template draf surat keluar.
- [ ] File Storage: Hasil generate PDF surat resmi otomatis tersimpan di folder `/storage/surat/`.
- [ ] Audit log: Perubahan status pleno & surat masuk ke audit log.
