import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_renderer.dart';

class SduiTabs extends SduiComponent {
  const SduiTabs({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // Typically requires a TabController, for now we render a placeholder layout
    return DefaultTabController(
      length: node.children?.length ?? 0,
      child: Column(
        children: [
          TabBar(
            tabs: node.children?.map((n) => Tab(text: n.props['title'] ?? 'Tab')).toList() ?? [],
          ),
          SizedBox(
            height: 300,
            child: TabBarView(
              children: node.children?.map((n) => SduiRenderer(node: n)).toList() ?? [],
            ),
          )
        ],
      ),
    );
  }
}
