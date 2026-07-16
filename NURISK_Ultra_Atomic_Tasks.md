# NURISK — Ultra Atomic Task List (Implementasi di VSCode)

Dokumen ini HANYA berisi task yang dikerjakan di VSCode (kode: Flutter, CSS, Tailwind, Laravel Blade).
Task yang tidak masuk (dikerjakan di luar VSCode, TIDAK ada di sini): desain logo, ilustrasi, fotografi, brand story, penulisan brand values ke materi cetak.

Aturan penomoran: `T-[Bagian].[Urutan]`. Setiap task = satu tindakan, satu file/satu blok kode, satu kriteria selesai. Kerjakan berurutan dari atas ke bawah.

---

## BAGIAN A — SETUP PROYEK

**T-A.1**
Buat folder `lib/core/theme/` di project Flutter.
Selesai jika: folder `lib/core/theme/` ada.

**T-A.2**
Buat folder `lib/core/constants/` di project Flutter.
Selesai jika: folder `lib/core/constants/` ada.

**T-A.3**
Buka `pubspec.yaml`. Tambahkan dependency `google_fonts` di bawah `dependencies:`.
```yaml
dependencies:
  google_fonts: ^6.2.1
```
Selesai jika: baris `google_fonts: ^6.2.1` ada di `pubspec.yaml`.

**T-A.4**
Jalankan `flutter pub get` di terminal VSCode.
Selesai jika: terminal menampilkan "Got dependencies!" tanpa error.

---

## BAGIAN B — COLOR TOKENS (Flutter)

**T-B.1**
Buat file baru `lib/core/theme/nurisk_colors.dart`.
Selesai jika: file `lib/core/theme/nurisk_colors.dart` ada dan kosong.

**T-B.2**
Di file `lib/core/theme/nurisk_colors.dart`, baris pertama, tambahkan:
```dart
import 'package:flutter/material.dart';
```
Selesai jika: baris import ada di baris 1.

**T-B.3**
Di bawah import, tambahkan deklarasi class kosong:
```dart
class NuriskColors {
  NuriskColors._();
}
```
Selesai jika: class `NuriskColors` dengan constructor private `NuriskColors._()` ada di file.

**T-B.4**
Di dalam class `NuriskColors`, tambahkan 10 konstanta warna Primary (Deep NU Green) berikut, persis seperti ini:
```dart
  static const Color primary50  = Color(0xFFE6F3EC);
  static const Color primary100 = Color(0xFFC2E4D2);
  static const Color primary200 = Color(0xFF99D3B5);
  static const Color primary300 = Color(0xFF6FC297);
  static const Color primary400 = Color(0xFF4CB47F);
  static const Color primary500 = Color(0xFF2CA368);
  static const Color primary600 = Color(0xFF0F6B3C);
  static const Color primary700 = Color(0xFF0B5730);
  static const Color primary800 = Color(0xFF084325);
  static const Color primary900 = Color(0xFF052E19);
```
Selesai jika: 10 baris di atas ada di dalam class, tidak ada typo hex.

**T-B.5**
Di bawah T-B.4, tambahkan 10 konstanta Neutral Gray berikut:
```dart
  static const Color neutral50  = Color(0xFFF8F9FA);
  static const Color neutral100 = Color(0xFFF1F3F5);
  static const Color neutral200 = Color(0xFFE9ECEF);
  static const Color neutral300 = Color(0xFFDEE2E6);
  static const Color neutral400 = Color(0xFFCED4DA);
  static const Color neutral500 = Color(0xFFADB5BD);
  static const Color neutral600 = Color(0xFF6C757D);
  static const Color neutral700 = Color(0xFF495057);
  static const Color neutral800 = Color(0xFF343A40);
  static const Color neutral900 = Color(0xFF212529);
```
Selesai jika: 10 baris di atas ada di dalam class.

**T-B.6**
Di bawah T-B.5, tambahkan 6 konstanta Semantic berikut:
```dart
  static const Color emergencyRed  = Color(0xFFD7263D);
  static const Color warningOrange = Color(0xFFF2994A);
  static const Color safeGreen     = Color(0xFF27AE60);
  static const Color infoBlue      = Color(0xFF2F80ED);
  static const Color darkText      = Color(0xFF1A1D1F);
  static const Color bgWhite       = Color(0xFFFFFFFF);
```
Selesai jika: 6 baris di atas ada di dalam class.

