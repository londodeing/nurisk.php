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
    
    // In a generic plugin, source_url and metadata (like TileSize) should come from config
    final url = config['source_url'] ?? _sourceUrl;
    final metadata = config['metadata'] ?? {};
    final tileSize = metadata['tile_size'] ?? 256;

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
        rasterOpacity: metadata['opacity'] ?? 0.7,
      ),
    );
  }

  @override
  Future<void> removeLayer(dynamic mapController) async {
    final MapLibreMapController controller = mapController;
    await controller.removeLayer(_layerId);
    await controller.removeSource(_layerId);
  }
}
