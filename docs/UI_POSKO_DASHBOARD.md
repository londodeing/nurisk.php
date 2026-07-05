# UI Design: Posko Dashboard (Operational Board)

**Target Pengguna:** Operator Posko (Bekerja di bawah tenda, layar laptop/tablet, situasi panik).
**Prinsip Desain:** "Satu Layar untuk Seluruh Operasi"

## Wireframe Layout (Bootstrap Grid)

```text
[ Navbar: Logo ] [ Clock ] [ Posko Induk Banten ] [ User ]

[ ALERT BANNER (merah): "Peringatan Hujan Deras dalam 2 Jam" ]

+-------------------------------------------------------------+
| QUICK ACTIONS (Button Group Raksasa)                        |
| [ + TUGAS BARU ] [ + SITREP ] [ + BARANG KELUAR ] [ PANIK ] |
+-------------------------------------------------------------+

+------------------------+ +----------------------------------+
| WIDGET: Relawan (col-4)| | WIDGET: Stok Kritis (col-4)      |
| Aktif: 25   Idle: 5    | | - Beras (Sisa 15 Kg) [MUTASI]    |
| Tugas: Evakuasi (10)   | | - Air Mineral (Habis)            |
+------------------------+ +----------------------------------+
| WIDGET: Sitrep (col-4) |
| Terakhir: 10 menit lalu|
| "Jalan putus di Desa X"|
+------------------------+

+-------------------------------------------------------------+
| DECISION QUEUE (Tugas yang Belum Selesai / Overdue)         |
| 1. [Evakuasi Lansia] - @TimAlpha - Telat 2 Jam -> [TINDAK!] |
| 2. [Ambil Sembako]   - @TimBravo - Sedang Jalan             |
+-------------------------------------------------------------+
```

## Elemen Spesifik
- **Decision Queue:** Tabel list tugas yang disortir berdasarkan "Waktu Lewat Tenggat" paling parah, sehingga operator tidak perlu mencari tugas mana yang bermasalah.
- **Alert:** Menggunakan kelas `alert-danger alert-dismissible` untuk informasi darurat (krisis cuaca/instruksi PCNU).
- **Quick Action:** Tombol besar (`btn-lg w-100`) dengan ikon tebal agar mudah diklik meski menggunakan mousepad yang kotor.
