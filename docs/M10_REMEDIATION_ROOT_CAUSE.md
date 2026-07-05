# M10 MOBILISASI — REMEDIATION ROOT CAUSE ANALYSIS

Dokumen ini berisi analisis akar masalah (Root Cause Analysis) berdasarkan temuan dari Independent Architect Review.

## 1. FATAL BUG: Sync Down Tidak Berjalan (SyncObserver Bypass)
* **Severity:** CRITICAL
* **Evidence:** `app/Observers/SyncObserver.php` baris 12-19 (method `getUuid`) dan baris 21-30 (method `getEntityType`).
* **Root Cause:** Method `getUuid` pada `SyncObserver` tidak memeriksa atribut `uuid_mobilisasi`. Selain itu, method `getEntityType` tidak mendaftarkan class `OperasiMobilisasi`, sehingga jatuh ke `default => strtolower(class_basename($model))` yang menghasilkan `'operasimobilisasi'` alih-alih `'mobilisasi'`.
* **Impact:** Ketika data mobilisasi dibuat, diupdate, atau dihapus (soft-delete) di server, tidak ada `SyncCursor` maupun `SyncTombstone` yang dibuat. Akibatnya, sinkronisasi arah server-ke-client (sync down) rusak total karena server tidak memiliki log perubahan untuk ditarik oleh Flutter client.
* **Fix Strategy:** 
  1. Tambahkan `if (isset($model->uuid_mobilisasi)) return $model->uuid_mobilisasi;` pada `getUuid()`.
  2. Tambahkan `\App\Models\OperasiMobilisasi::class => 'mobilisasi',` pada `getEntityType()`.
  3. Buat test khusus yang memverifikasi bahwa operasi insert/update/delete menghasilkan cursor dan tombstone yang benar.

## 2. Massive Authorization Bypass pada Endpoint Index
* **Severity:** HIGH
* **Evidence:** `app/Http/Controllers/Api/Operasi/MobilisasiApiController.php` method `index()`.
* **Root Cause:** Controller tidak memanggil metode otorisasi policy `$this->authorize('viewAny', OperasiMobilisasi::class);` pada awal eksekusi method `index()`.
* **Impact:** Kebocoran data. Pengguna yang berhasil mencapai endpoint ini (termasuk aktor tanpa hak akses yang relevan) dapat menarik seluruh data mobilisasi di sistem tanpa pembatasan scope/wilayah.
* **Fix Strategy:** Tambahkan pemanggilan `authorize('viewAny')` di awal method `index()`, serta pastikan route juga aman jika ada middleware yang terlewat.

## 3. Bypass Otorisasi Boundary Insiden pada Endpoint Store
* **Severity:** HIGH
* **Evidence:** `app/Http/Controllers/Api/Operasi/MobilisasiApiController.php` method `store()` dan `app/Policies/OperasiMobilisasiPolicy.php` method `create()`.
* **Root Cause:** Method `store()` hanya memanggil `$this->authorize('create', OperasiMobilisasi::class);` yang mana pada policy `create()` hanya mengecek keberadaan global role (`super_admin`, `pwnu`, `pcnu`) tanpa mengevaluasi keterkaitan user dengan insiden target.
* **Impact:** Insecure Direct Object Reference (IDOR) pada pembuatan relasi. Seorang user dari PCNU wilayah A dapat secara sengaja/tidak sengaja membuat mobilisasi untuk insiden di wilayah PCNU B, melanggar batas (boundary) otorisasinya.
* **Fix Strategy:** Ubah validasi di `store()` menjadi `$this->authorize('create', [OperasiMobilisasi::class, $insiden]);`. Ubah signature policy `create(AuthUser $user, OperasiInsiden $insiden)` untuk memvalidasi `canManageInsiden($user, $insiden)`.

## 4. Missing Database Index pada Kolom Kritis
* **Severity:** MEDIUM
* **Evidence:** `database/migrations/2026_06_17_125727_create_operasi_mobilisasi_table.php`.
* **Root Cause:** Migrasi awal tabel `operasi_mobilisasi` tidak mendefinisikan index pada kolom Foreign Key (FK) serta kolom yang berpotensi menjadi acuan query filter dan sinkronisasi offline.
* **Impact:** Menurunkan performa query seiring bertambahnya volume data, serta menyalahi `DATABASE_CONVENTION.md` NURISK yang mewajibkan indexing pada seluruh FK, status, dan timestamp transaksi besar.
* **Fix Strategy:** Buat migration baru (incremental) untuk menambahkan index pada `id_insiden`, `id_pengguna`, `status_mobilisasi`, dan `sync_version`.

## 5. Integer Exposure (id_pengguna)
* **Severity:** LOW/MEDIUM
* **Evidence:** `app/Http/Requests/Operasi/StoreMobilisasiRequest.php` dan `app/Http/Resources/Operasi/MobilisasiResource.php`.
* **Root Cause:** Aturan input menerima integer `id_pengguna` dan resource output memaparkannya secara langsung. Hal ini diakibatkan oleh `AuthUser` yang belum bermigrasi penuh menggunakan UUID.
* **Impact:** Meski sesuai dengan konvensi legacy NURISK untuk `AuthUser`, pemaparan integer PK tetap berpotensi menjadi celah enumeration bagi penyerang.
* **Fix Strategy:** Temuan ini lebih bersifat limitasi sistem secara umum. Remediasi akan difokuskan pada pengamanan policy layer secara ketat untuk menetralkan risiko eksploitasi jika ada enumeration attempt.

## 6. Very Low Test Coverage
* **Severity:** HIGH (Production Readiness Blocker)
* **Evidence:** `tests/Feature/Operasi/Mobilisasi/MobilisasiApiTest.php` dan `MobilisasiSyncTest.php`.
* **Root Cause:** Pengujian hanya menyentuh "happy path" (pembuatan sukses, transisi berhasil, sync conflict dasar). Skenario penolakan hak akses (forbidden), batas wilayah (scope boundaries), dan sinkronisasi turun (pull sync) tidak pernah diuji.
* **Impact:** Bug fatal (seperti kegagalan `SyncObserver`) gagal terdeteksi di CI/CD, memberi ilusi bahwa sistem siap produksi.
* **Fix Strategy:** Ekspansi massive pada Test Suite untuk menyentuh \>90% coverage termasuk unauthorized access, cross-scope boundaries, pull sync, tombstone sync, stale update detection, dan invalid state transition.
