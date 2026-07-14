import '../../domain/entities/dashboard_kpi_entity.dart';

class DashboardKpiModel extends DashboardKpiEntity {
  const DashboardKpiModel({
    required super.activeIncidents,
    required super.verifiedIncidents,
    required super.impactedRegions,
    required super.deployedVolunteers,
    required super.lastUpdated,
  });

  factory DashboardKpiModel.fromJson(Map<String, dynamic> json) {
    return DashboardKpiModel(
      activeIncidents: json['total_insiden'] ?? 0,
      verifiedIncidents: json['total_personel'] ?? 0, // Mapped to total_personel to fit the UI format
      impactedRegions: json['korban_terdampak'] ?? 0, // Mapped to korban_terdampak
      deployedVolunteers: json['kebutuhan_gap'] ?? 0, // Mapped to kebutuhan_gap
      lastUpdated: DateTime.tryParse(json['updated_at'] ?? '') ?? DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'active_incidents': activeIncidents,
      'verified_incidents': verifiedIncidents,
      'impacted_regions': impactedRegions,
      'deployed_volunteers': deployedVolunteers,
      'last_updated': lastUpdated.toIso8601String(),
    };
  }
}
