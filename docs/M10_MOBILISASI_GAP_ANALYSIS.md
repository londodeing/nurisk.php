# M10 MOBILISASI GAP ANALYSIS

## Arsitektur Saat Ini
NURISK saat ini memiliki arsitektur Offline-First yang didukung oleh:
1. **Sync Versioning & Tombstones**: Menyimpan jejak versi setiap record dan record yang dihapus dalam tabel `sync_tombstones`.
2. **Device Registry & Sync Queues**: Memanajemen perangkat yang aktif mensinkronisasikan data melalui `mobile_devices` dan `mobile_sync_queues`.
3. **Cursor-Based Sync (Per-Entity Cursors)**: Endpoint `/api/v1/sync` menangani pull dan push multi-entity (Sitrep, Klaster, Assessment, Penugasan).
4. **UUID as Public ID**: Menghilangkan paparan Integer PK untuk mitigasi IDOR (ARCH-006D).
5. **Soft Deletes**: Seluruh entitas penting dipertahankan di database untuk kebutuhan audit trail.
6. **Authorization Matrix**: Menggunakan `AuthorizationContextService` untuk mengecek hak akses multi-layered (Role dan Scope unit organisasi).

## Komponen yang Dapat Direuse
Untuk domain M10 (Mobilisasi), komponen yang dapat langsung direuse:
1. **`App\Services\Auth\AuthorizationContextService`**: Pengecekan role pengguna dan otoritas insiden.
2. **`App\Http\Controllers\Api\Operasi\SyncApiController`**: Hanya perlu menambahkan parser untuk `operasi_mobilisasi` dalam payload `changes` dan memprosesnya secara atomic.
3. **`App\Observers\SyncObserver`**: Dapat dipasangkan pada model `OperasiMobilisasi` untuk otomatis mencatat tombstone jika data dihapus.
4. **Pola FormRequest UUID**: Pengecekan relasi `uuid_insiden` dan pengguna.

## Komponen yang Harus Dibuat Baru
1. **Migration `operasi_mobilisasi`**: Termasuk kolom wajib `id_mobilisasi`, `uuid_mobilisasi`, UUID governance constraint, `sync_version`, dan timestamps/softDeletes.
2. **Model `OperasiMobilisasi`**: Meliputi setup `booted()` untuk generate UUID dan integrasi `sync_version`, serta relasi dengan `insiden` (eager-loaded `with = ['insiden']`).
3. **API Controller `MobilisasiApiController`**: Sesuai dengan API_STYLE_GUIDE, mendukung pagination standar dan action transisi state.
4. **Policy `OperasiMobilisasiPolicy`**: Mengikat state machine rules dan context authorization.
5. **API Resource `MobilisasiResource`**: Output contract khusus yang aman (meng-hide Integer ID).
6. **Sync Mapping Service**: Ekstensi `SyncService` untuk memetakan payload array mobilisasi (mengonversi `uuid_insiden` ke `id_insiden` pada internal sync update/insert).

## Risiko Implementasi
- **UUID to Integer Constraint Violation**: Mengingat tabel mobilisasi sangat terikat pada insiden (`id_insiden`) dan pengguna (`id_pengguna`), pemetaan payload sync dari UUID ke internal ID harus atomic dan akurat.
- **Race Condition State Machine**: Transisi status seperti `draft -> disetujui` memiliki potensi bentrok jika dilakukan offline pada saat yang sama di dua perangkat. Perlu ditangani via `sync_version` optimistic locking (yang sudah direuse dari Penugasan).

## Dampak Terhadap Flutter
Mobile engineer harus menyiapkan:
- Local database table `operasi_mobilisasi`.
- Integrasi entity `mobilisasi` di dalam payload `POST /api/v1/sync` (pada struktur `data.changes`).
- Penanganan UI yang merefleksikan state machine transisi dan status HTTP 409 Conflict.
