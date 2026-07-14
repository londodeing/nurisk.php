import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../sdui_node.dart';
import '../sdui_component.dart';
import '../../registry/action_resolver.dart';
import '../../registry/dynamic_icon.dart';

class SduiGrid extends SduiComponent {
  const SduiGrid({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final title = node.props['title'];
    final layout = node.props['layout'] ?? 'responsiveGrid';
    final items = node.props['items'] as List<dynamic>? ?? [];

    final screenWidth = MediaQuery.of(context).size.width;
    final int columns = (layout == 'responsiveGrid' || layout == 'auto')
        ? (screenWidth / 120).floor().clamp(2, 4)
        : (node.props['columns'] as int? ?? 3);

    Widget grid = GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: columns,
        crossAxisSpacing: 16,
        mainAxisSpacing: 16,
        childAspectRatio: 1.0, // Ensures squares
      ),
      itemCount: items.length,
      itemBuilder: (context, index) {
        final item = items[index];
        final iconStr = item['icon'] as String?;
        final label = item['label'] ?? item['title'] ?? '';
        final value = item['value']?.toString();

        return InkWell(
          onTap: () {
            if (item['action'] != null) {
              ActionResolver.execute(item['action'], ref);
            } else if (item['target'] != null) {
              ActionResolver.execute({'type': 'navigate', 'target': item['target']}, ref);
            }
          },
          borderRadius: BorderRadius.circular(12),
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (iconStr != null) ...[
                  Icon(DynamicIcon.get(iconStr), size: 32, color: Colors.blue.shade700),
                  const SizedBox(height: 8),
                ],
                if (value != null) ...[
                  Text(
                    value,
                    style: TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.blue.shade900,
                    ),
                  ),
                  const SizedBox(height: 4),
                ],
                Text(
                  label,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500),
                ),
              ],
            ),
          ),
        );
      },
    );

    if (title != null) {
      return Card(
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.all(16.0).copyWith(bottom: 0),
              child: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ),
            grid,
          ],
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: grid,
    );
  }
}
