# Incident Feed Module (Public Layer)

## Purpose
Modul ini bertugas menampilkan umpan (*feed*) laporan kejadian bencana terkini ke layar publik. Modul ini berpegang pada **Dokumen 21: Public Incident Feed Spec** yang mengamankan privasi pelapor. 

## Public API
- `GET /api/public/incidents?page=1&limit=10` (BFF tersendiri tanpa data sensitif ERP)

## Provider (State)
- **`incidentFeedProvider`**: `AsyncNotifierProvider` yang mengembalikan `IncidentFeedState`. Mengelola list insiden, `currentPage`, status `isLoadingMore`, dan `loadMoreError` secara asinkron.

## Dependencies
- **Layer Presentation**: `flutter_riverpod`
- **Layer Data**: `Dio` (via `PublicApiClient`), `Drift` (via `incident_table.dart` di `core/storage`)

## Pagination Strategy
- Menggunakan pola Load More berbasis State. List akan ditambahkan (*appended*) setiap kali fungsi `loadMore()` pada Notifier dipanggil.

## Cache Policy (TTL)
- **Logika**: Data disimpan ke dalam `incident_table.dart`. Karena sifat pagination, *cache* SQLite difokuskan sebagai *Offline Fallback* hanya untuk halaman pertama (*Page 1*), sehingga pengguna yang membuka aplikasi saat offline tetap melihat feed terakhir tanpa melihat *Error Screen*.

## Privacy Compliance (Blacklist & Whitelist)
Modul ini (Entity dan Model) **TIDAK PERNAH** memetakan (*mapping*) atau mendefinisikan field sensitif (NIK, Nomor HP, Alamat Presisi). Hanya *Thumbnail*, status, kategori, distrik generik, dan keparahan yang diakomodasi.

## Known Limitations
- Infinite Scrolling otomatis (berbasis *ScrollController*) belum diimplementasikan di versi awal ini untuk mencegah memori bocor (menggunakan tombol *Load More* sebagai ganti).
