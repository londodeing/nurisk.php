TYPE=VIEW
query=select `i`.`kode_kejadian` AS `kode_kejadian`,`s`.`nomor_sitrep` AS `nomor_sitrep`,`s`.`waktu_pelaporan` AS `waktu_pelaporan`,`p`.`nama_lengkap` AS `petugas_pelapor`,`s`.`kondisi_umum` AS `kondisi_umum`,json_extract(`s`.`snapshot_dampak`,\'$.data.meninggal\') AS `jml_meninggal`,`s`.`upaya_penanganan` AS `upaya_penanganan`,`s`.`file_pdf_path` AS `file_pdf_path` from ((`nurisk`.`operasi_sitrep` `s` join `nurisk`.`operasi_insiden` `i` on(`s`.`id_insiden` = `i`.`id_insiden`)) join `nurisk`.`auth_pengguna_profil` `p` on(`s`.`id_petugas` = `p`.`id_pengguna`))
md5=99dbff36003b7c5659ccc5f25180e256
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=0001781714102625995
create-version=2
source=SELECT `i`.`kode_kejadian` AS `kode_kejadian`, `s`.`nomor_sitrep` AS `nomor_sitrep`, `s`.`waktu_pelaporan` AS `waktu_pelaporan`, `p`.`nama_lengkap` AS `petugas_pelapor`, `s`.`kondisi_umum` AS `kondisi_umum`, json_extract(`s`.`snapshot_dampak`,\'$.data.meninggal\') AS `jml_meninggal`, `s`.`upaya_penanganan` AS `upaya_penanganan`, `s`.`file_pdf_path` AS `file_pdf_path` FROM ((`operasi_sitrep` `s` join `operasi_insiden` `i` on(`s`.`id_insiden` = `i`.`id_insiden`)) join `auth_pengguna_profil` `p` on(`s`.`id_petugas` = `p`.`id_pengguna`))
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `i`.`kode_kejadian` AS `kode_kejadian`,`s`.`nomor_sitrep` AS `nomor_sitrep`,`s`.`waktu_pelaporan` AS `waktu_pelaporan`,`p`.`nama_lengkap` AS `petugas_pelapor`,`s`.`kondisi_umum` AS `kondisi_umum`,json_extract(`s`.`snapshot_dampak`,\'$.data.meninggal\') AS `jml_meninggal`,`s`.`upaya_penanganan` AS `upaya_penanganan`,`s`.`file_pdf_path` AS `file_pdf_path` from ((`nurisk`.`operasi_sitrep` `s` join `nurisk`.`operasi_insiden` `i` on(`s`.`id_insiden` = `i`.`id_insiden`)) join `nurisk`.`auth_pengguna_profil` `p` on(`s`.`id_petugas` = `p`.`id_pengguna`))
mariadb-version=100432
