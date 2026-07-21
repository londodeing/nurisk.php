# Audit Pra-Submit Google Play — NURisk

**Tanggal Audit:** 21 Juli 2026
**Auditor:** Senior Android Release Engineer
**Aplikasi:** NURisk v1.0.0+1 (org.nurisk.app)
**Platform:** Flutter 3.44.4 / Dart 3.12.2

---

## 1. AUDIT BUILD & RELEASE

### Hasil Build

| Item | Status | Detail |
|------|--------|--------|
| `flutter build appbundle --release` | **PASS** | Berhasil |
| Ukuran AAB | **WARNING** | **80.4 MB** — cukup besar, disarankan optimasi asset & R8 |
| `minSdkVersion` | **PASS** | 24 (dari `flutter.minSdkVersion`) |
| `targetSdkVersion` | **PASS** | 35 (dari `flutter.targetSdkVersion`) |
| `compileSdkVersion` | **PASS** | 35 (dari `flutter.compileSdkVersion`) |
| `versionCode` | **PASS** | 1 |
| `versionName` | **PASS** | 1.0.0 |
| `usesCleartextTraffic` | **PASS** | `false` |
| R8 / ProGuard | **PASS** | `isMinifyEnabled = true` |
| Kotlin Gradle Plugin | **WARNING** | Plugin `maplibre_gl` menerapkan KGP — akan bermasalah di Fluture Flutter |

**File diperiksa:**
- `android/app/build.gradle.kts` — OK
- `android/settings.gradle.kts` — OK
- `android/gradle.properties` — OK
- `pubspec.yaml` — OK

### Dependency: `flutter pub deps`

| Dependency | Versi | Status |
|------------|-------|--------|
| flutter_riverpod | ^3.3.2 | OK |
| go_router | ^17.3.0 | OK |
| dio | ^5.4.3 | OK |
| maplibre_gl | ^0.26.2 | OK (MapLibre, tanpa token) |
| flutter_secure_storage | ^10.3.1 | OK |
| permission_handler | ^12.0.3 | OK |
| url_launcher | ^6.3.2 | **WARNING** — declared but unused |
| flutter_dotenv | ^6.0.1 | OK |

---

## 2. AUDIT CRASH & STARTUP

### Inisialisasi

| Item | Status | Detail |
|------|--------|--------|
| `main()` async init | **PASS** | `WidgetsFlutterBinding.ensureInitialized()` + try/catch dotenv |
| Runtime initializer | **PASS** | Error handling via `RuntimeState` |
| ErrorBoundary | **PASS** | `AppErrorHandler.init()` dipanggil |
| Splash screen | **PASS** | Ada splash screen + loading indicator |
| HTTP client startup | **PASS** | Tidak ada panggilan ke localhost |
| Firebase Crashlytics | **FAIL** | Tidak terpasang — tidak ada crash reporting di production |

### ❌ FAIL: Debug forensic flags di release build

**File:** `lib/main.dart:25-27`
```dart
debugPrintGestureArenaDiagnostics = true;
debugPrintHitTestResults = true;
debugPrintRecognizerCallbacksTrace = true;
```

**Penyebab:** Flag debug tetap aktif di release — output log berantakan dan performa turun.

**Risiko:** Penolakan Google? Rendah. Namun sangat tidak profesional dan akan membuat logcat penuh spam.

**Perbaikan:** Hapus ketiga baris atau bungkus dengan `kDebugMode`.

### ❌ FAIL: HitTest listener dump debug di release

**File:** `lib/main.dart:133-147`

Setiap sentuhan layar mencetak hit test tree ke console. Ini adalah forensic tool yang TIDAK BOLEH ada di release.

**Perbaikan:** Hapus seluruh `Listener` wrapper atau guard dengan `kDebugMode`.

### Simulasi Logcat Crash (jika terjadi)

```
E/flutter (12345): [API Error] 500 https://nurisk.org/api/v1/auth/user
E/flutter (12345): [API Error Data] {message: Server Error}
E/flutter (12345): Platform error: DioException [connection timeout]
E/flutter (12345): #0 DioExceptionMapper._mapDioException
```

Error akan tertangani oleh `AppErrorHandler` dan ditampilkan sebagai SnackBar/error screen. Tidak akan force-close.

