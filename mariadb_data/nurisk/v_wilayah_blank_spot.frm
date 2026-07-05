TYPE=VIEW
query=select `k`.`nama_kab` AS `nama_kab`,`kc`.`nama_kec` AS `nama_kec`,`d`.`nama_desa` AS `nama_desa` from (((`nurisk`.`wilayah_desa` `d` join `nurisk`.`wilayah_kecamatan` `kc` on(`d`.`id_kec` = `kc`.`id_kec`)) join `nurisk`.`wilayah_kabupaten` `k` on(`kc`.`id_kab` = `k`.`id_kab`)) left join `nurisk`.`auth_pengguna_profil` `p` on(`d`.`id_desa` = `p`.`id_desa_domisili`)) where `p`.`id_pengguna` is null
md5=a817e6acfa8fb1325b79ab80c7f1a5d0
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=0001781714102679230
create-version=2
source=SELECT `k`.`nama_kab` AS `nama_kab`, `kc`.`nama_kec` AS `nama_kec`, `d`.`nama_desa` AS `nama_desa` FROM (((`wilayah_desa` `d` join `wilayah_kecamatan` `kc` on(`d`.`id_kec` = `kc`.`id_kec`)) join `wilayah_kabupaten` `k` on(`kc`.`id_kab` = `k`.`id_kab`)) left join `auth_pengguna_profil` `p` on(`d`.`id_desa` = `p`.`id_desa_domisili`)) WHERE `p`.`id_pengguna` is null
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `k`.`nama_kab` AS `nama_kab`,`kc`.`nama_kec` AS `nama_kec`,`d`.`nama_desa` AS `nama_desa` from (((`nurisk`.`wilayah_desa` `d` join `nurisk`.`wilayah_kecamatan` `kc` on(`d`.`id_kec` = `kc`.`id_kec`)) join `nurisk`.`wilayah_kabupaten` `k` on(`kc`.`id_kab` = `k`.`id_kab`)) left join `nurisk`.`auth_pengguna_profil` `p` on(`d`.`id_desa` = `p`.`id_desa_domisili`)) where `p`.`id_pengguna` is null
mariadb-version=100432
