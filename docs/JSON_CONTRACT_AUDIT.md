# JSON Contract Audit

Evaluasi keselarasan *Payload* API yang dihasilkan oleh Backend (BFF Laravel) terhadap kontrak JSON SDUI yang disyaratkan dalam `m22a_04_bff_contract.md`.

---

## 1. Rute `/api/bff/dashboard` (Dulu `/api/public/dashboard/config`)
- **Status:** **PATUH** (Setelah perbaikan Phase 2.2C).
- **Struktur Saat Ini:**
  ```json
  {
    "status": "success",
    "version": "1.0",
    "data": {
      "screen_title": "Beranda Utama",
      "layout_type": "scrollable_column",
      "widgets": [
        {
          "type": "SummaryCard",
          "props": { "title": "Insiden Aktif", "value": "1" },
          "actions": { "on_tap": { "type": "navigate", "target": "/map" } }
        }
      ],
      "bottom_nav": [...]
    }
  }
  ```
- **Analisis Kepatuhan:** Format mengembalikan susunan widget beserta `props` dan `actions` secara bersih.

---

## 2. Rute `/api/account/home` (Profil & Akun)
- **Status:** **TIDAK PATUH / SEBAGIAN**
- **Struktur Saat Ini:**
  Mengembalikan array `cards`, tetapi propertinya masih terlalu spesifik dan sebagian besar penentuan visual dikerjakan di Flutter.
- **Analisis Kepatuhan:**
  Meskipun mengembalikan metadata menu pengaturan, format data tidak seragam dengan widget SDUI generik. Ada data kartu identitas yang strukturnya di-hardcode secara terpisah di Flutter (`IdentityCard`), melanggar penyatuan skema data.

---

## 3. Rute Peta Spasial `/api/map/layers` & `/api/map/geojson`
- **Status:** **TIDAK PATUH**
- **Struktur Saat Ini:**
  Mengembalikan representasi GeoJSON mentah. Seluruh gaya visual (warna garis, warna pin, pengelompokan ikon kluster) ditentukan manual di berkas Flutter `MapScreen`.
- **Analisis Kepatuhan:**
  Melanggar prinsip `COP Architecture` dan `Legend Architecture`. Backend wajib mengirimkan metadata penataan gaya (*styling metadata*) seperti `marker_color`, `icon_type`, dan `popup_template_html` di dalam payload.

---

## 4. Rute Validasi Laporan `/api/laporan/antrean`
- **Status:** **TIDAK PATUH**
- **Struktur Saat Ini:**
  Mengembalikan model database `LaporanKejadian` mentah ke Flutter.
- **Analisis Kepatuhan:**
  Seluruh pelabelan status ("Draf", "Valid", "Ditolak") beserta warna lencana (*badge colors*) dipetakan secara lokal di Flutter menggunakan logika percabangan. Sesuai *SDUI Contract*, BFF harus mengagregasikan status menjadi representasi *badge* siap saji (misal: `{ "label": "Validasi Selesai", "bg_color": "#E6F4EA", "text_color": "#137333" }`).