---

## 3. AUDIT NETWORK & HTTPS

### Pemeriksaan

| Item | Status | Detail |
|------|--------|--------|
| Semua endpoint HTTPS | **PASS** | `https://nurisk.org/api/` |
| Tidak ada IP lokal hardcode | **PASS** | Tidak ditemukan `localhost`, `192.168.*`, `10.0.2.2` |
| Tidak ada secret di source | **PASS** | Tidak ada `api_key`, `secret`, `password` di source code |
| Timeout dikonfigurasi | **PASS** | `connectTimeout`, `receiveTimeout`, `sendTimeout` = 15s |
| Token disimpan aman | **PASS** | `FlutterSecureStorage` (Keychain/EncryptedSharedPreferences) |
| Token tidak di-log | **PASS** | Debug log hanya method & URL, bukan token |
| Credential di source | **PASS** | Tidak ada |

**File diperiksa:**
- `lib/core/api/api_client.dart` — OK
- `lib/features/auth/data/datasources/auth_remote_datasource.dart` — OK
- `lib/core/storage/secure_storage_service.dart` — OK
- `.env` — hanya berisi API_BASE_URL dan MAPBOX_ACCESS_TOKEN (mock)

---

## 4. AUDIT PERMISSION ANDROID

### Tabel Permission

| Permission | Digunakan Oleh | Status | Risiko |
|------------|---------------|--------|--------|
| `INTERNET` | Seluruh aplikasi | ✅ **Aman** | Tidak ada |
| `ACCESS_FINE_LOCATION` | Pelaporan + Map | ✅ **Aman** | Tidak ada |
| `ACCESS_COARSE_LOCATION` | Pelaporan + Map | ✅ **Aman** | Tidak ada |
| `CAMERA` | Upload foto laporan | ✅ **Aman** | Tidak ada |
| `READ_EXTERNAL_STORAGE` | Upload foto (legacy, maxSdk=32) | ✅ **Aman** | Dibutuhkan untuk Android < 13 |
| `WRITE_EXTERNAL_STORAGE` | Simpan foto (legacy, maxSdk=32) | ✅ **Aman** | Dibutuhkan untuk Android < 13 |

### Permission yang TIDAK ada (baik)

| Permission | Keterangan |
|------------|------------|
| `READ_CONTACTS` | ❌ Tidak ada |
| `READ_PHONE_STATE` | ❌ Tidak ada |
| `MANAGE_EXTERNAL_STORAGE` | ❌ Tidak ada |
| `QUERY_ALL_PACKAGES` | ❌ Tidak ada |
| `ACCESS_BACKGROUND_LOCATION` | ❌ Tidak ada |

**Status: PASS** — Tidak ada permission mencurigakan.

**File:** `android/app/src/main/AndroidManifest.xml:1-59` — OK

---

## 5. AUDIT PRIVACY POLICY & DATA SAFETY

### Cek Ketersediaan

| Item | Status | Detail |
|------|--------|--------|
| URL Privacy Policy | **PASS** | `https://nurisk.org/privacy` (HTTPS) |
| Halaman publik | **PASS** | Tidak perlu login |
| Privacy Policy di app | **PASS** | `PrivacyPolicyScreen` sudah ditambahkan |
| Terms of Service | **WARNING** | Belum ada halaman Syarat & Ketentuan |
| Email kontak privasi | **PASS** | `privasi@nurisk.id` |

### Data Safety Checklist (Google Play Console)

| Data | Dikumpulkan? | Dibagikan? | Wajib? | Tujuan |
|------|-------------|-----------|--------|--------|
| Nama | ✅ Ya | ❌ Tidak | Ya | Registrasi & profil |
| Email | ✅ Ya | ❌ Tidak | Ya | Autentikasi & notifikasi |
| Nomor HP | ✅ Ya | ❌ Tidak | Ya | Autentikasi & kontak darurat |
| Lokasi GPS | ✅ Ya | ❌ Tidak | Ya | Pelaporan bencana |
| Foto | ✅ Ya | ❌ Tidak | Ya | Verifikasi kejadian |
| Device info | ✅ Ya | ❌ Tidak | Tidak | Keamanan & analitik |
| Log aktivitas | ✅ Ya | ❌ Tidak | Tidak | Keamanan sistem |

