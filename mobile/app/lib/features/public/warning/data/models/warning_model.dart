import '../../domain/entities/warning_entity.dart';

class WarningModel extends WarningEntity {
  const WarningModel({
    required super.id,
    required super.source,
    required super.title,
    required super.description,
    required super.severity,
    required super.issuedAt,
    super.expiresAt,
  });

  factory WarningModel.fromJson(Map<String, dynamic> json) {
    return WarningModel(
      id: json['id'] ?? '',
      source: json['source'] ?? 'Unknown',
      title: json['title'] ?? 'Warning',
      description: json['description'] ?? '',
      severity: _parseSeverity(json['severity']),
      issuedAt: DateTime.tryParse(json['issued_at'] ?? '') ?? DateTime.now(),
      expiresAt: json['expires_at'] != null ? DateTime.tryParse(json['expires_at']) : null,
    );
  }

  static WarningSeverity _parseSeverity(String? severity) {
    switch (severity?.toLowerCase()) {
      case 'critical':
        return WarningSeverity.critical;
      case 'warning':
        return WarningSeverity.warning;
      case 'info':
      default:
        return WarningSeverity.info;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'source': source,
      'title': title,
      'description': description,
      'severity': severity.name,
      'issued_at': issuedAt.toIso8601String(),
      'expires_at': expiresAt?.toIso8601String(),
    };
  }
}
