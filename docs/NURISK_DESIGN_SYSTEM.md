# NURISK Design System (Bootstrap 5 Extended)

Sistem Desain ini menjamin konsistensi *brand* dan komunikasi darurat selama operasi NURISK.

## 1. Typography (Inter / Roboto)
- **H1 (Page Title):** `fs-3 fw-bold` (28px) - Digunakan untuk nama halaman.
- **H2 (Section/Widget Title):** `fs-5 fw-semibold` (20px) - Digunakan pada *Card Header*.
- **H3 (Label/Key):** `fs-6 fw-bold text-uppercase text-muted` (12px) - Label metrik.
- **Body:** `fs-6` (16px) - Digunakan untuk input *form* (menghindari auto-zoom iOS).
- **Caption:** `small text-muted` (14px) - Petunjuk pengisian.

## 2. Spacing System
Mengandalkan utilitas Bootstrap (0-5).
- `xs` (0.25rem) / `s-1`
- `sm` (0.5rem) / `s-2`
- `md` (1rem) / `s-3` (Standar jarak antar *Widget*)
- `lg` (1.5rem) / `s-4`
- `xl` (3rem) / `s-5` (Jarak section utama)

## 3. Color System
- **Success:** `#198754` (Hijau Bootstrap) -> Aman, Berhasil, Stok Penuh.
- **Warning:** `#ffc107` (Kuning Bootstrap) -> Siaga, Perlu Perhatian, Kuota Menipis.
- **Danger:** `#dc3545` (Merah Bootstrap) -> Darurat, Kritis, Stok Habis, Gagal.
- **Info:** `#0dcaf0` (Cyan Bootstrap) -> Informasi sistem, Sinkronisasi Berjalan.
- **Neutral:** `#6c757d` (Abu-abu) -> Idle, Draft, Selesai.

## 4. Status Colors (Contextual Semantics)
Penerapan spesifik pada operasi NURISK:
- **Insiden Aktif:** `Danger` (Berkedip/Pulse)
- **Posko Aktif:** `Success`
- **Logistik Kritis:** `Warning` (Sisa < 30%), `Danger` (Habis)
- **Eskalasi:** `Warning` (Menunggu), `Success` (Disetujui)
- **Surat Pending:** `Secondary / Neutral`
