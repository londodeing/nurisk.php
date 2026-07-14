import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/runtime/actions/action_dispatcher_scope.dart';
import 'package:nurisk_mobile/core/runtime/actions/runtime_action.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';

class SduiSwitch extends SduiComponent {
  const SduiSwitch({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final label = node.props['label'] as String? ?? '';
    final initialValue = node.props['value'] as bool? ?? false;
    final actionJson = node.actions['on_change'] as Map<String, dynamic>?;
    final subtitle = node.props['subtitle'] as String?;

    return SwitchListTile(
      title: Text(label),
      subtitle: subtitle != null ? Text(subtitle) : null,
      value: initialValue,
      onChanged: actionJson != null
          ? (val) {
              final dispatcher = ActionDispatcherScope.of(context);
              final action = RuntimeAction.fromJson({
                ...actionJson,
                'payload': {
                  ...(actionJson['payload'] as Map<String, dynamic>? ?? {}),
                  'value': val,
                },
              });
              dispatcher.dispatch(action, nodeState: node.state);
            }
          : null,
    );
  }
}
