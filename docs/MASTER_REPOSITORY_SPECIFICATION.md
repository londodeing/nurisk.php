# Master Repository Specification

Phase 2.4 — Local Master Repository (ADR-002)

---

## 1. Tujuan

Membangun satu abstraction layer (`MasterRepository`) yang menjadi satu-satunya pintu akses master data di Flutter. Seluruh domain (Public, COP, Governance, Volunteer, Auth) hanya boleh memanggil `MasterRepository` — tidak boleh tahu apakah data berasal dari JSON, SQLite, cache, atau API.

---

## 2. Struktur Folder

```
lib/core/master/
├── master_repository.dart            # Abstract interface
├── master_repository_impl.dart       # Composite impl (delegates ke sub-repos)
│
├── models/
│   ├── jenis_bencana.dart
│   ├── wilayah.dart
│   ├── keahlian.dart
│   ├── severity.dart
│   ├── prioritas.dart
│   ├── status_laporan.dart
│   ├── status_insiden.dart
│   ├── status_operasi.dart
│   ├── level_risiko.dart
│   ├── skala_kejadian.dart
│   ├── satuan.dart
│   ├── klaster.dart
│   ├── icon_cop.dart
│   ├── warna_indikator.dart
│   ├── resource_jenis.dart
│   ├── kendaraan_jenis.dart
│   ├── shelter_jenis.dart
│   ├── logistik_jenis.dart
│   ├── relawan_jenis.dart
│   ├── surat_jenis.dart
│   ├── jabatan_ttd.dart
│   ├── approval_status.dart
│   ├── workflow_status.dart
│   ├── assessment_indikator.dart
│   ├── assessment_kebutuhan.dart
│   └── pcnu.dart
│
├── repositories/
│   ├── json_master_repository.dart   # Tier A — reads from assets/master/*.json
│   ├── sqlite_master_repository.dart # Tier B — reads from master.db
│   └── organization_repository.dart  # Tier C — API + SQLite cache + TTL
│
├── providers/
│   └── master_providers.dart         # Riverpod providers
│
└── mappers/
    ├── wilayah_mapper.dart           # Wilayah data → unified model
    └── bencana_mapper.dart           # Bencana data → unified model
```

---

## 3. Abstract Interface

```dart
abstract class MasterRepository {
  // Tier A — Immutable (from JSON)
  Future<List<JenisBencana>> getJenisBencana();
  Future<List<Severity>> getSeverity();
  Future<List<Prioritas>> getPrioritas();
  Future<List<StatusLaporan>> getStatusLaporan();
  Future<List<StatusInsiden>> getStatusInsiden();
  Future<List<StatusOperasi>> getStatusOperasi();
  Future<List<LevelRisiko>> getLevelRisiko();
  Future<List<SkalaKejadian>> getSkalaKejadian();
  Future<List<Satuan>> getSatuan();
  Future<List<Klaster>> getKlaster();
  Future<List<IconCop>> getIconCop();
  Future<Map<String, WarnaIndikator>> getWarnaIndikator();
  Future<List<ResourceJenis>> getResourceJenis();
  Future<List<KendaraanJenis>> getKendaraanJenis();
  Future<List<ShelterJenis>> getShelterJenis();
  Future<List<LogistikJenis>> getLogistikJenis();
  Future<List<RelawanJenis>> getRelawanJenis();
  Future<List<SuratJenis>> getSuratJenis();
  Future<List<JabatanTtd>> getJabatanTtd();
  Future<List<ApprovalStatus>> getApprovalStatus();
  Future<Map<String, List<String>>> getWorkflow();
  Future<List<AssessmentIndikator>> getAssessmentIndikator();
  Future<List<AssessmentKebutuhan>> getAssessmentKebutuhan();

  // Tier B — Semi-static (from SQLite)
  Future<List<Kabupaten>> getKabupaten();
  Future<List<Kecamatan>> getKecamatan(String idKab);
  Future<List<Desa>> getDesa(String idKec);

  // Tier C — Organizational (cache + API)
  Future<List<Pcnu>> getPcnuList();
  Future<List<Keahlian>> getKeahlian();
}
```

---

## 4. Unified Models

### 4.1 Migration dari model lama

| Model Lama (API) | Model Lama (Local) | Model Baru (Unified) |
|---|---|---|
| `JenisBencanaModel` (report/data/models) | `JenisBencanaMasterModel` (master_data/models) | `JenisBencana` |
| `WilayahModel` (report/data/models) | `WilayahMasterModel` (master_data/models) + `KabupatenData`/`KecamatanData`/`DesaData` (master_database.dart) | `Kabupaten`, `Kecamatan`, `Desa` |

