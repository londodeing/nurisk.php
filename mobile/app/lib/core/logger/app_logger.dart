import 'dart:developer' as developer;

enum LogLevel { debug, info, warning, error }

class AppLogger {
  final String tag;
  final bool isDebug;

  const AppLogger({this.tag = 'NURISK', this.isDebug = true});

  void d(String message) {
    if (isDebug) {
      print('[DEBUG][$tag] $message');
    }
  }

  void i(String message) {
    print('[INFO][$tag] $message');
  }

  void w(String message) {
    print('[WARN][$tag] $message');
  }

  void e(String message, [Object? error, StackTrace? stack]) {
    print('[ERROR][$tag] $message');
    if (error != null) print(error);
    if (stack != null) print(stack);
  }

  factory AppLogger.of(String tag) => AppLogger(tag: tag);
}

final appLogger = AppLogger();
