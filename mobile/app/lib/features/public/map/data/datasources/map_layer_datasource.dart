import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';

class MapLayerDatasource {
  final Dio _dio;

  MapLayerDatasource(this._dio);

  Future<Map<String, dynamic>> fetchLayerGeoJson(String layerId) async {
    try {
      final isOperational = ['incident', 'ambulance', 'posko', 'shelter', 'volunteer'].contains(layerId);
      final path = isOperational 
          ? 'public/map/operational/$layerId' 
          : 'public/map/layers/$layerId';
          
      final response = await _dio.get(path);
      
      if (response.statusCode == 200) {
        return response.data as Map<String, dynamic>;
      } else {
        throw Exception('Failed to load layer: $layerId (Status: ${response.statusCode})');
      }
    } catch (e) {
      throw Exception('Error fetching layer $layerId: $e');
    }
  }

  Future<Map<String, dynamic>> fetchMapConfig() async {
    final response = await _dio.get('public/map/config');
    if (response.statusCode == 200) {
      return response.data as Map<String, dynamic>;
    }
    throw Exception('Failed to load map config (Status: ${response.statusCode})');
  }
}

final mapLayerDatasourceProvider = Provider<MapLayerDatasource>((ref) {
  final dio = ref.watch(publicApiClientProvider);
  return MapLayerDatasource(dio);
});
