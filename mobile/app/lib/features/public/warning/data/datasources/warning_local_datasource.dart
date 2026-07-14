import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/storage/public/database_provider.dart';
import 'package:nurisk_mobile/core/storage/public/public_database.dart';
import '../models/warning_model.dart';

abstract class WarningLocalDatasource {
  Future<List<WarningModel>> getCachedWarnings();
  Future<void> cacheWarnings(List<WarningModel> warnings);
}

class WarningLocalDatasourceImpl implements WarningLocalDatasource {
  final PublicDatabase? _db;

  WarningLocalDatasourceImpl(this._db);

  @override
  Future<List<WarningModel>> getCachedWarnings() async {
    final db = _db;
    if (db == null) return [];
    final rows = await db.select(db.warningCache).get();
    return rows.map((r) {
      final json = jsonDecode(r.dataJson) as Map<String, dynamic>;
      return WarningModel.fromJson(json);
    }).toList();
  }

  @override
  Future<void> cacheWarnings(List<WarningModel> warnings) async {
    final db = _db;
    if (db == null) return;
    await db.transaction(() async {
      for (final w in warnings) {
        await db.into(db.warningCache).insertOnConflictUpdate(
          WarningCacheCompanion.insert(
            id: w.id,
            dataJson: jsonEncode(w.toJson()),
            updatedAt: DateTime.now(),
          ),
        );
      }
    });
  }
}

final warningLocalDatasourceProvider = Provider<WarningLocalDatasource>((ref) {
  final db = ref.watch(publicDatabaseProvider);
  return WarningLocalDatasourceImpl(db);
});
