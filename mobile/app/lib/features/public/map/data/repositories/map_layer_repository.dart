import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../datasources/map_layer_datasource.dart';

class MapLayerRepository {
  static const Set<String> _rasterLayerIds = {
    'flood', 'banjir_bandang', 'longsor', 'cuaca_ekstrim', 'kekeringan', 'gunung_api',
  };

  final MapLayerDatasource _datasource;

  MapLayerRepository(this._datasource);

  Future<Map<String, dynamic>> getLayerData(String layerId) async {
    if (_rasterLayerIds.contains(layerId)) {
      return {};
    }
    return await _datasource.fetchLayerGeoJson(layerId);
  }
}

final mapLayerRepositoryProvider = Provider<MapLayerRepository>((ref) {
  final datasource = ref.watch(mapLayerDatasourceProvider);
  return MapLayerRepository(datasource);
});
