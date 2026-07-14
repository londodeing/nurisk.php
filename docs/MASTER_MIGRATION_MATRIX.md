# Master Migration Matrix

Berdasarkan Phase 2.3 — Master Migration Audit (ADR-002)

---

## Ringkasan

Audit menemukan **10 endpoint master** masih dipanggil via API, **25+ file JSON** belum memiliki konsumen Dart, dan **2 duplikasi model class** yang perlu di-unifikasi.

---

## Prioritas 1 — Critical (Must Migrate)

Endpoint master yang masih dipanggil via API, padahal data sudah tersedia secara lokal.

| # | Domain | File | Line | Endpoint | Tier | Lokal Tersedia? |
|---|--------|------|------|----------|------|-----------------|
| 1 | Public/Report | `laporan_remote_datasource.dart` | 112 | `GET /master/jenis-bencana` | A | ✅ JSON asset |
| 2 | Public/Report | `laporan_remote_datasource.dart` | 124 | `GET /wilayah/kabupaten` | B | ✅ SQLite master.db |
| 3 | Public/Report | `laporan_remote_datasource.dart` | 136 | `GET /wilayah/kecamatan` | B | ✅ SQLite master.db |
| 4 | Public/Report | `laporan_remote_datasource.dart` | 148 | `GET /wilayah/desa` | B | ✅ SQLite master.db |
| 5 | Auth/Register | `register_screen.dart` | 65 | `GET /wilayah/kabupaten` | B | ✅ SQLite master.db |
| 6 | Auth/Register | `register_screen.dart` | 90 | `GET /wilayah/kecamatan` | B | ✅ SQLite master.db |
| 7 | Auth/Register | `register_screen.dart` | 113 | `GET /wilayah/desa` | B | ✅ SQLite master.db |
| 8 | Auth/Register | `register_screen.dart` | 133 | `GET /keahlian` | A | ✅ JSON asset (`relawan/jenis.json`) |
| 9 | Auth/Register | `register_screen.dart` | 138 | `GET /wilayah/pcnu` | C | ❌ Perlu Tier C cache |
| 10 | Public/Report | `report_validation_list_screen.dart` | 32 | `GET /wilayah/pcnu` | C | ❌ Perlu Tier C cache |

**Status:** #1-4 sudah dimigrasi (Report Wizard pakai lokal), tapi method di datasource masih ada.

---

## Prioritas 2 — Medium (Config & Operational Reference)

Data yang bisa di-cache lokal dengan TTL atau stale-while-revalidate.

| # | Domain | File | Endpoint | Tipe | Rekomendasi |
|---|--------|------|----------|------|-------------|
| 11 | Public/Config | `config_remote_datasource.dart` | `GET /dashboard/config` | Config | Cache lokal, TTL 24 jam |
| 12 | Public/Map | `map_layer_datasource.dart` | `GET /public/map/config` | Config | Cache lokal, TTL 6 jam |
| 13 | Public/Map | `map_layer_datasource.dart` | `GET /public/map/operational/{layerId}` | Operational | Sudah benar (data operasional real-time) |
| 14 | Public/Map | `layer_control_bottom_sheet.dart` | `GET /public/map/config` | Config | Sama dengan #12 |
| 15 | Governance | `governance_provider.dart` | `GET /governance/pending` | Operational | Cache lokal, TTL 5 menit |

---

## Prioritas 3 — Low (Unconsumed Assets)

File JSON yang sudah ada di `assets/master/` tetapi belum memiliki model Dart atau konsumen.

