import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'certification_entry.dart';
import 'certification_report.dart';
import 'certified_scene.dart';
import 'validators/envelope_validator.dart';
import 'validators/schema_validator.dart';
import 'validators/registry_validator.dart';
import 'validators/action_validator.dart';
import 'validators/state_validator.dart';
import 'validators/property_validator.dart';

class FlutterCertificationEngine {
  const FlutterCertificationEngine();

  CertifiedScene certify(Map<String, dynamic> envelope) {
    var report = const CertificationReport();

    // 1. Envelope — critical
    var subReport = EnvelopeValidator.validate(envelope);
    report = report.merge(subReport);
    if (subReport.hasErrors) {
      return CertifiedScene(
        status: CertificationStatus.invalidEnvelope,
        report: report,
      );
    }

    // 2. Schema — critical
    subReport = SchemaValidator.validate(envelope);
    report = report.merge(subReport);
    if (subReport.hasErrors) {
      return CertifiedScene(
        status: CertificationStatus.unsupportedSchema,
        report: report,
      );
    }

    // 3. Parse root
    final rootJson = envelope['root'] as Map<String, dynamic>;
    SduiNode root;
    try {
      root = SduiNode.fromJson(rootJson);
    } catch (e) {
      report = report.addEntry(CertificationEntry(
        validator: 'FlutterCertificationEngine',
        severity: CertificationSeverity.error,
        message: 'Failed to parse root node: $e',
      ));
      return CertifiedScene(
        status: CertificationStatus.parseError,
        report: report,
      );
    }

    // 4. Registry — non-critical
    subReport = RegistryValidator.validate(root);
    report = report.merge(subReport);

    // 5. Action — non-critical
    subReport = ActionValidator.validate(root);
    report = report.merge(subReport);

    // 6. State — normalize + validate
    root = StateValidator.normalize(root);
    subReport = StateValidator.validate(root);
    report = report.merge(subReport);

    // 7. Property — normalize + validate
    root = PropertyValidator.normalize(root);
    subReport = PropertyValidator.validate(root);
    report = report.merge(subReport);

    return CertifiedScene(
      status: CertificationStatus.valid,
      report: report,
      root: root,
    );
  }
}
