import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/config_entity.dart';
import '../../data/repositories/config_repository_impl.dart';

final configProvider = AsyncNotifierProvider<ConfigNotifier, ConfigEntity>(ConfigNotifier.new);

class ConfigNotifier extends AsyncNotifier<ConfigEntity> {
  @override
  Future<ConfigEntity> build() async {
    return _fetchConfig();
  }

  Future<ConfigEntity> _fetchConfig() async {
    final repository = ref.read(configRepositoryProvider);
    return repository.getDashboardConfig();
  }

  Future<void> refresh() async {
    final result = await AsyncValue.guard(() => _fetchConfig());
    if (result is AsyncData) {
      state = result;
    }
  }
}
