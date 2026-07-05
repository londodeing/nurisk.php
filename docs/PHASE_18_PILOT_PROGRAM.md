# NURISK Phase 18 — Real Operational Pilot (30-Day Validation)

Dokumen ini merangkum program percontohan (*pilot program*) operasi lapangan NURISK selama 30 hari. Pilot ini adalah transisi krusial dari fase pengembangan menuju produksi penuh skala wilayah.

---

## 1. Pilot Objective

**Tujuan Pilot:**
Mengukur daya tahan sistem, validitas *workflow* (Logistik, *Offline Sync*, Surat & Pleno), dan respon perangkat lunak ketika digunakan secara paralel di medan nyata oleh pengguna non-teknis. Pilot ini bukan untuk menguji fitur secara fungsional, melainkan kelayakan operasionalnya.

**Hipotesis yang Ingin Dibuktikan:**
1. *Zero Data Loss*: Data asinkron (sitrep/assesment via mobile offline sync) terintegrasi ke *Command Center* tanpa ada konflik tertinggal.
2. *Realtime Stability*: Dasbor *Command Center* menampilkan status posko dan pergerakan stok logistik tanpa ada jeda atau *crash*.
3. *Governance Compliance*: Alur pembuatan surat jalan (mutasi/tugas) dan eskalasi pleno dikelola sepenuhnya melalui aplikasi tanpa kertas (100% *paperless*).

**Indikator Keberhasilan:**
- Tingkat sinkronisasi mobile berhasil 98% (minim error HTTP 429 / 500).
- Tidak ada stok logistik minus (Race condition negatif = 0).
- Waktu respon RTO (Recovery Time Objective) jika *downtime* terjadi tercatat kurang dari 15 menit.

**Indikator Kegagalan:**
- Ditemukannya konflik integritas data (contoh: logistik ganda atau penugasan tumpang tindih).
- 30% relawan atau operator menolak penggunaan karena alur aplikasi yang membingungkan.

---

## 2. Pilot Scope

Definisi ruang lingkup validasi 30-hari:

- **Jumlah PCNU:** 3 PCNU (Cabang) sebagai sampel representatif.
- **Jumlah Operator:** 15 Operator Posko.
- **Jumlah Relawan:** 150 Relawan Lapangan.
- **Jumlah Insiden Simulasi:** 3 Insiden Bencana Buatan (1 per PCNU untuk hari ke-1 sampai ke-5).
- **Jumlah Insiden Nyata:** 1-2 Insiden Bencana Aktual (jika alam berkehendak selama 30 hari).
- **Jumlah Surat:** Target 300 penerbitan dokumen administrasi.
- **Jumlah Pleno:** 30 sesi persetujuan / eskalasi kebijakan.
- **Jumlah Sitrep:** 450 Laporan (rata-rata 1 sitrep per hari dari tiap posko/relawan).

---

## 3. Stakeholder Matrix

| Stakeholder | Peran | Ekspektasi | Risiko |
| ----------- | ----- | ---------- | ------ |
| **PWNU** | *Sponsor & Pemantau* | Dasbor Command Center wilayah yang 100% transparan dan real-time | Laporan lambat masuk mengakibatkan kesalahan pengambilan keputusan strategis. |
| **PCNU** | *Manajer Bencana* | Kemudahan administrasi (pleno, surat, eskalasi logistik) ke PWNU | *Bottleneck* pada approval karena aplikasi sulit digunakan oleh pengurus senior. |
| **Operator Posko** | *Data Entry / Logistik* | Manajemen stok dan pembagian tugas cepat tanpa lag | Pekerjaan administratif menjadi lebih lambat daripada mencatat manual di buku. |
| **Relawan** | *Eksekutor Lapangan* | Fitur *Offline Sync* di blank-spot area bekerja mulus tanpa data hilang | Baterai boros, UI rumit di bawah tekanan bencana, atau laporan sitrep gagal terkirim. |
| **Admin Sistem (DevOps)** | *Pengendali Layanan* | Beban server terprediksi, backup berjalan otomatis, nol *unhandled exception* | Kegagalan server (*downtime*) atau Redis macet yang memblokir semua operasi. |

---

## 4. Daily Operational Checklist

*Checklist* harian bagi Operator Posko untuk memvalidasi rotasi aplikasi.