**T-B.7**
Di bawah T-B.6, tambahkan 7 konstanta Dark Mode berikut:
```dart
  static const Color surfaceBase     = Color(0xFF121614);
  static const Color surfaceElevated = Color(0xFF1B211D);
  static const Color surfaceOverlay  = Color(0xFF242B26);
  static const Color primaryAccentDark = Color(0xFF4CB47F);
  static const Color textPrimaryDark   = Color(0xFFF1F3F5);
  static const Color textSecondaryDark = Color(0xFFADB5BD);
  static const Color borderDark        = Color(0xFF343A40);
```
Selesai jika: 7 baris di atas ada di dalam class.

**T-B.8**
Simpan file `lib/core/theme/nurisk_colors.dart`.
Selesai jika: `flutter analyze` tidak menampilkan error pada file ini.

---

## BAGIAN C — SPACING, RADIUS, ELEVATION TOKENS (Flutter)

**T-C.1**
Buat file baru `lib/core/theme/nurisk_spacing.dart`.
Selesai jika: file ada.

**T-C.2**
Isi file `lib/core/theme/nurisk_spacing.dart` persis dengan kode berikut:
```dart
class NuriskSpacing {
  NuriskSpacing._();
  static const double xs = 4;
  static const double sm = 8;
  static const double md = 12;
  static const double lg = 16;
  static const double xl = 24;
  static const double xxl = 32;
  static const double xxxl = 48;
}
```
Selesai jika: file berisi tepat 7 konstanta di atas.

**T-C.3**
Buat file baru `lib/core/theme/nurisk_radius.dart`.
Selesai jika: file ada.

**T-C.4**
Isi file `lib/core/theme/nurisk_radius.dart` persis dengan kode berikut:
```dart
class NuriskRadius {
  NuriskRadius._();
  static const double xs = 4;
  static const double sm = 8;
  static const double md = 12;
  static const double lg = 16;
  static const double full = 999;
}
```
Selesai jika: file berisi tepat 5 konstanta di atas.

**T-C.5**
Buat file baru `lib/core/theme/nurisk_elevation.dart`.
Selesai jika: file ada.

**T-C.6**
Isi file `lib/core/theme/nurisk_elevation.dart` persis dengan kode berikut:
```dart
class NuriskElevation {
  NuriskElevation._();
  static const double level0 = 0;
  static const double level1 = 1;
  static const double level2 = 3;
  static const double level3 = 6;
  static const double level4 = 8;
  static const double level5 = 12;
}
```
Selesai jika: file berisi tepat 6 konstanta di atas.

---

## BAGIAN D — MOTION TOKENS (Flutter)

**T-D.1**
Buat file baru `lib/core/theme/nurisk_motion.dart`.
Selesai jika: file ada.

**T-D.2**
Tambahkan import di baris pertama:
```dart
import 'package:flutter/animation.dart';
```
Selesai jika: baris import ada di baris 1.

**T-D.3**
Tambahkan durasi animasi berikut ke file yang sama:
```dart
class NuriskMotion {
  NuriskMotion._();
  static const Duration micro = Duration(milliseconds: 150);
  static const Duration standard = Duration(milliseconds: 200);
  static const Duration pageTransition = Duration(milliseconds: 300);
  static const Duration sheetIn = Duration(milliseconds: 260);
  static const Duration sheetOut = Duration(milliseconds: 200);
  static const Duration expandCollapse = Duration(milliseconds: 220);
  static const Duration heroTransition = Duration(milliseconds: 320);
  static const Duration bannerIn = Duration(milliseconds: 240);
  static const Duration bannerOut = Duration(milliseconds: 180);
  static const Curve standardCurve = Curves.easeInOut;
  static const Curve emphasizedDecelerate = Curves.easeOutCubic;
  static const Curve emphasizedAccelerate = Curves.easeInCubic;
}
```
Selesai jika: 9 konstanta `Duration` dan 3 konstanta `Curve` ada di file, tidak ada error `flutter analyze`.

---

## BAGIAN E — TYPOGRAPHY (Flutter)

**T-E.1**
Buat file baru `lib/core/theme/nurisk_text_theme.dart`.
Selesai jika: file ada.

**T-E.2**
Tambahkan 2 baris import berikut di baris pertama file:
```dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
```
Selesai jika: kedua baris import ada.

