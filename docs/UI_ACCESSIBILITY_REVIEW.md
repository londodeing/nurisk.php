# NURISK Phase 19A — Accessibility Review

Panduan aksesibilitas untuk operator krisis yang kelelahan dan berada di lapangan dengan visibilitas rendah.

## 1. Contrast Ratio
- Penggunaan rasio minimal **4.5:1** (AA Standard).
- Latar belakang merah (`bg-danger`) harus dikawinkan dengan teks putih (`text-white`), tidak boleh dengan teks hitam. Teks abu-abu (`text-muted`) pada *background* putih harus cukup gelap.

## 2. Keyboard Navigation
- Seluruh tabel (Decision Queue) dan form (Mutasi Logistik) harus dapat dinavigasi sepenuhnya dengan tombol `TAB`.
- *Modal* dialog harus secara otomatis memfokuskan kursor pada input teks pertama atau tombol '*Aksi Utama*'.

## 3. Mobile Viewport
- *Meta tag* `<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">` harus diaplikasikan untuk mencegah *zoom-in* paksa di ponsel ketika operator salah menyentuh area ganda.

## 4. Touch Target Size
- Minimum area sentuh adalah **48x48 pixels**. Tombol *Quick Actions* akan menggunakan ukuran `btn-lg` (`padding: 0.5rem 1rem`) dengan margin ekstra (mb-2) agar jari operator yang tertutup sarung tangan tidak meleset mengeklik tombol pembatalan.

## 5. Readability Saat Stress
- **Informasi Agregat Biner:** "ADA" atau "HABIS". Manusia yang panik kesulitan membedakan "15 kg" vs "150 kg" secara sekejap.
- Semua notifikasi darurat (P0) diletakkan di *Top Banner Area*, memaksa keseluruhan konten UI turun ke bawah.
