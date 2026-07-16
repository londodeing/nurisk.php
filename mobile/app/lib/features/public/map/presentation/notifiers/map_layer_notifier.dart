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
    if (mapController == null) {
      log('toggleLayer skipped: mapController is null for $layerId');
      return;
    }

    final repository = ref.read(mapLayerRepositoryProvider);
    final registry = ref.read(layerRegistryProvider);

    final plugin = registry.getPlugin(layerId);
    if (plugin == null) {
      log('Plugin not found for layer: $layerId');
      return;
    }

    try {
      final isCurrentlyActive = state.activeLayerIds.contains(layerId);

      if (isCurrentlyActive) {
        await plugin.removeLayer(mapController);
        final newActiveIds = Set<String>.from(state.activeLayerIds)..remove(layerId);
        state = state.copyWith(activeLayerIds: newActiveIds);
      } else {
        state = state.copyWith(isLoading: true, error: null);
        final geoJsonData = await repository.getLayerData(layerId);
        await plugin.renderLayer(mapController, geoJsonData);

        final newActiveIds = Set<String>.from(state.activeLayerIds)..add(layerId);
        state = state.copyWith(activeLayerIds: newActiveIds, isLoading: false);
      }
      } catch (e, stack) {
      log('Error toggling layer $layerId: $e\n$stack');
      state = state.copyWith(
        isLoading: false,
        error: e.toString().length > 120
            ? '${e.runtimeType}: ${e.toString().substring(0, 120)}...'
            : '${e.runtimeType}: $e',
      );
    }
  }

  Future<void> refreshLayer(String layerId, dynamic mapController) async {
    if (mapController == null) return;
    if (!state.activeLayerIds.contains(layerId)) return;

    final registry = ref.read(layerRegistryProvider);
    final plugin = registry.getPlugin(layerId);
    if (plugin == null) return;

    try {
      final repository = ref.read(mapLayerRepositoryProvider);
      final geoJsonData = await repository.getLayerData(layerId);
      await plugin.removeLayer(mapController);
      await plugin.renderLayer(mapController, geoJsonData);
    } catch (e, stack) {
      log('Error refreshing layer $layerId: $e\n$stack');
    }
  }
}

final mapLayerNotifierProvider = NotifierProvider<MapLayerNotifier, MapLayerState>(() {
  return MapLayerNotifier();
});
