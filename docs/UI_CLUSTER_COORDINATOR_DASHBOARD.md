# UI Design: Koordinator Klaster Dashboard

**Target Pengguna:** PIC Sektoral (Misal: Ketua Klaster Logistik, Kesehatan, atau WASH).
**Tujuan:** Mengawasi *Gap Analysis* di bidang spesifiknya secara eksklusif.

## Komponen Layar

```text
[ Navbar: KLASTER KESEHATAN ] [ Kinerja: 78% Terpenuhi ]

+-----------------------------------------------------------+
| SECTORAL DECISION QUEUE                                   |
| 1. Wabah diare meningkat di Posko C (Status: MERAH)       |
| 2. Kekurangan 5 Tenaga Medis di Posko A                   |
+-----------------------------------------------------------+

+-----------------------------------------+-----------------+
| GAP ANALYSIS (Kebutuhan vs Stok)        | PENDING ACTIONS |
| Oksigen:  [========  ] (80%)            | 2 Permintaan    |
| Obat Diare: [===     ] (30%) - KRITIS   | Mobilisasi Obat |
| Perban:   [========= ] (90%)            | Menunggu        |
+-----------------------------------------+-----------------+
```

## Human Factors
- **Fokus Menyempit:** Jika ia Koordinator Logistik, layar ini akan secara absolut menyembunyikan status relawan medis atau pergerakan surat. Hanya memunculkan rasio barang vs pengungsi.
- **Aksi Cepat:** Menyetujui transfer barang antar-posko (Kanibalisasi sumber daya) via blok *Pending Actions*.
