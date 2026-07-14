import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/features/cop/runtime/cop_state_machine.dart';
import 'package:nurisk_mobile/features/cop/runtime/live_connection_manager.dart';
import 'package:nurisk_mobile/features/cop/runtime/offline_cache_manager.dart';

void main() {
  group('LiveConnectionManager', () {
    test('Connect transitions state to LIVE', () async {
      final container = ProviderContainer();
      final manager = container.read(liveConnectionManagerProvider);
      
      manager.connect();
      // Should be sync immediately
      expect(container.read(copStateMachineProvider), CopState.sync);
      
      // Wait for simulated delay
      await Future.delayed(const Duration(milliseconds: 600));
      expect(container.read(copStateMachineProvider), CopState.live);
    });

    test('Disconnect transitions state to DEGRADED then OFFLINE', () async {
      final container = ProviderContainer();
      final manager = container.read(liveConnectionManagerProvider);
      
      manager.connect();
      await Future.delayed(const Duration(milliseconds: 600));
      
      manager.disconnect();
      expect(container.read(copStateMachineProvider), CopState.degraded);
      
      await Future.delayed(const Duration(seconds: 3));
      expect(container.read(copStateMachineProvider), CopState.offline);
    });
  });

  group('OfflineCacheManager', () {
    test('serializes and deserializes scene snapshot', () async {
      final container = ProviderContainer();
      final cache = container.read(offlineCacheManagerProvider);
      
      final mockScene = {
        'type': 'Scene',
        'layers': [
          {'id': 'test', 'type': 'IncidentLayer'}
        ]
      };
      
      await cache.saveSceneSnapshot(mockScene);
      
      final loaded = await cache.loadSceneSnapshot();
      expect(loaded, isNotNull);
      expect(loaded!['type'], 'Scene');
      expect((loaded['layers'] as List).length, 1);
    });
  });
}
