# UI Design: Operator Posko Dashboard (Data Entry Layout)

**Target Pengguna:** Relawan Administrasi Posko.
**Tujuan:** Layar kasir (*Point of Sale*) versi bencana. Kecepatan *Data Entry*.

## Komponen Layar (Grid Input)

```text
[ Navbar: OPERATOR POSKO ] [ Shift: 08:00 - 16:00 ]

+-----------------------------------------------------------+
| TUGAS INPUT MENUNGGU (SLA: < 1 Jam)                       |
| [ ] 3 Berita Acara Penerimaan Barang belum didigitalkan.  |
| [ ] Draf Sitrep Pagi belum di-submit.                     |
+-----------------------------------------------------------+

+---------------------------+-------------------------------+
| QUICK INPUT (1 KLIK)      | DRAFT & RECENT UPLOADS        |
| [ + INPUT LOGISTIK ]      | 09:12 - BAST Masuk (Beras)    |
| [ + CATAT RELAWAN ]       | 08:30 - Sitrep Pagi (Draft)   |
| [ + BUAT DRAF SURAT ]     | 08:00 - Absen Relawan Pagi    |
| [ + UPDATE KORBAN ]       |                               |
| [ + KIRIM SITREP ]        |                               |
+---------------------------+-------------------------------+
```

## Human Factors
- **Fokus Tunggal:** Tidak ada *Decision Queue* atau Peta Kritis. Operator hanya fokus melihat "Apa yang belum saya ketik?".
- **Draf Otomatis:** Semua input tersimpan ke *local storage browser* per ketikan (*keystroke*), meminimalisasi kehilangan data saat PC posko mati lampu.
