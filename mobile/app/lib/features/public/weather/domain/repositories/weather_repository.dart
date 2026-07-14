import '../entities/weather_entity.dart';

abstract class WeatherRepository {
  Future<WeatherEntity> getCurrentWeather({required double lat, required double lng});
}
