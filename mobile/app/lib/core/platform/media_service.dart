import 'dart:io';
import 'package:image_picker/image_picker.dart';
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';
import 'package:nurisk_mobile/core/services/permission_service.dart';

class MediaResult {
  final File? file;
  final String? failure;
  final String? message;

  const MediaResult({this.file, this.failure, this.message});

  bool get isSuccess => file != null;
  bool get isCancelled => failure == 'cancelled';
  bool get isPermissionDenied => failure == 'permission_denied';
  bool get isTooLarge => failure == 'file_too_large';
  bool get isFailed => failure == 'capture_failed';
}

class MediaService {
  final PermissionService _permissionService;
  final ImagePicker _picker = ImagePicker();

  MediaService(this._permissionService);

  static const int _maxFileSizeBytes = 20 * 1024 * 1024; // 20 MB

  Future<MediaResult> takePhoto({double maxWidth = 1920, double maxHeight = 1920, int quality = 85}) async {
    final granted = await _permissionService.requestCamera();
    if (!granted) {
      RuntimeLogger.w('Camera permission denied', screen: 'media', feature: 'camera');
      return MediaResult(
        failure: 'permission_denied',
        message: 'Izin kamera ditolak. Buka pengaturan untuk mengaktifkan.',
      );
    }

    try {
      RuntimeLogger.i('Opening camera', screen: 'media', feature: 'camera');
      final picked = await _picker.pickImage(
        source: ImageSource.camera,
        maxWidth: maxWidth,
        maxHeight: maxHeight,
        imageQuality: quality,
        preferredCameraDevice: CameraDevice.rear,
      );

      if (picked == null) {
        RuntimeLogger.i('Camera cancelled', screen: 'media', feature: 'camera');
        return const MediaResult(failure: 'cancelled', message: 'Pengambilan foto dibatalkan.');
      }

      final file = File(picked.path);
      final fileSize = await file.length();

      if (fileSize > _maxFileSizeBytes) {
        RuntimeLogger.w('Photo too large: ${(fileSize / 1024 / 1024).toStringAsFixed(1)}MB', screen: 'media', feature: 'camera');
        return MediaResult(
          failure: 'file_too_large',
          message: 'Ukuran foto terlalu besar (${(fileSize / 1024 / 1024).toStringAsFixed(1)}MB). Maksimal 20MB.',
        );
      }

      RuntimeLogger.nativePlugin('ImagePicker', 'takePhoto', success: true);
      return MediaResult(file: file);
    } catch (e, stack) {
      RuntimeLogger.nativePlugin('ImagePicker', 'takePhoto', success: false, error: e.toString());
      RuntimeLogger.e('Camera failed', e, stack, screen: 'media', feature: 'camera', plugin: 'image_picker');
      return MediaResult(
        failure: 'capture_failed',
        message: 'Gagal mengambil foto: ${e.toString().split('\n').first}',
      );
    }
  }

  Future<MediaResult> pickFromGallery({double maxWidth = 1920, double maxHeight = 1920, int quality = 85}) async {
    final granted = await _permissionService.requestGallery();
    if (!granted) {
      RuntimeLogger.w('Gallery permission denied', screen: 'media', feature: 'gallery');
      return MediaResult(
        failure: 'permission_denied',
        message: 'Izin galeri ditolak. Buka pengaturan untuk mengaktifkan.',
      );
    }

    try {
      RuntimeLogger.i('Opening gallery', screen: 'media', feature: 'gallery');
      final picked = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: maxWidth,
        maxHeight: maxHeight,
        imageQuality: quality,
      );

      if (picked == null) {
        RuntimeLogger.i('Gallery cancelled', screen: 'media', feature: 'gallery');
        return const MediaResult(failure: 'cancelled', message: 'Pemilihan foto dibatalkan.');
      }

      final file = File(picked.path);
      final fileSize = await file.length();

      if (fileSize > _maxFileSizeBytes) {
        RuntimeLogger.w('Gallery image too large: ${(fileSize / 1024 / 1024).toStringAsFixed(1)}MB', screen: 'media', feature: 'gallery');
        return MediaResult(
          failure: 'file_too_large',
          message: 'Ukuran foto terlalu besar (${(fileSize / 1024 / 1024).toStringAsFixed(1)}MB). Maksimal 20MB.',
        );
      }

      RuntimeLogger.nativePlugin('ImagePicker', 'pickFromGallery', success: true);
      return MediaResult(file: file);
    } catch (e, stack) {
      RuntimeLogger.nativePlugin('ImagePicker', 'pickFromGallery', success: false, error: e.toString());
      RuntimeLogger.e('Gallery failed', e, stack, screen: 'media', feature: 'gallery', plugin: 'image_picker');
      return MediaResult(
        failure: 'capture_failed',
        message: 'Gagal memilih foto: ${e.toString().split('\n').first}',
      );
    }
  }
}
