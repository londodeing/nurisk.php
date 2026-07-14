# ADR-002: Master Data Strategy & Offline-First Architecture

## Status
Proposed

## Context & Problem Statement
NURISK ditargetkan sebagai **offline-first disaster platform** yang harus tetap berfungsi di daerah tanpa koneksi internet. Saat ini, **semua data master** (jenis bencana, wilayah, organisasi, dsb.) diambil melalui REST API setiap kali dibutuhkan. Pendekatan ini memiliki beberapa masalah serius:

1. **Startup aplikasi selalu bergantung internet** — tanpa koneksi, aplikasi tidak bisa memuat data dasar.
2. **Report Wizard lambat** — harus menunggu 4 request berurutan (provinsi → kabupaten → kecamatan → desa).
3. **Offline mode setengah matang** — cache data master tidak terstruktur, hanya mengandalkan `SharedPreferences` atau Drift cache per-fitur.
4. **Backend menerima waste request** — data yang hampir tidak pernah berubah diminta berulang kali, membuang bandwidth dan CPU.
5. **Flutter sulit diuji secara offline** — harus menghidupkan backend hanya untuk mendapatkan daftar jenis bencana.

Di sisi lain, memasukkan **semua** data ke Flutter juga salah karena data organisasi (role, jabatan, permission, struktur organisasi) bisa berubah setiap hari dan harus sinkron dengan backend.

Kita membutuhkan klasifikasi tegas dan strategi penyimpanan yang berbeda untuk setiap kategori data master.

## Decision
Kami menetapkan **4-Tier Master Data Classification** dan mengubah arsitektur data NURISK dari "semua dari API" menjadi "API untuk data operasional + lokal-first untuk data master".

---

## Tier Classification

### Tier A — Immutable Master (Bundled in APK)
Data yang hampir **tidak pernah berubah** setelah aplikasi dirilis. Ditambahkan sebagai **JSON assets** di Flutter, langsung dibaca dari file tanpa API.

#### Daftar Lengkap Tier A:

| No | Data | Format | Keterangan |
|----|------|--------|------------|
| 1 | Jenis Bencana | `assets/master/bencana/jenis.json` | 13 jenis (Banjir, Gempa, dll.) |
| 2 | Tingkat Severity | `assets/master/severity.json` | minor, sedang, signifikan, berat, katastrofik |
| 3 | Status Laporan | `assets/master/status/laporan.json` | menunggu, ya, tidak |
| 4 | Status Insiden | `assets/master/status/insiden.json` | draft, terverifikasi, respon, pemulihan, selesai, dibatalkan |
| 5 | Status Operasi | `assets/master/status/operasi.json` | monitoring, siaga, tanggap_darurat, pemulihan, selesai |
| 6 | Prioritas | `assets/master/prioritas.json` | rendah, sedang, tinggi, kritis |
| 7 | Warna Indikator | `assets/master/warna_indikator.json` | mapping status → warna hex/CSS |
| 8 | Icon COP | `assets/master/icon_cop.json` | mapping jenis bencana → icon |
| 9 | Jenis Resource | `assets/master/resource/jenis.json` | kategori sumber daya |
| 10 | Jenis Kendaraan | `assets/master/kendaraan/jenis.json` | dari inventaris_jenis (Ambulans, dll.) |
| 11 | Jenis Shelter | `assets/master/shelter/jenis.json` | dari logistik_kategori |
| 12 | Jenis Logistik | `assets/master/logistik/jenis.json` | kategori logistik (Pangan, Sandang, dll.) |
| 13 | Jenis Relawan | `assets/master/relawan/jenis.json` | keahlian master (SAR, Medis, dll.) |
| 14 | Satuan | `assets/master/satuan.json` | kg, unit, paket, liter, dus, dll. |
| 15 | Klaster | `assets/master/klaster.json` | Kesehatan, Logistik, SAR, dll. |
| 16 | Skala Kejadian | `assets/master/skala_kejadian.json` | lokal, kecamatan, kabupaten, provinsi, nasional |
| 17 | Level Risiko | `assets/master/level_risiko.json` | sangat_rendah, rendah, sedang, tinggi, sangat_tinggi |
| 18 | Indikator Assessment | `assets/master/assessment/indikator.json` | master indikator skor penilaian |
| 19 | Kebutuhan Numerik | `assets/master/assessment/kebutuhan_numerik.json` | item kebutuhan standar (sembako, beras, dll.) |
| 20 | Jenis Surat | `assets/master/surat/jenis.json` | SK, ST, SE, SU |
| 21 | Jabatan Penandatangan | `assets/master/surat/jabatan_ttd.json` | Ketua PCNU, Sekretaris, dll. |
| 22 | Status Approval | `assets/master/approval/status.json` | PENDING, APPROVED, REJECTED, REVISION |
| 23 | Status Timeline | `assets/master/timeline/status.json` | draft, terverifikasi, respon, dll. |
| 24 | Enum Workflow | `assets/master/workflow/enum.json` | berbagai status workflow |