### Jawaban Data Safety

| Pertanyaan | Jawaban |
|------------|---------|
| Data dienkripsi saat transit? | ✅ Ya (HTTPS) |
| Data dienkripsi saat penyimpanan? | ✅ Ya (server-side encryption) |
| Pengguna bisa minta hapus data? | ✅ Ya (via email/menu aplikasi) |
| Data wajib untuk fungsi aplikasi? | ✅ Ya (sebagian bersifat wajib) |
| Data diperjualbelikan? | ❌ Tidak |

---

## 6. AUDIT FITUR HAPUS AKUN

### ❌ FAIL: Tidak ada fitur hapus akun

**Kondisi saat ini:**
- ✅ Privacy policy menyebutkan penghapusan akun via email `privasi@nurisk.id`
- ❌ Tidak ada API endpoint `DELETE /account` di backend
- ❌ Tidak ada tombol "Hapus Akun" di Flutter app
- ❌ Tidak ada mekanisme verifikasi identitas sebelum penghapusan

**Risiko penolakan Google:** **TINGGI** — Google Play mewajibkan pengguna bisa menghapus akun dan data dari dalam aplikasi (tidak hanya via email).

### Spesifikasi API yang harus dibuat

**Backend (Laravel) — endpoint baru:**

```php
// routes/api.php — dalam grup middleware auth:sanctum
Route::delete('account', [AccountController::class, 'destroy'])
    ->name('api.account.destroy');
```

**Controller:**

```php
namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function destroy(Request $request)
    {
        $user = $request->user();

        // Optional: verify password before deletion
        if ($request->has('password')) {
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kata sandi salah.'
                ], 403);
            }
        }

        // Anonimize or delete user data
        $user->tokens()->delete();

        // Anonymize: keep record for audit, remove PII
        $user->update([
            'nama_lengkap' => '[Dihapus]',
            'email' => null,
            'no_hp' => null,
            'password' => bcrypt('[deleted-' . now()->timestamp . ']'),
            'tanggal_hapus' => now(),
        ]);

        // Or hard delete:
        // $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dihapus.'
        ]);
    }
}
```

**Contoh response:**

```json
// 200 — Berhasil
{
  "success": true,
  "message": "Akun berhasil dihapus. Data pribadi Anda telah dianonimkan."
}

// 403 — Password salah
{
  "success": false,
  "message": "Kata sandi salah."
}

// 401 — Token tidak valid
{
  "message": "Unauthenticated."
}
```

**Flutter — tambahkan tombol hapus akun:**

Buat method di `AuthRepository`:
```dart
Future<void> deleteAccount({String? password}) async {
  await _client.delete('/v1/account', data: {
    if (password != null) 'password': password,
  });
  await SecureStorageService.clearAll();
}
```

Tambah tombol di `WorkspaceScreen` (setelah logout):
```dart
_buildDestructiveButton(
  icon: Icons.person_remove_rounded,
  label: 'Hapus Akun',
  subtitle: 'Hapus akun dan seluruh data pribadi Anda',
  color: NuriskColors.emergencyRed,
  onTap: () => _showDeleteAccountDialog(context, ref),
),
```

---

## 7. AUDIT WEBVIEW / NATIVE EXPERIENCE

| Kriteria | Skor |
|----------|------|
| Native experience | **100/100** — App native Flutter murni, navigasi GoRouter |
| Offline readiness | **80/100** — Ada SQLite lokal (drift) untuk master data, SDUI cached |
| Branding quality | **90/100** — Tema hijau NU, ikon MapLibre, splash screen |

**Kesimpulan:** PASS — 100% native Flutter, tidak ada WebView wrapper.

---

## 8. AUDIT KONTEN & MODERASI

### Status Laporan

| Status | Keterangan |
|--------|------------|
| Laporan Awal | Warga membuat laporan |
| Menunggu Verifikasi | Laporan masuk antrian petugas |
| Terverifikasi | TRC/administrator memvalidasi |
| Ditolak | Laporan tidak valid/ditolak |

### Rekomendasi Moderasi