**T-E.3**
Tambahkan function berikut ke file yang sama, tepat di bawah import:
```dart
TextTheme buildNuriskTextTheme() {
  final base = GoogleFonts.plusJakartaSansTextTheme();
  return base.copyWith(
    displayLarge: base.displayLarge?.copyWith(fontSize: 32, height: 40 / 32, fontWeight: FontWeight.w700, letterSpacing: -0.2),
    headlineLarge: base.headlineLarge?.copyWith(fontSize: 28, height: 36 / 28, fontWeight: FontWeight.w700, letterSpacing: -0.2),
    titleLarge: base.titleLarge?.copyWith(fontSize: 22, height: 28 / 22, fontWeight: FontWeight.w600),
    titleMedium: base.titleMedium?.copyWith(fontSize: 18, height: 24 / 18, fontWeight: FontWeight.w600),
    bodyLarge: base.bodyLarge?.copyWith(fontSize: 16, height: 1.5, fontWeight: FontWeight.w400),
    bodyMedium: base.bodyMedium?.copyWith(fontSize: 14, height: 20 / 14, fontWeight: FontWeight.w400, letterSpacing: 0.1),
    bodySmall: base.bodySmall?.copyWith(fontSize: 12, height: 16 / 12, fontWeight: FontWeight.w400, letterSpacing: 0.2),
    labelSmall: base.labelSmall?.copyWith(fontSize: 11, height: 16 / 11, fontWeight: FontWeight.w600, letterSpacing: 0.6),
    labelLarge: base.labelLarge?.copyWith(fontSize: 14, height: 20 / 14, fontWeight: FontWeight.w600, letterSpacing: 0.2),
  );
}
```
Selesai jika: function `buildNuriskTextTheme()` mengembalikan `TextTheme` tanpa error compile.

**T-E.4**
Buka `pubspec.yaml`, pastikan baris `google_fonts` sudah ada (lihat T-A.3). Jika belum, tambahkan.
Selesai jika: `flutter pub get` berjalan tanpa error setelah perubahan.

---

## BAGIAN F — FLUTTER THEME (Light & Dark)

**T-F.1**
Buat file baru `lib/core/theme/nurisk_theme.dart`.
Selesai jika: file ada.

**T-F.2**
Tambahkan 3 baris import berikut di baris pertama:
```dart
import 'package:flutter/material.dart';
import 'nurisk_colors.dart';
import 'nurisk_text_theme.dart';
```
Selesai jika: ketiga baris import ada.

**T-F.3**
Tambahkan definisi `nuriskLightTheme` berikut ke file yang sama:
```dart
final ThemeData nuriskLightTheme = ThemeData(
  useMaterial3: true,
  colorScheme: ColorScheme.fromSeed(
    seedColor: NuriskColors.primary600,
    brightness: Brightness.light,
    error: NuriskColors.emergencyRed,
  ),
  scaffoldBackgroundColor: NuriskColors.bgWhite,
  textTheme: buildNuriskTextTheme(),
);
```
Selesai jika: variabel `nuriskLightTheme` bertipe `ThemeData` ada di file, tanpa error compile.

**T-F.4**
Tambahkan definisi `nuriskDarkTheme` berikut, tepat di bawah T-F.3:
```dart
final ThemeData nuriskDarkTheme = nuriskLightTheme.copyWith(
  brightness: Brightness.dark,
  scaffoldBackgroundColor: NuriskColors.surfaceBase,
  colorScheme: ColorScheme.fromSeed(
    seedColor: NuriskColors.primaryAccentDark,
    brightness: Brightness.dark,
  ),
);
```
Selesai jika: variabel `nuriskDarkTheme` bertipe `ThemeData` ada di file, tanpa error compile.

**T-F.5**
Tambahkan style tombol Primary ke dalam `nuriskLightTheme` (di dalam `ThemeData(...)`, tepat setelah `textTheme:`):
```dart
  elevatedButtonTheme: ElevatedButtonThemeData(
    style: ElevatedButton.styleFrom(
      backgroundColor: NuriskColors.primary600,
      foregroundColor: Colors.white,
      minimumSize: const Size(64, 48),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
    ),
  ),
```
Selesai jika: properti `elevatedButtonTheme` ada di dalam `nuriskLightTheme`.

