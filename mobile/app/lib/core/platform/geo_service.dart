import 'dart:async';
import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart' as ph;
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';
import 'package:nurisk_mobile/core/services/permission_service.dart';

class GeoPoint {
  final double latitude;
  final double longitude;
  final double accuracy;
  final bool isMocked;

  const GeoPoint({
    required this.latitude,
    required this.longitude,
    this.accuracy = 0,
    this.isMocked = false,
  });

  String toDisplayString() => '${latitude.toStringAsFixed(6)}, ${longitude.toStringAsFixed(6)}';
}

enum GeoFailure {
  serviceDisabled,
  permissionDenied,
  permissionPermanentlyDenied,
  timeout,
  lowAccuracy,
  mockDetected,
  unknown,
}

class GeoResult {
  final GeoPoint? point;
  final GeoFailure? failure;
  final String? message;

  const GeoResult({this.point, this.failure, this.message});

  bool get isSuccess => point != null;
}

class GeoService {
  final PermissionService _permissionService;

  GeoService(this._permissionService);

  static const double _minAccuracyMeters = 100;

  Future<GeoResult> getCurrentPosition({Duration timeout = const Duration(seconds: 15)}) async {
    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        RuntimeLogger.w('GPS service disabled', screen: 'geo', feature: 'location');
        return GeoResult(
          failure: GeoFailure.serviceDisabled,
          message: 'Layanan GPS tidak aktif. Aktifkan GPS di pengaturan.',
        );
      }

      final granted = await _permissionService.requestLocation();
      if (!granted) {
        final permStatus = await _permissionService.isPermanentlyDeniedFor(
          ph.Permission.locationWhenInUse,
        );
        RuntimeLogger.w('Location permission denied', screen: 'geo', feature: 'location');
        return GeoResult(
          failure: permStatus ? GeoFailure.permissionPermanentlyDenied : GeoFailure.permissionDenied,
          message: permStatus
              ? 'Izin lokasi ditolak permanen. Buka pengaturan untuk mengaktifkan.'
              : 'Izin lokasi ditolak.',
        );
      }

      RuntimeLogger.i('Getting current position', screen: 'geo', feature: 'location');
      final position = await Geolocator.getCurrentPosition(
        locationSettings: LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: timeout,
        ),
      );

      if (position.isMocked) {
        RuntimeLogger.w('Mock location detected', screen: 'geo', feature: 'location');
        return GeoResult(
          point: GeoPoint(
            latitude: position.latitude,
            longitude: position.longitude,
            accuracy: position.accuracy,
            isMocked: true,
          ),
          failure: GeoFailure.mockDetected,
          message: 'Lokasi mock terdeteksi.',
        );
      }

      if (position.accuracy > _minAccuracyMeters) {
        RuntimeLogger.w('Low GPS accuracy: ${position.accuracy}m', screen: 'geo', feature: 'location');
      }

      RuntimeLogger.nativePlugin('Geolocator', 'getCurrentPosition', success: true);
      return GeoResult(
        point: GeoPoint(
          latitude: position.latitude,
          longitude: position.longitude,
          accuracy: position.accuracy,
        ),
      );
    } on TimeoutException {
      RuntimeLogger.w('GPS timeout', screen: 'geo', feature: 'location');
      return GeoResult(
        failure: GeoFailure.timeout,
        message: 'Waktu permintaan lokasi habis. Coba lagi di area terbuka.',
      );
    } catch (e, stack) {
      RuntimeLogger.nativePlugin('Geolocator', 'getCurrentPosition', success: false, error: e.toString());
      RuntimeLogger.e('GPS failed', e, stack, screen: 'geo', feature: 'location', plugin: 'geolocator');
      return GeoResult(
        failure: GeoFailure.unknown,
        message: 'Gagal mendapatkan lokasi: ${e.toString().split('\n').first}',
      );
    }
  }

  double distanceInMeters(GeoPoint from, GeoPoint to) {
    return Geolocator.distanceBetween(from.latitude, from.longitude, to.latitude, to.longitude);
  }
}
