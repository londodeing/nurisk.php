import 'dart:convert';
import 'package:drift/drift.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/storage/public/database_provider.dart';
import 'package:nurisk_mobile/core/storage/public/public_database.dart';
import '../models/incident_model.dart';

abstract class IncidentLocalDatasource {
  Future<List<IncidentModel>> getCachedIncidents({required int page, required int limit});
  Future<void> cacheIncidents(List<IncidentModel> incidents);
}

class IncidentLocalDatasourceImpl implements IncidentLocalDatasource {
  final PublicDatabase? _db;

  IncidentLocalDatasourceImpl(this._db);

  @override
  Future<List<IncidentModel>> getCachedIncidents({required int page, required int limit}) async {
    final db = _db;
    if (db == null) return [];
    final offset = (page - 1) * limit;
    final rows = await (db.select(db.incidentCache)
          ..orderBy([(t) => OrderingTerm.asc(t.updatedAt)])
          ..limit(limit, offset: offset))
        .get();
    return rows.map((r) {
      final json = jsonDecode(r.dataJson) as Map<String, dynamic>;
      return IncidentModel.fromJson(json);
    }).toList();
  }

  @override
  Future<void> cacheIncidents(List<IncidentModel> incidents) async {
    final db = _db;
    if (db == null) return;
    await db.transaction(() async {
      for (final incident in incidents) {
        await db.into(db.incidentCache).insertOnConflictUpdate(
          IncidentCacheCompanion.insert(
            id: incident.id,
            dataJson: jsonEncode(incident.toJson()),
            updatedAt: DateTime.now(),
          ),
        );
      }
    });
  }
}

final incidentLocalDatasourceProvider = Provider<IncidentLocalDatasource>((ref) {
  final db = ref.watch(publicDatabaseProvider);
  return IncidentLocalDatasourceImpl(db);
});
