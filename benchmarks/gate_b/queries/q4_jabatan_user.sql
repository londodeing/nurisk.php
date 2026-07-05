EXPLAIN
SELECT
    pj.tipe_lingkup,
    pj.id_lingkup,
    j.nama_jabatan
FROM pengguna_jabatan pj
INNER JOIN master_jabatan j ON pj.id_jabatan_posisi = j.id_jabatan_posisi
WHERE pj.id_pengguna = 1
  AND pj.status_aktif = 1
  AND (pj.berakhir_pada IS NULL OR DATE(pj.berakhir_pada) >= CURDATE())
ORDER BY pj.ditugaskan_pada DESC;
