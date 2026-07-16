class IncidentEntity {
  final String id;
  final String? kode;
  final String title;
  final String category;
  final String severity;
  final String status;
  final DateTime occurredAt;
  final String district;
  final String? thumbnailUrl;
  final bool isVerified;
  final double? latitude;
  final double? longitude;
  final Map<String, dynamic> needsNumeric;
  final int korbanSummary;

  const IncidentEntity({
    required this.id,
    this.kode,
    required this.title,
    required this.category,
    required this.severity,
    required this.status,
    required this.occurredAt,
    required this.district,
    this.thumbnailUrl,
    required this.isVerified,
    this.latitude,
    this.longitude,
    this.needsNumeric = const {},
    this.korbanSummary = 0,
  });
}
