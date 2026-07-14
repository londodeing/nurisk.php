import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/warning_entity.dart';
import '../../domain/repositories/warning_repository.dart';
import '../datasources/warning_remote_datasource.dart';
import '../datasources/warning_local_datasource.dart';

class WarningRepositoryImpl implements WarningRepository {
  final WarningRemoteDatasource remoteDatasource;
  final WarningLocalDatasource localDatasource;

  WarningRepositoryImpl(this.remoteDatasource, this.localDatasource);

  @override
  Future<List<WarningEntity>> getActiveWarnings() async {
    try {
      // 1. Fetch from Remote
      final remoteWarnings = await remoteDatasource.fetchActiveWarnings();
      
      // 2. Cache it Locally
      await localDatasource.cacheWarnings(remoteWarnings);
      
      return remoteWarnings;
    } catch (e) {
      // 3. Fallback to Local Cache on network failure
      final localWarnings = await localDatasource.getCachedWarnings();
      if (localWarnings.isNotEmpty) {
        return localWarnings.where((w) => w.isActive).toList();
      }
      throw Exception('Failed to fetch warnings data and no active cache available.');
    }
  }
}

final warningRepositoryProvider = Provider<WarningRepository>((ref) {
  return WarningRepositoryImpl(
    ref.watch(warningRemoteDatasourceProvider),
    ref.watch(warningLocalDatasourceProvider),
  );
});
