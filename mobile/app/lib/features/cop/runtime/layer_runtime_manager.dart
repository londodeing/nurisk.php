import 'package:flutter_riverpod/flutter_riverpod.dart';

class LayerRuntimeState {
  final bool isVisible;
  final double opacity;
  final int zIndex;
  
  LayerRuntimeState({
    this.isVisible = true,
    this.opacity = 1.0,
    this.zIndex = 0,
  });

  LayerRuntimeState copyWith({
    bool? isVisible,
    double? opacity,
    int? zIndex,
  }) {
    return LayerRuntimeState(
      isVisible: isVisible ?? this.isVisible,
      opacity: opacity ?? this.opacity,
      zIndex: zIndex ?? this.zIndex,
    );
  }
}

class LayerRuntimeManager extends Notifier<Map<String, LayerRuntimeState>> {
  @override
  Map<String, LayerRuntimeState> build() {
    return {};
  }

  void registerLayer(String layerId, {int zIndex = 0}) {
    if (!state.containsKey(layerId)) {
      state = {
        ...state,
        layerId: LayerRuntimeState(zIndex: zIndex),
      };
    }
  }

  void toggleVisibility(String layerId) {
    if (state.containsKey(layerId)) {
      final layer = state[layerId]!;
      state = {
        ...state,
        layerId: layer.copyWith(isVisible: !layer.isVisible),
      };
    }
  }

  void setOpacity(String layerId, double opacity) {
    if (state.containsKey(layerId)) {
      final layer = state[layerId]!;
      state = {
        ...state,
        layerId: layer.copyWith(opacity: opacity),
      };
    }
  }

  bool isLayerVisible(String layerId) {
    return state[layerId]?.isVisible ?? false;
  }
}

final layerRuntimeManagerProvider = NotifierProvider<LayerRuntimeManager, Map<String, LayerRuntimeState>>((ref) {
  return LayerRuntimeManager();
});
