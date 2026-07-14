import 'dart:convert';
import 'package:drift/drift.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/storage/public/database_provider.dart';
import 'package:nurisk_mobile/core/storage/public/public_database.dart';
import '../models/dashboard_kpi_model.dart';

abstract class DashboardKpiLocalDatasource {
  Future<DashboardKpiModel?> getCachedKpi();
  Future<void> cacheKpi(DashboardKpiModel kpi);
}

class DashboardKpiLocalDatasourceImpl implements DashboardKpiLocalDatasource {
  final PublicDatabase? _db;

  DashboardKpiLocalDatasourceImpl(this._db);

  @override
  Future<DashboardKpiModel?> getCachedKpi() async {
    final db = _db;
    if (db == null) return null;
    final rows = await (db.select(db.dashboardKPICache)
          ..orderBy([(t) => OrderingTerm.desc(t.updatedAt)])
          ..limit(1))
        .get();
    if (rows.isEmpty) return null;
    final json = jsonDecode(rows.first.dataJson) as Map<String, dynamic>;
    return DashboardKpiModel.fromJson(json);
  }

  @override
  Future<void> cacheKpi(DashboardKpiModel kpi) async {
    final db = _db;
    if (db == null) return;
    await db.into(db.dashboardKPICache).insertOnConflictUpdate(
      DashboardKPICacheCompanion.insert(
        id: 'public_kpi',
        dataJson: jsonEncode(kpi.toJson()),
        updatedAt: DateTime.now(),
      ),
    );
  }
}

final dashboardKpiLocalDatasourceProvider = Provider<DashboardKpiLocalDatasource>((ref) {
  final db = ref.watch(publicDatabaseProvider);
  return DashboardKpiLocalDatasourceImpl(db);
});
