import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';

abstract class CopEvent {}

class MarkerSelectedEvent extends CopEvent {
  final String markerId;
  final double latitude;
  final double longitude;
  final Map<String, dynamic> metadata;

  MarkerSelectedEvent({
    required this.markerId,
    required this.latitude,
    required this.longitude,
    this.metadata = const {},
  });
}

class MapTappedEvent extends CopEvent {
  final double latitude;
  final double longitude;

  MapTappedEvent({required this.latitude, required this.longitude});
}

class PolygonSelectedEvent extends CopEvent {
  final String polygonId;

  PolygonSelectedEvent({required this.polygonId});
}

class CopEventBus {
  final _controller = StreamController<CopEvent>.broadcast();

  Stream<CopEvent> get stream => _controller.stream;

  void dispatch(CopEvent event) {
    _controller.add(event);
  }

  void dispose() {
    _controller.close();
  }
}

final copEventBusProvider = Provider<CopEventBus>((ref) {
  final bus = CopEventBus();
  ref.onDispose(() => bus.dispose());
  return bus;
});