**T-F.6**
Tambahkan style Input Field ke dalam `nuriskLightTheme`, tepat setelah `elevatedButtonTheme:`:
```dart
  inputDecorationTheme: InputDecorationTheme(
    filled: true,
    fillColor: NuriskColors.primary50.withOpacity(0.3),
    border: OutlineInputBorder(borderRadius: BorderRadius.circular(4)),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(4),
      borderSide: const BorderSide(color: NuriskColors.primary600, width: 1.5),
    ),
    errorBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(4),
      borderSide: const BorderSide(color: NuriskColors.emergencyRed, width: 1.5),
    ),
  ),
```
Selesai jika: properti `inputDecorationTheme` ada di dalam `nuriskLightTheme`.

**T-F.7**
Buka file `lib/main.dart`. Tambahkan import berikut di baris paling atas:
```dart
import 'core/theme/nurisk_theme.dart';
```
Sesuaikan path relatif jika struktur folder berbeda.
Selesai jika: baris import ada tanpa error "file not found".

**T-F.8**
Di `lib/main.dart`, cari widget `MaterialApp(`. Tambahkan / ganti 3 baris berikut di dalamnya:
```dart
      theme: nuriskLightTheme,
      darkTheme: nuriskDarkTheme,
      themeMode: ThemeMode.system,
```
Selesai jika: `flutter run` menampilkan aplikasi dengan warna Primary-600 pada tombol default.

---

## BAGIAN G — KOMPONEN: BUTTON

**T-G.1**
Buat file baru `lib/shared/widgets/buttons/nurisk_primary_button.dart`.
Selesai jika: file ada.

**T-G.2**
Isi file dengan widget berikut persis:
```dart
import 'package:flutter/material.dart';
import '../../../core/theme/nurisk_colors.dart';
import '../../../core/theme/nurisk_radius.dart';

class NuriskPrimaryButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  const NuriskPrimaryButton({super.key, required this.label, required this.onPressed});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 48,
      child: ElevatedButton(
        onPressed: onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: NuriskColors.primary600,
          foregroundColor: Colors.white,
          disabledBackgroundColor: NuriskColors.primary600.withOpacity(0.38),
          padding: const EdgeInsets.symmetric(horizontal: 20),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(NuriskRadius.sm)),
        ),
        child: Text(label),
      ),
    );
  }
}
```
Sesuaikan path import jika struktur folder berbeda.
Selesai jika: widget `NuriskPrimaryButton` bisa dipanggil tanpa error compile.

**T-G.3**
Buat file baru `lib/shared/widgets/buttons/nurisk_secondary_button.dart` dengan isi yang sama seperti T-G.2, tapi ganti nama class menjadi `NuriskSecondaryButton` dan ganti `style:` menjadi:
```dart
        style: OutlinedButton.styleFrom(
          foregroundColor: NuriskColors.primary600,
          side: const BorderSide(color: NuriskColors.primary600, width: 1.5),
          padding: const EdgeInsets.symmetric(horizontal: 20),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(NuriskRadius.sm)),
        ),
```
Gunakan `OutlinedButton(...)` bukan `ElevatedButton(...)`.
Selesai jika: widget `NuriskSecondaryButton` bisa dipanggil tanpa error compile.

**T-G.4**
Buat file baru `lib/shared/widgets/buttons/nurisk_destructive_button.dart`, salin struktur dari T-G.2, ganti nama class menjadi `NuriskDestructiveButton`, dan ganti `backgroundColor` menjadi `NuriskColors.emergencyRed`.
Selesai jika: widget `NuriskDestructiveButton` bisa dipanggil tanpa error compile.

---

## BAGIAN H — KOMPONEN: CARD, CHIP, BADGE, AVATAR

**T-H.1**
Buat file baru `lib/shared/widgets/cards/nurisk_card.dart` berisi widget dasar berikut:
```dart
import 'package:flutter/material.dart';
import '../../../core/theme/nurisk_radius.dart';
import '../../../core/theme/nurisk_spacing.dart';

class NuriskCard extends StatelessWidget {
  final Widget child;
  const NuriskCard({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(NuriskRadius.md)),
      child: Padding(
        padding: const EdgeInsets.all(NuriskSpacing.lg),
        child: child,
      ),
    );
  }
}
```
Selesai jika: widget `NuriskCard` bisa dipanggil tanpa error compile.

