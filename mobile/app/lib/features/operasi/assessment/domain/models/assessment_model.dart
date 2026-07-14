class AssessmentModel {
  final int? id;
  final String uuidInsiden;
  final String? jenisLaporan;
  final String? cakupanWilayahDeskripsi;
  final double? latitude;
  final double? longitude;
  final bool isSubmitted;

  AssessmentModel({
    this.id,
    required this.uuidInsiden,
    this.jenisLaporan,
    this.cakupanWilayahDeskripsi,
    this.latitude,
    this.longitude,
    this.isSubmitted = false,
  });

  factory AssessmentModel.fromJson(Map<String, dynamic> json) {
    return AssessmentModel(
      id: json['id_assessment_utama'],
      uuidInsiden: json['uuid_insiden'] ?? '',
      jenisLaporan: json['jenis_laporan'],
      cakupanWilayahDeskripsi: json['cakupan_wilayah_deskripsi'],
      latitude: json['latitude'] != null ? double.tryParse(json['latitude'].toString()) : null,
      longitude: json['longitude'] != null ? double.tryParse(json['longitude'].toString()) : null,
      isSubmitted: json['status_dokumen'] == 'final',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'uuid_insiden': uuidInsiden,
      'jenis_laporan': jenisLaporan,
      'cakupan_wilayah_deskripsi': cakupanWilayahDeskripsi,
      'latitude': latitude,
      'longitude': longitude,
    };
  }

  AssessmentModel copyWith({
    int? id,
    String? uuidInsiden,
    String? jenisLaporan,
    String? cakupanWilayahDeskripsi,
    double? latitude,
    double? longitude,
    bool? isSubmitted,
  }) {
    return AssessmentModel(
      id: id ?? this.id,
      uuidInsiden: uuidInsiden ?? this.uuidInsiden,
      jenisLaporan: jenisLaporan ?? this.jenisLaporan,
      cakupanWilayahDeskripsi: cakupanWilayahDeskripsi ?? this.cakupanWilayahDeskripsi,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      isSubmitted: isSubmitted ?? this.isSubmitted,
    );
  }
}
