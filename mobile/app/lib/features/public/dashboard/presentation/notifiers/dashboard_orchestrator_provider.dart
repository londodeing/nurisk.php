import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/features/public/warning/presentation/notifiers/warning_provider.dart';
import 'package:nurisk_mobile/features/public/weather/presentation/notifiers/weather_provider.dart';
import 'dashboard_kpi_provider.dart';
import 'package:nurisk_mobile/features/public/incident/presentation/notifiers/incident_provider.dart';

final dashboardOrchestratorProvider = Provider<DashboardOrchestrator>((ref) {
  return DashboardOrchestrator(ref);
});

class DashboardOrchestrator {
  final Ref _ref;

  DashboardOrchestrator(this._ref);

  Future<void> refreshAll() async {
    // Phase 1 Refresh (Warning, Weather, KPI)
    // We await them so the PullToRefresh indicator knows when to hide.
    // Incident is also refreshed here since it's above the fold mostly.
    
    await Future.wait([
      _ref.read(warningProvider.notifier).refresh(),
      _ref.read(weatherProvider.notifier).refresh(),
      _ref.read(dashboardKpiProvider.notifier).refresh(),
      _ref.read(incidentFeedProvider.notifier).refresh(),
    ]);

    // Phase 3 Refresh (News, Donation, OrgSummary) will be added here
    // but without awaiting them strictly if we don't want to block the UI.
  }
}
