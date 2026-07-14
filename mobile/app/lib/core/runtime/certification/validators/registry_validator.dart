import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_registry.dart';
import '../certification_entry.dart';
import '../certification_report.dart';

class RegistryValidator {
  static CertificationReport validate(SduiNode node) {
    final entries = <CertificationEntry>[];
    _walk(node, entries);
    return CertificationReport(entries: entries);
  }

  static void _walk(SduiNode node, List<CertificationEntry> entries) {
    final builder = SduiRegistry.instance.getBuilder(node.type);
    if (builder == null) {
      entries.add(CertificationEntry(
        validator: 'RegistryValidator',
        severity: CertificationSeverity.warning,
        message: 'Unknown component type: ${node.type}',
        details: {'type': node.type, 'id': node.id},
      ));
    } else {
      entries.add(CertificationEntry(
        validator: 'RegistryValidator',
        severity: CertificationSeverity.pass,
        message: 'Component "${node.type}" is registered',
      ));
    }

    if (node.children != null) {
      for (final child in node.children!) {
        _walk(child, entries);
      }
    }
  }
}
