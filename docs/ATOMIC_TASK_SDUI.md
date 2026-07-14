# Atomic Tasks: SDUI Platform Compliance

Daftar tugas rinci (*atomic tasks*) untuk membersihkan seluruh sisa ketidakpatuhan arsitektur SDUI di NURISK.

---

## 1. Modul Navigasi Utama (Bottom Bar)
- `[ ]` **BFF (Laravel):** Pastikan endpoint `/api/bff/dashboard` merespons dengan array `bottom_nav` lengkap di setiap mandate login.
- `[ ]` **Klien (Flutter):** Di `app_router.dart`, ubah shell route agar membaca konfigurasi tab dinamis.
- `[ ]` **Klien (Flutter):** Implementasi `DynamicBottomNav` widget untuk menggantikan `PublicBottomNav` statis.

## 2. Modul Peta (COP)
- `[ ]` **BFF (Laravel):** Ubah model response `/api/map/layers` agar menyertakan objek `style` (hex color, icon glyph string).
- `[ ]` **Klien (Flutter):** Hapus pemetaan warna marker lokal di `MapScreen.dart`. Gunakan properti hex color dari API.
- `[ ]` **Klien (Flutter):** Refactor legend sheet agar dirender berdasarkan array `legend` dinamis yang dikirim backend.

## 3. Modul Formulir (Form SDUI)
- `[ ]` **Klien (Flutter):** Buat widget baru `SduiFormRenderer` yang merender form input dinamis dari skema JSON.
- `[ ]` **Klien (Flutter):** Ganti implementasi `ReportWizardScreen` dengan `SduiFormRenderer`.
- `[ ]` **Klien (Flutter):** Ganti implementasi `AssessmentWizardScreen` dengan `SduiFormRenderer`.

## 4. Modul KPI & Dashboard Publik
- `[ ]` **BFF (Laravel):** Bungkus data metrik publik ke dalam format tipe widget `SummaryCardGrid`.
- `[ ]` **Klien (Flutter):** Daftarkan `SummaryCardGrid` di `WidgetFactory`.
- `[ ]` **Klien (Flutter):** Hapus widget legacy `KpiCardsSection` dari kode Flutter.

## 5. Modul Akun & Pengaturan
- `[ ]` **BFF (Laravel):** Tambahkan parameter `"icon_glyph"` pada data menu di `SettingsCardBuilder`.
- `[ ]` **Klien (Flutter):** Hapus metode pembantu `_iconForSetting` di `settings_card.dart` dan gunakan font icon loader dinamis.
