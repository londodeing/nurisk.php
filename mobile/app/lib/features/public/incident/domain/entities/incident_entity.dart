class IncidentEntity {
  final String id;
  final String title;
  final String category;
  final String severity;
  final String status;
  final DateTime occurredAt;
  final String district;
  final String? thumbnailUrl;
  final bool isVerified;

  const IncidentEntity({
    required this.id,
    required this.title,
    required this.category,
    required this.severity,
    required this.status,
    required this.occurredAt,
    required this.district,
    this.thumbnailUrl,
    required this.isVerified,
  });
}
