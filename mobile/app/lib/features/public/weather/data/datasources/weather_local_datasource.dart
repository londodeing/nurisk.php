import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/storage/public/database_provider.dart';
import 'package:nurisk_mobile/core/storage/public/public_database.dart';
import '../models/weather_model.dart';

abstract class WeatherLocalDatasource {
  Future<WeatherModel?> getCachedWeather(String locationId);
  Future<void> cacheWeather(String locationId, WeatherModel weather);
}

class WeatherLocalDatasourceImpl implements WeatherLocalDatasource {
  final PublicDatabase? _db;

  WeatherLocalDatasourceImpl(this._db);

  @override
  Future<WeatherModel?> getCachedWeather(String locationId) async {
    final db = _db;
    if (db == null) return null;
    final rows = await (db.select(db.weatherCache)
          ..where((t) => t.id.equals(locationId))
          ..limit(1))
        .get();
    if (rows.isEmpty) return null;
    final json = jsonDecode(rows.first.dataJson) as Map<String, dynamic>;
    return WeatherModel.fromJson(json);
  }

  @override
  Future<void> cacheWeather(String locationId, WeatherModel weather) async {
    final db = _db;
    if (db == null) return;
    await db.into(db.weatherCache).insertOnConflictUpdate(
          WeatherCacheCompanion.insert(
            id: locationId,
            dataJson: jsonEncode(weather.toJson()),
            updatedAt: DateTime.now(),
          ),
        );
  }
}

final weatherLocalDatasourceProvider = Provider<WeatherLocalDatasource>((ref) {
  final db = ref.watch(publicDatabaseProvider);
  return WeatherLocalDatasourceImpl(db);
});
