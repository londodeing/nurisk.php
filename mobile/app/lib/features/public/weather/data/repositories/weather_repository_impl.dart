import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/weather_entity.dart';
import '../../domain/repositories/weather_repository.dart';
import '../datasources/weather_remote_datasource.dart';
import '../datasources/weather_local_datasource.dart';

class WeatherRepositoryImpl implements WeatherRepository {
  final WeatherRemoteDatasource remoteDatasource;
  final WeatherLocalDatasource localDatasource;

  WeatherRepositoryImpl(this.remoteDatasource, this.localDatasource);

  @override
  Future<WeatherEntity> getCurrentWeather({required double lat, required double lng}) async {
    final locationId = '${lat}_$lng';

    try {
      // 1. Fetch from Remote
      final remoteWeather = await remoteDatasource.fetchCurrentWeather(lat, lng);
      
      // 2. Cache it Locally
      await localDatasource.cacheWeather(locationId, remoteWeather);
      
      return remoteWeather;
    } catch (e) {
      // 3. Fallback to Local Cache on network failure
      final localWeather = await localDatasource.getCachedWeather(locationId);
      if (localWeather != null) {
        return localWeather;
      }
      throw Exception('Failed to fetch weather data and no cache available.');
    }
  }
}

final weatherRepositoryProvider = Provider<WeatherRepository>((ref) {
  return WeatherRepositoryImpl(
    ref.watch(weatherRemoteDatasourceProvider),
    ref.watch(weatherLocalDatasourceProvider),
  );
});
