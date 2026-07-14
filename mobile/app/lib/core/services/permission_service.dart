import 'package:permission_handler/permission_handler.dart' as ph;
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';

class PermissionService {
  Future<bool> requestCamera() => _request(ph.Permission.camera);

  Future<bool> requestLocation() => _request(ph.Permission.locationWhenInUse);

  Future<bool> requestGallery() => _request(
        ph.Permission.photos,
        fallback: ph.Permission.storage,
      );

  Future<bool> requestNotification() => _request(ph.Permission.notification);

  Future<bool> requestStorage() => _request(ph.Permission.storage);

  Future<bool> requestMicrophone() => _request(ph.Permission.microphone);

  Future<bool> requestBluetooth() => _request(ph.Permission.bluetooth);

  Future<bool> isGranted(ph.Permission permission) async {
    final status = await permission.status;
    return status.isGranted || status == ph.PermissionStatus.limited;
  }

  Future<bool> isPermanentlyDeniedFor(ph.Permission permission) async {
    final status = await permission.status;
    return status.isPermanentlyDenied;
  }

  Future<void> openSettings() async {
    RuntimeLogger.i('Opening app settings', screen: 'permission');
    await ph.openAppSettings();
  }

  Future<bool> _request(
    ph.Permission permission, {
    ph.Permission? fallback,
  }) async {
    final name = permission.toString();

    RuntimeLogger.permission(name, false);

    var status = await permission.status;

    if (status.isGranted || status == ph.PermissionStatus.limited) {
      RuntimeLogger.permission(name, true);
      return true;
    }

    if (status.isPermanentlyDenied) {
      RuntimeLogger.w('$name permanently denied — opening settings', screen: 'permission');
      await openSettings();
      return false;
    }

    if (status == ph.PermissionStatus.restricted) {
      RuntimeLogger.w('$name restricted (parental controls)', screen: 'permission');
      return false;
    }

    status = await permission.request();
    final granted = status.isGranted || status == ph.PermissionStatus.limited;

    RuntimeLogger.permission(name, granted, wasRequested: true);

    if (granted) return true;

    if (!granted && fallback != null) {
      return _request(fallback);
    }

    if (status.isPermanentlyDenied) {
      await openSettings();
    }

    return false;
  }
}
