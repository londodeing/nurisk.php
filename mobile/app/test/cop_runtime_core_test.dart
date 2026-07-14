import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/features/cop/runtime/cop_state_machine.dart';
import 'package:nurisk_mobile/features/cop/runtime/cop_event_bus.dart';
import 'package:nurisk_mobile/features/cop/runtime/selection_context_provider.dart';

void main() {
  group('COP State Machine', () {
    test('Initial state is BOOT', () {
      final container = ProviderContainer();
      final state = container.read(copStateMachineProvider);
      expect(state, CopState.boot);
    });

    test('Transitions from BOOT -> SYNC -> READY -> LIVE', () {
      final container = ProviderContainer();
      final notifier = container.read(copStateMachineProvider.notifier);

      notifier.startSync();
      expect(container.read(copStateMachineProvider), CopState.sync);

      notifier.ready();
      expect(container.read(copStateMachineProvider), CopState.ready);

      notifier.goLive();
      expect(container.read(copStateMachineProvider), CopState.live);
    });

    test('Transitions to DEGRADED and RECOVERY correctly', () {
      final container = ProviderContainer();
      final notifier = container.read(copStateMachineProvider.notifier);

      // Force to live
      notifier.startSync();
      notifier.ready();
      notifier.goLive();

      notifier.degrade();
      expect(container.read(copStateMachineProvider), CopState.degraded);

      notifier.startRecovery();
      expect(container.read(copStateMachineProvider), CopState.recovery);
    });
  });

  group('COP Event Bus and Selection Context', () {
    test('MarkerSelectedEvent updates SelectionContext', () async {
      final container = ProviderContainer();
      
      // Initialize manager so it listens
      container.read(selectionContextManagerProvider);
      
      final bus = container.read(copEventBusProvider);
      
      bus.dispatch(MarkerSelectedEvent(
        markerId: 'marker_123',
        latitude: -7.0,
        longitude: 110.0,
      ));

      // Wait a microtask for stream to process
      await Future.delayed(Duration.zero);

      final context = container.read(selectionContextProvider);
      expect(context.hasSelection, true);
      expect(context.selectedEntityId, 'marker_123');
      expect(context.entityType, 'Marker');
    });

    test('MapTappedEvent clears SelectionContext', () async {
      final container = ProviderContainer();
      container.read(selectionContextManagerProvider);
      
      final bus = container.read(copEventBusProvider);
      
      bus.dispatch(MarkerSelectedEvent(markerId: '123', latitude: 0, longitude: 0));
      await Future.delayed(Duration.zero);
      expect(container.read(selectionContextProvider).hasSelection, true);

      bus.dispatch(MapTappedEvent(latitude: 0, longitude: 0));
      await Future.delayed(Duration.zero);
      expect(container.read(selectionContextProvider).hasSelection, false);
    });
  });
}
