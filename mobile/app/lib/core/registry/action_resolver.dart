import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';

class ActionResolver {
  static Future<void> execute(Map<String, dynamic>? action, WidgetRef ref) async {
    if (action == null) return;
    
    final type = action['type'];
    
    switch (type) {
      case 'navigate':
        final target = action['target'];
        if (target != null) {
          RuntimeServicesScope.instance.navigation.push(target);
        }
        break;
      case 'event':
        final event = action['event'];
        if (event != null) {
          _handleDomainEvent(event);
        }
        break;
      case 'fetch_page':
        // Future implementation: Fetch JSON and render Dynamic Page
        break;
      case 'api_call':
        // Future implementation: Dio POST call
        final endpoint = action['endpoint'];
        if (endpoint != null) {
           debugPrint('Executing API: $endpoint');
        }
        break;
      default:
        debugPrint('Unknown action type: $type');
    }
  }

  static void _handleDomainEvent(String event) {
    switch (event) {
      case 'EDIT_PROFILE':
        RuntimeServicesScope.instance.navigation.push('/profile/edit');
        break;
      case 'OPEN_MANDATE':
        RuntimeServicesScope.instance.navigation.push('/auth/mandate');
        break;
      case 'OPEN_LOGIN':
        RuntimeServicesScope.instance.navigation.push('/auth/login');
        break;
      case 'OPEN_REGISTER':
        RuntimeServicesScope.instance.navigation.push('/auth/register');
        break;
      case 'OPEN_REPORT_VALIDATION':
        RuntimeServicesScope.instance.navigation.push('/g/report-validation');
        break;
      case 'OPEN_PIN_SETTINGS':
        RuntimeServicesScope.instance.navigation.push('/settings/pin');
        break;
      case 'OPEN_BIOMETRIC':
        RuntimeServicesScope.instance.navigation.push('/settings/biometric');
        break;
      case 'OPEN_OFFLINE_MODE':
        RuntimeServicesScope.instance.navigation.push('/settings/offline');
        break;
      case 'OPEN_LANGUAGE':
        RuntimeServicesScope.instance.navigation.push('/settings/language');
        break;
      case 'OPEN_ABOUT':
        RuntimeServicesScope.instance.navigation.push('/settings/about');
        break;
      default:
        debugPrint('Unhandled Domain Event: $event');
    }
  }
}
