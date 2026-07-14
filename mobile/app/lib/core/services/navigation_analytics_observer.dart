import 'package:flutter/material.dart';
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';

class NavigationAnalyticsObserver extends NavigatorObserver {
  @override
  void didPush(Route<dynamic> route, Route<dynamic>? previousRoute) {
    super.didPush(route, previousRoute);
    RuntimeLogger.navigation(
      previousRoute?.settings.name ?? '(initial)',
      route.settings.name ?? '(unnamed)',
      method: 'push',
    );
  }

  @override
  void didPop(Route<dynamic> route, Route<dynamic>? previousRoute) {
    super.didPop(route, previousRoute);
    RuntimeLogger.navigation(
      route.settings.name ?? '(popped)',
      previousRoute?.settings.name ?? '(none)',
      method: 'pop',
    );
  }

  @override
  void didReplace({Route<dynamic>? newRoute, Route<dynamic>? oldRoute}) {
    super.didReplace(newRoute: newRoute, oldRoute: oldRoute);
    RuntimeLogger.navigation(
      oldRoute?.settings.name ?? '(old)',
      newRoute?.settings.name ?? '(new)',
      method: 'replace',
    );
  }

  @override
  void didRemove(Route<dynamic> route, Route<dynamic>? previousRoute) {
    super.didRemove(route, previousRoute);
    RuntimeLogger.navigation(
      route.settings.name ?? '(removed)',
      previousRoute?.settings.name ?? '(none)',
      method: 'remove',
    );
  }
}
