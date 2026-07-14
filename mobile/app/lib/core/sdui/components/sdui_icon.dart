import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_nss_utils.dart';

class SduiIcon extends SduiComponent {
  const SduiIcon({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final name = node.props['name'];
    final size = (node.props['size'] as num?)?.toDouble() ?? 24.0;
    
    IconData icon = Icons.info_outline;
    switch (name) {
      case 'warning': icon = Icons.warning_amber_rounded; break;
      case 'cloud': icon = Icons.cloud; break;
      case 'person': icon = Icons.person; break;
      case 'account_circle': icon = Icons.account_circle; break;
      case 'login': icon = Icons.login; break;
      case 'person_add': icon = Icons.person_add; break;
      case 'search': icon = Icons.search; break;
      case 'favorite': icon = Icons.favorite; break;
      case 'report': icon = Icons.report; break;
      case 'map': icon = Icons.map; break;
      case 'check_circle': icon = Icons.check_circle; break;
      case 'edit_document': icon = Icons.edit_document; break;
      case 'home_work': icon = Icons.home_work; break;
      case 'assignment': icon = Icons.assignment; break;
      case 'fact_check': icon = Icons.fact_check; break;
      case 'shield': icon = Icons.shield; break;
      case 'fingerprint': icon = Icons.fingerprint; break;
      case 'offline_bolt': icon = Icons.offline_bolt; break;
      case 'info': icon = Icons.info; break;
      case 'logout': icon = Icons.logout; break;
      case 'chevron_right': icon = Icons.chevron_right; break;
      case 'no_accounts': icon = Icons.no_accounts; break;
      case 'phone': icon = Icons.phone; break;
      case 'schedule': icon = Icons.schedule; break;
      case 'lock': icon = Icons.lock; break;
      case 'group': icon = Icons.group; break;
      case 'star': icon = Icons.star; break;
    }

    // NSS uses "foreground" instead of "color"
    final color = SduiNssUtils.parseColor(node.props['foreground'] as String?);

    return Icon(icon, size: size, color: color);
  }
}
