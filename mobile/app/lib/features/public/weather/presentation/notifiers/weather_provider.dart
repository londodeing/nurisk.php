import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/weather_entity.dart';
import '../../data/repositories/weather_repository_impl.dart';

final weatherProvider = AsyncNotifierProvider<WeatherNotifier, WeatherEntity>(WeatherNotifier.new);

class WeatherNotifier extends AsyncNotifier<WeatherEntity> {
  @override
  Future<WeatherEntity> build() async {
    // Default location (e.g. Jakarta) for public dashboard
    return _fetchWeather(-6.2088, 106.8456);
  }

  Future<WeatherEntity> _fetchWeather(double lat, double lng) async {
    final repository = ref.read(weatherRepositoryProvider);
    return repository.getCurrentWeather(lat: lat, lng: lng);
  }

  Future<void> refresh() async {
    final result = await AsyncValue.guard(() => _fetchWeather(-6.2088, 106.8456));
    if (result is AsyncData) {
      state = result;
    }
  }
}