### 4.2 Daftar model

```dart
class JenisBencana {
  final int id;
  final String nama;
  final String slug;
  final String kategori;
  final String ikonMap;
}

class Kabupaten {
  final String idKab;
  final String namaKab;
}

class Kecamatan {
  final String idKec;
  final String idKab;
  final String namaKec;
}

class Desa {
  final String idDesa;
  final String idKec;
  final String namaDesa;
}

class Keahlian {
  final int id;
  final String nama;
  final String deskripsi;
}

class Severity {
  final String id;
  final String nama;
  final int skor;
  final String warna;
}

class Prioritas {
  final String id;
  final String nama;
  final int skor;
  final String warna;
}

class StatusLaporan {
  final String id;
  final String nama;
  final String warna;
  final int urutan;
}

class StatusInsiden {
  final String id;
  final String nama;
  final String warna;
  final int urutan;
}

class StatusOperasi {
  final String id;
  final String nama;
  final String warna;
  final int urutan;
}

class LevelRisiko {
  final String id;
  final String nama;
  final int skor;
  final String warna;
}

class SkalaKejadian {
  final String id;
  final String nama;
  final int level;
}

class Satuan {
  final String id;
  final String nama;
  final String singkatan;
}

class Klaster {
  final int id;
  final String nama;
  final String deskripsi;
}

class IconCop {
  final String slug;
  final String icon;
}

class WarnaIndikator {
  final String bg;
  final String text;
  final String hex;
}

class ResourceJenis {
  final String id;
  final String nama;
}

class KendaraanJenis {
  final int id;
  final String nama;
  final String ikon;
}

class ShelterJenis {
  final int id;
  final String nama;
  final int kapasitas;
}

class LogistikJenis {
  final int id;
  final String nama;
}

class RelawanJenis {
  final int id;
  final String nama;
  final String deskripsi;
}

class SuratJenis {
  final int id;
  final String kode;
  final String nama;
  final String kategori;
}

class JabatanTtd {
  final int id;
  final String nama;
  final int urutan;
}

class ApprovalStatus {
  final String id;
  final String nama;
  final int urutan;
  final String warna;
}

class AssessmentIndikator {
  final String kode;
  final String nama;
  final String domain;
  final int bobot;
  final String satuan;
}

class AssessmentKebutuhan {
  final String kode;
  final String nama;
  final String satuan;
  final String kategori;
}

class Pcnu {
  final int id;
  final String nama;
}
```

---

## 5. Implementasi

### 5.1 JsonMasterRepository (Tier A)

```
Lokasi: lib/core/master/repositories/json_master_repository.dart
Sumber: assets/master/*.json
Cache: in-memory (Map atau list)
Inisialisasi: lazy (baca saat pertama kali dipanggil)
```

Method yang diimplementasikan:
- `getJenisBencana()` → `assets/master/bencana/jenis.json`
- `getSeverity()` → `assets/master/severity/master.json`
- `getPrioritas()` → `assets/master/prioritas.json`
- `getStatusLaporan()` → `assets/master/status/laporan.json`
- `getStatusInsiden()` → `assets/master/status/insiden.json`
- `getStatusOperasi()` → `assets/master/status/operasi.json`
- `getLevelRisiko()` → `assets/master/level_risiko.json`
- `getSkalaKejadian()` → `assets/master/skala_kejadian.json`
- `getSatuan()` → `assets/master/satuan.json`
- `getKlaster()` → `assets/master/klaster/data.json`
- `getIconCop()` → `assets/master/icon_cop/data.json`
- `getWarnaIndikator()` → `assets/master/warna_indikator.json`
- `getResourceJenis()` → `assets/master/resource/jenis.json`
- `getKendaraanJenis()` → `assets/master/kendaraan/jenis.json`
- `getShelterJenis()` → `assets/master/shelter/jenis.json`
- `getLogistikJenis()` → `assets/master/logistik/jenis.json`
- `getKeahlian()` → `assets/master/relawan/jenis.json`
- `getSuratJenis()` → `assets/master/surat/jenis.json`
- `getJabatanTtd()` → `assets/master/surat/jabatan_ttd.json`
- `getApprovalStatus()` → `assets/master/approval/status.json`
- `getWorkflow()` → `assets/master/workflow/enum.json`
- `getAssessmentIndikator()` → `assets/master/assessment/indikator.json`
- `getAssessmentKebutuhan()` → `assets/master/assessment/kebutuhan_numerik.json`

