import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/config_entity.dart';
import '../../domain/repositories/config_repository.dart';
import '../datasources/config_remote_datasource.dart';
import '../datasources/config_local_datasource.dart';

class ConfigRepositoryImpl implements ConfigRepository {
  final ConfigRemoteDatasource remoteDatasource;
  final ConfigLocalDatasource localDatasource;

  ConfigRepositoryImpl(this.remoteDatasource, this.localDatasource);

  @override
  Future<ConfigEntity> getDashboardConfig() async {
    try {
      final remoteConfig = await remoteDatasource.fetchDashboardConfig();
      await localDatasource.cacheConfig(remoteConfig);
      return remoteConfig;
    } catch (e) {
      final localConfig = await localDatasource.getCachedConfig();
      if (localConfig != null) {
        return localConfig;
      }
      throw Exception('Failed to fetch config data and no fallback available.');
    }
  }
}

final configRepositoryProvider = Provider<ConfigRepository>((ref) {
  return ConfigRepositoryImpl(
    ref.watch(configRemoteDatasourceProvider),
    ref.watch(configLocalDatasourceProvider),
  );
});
