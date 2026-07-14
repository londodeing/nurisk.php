# NURISK MOBILE — DESIGN SYSTEM
## Document 12: Design System Blueprint
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Platform-Wide

---

## 1. BRAND IDENTITY

NURISK adalah platform ERP darurat untuk NU Peduli. Design system mencerminkan:
- **Kepercayaan & Ketegasan**: Warna deep teal-hijau, bukan warna plasir
- **Kejelasan**: Hierarki informasi yang sangat jelas untuk kondisi lapangan
- **Aksesibilitas**: Kontras tinggi, font yang mudah dibaca di bawah sinar matahari
- **Kesiapsiagaan**: Komponen darurat (alert, emergency) selalu menonjol

---

## 2. COLOR PALETTE

### 2.1 Brand Colors (Primary)

| Token | HEX | RGB | Penggunaan |
|-------|-----|-----|-----------|
| `color.brand.primary` | `#1B5E3B` | 27,94,59 | CTA utama, active nav item |
| `color.brand.primary.light` | `#2E7D52` | 46,125,82 | Hover state, secondary button |
| `color.brand.primary.dark` | `#0D3D20` | 13,61,32 | Pressed state |
| `color.brand.accent` | `#4CAF80` | 76,175,128 | Highlight, badge sukses |
| `color.brand.accent.light` | `#A5D6BE` | 165,214,190 | Background chip, tag |

### 2.2 Status Colors

| Token | HEX | Penggunaan |
|-------|-----|-----------|
| `color.status.success` | `#2E7D32` | Sukses, terverifikasi |
| `color.status.warning` | `#E65100` | Peringatan, menunggu |
| `color.status.error` | `#B71C1C` | Error, ditolak |
| `color.status.info` | `#1565C0` | Informasi, proses |
| `color.status.neutral` | `#546E7A` | Status netral |

### 2.3 Emergency Colors (Khusus Komponen Darurat)

| Token | HEX | Penggunaan |
|-------|-----|-----------|
| `color.emergency.primary` | `#D32F2F` | Emergency override button |
| `color.emergency.background` | `#FFEBEE` | Alert kritikal background |
| `color.emergency.text` | `#B71C1C` | Teks emergency |

### 2.4 Neutral Colors (Surfaces)

| Token | HEX | Penggunaan |
|-------|-----|-----------|
| `color.surface.background` | `#F5F7F5` | Background halaman |
| `color.surface.card` | `#FFFFFF` | Card background |
| `color.surface.elevated` | `#FAFAFA` | Sheet background |
| `color.surface.divider` | `#E0E0E0` | Divider, border |
| `color.text.primary` | `#1A2421` | Teks utama |
| `color.text.secondary` | `#546E7A` | Teks sub, label |
| `color.text.disabled` | `#90A4AE` | Teks non-aktif |
| `color.text.on.primary` | `#FFFFFF` | Teks di atas brand primary |

---

## 3. TYPOGRAPHY

### 3.1 Font Family

**Primary Font**: **Inter** (Google Fonts)
- Excellent readability di ukuran kecil
- Mendukung Latin extended (aksen Indonesia)
- Available via `google_fonts` package

**Monospace Font**: **JetBrains Mono** (untuk kode, ID unik)

### 3.2 Type Scale

| Token | Font | Size | Weight | Line Height | Penggunaan |
|-------|------|------|--------|-------------|-----------|
| `text.display.large` | Inter | 32sp | 700 Bold | 40sp | Headline splash, error |
| `text.headline.large` | Inter | 24sp | 700 Bold | 32sp | Page title |
| `text.headline.medium` | Inter | 20sp | 600 SemiBold | 28sp | Section header |
| `text.headline.small` | Inter | 18sp | 600 SemiBold | 24sp | Card title |
| `text.title.large` | Inter | 16sp | 600 SemiBold | 22sp | List item title |
| `text.title.medium` | Inter | 14sp | 600 SemiBold | 20sp | Button, label |
| `text.body.large` | Inter | 16sp | 400 Regular | 24sp | Body teks utama |
| `text.body.medium` | Inter | 14sp | 400 Regular | 20sp | Body sekunder |
| `text.body.small` | Inter | 12sp | 400 Regular | 18sp | Helper text |
| `text.label.large` | Inter | 14sp | 500 Medium | 20sp | Form label |
| `text.label.small` | Inter | 11sp | 500 Medium | 16sp | Badge, chip |
| `text.mono` | JetBrains Mono | 13sp | 400 Regular | 20sp | ID, kode |

---

## 4. SPACING SYSTEM

Berbasis grid 4dp:

| Token | Value | Penggunaan |
|-------|-------|-----------|
| `space.xs` | 4dp | Jarak antar icon dan teks |
| `space.sm` | 8dp | Padding dalam chip |
| `space.md` | 12dp | Padding kecil |
| `space.lg` | 16dp | Padding standar (default) |
| `space.xl` | 24dp | Padding section |
| `space.2xl` | 32dp | Jarak antar section besar |
| `space.3xl` | 48dp | Padding halaman (bottom safe area) |

