import 'package:flutter/material.dart';

class DynamicIcon {
  static IconData get(String name) {
    switch (name) {
      case 'person':
      case 'bi-person':
        return Icons.person;
      case 'shield-check':
      case 'bi-shield-check':
        return Icons.verified_user;
      case 'lock':
      case 'bi-lock':
        return Icons.lock;
      case 'fingerprint':
      case 'bi-fingerprint':
        return Icons.fingerprint;
      case 'cloud-download':
      case 'bi-cloud-download':
        return Icons.cloud_download;
      case 'translate':
      case 'bi-translate':
        return Icons.language;
      case 'info-circle':
      case 'bi-info-circle':
        return Icons.info;
      case 'box-arrow-right':
      case 'bi-box-arrow-right':
        return Icons.logout;
      case 'plus-circle':
        return Icons.add_circle;
      case 'map':
        return Icons.map;
      case 'clipboard':
        return Icons.assignment;
      default:
        return Icons.circle;
    }
  }
}
