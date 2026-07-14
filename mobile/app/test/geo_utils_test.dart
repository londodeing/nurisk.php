import 'package:flutter_test/flutter_test.dart';
import 'package:maplibre_gl/maplibre_gl.dart';
import 'package:nurisk_mobile/core/utils/geo_utils.dart';

void main() {
  group('GeoUtils', () {
    test('createBufferPolygon generates correct number of points', () {
      final center = const LatLng(-7.595, 110.952);
      final points = GeoUtils.createBufferPolygon(center, 5.0, points: 36);
      
      // 36 segments + 1 closing point = 37 points
      expect(points.length, 37);
      
      // First and last point must be identical to close the polygon
      expect(points.first.latitude, closeTo(points.last.latitude, 0.000001));
      expect(points.first.longitude, closeTo(points.last.longitude, 0.000001));
    });

    test('createBufferPolygon coordinates are around center', () {
      final center = const LatLng(-7.595, 110.952);
      final points = GeoUtils.createBufferPolygon(center, 5.0, points: 4);
      
      for (var point in points) {
        // Since it's a 5km radius, the lat/lng shouldn't be too far from center
        expect((point.latitude - center.latitude).abs(), lessThan(0.1));
        expect((point.longitude - center.longitude).abs(), lessThan(0.1));
      }
    });
  });
}
