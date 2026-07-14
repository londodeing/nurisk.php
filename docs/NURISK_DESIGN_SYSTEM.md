# NURISK Design System

Sistem desain ini adalah panduan referensi tunggal (Single Source of Truth) untuk seluruh komposisi UI dalam sistem **Primitive SDUI** Nurisk.
Alih-alih membuat "Semantic Widget" di Flutter, Backend wajib menyusun layout berdasarkan spesifikasi visual di bawah ini menggunakan komponen primitif (Container, Row, Column, Text, Icon, dll).

---

## 1. Typography

Flutter tidak boleh menebak ukuran huruf. Seluruh properti `style` pada komponen `Text` harus mereferensikan token berikut:

*   **`headline`**: 20px, Bold (FontWeight.w700), untuk judul utama layar atau nama pengguna.
*   **`title`**: 16px, Semi-Bold (FontWeight.w600), untuk judul card/section.
*   **`subtitle`**: 14px, Medium (FontWeight.w500), untuk sub-judul atau peran organisasi.
*   **`body`**: 13px, Normal (FontWeight.w400), untuk teks standar atau deskripsi.
*   **`caption`**: 11px, Normal (FontWeight.w400), warna lebih redup, untuk timestamp atau metadata.
*   **`metric`**: 24px - 28px, Black (FontWeight.w900), untuk angka statistik utama.

---

## 2. Color Tokens

Warna dikirimkan dari Backend menggunakan nama token, bukan Hex Code, agar Flutter bisa mendukung *Dark Mode* otomatis ke depannya.

*   **`primary`**: Hijau Utama (e.g. #2E7D32)
*   **`secondary`**: Abu-abu kebiruan / Accent
*   **`background`**: Abu-abu sangat terang (e.g. #F5F7F5), digunakan pada *Scaffold*
*   **`surface`**: Putih solid (#FFFFFF) untuk latar Card dan Container
*   **`danger`**: Merah (#D32F2F) untuk Mandat, Warning, Error
*   **`danger_light`**: Latar belakang merah pudar untuk Banner peringatan
*   **`text_main`**: Hitam atau Abu-abu sangat gelap
*   **`text_muted`**: Abu-abu (#757575) untuk caption/subtitle

---

## 3. Spacing, Margin & Padding

Semua jarak (margin, padding, spacing antar komponen) menggunakan kelipatan 4:

*   **`4px`**: Jarak mikro (misal: antara nama dan role)
*   **`8px`**: Jarak antar elemen kecil di dalam Row
*   **`12px`**: Spacing ideal untuk Grid Item
*   **`16px`**: Standard margin kiri-kanan layar, Standard padding dalam Card
*   **`24px`**: Jarak antar Section/Card besar

---

## 4. Bentuk (Shape) & Elevation

*   **Border Radius**:
    *   Card Utama / Container: `16px`
    *   Button / Tile: `8px`
    *   Avatar / Icon Circle: `50%` (Bulat sempurna)
*   **Elevation (Shadow)**:
    *   Semua *Surface Card* di atas *Background* memiliki soft shadow: blur `10px`, offset `(0, 4)`, opacity `5%`.

---

## 5. Primitive Template Specifications

Berikut adalah spesifikasi komposisi primitif yang harus dihasilkan Backend untuk membentuk desain UI yang diinginkan.

### A. Template: Identity (Profil Pengguna)
Digunakan di halaman Akun (Paling Atas).
**Komposisi:**
```
Container (padding: 16, margin: 16, radius: 16, bg: surface, shadow: true)
 └── Row
      ├── Container (Bulat, Avatar, radius: 32)
      │    └── Icon (person)
      └── Column (margin_left: 16, cross_axis: start, expanded: true)
           ├── Text (style: headline, text: Nama)
           └── Text (style: subtitle, text: Role • Organisasi, color: text_muted)
```

### B. Template: Summary / Statistics
Digunakan untuk menapilkan metrik berjejer.
**Komposisi:**
```
Container (padding: 16, margin_bottom: 16, radius: 16, bg: surface, shadow: true)
 └── Column (cross_axis: start)
      ├── Text (style: title, text: "Ringkasan")
      └── Row (main_axis: space_evenly, margin_top: 16)
           ├── Column (item 1)
           │    ├── Text (style: metric, text: "11", color: primary)
           │    └── Text (style: caption, text: "Antrean", color: text_muted)
           ├── Column (item 2) ...
           └── Column (item 3) ...
```

### C. Template: Quick Action Grid
Digunakan untuk menu-menu aksi.
**Komposisi:**
```
Container (padding: 16, margin_bottom: 16, radius: 16, bg: surface, shadow: true)
 └── Column (cross_axis: start)
      ├── Text (style: title, text: "Aksi Cepat")
      └── Grid (cross_axis_count: 2, spacing: 12, child_aspect_ratio: 2.5)
           ├── Container (padding: 12, bg: primary_light, radius: 8, onTap: {...})
           │    └── Row
           │         ├── Icon (color: primary, size: 20)
           │         └── Text (style: subtitle, text: "Lapor", margin_left: 8)
           ├── Container (item 2)...
```

### D. Template: List / Task Item
Digunakan di dalam Task, Activity, atau Setting.
**Komposisi:**
```
Container (padding: 16, margin_bottom: 16, radius: 16, bg: surface, shadow: true)
 └── Column (cross_axis: start)
      ├── Text (style: title, text: "Tugas")
      └── ListView (shrink: true, padding: 0)
           ├── Row (margin_bottom: 12, onTap: {...})
           │    ├── Icon / Avatar
           │    ├── Column (margin_left: 12, expanded: true)
           │    │    ├── Text (style: subtitle, text: "Tugas A")
           │    │    └── Text (style: caption, text: "Batas Waktu: ...")
           │    └── Icon (chevron_right)
```

### E. Template: Header Banner (Mandat Aktif)
**Komposisi:**
```
Container (padding: 12, margin_bottom: 16, bg: danger_light, radius: 12)
 └── Row
      ├── Icon (shield, color: danger)
      └── Column (margin_left: 12, expanded: true)
           ├── Text (style: subtitle, text: "MANDAT PWNU", color: danger)
           └── Text (style: caption, text: "Wewenang Komando", color: danger)
```
