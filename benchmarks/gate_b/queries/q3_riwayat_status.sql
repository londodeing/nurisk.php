EXPLAIN
SELECT
    r.id_history,
    r.status_sebelumnya,
    r.status_terbaru,
    r.alasan,
    r.dibuat_pada,
    u.id_pengguna
FROM riwayat_status_insiden r
INNER JOIN auth_users u ON r.id_pengguna = u.id_pengguna
WHERE r.id_insiden = 1
  AND r.dihapus_pada IS NULL
ORDER BY r.dibuat_pada ASC;
