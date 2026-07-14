import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';

class SduiDropdown extends SduiComponent {
  const SduiDropdown({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final options = (node.props['options'] as List<dynamic>?) ?? [];
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: DropdownButtonFormField<String>(
        items: options.map((e) => DropdownMenuItem<String>(
          value: e['value'],
          child: Text(e['label']),
        )).toList(),
        onChanged: (v) {},
        decoration: InputDecoration(
          labelText: node.props['label'],
          border: const OutlineInputBorder(),
        ),
      ),
    );
  }
}
