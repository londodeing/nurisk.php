# Weather Module (Public Layer)

## Purpose
Modul ini bertanggung jawab untuk mengambil, menyimpan (cache), dan menampilkan data cuaca terkini (suhu, kondisi, lokasi) pada halaman Dashboard publik. Modul ini diinisialisasi secara pasif melalui state provider.

## Public API
- `GET /internal/weather/current` (Proxy NURISK untuk data BMKG / stasiun cuaca internal)

## Provider (State)
- **`weatherProvider`**: `AsyncNotifierProvider` yang mengembalikan `AsyncValue<WeatherEntity>`. Provider ini menangani keseluruhan siklus hidup data cuaca (Loading, Data, Error).

## Dependencies
- **Layer Presentation**: `flutter_riverpod`
- **Layer Data**: `Dio` (via `PublicApiClient`), `Drift` (via `weather_table.dart` di `core/storage`)

## Cache Policy (TTL)
- **Durasi TTL**: 30 menit.
- **Logika**: Data cuaca disimpan ke dalam tabel SQLite `weather_table.dart`. Saat UI me-request cuaca, sistem akan mengecek umur cache. Jika di bawah 30 menit, modul akan merender cache seketika (*instant render*) sembari melakukan polling background jika diperlukan.

## Offline Behaviour
- **Graceful Degradation**: Jika perangkat terputus dari jaringan atau `PublicApiClient` mengalami timeout, `WeatherRepositoryImpl` akan secara otomatis melakukan fallback (*catch exception*) untuk membaca baris terakhir yang ada di SQLite. UI akan tetap merender data namun menampilkan *timestamp* pembaruan terakhir yang usang.
- Jika database SQLite benar-benar kosong dan jaringan mati, modul akan melempar *Exception* yang akan memicu state `error` pada `AsyncValue`, sehingga `WeatherCard` akan me-render UI *Error* beserta tombol *Retry*.

## Known Limitations
- Saat ini modul belum sepenuhnya memisahkan pemanggilan proxy lokal (stasiun cuaca internal NURISK) dengan data cuaca nasional BMKG secara terstruktur di sisi UI.
- Logika komputasi perbandingan TTL 30 menit (stale-while-revalidate murni) masih diimplementasikan secara statis dan perlu pengerasan (*hardening*) pada iterasi Repository mendatang.
