import 'certification_entry.dart';

class CertificationReport {
  final List<CertificationEntry> entries;

  const CertificationReport({this.entries = const []});

  bool get hasErrors => entries.any((e) => e.severity == CertificationSeverity.error);
  bool get hasWarnings => entries.any((e) => e.severity == CertificationSeverity.warning);
  bool get passed => !hasErrors;

  List<CertificationEntry> get passes =>
      entries.where((e) => e.severity == CertificationSeverity.pass).toList();
  List<CertificationEntry> get warnings =>
      entries.where((e) => e.severity == CertificationSeverity.warning).toList();
  List<CertificationEntry> get errors =>
      entries.where((e) => e.severity == CertificationSeverity.error).toList();

  CertificationReport merge(CertificationReport other) {
    return CertificationReport(entries: [...entries, ...other.entries]);
  }

  CertificationReport addEntry(CertificationEntry entry) {
    return CertificationReport(entries: [...entries, entry]);
  }

  @override
  String toString() {
    final sb = StringBuffer();
    sb.writeln('FlutterCertificationReport');
    sb.writeln('─' * 40);
    for (final entry in entries) {
      sb.writeln(entry);
    }
    sb.writeln('─' * 40);
    if (passed) {
      sb.writeln('✓ Safe To Render${hasWarnings ? ' (with warnings)' : ''}');
    } else {
      sb.writeln('✗ Render Blocked — ${errors.length} error(s)');
    }
    return sb.toString();
  }
}
