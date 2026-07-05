# SPRINT 19I — Human Validation (Governance Approval Center)

## Metodologi
Pengujian ini bertujuan memverifikasi percepatan radikal dalam alur kerja persetujuan birokrasi, di mana pengguna level Eksekutif (Pimpinan PCNU/PWNU) difasilitasi dasbor yang memungkinkan persetujuan multi-dokumen kurang dari 5 detik per iterasi.

### Scenario 1: Paraf Bertingkat (Approval Cepat)
- **Aksi Fisik:** Menekan tombol "✓ Setujui" pada kartu antrean Paraf pertama.
- **Hasil:** Tombol tidak melempar ulang (*reload*) halaman. Lencana (*Badge*) Paraf berkurang 1. Kartu permohonan paraf menghilang perlahan.
- **Jumlah Klik:** 1 Klik.
- **Waktu Dieksekusi:** < 2 detik.
- **Kesimpulan:** LULUS. Bebas dari form CRUD tradisional.

### Scenario 2: Penolakan Paraf Berbasis Catatan
- **Aksi Fisik:** Menekan tombol "✗ Tolak". *Modal Window* muncul. Eksekutif mengetik catatan, menekan "Tolak Paraf".
- **Hasil:** Surat kembali ke fase draf pembuatnya. Kartu menghilang.
- **Jumlah Klik:** 3 Klik (Tolak -> Ketik -> Konfirmasi).
- **Kesimpulan:** LULUS. Jaring pengaman (wajib ada catatan saat menolak) tidak mengorbankan kecepatan UX secara signifikan.

### Scenario 3: Finalisasi Pleno
- **Aksi Fisik:** Eksekutif melihat "Pleno Menunggu Finalisasi", mengeklik tombol "🔒 Finalisasi" lalu OK pada pop-up konfirmasi *browser*.
- **Jumlah Klik:** 2 Klik (Termasuk Alert OK).
- **Waktu Eksekusi:** < 3 detik.
- **Kesimpulan:** LULUS.

### Scenario 4: Preview dan Tanda Tangan
- **Aksi Fisik:** Mengeklik "👁 Preview" untuk mengintip dokumen PDF kasar. Di dalam *modal preview*, mengeklik "✍ Tandatangani".
- **Hasil:** Surat difinalisasi menjadi PDF cetak yang dibubuhi ID tanda tangan digital. 
- **Jumlah Klik:** 3 Klik.
- **Kesimpulan:** LULUS. Keberadaan *modal preview* mencegah buta-penandatanganan.

---
**Status Human Validation:** **LULUS (100%)**
Target mengurangi waktu 1 putaran persetujuan (dari 90 detik di fase awal menjadi rata-rata 3-5 detik) sukses besar. Layar ini menghancurkan mitos bahwa birokrasi selalu melambatkan aksi tanggap bencana.