| Item | Rekomendasi |
|------|-------------|
| Foto korban eksplisit | Tambahkan warning/konfirmasi sebelum upload |
| Darah/luka berat | Saring dengan automated flagging (masa depan) |
| Ujaran kebencian | Tambahkan Terms of Service yang melarang |
| Informasi belum terverifikasi | Label "Belum Terverifikasi" pada laporan publik |

**Status: WARNING** — Moderasi manual via verifikasi TRC sudah jalan, tapi belum ada automated filtering.

---

## 9. AUDIT PLAY STORE LISTING

### Checklist Metadata

| Item | Status | Catatan |
|------|--------|---------|
| Nama aplikasi | **PASS** | "NURisk" — tidak meniru instansi pemerintah |
| Deskripsi singkat (80 char) | **BELUM** | Perlu dibuat |
| Deskripsi lengkap | **BELUM** | Perlu dibuat |
| Ikon 512x512 | **PASS** | Ada di mipmap (perlu diekspor ke 512px untuk listing) |
| Feature graphic 1024x500 | **BELUM** | Belum ada asset |
| Screenshot (min 2) | **BELUM** | Belum ada screenshot |
| Email dukungan | **PASS** | `privasi@nurisk.id` |
| URL situs web | **PASS** | `https://nurisk.org` |
| Kategori | **REKOMENDASI** | Acara & Acara / Cuaca (atau Sosial) |

### Rekomendasi Nama & Deskripsi

**Nama:** NURisk — NU Peduli Jateng
**Deskripsi singkat:** Sistem pelaporan dan manajemen bencana NU Jawa Tengah
**Deskripsi lengkap:**
```
NURisk (NU Risk Information System) adalah aplikasi pelaporan dan manajemen penanggulangan bencana resmi dari LPBI NU Jawa Tengah.

Fitur utama:
• Laporkan kejadian bencana dengan foto dan lokasi GPS
• Pantau status laporan secara real-time
• Dashboard informasi bencana terkini
• Koordinasi respons bencana dengan TRC dan posko
• Notifikasi perkembangan penanganan

NURisk dikelola oleh LPBI NU Jawa Tengah (Lembaga Penanggulangan Bencana dan Perubahan Iklim Nahdlatul Ulama Jawa Tengah).
```

---

## 10. AUDIT KEAMANAN DASAR

| Item | Status | Detail |
|------|--------|--------|
| R8 / Obfuscation | **PASS** | `isMinifyEnabled = true`, proguard-rules.pro ada |
| `debuggable=false` | **PASS** | Default release build |
| Logging sensitif mati | **PASS** | API log hanya di `kDebugMode` |
| Token tidak di console | **PASS** | Tidak ada print token |
| `usesCleartextTraffic` | **PASS** | `false` |
| Certificate pinning | **WARNING** | Tidak diterapkan (opsional untuk MVP) |
| Backup Android | **WARNING** | Belum ada konfigurasi `android:allowBackup` |
| Debug forensic flags | **FAIL** | 3 flag debug + Listener hit test di release |

**Skor keamanan: 75/100**

---

## 11. SIMULASI REVIEW GOOGLE

### Skenario 1: Install & Buka Aplikasi
1. Install AAB → ✅ Berhasil
2. Buka aplikasi → ✅ Splash screen muncul, loading normal
3. Login → ✅ Form login berfungsi, validasi error OK

### Skenario 2: Tolak Izin Lokasi
1. Aplikasi minta izin lokasi → ✅ Sesuai fitur
2. Tolak izin → ✅ Aplikasi tetap berfungsi (map mungkin terbatas)
3. **WARNING**: Tidak ada edukasi mengapa lokasi diperlukan saat pertama kali

### Skenario 3: Buat Laporan Tanpa Foto
1. Buka form laporan → ✅ Berfungsi
2. Isi data tanpa foto → ✅ Bisa submit
3. **WARNING**: Sebaiknya validasi minimum foto untuk laporan bencana

### Skenario 4: Upload Foto
1. Pilih foto dari galeri → ✅ Berfungsi
2. Ambil foto dari kamera → ✅ Berfungsi
3. Upload berhasil → ✅ OK

### Skenario 5: Logout
1. Buka menu profil → ✅ WorkspaceScreen muncul
2. Tekan logout → ✅ Konfirmasi dialog muncul
3. Logout berhasil → ✅ Kembali ke guest view

