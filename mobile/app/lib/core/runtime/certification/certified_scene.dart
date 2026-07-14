import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'certification_report.dart';

enum CertificationStatus { valid, invalidEnvelope, unsupportedSchema, parseError }

class CertifiedScene {
  final CertificationStatus status;
  final CertificationReport report;
  final SduiNode? root;

  const CertifiedScene({
    required this.status,
    required this.report,
    this.root,
  });

  bool get isValid => status == CertificationStatus.valid;
  bool get isError => !isValid;
}