#### Aturan Tier A:
- Disimpan sebagai file JSON di `assets/master/*.json`
- Flutter membaca langsung dari `rootBundle.loadString()`
- **Tidak ada endpoint API** untuk data Tier A
- Update hanya melalui rilis APK baru
- Setiap JSON memiliki schema version

---

### Tier B — Semi-Static Master (Bundled Snapshot + Delta Update)
Data yang **jarang berubah** (bulanan/tahunan). APK membawa snapshot SQLite. Backend hanya memberitahu versi terbaru.

#### Daftar Lengkap Tier B:

| No | Data | Format | Keterangan |
|----|------|--------|------------|
| 1 | Provinsi | `assets/master/wilayah/provinsi.json` | disimpan di master.db |
| 2 | Kabupaten | `assets/master/wilayah/kabupaten.json` | 35 kab/kota Jateng |
| 3 | Kecamatan | `assets/master/wilayah/kecamatan.json` | 576 kecamatan |
| 4 | Desa | `assets/master/wilayah/desa.json` | 8.577 desa |
| 5 | Kode Wilayah Kemendagri | termasuk dalam ID | embedded di id_kab, id_kec, id_desa |
| 6 | Polygon Administrasi | `assets/vector/admin.mbtiles` | GeoPackage untuk MapLibre |

#### Aturan Tier B:
- APK membawa **snapshot SQLite** (`assets/master/master.db`) berisi tabel `provinsi`, `kabupaten`, `kecamatan`, `desa`
- Backend menyediakan endpoint:

```
GET /api/master/version
Response: { "version": "2026.07.01" }
```

- Saat startup, Flutter membandingkan `local_version` (SharedPrefs) dengan `remote_version`
- Jika berbeda, Flutter mendownload delta:

```
GET /api/master/delta?from=2026.07.01&to=2026.07.15
Response: { "updates": [...], "deletes": [...] }
```

- Karena sifat data yang jarang berubah, **delta bersifat opsional** — aplikasi tetap berfungsi dengan snapshot lama
- Update hanya perlu dilakukan ketika data snapshot sudah >3 bulan outdated

---

### Tier C — Organizational Master (API + SQLite Cache + TTL)
Data yang **tidak boleh dibundle** karena bisa berubah setiap hari. Tetap diambil dari API, tetapi di-cache di SQLite lokal dengan TTL.

#### Daftar Lengkap Tier C:

| No | Data | TTL | Catatan |
|----|------|-----|---------|
| 1 | Role / Peran | 24 jam | `auth_roles` |
| 2 | Jabatan Posisi | 24 jam | `master_jabatan` (ketua, sekretaris, dll.) |
| 3 | Struktur Organisasi | 24 jam | `organisasi_unit` (PWNU, PCNU, MWC, Ranting) |
| 4 | Pengurus | 24 jam | `organisasi_pengurus` |
| 5 | Mandat | 24 jam | `auth_mandat` |
| 6 | Posko Aktif | 6 jam | `operasi_posko` |
| 7 | PCNU | 24 jam | PCNU list untuk scope selector |
| 8 | MWC | 24 jam | per PCNU |
| 9 | Ranting | 24 jam | per MWC |
| 10 | Inventaris Kategori | 24 jam | `inventaris_kategori` |
| 11 | Inventaris Jenis | 24 jam | `inventaris_jenis` |