| # | Asset Path | Data Domain | Butuh Model? | Butuh Loader? | Konsumen Potensial |
|---|------------|-------------|--------------|---------------|-------------------|
| 1 | `assets/master/severity/master.json` | Severity | ✅ | ✅ | Dashboard, Assessment |
| 2 | `assets/master/prioritas.json` | Prioritas | ✅ | ✅ | Operasi, Approval |
| 3 | `assets/master/status/laporan.json` | Status Laporan | ✅ | ✅ | Report, Tracking |
| 4 | `assets/master/status/insiden.json` | Status Insiden | ✅ | ✅ | COP Map, Dashboard |
| 5 | `assets/master/status/operasi.json` | Status Operasi | ✅ | ✅ | Operasi |
| 6 | `assets/master/level_risiko.json` | Level Risiko | ✅ | ✅ | Assessment |
| 7 | `assets/master/skala_kejadian.json` | Skala Kejadian | ✅ | ✅ | Assessment |
| 8 | `assets/master/satuan.json` | Satuan | ✅ | ✅ | Logistik, Assessment |
| 9 | `assets/master/warna_indikator.json` | Warna Indikator | ✅ | ✅ | COP Map, Dashboard |
| 10 | `assets/master/workflow/enum.json` | Workflow | ✅ | ✅ | Governance |
| 11 | `assets/master/klaster/data.json` | Klaster | ✅ | ✅ | Operasi |
| 12 | `assets/master/icon_cop/data.json` | Icon COP | ✅ | ✅ | COP Map |
| 13 | `assets/master/relawan/jenis.json` | Jenis Relawan | ✅ | ✅ | Relawan, Register |
| 14 | `assets/master/resource/jenis.json` | Jenis Resource | ✅ | ✅ | Resource |
| 15 | `assets/master/kendaraan/jenis.json` | Jenis Kendaraan | ✅ | ✅ | Aset, Inventaris |
| 16 | `assets/master/shelter/jenis.json` | Jenis Shelter | ✅ | ✅ | Pengungsian |
| 17 | `assets/master/logistik/jenis.json` | Jenis Logistik | ✅ | ✅ | Logistik |
| 18 | `assets/master/surat/jenis.json` | Jenis Surat | ✅ | ✅ | Governance/Surat |
| 19 | `assets/master/surat/jabatan_ttd.json` | Jabatan TTD | ✅ | ✅ | Governance/Surat |
| 20 | `assets/master/approval/status.json` | Status Approval | ✅ | ✅ | Governance |
| 21 | `assets/master/assessment/indikator.json` | Indikator Assessment | ✅ | ✅ | Assessment |
| 22 | `assets/master/assessment/kebutuhan_numerik.json` | Kebutuhan Numerik | ✅ | ✅ | Assessment |
| 23 | `assets/master/assessment/indikator_skor_panduan.json` | Panduan Skor | ✅ | ✅ | Assessment |

---

## Duplikasi Model Class

| Model | File (Lama — API) | File (Baru — Local) | Tindakan |
|-------|-------------------|---------------------|----------|
| JenisBencanaModel | `features/.../report/data/models/jenis_bencana_model.dart` | `core/services/master_data/models/jenis_bencana_model.dart` | Unifikasi: pindahkan ke core, hapus yang lama |
| WilayahModel | `features/.../report/data/models/wilayah_model.dart` | `core/services/master_data/models/wilayah_model.dart` | Unifikasi: pindahkan ke core, hapus yang lama |

---

## Revised Roadmap

```
ADR-002
  │
  ▼
Phase 2.3 — Audit ✅ (SELESAI)
  │
  ▼
Phase 2.4 — Local Master Repository
  │  ├── Buat MasterRepository (abstraksi akses master)
  │  ├── BencanaRepository, WilayahRepository, dll.
  │  └── Unifikasi model class
  │
  ▼
Phase 2.5 — Master Migration
  │  ├── [P1] Migrasi register_screen.dart → local providers
  │  ├── [P1] Migrasi report_validation_list_screen.dart → local
  │  ├── [P1] Hapus master methods dari LaporanRemoteDatasource
  │  ├── [P2] Dashboard config cache
  │  └── [P3] Buat Dart models untuk unconsumed assets
  │
  ▼
Phase 2.6 — Legacy API Removal & Offline Validation
  │  ├── Hapus endpoint master lama dari backend
  │  ├── Uji coba offline penuh (airplane mode)
  │  └── Validasi semua domain tanpa internet
  │
  ▼
Phase 3 — MasterVersionService & Delta Update
```

---

## Progress Tracker

| Fase | Item | Status |
|------|------|--------|
| 2.3 | Audit laporan_remote_datasource.dart | ✅ |
| 2.3 | Audit register_screen.dart | ✅ |
| 2.3 | Audit report_validation_list_screen.dart | ✅ |
| 2.3 | Audit config / map / governance | ✅ |
| 2.3 | Klasifikasi unconsumed assets | ✅ |
| 2.4 | MasterRepository abstraction | ⏳ |
| 2.4 | Unifikasi model class | ⏳ |
| 2.5 | [P1] Register screen migration | ⏳ |
| 2.5 | [P1] Validation list migration | ⏳ |
| 2.5 | [P1] Remote datasource cleanup | ⏳ |
| 2.5 | [P2] Config cache | ⏳ |
| 2.5 | [P3] Unconsumed models | ⏳ |
| 2.6 | Backend endpoint removal | ⏳ |
| 2.6 | Offline validation | ⏳ |