---

## 5. BORDER RADIUS

| Token | Value | Penggunaan |
|-------|-------|-----------|
| `radius.none` | 0 | Divider, full-bleed |
| `radius.sm` | 4dp | Tag, badge kecil |
| `radius.md` | 8dp | Button, input field |
| `radius.lg` | 12dp | Card |
| `radius.xl` | 16dp | Bottom sheet, dialog |
| `radius.2xl` | 24dp | FAB, pill button |
| `radius.full` | 999dp | Avatar, circular badge |

---

## 6. ELEVATION (SHADOWS)

| Token | Value | Penggunaan |
|-------|-------|-----------|
| `elevation.none` | 0 | Flat surface |
| `elevation.low` | 1dp | Subtle card |
| `elevation.medium` | 4dp | Card utama, app bar |
| `elevation.high` | 8dp | Bottom sheet, dropdown |
| `elevation.overlay` | 16dp | Dialog, modal |
| `elevation.floating` | 24dp | FAB |

---

## 7. ICON SYSTEM

**Icon Package**: `Material Icons` (built-in Flutter) + `Phosphor Icons` untuk ikon khusus

**Ukuran Icon**:
| Context | Size |
|---------|------|
| Bottom navigation | 24dp |
| List item leading | 20dp |
| Button dengan icon | 18dp |
| Badge/chip | 14dp |

**Icon Semantics untuk NURISK**:
| Konsep | Icon |
|--------|------|
| Insiden / Bencana | `warning_amber` |
| Posko | `home_work` |
| Relawan | `people` |
| Governance | `account_balance` |
| Approval | `check_circle` |
| Rejection | `cancel` |
| Delegasi | `swap_horiz` |
| Emergency | `crisis_alert` |
| Surat | `description` |
| Media | `photo_camera` |
| Sync | `sync` |
| GPS | `location_on` |
| Wilayah | `map` |

---

## 8. BUTTON SYSTEM

### 8.1 Button Types

| Type | Style | Penggunaan |
|------|-------|-----------|
| **Primary** | Filled, brand primary | Aksi utama (Simpan, Kirim) |
| **Secondary** | Outlined, brand primary | Aksi sekunder (Batal, Kembali) |
| **Danger** | Filled, error red | Delete, Tolak |
| **Ghost** | Text only | Aksi tersier (Lupa Password) |
| **Emergency** | Filled, emergency red + border | Emergency Override |
| **Icon Button** | Icon only | Toolbar action |

### 8.2 Button Sizes

| Size | Height | Padding H | Font |
|------|--------|-----------|------|
| Large | 52dp | 24dp | 16sp SemiBold |
| Medium | 44dp | 20dp | 14sp SemiBold |
| Small | 36dp | 16dp | 12sp SemiBold |

### 8.3 Button States
| State | Visual |
|-------|--------|
| Default | Normal |
| Hover | +8% lightness |
| Pressed | +16% darkness + scale 0.98 |
| Loading | Spinner menggantikan label |
| Disabled | Opacity 0.4, no interaction |

---

## 9. CARD SYSTEM

### 9.1 Card Types

**Standard Card** (List item, Info):
```
╔═══════════════════════════════╗  radius: 12dp
║ [Icon/Avatar]  [Title]        ║  padding: 16dp
║               [Subtitle]      ║  elevation: 1dp
║ [Metadata] [Badge] [Action]   ║
╚═══════════════════════════════╝
```

**Alert Card** (Warning, Info):
```
╔═══════════════════════════════╗  radius: 8dp
║ [!] [Title]                   ║  left border: 4dp brand color
║     [Body text...]            ║  background: tinted
╚═══════════════════════════════╝
```

**Stat Card** (Dashboard widget):
```
╔═══════════════════════════════╗  radius: 12dp
║ [Angka besar]                 ║  padding: 20dp
║ [Label]                       ║  elevation: 2dp
║ [Trend indicator]             ║
╚═══════════════════════════════╝
```

---

## 10. BOTTOM SHEET

**Standard Bottom Sheet** (Info, Detail):
- Drag handle di atas (lebar 40dp, tebal 4dp, radius 2dp)
- Border radius top: 24dp
- Max height: 85% screen height
- Tidak boleh dismiss dengan swipe jika ada input yang belum disimpan

**Confirmation Sheet** (Approve/Reject):
```
╔═══════════════════════════════╗
║  ▬▬▬▬▬ (drag handle)          ║
║                                ║
║  Judul Konfirmasi              ║
║  Deskripsi aksi...             ║
║                                ║
║  [Field input opsional]        ║
║                                ║
║  [Tombol Utama]                ║
║  [Tombol Sekunder / Batal]     ║
╚═══════════════════════════════╝
```

