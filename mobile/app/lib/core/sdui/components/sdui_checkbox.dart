import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';

class SduiCheckbox extends SduiComponent {
  const SduiCheckbox({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final label = node.props['label'] ?? '';
    return CheckboxListTile(
      title: Text(label),
      value: false, // state managed later
      onChanged: (val) {},
    );
  }
}