### 5.2 SQLiteMasterRepository (Tier B)

```
Lokasi: lib/core/master/repositories/sqlite_master_repository.dart
Sumber: assets/master/wilayah/master.db (di-copy ke documents dir)
Cache: Drift NativeDatabase (read-only)
Inisialisasi: async (copy db file + open connection)
```

Method yang diimplementasikan:
- `getKabupaten()` → `SELECT * FROM kabupaten ORDER BY nama_kab`
- `getKecamatan(idKab)` → `SELECT * FROM kecamatan WHERE id_kab = ? ORDER BY nama_kec`
- `getDesa(idKec)` → `SELECT * FROM desa WHERE id_kec = ? ORDER BY nama_desa`

### 5.3 OrganizationRepository (Tier C)

```
Lokasi: lib/core/master/repositories/organization_repository.dart
Sumber: API (dengan cache SQLite + TTL)
Cache: TTL 24 jam untuk PCNU, 24 jam untuk role/jabatan
Fallback: cache tetap dipakai setelah TTL expired jika offline
```

Method yang diimplementasikan:
- `getPcnuList()` → API `GET /wilayah/pcnu` → cache → TTL 24h

---

## 6. MasterRepositoryImpl (Composite)

```dart
class MasterRepositoryImpl implements MasterRepository {
  final JsonMasterRepository jsonRepo;
  final SQLiteMasterRepository sqliteRepo;
  final OrganizationRepository orgRepo;

  MasterRepositoryImpl({
    required this.jsonRepo,
    required this.sqliteRepo,
    required this.orgRepo,
  });

  // Delegasi ke sub-repository
  Future<List<JenisBencana>> getJenisBencana() => jsonRepo.getJenisBencana();
  Future<List<Kabupaten>> getKabupaten() => sqliteRepo.getKabupaten();
  Future<List<Pcnu>> getPcnuList() => orgRepo.getPcnuList();
  // ... dan seterusnya
}
```

---

## 7. Riverpod Providers

```dart
// === Core Providers ===
final jsonMasterRepositoryProvider = Provider<JsonMasterRepository>((ref) {
  return JsonMasterRepository();
});

final sqliteMasterRepositoryProvider = Provider<SQLiteMasterRepository>((ref) {
  return SQLiteMasterRepository();
});

final sqliteMasterInitProvider = FutureProvider<void>((ref) async {
  await ref.read(sqliteMasterRepositoryProvider).init();
});

final organizationRepositoryProvider = Provider<OrganizationRepository>((ref) {
  return OrganizationRepository(ref.watch(publicApiClientProvider));
});

final masterRepositoryProvider = Provider<MasterRepository>((ref) {
  return MasterRepositoryImpl(
    jsonRepo: ref.watch(jsonMasterRepositoryProvider),
    sqliteRepo: ref.watch(sqliteMasterRepositoryProvider),
    orgRepo: ref.watch(organizationRepositoryProvider),
  );
});

// === Data Providers ===
final jenisBencanaProvider = FutureProvider<List<JenisBencana>>((ref) {
  return ref.read(masterRepositoryProvider).getJenisBencana();
});

final kabupatenProvider = FutureProvider<List<Kabupaten>>((ref) async {
  await ref.read(sqliteMasterInitProvider.future);
  return ref.read(masterRepositoryProvider).getKabupaten();
});

final kecamatanProvider = FutureProvider.family<List<Kecamatan>, String>((ref, idKab) async {
  await ref.read(sqliteMasterInitProvider.future);
  return ref.read(masterRepositoryProvider).getKecamatan(idKab);
});

final desaProvider = FutureProvider.family<List<Desa>, String>((ref, idKec) async {
  await ref.read(sqliteMasterInitProvider.future);
  return ref.read(masterRepositoryProvider).getDesa(idKec);
});

final pcnuListProvider = FutureProvider<List<Pcnu>>((ref) {
  return ref.read(masterRepositoryProvider).getPcnuList();
});

final keahlianProvider = FutureProvider<List<Keahlian>>((ref) {
  return ref.read(masterRepositoryProvider).getKeahlian();
});

// ... dan seterusnya untuk setiap entity
```

---

## 8. Mapper

### 8.1 WilayahMapper

Memetakan dari format SQLite ke unified model:

