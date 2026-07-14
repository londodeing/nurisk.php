# COP (Common Operational Picture) Compliance Audit

Audit mendalam terhadap Peta Operasional, Kontrol Layer, Legenda, Popup, dan Timeline.

---

## 1. Peta Operasional (Map & Layers)
- **File:** [map_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/map/presentation/screens/map_screen.dart)
- **Pelanggaran Arsitektur:**
  Aplikasi seluler secara statis merender data spasial dengan mengasumsikan tipe-tipe layer tertentu (seperti bencana alam, status jalan, posko). Sesuai dokumen `COP Architecture` dan `Layer Catalog`, peladen BFF harus mendefinisikan koleksi layer yang aktif:
  ```json
  "layers": [
    {
      "id": "layer_posko_nu",
      "name": "Posko NURISK",
      "type": "geojson",
      "url": "/api/map/geojson/posko",
      "visible": true,
      "style": { "icon": "home_camp", "color": "#10B981" }
    }
  ]
  ```
  Saat ini, styling layer di-hardcode di sisi Flutter.

---

## 2. Legend & Layer Control
- **File:** [layer_control_bottom_sheet.dart](file:///home/londo/nurisk/mobile/app/lib/features/map/presentation/widgets/layer_control_bottom_sheet.dart)
- **Pelanggaran Arsitektur:**
  Pilihan layer untuk diaktifkan/dinonaktifkan (seperti "Kawasan Rawan Bencana", "Posko Terdekat") ditulis permanen (*hardcoded*) di widget ini. Legenda peta juga di-render menggunakan switch-case lokal di klien. Semuanya harus dibentuk dinamis dari konfigurasi BFF.

---

## 3. Popup Detail Kejadian (Popup Specification)
- **File:** [spatial_filter_bottom_sheet.dart](file:///home/londo/nurisk/mobile/app/lib/features/map/presentation/widgets/spatial_filter_bottom_sheet.dart)
- **Pelanggaran Arsitektur:**
  Ketika pin bencana pada peta diketuk, aplikasi menampilkan detail laporan menggunakan tata letak kaku (`SpatialFilterBottomSheet`). Sesuai `Popup Specification`, informasi yang muncul pada popup harus dikirimkan dalam format pasangan kunci-nilai (*key-value pairs*) dinamis atau kode HTML sederhana yang siap dirender oleh klien, guna menghindari modifikasi Flutter saat format detail kejadian berubah.
