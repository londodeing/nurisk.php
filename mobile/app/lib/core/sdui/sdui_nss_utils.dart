import 'package:flutter/material.dart';

class SduiNssUtils {
  static EdgeInsets parseEdgeInsets(dynamic prop) {
    if (prop == null) return EdgeInsets.zero;
    if (prop is Map) {
      if (prop.containsKey('all')) {
        final val = (prop['all'] as num?)?.toDouble() ?? 0.0;
        return EdgeInsets.all(val);
      }
      if (prop.containsKey('x') || prop.containsKey('y')) {
        final x = (prop['x'] as num?)?.toDouble() ?? 0.0;
        final y = (prop['y'] as num?)?.toDouble() ?? 0.0;
        return EdgeInsets.symmetric(horizontal: x, vertical: y);
      }
      final t = (prop['t'] as num?)?.toDouble() ?? 0.0;
      final b = (prop['b'] as num?)?.toDouble() ?? 0.0;
      final l = (prop['l'] as num?)?.toDouble() ?? 0.0;
      final r = (prop['r'] as num?)?.toDouble() ?? 0.0;
      return EdgeInsets.only(top: t, bottom: b, left: l, right: r);
    }
    return EdgeInsets.zero;
  }

  static BorderRadius parseRadius(String? token) {
    switch (token) {
      case 'none': return BorderRadius.zero;
      case 'sm': return BorderRadius.circular(4);
      case 'md': return BorderRadius.circular(8);
      case 'lg': return BorderRadius.circular(16);
      case 'xl': return BorderRadius.circular(24);
      case 'full': return BorderRadius.circular(9999);
      default: return BorderRadius.zero;
    }
  }

  static Color? parseColor(String? token) {
    switch (token) {
      case 'primary': return Colors.green.shade700;
      case 'secondary': return Colors.blue.shade700;
      case 'surface': return Colors.white;
      case 'background': return Colors.grey.shade50;
      case 'danger': return Colors.red.shade600;
      case 'warning': return Colors.orange.shade500;
      case 'info': return Colors.blue.shade500;
      case 'success': return Colors.green.shade500;
      case 'transparent': return Colors.transparent;
      case 'text_main': return Colors.black87;
      case 'text_muted': return Colors.black54;
      case 'text_inverse': return Colors.white;
      default: return null;
    }
  }
}
