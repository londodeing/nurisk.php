import '../models/map_layer_plugin.dart';
import 'package:maplibre_gl/maplibre_gl.dart';

class GenericRasterPlugin implements MapLayerPlugin {
  final String _layerId;
  final String _name;
  final String _icon;
  final String _sourceUrl;

  GenericRasterPlugin({
    required String layerId,
    required String name,
    required String sourceUrl,
    String icon = 'map',
  })  : _layerId = layerId,
        _name = name,
        _icon = icon,
        _sourceUrl = sourceUrl;

  @override
  String get layerId => _layerId;

  @override
  String get name => _name;

  @override
  String get icon => _icon;

  @override
  String get description => 'Generic Raster Layer (WMS/WMTS/XYZ/TileJSON)';

  @override
  Future<void> renderLayer(dynamic mapController, Map<String, dynamic> config) async {
    final MapLibreMapController controller = mapController;
    
    try { await controller.removeLayer(_layerId); } catch (_) {}
    try { await controller.removeSource(_layerId); } catch (_) {}

    final url = config['source_url'] ?? _sourceUrl;
    final meta = config['metadata'];
    final tileSize = (meta is Map ? (meta['tile_size'] as num?)?.toDouble() : null) ?? 256.0;
    final opacity = (meta is Map ? (meta['opacity'] as num?)?.toDouble() : null) ?? 0.7;

    await controller.addSource(
      _layerId,
      RasterSourceProperties(
        tiles: [url],
        tileSize: tileSize,
      ),
    );

    await controller.addLayer(
      _layerId,
      _layerId,
      RasterLayerProperties(
        rasterOpacity: opacity,
      ),
    );
  }

  @override
  Future<void> removeLayer(dynamic mapController) async {
    final MapLibreMapController controller = mapController;
    try { await controller.removeLayer(_layerId); } catch (_) {}
    try { await controller.removeSource(_layerId); } catch (_) {}
  }
}
