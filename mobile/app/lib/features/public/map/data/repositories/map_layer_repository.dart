import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../datasources/map_layer_datasource.dart';

class MapLayerRepository {
  final MapLayerDatasource _datasource;

  MapLayerRepository(this._datasource);

  /// Fetches GeoJSON for a specific layer.
  /// According to the Spatial API Contract (M1.5), 
  /// all layers return a standardized GeoJSON FeatureCollection.
  Future<Map<String, dynamic>> getLayerData(String layerId) async {
    return await _datasource.fetchLayerGeoJson(layerId);
  }
}

final mapLayerRepositoryProvider = Provider<MapLayerRepository>((ref) {
  final datasource = ref.watch(mapLayerDatasourceProvider);
  return MapLayerRepository(datasource);
});
