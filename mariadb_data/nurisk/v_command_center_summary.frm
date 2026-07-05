TYPE=VIEW
query=select `i`.`id_insiden` AS `id_insiden`,`i`.`kode_kejadian` AS `kode_kejadian`,`i`.`status_insiden` AS `status_insiden`,`i`.`status_operasi` AS `command_state`,`i`.`prioritas` AS `prioritas`,to_days(current_timestamp()) - to_days(`i`.`waktu_mulai`) AS `lama_kejadian_hari`,coalesce(`mk`.`nama_klaster`,\'Tunggu Aktivasi\') AS `nama_klaster`,(select count(0) from `nurisk`.`operasi_sitrep` where `nurisk`.`operasi_sitrep`.`id_insiden` = `i`.`id_insiden`) AS `jumlah_sitrep` from ((`nurisk`.`operasi_insiden` `i` left join `nurisk`.`operasi_klaster` `ok` on(`i`.`id_insiden` = `ok`.`id_insiden`)) left join `nurisk`.`operasi_master_klaster` `mk` on(`ok`.`id_klaster` = `mk`.`id_klaster`))
md5=b74b483355103823f53ca79a7b81747c
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=0001781714102612743
create-version=2
source=SELECT `i`.`id_insiden` AS `id_insiden`, `i`.`kode_kejadian` AS `kode_kejadian`, `i`.`status_insiden` AS `status_insiden`, `i`.`status_operasi` AS `command_state`, `i`.`prioritas` AS `prioritas`, to_days(current_timestamp()) - to_days(`i`.`waktu_mulai`) AS `lama_kejadian_hari`, coalesce(`mk`.`nama_klaster`,\'Tunggu Aktivasi\') AS `nama_klaster`, (select count(0) from `operasi_sitrep` where `operasi_sitrep`.`id_insiden` = `i`.`id_insiden`) AS `jumlah_sitrep` FROM ((`operasi_insiden` `i` left join `operasi_klaster` `ok` on(`i`.`id_insiden` = `ok`.`id_insiden`)) left join `operasi_master_klaster` `mk` on(`ok`.`id_klaster` = `mk`.`id_klaster`))
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `i`.`id_insiden` AS `id_insiden`,`i`.`kode_kejadian` AS `kode_kejadian`,`i`.`status_insiden` AS `status_insiden`,`i`.`status_operasi` AS `command_state`,`i`.`prioritas` AS `prioritas`,to_days(current_timestamp()) - to_days(`i`.`waktu_mulai`) AS `lama_kejadian_hari`,coalesce(`mk`.`nama_klaster`,\'Tunggu Aktivasi\') AS `nama_klaster`,(select count(0) from `nurisk`.`operasi_sitrep` where `nurisk`.`operasi_sitrep`.`id_insiden` = `i`.`id_insiden`) AS `jumlah_sitrep` from ((`nurisk`.`operasi_insiden` `i` left join `nurisk`.`operasi_klaster` `ok` on(`i`.`id_insiden` = `ok`.`id_insiden`)) left join `nurisk`.`operasi_master_klaster` `mk` on(`ok`.`id_klaster` = `mk`.`id_klaster`))
mariadb-version=100432
