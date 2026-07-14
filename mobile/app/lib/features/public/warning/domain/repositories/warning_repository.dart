import '../entities/warning_entity.dart';

abstract class WarningRepository {
  Future<List<WarningEntity>> getActiveWarnings();
}
