import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';
import 'package:nurisk_mobile/core/router/app_router.dart';

class NavigationService {
  final GoRouter _router;

  NavigationService(this._router);

  // Named route methods — prefer these over raw path strings
  void goHome() => _go(RoutePaths.home);
  void goMap() => _go(RoutePaths.map);
  void goReport() => _go(RoutePaths.report);
  void goResource() => _go(RoutePaths.resource);
  void goProfile() => _go(RoutePaths.profile);
  void goAccount() => goProfile();
  void goLogin() => _push(RoutePaths.login);
  void goRegister() => _push(RoutePaths.register);
  void goMandatePicker(Map<String, dynamic> extras) => _go(RoutePaths.mandate, extra: extras);
  void goTracking(String trackingCode) => _push('${RoutePaths.reportTrackingBase}/$trackingCode');

  void logout() {
    RuntimeLogger.i('Logout — navigating home', feature: 'auth');
    _go(RoutePaths.home);
  }

  // Generic routing (use only when named methods don't fit)
  void push(String path, {Object? extra}) => _push(path, extra: extra);
  void go(String path, {Object? extra}) => _go(path, extra: extra);

  void pop() {
    if (_router.canPop()) {
      RuntimeLogger.navigation('pop', 'back', method: 'pop');
      _router.pop();
    }
  }

  bool canPop() => _router.canPop();

  // Named alias methods (preferred over To-prefixed versions)
  void goToMap() => goMap();
  void goToReport() => goReport();
  void goToResource() => goResource();
  void goToProfile() => goProfile();
  void goToLogin() => goLogin();
  void goToRegister() => goRegister();
  void goToTracking(String code) => goTracking(code);

  void _go(String path, {Object? extra}) {
    RuntimeLogger.navigation(path, path, method: 'go');
    _router.go(path, extra: extra);
  }

  void _push(String path, {Object? extra}) {
    RuntimeLogger.navigation(path, path, method: 'push');
    _router.push(path, extra: extra);
  }
}
