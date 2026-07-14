import '../../domain/entities/config_entity.dart';

class ConfigModel extends ConfigEntity {
  const ConfigModel({
    required super.version,
    required super.screenTitle,
    required super.layoutType,
    required super.widgets,
    required super.bottomNav,
    required super.featureFlags,
    required super.rawJson,
  });

  factory ConfigModel.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>? ?? json;
    return ConfigModel(
      version: json['version'] ?? data['version'] ?? '1.0',
      screenTitle: data['screen_title'] ?? data['screen'] ?? 'Dashboard',
      layoutType: data['layout_type'] ?? data['layout'] ?? 'vertical',
      widgets: List<dynamic>.from(data['widgets'] ?? data['nodes'] ?? []),
      bottomNav: [],
      featureFlags: Map<String, bool>.from(data['feature_flags'] ?? json['feature_flags'] ?? {}),
      rawJson: json,
    );
  }

  Map<String, dynamic> toJson() {
    return rawJson;
  }
}
