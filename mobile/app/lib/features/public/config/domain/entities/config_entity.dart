class ConfigEntity {
  final String version;
  final String screenTitle;
  final String layoutType;
  final List<dynamic> widgets;
  final List<dynamic> bottomNav;
  final Map<String, bool> featureFlags;
  final Map<String, dynamic> rawJson;

  const ConfigEntity({
    required this.version,
    required this.screenTitle,
    required this.layoutType,
    required this.widgets,
    required this.bottomNav,
    required this.featureFlags,
    required this.rawJson,
  });

  bool isFeatureEnabled(String featureName) {
    return featureFlags[featureName] ?? false;
  }
}
