import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:maplibre_gl/maplibre_gl.dart';

class MapState {
  final LatLng cameraPosition;
  final double zoom;
  final bool isMapReady;
  // Add other states like current bounding box, selected marker etc.

  MapState({
    required this.cameraPosition,
    required this.zoom,
    this.isMapReady = false,
  });

  MapState copyWith({
    LatLng? cameraPosition,
    double? zoom,
    bool? isMapReady,
  }) {
    return MapState(
      cameraPosition: cameraPosition ?? this.cameraPosition,
      zoom: zoom ?? this.zoom,
      isMapReady: isMapReady ?? this.isMapReady,
    );
  }
}

class MapStateNotifier extends Notifier<MapState> {
  @override
  MapState build() {
    return MapState(
      cameraPosition: const LatLng(-6.200000, 106.816666),
      zoom: 12.0,
    );
  }

  void onMapCreated() {
    state = state.copyWith(isMapReady: true);
  }

  void updateCameraPosition(LatLng position, double zoom) {
    state = state.copyWith(cameraPosition: position, zoom: zoom);
  }
}

final mapStateProvider = NotifierProvider<MapStateNotifier, MapState>(() {
  return MapStateNotifier();
});