---

## 11. BOTTOM NAVIGATION

**Max Items**: 5  
**Style**: Material 3 NavigationBar  
**Active Indicator**: Filled pill di atas icon

**Behaviour**:
- Tab tap → navigate ke root route tab tersebut
- Tab long-press → scroll to top jika sudah di route tersebut
- Badge merah (angka) untuk notifikasi/inbox count
- Badge titik untuk update tanpa angka

---

## 12. DIALOG SYSTEM

### 12.1 Alert Dialog
```
╔═════════════════════════════╗
║ [Icon] Judul               ║
╠═════════════════════════════╣
║ Teks penjelasan yang cukup  ║
║ singkat dan jelas.          ║
╠═════════════════════════════╣
║        [Batal]  [Konfirmasi]║
╚═════════════════════════════╝
```

### 12.2 Destructive Dialog
```
╔═════════════════════════════╗
║ 🔴 Hapus Data              ║
╠═════════════════════════════╣
║ Data yang dihapus tidak     ║
║ dapat dikembalikan.         ║
╠═════════════════════════════╣
║        [Batal]  [Hapus]ᵣₑd ║
╚═════════════════════════════╝
```

---

## 13. LOADING STATE

| Context | Loading Indicator |
|---------|-------------------|
| Full screen | Centered circular progress + logo |
| List loading | Skeleton shimmer (setiap list item) |
| Button loading | CircularProgressIndicator kecil di button |
| Inline | Circular progress 16dp |
| Image loading | Shimmer rectangle sesuai ukuran image |

---

## 14. ERROR STATE

### 14.1 Full Screen Error
```
╔════════════════════════════════╗
║                                ║
║        [Ilustrasi error]       ║
║                                ║
║  Terjadi Kesalahan             ║
║  [Pesan error yang manusiawi]  ║
║                                ║
║      [Coba Lagi]               ║
╚════════════════════════════════╝
```

**Ilustrasi berdasarkan tipe error**:
- Network error: gambar kabel putus
- Server error: gambar server dengan tanda seru
- 404: gambar peta kosong
- 403: gambar gembok

### 14.2 Inline Error
- Form field: teks merah di bawah field dengan icon `!`
- Snackbar: di bagian bawah untuk aksi yang gagal

---

## 15. EMPTY STATE

```
╔════════════════════════════════╗
║                                ║
║       [Ilustrasi kosong]       ║
║                                ║
║  Belum ada [Entitas]           ║
║  Deskripsi singkat apa yang    ║
║  harus dilakukan.              ║
║                                ║
║       [Tombol Aksi]            ║
╚════════════════════════════════╝
```

---

## 16. SUCCESS STATE

```
╔════════════════════════════════╗
║                                ║
║         ✅ (animasi)           ║
║                                ║
║  [Judul Sukses]                ║
║  [Deskripsi singkat]           ║
║                                ║
║  [Tombol Lanjutkan]            ║
╚════════════════════════════════╝
```

Atau sebagai Snackbar ringan untuk aksi kecil:
```
╔════════════════════════════════╗
║ ✅ Laporan berhasil dikirim    ║
╚════════════════════════════════╝
```

---

## 17. PERMISSION DENIED STATE

```
╔════════════════════════════════╗
║         🔒                     ║
║                                ║
║  Akses Dibatasi                ║
║  Fitur ini memerlukan          ║
║  kewenangan yang tidak Anda    ║
║  miliki saat ini.              ║
║                                ║
║  Posisi aktif: [Jabatan]       ║
║  Node: [Nama Node]             ║
║                                ║
║  [← Kembali]  [Ganti Posisi]  ║
╚════════════════════════════════╝
```

---

## 18. NETWORK LOST STATE

**Banner** (muncul otomatis, tidak perlu full-screen):
```
╔════════════════════════════════╗
║ 🔴 Tidak ada koneksi internet  ║
╚════════════════════════════════╝
```

**Jika action yang diminta wajib online**:
```
╔════════════════════════════════╗
║ 📶 Koneksi Diperlukan          ║
║ Aksi ini memerlukan koneksi    ║
║ internet. Data Anda akan       ║
║ tersimpan dan dikirim saat     ║
║ koneksi tersedia.              ║
║                                ║
║        [Oke, Mengerti]         ║
╚════════════════════════════════╝
```

---

## 19. MAINTENANCE MODE STATE

```
╔════════════════════════════════╗
║         🛠️                     ║
║                                ║
║  Sedang Dalam Pemeliharaan     ║
║  Sistem NURISK sedang          ║
║  diperbarui. Silakan coba      ║
║  kembali dalam beberapa saat.  ║
║                                ║
║  [↻ Cek Kembali]               ║
╚════════════════════════════════╝
```

---

*Document Status: APPROVED FOR SPRINT F1 — Spesifikasi warna dan font final, perlu review ulang setelah mockup dibuat*
