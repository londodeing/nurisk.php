import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';
import '../models/weather_model.dart';

abstract class WeatherRemoteDatasource {
  Future<WeatherModel> fetchCurrentWeather(double lat, double lng);
}

class WeatherRemoteDatasourceImpl implements WeatherRemoteDatasource {
  final Dio dio;

  WeatherRemoteDatasourceImpl(this.dio);

  @override
  Future<WeatherModel> fetchCurrentWeather(double lat, double lng) async {
    try {
      final response = await dio.get(
        'internal/weather/current',
        queryParameters: {'lat': lat, 'lng': lng},
      );
      
      if (response.statusCode == 200) {
        final data = response.data['data'];
        if (data == null) {
          throw Exception('Weather data not available');
        }
        return WeatherModel.fromJson(data);
      } else {
        throw Exception('Failed to load weather data');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }
}

final weatherRemoteDatasourceProvider = Provider<WeatherRemoteDatasource>((ref) {
  return WeatherRemoteDatasourceImpl(ref.watch(publicApiClientProvider));
});
