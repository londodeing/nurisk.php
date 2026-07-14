import '../../domain/entities/weather_entity.dart';

class WeatherModel extends WeatherEntity {
  const WeatherModel({
    required super.locationName,
    required super.temperature,
    required super.condition,
    required super.iconUrl,
    required super.updatedAt,
  });

  factory WeatherModel.fromJson(Map<String, dynamic> json) {
    // Generate OpenWeatherMap icon URL from condition_code if available
    final String? conditionCode = json['condition_code'] as String?;
    final String iconUrl = conditionCode != null 
        ? 'https://openweathermap.org/img/wn/$conditionCode@2x.png' 
        : '';

    return WeatherModel(
      locationName: json['location_name'] ?? 'Pusat Operasi',
      temperature: (json['temperature'] as num?)?.toDouble() ?? 0.0,
      condition: json['condition'] ?? 'Unknown',
      iconUrl: json['icon_url'] ?? iconUrl,
      updatedAt: json['timestamp'] != null 
          ? DateTime.tryParse(json['timestamp']) ?? DateTime.now()
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'location_name': locationName,
      'temperature': temperature,
      'condition': condition,
      'icon_url': iconUrl,
      'updated_at': updatedAt.toIso8601String(),
    };
  }
}
