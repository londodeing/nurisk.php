# UI Design: PWNU Dashboard (Executive Summary)

**Target Pengguna:** PWNU, Pengurus Wilayah.
**Prinsip Desain:** "Rangkuman Level Tinggi & Titik Kritis Wilayah"

## Wireframe Layout

```text
[ Navbar: PWNU Banten ] [ Polling: 30s ] [ Filter: Bulan Ini ] [ User ]

+-----------------------------------------------------------------+
| WIDGET RINGKASAN PROVINSI (Angka Agregat)                       |
| [ Total Relawan: 500 ] [ Total Korban: 1,200 ] [ Kerugian: 5M ] |
+-----------------------------------------------------------------+

+-------------------------------+ +-------------------------------+
| WIDGET: Tren Insiden (Chart)  | | WIDGET: Kebutuhan Bantuan     |
| [ Grafik Garis Naik/Turun ]   | | (Daftar barang paling dicari) |
| Menampilkan frekuensi insiden | | 1. Tenda Pleton               |
| selama 30 hari terakhir.      | | 2. Genset 5000W               |
| (Chart.js Line Chart)         | | 3. Perahu Karet               |
+-------------------------------+ +-------------------------------+

+-----------------------------------------------------------------+
| KABUPATEN KRITIS (Daftar PCNU yang membutuhkan intervensi)      |
| [!] PCNU Lebak     - 5 Insiden Aktif, Logistik Kritis           |
| [!] PCNU Pandeglang - 2 Posko Lumpuh                            |
+-----------------------------------------------------------------+
```

## Fokus Eksekutif
- **Bukan Operasional:** Layar ini sengaja menyembunyikan detail seperti nama relawan atau isi sitrep individual.
- **Identifikasi Titik Lemah:** Fitur "Kabupaten Kritis" adalah sorotan utama agar PWNU dapat menginstruksikan BKO (Bantuan Kendali Operasi) antar PCNU dengan cepat.
