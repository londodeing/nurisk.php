# Domain M10 Mobilisasi Implementation Plan

## Goal Description
Mengimplementasikan Domain M10 (Mobilisasi) sesuai arsitektur Offline-First (ARCH-006) dan UUID Governance (RULE-UUID-001). Implementasi akan memimik pola yang ada di domain Penugasan, di mana entitas dilengkapi dengan UUID, state machine transisi, pengamanan Integer PK dari publik, serta integrasi Sync Cursors.

## User Review Required

> [!IMPORTANT]
> Mohon direview terlebih dahulu sebelum lanjut ke tahap Coding (Langkah 9). Tidak ada migration maupun kode yang akan dibuat sebelum ini di-approve.

> [!WARNING]
> Relasi `id_pengguna` pada domain Mobilisasi tetap menggunakan ID integer asli atau mem-bypass UUID pada input form untuk relawan? Mengacu pada arsitektur yang sudah berjalan, `id_pengguna` (pengguna auth) umumnya didapat secara otomatis via `Auth::id()` pada saat input, namun jika diinput oleh PCNU untuk menugaskan pengguna lain, apakah menggunakan UUID pengguna atau email? (Asumsi saat ini: Meminjam style `Penugasan`, pengikatan via API dapat menggunakan `id_pengguna` jika diizinkan internal atau ditambahkan `uuid_pengguna` di payload request).

## Proposed Changes

### Database Migration
Akan membuat migration tabel `operasi_mobilisasi` dengan struktur:
- `id_mobilisasi` (PK, unsigned big integer)
- `uuid_mobilisasi` (UUID, unique index)
- `id_insiden` (FK ke `operasi_insiden`)
- `id_pengguna` (FK ke `auth_users` atau tabel pengguna terkait)
- `jenis_mobilisasi` (string, mis. 'personil', 'armada', 'logistik')
- `status_mobilisasi` (string default `draft`)
- `lokasi_asal`, `lokasi_tujuan` (string)
- `waktu_berangkat`, `waktu_tiba` (datetime nullable)
- `catatan` (text nullable)
- `sync_version` (bigint default 1)
- `created_by`, `updated_by`, `deleted_by` (FK ke `auth_users`)
- `alasan_hapus` (text nullable)
- timestamps, softDeletes

### Model & Observer
- [NEW] `app/Models/OperasiMobilisasi.php`: Menerapkan auto UUID, increment `sync_version`, relasi ke `OperasiInsiden` (dengan `$with = ['insiden']` agar terhindar dari *lazy-loading exceptions*).
- Update `AppServiceProvider.php` untuk memasangkan `SyncObserver` ke `OperasiMobilisasi` sehingga aksi hapus tercatat di `sync_tombstones`.

### API Resource & Controller
- [NEW] `app/Http/Resources/Operasi/MobilisasiResource.php`: Me-*masking* `id_mobilisasi` menjadi `uuid_mobilisasi` pada output JSON.
- [NEW] `app/Http/Controllers/Api/Operasi/MobilisasiApiController.php`: Mengimplementasikan REST standar (Index, Show, Store, Update, Destroy) dan transisi spesifik (approve, depart, arrive, finish, cancel).

### Requests & Policies
- [NEW] `app/Http/Requests/Operasi/StoreMobilisasiRequest.php`, dkk: Memvalidasi input `uuid_insiden` dan me-*resolve* ke internal ID.
- [NEW] `app/Policies/OperasiMobilisasiPolicy.php`: Validasi Lapis 4 via `AuthorizationContextService`.

### Offline Sync Engine
- [MODIFY] `app/Services/Operasi/SyncService.php`: Mendaftarkan tabel `mobilisasi`, menangani konversi `uuid_insiden` ke `id_insiden` pada saat batch push offline dari perangkat mobile.

### Testing
Akan dibuatkan unit dan feature test komprehensif (`MobilisasiApiTest`, `MobilisasiSyncTest`, `MobilisasiStateMachineTest`) dengan target pass 100% dan zero regression terhadap domain lain.

## Verification Plan

### Automated Tests
- `php vendor/bin/phpunit tests/Feature/Operasi/Mobilisasi/`
- Memastikan `SyncConflictTest` dan pipeline `ARCH-006D` lainnya tidak terdampak.

### Manual Verification
- Melakukan verifikasi JSON payload agar 100% menggunakan `id` berbentuk UUID, bukan auto-increment.
- Memverifikasi endpoint RPC State Machine (mis. `/api/v1/mobilisasi/{uuid}/approve`) dapat mengubah state dan menaikkan `sync_version`.
