# NURISK MOBILE — MODULE CONTRACT
## Document 18: Public Module Development Constitution
**Version**: 1.0.0 | **Status**: APPROVED | **Domain**: Public Layer  
**Author**: Enterprise Mobile Solution Architect

---

## 1. PREAMBLE

Dokumen ini berfungsi sebagai **Konstitusi Pengembangan (Module Contract)** untuk seluruh modul di dalam NURISK Mobile (dimulai dari Public Domain, hingga ke Governance dan Operasi nantinya). 

Tujuan konstitusi ini adalah memastikan **konsistensi**, **independensi penuh**, dan **skalabilitas** dari setiap fitur sehingga NURISK tidak pernah berakhir menjadi aplikasi monolith yang sulit di-maintain.

---

## 2. MODULAR INDEPENDENCE & STRUCTURE

Setiap modul (misalnya `weather`, `warning`, `incident`) diperlakukan sebagai *mini-application* atau *package* tersendiri. 

### 2.1 WAJIB memiliki struktur direktori:
```
features/public/<module_name>/
├── data/
│   ├── datasources/
│   ├── models/
│   └── repositories/ (impl)
├── domain/
│   ├── entities/
│   └── repositories/ (interface)
├── presentation/
│   ├── notifiers/ (state management)
│   ├── widgets/
│   └── screens/
└── <module_name>_module.dart (ENTRY POINT)
```

### 2.2 ENTRY POINT (`module.dart`)
Setiap modul **HARUS** mengekspos dirinya melalui satu file `module.dart`. 
Modul lain (termasuk Dashboard) hanya boleh melakukan import dari file ini.
Contoh untuk `weather`:
```dart
// weather_module.dart
export 'presentation/widgets/weather_card.dart';
export 'presentation/notifiers/weather_provider.dart';
```

### 2.3 ISOLASI ANTAR MODUL
Modul **DILARANG KERAS** melakukan import ke modul lain secara langsung.
Jika modul `warning` membutuhkan entitas dari `incident`, maka entitas tersebut harus diangkat ke `lib/core/domain/` atau `lib/shared/domain/`.

---

## 3. WAJIB ADA PADA SETIAP MODUL

Setiap pengembangan modul **HARUS** memiliki komponen berikut:

1. **Provider / Notifier**: Mengelola *business state*.
2. **Repository (Interface & Impl)**: Orkestrator pemanggilan data.
3. **Remote Datasource**: Khusus berinteraksi dengan API/Jaringan.
4. **Local Datasource**: Khusus berinteraksi dengan Cache (SQLite/Hive).
5. **Entity**: Objek bisnis murni di layer Domain.
6. **Model**: DTO (Data Transfer Object) untuk konversi JSON ke Entity di layer Data.

---

## 4. LARANGAN KERAS DI LAYER UI (PRESENTATION)

Di dalam folder `presentation/` (Widget / Screen / Notifier), developer **DILARANG KERAS** mengimpor atau menggunakan secara langsung:
- ❌ `Dio` atau HTTP Client
- ❌ `Drift` / `SQLite` / Database Engine
- ❌ `SharedPreferences` / `FlutterSecureStorage`
- ❌ `GoRouter` untuk hardcoded route (gunakan Route Helper/Delegation)

**Semua aksi data harus melalui Notifier -> Repository**.

---

## 5. REPOSITORY & DATA LAYER PATTERN

Repository adalah jembatan pintar, bukan sekadar pass-through.
Repository **WAJIB** mengatur strategi prioritas pengembalian data:

```
UI (Notifier) 
  ↓ panggil
Repository
  ├── 1. Panggil RemoteDatasource (BMKG/NURISK API)
  ├── 2. Jika sukses -> Simpan ke LocalDatasource (SQLite) -> Return Data
  └── 3. Jika gagal (offline/timeout) -> Return LocalDatasource (Cache)
```

UI sama sekali tidak boleh tahu apakah data berasal dari API atau SQLite.

---

## 6. LARANGAN PENGGUNAAN "CONTROLLER" & "GOD OBJECT"

### 6.1 State Management (Riverpod)
- Dilarang membuat kelas dengan suffix `Controller` (contoh: ❌ `WeatherController`).
- Gunakan terminologi Riverpod standar: `Notifier`, `AsyncNotifier`, `Provider`, dan `State`.
- Contoh: ✅ `WeatherNotifier`, ✅ `WeatherState`, ✅ `weatherProvider`.

### 6.2 Anti-God Widget (Aturan Dashboard)
Dashboard **TIDAK BOLEH** memiliki logic fetching data.
Dashboard **TIDAK BOLEH** memanggil API atau `Repository` secara langsung.
Dashboard murni melakukan agregasi statis:
```dart
class PublicDashboard extends ConsumerWidget {
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // Hanya membaca state yang diekspos oleh module.dart masing-masing
    final weatherState = ref.watch(weatherProvider);
    final warningState = ref.watch(warningProvider);

    return ListView(
      children: [
        WeatherCard(state: weatherState),
        WarningBanner(state: warningState),
        // ...
      ],
    );
  }
}
```

---
*Kepatuhan terhadap dokumen ini bersifat absolut. Pelanggaran terhadap kontrak ini akan digagalkan pada tahap Code Review (PR).*
