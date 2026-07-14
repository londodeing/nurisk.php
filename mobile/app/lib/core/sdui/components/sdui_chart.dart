import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';

class SduiChart extends SduiComponent {
  const SduiChart({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Container(
      height: 200,
      color: Colors.grey.shade200,
      child: Center(
        child: Text('Chart: ${node.props["type"]}'),
      ),
    );
  }
}
