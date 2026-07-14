import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/dashboard_kpi_entity.dart';
import '../../data/repositories/dashboard_kpi_repository_impl.dart';

final dashboardKpiProvider = AsyncNotifierProvider<DashboardKpiNotifier, DashboardKpiEntity>(DashboardKpiNotifier.new);

class DashboardKpiNotifier extends AsyncNotifier<DashboardKpiEntity> {
  @override
  Future<DashboardKpiEntity> build() async {
    return _fetchKpi();
  }

  Future<DashboardKpiEntity> _fetchKpi() async {
    final repository = ref.read(dashboardKpiRepositoryProvider);
    return repository.getPublicKpi();
  }

  Future<void> refresh() async {
    final result = await AsyncValue.guard(() => _fetchKpi());
    if (result is AsyncData) {
      state = result;
    }
  }
}
