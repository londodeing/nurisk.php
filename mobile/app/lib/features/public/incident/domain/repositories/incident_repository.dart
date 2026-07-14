import '../entities/incident_entity.dart';

abstract class IncidentRepository {
  Future<List<IncidentEntity>> getLatestIncidents({required int page, required int limit});
}
