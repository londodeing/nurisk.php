import '../models/map_layer_plugin.dart';
import 'package:maplibre_gl/maplibre_gl.dart';

class GenericGeoJsonPlugin implements MapLayerPlugin {
  final String _layerId;
  final String _name;
  final String _icon;

  GenericGeoJsonPlugin({
    required String layerId,
    required String name,
    String icon = 'layers',
  })  : _layerId = layerId,
        _name = name,
        _icon = icon;

  @override
  String get layerId => _layerId;

  @override
  String get name => _name;

  @override
  String get icon => _icon;

  @override
  String get description => 'Generic GeoJSON Layer';

  @override
  Future<void> renderLayer(dynamic mapController, Map<String, dynamic> geoJsonData) async {
    final MapLibreMapController controller = mapController;

    try { await controller.removeLayer(_layerId + '_line'); } catch (_) {}
    try { await controller.removeLayer(_layerId + '_polygon'); } catch (_) {}
    try { await controller.removeLayer(_layerId); } catch (_) {}
    try { await controller.removeSource(_layerId); } catch (_) {}

    final isCluster = geoJsonData['metadata']?['cluster'] ?? false;

    await controller.addSource(
      _layerId,
      GeojsonSourceProperties(
        data: geoJsonData,
        cluster: isCluster,
        clusterMaxZoom: isCluster ? 14 : null,
        clusterRadius: isCluster ? 50 : null,
      ),
    );

    await controller.addLayer(
      _layerId,
      _layerId,
      CircleLayerProperties(
        circleRadius: 6,
        circleColor: ['get', 'color'],
        circleStrokeWidth: 2,
        circleStrokeColor: '#ffffff',
      ),
      filter: ['==', ['geometry-type'], 'Point'],
    );

    await controller.addLayer(
      _layerId,
      _layerId + '_polygon',
      FillLayerProperties(
        fillColor: ['get', 'fillColor'],
        fillOpacity: 0.5,
      ),
      filter: ['==', ['geometry-type'], 'Polygon'],
    );
    
    await controller.addLayer(
      _layerId,
      _layerId + '_line',
      LineLayerProperties(
        lineColor: ['get', 'lineColor'],
        lineWidth: 3.0,
      ),
      filter: ['==', ['geometry-type'], 'LineString'],
    );
  }

  @override
  Future<void> removeLayer(dynamic mapController) async {
    final MapLibreMapController controller = mapController;
    try { await controller.removeLayer(_layerId + '_line'); } catch (_) {}
    try { await controller.removeLayer(_layerId + '_polygon'); } catch (_) {}
    try { await controller.removeLayer(_layerId); } catch (_) {}
    try { await controller.removeSource(_layerId); } catch (_) {}
  }
}
