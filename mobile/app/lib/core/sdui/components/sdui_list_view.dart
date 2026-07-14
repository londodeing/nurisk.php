import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../sdui_component.dart';
import '../sdui_renderer.dart';

class SduiListView extends SduiComponent {
  const SduiListView({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final padding = node.props['padding'] as num? ?? 16;
    final spacing = node.props['spacing'] as num? ?? 16;
    final children = node.children ?? [];

    return ListView.separated(
      padding: EdgeInsets.all(padding.toDouble()),
      itemCount: children.length,
      separatorBuilder: (context, index) => SizedBox(height: spacing.toDouble()),
      itemBuilder: (context, index) {
        return SduiRenderer(node: children[index]);
      },
    );
  }
}
