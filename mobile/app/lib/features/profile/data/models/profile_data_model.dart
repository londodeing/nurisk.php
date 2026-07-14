class ProfileData {
  final Map<String, dynamic> identity;
  final Map<String, dynamic> activeMandate;
  final List<dynamic> statistics;
  final List<dynamic> quickActions;
  final List<dynamic> tasks;
  final Map<String, dynamic> organization;
  final List<dynamic> resources;
  final List<dynamic> activities;
  final Map<String, dynamic> settingsConfig;

  ProfileData({
    required this.identity,
    required this.activeMandate,
    required this.statistics,
    required this.quickActions,
    required this.tasks,
    required this.organization,
    required this.resources,
    required this.activities,
    required this.settingsConfig,
  });

  factory ProfileData.fromJson(Map<String, dynamic> json) {
    return ProfileData(
      identity: json['identity'] ?? {},
      activeMandate: json['active_mandate'] ?? {},
      statistics: json['statistics'] ?? [],
      quickActions: json['quick_actions'] ?? [],
      tasks: json['tasks'] ?? [],
      organization: json['organization'] ?? {},
      resources: json['resources'] ?? [],
      activities: json['activities'] ?? [],
      settingsConfig: json['settings_config'] ?? {},
    );
  }
}
