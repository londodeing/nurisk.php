# Warning Module (Public Layer)

## Purpose
Modul ini bertindak sebagai aggregator untuk sistem Peringatan Dini (EWS) dari berbagai sumber (BMKG, BNPB, LPBI, Incident Engine). Modul akan menyeleksi dan menampilkan peringatan yang aktif di halaman Dashboard publik.

## Public API
- `GET /api/public/warnings` (Backend melakukan agregasi data dan mengembalikan daftar peringatan aktif)

## Provider (State)
- **`warningProvider`**: `AsyncNotifierProvider` yang mengembalikan `AsyncValue<List<WarningEntity>>`. Memiliki mekanisme *silent polling* setiap 30 detik untuk memastikan UI selalu mendapatkan update kritis.

## Dependencies
- **Layer Presentation**: `flutter_riverpod`
- **Layer Data**: `Dio` (via `PublicApiClient`), `Drift` (via `warning_table.dart` di `core/storage`)

## Cache Policy (TTL)
- **Durasi TTL**: Sangat agresif, bergantung pada severity. Default fallback cache adalah 30 detik.
- **Logika**: Data disimpan ke dalam `warning_table.dart`. Karena sifatnya kritis, polling dilakukan di background secara berkala.

## Offline Behaviour
- **Fail Silently**: Jika tidak ada data cache dan API gagal, `WarningBanner` akan menyembunyikan dirinya sendiri (`SizedBox.shrink()`). Tidak ada tampilan "Error" yang mencolok agar tidak merusak UI Dashboard, mengingat *warning* bersifat dinamis.
- Jika ada cache aktif, akan ditampilkan banner peringatan terakhir.

## Known Limitations
- Backend API untuk `/api/public/warnings` mungkin memerlukan arsitektur *Backend-For-Frontend* (BFF) yang solid agar tidak menjadi *bottleneck* ketika banyak client melakukan polling 30 detik secara bersamaan. (Rekomendasi server: Implementasi Redis/Memcached di backend NURISK).
