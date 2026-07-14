import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class SduiActionHandler {
  static void execute(BuildContext context, Map<String, dynamic>? action) {
    if (action == null) return;
    
    final type = action['type'];
    
    switch (type) {
      case 'navigate':
        final target = action['target'] as String?;
        final replace = action['replace'] as bool? ?? false;
        if (target != null) {
          if (replace) {
            context.go(target);
          } else {
            context.push(target);
          }
        }
        break;
      
      case 'snackbar':
        final message = action['message'] as String? ?? '';
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
        break;

      case 'dialog':
      case 'bottom_sheet':
      case 'submit':
      case 'toggle':
      case 'reload':
      case 'refresh':
      case 'external_url':
      case 'phone':
      case 'email':
      case 'download':
        // TODO: Full implementation of these NSS actions
        debugPrint('SDUI Action "$type" is recognized by NSS but not fully implemented yet.');
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Aksi $type belum diimplementasikan di versi ini'))
        );
        break;
      
      default:
        debugPrint('SDUI Warning: Unknown action type "$type". Ignored.');
        break;
    }
  }
}
