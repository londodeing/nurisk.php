import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';
import '../models/incident_model.dart';

abstract class IncidentRemoteDatasource {
  Future<List<IncidentModel>> fetchLatestIncidents({required int page, required int limit});
}

class IncidentRemoteDatasourceImpl implements IncidentRemoteDatasource {
  final Dio dio;

  IncidentRemoteDatasourceImpl(this.dio);

  @override
  Future<List<IncidentModel>> fetchLatestIncidents({required int page, required int limit}) async {
    try {
      // In this phase, we fetch all incidents from the dashboard endpoint as pagination is not implemented yet.
      final response = await dio.get('public/dashboard');
      
      if (response.statusCode == 200) {
        final List data = response.data['insiden'] ?? [];
        return data.map((json) => IncidentModel.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load incident feed data');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }
}

final incidentRemoteDatasourceProvider = Provider<IncidentRemoteDatasource>((ref) {
  return IncidentRemoteDatasourceImpl(ref.watch(publicApiClientProvider));
});
