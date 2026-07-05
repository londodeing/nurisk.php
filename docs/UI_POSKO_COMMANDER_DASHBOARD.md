# UI Design: Komandan Posko Dashboard

**Target Pengguna:** Manajer Posko di lapangan.
**Fokus:** Penyelesaian Masalah Kritis (*Bottleneck*).

## Komponen Layar

```text
[ Navbar: POSKO ALPHA COMMANDER ] [ Freshness: < 1m ]

+-----------------------------------------------------------+
| CRITICAL ALERTS (Jika Ada)                                |
| [!] Stok Air Bersih Habis dalam 2 Jam.                    |
+-----------------------------------------------------------+

+-----------------------------------------+-----------------+
| DECISION QUEUE (Maks 5 Item)            | RESOURCE STATUS |
| 1. 5 Relawan menganggur > 2 jam [TUGASKAN] | Relawan: 15/20  |
| 2. Sitrep Shift Pagi terlambat [TAGIH]  | Logistik: AMAN  |
| 3. Evakuasi Medis Terhambat [ESKALASI]  | Kendaraan: 1/1  |
+-----------------------------------------+-----------------+

+-----------------------------------------------------------+
| ESCALATION & PENDING QUEUE                                |
| Menunggu izin operasional genset dari PCNU: 4 Jam.        |
+-----------------------------------------------------------+
```

## Pertanyaan yang Dijawab
1. **Apa masalah terbesar posko saat ini?** (Via *Critical Alerts*)
2. **Apa yang harus diputuskan?** (Via *Decision Queue*)
3. **Siapa yang butuh bantuan?** (Jika KPI Logistik drop)
4. **Apa yang harus dieskalasikan?** (Menekan tombol Eskalasi di *Queue*).
