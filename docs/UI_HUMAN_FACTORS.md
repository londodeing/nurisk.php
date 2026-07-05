# NURISK Phase 19 — Human Factors Review

Dokumen ini menganalisis tata letak UI yang disarankan berdasarkan batasan kognitif dan operasional.

## Analisis Jumlah Klik (Click Depth)
- **Tujuan UI:** Mengurangi kedalaman struktur. Fitur utama (Posko: Buat Sitrep, Input Logistik) tidak boleh berada di balik lebih dari 1 menu navigasi.
- **Implementasi:** Penggunaan **Quick Actions** di halaman depan menjamin operasi inti dapat diselesaikan dalam 2 klik (Klik Menu -> Submit Modal).

## Beban Kognitif (Cognitive Load)
- Dashboard Posko dihindarkan dari grafik statistik kompleks, karena otak operator sedang dipenuhi variabel lapangan. Sebagai gantinya, data ditampilkan sebagai "Angka Absolut" dan "Kondisi Biner" (Habis/Ada, Idle/Tugas).

## Kemungkinan Kesalahan Operator
- **Fatigue Error:** Di jam 2 pagi, operator cenderung salah klik *dropdown*.
- **Mitigasi:** *Warning modal* (konfirmasi) ganda untuk aksi destruktif. Penggunaan blok warna kontras untuk aksi sukses (hijau) dan peringatan krisis (merah).

## Usability Saat Bencana & Kelelahan
- Layar didesain responsif, mengasumsikan pengguna membuka aplikasi di *smartphone* atau tablet yang retak, dengan visibilitas luar ruang (*sun glare*). Penggunaan *Card* berukuran besar dengan batas (border) tebal, serta dilarang menggunakan *font* kurang dari `14px` (ukuran teks minimum).
