# INDEPENDENT ARCHITECT PRODUCTION REVIEW — M10 MOBILISASI (ZERO TRUST)

## 1. Executive Summary
Audit Zero Trust independen telah dilakukan terhadap seluruh implementasi Domain M10 Mobilisasi di platform NURISK. Evaluasi ini mencakup migrasi database, model Eloquent, policy otorisasi Lapis 4, API endpoints, sinkronisasi offline (offline sync), dan cakupan unit testing.

Hasil review menunjukkan beberapa **celah keamanan kritikal** dan **bug fungsional fatal** pada mekanisme Offline Sync. Implementasi saat ini dinyatakan **REJECTED** dan tidak siap untuk naik ke lingkungan produksi maupun dikonsumsi oleh aplikasi Flutter sebelum perbaikan dilakukan.

---

## 2. Files Reviewed
Seluruh file aktual di bawah ini telah diverifikasi baris demi baris:
* **Migration:** [2026_06_17_125727_create_operasi_mobilisasi_table.php](file:///home/londo/nurisk/database/migrations/2026_06_17_125727_create_operasi_mobilisasi_table.php)
* **Model:** [OperasiMobilisasi.php](file:///home/londo/nurisk/app/Models/OperasiMobilisasi.php)
* **Policy:** [OperasiMobilisasiPolicy.php](file:///home/londo/nurisk/app/Policies/OperasiMobilisasiPolicy.php)
* **Controllers:**
  * [MobilisasiApiController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Operasi/MobilisasiApiController.php)
  * [SyncApiController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Operasi/SyncApiController.php)
* **Requests & Resources:**
  * [StoreMobilisasiRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/StoreMobilisasiRequest.php)
  * [UpdateMobilisasiRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/UpdateMobilisasiRequest.php)
  * [MobilisasiResource.php](file:///home/londo/nurisk/app/Http/Resources/Operasi/MobilisasiResource.php)
* **Routes:** [api.php](file:///home/londo/nurisk/routes/api.php)
* **Observer:** [SyncObserver.php](file:///home/londo/nurisk/app/Observers/SyncObserver.php)
* **Tests:**
  * [MobilisasiApiTest.php](file:///home/londo/nurisk/tests/Feature/Operasi/Mobilisasi/MobilisasiApiTest.php)
  * [MobilisasiSyncTest.php](file:///home/londo/nurisk/tests/Feature/Operasi/Mobilisasi/MobilisasiSyncTest.php)

---

## 3. Architecture Findings
* **Bypass Otorisasi Global:** Route API untuk wilayah dan operasi (termasuk mobilisasi) di `routes/api.php` tidak dibungkus oleh middleware autentikasi Sanctum (`auth:sanctum`). Endpoint dapat diakses secara publik dan anonim.
* **Redundant/Stale Code:** Ditemukan route stub `Route::post('mobilisasi/bulk', ...)` yang menunjuk ke `BulkStubController`. Kode ini membingungkan karena sinkronisasi seharusnya terpusat di `SyncApiController`.

---

## 4. Database Findings
* **Missing Index pada Foreign Keys:** Migration tidak mendefinisikan index eksplisit untuk kolom FK (`id_insiden`, `id_pengguna`, `created_by`, `updated_by`, `deleted_by`). Hal ini melanggar **DATABASE_CONVENTION.md (Aturan 10.1: Kolom FK Wajib diindex)** dan berpotensi menurunkan performa query join saat data bertambah besar.
* **Missing Index pada Status & Sync Version:** Kolom `status_mobilisasi` yang sering difilter dan `sync_version` yang digunakan untuk cursor sync tidak di-index.

---

## 5. Security Findings
* **Massive Authorization Bypass di `index()` (High/Critical):** Method `index` pada `MobilisasiApiController` tidak memanggil gate authorization `$this->authorize('viewAny', OperasiMobilisasi::class)`. Siapa saja (bahkan aktor anonim) dapat menarik seluruh daftar mobilisasi tanpa pembatasan scope wilayah maupun peran.
* **Bypass Otorisasi Unit/Insiden pada `store()` (High):** Pada method `store`, controller hanya memanggil `$this->authorize('create', OperasiMobilisasi::class)` yang memverifikasi peran global (`super_admin`, `pwnu`, `pcnu`). Namun, tidak dilakukan pengecekan apakah user memiliki otoritas mengelola insiden target (`uuid_insiden`). Ini melanggar Lapis 4 Authorization Matrix di mana PCNU hanya boleh membuat mobilisasi untuk insiden di dalam wilayah otoritasnya.
* **Integer Exposure (`id_pengguna`):** `StoreMobilisasiRequest` masih menerima integer `id_pengguna` dan `MobilisasiResource` masih memaparkan integer `id_pengguna` secara langsung ke client REST API. Meskipun hal ini merupakan batasan bawaan sistem karena entitas `AuthUser` belum dimigrasi ke UUID, exposure ini tetap memicu risiko deteksi IDOR di masa depan.

---

## 6. State Machine Findings
* **Validasi Transisi Server-Side Terimplementasi:** Alur transisi status (`draft` → `disetujui` → `berangkat` → `tiba` → `selesai`) dan `dibatalkan` telah dilindungi dengan status code `422` di controller jika ada transisi ilegal.
* **Celah State Machine:** Validasi state machine hanya dilakukan di level REST controller. Jika data di-push via `SyncApiController`, tidak ada pengecekan transisi state machine yang valid di layer sync service. Client sync dapat langsung menyisipkan status apa pun (bypass aturan state machine).

---

## 7. Offline Sync Findings
* **FATAL BUG: Sync Down Tidak Berjalan (`SyncObserver` Bypass):**
  Mekanisme dynamic UUID resolution di [SyncObserver.php](file:///home/londo/nurisk/app/Observers/SyncObserver.php#L12-L19) sama sekali tidak mendeteksi `uuid_mobilisasi`. 
  ```php
  protected function getUuid(Model $model): string
  {
      if (isset($model->uuid_assessment)) return $model->uuid_assessment;
      if (isset($model->uuid_sitrep)) return $model->uuid_sitrep;
      if (isset($model->uuid_klaster_operasi)) return $model->uuid_klaster_operasi;
      if (isset($model->uuid_penugasan)) return $model->uuid_penugasan;
      return ''; // OperasiMobilisasi akan mengembalikan string kosong!
  }
  ```
  Karena mengembalikan string kosong, observer langsung menghentikan proses (`return 0`) pada saat insert/update/delete. Akibatnya, **tidak ada rekaman `SyncCursor` maupun `SyncTombstone` yang dibuat untuk entitas mobilisasi**. Perubahan data mobilisasi di server tidak akan pernah tersinkronisasi turun (sync down) ke perangkat mobile.
* **Ketidaksesuaian Nama Entitas (Entity Type Mismatch):**
  Jika fungsi UUID di atas diperbaiki, `getEntityType` pada `SyncObserver` akan mengembalikan `'operasimobilisasi'` (mengikuti `class_basename` model). Sedangkan `SyncApiController` mendefinisikannya sebagai `'mobilisasi'`. Perbedaan ini akan merusak sinkronisasi cursor di database.

---

## 8. Testing Findings
* **Coverage Sangat Rendah (Happy Path Only):**
  Cakupan pengujian di `MobilisasiApiTest` dan `MobilisasiSyncTest` sangat minim (Score: **30/100**).
* **Missing Tests:**
  * Tidak ada uji coba otorisasi Lapis 4 untuk pembatasan wilayah (PCNU A mengakses/membuat mobilisasi di wilayah PCNU B).
  * Tidak ada uji coba penarikan data sync down dari server ke mobile client (sehingga bug fatal `SyncObserver` di atas tidak terdeteksi oleh test suite).
  * Tidak ada uji coba soft-delete sync tombstone.
  * Tidak ada pengujian state transitions secara lengkap (depart, arrive, finish, cancel).

---

## 9. Production Readiness Score

| Kategori | Skor (0-100) | Keterangan |
| :--- | :---: | :--- |
| **Architecture** | 80 | Struktur route and controller mengikuti pattern, namun ada bypass middleware auth. |
| **Security** | 40 | Kehilangan otorisasi krusial pada `index()` dan bypass pengecekan insiden pada `store()`. |
| **Database** | 70 | Schema valid tetapi tidak memiliki index FK dan status wajib. |
| **API** | 65 | Response resource baik tetapi masih memaparkan integer user ID. |
| **Offline Sync** | 10 | **FAIL**: Sync down benar-benar mati total karena bug pemetaan UUID di observer. |
| **Testing** | 30 | Hanya menguji skenario dasar (happy path), tidak menguji skenario negatif atau sync down. |
| **Maintainability**| 75 | Kode ditulis rapi namun relasi model `updater` dan `deleter` terlewat. |
| **OVERALL READINESS** | **45 / 100** | **REJECTED FOR PRODUCTION** |

---

## 10. Critical Findings

### 1. SEVERITY: CRITICAL
* **File:** [SyncObserver.php](file:///home/londo/nurisk/app/Observers/SyncObserver.php#L12-L19)
* **Root Cause:** Tidak adanya pengecekan properti `uuid_mobilisasi` dalam method `getUuid()`, serta fallback name `operasimobilisasi` di `getEntityType()` yang tidak sesuai dengan kontrak sync (`mobilisasi`).
* **Impact:** Sinkronisasi arah server-ke-client (sync down) untuk domain mobilisasi rusak total karena server tidak pernah mencatat cursor perubahan.
* **Recommendation:** Tambahkan pengecekan `uuid_mobilisasi` di `getUuid()` dan daftarkan mapping `'operasi_mobilisasi'` / `OperasiMobilisasi::class` ke `'mobilisasi'` di `getEntityType()`.

### 2. SEVERITY: HIGH
* **File:** [MobilisasiApiController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Operasi/MobilisasiApiController.php#L16)
* **Root Cause:** Method `index()` tidak memanggil `$this->authorize('viewAny', OperasiMobilisasi::class)`.
* **Impact:** Kebocoran data mobilisasi secara massal. Pengguna tanpa hak akses atau anonim dapat membaca seluruh data mobilisasi di sistem.
* **Recommendation:** Tambahkan baris `$this->authorize('viewAny', OperasiMobilisasi::class);` di awal method `index()`.

### 3. SEVERITY: HIGH
* **File:** [MobilisasiApiController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Operasi/MobilisasiApiController.php#L47) & [OperasiMobilisasiPolicy.php](file:///home/londo/nurisk/app/Policies/OperasiMobilisasiPolicy.php#L28)
* **Root Cause:** Pengecekan otorisasi pembuatan mobilisasi di `store()` hanya menggunakan policy `create` yang bersifat global (role-based saja) tanpa mengikutsertakan objek `$insiden`.
* **Impact:** IDOR/Bypass Otorisasi Wilayah. PCNU dari wilayah lain dapat membuat mobilisasi untuk insiden yang bukan kewenangannya.
* **Recommendation:** Ubah pemanggilan authorize menjadi `$this->authorize('create', [OperasiMobilisasi::class, $insiden]);` dan perbarui parameter method `create(AuthUser $user, OperasiInsiden $insiden)` pada policy untuk memeriksa `canManageInsiden`.

---

## 11. Recommended Fixes

### Step 1: Perbaikan SyncObserver
Ubah method `getUuid` dan `getEntityType` pada `SyncObserver` agar mengenali `OperasiMobilisasi`:
```php
protected function getUuid(Model $model): string
{
    if (isset($model->uuid_assessment)) return $model->uuid_assessment;
    if (isset($model->uuid_sitrep)) return $model->uuid_sitrep;
    if (isset($model->uuid_klaster_operasi)) return $model->uuid_klaster_operasi;
    if (isset($model->uuid_penugasan)) return $model->uuid_penugasan;
    if (isset($model->uuid_mobilisasi)) return $model->uuid_mobilisasi; // FIX
    return '';
}

protected function getEntityType(Model $model): string
{
    return match (get_class($model)) {
        \App\Models\AssessmentUtama::class => 'assessment',
        \App\Models\OperasiSitrep::class => 'sitrep',
        \App\Models\OperasiKlaster::class => 'klaster',
        \App\Models\OperasiPenugasan::class => 'penugasan',
        \App\Models\OperasiMobilisasi::class => 'mobilisasi', // FIX
        default => strtolower(class_basename($model)),
    };
}
```

### Step 2: Pengamanan API Endpoint & Policy
1. Tambahkan `$this->authorize('viewAny', OperasiMobilisasi::class);` di method `index()`.
2. Sesuaikan logic store dan policy agar memvalidasi kepemilikan insiden:
   * Controller `store()`: `$this->authorize('create', [OperasiMobilisasi::class, $insiden]);`
   * Policy `create()`:
     ```php
     public function create(AuthUser $user, OperasiInsiden $insiden): bool
     {
         return $this->authContext->canManageInsiden($user, $insiden);
     }
     ```

### Step 3: Tambahkan Database Index
Buat migration baru untuk menambahkan index performa pada tabel `operasi_mobilisasi`:
```php
Schema::table('operasi_mobilisasi', function (Blueprint $table) {
    $table->index('id_insiden');
    $table->index('id_pengguna');
    $table->index('status_mobilisasi');
    $table->index('sync_version');
});
```

---

## 12. Final Verdict

**REJECTED**

Sistem saat ini memiliki celah keamanan otorisasi yang fatal serta kegagalan total pada alur sinkronisasi arah bawah (sync down). Modul ini **TIDAK LAYAK** didistribusikan ke tim Flutter atau dideploy ke server produksi sebelum seluruh rekomendasi perbaikan di atas diterapkan dan diverifikasi ulang dengan integration tests yang mencakup kasus-kasus negatif.
