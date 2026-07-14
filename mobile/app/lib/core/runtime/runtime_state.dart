import 'package:flutter_riverpod/flutter_riverpod.dart';

enum RuntimeStatus { uninitialized, ok, degraded, failed }

class RuntimeState {
  final RuntimeStatus status;
  final List<String> failedComponents;
  final List<String> degradedComponents;

  const RuntimeState({
    this.status = RuntimeStatus.uninitialized,
    this.failedComponents = const [],
    this.degradedComponents = const [],
  });

  RuntimeState copyWith({
    RuntimeStatus? status,
    List<String>? failedComponents,
    List<String>? degradedComponents,
  }) {
    return RuntimeState(
      status: status ?? this.status,
      failedComponents: failedComponents ?? this.failedComponents,
      degradedComponents: degradedComponents ?? this.degradedComponents,
    );
  }

  bool get isOperational => status == RuntimeStatus.ok || status == RuntimeStatus.degraded;
}

class RuntimeStateNotifier extends Notifier<RuntimeState> {
  @override
  RuntimeState build() => const RuntimeState();

  void set(RuntimeState newState) => state = newState;

  void markOk() => state = state.copyWith(status: RuntimeStatus.ok);

  void markDegraded(String component) {
    state = state.copyWith(
      status: RuntimeStatus.degraded,
      degradedComponents: [...state.degradedComponents, component],
    );
  }

  void markFailed(String component) {
    state = state.copyWith(
      status: RuntimeStatus.failed,
      failedComponents: [...state.failedComponents, component],
    );
  }

  void reset() => state = const RuntimeState();
}

final runtimeStateProvider = NotifierProvider<RuntimeStateNotifier, RuntimeState>(
  RuntimeStateNotifier.new,
);
