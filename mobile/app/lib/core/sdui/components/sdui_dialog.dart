import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_renderer.dart';

class SduiDialog extends SduiComponent {
  const SduiDialog({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return AlertDialog(
      title: Text(node.props['title'] ?? 'Dialog'),
      content: SingleChildScrollView(
        child: Column(
          children: node.children?.map((n) => SduiRenderer(node: n)).toList() ?? [],
        ),
      ),
    );
  }
}
