import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/dashboard_kpi_entity.dart';
import '../../domain/repositories/dashboard_kpi_repository.dart';
import '../datasources/dashboard_kpi_remote_datasource.dart';
import '../datasources/dashboard_kpi_local_datasource.dart';

class DashboardKpiRepositoryImpl implements DashboardKpiRepository {
  final DashboardKpiRemoteDatasource remoteDatasource;
  final DashboardKpiLocalDatasource localDatasource;

  DashboardKpiRepositoryImpl(this.remoteDatasource, this.localDatasource);

  @override
  Future<DashboardKpiEntity> getPublicKpi() async {
    try {
      // 1. Fetch from Remote BFF
      final remoteKpi = await remoteDatasource.fetchPublicKpi();
      
      // 2. Cache it Locally
      await localDatasource.cacheKpi(remoteKpi);
      
      return remoteKpi;
    } catch (e) {
      // 3. Fallback to Local Cache on network failure
      final localKpi = await localDatasource.getCachedKpi();
      if (localKpi != null) {
        return localKpi;
      }
      throw Exception('Failed to fetch KPI data and no cache available.');
    }
  }
}

final dashboardKpiRepositoryProvider = Provider<DashboardKpiRepository>((ref) {
  return DashboardKpiRepositoryImpl(
    ref.watch(dashboardKpiRemoteDatasourceProvider),
    ref.watch(dashboardKpiLocalDatasourceProvider),
  );
});
