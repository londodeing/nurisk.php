import '../entities/dashboard_kpi_entity.dart';

abstract class DashboardKpiRepository {
  Future<DashboardKpiEntity> getPublicKpi();
}
