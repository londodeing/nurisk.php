class WilayahModel {
  final String id;
  final String nama;

  const WilayahModel({required this.id, required this.nama});

  factory WilayahModel.fromKabupatenJson(Map<String, dynamic> json) {
    return WilayahModel(
      id: json['id_kab'] ?? '',
      nama: json['nama_kab'] ?? '',
    );
  }

  factory WilayahModel.fromKecamatanJson(Map<String, dynamic> json) {
    return WilayahModel(
      id: json['id_kec'] ?? '',
      nama: json['nama_kec'] ?? '',
    );
  }

  factory WilayahModel.fromDesaJson(Map<String, dynamic> json) {
    return WilayahModel(
      id: json['id_desa'] ?? '',
      nama: json['nama_desa'] ?? '',
    );
  }
}
