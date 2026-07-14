# NURISK MOBILE — SPRINT F1.5 DESIGN REVIEW
## Document 19: Weather Module as Reference Implementation
**Version**: 1.0.0 | **Status**: REVIEW IN PROGRESS | **Domain**: Public Layer  

---

## 1. OBJECTIVE

Dokumen ini adalah hasil Design Review khusus untuk modul pertama: **Weather Module** (Sprint F1.5). Modul ini akan menjadi *Template Standard* atau *Reference Implementation* bagi puluhan modul masa depan (Warning, Incident, Governance, dll).

Jika ada pola yang salah di sini, maka kesalahan akan terreplikasi. Oleh karena itu, modul ini harus lulus audit 100% terhadap arsitektur ideal.

---

## 2. DEPENDENCY DIRECTION AUDIT

**Pola yang Diharapkan:** *Data mengalir dari dalam (Data) ke luar (Presentation).*
`Presentation (Widget -> Notifier) -> Domain (Repository Interface) <- Data (RepositoryImpl -> Datasource)`

**Hasil Audit Weather Module:**
- `weather_card.dart` hanya bergantung pada `weather_provider.dart`.
- `weather_provider.dart` hanya memanggil antarmuka abstrak `WeatherRepository`.
- `weather_repository_impl.dart` membungkus pemanggilan `remote` dan `local` datasource.
- **Kesimpulan**: ✅ Lulus. Tidak ada Presentation layer yang memanggil RepositoryImpl atau Datasource secara langsung.

---

## 3. REPOSITORY PATTERN AUDIT

**Pola yang Diharapkan:** Repository mengabstraksi sumber data sepenuhnya dari Notifier.

**Hasil Audit Weather Module:**
- Notifier (`WeatherNotifier`) hanya mengetahui `WeatherEntity` dan `getCurrentWeather()`.
- `WeatherRepositoryImpl` melakukan orkestrasi pemanggilan remote dan local.
- **Kesimpulan**: ✅ Lulus. Notifier sepenuhnya buta mengenai keberadaan Dio, API, maupun SQLite.

---

## 4. OFFLINE STRATEGY AUDIT

**Strategi Baku (Offline-First Fallback):**
Modul cuaca mengimplementasikan alur berikut di dalam `WeatherRepositoryImpl`:
1. **Network Attempt**: Memanggil `fetchCurrentWeather()` dari remote.
2. **Success**: Jika sukses, simpan model tersebut ke SQLite via `cacheWeather()`, lalu return ke UI.
3. **Failure**: Jika jaringan terputus (DioException), blok `catch` akan memanggil `getCachedWeather()` dari SQLite.
4. **Cache Return**: Jika cache ada, UI akan merender cuaca yang ada beserta field `updatedAt` (timestamp usang).
5. **Cache Empty**: Jika SQLite kosong, maka repository melempar Exception ke UI, dan UI berpindah ke *Error State*.

**Kesimpulan**: ✅ Lulus. Strategi *Graceful Degradation* telah terimplementasi eksplisit.

---

## 5. REFRESH POLICY (TTL) AUDIT

**Pola Stale-While-Revalidate untuk Cuaca:**
Cuaca bukan data statis. Membuka dashboard berkali-kali tidak boleh men-spam API BMKG/Internal.
- **TTL Cache**: 30 Menit.
- **Logika Fetch**:
  1. Baca `updatedAt` dari Local Cache SQLite.
  2. Jika `now() - updatedAt < 30 menit`: Langsung kembalikan data Cache ke UI (Instant Load). Secara *background*, trigger refresh diam-diam tanpa memblokir state.
  3. Jika `now() - updatedAt > 30 menit`: UI masuk state Loading, lakukan request API.
- *Catatan Perbaikan*: Saat ini `WeatherNotifier` belum memiliki mekanisme cek TTL murni sebelum API call. Logika ini akan ditambahkan ke *Generic AsyncNotifier Pattern*.

**Kesimpulan**: ⚠️ Partial Pass. Aturan TTL telah ditetapkan namun perlu diintegrasikan ke Repository.

---

## 6. STATE MANAGEMENT & GENERIC PATTERN

**Pola yang Diharapkan:** Menggunakan `AsyncNotifier` dari Riverpod untuk menampung siklus (Loading/Data/Error).

**Hasil Audit Weather Module:**
- Telah membuang konsep "Controller".
- Menggunakan `AsyncNotifierProvider` yang mengembalikan `AsyncValue<WeatherEntity>`.
- Pola ini **SUDAH IDEAL** dan wajib dijadikan *Generic Pattern* untuk:
  - `WarningNotifier` (Sprint F1.6)
  - `IncidentNotifier` (Sprint F1.8)
  - `NewsNotifier` (Sprint F1.9)

**Kesimpulan**: ✅ Lulus. Pola Notifier siap direplikasi.

---

## 7. WIDGET PURITY AUDIT

**Pola yang Diharapkan:** Widget hanya merender state, dilarang mengakses infrastruktur data.

**Hasil Audit Weather Module:**
- `WeatherCard` hanya menggunakan `ref.watch(weatherProvider)`.
- Widget berisi `.when(data: ..., loading: ..., error: ...)`.
- **Dilarang**: Pemanggilan `dio.get()`, `SharedPreferences`, atau navigasi internal di dalam blok build selain `Action`.

**Kesimpulan**: ✅ Lulus. Widget murni bertindak sebagai Presenter reaktif.

---

## 8. MODULE CONTRACT CHECKLIST (F1.5)

| Komponen | Status File | Lokasi |
|----------|-------------|--------|
| **Entity** | ✅ Ada | `domain/entities/weather_entity.dart` |
| **Repository** | ✅ Ada | `domain/repositories/weather_repository.dart` |
| **RepositoryImpl** | ✅ Ada | `data/repositories/weather_repository_impl.dart` |
| **RemoteDatasource** | ✅ Ada | `data/datasources/weather_remote_datasource.dart` |
| **LocalDatasource** | ✅ Ada | `data/datasources/weather_local_datasource.dart` |
| **Model (DTO)** | ✅ Ada | `data/models/weather_model.dart` |
| **Provider (Notifier)**| ✅ Ada | `presentation/notifiers/weather_provider.dart` |
| **Widget (UI)** | ✅ Ada | `presentation/widgets/weather_card.dart` |
| **Module Export** | ✅ Ada | `weather_module.dart` |
| **Module README** | ✅ Ada | `README.md` (Spesifikasi Internal Modul) |

---
**Rekomendasi Eksekutif**:
Weather Module **LULUS** Audit Desain dan resmi menjadi *Reference Implementation* NURISK Mobile. Semua modul, mulai dari Warning Module (F1.6) hingga ke level Governance, WAJIB mengkloning struktur berlapis ini.
