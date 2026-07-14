import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_nss_utils.dart';

class SduiBadge extends SduiComponent {
  const SduiBadge({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final text = node.props['text']?.toString() ?? '';
    
    // NSS uses "background" and "foreground"
    final bgColor = SduiNssUtils.parseColor(node.props['background'] as String?) ?? Colors.green.shade700;
    final textColor = SduiNssUtils.parseColor(node.props['foreground'] as String?) ?? Colors.white;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: textColor,
          fontSize: 10,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}
