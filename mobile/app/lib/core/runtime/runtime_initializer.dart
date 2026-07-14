import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';
import 'package:nurisk_mobile/core/platform/geo_service.dart';
import 'package:nurisk_mobile/core/platform/media_service.dart';
import 'package:nurisk_mobile/core/runtime/app_lifecycle_service.dart';
import 'package:nurisk_mobile/core/runtime/error_boundary.dart';
import 'package:nurisk_mobile/core/runtime/runtime_state.dart';
import 'package:nurisk_mobile/core/services/navigation_service.dart';
import 'package:nurisk_mobile/core/services/permission_service.dart';
import 'package:nurisk_mobile/core/storage/public/database_provider.dart';

class RuntimeServices {
  final AppLifecycleService lifecycle;
  final PermissionService permission;
  final NavigationService navigation;
  final MediaService media;
  final GeoService geo;

  RuntimeServices({
    required this.lifecycle,
    required this.permission,
    required this.navigation,
    required this.media,
    required this.geo,
  });
}

class RuntimeInitializer {
  static Future<RuntimeState> initialize(GoRouter router) async {
    RuntimeLogger.i('Runtime initialization started');

    AppErrorBoundary.init();
    RuntimeLogger.i('Phase 1: Core diagnostics initialized');

    final lifecycle = AppLifecycleService();
    lifecycle.init();

    await createPublicDatabase();

    final permission = PermissionService();
    final navigation = NavigationService(router);
    final media = MediaService(permission);
    final geo = GeoService(permission);

    final services = RuntimeServices(
      lifecycle: lifecycle,
      permission: permission,
      navigation: navigation,
      media: media,
      geo: geo,
    );

    RuntimeServicesScope.instance = services;

    RuntimeLogger.i('Runtime initialization complete');

    return const RuntimeState(status: RuntimeStatus.ok);
  }
}

class RuntimeServicesScope {
  static RuntimeServices? _instance;

  static RuntimeServices get instance {
    assert(_instance != null, 'RuntimeServices not initialized');
    return _instance!;
  }

  static set instance(RuntimeServices services) => _instance = services;
}

final runtimeServicesProvider = Provider<RuntimeServices>((ref) {
  return RuntimeServicesScope.instance;
});
