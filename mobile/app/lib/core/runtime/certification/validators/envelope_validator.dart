import '../certification_entry.dart';
import '../certification_report.dart';

class EnvelopeValidator {
  static const _requiredFields = ['schema_version', 'scene_id', 'version', 'ttl_seconds', 'root'];

  static CertificationReport validate(Map<String, dynamic> envelope) {
    final entries = <CertificationEntry>[];

    for (final field in _requiredFields) {
      if (!envelope.containsKey(field) || envelope[field] == null) {
        entries.add(CertificationEntry(
          validator: 'EnvelopeValidator',
          severity: CertificationSeverity.error,
          message: 'Missing required envelope field: $field',
          details: {'field': field},
        ));
      } else {
        entries.add(CertificationEntry(
          validator: 'EnvelopeValidator',
          severity: CertificationSeverity.pass,
          message: 'Envelope field "$field" present',
        ));
      }
    }

    return CertificationReport(entries: entries);
  }
}
