import 'dart:math' as math;
import 'package:maplibre_gl/maplibre_gl.dart';

/// GeoUtils acts as the bridge between Semantic Primitives and Render Geometries.
/// 
/// For example, the backend emits `Radius` as a Semantic Primitive (center point + km radius) 
/// to keep payloads lightweight and domain-focused. GeoUtils converts this semantic data 
/// into a `Polygon` (a Render Primitive) for the MapLibre engine, rather than requiring the 
/// server to send heavy GeoJSON coordinate arrays for simple circles.
class GeoUtils {
  /// Generates a List of LatLng representing a polygon circle around a given center
  static List<LatLng> createBufferPolygon(LatLng center, double radiusKm, {int points = 36}) {
    const double earthRadiusKm = 6371.0;
    final double lat = center.latitude * math.pi / 180.0;
    final double lng = center.longitude * math.pi / 180.0;
    final double d = radiusKm / earthRadiusKm;
    
    List<LatLng> circleCoords = [];
    
    for (int i = 0; i <= points; i++) {
      final double bearing = i * 360 / points * math.pi / 180.0;
      final double circleLat = math.asin(
        math.sin(lat) * math.cos(d) + 
        math.cos(lat) * math.sin(d) * math.cos(bearing)
      );
      final double circleLng = lng + math.atan2(
        math.sin(bearing) * math.sin(d) * math.cos(lat), 
        math.cos(d) - math.sin(lat) * math.sin(circleLat)
      );
      
      circleCoords.add(LatLng(circleLat * 180.0 / math.pi, circleLng * 180.0 / math.pi));
    }
    
    return circleCoords;
  }
}
