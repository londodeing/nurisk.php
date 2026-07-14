import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_renderer.dart';

class SduiRow extends SduiComponent {
  const SduiRow({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final spacing = (node.props['spacing'] as num?)?.toDouble() ?? 0;
    
    CrossAxisAlignment cross = CrossAxisAlignment.center;
    switch (node.props['crossAxisAlignment']) {
      case 'start': cross = CrossAxisAlignment.start; break;
      case 'end': cross = CrossAxisAlignment.end; break;
      case 'stretch': cross = CrossAxisAlignment.stretch; break;
      case 'baseline': cross = CrossAxisAlignment.baseline; break;
      case 'center': default: cross = CrossAxisAlignment.center; break;
    }

    MainAxisAlignment main = MainAxisAlignment.start;
    switch (node.props['mainAxisAlignment']) {
      case 'center': main = MainAxisAlignment.center; break;
      case 'end': main = MainAxisAlignment.end; break;
      case 'spaceBetween': main = MainAxisAlignment.spaceBetween; break;
      case 'spaceAround': main = MainAxisAlignment.spaceAround; break;
      case 'spaceEvenly': main = MainAxisAlignment.spaceEvenly; break;
      case 'start': default: main = MainAxisAlignment.start; break;
    }

    final List<Widget> children = [];
    if (node.children != null) {
      for (int i = 0; i < node.children!.length; i++) {
        children.add(SduiRenderer(node: node.children![i]));
        if (spacing > 0 && i < node.children!.length - 1) {
          children.add(SizedBox(width: spacing));
        }
      }
    }

    final row = Row(
      mainAxisAlignment: main,
      crossAxisAlignment: cross,
      children: children,
    );

    if (node.props['scrollable'] == true) {
      return SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: row,
      );
    }

    return row;
  }
}
