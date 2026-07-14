import 'package:nurisk_mobile/core/runtime/state/runtime_node_state.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import '../certification_entry.dart';
import '../certification_report.dart';

class StateValidator {
  static CertificationReport validate(SduiNode node) {
    final entries = <CertificationEntry>[];
    _walk(node, entries);
    return CertificationReport(entries: entries);
  }

  static void _walk(SduiNode node, List<CertificationEntry> entries) {
    final s = node.state;

    // visible=false + loading=true → warning (loading hidden)
    if (!s.visible && s.loading) {
      entries.add(CertificationEntry(
        validator: 'StateValidator',
        severity: CertificationSeverity.warning,
        message: 'Node "${node.id}" has visible=false but loading=true — loading indicator is hidden',
        details: {'id': node.id, 'type': node.type},
      ));
    }

    // visible=false + enabled=false → redundant, but not an error
    // selected without enabled → warning
    if (s.selected && !s.enabled) {
      entries.add(CertificationEntry(
        validator: 'StateValidator',
        severity: CertificationSeverity.warning,
        message: 'Node "${node.id}" is selected but disabled — visual conflict',
        details: {'id': node.id, 'type': node.type},
      ));
    }

    if (node.children != null) {
      for (final child in node.children!) {
        _walk(child, entries);
      }
    }
  }

  /// Returns a new node tree with invalid state combinations normalized.
  static SduiNode normalize(SduiNode node) {
    RuntimeNodeState normalizedState = node.state;

    // visible=false → loading should also be false
    if (!normalizedState.visible && normalizedState.loading) {
      normalizedState = normalizedState.copyWith(loading: false);
    }

    SduiNode normalized = node;
    if (normalizedState != node.state) {
      normalized = SduiNode(
        id: node.id,
        type: node.type,
        key: node.key,
        state: normalizedState,
        version: node.version,
        dirty: node.dirty,
        props: node.props,
        actions: node.actions,
        children: node.children,
      );
    }

    // Recurse children
    if (normalized.children != null) {
      normalized = SduiNode(
        id: normalized.id,
        type: normalized.type,
        key: normalized.key,
        state: normalized.state,
        version: normalized.version,
        dirty: normalized.dirty,
        props: normalized.props,
        actions: normalized.actions,
        children: normalized.children!.map(normalize).toList(),
      );
    }

    return normalized;
  }
}
