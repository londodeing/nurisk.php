import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/config_model.dart';

abstract class ConfigLocalDatasource {
  Future<ConfigModel?> getCachedConfig();
  Future<void> cacheConfig(ConfigModel config);
}

class ConfigLocalDatasourceImpl implements ConfigLocalDatasource {
  ConfigLocalDatasourceImpl();

  static const _storageKey = 'dashboard_config_cache';
  static const _defaultConfig = ConfigModel(
    version: '1.0',
    screenTitle: 'Dashboard',
    layoutType: 'scrollable_column',
    widgets: [],
    bottomNav: [],
    featureFlags: {},
    rawJson: {},
  );

  @override
  Future<ConfigModel?> getCachedConfig() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cached = prefs.getString(_storageKey);
      if (cached == null) return _defaultConfig;
      final json = jsonDecode(cached);
      return ConfigModel.fromJson(json);
    } catch (_) {
      return _defaultConfig;
    }
  }

  @override
  Future<void> cacheConfig(ConfigModel config) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_storageKey, jsonEncode(config.toJson()));
    } catch (_) {
    }
  }
}

final configLocalDatasourceProvider = Provider<ConfigLocalDatasource>((ref) {
  return ConfigLocalDatasourceImpl();
});