**T-H.2**
Buat file baru `lib/shared/widgets/badges/nurisk_status_chip.dart` berisi:
```dart
import 'package:flutter/material.dart';
import '../../../core/theme/nurisk_colors.dart';
import '../../../core/theme/nurisk_radius.dart';

enum NuriskStatus { danger, warning, safe, info }

class NuriskStatusChip extends StatelessWidget {
  final String label;
  final NuriskStatus status;
  const NuriskStatusChip({super.key, required this.label, required this.status});

  Color get _bg {
    switch (status) {
      case NuriskStatus.danger: return NuriskColors.emergencyRed.withOpacity(0.1);
      case NuriskStatus.warning: return NuriskColors.warningOrange.withOpacity(0.1);
      case NuriskStatus.safe: return NuriskColors.safeGreen.withOpacity(0.1);
      case NuriskStatus.info: return NuriskColors.infoBlue.withOpacity(0.1);
    }
  }

  Color get _fg {
    switch (status) {
      case NuriskStatus.danger: return NuriskColors.emergencyRed;
      case NuriskStatus.warning: return NuriskColors.warningOrange;
      case NuriskStatus.safe: return NuriskColors.safeGreen;
      case NuriskStatus.info: return NuriskColors.infoBlue;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: _bg, borderRadius: BorderRadius.circular(NuriskRadius.full)),
      child: Text(label, style: TextStyle(color: _fg, fontSize: 11, fontWeight: FontWeight.w600)),
    );
  }
}
```
Selesai jika: widget `NuriskStatusChip` menerima parameter `label` dan `status`, tanpa error compile.

**T-H.3**
Buat file baru `lib/shared/widgets/avatars/nurisk_avatar.dart` berisi:
```dart
import 'package:flutter/material.dart';
import '../../../core/theme/nurisk_colors.dart';

enum NuriskRole { volunteer, operator, commander }

class NuriskAvatar extends StatelessWidget {
  final String initials;
  final NuriskRole role;
  const NuriskAvatar({super.key, required this.initials, required this.role});

  Color get _ringColor {
    switch (role) {
      case NuriskRole.volunteer: return NuriskColors.infoBlue;
      case NuriskRole.operator: return NuriskColors.primary600;
      case NuriskRole.commander: return NuriskColors.emergencyRed;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 40, height: 40,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        border: Border.all(color: _ringColor, width: 2),
        color: NuriskColors.primary100,
      ),
      alignment: Alignment.center,
      child: Text(initials, style: TextStyle(color: NuriskColors.primary700, fontWeight: FontWeight.w600)),
    );
  }
}
```
Selesai jika: widget `NuriskAvatar` menerima parameter `initials` dan `role`, tanpa error compile.

---

## BAGIAN I — KOMPONEN STATE (Disabled, Loading, Selected)

**T-I.1**
Buka file `lib/shared/widgets/buttons/nurisk_primary_button.dart` (dibuat di T-G.2). Tambahkan parameter `isLoading` ke constructor:
```dart
  final bool isLoading;
  const NuriskPrimaryButton({super.key, required this.label, required this.onPressed, this.isLoading = false});
```
Selesai jika: parameter `isLoading` ada di constructor dengan default `false`.

**T-I.2**
Di file yang sama, ganti isi `child: Text(label),` menjadi:
```dart
        child: isLoading
            ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
            : Text(label),
```
Selesai jika: saat `isLoading: true`, tombol menampilkan spinner, bukan teks.

**T-I.3**
Di `lib/shared/widgets/buttons/nurisk_primary_button.dart`, ubah baris `onPressed: onPressed,` menjadi:
```dart
        onPressed: isLoading ? null : onPressed,
```
Selesai jika: saat `isLoading: true`, tombol tidak bisa ditekan.

---

## BAGIAN J — ACCESSIBILITY (Flutter)

**T-J.1**
Buka setiap file widget ikon status (contoh: `NuriskStatusChip` dari T-H.2). Bungkus `Container` dengan `Semantics`:
```dart
    return Semantics(
      label: label,
      child: Container(
        // ... isi container yang sudah ada
      ),
    );
```
Selesai jika: widget `NuriskStatusChip` terbaca oleh screen reader (cek dengan TalkBack/VoiceOver).

**T-J.2**
Di `lib/main.dart`, pastikan `MaterialApp` memiliki baris berikut agar text scaling sistem tidak dibatasi:
```dart
      builder: (context, child) {
        final mq = MediaQuery.of(context);
        return MediaQuery(
          data: mq.copyWith(textScaler: mq.textScaler.clamp(minScaleFactor: 1.0, maxScaleFactor: 2.0)),
          child: child!,
        );
      },
```
Selesai jika: baris `builder:` ada di dalam `MaterialApp(...)`.