#### Aturan Tier C:
- Data diambil via API saat pertama kali dibutuhkan
- Disimpan di SQLite lokal (`nurisk_master.db`) dengan timestamp TTL
- Sebelum expiry, aplikasi membaca dari cache lokal — **tanpa internet**
- Setelah expiry, background fetch update
- TTL di-reset setiap kali aplikasi berhasil sinkronisasi data tersebut
- Jika offline saat TTL expired, cache tetap digunakan (stale-while-revalidate)

---

### Tier D — Operational Data (API + SQLite Projection)
Data transaksional yang **tidak pernah dibundle**. Selalu dari API, dengan proyeksi offline di SQLite.

#### Daftar Lengkap Tier D:

| No | Data | Sinkronisasi |
|----|------|-------------|
| 1 | Laporan Kejadian | realtime / bulk sync |
| 2 | Insiden | realtime / bulk sync |
| 3 | Assessment | realtime / bulk sync |
| 4 | Relawan & Penugasan | realtime / bulk sync |
| 5 | Approval & Keputusan | realtime / bulk sync |
| 6 | Timeline & Riwayat | realtime / bulk sync |
| 7 | Dashboard KPI | cache-then-network |
| 8 | Weather | cache-then-network |

#### Aturan Tier D:
- Sesuai dengan ADR-001, ADR-006, ADR-006A/B/C, ADR-007 (Offline Sync Governance) yang sudah ada
- Tidak ada perubahan pada strategi ini
- Sync menggunakan antrian operasi offline + bulk endpoint `POST /api/v1/sync`

---

## Architecture Change

### Before (Current)
```
Flutter App
  │
  ├── API (semua data)
  │     ├── GET /master/jenis-bencana
  │     ├── GET /master/jabatan
  │     ├── GET /wilayah/kabupaten
  │     ├── GET /wilayah/kecamatan
  │     ├── GET /wilayah/desa
  │     └── ...
  │
  └── Drift Cache (per-fitur, tidak terstruktur)
        ├── WeatherCache
        ├── IncidentCache
        └── DashboardKPICache
```

### After (Target)
```
Flutter App
  │
  ├── Immutable Assets
  │     ├── assets/master/bencana/jenis.json  (Tier A)
  │     ├── assets/master/status/laporan.json  (Tier A)
  │     └── assets/master/...                   (Tier A)
  │
  ├── Local SQLite Master DB (Tier B)
  │     ├── provinsi
  │     ├── kabupaten
  │     ├── kecamatan
  │     └── desa
  │
  ├── Runtime Cache SQLite (Tier C)
  │     ├── auth_roles (TTL: 24h)
  │     ├── organisasi_unit (TTL: 24h)
  │     ├── organisasi_pengurus (TTL: 24h)
  │     ├── posko_aktif (TTL: 6h)
  │     └── auth_mandat (TTL: 24h)
  │
  └── API (Operational Only — Tier D)
        ├── POST /lapor
        ├── POST /api/v1/sync
        ├── PATCH /laporan/{id}/validasi
        ├── GET /api/master/version
        ├── GET /api/master/delta
        └── ...
```

### Flutter Runtime Layer Architecture
```
Runtime Layer
        │
        ▼
Master Data Service
  ├── Tier A: JsonMasterLoader  (rootBundle → memory cache)
  ├── Tier B: SqliteMasterLoader (master.db → Drift query)
  └── Tier C: CachedMasterLoader (API → Drift → TTL check)
        │
        ▼
SQLite Master (nurisk_master.db)
  ├── master_tier_a (optional — bisa dari JSON)
  ├── master_tier_b (provinsi, kabupaten, kecamatan, desa)
  └── master_tier_c (role, jabatan, organisasi, dll.)
        │
        ▼
SQLite Projection (nurisk_public.db)
  ├── WeatherCache
  ├── IncidentCache
  ├── DashboardKPICache
  └── ...
        │
        ▼
Operational API (Dio)
  ├── Tier D endpoints
  └── Master version check
```

---

