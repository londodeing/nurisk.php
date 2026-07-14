import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import '../certification_entry.dart';
import '../certification_report.dart';

class ActionValidator {
  static const knownActionTypes = {
    'navigate',
    'submit',
    'reload',
    'toast',
    'action',
  };

  static CertificationReport validate(SduiNode node) {
    final entries = <CertificationEntry>[];
    _walk(node, entries);
    return CertificationReport(entries: entries);
  }

  static void _walk(SduiNode node, List<CertificationEntry> entries) {
    for (final entry in node.actions.entries) {
      final event = entry.key; // e.g. 'on_tap', 'on_submit'
      final actionValue = entry.value;

      if (actionValue is Map<String, dynamic>) {
        _validateAction(event, actionValue, entries);
      } else if (actionValue is List) {
        for (final item in actionValue) {
          if (item is Map<String, dynamic>) {
            _validateAction(event, item, entries);
          }
        }
      }
    }

    if (node.children != null) {
      for (final child in node.children!) {
        _walk(child, entries);
      }
    }
  }

  static void _validateAction(
    String event,
    Map<String, dynamic> actionJson,
    List<CertificationEntry> entries,
  ) {
    final type = actionJson['type'] as String?;

    if (type == null) {
      entries.add(CertificationEntry(
        validator: 'ActionValidator',
        severity: CertificationSeverity.warning,
        message: 'Action on "$event" is missing "type" field',
        details: {'event': event, 'action': actionJson},
      ));
      return;
    }

    if (knownActionTypes.contains(type)) {
      entries.add(CertificationEntry(
        validator: 'ActionValidator',
        severity: CertificationSeverity.pass,
        message: 'Action type "$type" on "$event" is supported',
      ));
    } else {
      entries.add(CertificationEntry(
        validator: 'ActionValidator',
        severity: CertificationSeverity.warning,
        message: 'Unknown action type "$type" on "$event"',
        details: {'event': event, 'type': type},
      ));
    }

    // Validate on_success chains recursively
    if (actionJson['on_success'] != null) {
      final chain = actionJson['on_success'];
      if (chain is List) {
        for (final item in chain) {
          if (item is Map<String, dynamic>) {
            _validateAction('$event.on_success', item, entries);
          }
        }
      } else if (chain is Map) {
        _validateAction('$event.on_success', chain as Map<String, dynamic>, entries);
      }
    }

    // Validate on_failure chains recursively
    if (actionJson['on_failure'] != null) {
      final chain = actionJson['on_failure'];
      if (chain is List) {
        for (final item in chain) {
          if (item is Map<String, dynamic>) {
            _validateAction('$event.on_failure', item, entries);
          }
        }
      } else if (chain is Map) {
        _validateAction('$event.on_failure', chain as Map<String, dynamic>, entries);
      }
    }
  }
}
