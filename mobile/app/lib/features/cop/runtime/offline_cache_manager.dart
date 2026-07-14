import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class OfflineCacheManager {
  // In a real app, use SharedPreferences or flutter_secure_storage
  // For the prototype, we use in-memory simulation to avoid async init complexity in tests
  String? _mockStorage;

  Future<void> saveSceneSnapshot(Map<String, dynamic> scene) async {
    _mockStorage = jsonEncode(scene);
  }

  Future<Map<String, dynamic>?> loadSceneSnapshot() async {
    if (_mockStorage == null) return null;
    return jsonDecode(_mockStorage!) as Map<String, dynamic>;
  }
  
  void clear() {
    _mockStorage = null;
  }
}

final offlineCacheManagerProvider = Provider<OfflineCacheManager>((ref) {
  return OfflineCacheManager();
});
