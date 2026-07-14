import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';

class SduiUnknownComponent extends SduiComponent {
  const SduiUnknownComponent({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // Log to RuntimeLogger
    WidgetsBinding.instance.addPostFrameCallback((_) {
      RuntimeLogger.e('Missing Renderer for SDUI Component: ${node.type}', null, StackTrace.current);
    });

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey.shade300, style: BorderStyle.solid),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.developer_mode, color: Colors.grey.shade500, size: 16),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              'Unsupported Component: ${node.type}',
              style: TextStyle(color: Colors.grey.shade600, fontSize: 11),
            ),
          ),
        ],
      ),
    );
  }
}
