import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/runtime/app_lifecycle_service.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import '../../domain/entities/warning_entity.dart';
import '../../data/repositories/warning_repository_impl.dart';

final warningProvider = AsyncNotifierProvider<WarningNotifier, List<WarningEntity>>(WarningNotifier.new);

class WarningNotifier extends AsyncNotifier<List<WarningEntity>> with AppLifecycleObserver {
  Timer? _pollingTimer;

  @override
  Future<List<WarningEntity>> build() async {
    RuntimeServicesScope.instance.lifecycle.registerObserver(this);

    ref.onDispose(() {
      RuntimeServicesScope.instance.lifecycle.unregisterObserver(this);
      _pollingTimer?.cancel();
    });

    _startPolling();

    return _fetchWarnings();
  }

  @override
  void onBackground() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
  }

  @override
  void onForeground() {
    _startPolling();
    refreshSilently();
  }

  void _startPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = Timer.periodic(const Duration(seconds: 30), (_) {
      refreshSilently();
    });
  }

  Future<List<WarningEntity>> _fetchWarnings() async {
    final repository = ref.read(warningRepositoryProvider);
    return repository.getActiveWarnings();
  }

  Future<void> refresh() async {
    final result = await AsyncValue.guard(() => _fetchWarnings());
    if (result is AsyncData) {
      state = result;
    }
  }

  Future<void> refreshSilently() async {
    final newData = await AsyncValue.guard(() => _fetchWarnings());
    if (newData is AsyncData) {
      state = newData;
    }
  }
}
