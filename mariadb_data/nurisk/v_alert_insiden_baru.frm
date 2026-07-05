TYPE=VIEW
query=select `i`.`kode_kejadian` AS `kode_kejadian`,`bj`.`nama_bencana` AS `nama_bencana`,`p`.`nama_pcnu` AS `nama_pcnu`,`i`.`waktu_mulai` AS `waktu_mulai`,`i`.`prioritas` AS `prioritas` from (((`nurisk`.`operasi_insiden` `i` join `nurisk`.`bencana_master_jenis` `bj` on(`i`.`id_jenis_bencana` = `bj`.`id_jenis`)) join `nurisk`.`organisasi_pcnu` `p` on(`i`.`id_pcnu` = `p`.`id_pcnu`)) left join `nurisk`.`operasi_klaster` `ok` on(`i`.`id_insiden` = `ok`.`id_insiden`)) where `ok`.`id_operasi_klaster` is null and `i`.`status_insiden` not in (\'selesai\',\'dibatalkan\')
md5=34c0ef8a8c223cc493998fc38f3b056c
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=0001781714102559479
create-version=2
source=SELECT `i`.`kode_kejadian` AS `kode_kejadian`, `bj`.`nama_bencana` AS `nama_bencana`, `p`.`nama_pcnu` AS `nama_pcnu`, `i`.`waktu_mulai` AS `waktu_mulai`, `i`.`prioritas` AS `prioritas` FROM (((`operasi_insiden` `i` join `bencana_master_jenis` `bj` on(`i`.`id_jenis_bencana` = `bj`.`id_jenis`)) join `organisasi_pcnu` `p` on(`i`.`id_pcnu` = `p`.`id_pcnu`)) left join `operasi_klaster` `ok` on(`i`.`id_insiden` = `ok`.`id_insiden`)) WHERE `ok`.`id_operasi_klaster` is null AND `i`.`status_insiden` not in (\'selesai\',\'dibatalkan\')
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `i`.`kode_kejadian` AS `kode_kejadian`,`bj`.`nama_bencana` AS `nama_bencana`,`p`.`nama_pcnu` AS `nama_pcnu`,`i`.`waktu_mulai` AS `waktu_mulai`,`i`.`prioritas` AS `prioritas` from (((`nurisk`.`operasi_insiden` `i` join `nurisk`.`bencana_master_jenis` `bj` on(`i`.`id_jenis_bencana` = `bj`.`id_jenis`)) join `nurisk`.`organisasi_pcnu` `p` on(`i`.`id_pcnu` = `p`.`id_pcnu`)) left join `nurisk`.`operasi_klaster` `ok` on(`i`.`id_insiden` = `ok`.`id_insiden`)) where `ok`.`id_operasi_klaster` is null and `i`.`status_insiden` not in (\'selesai\',\'dibatalkan\')
mariadb-version=100432
