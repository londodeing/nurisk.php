# Skenario Pengujian Penerimaan Pengguna (UAT) - NURISK Fase 19

Skenario di bawah ini disiapkan untuk digunakan oleh tester (Pimpinan PWNU/PCNU, TRC, Koordinator) guna memberikan lampu hijau sebelum sistem mengudara (Go-Live).

## 1. Modul TRC (Tim Reaksi Cepat)
- **Skenario 1.1:** Relawan TRC membuka halaman di HP dengan koneksi 3G. Halaman termuat dengan tombol aksi cepat memadai.
- **Skenario 1.2:** Relawan memencet "Kirim Assessment" dua kali berturut-turut dengan cepat (Double-tap). *Expected:* Modal hanya terbuka satu kali (*Debounce bekerja*).
- **Skenario 1.3:** Saat mengirim *Sitrep*, relawan mematikan WiFi/Data. *Expected:* Pesan Toast merah muncul: "Koneksi Terputus", form tidak mogok (freeze).

## 2. Modul Posko (Operator & Komandan)
- **Skenario 2.1:** Komandan Posko membiarkan layar dasbor terbuka selama 5 menit. Operator di meja sebelah membuat entri logistik baru. *Expected:* Pada menit ke-5, daftar antrean di layar Komandan terbarui dengan sendirinya (AJAX Polling sukses).
- **Skenario 2.2:** Operator Posko membuka Modal "Penugasan Baru", mengisi nama, lalu menekan tombol "Batal"/menutup modal. Operator kemudian membuka kembali modal tersebut. *Expected:* Semua isian telah kembali kosong (Auto-Reset sukses).

## 3. Modul Koordinator Klaster (Gap Management)
- **Skenario 3.1:** Koordinator Klaster menekan tombol "AI Suggestion" untuk mendistribusikan logistik berlebih. *Expected:* Overlay "Loading" (spinner) global menyelimuti layar, mencegah Koordinator mengklik tombol lain hingga proses kalkulasi usai.

## 4. Modul Eksekutif & Command Center
- **Skenario 4.1 (Approval):** Pimpinan PCNU mengklik "Setujui" pada sebuah surat. *Expected:* Surat seketika melipat dan menghilang dari layar. Angka Lencana (Badge) di pojok kanan atas berkurang 1 tanpa laman memuat-ulang (*Zero-Redirect*).
- **Skenario 4.2 (Command Center Network Drop):** Proyektor menampilkan Command Center. Kabel LAN dicabut selama 2 menit. *Expected:* Di Console, sistem otomatis mencoba menghubungi ulang server dalam kelipatan interval waktu (30d -> 60d -> 120d). Begitu kabel dicolok lagi, sistem pulih seketika tanpa perlu menekan F5.

## Lembar Pengesahan
[ ] Lulus - Tanda Tangan QA / Pimpinan: _______________________
[ ] Gagal - Catatan Defect: ___________________________________
