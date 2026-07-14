// DEPRECATED: Gunakan core/master/models/wilayah.dart → class Kabupaten, Kecamatan, Desa
// Akan dihapus di Phase 2.5B

class WilayahMasterModel {
  final String id;
  final String nama;
  final String? idKab;
  final String? idKec;

  const WilayahMasterModel({
    required this.id,
    required this.nama,
    this.idKab,
    this.idKec,
  });

  factory WilayahMasterModel.fromKabupatenJson(Map<String, dynamic> json) {
    return WilayahMasterModel(
      id: json['id_kab'] ?? '',
      nama: json['nama_kab'] ?? '',
      idKab: json['id_kab'] ?? '',
    );
  }

  factory WilayahMasterModel.fromKecamatanJson(Map<String, dynamic> json) {
    return WilayahMasterModel(
      id: json['id_kec'] ?? '',
      nama: json['nama_kec'] ?? '',
      idKab: json['id_kab'] ?? '',
      idKec: json['id_kec'] ?? '',
    );
  }

  factory WilayahMasterModel.fromDesaJson(Map<String, dynamic> json) {
    return WilayahMasterModel(
      id: json['id_desa'] ?? '',
      nama: json['nama_desa'] ?? '',
      idKec: json['id_kec'] ?? '',
    );
  }
}
