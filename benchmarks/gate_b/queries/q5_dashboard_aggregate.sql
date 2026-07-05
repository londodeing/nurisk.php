EXPLAIN
SELECT
    p.nama_pcnu,
    COUNT(CASE WHEN i.status_insiden = 'respon' THEN 1 END)     AS sedang_respon,
    COUNT(CASE WHEN i.status_insiden = 'pemulihan' THEN 1 END)  AS pemulihan,
    COUNT(CASE WHEN i.prioritas = 'kritis' THEN 1 END)          AS prioritas_kritis
FROM organisasi_pcnu p
LEFT JOIN operasi_insiden i
    ON p.id_pcnu = i.id_pcnu
   AND i.dihapus_pada IS NULL
   AND i.status_insiden NOT IN ('selesai', 'dibatalkan')
GROUP BY p.id_pcnu, p.nama_pcnu
ORDER BY sedang_respon DESC;
