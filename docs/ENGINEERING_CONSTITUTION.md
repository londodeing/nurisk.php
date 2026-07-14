# Engineering Constitution: NURISK Platform

Dokumen ini adalah konstitusi rekayasa perangkat lunak tertinggi bagi platform NURISK. Konstitusi ini mengikat seluruh pengembang (termasuk AI Agent) dan wajib dipatuhi di setiap fase pengerjaan proyek.

---

## 1. Order of Authority (Hierarki Pengambilan Keputusan)

Setiap pengerjaan tugas atau pengubahan kode wajib mengikuti hierarki otoritas dokumen berikut secara berurutan. Dokumen di tingkat atas mengesampingkan keputusan di tingkat bawahnya:

```
                  VISION (Arah Strategis NURISK)
                                ↓
                 PRD (Product Requirements Document)
                                ↓
                 ADR (Architecture Decision Records)
                                ↓
                 Architecture Specs (BFF, COP, Popup)
                                ↓
                 Database Contract (Skema Basis Data)
                                ↓
                 BFF Contract (Struktur JSON Peladen)
                                ↓
                 Read Model (Ui-Ready Data Aggregation)
                                ↓
                 Flutter Renderer (Zero-Trust Klien)
```

*Aturan Utama:* Aplikasi Klien (Flutter) **tidak boleh** mengambil keputusan tampilan atau alur kerja sebelum kontrak BFF disepakati.

---

## 2. Sepuluh Aturan Utama Proyek (The Ten Rules)

1.  **Rule 1 (Flutter is a Renderer):** Flutter murni bertindak sebagai Universal Renderer pasif. Ia tidak menentukan tata letak secara sepihak.
2.  **Rule 2 (No Business Logic in Flutter):** Tidak boleh ada logika bisnis, perhitungan operasional, atau pemrosesan workflow di dalam aplikasi seluler.
3.  **Rule 3 (No Role Logic in Flutter):** Flutter dilarang keras mengenali peran pengguna (*No hardcoded role checks*).
4.  **Rule 4 (No Static Colors for Status):** Seluruh kode warna tingkat keparahan (*severity*), status laporan, dan lencana (*badge*) wajib dikirim dari backend dalam bentuk format hex color.
5.  **Rule 5 (No KPI Calculations in Flutter):** Seluruh penghitungan metrik performa utama (KPI) dikalkulasikan di sisi peladen.
6.  **Rule 6 (Runtime Services Requirement):** Semua layar Flutter wajib diinisialisasi dan dihubungkan ke Runtime Platform.
7.  **Rule 7 (BFF Integration Only):** Semua komunikasi data presentasi untuk UI wajib melalui endpoint BFF.
8.  **Rule 8 (Read Model Architecture):** Setiap perubahan domain data di backend wajib melalui proyeksi Read Model sebelum disajikan ke BFF.
9.  **Rule 9 (Plugin Isolation):** Tidak boleh memanggil pustaka/plugin eksternal (seperti Dio, Geolocation, Kamera) secara langsung di dalam Feature Layer Flutter. Semuanya harus dijembatani oleh Core Service.
10. **Rule 10 (Zero Tolerance to DoR Violations):** Dilarang memulai implementasi kode apa pun sebelum seluruh kriteria *Definition of Ready* terpenuhi.

---

## 3. Definition of Ready (DoR) - Kriteria Memulai Sprint

Sebuah tugas/sprint hanya diizinkan untuk dimulai jika seluruh kriteria berikut berstatus **TERPENUHI (Checked)**:

*   `[ ]` **PRD Terintegrasi:** Dokumen persyaratan produk sudah lengkap dan disepakati.
*   `[ ]` **ADR Tersedia:** Keputusan arsitektur yang mendasarinya sudah didokumentasikan.
*   `[ ]` **BFF Contract Valid:** Struktur API input dan output sudah terdefinisi.
*   `[ ]` **Read Model Schema:** Skema proyeksi data siap saji sudah dirancang.
*   `[ ]` **Database Contract:** Migrasi tabel database sudah didefinisikan.
*   `[ ]` **Acceptance Criteria & Test Scenario:** Kriteria keberhasilan dan skenario pengujian unit/integrasi telah tertulis.

---

## 4. Definition of Done (DoD) - Kriteria Menutup Sprint

Sebuah tugas/sprint dinyatakan **SELESAI (Done)** hanya jika telah memenuhi syarat verifikasi berikut:

*   `[ ]` **Kepatuhan Linter:** `flutter analyze` bersih tanpa error.
*   `[ ]` **Static Analysis Backend:** Analisis statis Larastan/PHPStan lolos.
*   `[ ]` **Automated Tests:** Seluruh uji unit (PHPUnit & Dart Test) sukses dengan kegagalan nol.
*   `[ ]` **Architecture Gate Checklist:** PR telah lolos evaluasi gerbang arsitektur.
*   `[ ]` **Dokumentasi Diperbarui:** File ADR, spesifikasi API, dan `ARCHITECTURE_RECOVERY_ROADMAP.md` telah disinkronkan.

---

## 5. Architecture Gate Checklist (Gerbang Evaluasi PR)

Setiap pengajuan perubahan kode wajib menjawab checklist kepatuhan berikut. Jika ada jawaban **"Ya"** pada kriteria yang dilarang, maka kode **WAJIB DITOLAK**:

| Pertanyaan Evaluasi | Kepatuhan | Tindakan Jika Melanggar |
| :--- | :---: | :--- |
| Apakah fitur memanggil API Client (Dio) secara langsung tanpa melalui Repository Core? | **TIDAK** | Tolak PR (Wajib gunakan Repository Core). |
| Apakah Flutter memiliki logika pengkondisian peran pengguna seperti `if(role == ...)`? | **TIDAK** | Tolak PR (Peran harus dikontrol dinamis oleh BFF). |
| Apakah aplikasi Flutter melakukan perhitungan agregasi metrik/KPI? | **TIDAK** | Tolak PR (Metrik harus dihitung di backend). |
| Apakah Flutter menentukan warna status secara lokal (`Colors.red` dll)? | **TIDAK** | Tolak PR (Hex color dikirim oleh BFF). |
| Apakah modul/fitur memanfaatkan sistem Runtime Service secara konsisten? | **YA** | Tolak PR (Wajib gunakan Runtime Service). |