## Flutter Implementation Plan

### 1. Assets Structure
```
mobile/app/assets/master/
├── bencana/
│   └── jenis.json
├── status/
│   ├── laporan.json
│   ├── insiden.json
│   └── operasi.json
├── severity.json
├── prioritas.json
├── warna_indikator.json
├── icon_cop.json
├── satuan.json
├── klaster.json
├── skala_kejadian.json
├── level_risiko.json
├── resource/
│   └── jenis.json
├── kendaraan/
│   └── jenis.json
├── shelter/
│   └── jenis.json
├── logistik/
│   └── jenis.json
├── relawan/
│   └── jenis.json
├── assessment/
│   ├── indikator.json
│   └── kebutuhan_numerik.json
├── surat/
│   ├── jenis.json
│   └── jabatan_ttd.json
├── approval/
│   └── status.json
├── workflow/
│   └── enum.json
├── wilayah/
│   ├── master.db (SQLite — provinsi, kabupaten, kecamatan, desa)
│   └── provinsi.json (optional — bisa dari SQLite)
└── version.json
```

### 2. JsonMasterLoader
```dart
class JsonMasterLoader {
  Future<T> load<T>(String path, T Function(Map<String, dynamic>) fromJson);
  Future<List<T>> loadList<T>(String path, T Function(Map<String, dynamic>) fromJson);
}
```

### 3. Drift MasterDatabase
```dart
@DriftDatabase(tables: [Provinsi, Kabupaten, Kecamatan, Desa,
                         RoleCache, JabatanCache, OrganisasiUnitCache])
class MasterDatabase extends _$MasterDatabase {
  // Local queries for Tier B
  Future<List<Kabupaten>> getKabupaten();
  Future<List<Kecamatan>> getKecamatan(String idKab);
  Future<List<Desa>> getDesa(String idKec);

  // Tier C with TTL
  Future<List<RoleCache>> getRoles();  // checks TTL internally
  Future<void> upsertRoles(List<RoleCache> roles);
}
```

### 4. MasterVersionService
```dart
class MasterVersionService {
  Future<String> getLocalVersion();    // from SharedPreferences
  Future<String> getRemoteVersion();   // GET /api/master/version
  Future<void> checkAndUpdate();       // compare + download delta
}
```

---

## Backend Changes

### New Endpoints

```php
// ============================================================
// API — Master Version & Delta
// ============================================================
Route::prefix('master')->name('api.master.')->group(function () {
    Route::get('version', [MasterVersionController::class, 'version']);
    Route::get('delta', [MasterVersionController::class, 'delta']);
});
```

### GET /api/master/version
```json
{
  "app_version": "1.0.0",
  "master_version": "2026.07.01",
  "wilayah_version": "2026.07.01",
  "tier_b_tables": {
    "provinsi": {"version": "1.0", "rows": 1},
    "kabupaten": {"version": "1.0", "rows": 35},
    "kecamatan": {"version": "1.0", "rows": 576},
    "desa": {"version": "1.0", "rows": 8577}
  }
}
```

### GET /api/master/delta?from=2026.07.01&to=2026.07.15
```json
{
  "version_from": "2026.07.01",
  "version_to": "2026.07.15",
  "updates": {
    "kabupaten": [...],
    "kecamatan": [...],
    "desa": [...]
  },
  "deletes": {
    "kabupaten": [...],
    "kecamatan": [...],
    "desa": [...]
  }
}
```

### Deprecated Endpoints
Setelah migrasi selesai, endpoint berikut akan dihapus dari backend:

| Endpoint | Tier | Alasan |
|----------|------|--------|
| `GET /api/master/jenis-bencana` | A | Dibundle di APK |
| `GET /api/master/klaster` | A | Dibundle di APK |
| `GET /api/master/jabatan` | C | Pindah ke C + cache |
| `GET /api/master/surat-jenis` | A | Dibundle di APK |
| `GET /api/master/jabatan-ttd` | A | Dibundle di APK |
| `GET /api/master/sertifikasi` | A | Dibundle di APK |
| `GET /api/wilayah/kabupaten` | B | SQLite lokal |
| `GET /api/wilayah/kecamatan` | B | SQLite lokal |
| `GET /api/wilayah/desa` | B | SQLite lokal |

