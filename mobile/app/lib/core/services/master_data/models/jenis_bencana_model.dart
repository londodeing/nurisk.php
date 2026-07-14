// DEPRECATED: Gunakan core/master/models/wilayah.dart → class JenisBencana
// Akan dihapus di Phase 2.5B

class JenisBencanaMasterModel {
  final int id;
  final String nama;
  final String slug;
  final String kategori;
  final String ikonMap;

  const JenisBencanaMasterModel({
    required this.id,
    required this.nama,
    required this.slug,
    required this.kategori,
    required this.ikonMap,
  });

  factory JenisBencanaMasterModel.fromJson(Map<String, dynamic> json) {
    return JenisBencanaMasterModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      nama: json['nama'] ?? '',
      slug: json['slug'] ?? '',
      kategori: json['kategori'] ?? '',
      ikonMap: json['ikon_map'] ?? '',
    );
  }
}
