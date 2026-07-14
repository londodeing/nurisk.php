import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:maplibre_gl/maplibre_gl.dart';
import 'cop_event_bus.dart';

class CameraRuntimeController {
  MapLibreMapController? _mapController;

  void attach(MapLibreMapController controller) {
    _mapController = controller;
  }

  void detach() {
    _mapController = null;
  }

  Future<void> zoomToIncident(double latitude, double longitude) async {
    if (_mapController == null) return;
    await _mapController!.animateCamera(
      CameraUpdate.newLatLngZoom(LatLng(latitude, longitude), 16.0),
    );
  }

  Future<void> fitBounds(LatLngBounds bounds) async {
    if (_mapController == null) return;
    await _mapController!.animateCamera(
      CameraUpdate.newLatLngBounds(bounds, left: 50, right: 50, top: 50, bottom: 50),
    );
  }

  Future<void> lockNorth() async {
    if (_mapController == null) return;
    await _mapController!.animateCamera(
      CameraUpdate.bearingTo(0.0),
    );
  }
}

final cameraRuntimeProvider = Provider<CameraRuntimeController>((ref) {
  final controller = CameraRuntimeController();
  
  // Listen to event bus for camera intents
  final bus = ref.watch(copEventBusProvider);
  final sub = bus.stream.listen((event) {
    if (event is MarkerSelectedEvent) {
      controller.zoomToIncident(event.latitude, event.longitude);
    }
  });

  ref.onDispose(() {
    sub.cancel();
  });

  return controller;
});
