# SDUI Architecture Implementation Gaps

Analisis kesenjangan teknis terperinci mengenai elemen arsitektur SDUI yang tertulis di dokumen rencana namun absen pada kenyataan kode produksi saat ini.

---

## 1. Skema Widget Ditinggalkan
- **Dokumen Rencana:** `m22a_04_bff_contract.md`
- **Kondisi Riil:**
  Aplikasi seluler memiliki koleksi *widget* warisan seperti `WarningBanner`, `WeatherCard`, `CtaLogin`, dan `CtaVolunteer` yang tidak pernah diperbarui ke arsitektur `WidgetFactory`. 
  Layar dashboard baru memintas (*bypass*) komponen-komponen ini sehingga tidak dapat diatur tata letaknya melalui BFF secara dinamis.

---

## 2. Navigasi Bottom Bar Dinamis
- **Dokumen Rencana:** `m22a_05_navigation_contract.md` (Bagian 3)
- **Kondisi Riil:**
  Meskipun `DashboardBffController.php` telah mengirimkan payload navigasi bawah:
  ```json
  "bottom_nav": [
    {"id": "tab_home", "label": "Beranda", "icon": "home", "target_endpoint": "/api/bff/dashboard"},
    {"id": "tab_map", "label": "Peta", "icon": "map", "target_route": "/map"}
  ]
  ```
  Aplikasi Flutter mengabaikan parameter ini sepenuhnya. File router `app_router.dart` masih memuat susunan navigasi statis dan tidak membaca state `bottomNav` dari peladen.

---

## 3. Desentralisasi Aksi (Action Mapping)
- **Dokumen Rencana:** `m22a_01_adr_sdui.md`
- **Kondisi Riil:**
  Pada widget `settings_card.dart`, kita terpaksa menulis percabangan manual untuk tipe aksi `logout`:
  ```dart
  if (type == 'action' && actionId == 'logout') {
     ref.read(authStateProvider.notifier).logout();
  }
  ```
  Ini merupakan bentuk *hardcode* aksi di tingkat komponen. 
  Semestinya, platform memiliki `ActionResolver` terpusat yang menerima registrasi *handler* fungsi secara modular, sehingga komponen visual tidak perlu tahu fungsi internal apa yang sedang dijalankannya.

---

## 4. Hilangnya Modul Tata Kelola (Governance Specification)
- **Dokumen Rencana:** `Governance Dashboard Specification`
- **Kondisi Riil:**
  Tidak ditemukan berkas *controller* khusus BFF di peladen Laravel (`app/Http/Controllers/Api/Bff`) yang melayani dasbor kepemimpinan (*Governance Dashboard*). Akun pimpinan saat ini dipaksa melihat dasbor profil umum.
