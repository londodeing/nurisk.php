import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../sdui_node.dart';
import '../sdui_component.dart';

class SduiTimeline extends SduiComponent {
  const SduiTimeline({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final title = node.props['title'] ?? 'Aktivitas';
    final items = node.props['items'] as List<dynamic>? ?? [];

    return Card(
      elevation: 12,
      shadowColor: Colors.black.withOpacity(0.3),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
      color: Colors.white.withOpacity(0.95),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Text(
              title,
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
          ),
          if (items.isEmpty)
            const Padding(
              padding: EdgeInsets.all(16),
              child: Text("Belum ada aktivitas.", style: const TextStyle(color: Colors.grey)),
            ),
          ...items.map((item) {
            final itemMap = item as Map<String, dynamic>;
            return ListTile(
              leading: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.circle, size: 12, color: Colors.blue),
                  Container(width: 2, height: 24, color: Colors.blue.shade100),
                ],
              ),
              title: Text(itemMap['description'] ?? ''),
              subtitle: Text(itemMap['time'] ?? '', style: const TextStyle(fontSize: 12)),
            );
          }).toList(),
          const SizedBox(height: 8),
        ],
      ),
    );
  }
}
