# NURISK Phase 18B — Human Factors & Operational Reality Audit

Dokumen ini disusun dari kacamata gabungan *Senior UX Researcher*, *Disaster Operations Consultant*, *Product Manager*, dan *Incident Command System Advisor*. Titik berat fase ini bukan memvalidasi apakah kode bekerja, melainkan **apakah manusia dalam kondisi krisis sanggup menggunakan sistem ini**.

---

## 1. Operator Reality Assessment

Kondisi lapangan yang penuh tekanan mengharuskan kita memetakan realitas para operator sistem:

### A. Operator PWNU (Pusat Kendali Wilayah)
- **Tingkat Literasi Digital:** Menengah - Tinggi.
- **Kondisi Kerja:** Di ruangan ber-AC dengan koneksi internet stabil (fiber optik), monitor besar.
- **Tekanan Psikologis:** Sedang (tekanan birokrasi dan politis dari pimpinan).
- **Keterbatasan Alat:** Tidak ada, biasanya multi-monitor.
- **Kemungkinan Kesalahan Input:** Rendah, namun rentan pada *approval* buta (*blind approve*) akibat menumpuknya eskalasi.

### B. Operator PCNU (Manajer Cabang/Daerah)
- **Tingkat Literasi Digital:** Menengah. Terkadang diwakili oleh pengurus senior yang gagap teknologi.
- **Kondisi Kerja:** Ruang kantor cabang, internet terkadang *tethering* dari HP.
- **Tekanan Psikologis:** Tinggi (tuntutan mobilisasi logistik cepat dari posko).
- **Keterbatasan Waktu:** Sangat terbatas; dituntut segera memutuskan Pleno dan Distribusi.
- **Kemungkinan Kesalahan Input:** Menengah (salah ketik nomor surat, lupa mengubah status insiden).

### C. Operator Posko (Garis Depan Logistik & Data)
- **Tingkat Literasi Digital:** Rendah - Menengah (bervariasi).
- **Kondisi Kerja:** Tenda posko yang bising, berdebu, listrik dari genset, sinyal internet putus-nyambung.
- **Tekanan Psikologis:** Sangat Tinggi (berhadapan langsung dengan korban yang panik, kurang tidur).
- **Keterbatasan Waktu:** Sangat Kritis. Waktu ekstra untuk *input* data setara dengan nyawa.
- **Kemungkinan Kesalahan Input:** Sangat Tinggi (salah *input* jumlah stok, salah *assign* relawan, typo parah).

### D. Relawan Lapangan (Ujung Tombak Evakuasi/Assessment)
- **Tingkat Literasi Digital:** Menengah (umumnya milenial/Gen-Z yang terbiasa dengan medsos, bukan aplikasi *form-heavy*).
- **Kondisi Kerja:** Hujan, lumpur, malam hari, baterai HP sisa 15%, pakai sarung tangan.
- **Tekanan Psikologis:** Kritis (fokus pada keselamatan).
- **Keterbatasan Perangkat:** Layar kecil, layar retak, *touchscreen* tidak responsif karena basah.
- **Kemungkinan Kesalahan Input:** Sangat Tinggi (asal klik untuk cepat selesai).

---

## 2. Workflow Friction Audit

Identifikasi *friction* (gesekan) pada alur kerja utama yang dapat memicu frustrasi dan pengabaian sistem.

| Workflow | Jumlah Langkah | Risiko Ditinggalkan | Rekomendasi Penyederhanaan |
| -------- | -------------- | ------------------- | -------------------------- |
| **Input Sitrep** | > 8 klik (Buka -> Pilih Insiden -> Isi 5 field -> Submit) | **Tinggi** | **Quick Sitrep**: 1 klik *Voice-to-Text* atau cukup unggah foto + GPS + 1 kalimat. |
| **Mutasi Keluar Logistik** | > 10 klik (Cari barang -> Input qty -> Pilih penerima -> Approve) | **Sangat Tinggi** | **Barcode/QR Scanner**: Cukup *scan* barcode barang dan *scan* ID Relawan (2 langkah). |
| **Persetujuan Mobilisasi** | 4 klik (Buka -> Review -> Klik Approve -> Confirm) | Menengah | **Bulk Approve**: *Checklist* beberapa nama sekaligus lalu tekan *Approve All* di list. |
| **Pembuatan Surat/Pleno** | > 15 klik & Mengetik Panjang | Menengah | **Auto-Generate Draft**: Gunakan *template boilerplate* yang hanya butuh 1-2 klik persetujuan. |

