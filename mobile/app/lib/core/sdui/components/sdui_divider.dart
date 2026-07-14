import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';

class SduiDivider extends SduiComponent {
  const SduiDivider({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return const Divider(
      height: 1,
      thickness: 1,
      color: Color(0xFFE5E7EB), // Equivalent to gray-200 / surface variant
    );
  }
}
