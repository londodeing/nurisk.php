import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'cop_state_machine.dart';

class LiveConnectionManager {
  final Ref ref;
  Timer? _mockConnectionTimer;
  StreamController<List<Map<String, dynamic>>>? _patchStream;

  LiveConnectionManager(this.ref);

  Stream<List<Map<String, dynamic>>> get patchStream {
    _patchStream ??= StreamController<List<Map<String, dynamic>>>.broadcast();
    return _patchStream!.stream;
  }

  void connect() {
    final stateNotifier = ref.read(copStateMachineProvider.notifier);
    stateNotifier.startSync();
    
    // Simulate network delay
    Future.delayed(const Duration(milliseconds: 500), () {
      stateNotifier.ready();
      stateNotifier.goLive();
      
      // Simulate receiving patches every few seconds
      _mockConnectionTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
        // Emit a mock patch (e.g. moving a marker)
        if (_patchStream != null && !_patchStream!.isClosed) {
          _patchStream!.add([
            {
              "op": "replace",
              "path": "/layers/0/primitives/0/props/latitude",
              "value": -7.55 + (timer.tick * 0.001) // Simulate movement
            }
          ]);
        }
      });
    });
  }

  void disconnect() {
    _mockConnectionTimer?.cancel();
    final stateNotifier = ref.read(copStateMachineProvider.notifier);
    stateNotifier.degrade();
    
    // Simulate complete offline after degraded
    Future.delayed(const Duration(seconds: 2), () {
      if (ref.read(copStateMachineProvider) == CopState.degraded) {
        stateNotifier.goOffline();
      }
    });
  }

  void dispose() {
    _mockConnectionTimer?.cancel();
    _patchStream?.close();
  }
}

final liveConnectionManagerProvider = Provider<LiveConnectionManager>((ref) {
  final manager = LiveConnectionManager(ref);
  ref.onDispose(() => manager.dispose());
  return manager;
});
