import 'package:flutter/material.dart';
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';

enum AppLifecyclePhase {
  foreground,
  background,
  inactive,
  detached;

  static AppLifecyclePhase fromAppLifecycleState(AppLifecycleState state) {
    switch (state) {
      case AppLifecycleState.resumed:
        return AppLifecyclePhase.foreground;
      case AppLifecycleState.paused:
      case AppLifecycleState.hidden:
        return AppLifecyclePhase.background;
      case AppLifecycleState.inactive:
        return AppLifecyclePhase.inactive;
      case AppLifecycleState.detached:
        return AppLifecyclePhase.detached;
    }
  }
}

mixin AppLifecycleObserver {
  void onForeground() {}
  void onBackground() {}
  void onInactive() {}
  void onDetached() {}
}

class AppLifecycleService with WidgetsBindingObserver {
  AppLifecyclePhase _currentPhase = AppLifecyclePhase.foreground;
  final List<AppLifecycleObserver> _observers = [];

  AppLifecyclePhase get currentPhase => _currentPhase;

  void init() {
    WidgetsBinding.instance.addObserver(this);
    RuntimeLogger.i('AppLifecycleService initialized');
  }

  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _observers.clear();
  }

  void registerObserver(AppLifecycleObserver observer) {
    _observers.add(observer);
  }

  void unregisterObserver(AppLifecycleObserver observer) {
    _observers.remove(observer);
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    _currentPhase = AppLifecyclePhase.fromAppLifecycleState(state);
    RuntimeLogger.i('Lifecycle: $state', feature: 'lifecycle');

    switch (state) {
      case AppLifecycleState.resumed:
        for (final o in _observers) {
          o.onForeground();
        }
      case AppLifecycleState.paused:
      case AppLifecycleState.hidden:
        for (final o in _observers) {
          o.onBackground();
        }
      case AppLifecycleState.inactive:
        for (final o in _observers) {
          o.onInactive();
        }
      case AppLifecycleState.detached:
        for (final o in _observers) {
          o.onDetached();
        }
    }
  }
}
