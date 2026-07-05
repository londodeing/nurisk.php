# M10 MOBILISASI — DATABASE HARDENING

Sesuai dengan `DATABASE_CONVENTION.md` NURISK Aturan 10.1 (Kolom FK Wajib diindex), telah dilakukan pembuatan migrasi incremental untuk mengamankan struktur dan performa tabel `operasi_mobilisasi`.

## Rincian Hardening

**Nama File Migrasi:**
`2026_06_17_131214_add_indexes_to_operasi_mobilisasi_table.php`

**Tabel Target:**
`operasi_mobilisasi`

**Index yang Ditambahkan:**

1. `id_insiden` (Tipe: FK Integer) — Diperlukan untuk mempercepat pencarian data mobilisasi berdasarkan insiden spesifik, terutama saat policy Lapis 4 mengevaluasi `canManageInsiden()`.
2. `id_pengguna` (Tipe: FK Integer) — Diperlukan untuk mempercepat pencarian data mobilisasi yang dibuat atau ditugaskan ke pengguna spesifik.
3. `status_mobilisasi` (Tipe: Varchar) — Diperlukan untuk mempercepat filtering data berdasarkan status state machine (`draft`, `disetujui`, `berangkat`, `tiba`, `selesai`, `dibatalkan`).
4. `sync_version` (Tipe: BigInteger) — Sangat krusial untuk fitur Offline Sync karena endpoint `/api/v1/sync` secara konstan akan menarik (pull) perubahan data di mana `sync_version > client_cursor`.

*Catatan:* `deleted_at` (dalam tabel ini `dihapus_pada`) telah ditangani secara internal oleh `SoftDeletes` Laravel, namun index opsional belum ditambahkan secara default karena tidak semua query mengabaikan soft deletes dalam jumlah raksasa (biasanya ditangani via index gabungan jika query berat).

## Status: SUCCESS
Penambahan index telah berhasil disusun tanpa mengubah/mengedit migrasi lama yang mungkin sudah teraplikasi (deployed) di lingkungan staging/produksi.
