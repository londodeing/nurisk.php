import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';

class SduiFormField extends SduiComponent {
  const SduiFormField({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final label = node.props['label'] ?? '';
    final hint = node.props['hint'] ?? '';
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: TextFormField(
        decoration: InputDecoration(
          labelText: label,
          hintText: hint,
          border: const OutlineInputBorder(),
        ),
      ),
    );
  }
}
