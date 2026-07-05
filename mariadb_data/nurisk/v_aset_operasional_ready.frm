TYPE=VIEW
query=select `u`.`id_unit_aset` AS `id_unit_aset`,`k`.`nama_kategori` AS `nama_kategori`,`j`.`nama_jenis` AS `nama_jenis`,`u`.`nomor_registrasi` AS `nomor_registrasi`,`u`.`kondisi_fisik` AS `kondisi_fisik`,`s`.`nama_status` AS `nama_status`,`u`.`posisi_terakhir` AS `posisi_terakhir` from (((`nurisk`.`aset_unit` `u` join `nurisk`.`aset_master_jenis` `j` on(`u`.`id_jenis_aset` = `j`.`id_jenis_aset`)) join `nurisk`.`aset_master_kategori` `k` on(`j`.`id_kategori_aset` = `k`.`id_kategori_aset`)) join `nurisk`.`aset_master_status` `s` on(`u`.`id_status` = `s`.`id_status`)) where `u`.`id_status` = 1 and `u`.`kondisi_fisik` = \'baik\'
md5=39056ea538afbd0e771482a9f516c32f
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=0001781714102573023
create-version=2
source=SELECT `u`.`id_unit_aset` AS `id_unit_aset`, `k`.`nama_kategori` AS `nama_kategori`, `j`.`nama_jenis` AS `nama_jenis`, `u`.`nomor_registrasi` AS `nomor_registrasi`, `u`.`kondisi_fisik` AS `kondisi_fisik`, `s`.`nama_status` AS `nama_status`, `u`.`posisi_terakhir` AS `posisi_terakhir` FROM (((`aset_unit` `u` join `aset_master_jenis` `j` on(`u`.`id_jenis_aset` = `j`.`id_jenis_aset`)) join `aset_master_kategori` `k` on(`j`.`id_kategori_aset` = `k`.`id_kategori_aset`)) join `aset_master_status` `s` on(`u`.`id_status` = `s`.`id_status`)) WHERE `u`.`id_status` = 1 AND `u`.`kondisi_fisik` = \'baik\'
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `u`.`id_unit_aset` AS `id_unit_aset`,`k`.`nama_kategori` AS `nama_kategori`,`j`.`nama_jenis` AS `nama_jenis`,`u`.`nomor_registrasi` AS `nomor_registrasi`,`u`.`kondisi_fisik` AS `kondisi_fisik`,`s`.`nama_status` AS `nama_status`,`u`.`posisi_terakhir` AS `posisi_terakhir` from (((`nurisk`.`aset_unit` `u` join `nurisk`.`aset_master_jenis` `j` on(`u`.`id_jenis_aset` = `j`.`id_jenis_aset`)) join `nurisk`.`aset_master_kategori` `k` on(`j`.`id_kategori_aset` = `k`.`id_kategori_aset`)) join `nurisk`.`aset_master_status` `s` on(`u`.`id_status` = `s`.`id_status`)) where `u`.`id_status` = 1 and `u`.`kondisi_fisik` = \'baik\'
mariadb-version=100432
