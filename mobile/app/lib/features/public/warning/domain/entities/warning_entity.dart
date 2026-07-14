enum WarningSeverity {
  info,
  warning,
  critical,
}

class WarningEntity {
  final String id;
  final String source; // e.g. "BMKG", "BNPB", "PWNU", "Incident"
  final String title;
  final String description;
  final WarningSeverity severity;
  final DateTime issuedAt;
  final DateTime? expiresAt;

  const WarningEntity({
    required this.id,
    required this.source,
    required this.title,
    required this.description,
    required this.severity,
    required this.issuedAt,
    this.expiresAt,
  });

  bool get isActive {
    if (expiresAt == null) return true;
    return DateTime.now().isBefore(expiresAt!);
  }
}
