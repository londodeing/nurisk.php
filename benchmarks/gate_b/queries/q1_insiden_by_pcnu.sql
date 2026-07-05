-- Query yang dipanggil setiap user PCNU membuka halaman index insiden
-- Wajib menggunakan index fk_inc_pcnu

EXPLAIN
SELECT
    i.id_insiden,
    i.kode_kejadian,
    i.status_insiden,
    i.prioritas,
    i.waktu_mulai,
    j.nama_bencana
FROM operasi_insiden i
INNER JOIN bencana_master_jenis j ON i.id_jenis_bencana = j.id_jenis
WHERE i.id_pcnu = 5
  AND i.dihapus_pada IS NULL
ORDER BY i.dibuat_pada DESC
LIMIT 15 OFFSET 0;
