class JenisBencanaModel {
  final int id;
  final String nama;

  const JenisBencanaModel({required this.id, required this.nama});

  factory JenisBencanaModel.fromJson(Map<String, dynamic> json) {
    return JenisBencanaModel(
      id: json['id'] is int
          ? json['id']
          : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      nama: json['nama'] ?? '',
    );
  }
}