**Pagi (07:00 - 09:00)**
- [ ] Mengecek *Command Center* untuk memastikan seluruh Posko Aju dan status relawan akurat.
- [ ] Memeriksa stok logistik harian awal; sesuaikan sinkronisasi mutasi dari hari sebelumnya.
- [ ] Verifikasi ketersediaan surat tugas/mobilisasi baru untuk relawan.

**Siang (12:00 - 14:00)**
- [ ] Validasi penarikan (*pull/push*) *Offline Sync* Relawan yang kembali ke area bersinyal.
- [ ] Mengarsip Sitrep paruh hari yang masuk; memverifikasi foto dan titik koordinat GPS.
- [ ] (Jika ada) Mengikuti alur Pleno PCNU untuk eskalasi status bencana atau permohonan logistik.

**Malam (19:00 - 21:00)**
- [ ] Audit tutup stok logistik; memastikan barang fisik sama dengan data di dasbor.
- [ ] *Checkout* absen relawan; menutup status penugasan yang selesai.
- [ ] Rekap harian: mengecek apakah ada relawan yang gagal menyetor sitrep akibat *error device*.

---

## 5. Weekly Validation Checklist

*Checklist* divalidasi oleh Tim Teknis bersama PCNU.

- **Performa Sistem:** Apakah rata-rata waktu respon API (termasuk *sync*) tetap di bawah 2 detik?
- **Kualitas Data:** Apakah GPS yang tertera di peta insiden akurat? Apakah ada nilai logistik atau data relawan ganda?
- **Keamanan:** Apakah kebijakan otorisasi (akses lintas PCNU) berjalan sesuai *policy*? Adakah laporan login tak dikenali?
- **Penggunaan Fitur:** Modul mana yang paling jarang disentuh? (E.g. Pleno/Eskalasi diabaikan karena terlalu rumit?)
- **Kepatuhan Governance:** Apakah operator konsisten mencetak/membuat nomor surat dari sistem, atau mereka kembali ke format manual?

---

## 6. Bug Classification Framework

Jika *error* dilaporkan, kategorikan keikutsertaan *bug* sebagai berikut:

- **P0 (Critical Blocker)**
  Sistem terhenti. Data krusial korup.
  *Contoh: Tidak bisa aktivasi insiden, server 502/504 Bad Gateway massal, Sync Offline gagal total menyimpan Sitrep.*
- **P1 (High Impact)**
  Alur operasi penting rusak tapi masih ada *workaround* kecil, atau UX yang sangat membingungkan.
  *Contoh: Relawan tidak dapat input penugasan secara normal, mutasi logistik mengurangi stok tapi log tidak tercatat.*
- **P2 (Moderate)**
  Gangguan fungsionalitas non-kritis atau *bug UI* yang mengganggu visibilitas.
  *Contoh: Dashboard Live Map lambat merender titik, format surat PDF berantakan.*
- **P3 (Low / Enhancement)**
  *Minor typo*, peningkatan fitur.
  *Contoh: Warna tombol salah, *tooltip* tidak muncul, *loading spinner* hilang.*

---

## 7. UX Pain Point Collection Framework

Pengumpulan *feedback* UX difasilitasi melalui mekanisme pelaporan harian dari operator.

**Mekanisme:**
1. Di dalam NURISK Command Center akan disematkan tombol "Lapor Masalah UX" via Google Form/Layanan Eksternal.
2. Sesi Wawancara 15 menit mingguan dengan 3 Operator Posko secara acak.

**Kategori Feedback NURISK:**
- *Membingungkan*: Terminologi tidak familiar bagi relawan.
- *Terlalu Banyak Klik*: Workflow untuk membagikan 1 kardus beras membutuhkan lebih dari 3 klik.
- *Data Sulit Dicari*: Tidak ada filter *search* yang mumpuni di tabel logistik.
- *Workflow Tidak Natural*: Langkah persetujuan Pleno dinilai tidak sejalan dengan AD/ART nyata di lapangan.

---

## 8. Metrics Dashboard

Selama 30 hari, Tim Teknis wajib memonitor KPI berikut via infrastruktur server (Grafana / Telescope) dan basis data:

