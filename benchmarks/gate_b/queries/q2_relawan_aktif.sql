EXPLAIN
SELECT
    i.id_insiden,
    i.kode_kejadian,
    COUNT(DISTINCT pj.id_pengguna) AS jumlah_relawan_aktif
FROM operasi_insiden i
LEFT JOIN pengguna_jabatan pj
    ON pj.tipe_lingkup = 'pcnu'
   AND pj.id_lingkup = i.id_pcnu
   AND pj.status_aktif = 1
   AND (pj.berakhir_pada IS NULL OR pj.berakhir_pada >= NOW())
WHERE i.status_insiden IN ('respon', 'pemulihan')
  AND i.dihapus_pada IS NULL
GROUP BY i.id_insiden, i.kode_kejadian;
