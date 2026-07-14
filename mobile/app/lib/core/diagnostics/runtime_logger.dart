import 'package:flutter/foundation.dart';

abstract class LogBackend {
  void debug(String message, {Map<String, dynamic>? metadata});
  void info(String message, {Map<String, dynamic>? metadata});
  void warning(String message, {Map<String, dynamic>? metadata});
  void error(String message, Object? error, StackTrace? stack, {Map<String, dynamic>? metadata});
  void fatal(String message, Object? error, StackTrace? stack, {Map<String, dynamic>? metadata});
}

class ConsoleLogBackend implements LogBackend {
  @override
  void debug(String message, {Map<String, dynamic>? metadata}) {
    debugPrint('[DEBUG] $message ${_formatMetadata(metadata)}');
  }

  @override
  void info(String message, {Map<String, dynamic>? metadata}) {
    debugPrint('[INFO] $message ${_formatMetadata(metadata)}');
  }

  @override
  void warning(String message, {Map<String, dynamic>? metadata}) {
    debugPrint('[WARNING] $message ${_formatMetadata(metadata)}');
  }

  @override
  void error(String message, Object? error, StackTrace? stack, {Map<String, dynamic>? metadata}) {
    debugPrint('[ERROR] $message ${_formatMetadata(metadata)}');
    if (error != null) debugPrint('  Cause: $error');
    if (stack != null) debugPrint('  Stack: $stack');
  }

  @override
  void fatal(String message, Object? error, StackTrace? stack, {Map<String, dynamic>? metadata}) {
    debugPrint('[FATAL] $message ${_formatMetadata(metadata)}');
    if (error != null) debugPrint('  Cause: $error');
    if (stack != null) debugPrint('  Stack: $stack');
  }

  String _formatMetadata(Map<String, dynamic>? metadata) {
    if (metadata == null || metadata.isEmpty) return '';
    return '[${metadata.entries.map((e) => '${e.key}:${e.value}').join(', ')}]';
  }
}

class RuntimeLogger {
  static LogBackend _backend = ConsoleLogBackend();

  static void configure(LogBackend backend) {
    _backend = backend;
  }

  static void d(String message, {String? screen, String? feature}) {
    _backend.debug(message, metadata: _meta(screen, feature));
  }

  static void i(String message, {String? screen, String? feature}) {
    _backend.info(message, metadata: _meta(screen, feature));
  }

  static void w(String message, {String? screen, String? feature}) {
    _backend.warning(message, metadata: _meta(screen, feature));
  }

  static void e(String message, Object? error, StackTrace? stack, {String? screen, String? feature, String? plugin}) {
    _backend.error(message, error, stack, metadata: {
      if (screen != null) 'screen': screen,
      if (feature != null) 'feature': feature,
      if (plugin != null) 'plugin': plugin,
    });
  }

  static void f(String message, Object? error, StackTrace? stack, {String? screen, String? feature}) {
    _backend.fatal(message, error, stack, metadata: _meta(screen, feature));
  }

  static void nativePlugin(String plugin, String action, {bool success = true, String? error}) {
    final msg = success ? '$plugin.$action OK' : '$plugin.$action FAILED';
    if (success) {
      _backend.info(msg, metadata: {'plugin': plugin, 'action': action});
    } else {
      _backend.error(msg, null, null, metadata: {'plugin': plugin, 'action': action, 'error': error});
    }
  }

  static void permission(String permission, bool granted, {bool wasRequested = false}) {
    _backend.info('Permission $permission ${granted ? "GRANTED" : "DENIED"}${wasRequested ? " (after request)" : ""}',
        metadata: {'permission': permission, 'granted': granted, 'requested': wasRequested});
  }

  static void navigation(String from, String to, {String? method}) {
    _backend.info('$from → $to', metadata: {'from': from, 'to': to, 'method': method});
  }

  static Map<String, dynamic> _meta(String? screen, String? feature) {
    return {
      if (screen != null) 'screen': screen,
      if (feature != null) 'feature': feature,
    };
  }
}
