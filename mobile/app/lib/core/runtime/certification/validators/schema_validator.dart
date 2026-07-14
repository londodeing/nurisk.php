import '../certification_entry.dart';
import '../certification_report.dart';

class SchemaValidator {
  static CertificationReport validate(Map<String, dynamic> envelope) {
    final entries = <CertificationEntry>[];

    // 1. schema_version check
    final schemaVersion = envelope['schema_version'] as String?;
    if (schemaVersion == null) {
      entries.add(CertificationEntry(
        validator: 'SchemaValidator',
        severity: CertificationSeverity.error,
        message: 'schema_version is null',
      ));
      return CertificationReport(entries: entries);
    }

    if (!schemaVersion.startsWith('1.')) {
      entries.add(CertificationEntry(
        validator: 'SchemaValidator',
        severity: CertificationSeverity.error,
        message: 'Unsupported schema_version "$schemaVersion". Expected 1.x.x.',
        details: {'schema_version': schemaVersion},
      ));
      return CertificationReport(entries: entries);
    }

    entries.add(CertificationEntry(
      validator: 'SchemaValidator',
      severity: CertificationSeverity.pass,
      message: 'schema_version $schemaVersion is supported',
    ));

    // 2. root structure check
    final root = envelope['root'] as Map<String, dynamic>?;
    if (root == null) {
      entries.add(CertificationEntry(
        validator: 'SchemaValidator',
        severity: CertificationSeverity.error,
        message: 'root is null',
      ));
      return CertificationReport(entries: entries);
    }

    if (root['type'] == null) {
      entries.add(CertificationEntry(
        validator: 'SchemaValidator',
        severity: CertificationSeverity.error,
        message: 'root.type is required',
      ));
    } else {
      entries.add(CertificationEntry(
        validator: 'SchemaValidator',
        severity: CertificationSeverity.pass,
        message: 'root.type present',
      ));
    }

    if (root['id'] == null) {
      entries.add(CertificationEntry(
        validator: 'SchemaValidator',
        severity: CertificationSeverity.warning,
        message: 'root.id is missing — auto-generated id will be used',
      ));
    } else {
      entries.add(CertificationEntry(
        validator: 'SchemaValidator',
        severity: CertificationSeverity.pass,
        message: 'root.id present',
      ));
    }

    return CertificationReport(entries: entries);
  }
}
