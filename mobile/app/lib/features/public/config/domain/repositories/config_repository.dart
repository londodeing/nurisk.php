import '../entities/config_entity.dart';

abstract class ConfigRepository {
  Future<ConfigEntity> getDashboardConfig();
}
