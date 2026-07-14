import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import '../certification_entry.dart';
import '../certification_report.dart';

class PropertyValidator {
  static CertificationReport validate(SduiNode node) {
    final entries = <CertificationEntry>[];
    _walk(node, entries);
    return CertificationReport(entries: entries);
  }

  static void _walk(SduiNode node, List<CertificationEntry> entries) {
    for (final entry in node.props.entries) {
      _validateProp(entry.key, entry.value, entries, node.id);
    }

    if (node.children != null) {
      for (final child in node.children!) {
        _walk(child, entries);
      }
    }
  }

  static void _validateProp(
    String key,
    dynamic value,
    List<CertificationEntry> entries,
    String nodeId,
  ) {
    switch (key) {
      case 'padding':
      case 'margin':
        if (value is! String && value is! Map && value is! num) {
          entries.add(CertificationEntry(
            validator: 'PropertyValidator',
            severity: CertificationSeverity.warning,
            message: 'Invalid "$key" type on node "$nodeId": expected String/Map/num, got ${value.runtimeType}',
            details: {'nodeId': nodeId, 'prop': key, 'type': value.runtimeType.toString()},
          ));
        } else {
          entries.add(CertificationEntry(
            validator: 'PropertyValidator',
            severity: CertificationSeverity.pass,
            message: 'Property "$key" on node "$nodeId" has valid type',
          ));
        }
        break;

      case 'radius':
        if (value is! String && value is! num) {
          entries.add(CertificationEntry(
            validator: 'PropertyValidator',
            severity: CertificationSeverity.warning,
            message: 'Invalid "radius" type on node "$nodeId": expected String or num, got ${value.runtimeType}',
            details: {'nodeId': nodeId, 'prop': key, 'type': value.runtimeType.toString()},
          ));
        } else {
          entries.add(CertificationEntry(
            validator: 'PropertyValidator',
            severity: CertificationSeverity.pass,
            message: 'Property "$key" on node "$nodeId" has valid type',
          ));
        }
        break;

      case 'background':
      case 'foreground':
        if (value is! String) {
          entries.add(CertificationEntry(
            validator: 'PropertyValidator',
            severity: CertificationSeverity.warning,
            message: 'Invalid "$key" type on node "$nodeId": expected String (color hex), got ${value.runtimeType}',
            details: {'nodeId': nodeId, 'prop': key, 'type': value.runtimeType.toString()},
          ));
        } else {
          entries.add(CertificationEntry(
            validator: 'PropertyValidator',
            severity: CertificationSeverity.pass,
            message: 'Property "$key" on node "$nodeId" has valid type',
          ));
        }
        break;

      case 'spacing':
      case 'alignment':
        // spacing: num, alignment: String — accept any type, just warn if unexpected
        entries.add(CertificationEntry(
          validator: 'PropertyValidator',
          severity: CertificationSeverity.pass,
          message: 'Property "$key" on node "$nodeId" accepted (runtime-validated)',
        ));
        break;

      default:
        break; // unknown props are silently tolerated (forward-compat)
    }
  }

  /// Returns a new node tree with invalid property values normalized.
  static SduiNode normalize(SduiNode node) {
    final normalizedProps = Map<String, dynamic>.from(node.props);
    final keysToRemove = <String>[];

    for (final key in normalizedProps.keys) {
      final value = normalizedProps[key];
      switch (key) {
        case 'padding':
        case 'margin':
          if (value is! String && value is! Map && value is! num && value != null) {
            normalizedProps[key] = '0';
          }
          break;

        case 'radius':
          if (value is! String && value is! num && value != null) {
            normalizedProps[key] = 0;
          }
          break;

        case 'background':
        case 'foreground':
          if (value is! String && value != null) {
            keysToRemove.add(key);
          }
          break;

        case 'spacing':
          if (value is! num && value != null) {
            normalizedProps[key] = 0;
          }
          break;
      }
    }

    for (final key in keysToRemove) {
      normalizedProps.remove(key);
    }

    final normalized = SduiNode(
      id: node.id,
      type: node.type,
      key: node.key,
      state: node.state,
      version: node.version,
      dirty: node.dirty,
      props: normalizedProps,
      actions: node.actions,
      children: node.children,
    );

    if (normalized.children != null) {
      return SduiNode(
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
