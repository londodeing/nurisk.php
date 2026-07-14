# Renderer Audit: Percabangan Logika Klien

Pencarian forensik terhadap logika pengkondisian kaku di sisi klien Flutter yang melanggar arsitektur *Zero-Trust Client* & *Server-Driven UI*.

---

## Temuan 1: Penentuan Ikon & Struktur di `SettingsCard`
- **File:** [settings_card.dart](file:///home/londo/nurisk/mobile/app/lib/features/account/presentation/widgets/settings_card.dart#L50-L59)
- **Kode:**
  ```dart
  IconData _iconForSetting(String id) {
    switch (id) {
      case 'set_profil': return Icons.person;
      case 'set_mandate': return Icons.shield;
      case 'set_pin': return Icons.lock;
      case 'set_biometric': return Icons.fingerprint;
      case 'set_offline': return Icons.cloud_download;
      case 'set_language': return Icons.language;
      case 'set_about': return Icons.info;
      case 'set_logout': return Icons.logout;
      default: return Icons.settings;
    }
  }
  ```
- **Pelanggaran:** Penentuan ikon diputuskan berdasarkan `id` menu. Jika backend mengganti `id` atau menambahkan menu baru, Flutter tidak akan mengenali ikonnya. backend harus mengirimkan string ikon langsung (misal: `"person"`, `"lock"`) lalu dipetakan ke Icon Flutter lewat parser terpusat.

---

## Temuan 2: Status & Penugasan TRC
- **File:** [trc_assessment_queue_widget.dart](file:///home/londo/nurisk/mobile/app/lib/features/operasi/assessment/presentation/widgets/trc_assessment_queue_widget.dart#L74)
- **Kode:**
  ```dart
  style: TextStyle(fontSize: 10, color: Colors.green.shade800, fontWeight: FontWeight.bold),
  ```
  dan aksi tap:
  ```dart
  onTap: () {
    final uuidInsiden = tugas['uuid_insiden'];
    if (uuidInsiden != null) {
      ref.read(runtimeServicesProvider).navigation.push('/assessment/$uuidInsiden');
    }
  }
  ```
- **Pelanggaran:** Lencana (*badge*) status penugasan dipaksa berwarna hijau secara statis di Flutter (`Colors.green.shade800`). Aksi penekanan juga di-*hardcode* untuk melakukan navigasi ke `/assessment/$uuidInsiden`.

---

## Temuan 3: Pengecekan Peran di Splash Screen
- **File:** [splash_screen.dart](file:///home/londo/nurisk/mobile/app/lib/core/splash/splash_screen.dart#L37)
- **Kode:**
  ```dart
  if (auth.isAuthenticated && auth.activeRole != null) {
    nav.goHome();
  }
  ```
- **Pelanggaran:** Meskipun saat ini sudah diarahkan ke `goHome()`, penentuan arah awal setelah otentikasi masih disaring di Flutter secara statis. Idealnya, setelah otentikasi sukses, peladen merespons dengan aksi navigasi awal (misal: `action: { "type": "navigate", "target": "/dashboard" }`).
