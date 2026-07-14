import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../domain/models/map_layer_plugin.dart';

import '../domain/plugins/generic_geojson_plugin.dart';
import '../domain/plugins/generic_raster_plugin.dart';

class LayerRegistry {
  final Map<String, MapLayerPlugin> _plugins = {};

  void registerPlugin(MapLayerPlugin plugin) {
    _plugins[plugin.layerId] = plugin;
  }

  void unregisterPlugin(String layerId) {
    _plugins.remove(layerId);
  }

  MapLayerPlugin? getPlugin(String layerId) {
    return _plugins[layerId];
  }

  List<MapLayerPlugin> get allPlugins => _plugins.values.toList();
}

final layerRegistryProvider = Provider<LayerRegistry>((ref) {
  final registry = LayerRegistry();
  
  // Register plugins for each layer defined in map config
  registry.registerPlugin(GenericRasterPlugin(
    layerId: 'flood',
    name: 'Banjir',
    sourceUrl: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', // Fallback URL
  ));
  
  registry.registerPlugin(GenericGeoJsonPlugin(
    layerId: 'hotspot',
    name: 'Titik Panas',
  ));

  registry.registerPlugin(GenericGeoJsonPlugin(
    layerId: 'incident',
    name: 'Insiden',
    icon: 'warning',
  ));

  registry.registerPlugin(GenericGeoJsonPlugin(
    layerId: 'ambulance',
    name: 'Ambulans',
    icon: 'local_hospital',
  ));

  registry.registerPlugin(GenericGeoJsonPlugin(
    layerId: 'posko',
    name: 'Posko',
    icon: 'home',
  ));

  registry.registerPlugin(GenericGeoJsonPlugin(
    layerId: 'shelter',
    name: 'Shelter',
    icon: 'house',
  ));

  registry.registerPlugin(GenericGeoJsonPlugin(
    layerId: 'volunteer',
    name: 'Relawan',
    icon: 'person',
  ));

  return registry;
});
