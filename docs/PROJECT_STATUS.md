# PROJECT_STATUS.md — Status Kesiapan & Progress NURISK
# Dashboard Pra-Produksi & Development — Technical Project Manager

> Versi: 2.1 — Tanggal: 18 Juni 2026
> Status: ACTIVE (Diperbarui untuk Realignment Arsitektur Hybrid Monolith)

---

## 📊 RINGKASAN PROGRESS GLOBAL

* **Tahap Saat Ini**: Pra-Produksi & Pengembangan (Fase Realignment Arsitektur Hybrid Monolith)
* **Kesiapan Dokumen Pra-Produksi**: 100% (Selesai disesuaikan dengan arsitektur Hybrid Monolith)
* **Status Basis Data (SQL Dump v37)**: **FROZEN** (Seluruh skema dibekukan. Pengerjaan dinamis di testing akan ditransisikan ke migrasi fisik)
* **Kesiapan Development**: **IN PROGRESS** (Domain Auth, Organisasi, Insiden, Pos Aju, Klaster, Tugas, dan Relawan telah diimplementasikan baik secara Web maupun REST API).

---

## 🏃 ROADMAP SPRINT & TARGET DELIVERY

Status pelacakan sprint pengembangan NURISK yang diselaraskan dengan keputusan **Hybrid Monolith Architecture**:

| Sprint | Modul / Cakupan Kerja | Estimasi Durasi | Status | Deliverable Utama |
| :--- | :--- | :---: | :---: | :--- |
| **Sprint 1** | Autentikasi & Otorisasi | 2 Minggu | **SELESAI** | Auth, Middleware Role, Integrasi Jabatan, Web CRUD Jabatan |
| **Sprint 2** | Organisasi & Wilayah | 1 Minggu | **SELESAI** | Master wilayah, unit organisasi NU, dropdown dinamis wilayah |
| **Sprint 3** | Manajemen Insiden | 2 Minggu | **SELESAI** | Laporan publik, pembuatan insiden (Web CRUD), locking data |
| **Sprint 4** | API Pos Aju & Relawan | 2 Minggu | **SELESAI (Logic)** | REST API Pos Aju, Rekrutmen, Pendaftaran & Penugasan Relawan (Pending Migrasi Fisik) |
| **Sprint 5** | API Klaster & Tugas | 1 Minggu | **SELESAI (Logic)** | REST API 6 Klaster, Progres Kerja, dan Tugas Mikro Lapangan (Pending Migrasi Fisik) |
| **Sprint 6** | ARCH-002 — Migration Consolidation | 1 Minggu | **ACTIVE** | Migrasi fisik seluruh skema ke `database/migrations` (Pembersihan schema testing) |
| **Sprint 7** | Field Assessment (API) | 2 Minggu | **BELUM DIMULAI**| Input kaji cepat dampak manusia & kebutuhan mendesak |
| **Sprint 8** | Laporan Situasi (Sitrep API) | 2 Minggu | **BELUM DIMULAI**| Sitrep berurutan, DB auto-snapshot, hash integrity SHA-256 |
| **Sprint 9** | Manajemen Logistik (API) | 2 Minggu | **BELUM DIMULAI**| Stok gudang, kartu kontrol, mutasi barang via trigger DB |
| **Sprint 10** | Governance Pleno & Surat | 2 Minggu | **SELESAI** | Pleno strategis (Web), draft & paraf surat keluar (Web), generate PDF |
| **Sprint 11** | Aset & Pengungsian | 2 Minggu | **BELUM DIMULAI**| Double-booking aset (API), sensus harian pengungsian (API) |
| **Sprint 12** | Command Center & Dashboard | 2 Minggu | **BELUM DIMULAI**| Dashboard visual read-only (Web SSR + Leaflet.js), cetak PDF |

---

## 📊 REALISTIC PRODUCTION READINESS STATUS

Status kesiapan modul secara teknis untuk lingkungan produksi (Staging/Production):

| Domain | Business Logic | Production Ready | Keterangan / Blocker |
| :--- | :---: | :---: | :--- |
| **Auth** | 100% | 80-90% | Menunggu verifikasi di atas MySQL fisik |
| **Organisasi** | 100% | 80-90% | Menunggu verifikasi di atas MySQL fisik |
| **Wilayah** | 100% | 80-90% | Menunggu verifikasi di atas MySQL fisik |
| **Insiden** | 100% | 80-90% | Menunggu verifikasi di atas MySQL fisik |
| **Pos Aju** | 95% | 60% | Blocker: Migrasi fisik belum ada (ARCH-002) |
| **Klaster** | 95% | 60% | Blocker: Migrasi fisik belum ada (ARCH-002) |
| **Tugas** | 95% | 60% | Blocker: Migrasi fisik belum ada (ARCH-002) |
| **Relawan** | 95% | 60% | Blocker: Migrasi fisik belum ada (ARCH-002) |

---

## ⚙️ RINGKASAN PROGRESS MODUL (TEKNIS)

Status implementasi codebase riil (Models, Controllers, Form Requests, Policies, Blades, Tests) untuk masing-masing modul:

* **M01 — Auth & User Management**: 100% (Backend + Web Views + Seeder + Tests selesai)
* **M02 — Organisasi & Wilayah**: 100% (Backend + API Dropdown + Seeder selesai)
* **M03 — Jabatan & Keahlian**: 100% (Web CRUD Jabatan + Seeder selesai)
* **M04 — Laporan & Insiden**: 100% (Web CRUD + Backend Service + Tests selesai)
* **M05 — Field Assessment**: 0% (Belum Dimulai)
* **M06 — Laporan Situasi (Sitrep)**: 0% (Belum Dimulai)
* **M07 — Pleno & Eskalasi**: 100% (Web CRUD + Service + Policy + Tests selesai)
* **M08 — Surat Menyurat**: 100% (Web CRUD + Paraf + Finalisasi PDF + Tests selesai)
* **M09 — Assignment & Otoritas Kontekstual**: 10% (Model `OperasiPenugasan` selesai)
* **M10 — Mobilisasi Personel & Klaster**: 50% (REST API Klaster lengkap, Web UI pending)
* **M11 — Shift / Periode Operasional**: 50% (REST API Tugas lengkap, Web UI pending)
* **M12 — Logistik & Gudang**: 0% (Belum Dimulai)
* **M13 — Relawan & Pendaftaran**: 50% (REST API Relawan lengkap, Web UI pending)
* **M14 — Aset**: 0% (Belum Dimulai)
* **M15 — Pos Aju**: 50% (REST API Pos Aju lengkap, Web UI pending)
* **M16 — Pengungsian**: 0% (Belum Dimulai)
* **M17 — Jurnal & Audit**: 25% (Riwayat status insiden + operasi_jurnal untuk Pleno & Surat)
* **M18 — Command Center**: 0% (Belum Dimulai)

---

## ⚠️ CATATAN RISIKO & MITIGASI (RISK REGISTER)

| No | Risiko Teridentifikasi | Dampak | Level | Rencana Mitigasi |
| :--- | :--- | :--- | :---: | :--- |
| 1 | Tidak ada berkas migrasi fisik untuk Pos Aju, Relawan, Klaster, Tugas. | Aplikasi gagal dideploy ke server staging/production (Hanya jalan di sqlite test). | **Kritis** | Pindahkan definisi skema dinamis di unit test ke berkas migrasi fisik Laravel resmi. |
| 2 | Inkonsistensi data / *race condition* pada penyelesaian tugas dan klaster. | Status data klaster/tugas tidak konsisten akibat akses bersamaan. | **Tinggi** | Terapkan `DB::transaction()` pada service layer Pos Aju, Klaster, dan Relawan. |
| 3 | Pergeseran kebutuhan dari Web Monolit ke REST API Flutter Mobile. | Backlog lama menuntut Blade views yang tidak dibutuhkan oleh mobile client. | **Sedang** | Lakukan *realignment* backlog dengan menandai tugas web-only sebagai Optional. |

---

## 📝 LOG PERUBAHAN & STATUS TUGAS

* **17 Juni 2026 (ARCH-001)**:
  * Penyelarasan arsitektur resmi menjadi **Hybrid Monolith Architecture**.
  * Audit kesenjangan codebase dan pemetaan status modul diperbarui secara riil.
  * Klasifikasi ulang seluruh task web-only untuk Pos Aju, Relawan, dan Klaster menjadi Optional.
  * Identifikasi **PRODUCTION BLOCKER** berupa tidak adanya migrasi fisik untuk tabel Pos Aju dan Relawan.

* **18 Juni 2026 (M07-M08 Hardening)**:
  * **M07 Hardening (4 issues)**:
    - Validasi `different:level_sebelumnya` pada StoreEskalasiRequest (`level_baru` != `level_sebelumnya`).
    - Kategori event jurnal diubah dari `'aktivasi'` → `'sistem'` pada PlanoService::catatJurnal().
    - Guard update() dan finalisasi() pada PlanoPolicy mencegah modifikasi pleno final.
    - Authorize('view', $insiden) ditambahkan ke PlanoController::store() untuk scope PCNU.
  * **M08 Hardening (4 issues)**:
    - Finalisasi surat: PDF generation dipindah ke dalam DB::transaction sebelum status change (rollback jika gagal).
    - Jurnal audit trail pada SuratService::prosesParaf() (approve/reject dicatat ke operasi_jurnal).
    - Validasi MasterJabatanPenandatangan keberadaan jabatan pada StoreSuratRequest.
    - Policy update() guard untuk surat yang sudah ditandatangani/diarsip.
  * **Testing**: 18 test methods baru (8 Surat, 7 Plano, 3 Eskalasi). Full suite: 441 passed, 3 skipped.
  * **Model baru**: OperasiJurnal (app/Models/OperasiJurnal.php) untuk tabel operasi_jurnal.
  * **Produksi**: operasi_jurnal table exists in nurisk via ARCH-002 consolidation; testing DB needs separate migration to enable jurnal tests.
