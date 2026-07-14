import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_renderer.dart';

class SduiBottomSheet extends SduiComponent {
  const SduiBottomSheet({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: node.children?.map((n) => SduiRenderer(node: n)).toList() ?? [],
      ),
    );
  }
}