**T-J.3**
Untuk setiap widget tombol/ikon interaktif baru yang dibuat, pastikan ukuran minimal 48x48 logical pixel. Cek setiap file di `lib/shared/widgets/buttons/` dan pastikan `height: 48` ada.
Selesai jika: semua file button di folder tersebut memiliki tinggi minimal 48.

---

## BAGIAN K — VOICE & TONE (String Constants Bahasa Indonesia)

**T-K.1**
Buat file baru `lib/core/constants/nurisk_strings.dart`.
Selesai jika: file ada.

**T-K.2**
Isi file dengan konstanta berikut persis:
```dart
class NuriskStrings {
  NuriskStrings._();

  static const String btnKirimLaporan = 'Kirim Laporan';
  static const String btnHapusMisi = 'Hapus Misi';

  static const String dialogHapusTitle = 'Hapus laporan ini?';
  static const String dialogHapusBody = 'Tindakan ini tidak dapat dibatalkan.';
  static const String dialogBatal = 'Batal';
  static const String dialogHapus = 'Hapus';

  static const String successKirimLaporan = 'Laporan berhasil dikirim ke tim operator.';

  static const String errorJaringan = 'Gagal mengirim. Periksa koneksi internet, lalu coba lagi.';
  static const String errorLokasiKosong = 'Lokasi wajib diisi sebelum mengirim laporan.';

  static const String warningDataBelumTersimpan = 'Data belum tersimpan. Yakin ingin keluar halaman ini?';

  static const String notifMisiDitugaskan = 'Misi #{id} telah ditugaskan kepada Anda.';

  static const String offlineBanner = 'Anda sedang offline. Data akan tersinkron otomatis saat koneksi kembali.';
}
```
Selesai jika: 12 konstanta `String` di atas ada di file, tanpa error compile.

---

## BAGIAN L — CSS VARIABLES (Web Dashboard / Laravel Blade)

**T-L.1**
Buat file baru `resources/css/nurisk-tokens.css` (sesuaikan path jika struktur Laravel berbeda).
Selesai jika: file ada.

**T-L.2**
Isi file dengan kode berikut persis:
```css
:root {
  --nurisk-primary-50: #E6F3EC;
  --nurisk-primary-100: #C2E4D2;
  --nurisk-primary-200: #99D3B5;
  --nurisk-primary-300: #6FC297;
  --nurisk-primary-400: #4CB47F;
  --nurisk-primary-500: #2CA368;
  --nurisk-primary-600: #0F6B3C;
  --nurisk-primary-700: #0B5730;
  --nurisk-primary-800: #084325;
  --nurisk-primary-900: #052E19;

  --nurisk-neutral-50: #F8F9FA;
  --nurisk-neutral-100: #F1F3F5;
  --nurisk-neutral-200: #E9ECEF;
  --nurisk-neutral-300: #DEE2E6;
  --nurisk-neutral-400: #CED4DA;
  --nurisk-neutral-500: #ADB5BD;
  --nurisk-neutral-600: #6C757D;
  --nurisk-neutral-700: #495057;
  --nurisk-neutral-800: #343A40;
  --nurisk-neutral-900: #212529;

  --nurisk-red: #D7263D;
  --nurisk-orange: #F2994A;
  --nurisk-green-safe: #27AE60;
  --nurisk-blue: #2F80ED;
  --nurisk-text: #1A1D1F;
  --nurisk-bg: #FFFFFF;

  --nurisk-radius-xs: 4px;
  --nurisk-radius-sm: 8px;
  --nurisk-radius-md: 12px;
  --nurisk-radius-lg: 16px;
  --nurisk-radius-full: 999px;

  --nurisk-space-xs: 4px;
  --nurisk-space-sm: 8px;
  --nurisk-space-md: 12px;
  --nurisk-space-lg: 16px;
  --nurisk-space-xl: 24px;
  --nurisk-space-2xl: 32px;
  --nurisk-space-3xl: 48px;
}
```
Selesai jika: file berisi seluruh variabel di atas tanpa typo hex.

**T-L.3**
Di bawah blok `:root { ... }` pada file yang sama, tambahkan blok dark mode berikut:
```css
[data-theme='dark'] {
  --nurisk-bg: #121614;
  --nurisk-text: #F1F3F5;
  --nurisk-primary-600: #4CB47F;
}
```
Selesai jika: blok `[data-theme='dark']` ada tepat di bawah `:root`.

