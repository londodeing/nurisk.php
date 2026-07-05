TYPE=VIEW
query=select `p`.`id_pengguna` AS `id_pengguna`,`prof`.`nama_lengkap` AS `nama_lengkap`,`u`.`nama_unit` AS `nama_unit`,`u`.`tipe_unit` AS `level_unit`,`prof`.`id_desa_domisili` AS `id_desa_domisili`,`d`.`nama_desa` AS `nama_desa_domisili`,`u`.`id_wilayah` AS `kode_wilayah_unit`,case when `u`.`tipe_unit` = \'ranting\' and `prof`.`id_desa_domisili` = `u`.`id_wilayah` then \'Sesuai Desa\' when `u`.`tipe_unit` = \'mwc\' and left(`prof`.`id_desa_domisili`,6) = `u`.`id_wilayah` then \'Sesuai Kecamatan\' when `u`.`tipe_unit` = \'pcnu\' and left(`prof`.`id_desa_domisili`,4) = `u`.`id_wilayah` then \'Sesuai Kabupaten\' else \'Luar Wilayah\' end AS `status_cakupan_wilayah` from (((`nurisk`.`auth_users` `p` join `nurisk`.`auth_pengguna_profil` `prof` on(`p`.`id_pengguna` = `prof`.`id_pengguna`)) join `nurisk`.`organisasi_unit` `u` on(`p`.`id_unit` = `u`.`id_unit`)) left join `nurisk`.`wilayah_desa` `d` on(`prof`.`id_desa_domisili` = `d`.`id_desa`))
md5=c0c80ae71ca162b4adef534d44214a12
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=0001781714102653200
create-version=2
source=SELECT `p`.`id_pengguna` AS `id_pengguna`, `prof`.`nama_lengkap` AS `nama_lengkap`, `u`.`nama_unit` AS `nama_unit`, `u`.`tipe_unit` AS `level_unit`, `prof`.`id_desa_domisili` AS `id_desa_domisili`, `d`.`nama_desa` AS `nama_desa_domisili`, `u`.`id_wilayah` AS `kode_wilayah_unit`, CASE WHEN `u`.`tipe_unit` = \'ranting\' AND `prof`.`id_desa_domisili` = `u`.`id_wilayah` THEN \'Sesuai Desa\' WHEN `u`.`tipe_unit` = \'mwc\' AND left(`prof`.`id_desa_domisili`,6) = `u`.`id_wilayah` THEN \'Sesuai Kecamatan\' WHEN `u`.`tipe_unit` = \'pcnu\' AND left(`prof`.`id_desa_domisili`,4) = `u`.`id_wilayah` THEN \'Sesuai Kabupaten\' ELSE \'Luar Wilayah\' END AS `status_cakupan_wilayah` FROM (((`auth_users` `p` join `auth_pengguna_profil` `prof` on(`p`.`id_pengguna` = `prof`.`id_pengguna`)) join `organisasi_unit` `u` on(`p`.`id_unit` = `u`.`id_unit`)) left join `wilayah_desa` `d` on(`prof`.`id_desa_domisili` = `d`.`id_desa`))
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `p`.`id_pengguna` AS `id_pengguna`,`prof`.`nama_lengkap` AS `nama_lengkap`,`u`.`nama_unit` AS `nama_unit`,`u`.`tipe_unit` AS `level_unit`,`prof`.`id_desa_domisili` AS `id_desa_domisili`,`d`.`nama_desa` AS `nama_desa_domisili`,`u`.`id_wilayah` AS `kode_wilayah_unit`,case when `u`.`tipe_unit` = \'ranting\' and `prof`.`id_desa_domisili` = `u`.`id_wilayah` then \'Sesuai Desa\' when `u`.`tipe_unit` = \'mwc\' and left(`prof`.`id_desa_domisili`,6) = `u`.`id_wilayah` then \'Sesuai Kecamatan\' when `u`.`tipe_unit` = \'pcnu\' and left(`prof`.`id_desa_domisili`,4) = `u`.`id_wilayah` then \'Sesuai Kabupaten\' else \'Luar Wilayah\' end AS `status_cakupan_wilayah` from (((`nurisk`.`auth_users` `p` join `nurisk`.`auth_pengguna_profil` `prof` on(`p`.`id_pengguna` = `prof`.`id_pengguna`)) join `nurisk`.`organisasi_unit` `u` on(`p`.`id_unit` = `u`.`id_unit`)) left join `nurisk`.`wilayah_desa` `d` on(`prof`.`id_desa_domisili` = `d`.`id_desa`))
mariadb-version=100432
