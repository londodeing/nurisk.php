import 'package:flutter_riverpod/flutter_riverpod.dart';

enum CopState {
  boot,
  sync,
  ready,
  live,
  degraded,
  offline,
  recovery,
}

class CopStateMachine extends Notifier<CopState> {
  @override
  CopState build() {
    return CopState.boot;
  }

  void startSync() {
    if (state == CopState.boot || state == CopState.offline || state == CopState.recovery) {
      state = CopState.sync;
    }
  }

  void ready() {
    if (state == CopState.sync) {
      state = CopState.ready;
    }
  }

  void goLive() {
    if (state == CopState.ready || state == CopState.degraded || state == CopState.recovery) {
      state = CopState.live;
    }
  }

  void degrade() {
    if (state == CopState.live) {
      state = CopState.degraded;
    }
  }

  void goOffline() {
    if (state == CopState.live || state == CopState.degraded || state == CopState.sync) {
      state = CopState.offline;
    }
  }

  void startRecovery() {
    if (state == CopState.offline || state == CopState.degraded) {
      state = CopState.recovery;
    }
  }
}

final copStateMachineProvider = NotifierProvider<CopStateMachine, CopState>(() {
  return CopStateMachine();
});
