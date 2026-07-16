import '../../../incident/domain/entities/incident_entity.dart';

Map<String, dynamic> incidentsToGeoJson(List<IncidentEntity> incidents) {
  final features = <Map<String, dynamic>>[];
  for (final inc in incidents) {
    if (inc.latitude == null || inc.longitude == null) continue;

    features.add({
      'type': 'Feature',
      'id': inc.id,
      'geometry': {
        'type': 'Point',
        'coordinates': [inc.longitude, inc.latitude],
      },
      'properties': {
        'id': inc.id,
        'title': inc.title,
        'jenis': inc.category,
        'status': inc.status,
        'severity': inc.severity,
        'color': inc.severity == 'HIGH'
            ? '#EF4444'
            : inc.severity == 'MEDIUM'
                ? '#F59E0B'
                : '#3B82F6',
      },
    });
  }

  return {
    'type': 'FeatureCollection',
    'features': features,
  };
}
