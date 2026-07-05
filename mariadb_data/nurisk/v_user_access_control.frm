TYPE=VIEW
query=select `p`.`id_pengguna` AS `id_pengguna`,`prof`.`nama_lengkap` AS `nama_lengkap`,`r`.`nama_peran` AS `nama_peran`,`r`.`level_otoritas` AS `level_otoritas`,`p`.`status_akun` AS `status_akun` from ((`nurisk`.`auth_users` `p` join `nurisk`.`auth_pengguna_profil` `prof` on(`p`.`id_pengguna` = `prof`.`id_pengguna`)) join `nurisk`.`auth_roles` `r` on(`p`.`id_peran` = `r`.`id_peran`))
md5=f6c87c22979709225a157ca19ce99ecc
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=0001781714102666036
create-version=2
source=SELECT `p`.`id_pengguna` AS `id_pengguna`, `prof`.`nama_lengkap` AS `nama_lengkap`, `r`.`nama_peran` AS `nama_peran`, `r`.`level_otoritas` AS `level_otoritas`, `p`.`status_akun` AS `status_akun` FROM ((`auth_users` `p` join `auth_pengguna_profil` `prof` on(`p`.`id_pengguna` = `prof`.`id_pengguna`)) join `auth_roles` `r` on(`p`.`id_peran` = `r`.`id_peran`))
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `p`.`id_pengguna` AS `id_pengguna`,`prof`.`nama_lengkap` AS `nama_lengkap`,`r`.`nama_peran` AS `nama_peran`,`r`.`level_otoritas` AS `level_otoritas`,`p`.`status_akun` AS `status_akun` from ((`nurisk`.`auth_users` `p` join `nurisk`.`auth_pengguna_profil` `prof` on(`p`.`id_pengguna` = `prof`.`id_pengguna`)) join `nurisk`.`auth_roles` `r` on(`p`.`id_peran` = `r`.`id_peran`))
mariadb-version=100432
