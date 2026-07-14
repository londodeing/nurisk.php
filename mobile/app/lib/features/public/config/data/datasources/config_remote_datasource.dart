import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import '../models/config_model.dart';

abstract class ConfigRemoteDatasource {
  Future<ConfigModel> fetchDashboardConfig();
}

class ConfigRemoteDatasourceImpl implements ConfigRemoteDatasource {
  final Dio dio;

  ConfigRemoteDatasourceImpl(this.dio);

  @override
  Future<ConfigModel> fetchDashboardConfig() async {
    try {
      final response = await dio.get('public/dashboard/config');
      
      if (response.statusCode == 200) {
        return ConfigModel.fromJson(response.data);
      } else {
        throw Exception('Failed to load dashboard config');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }
}

final configRemoteDatasourceProvider = Provider<ConfigRemoteDatasource>((ref) {
  return ConfigRemoteDatasourceImpl(ref.watch(authApiClientProvider));
});
