import '../../domain/entities/incident_entity.dart';

class IncidentModel extends IncidentEntity {
  const IncidentModel({
    required super.id,
    required super.title,
    required super.category,
    required super.severity,
    required super.status,
    required super.occurredAt,
    required super.district,
    super.thumbnailUrl,
    required super.isVerified,
  });

  factory IncidentModel.fromJson(Map<String, dynamic> json) {
    return IncidentModel(
      id: json['id']?.toString() ?? '',
      title: json['description'] ?? json['kode'] ?? 'Unknown',
      category: json['jenis'] ?? 'UNKNOWN',
      severity: 'HIGH', // Not provided by endpoint
      status: json['status'] ?? 'ACTIVE',
      occurredAt: DateTime.tryParse(json['waktu_mulai'] ?? '') ?? DateTime.now(),
      district: json['pcnu'] ?? 'Unknown Location',
      thumbnailUrl: null,
      isVerified: true, // Only verified incidents are in this feed
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'category': category,
      'severity': severity,
      'status': status,
      'occurred_at': occurredAt.toIso8601String(),
      'district': district,
      'thumbnail': thumbnailUrl,
      'verified': isVerified,
    };
  }
}
