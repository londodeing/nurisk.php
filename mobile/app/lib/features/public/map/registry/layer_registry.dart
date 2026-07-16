import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../domain/models/map_layer_plugin.dart';

import '../domain/plugins/generic_raster_plugin.dart';

String _tileUrl(String layerName) {
  String base = dotenv.env['API_BASE_URL'] ?? 'http://10.0.2.2:8000/api/';
  if (!base.endsWith('/')) base += '/';
  return '${base}public/map/tiles/{z}/{x}/{y}.png?layers=$layerName';
}

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

  registry.registerPlugin(GenericRasterPlugin(
    layerId: 'flood',
    name: 'Banjir',
    sourceUrl: _tileUrl('raster:INDEKS_BAHAYA_BANJIR1'),
  ));

  registry.registerPlugin(GenericRasterPlugin(
    layerId: 'banjir_bandang',
    name: 'Banjir Bandang',
    sourceUrl: _tileUrl('raster:INDEKS_BAHAYA_BANJIRBANDANG1'),
  ));

  registry.registerPlugin(GenericRasterPlugin(
    layerId: 'longsor',
    name: 'Tanah Longsor',
    sourceUrl: _tileUrl('raster:INDEKS_BAHAYA_TANAHLONGSOR1'),
  ));

  registry.registerPlugin(GenericRasterPlugin(
    layerId: 'cuaca_ekstrim',
    name: 'Cuaca Ekstrim',
    sourceUrl: _tileUrl('raster:INDEKS_BAHAYA_CUACAEKSTRIM1'),
  ));

  registry.registerPlugin(GenericRasterPlugin(
    layerId: 'kekeringan',
    name: 'Kekeringan',
    sourceUrl: _tileUrl('raster:INDEKS_BAHAYA_KEKERINGAN1'),
  ));

  registry.registerPlugin(GenericRasterPlugin(
    layerId: 'gunung_api',
    name: 'Gunung Api',
    sourceUrl: _tileUrl('raster:INDEKS_BAHAYA_GUNUNGAPI1'),
  ));

  return registry;
});