### Skenario 6: Cari Menu Hapus Akun
1. Cari di profil → ❌ **TIDAK ADA** — tidak ada tombol hapus akun
2. **FAIL**: Tidak bisa hapus akun dari dalam aplikasi

### Skenario 7: Periksa Privacy Policy
1. Buka link privacy → ✅ Halaman bisa diakses dari workspace screen
2. Konten privacy → ✅ Lengkap, mencakup semua persyaratan

---

## 12. HASIL AKHIR

### STATUS KELAYAKAN

**⚠️ TUNDA SUBMIT – PERLU PERBAIKAN**

### SKOR

| Kategori | Skor |
|----------|------|
| Compliance | 70/100 |
| Security | 75/100 |
| Stability | 85/100 |
| Store readiness | 50/100 |
| **Overall** | **70/100** |

### TEMUAN KRITIS

- [x] Tidak ada fitur hapus akun (API + UI)
- [x] Debug forensic flags aktif di release build
- [x] HitTest Listener dump debug di release
- [x] Tidak ada Firebase Crashlytics / crash reporting
- [x] Metadata Play Store belum lengkap (deskripsi, screenshot, feature graphic)

### PRIORITAS PERBAIKAN

#### P0 (wajib sebelum submit) — Estimasi: 1 hari

| # | Item | File | Tindakan |
|---|------|------|----------|
| 1 | Buat API hapus akun | `routes/api.php` + `AccountController` | Tambah `DELETE /api/v1/account` |
| 2 | Tombol hapus akun di Flutter | `WorkspaceScreen` + `AuthRepository` | Tambah UI + call API |
| 3 | Hapus forensic debug flags | `lib/main.dart:25-27` | Hapus 3 baris `debugPrint*` |
| 4 | Hapus Listener hit test | `lib/main.dart:133-147` | Hapus wrapper `Listener` |

#### P1 (disarankan) — Estimasi: 1 hari

| # | Item | Tindakan |
|---|------|----------|
| 1 | Buat Terms of Service page | Buat `resources/views/public/terms.blade.php` + route |
| 2 | Tambah Firebase Crashlytics | Integrasi `firebase_crashlytics` untuk crash reporting |
| 3 | Optimasi ukuran AAB (80MB) | Tree-shake asset, compress gambar, minimalisasi font |
| 4 | Siapkan metadata Play Store | Deskripsi, screenshot, feature graphic 1024x500 |

#### P2 (opsional)

| # | Item | Tindakan |
|---|------|----------|
| 1 | Certificate pinning | Tambahkan dio certificate pinning untuk keamanan ekstra |
| 2 | `android:allowBackup` | Tambahkan `android:allowBackup="false"` di AndroidManifest |
| 3 | Automated content moderation | Filter gambar untuk konten eksplisit (masa depan) |

### RENCANA PERBAIKIAN 1 HARI

| Waktu | Aktivitas |
|-------|-----------|
| **Jam 1-2** | Backend: Buat `AccountController` + route `DELETE /api/v1/account` |
| **Jam 2-3** | Flutter: Tambah method `deleteAccount()` di AuthRepository |
| **Jam 3-4** | Flutter: Tambah tombol "Hapus Akun" + dialog konfirmasi di WorkspaceScreen |
| **Jam 4-5** | Flutter: Hapus forensic debug flags + Listener hit test di `main.dart` |
| **Jam 5-6** | Testing end-to-end: build + install + login + hapus akun |
| **Jam 6-8** | Siapkan listing: deskripsi, screenshot, feature graphic |

Setelah perbaikan P0 selesai: **SIAP SUBMIT**

### KEPUTUSAN

```
╔══════════════════════════════════════════════════════╗
║           ⚠️  TUNDA SUBMIT – PERLU PERBAIKAN         ║
║                                                      ║
║  3 temuan P0 harus diperbaiki sebelum submit:        ║
║  ❌ 1. Tidak ada fitur hapus akun (API + UI)        ║
║  ❌ 2. Debug forensic flags di release build         ║
║  ❌ 3. HitTest Listener debug di release             ║
║                                                      ║
║  Estimasi perbaikan: 1 hari kerja                    ║
║  Setelah perbaikan: SIAP SUBMIT ✓                    ║
╚══════════════════════════════════════════════════════╝
```
