import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/incident_entity.dart';
import '../../domain/repositories/incident_repository.dart';
import '../datasources/incident_remote_datasource.dart';
import '../datasources/incident_local_datasource.dart';

class IncidentRepositoryImpl implements IncidentRepository {
  final IncidentRemoteDatasource remoteDatasource;
  final IncidentLocalDatasource localDatasource;

  IncidentRepositoryImpl(this.remoteDatasource, this.localDatasource);

  @override
  Future<List<IncidentEntity>> getLatestIncidents({required int page, required int limit}) async {
    try {
      // 1. Fetch from Remote BFF
      final remoteIncidents = await remoteDatasource.fetchLatestIncidents(page: page, limit: limit);
      
      // 2. Cache it Locally (Only cache page 1 for quick boot)
      if (page == 1) {
        await localDatasource.cacheIncidents(remoteIncidents);
      }
      
      return remoteIncidents;
    } catch (e) {
      // 3. Fallback to Local Cache on network failure (only useful for page 1)
      if (page == 1) {
        final localIncidents = await localDatasource.getCachedIncidents(page: page, limit: limit);
        if (localIncidents.isNotEmpty) {
          return localIncidents;
        }
      }
      throw Exception('Failed to fetch incident data and no cache available.');
    }
  }
}

final incidentRepositoryProvider = Provider<IncidentRepository>((ref) {
  return IncidentRepositoryImpl(
    ref.watch(incidentRemoteDatasourceProvider),
    ref.watch(incidentLocalDatasourceProvider),
  );
});
