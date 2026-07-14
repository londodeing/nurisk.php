class WeatherEntity {
  final String locationName;
  final double temperature;
  final String condition;
  final String iconUrl;
  final DateTime updatedAt;

  const WeatherEntity({
    required this.locationName,
    required this.temperature,
    required this.condition,
    required this.iconUrl,
    required this.updatedAt,
  });
}
