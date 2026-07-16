import '../../domain/entities/incident_entity.dart';

class IncidentModel extends IncidentEntity {
  const IncidentModel({
    required super.id,
    super.kode,
    required super.title,
    required super.category,
    required super.severity,
    required super.status,
    required super.occurredAt,
    required super.district,
    super.thumbnailUrl,
    required super.isVerified,
    super.latitude,
    super.longitude,
    super.needsNumeric,
    super.korbanSummary,
  });

  factory IncidentModel.fromJson(Map<String, dynamic> json) {
    double? parseLat(dynamic v) {
      if (v == null) return null;
      if (v is num) return v.toDouble();
      final s = v.toString().trim();
      return s.isEmpty ? null : double.tryParse(s);
    }

    return IncidentModel(
      id: json['id']?.toString() ?? '',
      kode: json['kode']?.toString(),
      title: json['description'] ?? 'Tidak ada keterangan',
      category: json['jenis'] ?? 'UNKNOWN',
      severity: 'HIGH',
      status: json['status'] ?? 'ACTIVE',
      occurredAt: DateTime.tryParse(json['waktu_mulai'] ?? '') ?? DateTime.now(),
      district: json['pcnu'] ?? 'Unknown Location',
      thumbnailUrl: null,
      isVerified: true,
      latitude: parseLat(json['lat']),
      longitude: parseLat(json['lng']),
      needsNumeric: json['needs_numeric'] is Map
          ? Map<String, dynamic>.from(json['needs_numeric'])
          : {},
      korbanSummary: (json['korban_summary'] as num?)?.toInt() ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'kode': kode,
      'title': title,
      'category': category,
      'severity': severity,
      'status': status,
      'occurred_at': occurredAt.toIso8601String(),
      'district': district,
      'thumbnail': thumbnailUrl,
      'verified': isVerified,
      'lat': latitude,
      'lng': longitude,
      'needs_numeric': needsNumeric,
      'korban_summary': korbanSummary,
    };
  }
}
