# Navigation SDUI Compliance Audit

Audit terhadap logika navigasi perpindahan layar (*Routing & Navigation Flow*) di dalam kode Flutter.

---

## Temuan 1: Navigasi Kaku Bottom Bar (Bilah Bawah)
- **File:** `mobile/app/lib/core/router/app_router.dart`
- **Pelanggaran Arsitektur:**
  Menu bawah aplikasi (Home, Map, Lapor, Resource, Profile) dipaku mati (*hardcoded*) di dalam router menggunakan rute indeks statis.
- **Rujukan Dokumen:** `m22a_05_navigation_contract.md`
- **Koreksi:**
  Menu bawah harus dirender berdasarkan respon JSON `bottom_nav` yang dikirim dari API konfigurasi BFF. Flutter hanya bertugas merender ikon dan label tab yang dikirim, dan saat diketuk akan memicu *ActionResolver* untuk membuka halaman dinamis yang bersangkutan (misal: `/dynamic/resource` atau `/map`).

---

## Temuan 2: Aksi Navigasi Setelah Otentikasi
- **File:** [login_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/auth/presentation/screens/login_screen.dart#L90)
- **Kode Bukti:**
  ```dart
  ref.read(runtimeServicesProvider).navigation.goHome();
  ```
- **Pelanggaran Arsitektur:**
  Setelah masuk berhasil, klien secara manual mengarahkan ke halaman `goHome()`.
  Untuk mematuhi SDUI secara penuh, respon otentikasi dari peladen (Laravel) harus melampirkan parameter aksi navigasi yang diinginkan:
  ```json
  "on_success": {
    "type": "navigate",
    "target": "/dashboard"
  }
  ```
  Ini memberi backend kebebasan mengarahkan pengguna secara dinamis (misalnya melempar pengguna langsung ke halaman pembaruan profil jika data belum lengkap, tanpa mengubah kode Flutter).
