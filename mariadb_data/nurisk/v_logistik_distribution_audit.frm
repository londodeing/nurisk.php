TYPE=VIEW
query=select `i`.`kode_kejadian` AS `kode_kejadian`,`p`.`nama_pcnu` AS `penanggung_jawab`,`pos`.`nama_posaju` AS `nama_posaju`,`cat`.`nama_kategori` AS `nama_kategori`,`kat`.`nama_barang_standar` AS `nama_barang_standar`,`s`.`jumlah_tersedia` AS `jumlah_tersedia` from (((((`nurisk`.`logistik_stok` `s` join `nurisk`.`logistik_barang_katalog` `kat` on(`s`.`id_katalog` = `kat`.`id_katalog`)) join `nurisk`.`logistik_kategori` `cat` on(`kat`.`id_kategori` = `cat`.`id_kategori`)) join `nurisk`.`operasi_posaju` `pos` on(`s`.`id_posaju` = `pos`.`id_posaju`)) join `nurisk`.`operasi_insiden` `i` on(`pos`.`id_insiden` = `i`.`id_insiden`)) join `nurisk`.`organisasi_pcnu` `p` on(`i`.`id_pcnu` = `p`.`id_pcnu`))
md5=12400a171c896b5ca076e1dab53a37c7
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=0001781714102639548
create-version=2
source=SELECT `i`.`kode_kejadian` AS `kode_kejadian`, `p`.`nama_pcnu` AS `penanggung_jawab`, `pos`.`nama_posaju` AS `nama_posaju`, `cat`.`nama_kategori` AS `nama_kategori`, `kat`.`nama_barang_standar` AS `nama_barang_standar`, `s`.`jumlah_tersedia` AS `jumlah_tersedia` FROM (((((`logistik_stok` `s` join `logistik_barang_katalog` `kat` on(`s`.`id_katalog` = `kat`.`id_katalog`)) join `logistik_kategori` `cat` on(`kat`.`id_kategori` = `cat`.`id_kategori`)) join `operasi_posaju` `pos` on(`s`.`id_posaju` = `pos`.`id_posaju`)) join `operasi_insiden` `i` on(`pos`.`id_insiden` = `i`.`id_insiden`)) join `organisasi_pcnu` `p` on(`i`.`id_pcnu` = `p`.`id_pcnu`))
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `i`.`kode_kejadian` AS `kode_kejadian`,`p`.`nama_pcnu` AS `penanggung_jawab`,`pos`.`nama_posaju` AS `nama_posaju`,`cat`.`nama_kategori` AS `nama_kategori`,`kat`.`nama_barang_standar` AS `nama_barang_standar`,`s`.`jumlah_tersedia` AS `jumlah_tersedia` from (((((`nurisk`.`logistik_stok` `s` join `nurisk`.`logistik_barang_katalog` `kat` on(`s`.`id_katalog` = `kat`.`id_katalog`)) join `nurisk`.`logistik_kategori` `cat` on(`kat`.`id_kategori` = `cat`.`id_kategori`)) join `nurisk`.`operasi_posaju` `pos` on(`s`.`id_posaju` = `pos`.`id_posaju`)) join `nurisk`.`operasi_insiden` `i` on(`pos`.`id_insiden` = `i`.`id_insiden`)) join `nurisk`.`organisasi_pcnu` `p` on(`i`.`id_pcnu` = `p`.`id_pcnu`))
mariadb-version=100432
