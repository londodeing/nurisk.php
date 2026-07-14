import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_nss_utils.dart';

class SduiText extends SduiComponent {
  const SduiText({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final text = node.props['text']?.toString() ?? '';
    final styleStr = node.props['style'];
    
    TextStyle? style;
    if (styleStr == 'headline') style = Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold);
    if (styleStr == 'title') style = Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold);
    if (styleStr == 'subtitle') style = Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600);
    if (styleStr == 'caption') style = Theme.of(context).textTheme.bodySmall;
    if (styleStr == 'body') style = Theme.of(context).textTheme.bodyMedium;
    if (styleStr == 'metric') style = Theme.of(context).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.bold);

    // NSS uses "foreground" instead of "color"
    final color = SduiNssUtils.parseColor(node.props['foreground'] as String?);

    if (color != null) {
      style = (style ?? const TextStyle()).copyWith(color: color);
    }

    TextAlign? textAlign;
    switch (node.props['align']) {
      case 'center': textAlign = TextAlign.center; break;
      case 'right': textAlign = TextAlign.right; break;
      case 'justify': textAlign = TextAlign.justify; break;
      case 'left': default: textAlign = TextAlign.left; break;
    }

    TextOverflow? overflow;
    if (node.props['overflow'] == 'ellipsis') overflow = TextOverflow.ellipsis;

    final maxLines = node.props['maxLines'] as int?;

    return Text(
      text,
      style: style,
      textAlign: textAlign,
      maxLines: maxLines,
      overflow: overflow,
    );
  }
}
