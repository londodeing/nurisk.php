class OperationalObject {
  final String id;
  final String objectType;
  final String status;
  final String title;
  final String summary;
  
  // Render Specific
  final String? icon;
  final String? color;
  final int priority;

  // View Models
  final Map<String, dynamic> popupJson;
  final List<dynamic> timelineJson;
  final Map<String, dynamic> dashboardJson;
  final List<dynamic> permissions;
  final List<dynamic> actions;
  final int refreshInterval;

  OperationalObject({
    required this.id,
    required this.objectType,
    required this.status,
    required this.title,
    required this.summary,
    this.icon,
    this.color,
    this.priority = 0,
    this.popupJson = const {},
    this.timelineJson = const [],
    this.dashboardJson = const {},
    this.permissions = const [],
    this.actions = const [],
    this.refreshInterval = 60,
  });

  /// Factory method to parse from GeoJSON Feature properties
  factory OperationalObject.fromGeoJsonProperties(String featureId, Map<String, dynamic> properties) {
    return OperationalObject(
      id: properties['id']?.toString() ?? featureId,
      objectType: properties['object_type'] ?? 'unknown',
      status: properties['status'] ?? 'UNKNOWN',
      title: properties['title'] ?? 'No Title',
      summary: properties['summary'] ?? '',
      icon: properties['icon'],
      color: properties['color'],
      priority: properties['priority'] ?? 0,
      popupJson: properties['popup_json'] is Map<String, dynamic> ? properties['popup_json'] : {},
      timelineJson: properties['timeline_json'] is List ? properties['timeline_json'] : [],
      dashboardJson: properties['dashboard_json'] is Map<String, dynamic> ? properties['dashboard_json'] : {},
      permissions: properties['permissions'] is List ? properties['permissions'] : [],
      actions: properties['actions'] is List ? properties['actions'] : [],
      refreshInterval: properties['refresh_interval'] ?? 60,
    );
  }
}
