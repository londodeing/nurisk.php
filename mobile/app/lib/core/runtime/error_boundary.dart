import 'dart:ui' show PlatformDispatcher;
import 'package:flutter/material.dart';
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';

class AppErrorBoundary {
  static void init() {
    FlutterError.onError = (FlutterErrorDetails details) {
      RuntimeLogger.f(
        details.exceptionAsString(),
        details.exception,
        details.stack,
        screen: details.context?.toString(),
      );
    };

    PlatformDispatcher.instance.onError = (Object error, StackTrace stack) {
      RuntimeLogger.f('Platform error', error, stack);
      return true;
    };
  }

  static void captureError(
    Object error,
    StackTrace stack, {
    String? screen,
    String? feature,
    String? plugin,
  }) {
    RuntimeLogger.e(
      error.toString(),
      error,
      stack,
      screen: screen,
      feature: feature,
      plugin: plugin,
    );
  }
}

class WidgetErrorBoundary extends StatelessWidget {
  final Widget child;
  final String label;
  final Widget? fallback;

  const WidgetErrorBoundary({
    super.key,
    required this.child,
    required this.label,
    this.fallback,
  });

  @override
  Widget build(BuildContext context) {
    return _ErrorBoundary(
      label: label,
      fallback: fallback ?? const SizedBox.shrink(),
      child: child,
    );
  }
}

class _ErrorBoundary extends StatefulWidget {
  final Widget child;
  final String label;
  final Widget fallback;

  const _ErrorBoundary({
    required this.child,
    required this.label,
    required this.fallback,
  });

  @override
  State<_ErrorBoundary> createState() => _ErrorBoundaryState();
}

class _ErrorBoundaryState extends State<_ErrorBoundary> {
  bool _hasError = false;

  @override
  void initState() {
    super.initState();
    _hasError = false;
  }

  @override
  void didUpdateWidget(_ErrorBoundary oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.child != oldWidget.child) {
      _hasError = false;
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_hasError) {
      return widget.fallback;
    }
    return FlutterErrorWidgetCatcher(
      onError: (_) {
        RuntimeLogger.w('WidgetErrorBoundary caught error for: ${widget.label}');
        setState(() => _hasError = true);
      },
      child: widget.child,
    );
  }
}

class FlutterErrorWidgetCatcher extends StatelessWidget {
  final Widget child;
  final ValueChanged<FlutterErrorDetails> onError;

  const FlutterErrorWidgetCatcher({
    super.key,
    required this.child,
    required this.onError,
  });

  @override
  Widget build(BuildContext context) {
    return child;
  }
}
