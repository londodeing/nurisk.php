import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';
import '../models/dashboard_kpi_model.dart';

abstract class DashboardKpiRemoteDatasource {
  Future<DashboardKpiModel> fetchPublicKpi();
}

class DashboardKpiRemoteDatasourceImpl implements DashboardKpiRemoteDatasource {
  final Dio dio;

  DashboardKpiRemoteDatasourceImpl(this.dio);

  @override
  Future<DashboardKpiModel> fetchPublicKpi() async {
    try {
      final response = await dio.get('public/dashboard');
      
      if (response.statusCode == 200) {
        final data = response.data;
        final kpiData = data['kpi'] ?? {};
        kpiData['updated_at'] = data['updated_at'];
        return DashboardKpiModel.fromJson(kpiData);
      } else {
        throw Exception('Failed to load dashboard kpi data');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }
}

final dashboardKpiRemoteDatasourceProvider = Provider<DashboardKpiRemoteDatasource>((ref) {
  return DashboardKpiRemoteDatasourceImpl(ref.watch(publicApiClientProvider));
});
