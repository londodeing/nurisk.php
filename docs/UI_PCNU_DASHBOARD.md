# UI Design: PCNU Dashboard (Managerial View)

**Target Pengguna:** Ketua PCNU, Koordinator Cabang.
**Prinsip Desain:** "Helikopter View & Pengendali Arus Relawan"

## Wireframe Layout

```text
[ Navbar: Logo ] [ Cabang Kab. Banten ] [ Sync Indicator ] [ User ]

+-------------------------------------------------------------+
| QUICK ACTIONS                                               |
| [ Buka Pleno ] [ Terbitkan Surat ] [ Minta BKO Relawan ]    |
+-------------------------------------------------------------+

+-------------------------------------------------------------+
| WIDGET: Status Posko Aju Aktif (Row Cards)                  |
| [ Posko A - Aman ] [ Posko B - Kurang Relawan ] [ Posko C ] |
+-------------------------------------------------------------+

+---------------------------+ +-------------------------------+
| WIDGET: Logistik Cabang   | | WIDGET: Eskalasi & Pleno      |
| Stok Tersedia di Gudang   | | Menunggu Persetujuan: 2       |
| Total Relawan: 120        | | - Eskalasi Dana Darurat       |
| Di Lapangan: 85           | | - Penarikan Tim Medis         |
+---------------------------+ +-------------------------------+

+-------------------------------------------------------------+
| RECENT SITREPS (Konsolidasi dari semua Posko)               |
| [12:00] Posko A: "Air Sungai Meluap"                        |
| [11:45] Posko B: "Butuh tambahan tenda 5 unit"              |
+-------------------------------------------------------------+
```

## Fokus Manajerial
- **Monitoring Multi Posko:** Menampilkan status *health* setiap posko (Apakah posko itu lumpuh atau berjalan?).
- **Eskalasi Queue:** PCNU bertugas mengambil keputusan atas permintaan yang terlalu berat bagi Posko. Kotak ini diletakkan di *above the fold* (area pandang pertama).
