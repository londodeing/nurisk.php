class DashboardKpiEntity {
  final int activeIncidents;
  final int verifiedIncidents;
  final int impactedRegions;
  final int deployedVolunteers;
  final DateTime lastUpdated;

  const DashboardKpiEntity({
    required this.activeIncidents,
    required this.verifiedIncidents,
    required this.impactedRegions,
    required this.deployedVolunteers,
    required this.lastUpdated,
  });
}