---

## 3. Critical Screen Audit

Terdapat 10 layar utama yang menentukan keberhasilan operasi. Informasi harus dapat ditangkap dalam batas toleransi kognitif **5 detik**.

**1. Dasbor Command Center (PWNU/PCNU)**
- **Wajib (5s):** Total Korban Kritis, Posko kekurangan logistik, Titik merah di Peta.
- **Sembunyikan:** Histori log aktivitas, daftar nama pengurus yang *online*.
- **Keputusan Cepat:** Tombol "Eskalasi Status" atau "Kirim Bantuan BKO".

**2. Mobile Home Screen (Relawan)**
- **Wajib (5s):** Tugas saya hari ini, Status Koneksi (*Offline/Online*).
- **Sembunyikan:** Berita organisasi, statistik posko lain.
- **Keputusan Cepat:** Tombol darurat "Lapor Titik Kritis" (Sitrep Instan).

**3. Layar Input Mutasi Gudang**
- **Wajib (5s):** Saldo stok *realtime* (angka besar), tombol Input Masuk/Keluar.
- **Sembunyikan:** Kategori detail, riwayat mutasi lengkap.
- **Keputusan Cepat:** Input jumlah barang dan finalisasi distribusi.

*(Hanya 3 layar paling fatal yang dijabarkan untuk fokus efisiensi waktu, prinsip serupa diterapkan pada 7 layar sisanya: Daftar Insiden, Detail Assessment, Approval Mobilisasi, Pleno Board, Manajemen Klaster, Daftar Relawan, dan Pembuatan Surat).*

---

## 4. Field Reality Simulation

Bagaimana NURISK bertahan dari realitas bencana sebenarnya?

- **Listrik Padam + Internet Satelit Lambat (Latency > 1500ms):**
  - *Fitur yang berpotensi GAGAL:* Dashboard Command Center yang mengandalkan WebSocket/Reverb akan mengalami *timeout* terus menerus.
  - *Rekomendasi:* Fallback mode ke *Long-Polling* atau *Lightweight JSON Refresh* secara asinkron.
- **Hujan Deras + Sarung Tangan + Relawan Baru:**
  - *Fitur yang berpotensi GAGAL:* Input Assessment panjang dengan *dropdown* bersarang. Layar basah menyebabkan *ghost-touches*.
  - *Rekomendasi:* Antarmuka tombol raksasa (ukuran jempol) dan hindari navigasi *scroll* yang presisi.
- **Operator Kelelahan (Shift ke-18 jam) + 3 Insiden Bersamaan:**
  - *Fitur yang berpotensi GAGAL:* Mengalokasikan relawan ke klaster yang benar. Cenderung salah pilih *dropdown* insiden.
  - *Rekomendasi:* Beri warna layar *background* yang berbeda kontras untuk tiap insiden agar operator sadar sedang berada di menu insiden yang mana.

---

## 5. Cognitive Load Analysis

Beban kognitif (*Cognitive Load*) operator posko saat membuka halaman Daftar Penugasan Relawan:
- **Informasi Ditampilkan:** > 50 baris data (nama, nomor HP, asal, klaster, jam tugas).
- **Keputusan Harus Dibuat:** Siapa yang *idle* dan bisa di-*assign* ke evakuasi saat ini?
- **Notifikasi Diterima:** 3 *Toast notification* muncul bersamaan (Sitrep baru masuk).

**Rekomendasi Penyederhanaan:**
*Hukum Miller (7±2)*: Manusia hanya bisa memproses 5-9 informasi dalam memori jangka pendek.
- Ubah tabel data panjang menjadi **Kartu Ringkasan** (contoh: "15 Relawan Nganggur di Posko A").
- Padamkan *toast notification* non-kritis selama fase 'Tanggap Darurat', ubah menjadi indikator lonceng statis.

---

## 6. Feature Adoption Risk

### High Adoption (Akan sering dipakai)
- **Sitrep Cepat (Mobile):** Sifat dasar manusia ingin melaporkan situasi.
- **Absensi Check-in Relawan:** Sering dikaitkan dengan perhitungan jatah konsumsi/uang saku.
- **Live Map:** Menarik secara visual dan memuaskan atasan (PWNU).