```dart
class WilayahMapper {
  static Kabupaten kabupatenFromMap(Map<String, dynamic> json) {
    return Kabupaten(idKab: json['id_kab'], namaKab: json['nama_kab']);
  }
  static Kecamatan kecamatanFromMap(Map<String, dynamic> json) {
    return Kecamatan(idKec: json['id_kec'], idKab: json['id_kab'], namaKec: json['nama_kec']);
  }
  static Desa desaFromMap(Map<String, dynamic> json) {
    return Desa(idDesa: json['id_desa'], idKec: json['id_kec'], namaDesa: json['nama_desa']);
  }
}
```

### 8.2 BencanaMapper

Memetakan dari format JSON asset ke unified model:

```dart
class BencanaMapper {
  static JenisBencana fromJson(Map<String, dynamic> json) {
    return JenisBencana(
      id: json['id'], nama: json['nama'], slug: json['slug'],
      kategori: json['kategori'], ikonMap: json['ikon_map'],
    );
  }
}
```

---

## 9. Dependency Graph

```
masterRepositoryProvider
  ├── jsonMasterRepositoryProvider      (Tier A)
  │     └── rootBundle (flutter services)
  │
  ├── sqliteMasterRepositoryProvider    (Tier B)
  │     ├── sqliteMasterInitProvider
  │     └── NativeDatabase (drift)
  │
  └── organizationRepositoryProvider    (Tier C)
        ├── publicApiClientProvider (dio)
        └── SharedPreferences (TTL)
```

---

## 10. Cache Policy

| Data | Tier | Cache Type | TTL | Offline Fallback |
|------|------|------------|-----|------------------|
| JenisBencana | A | In-memory | Permanent (per session) | ✅ (bundled) |
| Severity | A | In-memory | Permanent | ✅ |
| Status | A | In-memory | Permanent | ✅ |
| Prioritas | A | In-memory | Permanent | ✅ |
| Warna | A | In-memory | Permanent | ✅ |
| Icon COP | A | In-memory | Permanent | ✅ |
| Klaster | A | In-memory | Permanent | ✅ |
| RelawanJenis | A | In-memory | Permanent | ✅ |
| Keahlian | A | In-memory | Permanent | ✅ |
| SuratJenis | A | In-memory | Permanent | ✅ |
| Satuan | A | In-memory | Permanent | ✅ |
| Workflow | A | In-memory | Permanent | ✅ |
| Assessment | A | In-memory | Permanent | ✅ |
| Kabupaten | B | SQLite | Snapshot version | ✅ (bundled) |
| Kecamatan | B | SQLite | Snapshot version | ✅ |
| Desa | B | SQLite | Snapshot version | ✅ |
| PCNU | C | SQLite + TTL | 24 jam | ✅ (cache) |
| Role | C | SQLite + TTL | 24 jam | ✅ (cache) |

---

## 11. Acceptance Criteria

Setelah Phase 2.4 selesai:

```
✓ lib/core/master/ folder exists dengan struktur di atas
✓ MasterRepository abstract class selesai
✓ MasterRepositoryImpl selesai (delegasi ke sub-repos)
✓ JsonMasterRepository selesai (semua Tier A method)
✓ SQLiteMasterRepository selesai (semua Tier B method)
✓ OrganizationRepository selesai (Tier C PCNU + TTL)
✓ Semua model master disatukan (tidak ada duplikasi)
✓ Riverpod providers selesai
✓ Flutter analyze tidak ada error
✗ BELUM ada perubahan pada domain lain (register, validation, etc.)
```

#### Larangan di feature layer:

```
lib/features/** TIDAK BOLEH mengandung:

import 'package:dio/dio.dart'
import 'package:drift/drift.dart'
import 'package:drift/native.dart'
import 'package:flutter/services.dart' (rootBundle)

KECUALI untuk:

master_repository.dart
master_providers.dart
```

Feature layer hanya boleh:

```dart
import 'package:nurisk_mobile/core/master/master_repository.dart';
import 'package:nurisk_mobile/core/master/providers/master_providers.dart';
```

---

## 12. Gate to Phase 2.5

Phase 2.5 (Migration) hanya boleh dimulai setelah dokumen ini disetujui dan seluruh implementasi Phase 2.4 sudah:
1. Tidak ada error flutter analyze
2. Tidak ada duplikasi model
3. Semua provider bisa di-instantiate
4. Feature layer belum diubah (acceptance criteria terpenuhi)
