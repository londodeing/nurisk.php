import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'cop_event_bus.dart';

class SelectionContext {
  final String? selectedEntityId;
  final String? entityType;
  final Map<String, dynamic>? metadata;

  SelectionContext({this.selectedEntityId, this.entityType, this.metadata});
  
  bool get hasSelection => selectedEntityId != null;
}

class SelectionContextNotifier extends Notifier<SelectionContext> {
  @override
  SelectionContext build() {
    return SelectionContext();
  }

  void selectEntity(String id, String type, [Map<String, dynamic>? metadata]) {
    state = SelectionContext(selectedEntityId: id, entityType: type, metadata: metadata);
  }

  void clearSelection() {
    state = SelectionContext();
  }
}

final selectionContextProvider = NotifierProvider<SelectionContextNotifier, SelectionContext>(() {
  return SelectionContextNotifier();
});

// A provider that automatically manages the subscription
final selectionContextManagerProvider = Provider<void>((ref) {
  final bus = ref.watch(copEventBusProvider);
  final notifier = ref.read(selectionContextProvider.notifier);
  
  final sub = bus.stream.listen((event) {
    if (event is MarkerSelectedEvent) {
      notifier.selectEntity(event.markerId, 'Marker', event.metadata);
    } else if (event is MapTappedEvent) {
      notifier.clearSelection();
    } else if (event is PolygonSelectedEvent) {
      notifier.selectEntity(event.polygonId, 'Polygon');
    }
  });
  
  ref.onDispose(() {
    sub.cancel();
  });
});