### Adoption
- **DAU (Daily Active Users):** > 80 user aktif/hari
- **MAU (Monthly Active Users):** > 140 user unik
- **Operator Aktif:** 100% dari operator yang direkrut rutin *login* setiap hari.

### Operations
- **Jumlah Insiden Terdaftar:** Sesuai *Scope* (minimal 3)
- **Jumlah Sitrep:** Menembus angka 300-400
- **Jumlah Penugasan:** Rasio 1 relawan minimal melayani 2 penugasan harian.

### Governance
- **Jumlah Pleno Terekam:** > 15 rapat Pleno (meski simulasi)
- **Jumlah Surat Terbit:** Sesuai pergerakan (minimal 100 dokumen).

### Logistics
- **Transaksi Mutasi:** Log aktivitas mencapai 1.000 *rows* mutasi masuk/keluar.
- **Stok Kritis:** 0% kejadian stok minus di tabel `logistik_stok`.

### Reliability
- **Error Rate:** Di bawah 1% transaksi gagal (5xx status).
- **Queue Failures:** < 0.5% (Event broadcast / job gagal).
- **Response Time:** p95 < 500ms, Sync Time < 3s.

---

## 9. Go / No-Go Framework

Berdasarkan *Metrics* dan hasil dari validasi mingguan, keputusan paska-30 Hari:

### GO REGIONAL
Skala deployment diperluas ke seluruh PCNU (publik production penuh).
*Kriteria:*
- Error Rate < 1% (P0 Bug = 0).
- Adopsi DAU > 80% stabil.
- 0 Kejadian Data Integrity Crash (logistik & sync offline 100% akurat).

### HOLD
Perlu penundaan 1 bulan lagi untuk refactoring dan perbaikan UX.
*Kriteria:*
- P0 Bug > 2 kejadian (misal server lumpuh, DB lock).
- Banyak keluhan P1 terkait kebingungan UI/UX yang menyebabkan operasi manual jauh lebih cepat.
- *Queue/Redis* sering mati (downtime > 30 menit).

### NO GO
Proyek ditinjau ulang; aplikasi ditutup dan dirombak arsitekturnya.
*Kriteria:*
- Data offline sync gagal memuat > 10% data aktual relawan.
- Sistem lambat dan ditinggalkan operator (Adopsi < 30%).

---

## 10. Pilot Closure Report Template

*(Laporan ini akan digunakan sebagai slide presentasi final kepada PWNU & PCNU).*

**I. Executive Summary**
> "Selama 30 hari, NURISK telah memproses [X] Insiden, [Y] Sitrep, dan [Z] Mutasi Logistik dengan RTO/Uptime Server [X]%. Operasi dinyatakan sukses dengan tingkat adopsi [X]% dari target lapangan."

**II. Key Achievements (Keberhasilan)**
1. Sinkronisasi *Offline* berhasil mengirim data relawan meski di area tanpa sinyal ([X] transaksi tersimpan asinkron).
2. Tata kelola Pleno dan Surat Keluar berhasil 100% diganti dari kertas menjadi sistem digital terpadu.

**III. Critical Learnings & Bottlenecks (Kendala Lapangan)**
1. [Sebutkan 2-3 Bug UX yang ditemukan di lapangan, e.g., Filter logistik rumit].
2. [Evaluasi infrastruktur, e.g., Kebutuhan peningkatan spesifikasi Server Redis].

**IV. Metrics Scorecard**
| Kategori | Target | Pencapaian Aktual | Status |
| :--- | :--- | :--- | :--- |
| **Uptime (Reliability)** | >99.9% | [Nilai Aktual]% | Pass/Fail |
| **Logistik Transaksi** | >1,000 | [Total] Mutasi | Pass/Fail |
| **Data Integrity (Stok)** | 0% Minus | [0]% Minus | Pass/Fail |

**V. Recommendation (Rekomendasi Strategis)**
Berdasarkan kriteria Go/No-Go framework, Komite Operasional & Tim Teknis memutuskan bahwa NURISK adalah **[ READY FOR REGIONAL DEPLOYMENT ]**.
Rekomendasi tindak lanjut adalah memperluas pelatihan aplikasi ke [X] jumlah Cabang baru, dan mendedikasikan 1 engineer untuk perbaikan alur persetujuan mobilisasi.