### Medium Adoption (Tergantung paksaan pimpinan)
- **Assessment Lengkap:** Lembar data panjang yang biasa diisi belakangan saat istirahat.
- **Approval Mobilisasi BKO:** Seringkali relawan berangkat dulu, *approval* disusulkan.

### Low Adoption (Berpotensi membebani)
- **Surat Menyurat Digital:** Birokrasi sering dikalahkan oleh instruksi via WhatsApp Group (WAG).
- **Klaster Task Management:** Relawan lebih suka bertindak langsung ketimbang mengecek status *checklist* 'To-Do' di aplikasi.

### Likely Abandoned (Pasti Ditinggalkan)
- **Modul Pleno Kompleks:** Rapat krisis PCNU tidak akan menyempatkan buka laptop untuk input hasil *voting* per individu; mereka akan rapat luring dan hanya menginput *Status Final* saja.

---

## 7. Command Center Reality Audit

- **Apakah operator membuka dasbor ini setiap hari?**
  Ya, tetapi sebagai tontonan statis di TV/Monitor (Mode *Kiosk*), bukan sebagai alat kerja analitik mendalam.
- **Widget yang kemungkinan tidak pernah dilihat:**
  Grafik batang historis kejadian per bulan (tidak relevan saat bencana sedang terjadi hari ini).
- **Widget yang harus dipromosikan ke posisi teratas:**
  Daftar "Kebutuhan Mendesak" (*Urgent Needs*) dari Posko Aju dan Status Stok Makanan.

---

## 8. Executive Dashboard Audit

(Kebutuhan PWNU / Ketua PCNU)

*HILANGKAN*: Latency server, jumlah *queries*, rincian nama relawan, log mutasi satuan.
*TAMPILKAN (The Big Three)*:
1. **Status Korban & Pengungsi (Agregat Total).**
2. **Kapasitas Gudang (Sisa Berapa Hari logistik bertahan?).**
3. **Peta Sebaran Posko (Hijau = Aman, Merah = Kritis).**

Ketua PCNU hanya butuh data untuk dijawab ke wartawan atau pemerintah (Bupati/Gubernur).

---

## 9. Training Requirement Matrix

| Role | Waktu Pelatihan Minimum | Risiko Setelah Pelatihan |
| ---- | ----------------------- | ------------------------ |
| **PWNU (Executive)** | 15 Menit | Lupa *password*, meminta ajudan yang membuka aplikasi. |
| **PCNU (Manager)** | 2 Jam | Cenderung kembali ke *WhatsApp* jika *approval flow* terlalu lambat. |
| **Operator Posko** | 4 Jam + Praktik | Salah menekan tombol "Mutasi Keluar" padahal maksudnya barang masuk (karena lelah). |
| **Relawan Mobile** | 30 Menit (On-Boarding UI) | Mengandalkan *Offline Sync* tapi menutup aplikasi secara paksa sebelum *background upload* selesai. |

---

## 10. Final Human Readiness Score

Mengabaikan kualitas kode 100%, inilah penilaian berdasarkan sifat dan psikologi manusia dalam kondisi bencana (*Human Factors*):

- **Technical Readiness:** 95/100 (Sistem kokoh, *realtime* jalan).
- **Operational Readiness:** 80/100 (Proses masih menuntut kedisiplinan *input* tinggi).
- **Human Readiness:** 60/100 (Risiko *human-error* kelelahan sangat tinggi akibat form yang repetitif).
- **Adoption Readiness:** 65/100 (Kompetisi dengan kecepatan grup *WhatsApp* sangat berat).

### Keputusan Kesiapan Manusia:
> **[ CONDITIONAL GO ]**

**Kesimpulan:**
NURISK secara teknis siap terbang, tetapi manusianya akan "*crash*". Sistem membutuhkan intervensi penyederhanaan UI sebelum operasi massal.
**Syarat Peluncuran (Conditional):** Implementasi *Quick-Action Buttons* (Tombol Aksi Cepat 1-Klik), pengurangan *form input* mandatory hingga tersisa 3 kolom saja untuk operasi lapangan, dan pemotongan birokrasi *approval* berlapis di saat status insiden adalah 'Tanggap Darurat'.
