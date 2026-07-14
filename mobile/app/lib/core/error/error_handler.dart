import 'dart:ui' show PlatformDispatcher;
import 'package:flutter/material.dart';
import '../logger/app_logger.dart';

class AppErrorHandler {
  static void init() {
    FlutterError.onError = (FlutterErrorDetails details) {
      appLogger.e(
        'Flutter error: ${details.exception}',
        details.exception,
        details.stack,
      );
    };

    PlatformDispatcher.instance.onError = (Object error, StackTrace stack) {
      appLogger.e('Platform error: $error', error, stack);
      return true;
    };
  }
}
