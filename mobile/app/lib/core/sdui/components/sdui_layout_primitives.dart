import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_renderer.dart';

class SduiExpanded extends SduiComponent {
  const SduiExpanded({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final flex = node.props['flex'] as int? ?? 1;
    final childNode = (node.children != null && node.children!.isNotEmpty) ? node.children!.first : null;
    
    return Expanded(
      flex: flex,
      child: childNode != null ? SduiRenderer(node: childNode) : const SizedBox.shrink(),
    );
  }
}

class SduiFlexible extends SduiComponent {
  const SduiFlexible({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final flex = node.props['flex'] as int? ?? 1;
    final fitStr = node.props['fit'] as String? ?? 'loose';
    final fit = fitStr == 'tight' ? FlexFit.tight : FlexFit.loose;
    final childNode = (node.children != null && node.children!.isNotEmpty) ? node.children!.first : null;
    
    return Flexible(
      flex: flex,
      fit: fit,
      child: childNode != null ? SduiRenderer(node: childNode) : const SizedBox.shrink(),
    );
  }
}

class SduiSizedBox extends SduiComponent {
  const SduiSizedBox({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final width = (node.props['width'] as num?)?.toDouble();
    final height = (node.props['height'] as num?)?.toDouble();
    
    return SizedBox(
      width: width,
      height: height,
    );
  }
}

class SduiAspectRatio extends SduiComponent {
  const SduiAspectRatio({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final ratio = (node.props['ratio'] as num?)?.toDouble() ?? 1.0;
    final childNode = (node.children != null && node.children!.isNotEmpty) ? node.children!.first : null;
    
    return AspectRatio(
      aspectRatio: ratio,
      child: childNode != null ? SduiRenderer(node: childNode) : const SizedBox.shrink(),
    );
  }
}
