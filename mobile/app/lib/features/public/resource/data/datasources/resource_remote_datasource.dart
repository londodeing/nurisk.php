import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';
import '../models/resource_model.dart';

final resourceRemoteDatasourceProvider = Provider<ResourceRemoteDatasource>((ref) {
  return ResourceRemoteDatasource(ref.read(publicApiClientProvider));
});

class ResourceRemoteDatasource {
  final Dio _dio;

  ResourceRemoteDatasource(this._dio);

  Future<List<ResourceModel>> getResources({int page = 1, int perPage = 20, String? category}) async {
    try {
      final Map<String, dynamic> queryParams = {
        'page': page,
        'limit': perPage,
      };
      if (category != null && category.isNotEmpty) {
        queryParams['category'] = category;
      }
      
      final res = await _dio.get('public/resources', queryParameters: queryParams);
      final data = res.data['data'] as List<dynamic>? ?? [];
      return data.map((e) => ResourceModel.fromJson(e as Map<String, dynamic>)).toList();
    } catch (e) {
      throw Exception('Gagal memuat sumber daya: $e');
    }
  }
}
