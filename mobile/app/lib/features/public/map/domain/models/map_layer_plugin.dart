abstract class MapLayerPlugin {
  /// Unique ID for the layer (e.g. 'hazard_banjir', 'incident_verified')
  String get layerId;

  /// Human readable name for the layer
  String get name;

  /// Icon to display in the filter menu/legend
  String get icon;

  /// Description for the legend
  String get description;

  /// Define how this plugin applies its GeoJSON data to MapLibre.
  /// Because MapLibre controllers are dynamically created,
  /// this method takes the controller and the loaded GeoJSON data as arguments.
  Future<void> renderLayer(dynamic mapController, Map<String, dynamic> geoJsonData);

  /// Logic to remove the layer from MapLibre
  Future<void> removeLayer(dynamic mapController);
}
