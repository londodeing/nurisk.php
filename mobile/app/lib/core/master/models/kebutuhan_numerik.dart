class KebutuhanNumerikMaster {
  final int idItem;
  final String kategori;
  final String kodeItem;
  final String namaItem;
  final String satuanDefault;
  final int urutan;

  KebutuhanNumerikMaster({
    required this.idItem,
    required this.kategori,
    required this.kodeItem,
    required this.namaItem,
    required this.satuanDefault,
    required this.urutan,
  });

  factory KebutuhanNumerikMaster.fromMap(Map<String, dynamic> map) {
    return KebutuhanNumerikMaster(
      idItem: map['id_item'] as int,
      kategori: map['kategori'] as String,
      kodeItem: map['kode_item'] as String,
      namaItem: map['nama_item'] as String,
      satuanDefault: map['satuan_default'] as String,
      urutan: map['urutan'] as int,
    );
  }
}
