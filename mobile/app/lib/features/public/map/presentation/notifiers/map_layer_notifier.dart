import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import '../../data/repositories/map_layer_repository.dart';
import '../../registry/layer_registry.dart';
import 'dart:developer';

class MapLayerState {
  final Set<String> activeLayerIds;
  final bool isLoading;
  final String? error;

  MapLayerState({
    this.activeLayerIds = const {},
    this.isLoading = false,
    this.error,
  });

  MapLayerState copyWith({
    Set<String>? activeLayerIds,
    bool? isLoading,
    String? error,
  }) {
    return MapLayerState(
      activeLayerIds: activeLayerIds ?? this.activeLayerIds,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

class MapLayerNotifier extends Notifier<MapLayerState> {
  @override
  MapLayerState build() => MapLayerState();

  Future<void> toggleLayer(String layerId, dynamic mapController) async {
    final repository = ref.read(mapLayerRepositoryProvider);
    final registry = ref.read(layerRegistryProvider);

    final plugin = registry.getPlugin(layerId);
    if (plugin == null) {
      log('Plugin not found for layer: $layerId');
      return;
    }

    final isCurrentlyActive = state.activeLayerIds.contains(layerId);
    
    if (isCurrentlyActive) {
      // Deactivate layer
      await plugin.removeLayer(mapController);
      final newActiveIds = Set<String>.from(state.activeLayerIds)..remove(layerId);
      state = state.copyWith(activeLayerIds: newActiveIds);
    } else {
      // Activate layer
      state = state.copyWith(isLoading: true, error: null);
      try {
        final geoJsonData = await repository.getLayerData(layerId);
        await plugin.renderLayer(mapController, geoJsonData);
        
        final newActiveIds = Set<String>.from(state.activeLayerIds)..add(layerId);
        state = state.copyWith(activeLayerIds: newActiveIds, isLoading: false);
      } catch (e) {
        state = state.copyWith(isLoading: false, error: DioExceptionMapper.toUserMessage(e));
        log('Error activating layer $layerId: $e');
      }
    }
  }
}

final mapLayerNotifierProvider = NotifierProvider<MapLayerNotifier, MapLayerState>(() {
  return MapLayerNotifier();
});