**Catatan:** Penghapusan endpoint dilakukan **bertahap** setelah Flutter sudah sepenuhnya menggunakan sumber data lokal. Endpoint Tier C tetap dipertahankan sebagai source of truth untuk cache.

### Backend MasterDataService Enhancement
`MasterDataService` yang sudah ada akan ditambahkan method untuk versioning dan delta:

```php
class MasterDataService
{
    public function getMasterVersion(): array;
    public function getDelta(string $from, string $to): array;
    public function exportWilayahToSqlite(): string; // generate master.db
}
```

---

## Migration Strategy

### Phase 1 — ADR Approval & Documentation (Sekarang)
- [x] Dokumen ADR-002 ini
- [ ] Socialisasi ke tim

### Phase 2 — Tier A: JSON Assets
1. Export seluruh data Tier A dari database ke file JSON
2. Buat struktur folder `assets/master/`
3. Implementasikan `JsonMasterLoader` service di Flutter
4. Buat unit test untuk setiap JSON
5. Update pubspec.yaml untuk mendaftarkan assets
6. Verifikasi Report Wizard membaca jenis bencana dari lokal

### Phase 3 — Tier B: Wilayah SQLite
1. Buat script artisan `master:export-wilayah` untuk generate master.db
2. Bundle master.db di `assets/master/wilayah/master.db`
3. Implementasikan `MasterDatabase` (Drift) untuk Tier B
4. Implementasikan `MasterVersionService` dengan version check
5. Implementasikan delta download
6. Refactor Report Wizard: dropdown kab/kec/desa dari SQLite lokal
7. Refactor COP Map: polygon administrasi dari GeoPackage lokal

### Phase 4 — Tier C: Cache Layer
1. Buat tabel cache di `MasterDatabase` untuk Tier C
2. Implementasikan TTL-based cache logic
3. Refactor service calls untuk role, jabatan, organisasi agar menggunakan cache
4. Implementasikan background refresh

### Phase 5 — Backend Cleanup
1. Tambahkan endpoint `GET /api/master/version`
2. Tambahkan endpoint `GET /api/master/delta`
3. Deprecate endpoint master yang tidak diperlukan lagi
4. Hapus endpoint setelah migrasi Flutter selesai

### Phase 6 — Testing & Validation
1. Uji coba offline penuh (airplane mode)
2. Uji coba startup dengan berbagai skenario koneksi
3. Uji coba Report Wizard tanpa internet
4. Uji coba COP Map tanpa internet
5. Performance benchmark

---

## Keuntungan

1. **Offline-first**: Aplikasi bisa startup dan digunakan tanpa internet
2. **Report Wizard instant**: dropdown wilayah <10ms (SQLite) vs 500ms-2s (API)
3. **Backend lebih ringan**: tidak ada waste request untuk data statis
4. **Testing lebih mudah**: Flutter bisa diuji tanpa backend
5. **UX lebih baik**: tidak ada loading spinner untuk data master
6. **Bandwidth hemat**: tidak perlu download 8.577 desa setiap kali

## Risiko & Mitigasi

| Risiko | Mitigasi |
|--------|----------|
| Data Tier A perlu update di luar rilis APK | Gunakan Tier C untuk data yang masih bisa berubah; Tier A benar-benar hanya untuk immutable |
| Wilayah berubah (pemekaran) | Backend tetap menjadi source of truth via version/delta |
| APK size membesar | master.db compressed (~2MB untuk 8.577 desa), JSON kecil |
| Version mismatch antara APK dan backend | Backend tetap serve data Tier A via API sebagai fallback |
| TTL cache basi saat offline | Stale-while-revalidate: cache tetap dipakai setelah TTL expired jika offline |

## Daftar Pustaka
- ADR-001: Assessment Endpoint Architecture
- ADR-006: Offline Sync & Mobile Governance
- ADR-006A: Tombstone Sync
- ADR-006B: Device Registry
- ADR-006C: Sync Cursor Strategy
- ADR-007: Mobile Sync Queue
