import 'package:flutter/material.dart';

class SduiErrorBoundary extends StatefulWidget {
  final Widget child;

  const SduiErrorBoundary({super.key, required this.child});

  @override
  State<SduiErrorBoundary> createState() => _SduiErrorBoundaryState();
}

class _SduiErrorBoundaryState extends State<SduiErrorBoundary> {
  Error? _error;
  Exception? _exception;
  StackTrace? _stackTrace;

  @override
  void initState() {
    super.initState();
    // Catch errors during build phase using ErrorWidget builder
    // This is a global override but we can also just use a local try-catch for the builder.
    // However, catching errors inside the build method of descendants is tricky.
  }

  @override
  Widget build(BuildContext context) {
    if (_error != null || _exception != null) {
      return _buildErrorScreen(_error ?? _exception!);
    }

    // We can't use traditional try-catch around a Widget tree in Flutter.
    // Instead, we will provide a utility method to safely parse and build.
    return widget.child;
  }

  Widget _buildErrorScreen(Object error) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Icon(Icons.warning_amber_rounded, size: 64, color: Colors.red),
              const SizedBox(height: 24),
              const Text(
                'SDUI Contract Error',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              Text(
                error.toString(),
                style: const TextStyle(color: Colors.red),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              const Text(
                'Terjadi ketidaksesuaian antara struktur JSON dari backend dengan spesifikasi (NSS).',
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class SduiSafeBuilder extends StatelessWidget {
  final Object? Function() parser;
  final Widget Function(dynamic data) builder;
  final Widget? fallback;

  const SduiSafeBuilder({
    super.key,
    required this.parser,
    required this.builder,
    this.fallback,
  });

  @override
  Widget build(BuildContext context) {
    try {
      final data = parser();
      return builder(data);
    } catch (e, stack) {
      debugPrint('SDUI Safe Builder Caught Exception: $e\\n$stack');
      return Scaffold(
        body: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Icon(Icons.error_outline, size: 64, color: Colors.red),
                const SizedBox(height: 24),
                const Text(
                  'SDUI Contract Error',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
                Text(
                  e.toString(),
                  style: const TextStyle(color: Colors.red),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                const Text(
                  'Payload yang dikirimkan oleh backend tidak valid menurut NSS.',
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ),
      );
    }
  }
}
