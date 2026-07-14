enum CertificationSeverity { pass, warning, error }

class CertificationEntry {
  final String validator;
  final CertificationSeverity severity;
  final String message;
  final Map<String, dynamic>? details;

  const CertificationEntry({
    required this.validator,
    required this.severity,
    required this.message,
    this.details,
  });

  String get icon {
    switch (severity) {
      case CertificationSeverity.pass:
        return '✓';
      case CertificationSeverity.warning:
        return '⚠';
      case CertificationSeverity.error:
        return '✗';
    }
  }

  @override
  String toString() => '$icon [$validator] $message';
}
