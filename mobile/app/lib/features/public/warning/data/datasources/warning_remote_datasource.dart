import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';
import '../models/warning_model.dart';

abstract class WarningRemoteDatasource {
  Future<List<WarningModel>> fetchActiveWarnings();
}

class WarningRemoteDatasourceImpl implements WarningRemoteDatasource {
  final Dio dio;

  WarningRemoteDatasourceImpl(this.dio);

  @override
  Future<List<WarningModel>> fetchActiveWarnings() async {
    try {
      final response = await dio.get('public/warnings'); // The aggregated API endpoint
      
      if (response.statusCode == 200) {
        final List data = response.data['data'] ?? [];
        return data.map((json) => WarningModel.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load warnings');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }
}

final warningRemoteDatasourceProvider = Provider<WarningRemoteDatasource>((ref) {
  return WarningRemoteDatasourceImpl(ref.watch(publicApiClientProvider));
});