**T-L.4**
Buka file layout utama Blade (contoh: `resources/views/layouts/app.blade.php`). Tambahkan baris berikut di dalam tag `<head>`:
```html
<link rel="stylesheet" href="{{ asset('css/nurisk-tokens.css') }}">
```
Sesuaikan path `asset()` dengan build tool yang dipakai (Vite/Mix).
Selesai jika: file CSS ter-load di browser (cek tab Network di DevTools, status 200).

---

## BAGIAN M — TAILWIND TOKENS

**T-M.1**
Buka file `tailwind.config.js` di root project Laravel.
Selesai jika: file ditemukan dan terbuka di editor.

**T-M.2**
Di dalam `module.exports`, cari (atau buat jika belum ada) blok `theme: { extend: { ... } }`. Tambahkan key `colors` berikut di dalam `extend`:
```js
      colors: {
        primary: {
          50: '#E6F3EC', 100: '#C2E4D2', 200: '#99D3B5', 300: '#6FC297',
          400: '#4CB47F', 500: '#2CA368', 600: '#0F6B3C', 700: '#0B5730',
          800: '#084325', 900: '#052E19',
        },
        emergency: '#D7263D',
        warning: '#F2994A',
        safe: '#27AE60',
        info: '#2F80ED',
      },
```
Selesai jika: key `colors` ada di dalam `theme.extend` pada `tailwind.config.js`.

**T-M.3**
Simpan `tailwind.config.js`. Jalankan `npm run build` (atau `npm run dev` sesuai setup project) di terminal VSCode.
Selesai jika: build selesai tanpa error, dan class seperti `bg-primary-600` bisa dipakai di file Blade.

**T-M.4**
Buka salah satu file Blade (contoh: `resources/views/components/button.blade.php`). Ganti class warna tombol lama dengan `bg-primary-600 hover:bg-primary-700 text-white rounded-lg`.
Selesai jika: tombol tampil dengan warna hijau `#0F6B3C` di browser setelah refresh.

---

## BAGIAN N — RESPONSIVE BREAKPOINTS (Tailwind + Blade)

**T-N.1**
Di `tailwind.config.js`, di dalam `theme.extend`, tambahkan key `screens` berikut:
```js
      screens: {
        'tablet': '600px',
        'desktop': '1024px',
        'command': '1440px',
      },
```
Selesai jika: key `screens` ada di dalam `theme.extend`.

**T-N.2**
Simpan file, jalankan ulang `npm run build`.
Selesai jika: class seperti `desktop:grid-cols-12` bisa dipakai di file Blade tanpa error build.

---

## BAGIAN O — DARK MODE TOGGLE (Web Dashboard)

**T-O.1**
Buka file layout utama Blade (`resources/views/layouts/app.blade.php`). Tambahkan atribut berikut ke tag `<html>`:
```html
<html lang="id" data-theme="light">
```
Selesai jika: atribut `data-theme="light"` ada di tag `<html>`.

**T-O.2**
Buat file baru `resources/js/theme-toggle.js`.
Selesai jika: file ada.

**T-O.3**
Isi file `resources/js/theme-toggle.js` dengan kode berikut persis:
```js
function toggleNuriskTheme() {
  const html = document.documentElement;
  const current = html.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  localStorage.setItem('nurisk-theme', next);
}

document.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('nurisk-theme');
  if (saved) {
    document.documentElement.setAttribute('data-theme', saved);
  }
});
```
Selesai jika: function `toggleNuriskTheme()` tersedia secara global di browser console.

**T-O.4**
Import file `theme-toggle.js` di `resources/js/app.js` dengan menambahkan baris:
```js
import './theme-toggle';
```
Selesai jika: baris import ada di `resources/js/app.js`.

---

## URUTAN PENGERJAAN YANG DISARANKAN

1. Bagian A (Setup)
2. Bagian B, C, D, E (Tokens Flutter)
3. Bagian F (Theme Flutter)
4. Bagian G, H, I, J, K (Komponen + Accessibility + Strings Flutter)
5. Bagian L, M, N, O (Web Dashboard: CSS, Tailwind, Dark Mode)

Setiap task harus dicentang selesai (kriteria "Selesai jika" terpenuhi) sebelum lanjut ke task berikutnya.
