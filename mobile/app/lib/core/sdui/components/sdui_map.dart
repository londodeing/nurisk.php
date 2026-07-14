import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:maplibre_gl/maplibre_gl.dart' as ml;
import 'dart:io' as io;

import 'package:nurisk_mobile/features/cop/runtime/cop_event_bus.dart';
import 'package:nurisk_mobile/features/cop/runtime/camera_runtime_controller.dart';
import 'package:nurisk_mobile/core/utils/geo_utils.dart' as nurisk_geo;

class SduiMap extends SduiComponent {
  const SduiMap({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final centerLat = (node.props['center_lat'] as num?)?.toDouble() ?? -7.595;
    final centerLng = (node.props['center_lng'] as num?)?.toDouble() ?? 110.952;
    final zoom = (node.props['zoom'] as num?)?.toDouble() ?? 12.0;
    
    final layers = (node.props['layers'] as List<dynamic>?) ?? [];
    
    // Extract all markers from layers or fallback to children
    final markers = <dynamic>[];
    if (layers.isNotEmpty) {
      for (var layer in layers) {
        if (layer['visible'] == true) {
           final primitives = layer['primitives'] as List<dynamic>? ?? [];
           markers.addAll(primitives);
        }
      }
    } else {
      markers.addAll(node.children ?? []);
    }

    // In headless test environments (Linux host) or web, we render a mock representation
    // to prevent native PlatformView crashes, but we also embed markers semantically.
    if (kIsWeb || (!kIsWeb && io.Platform.isLinux)) {
      return Container(
        height: 300,
        decoration: BoxDecoration(
          color: Colors.blue.shade50,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.blue.shade200),
        ),
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(8.0),
              child: Text(
                'Map Center: $centerLat, $centerLng (Zoom: $zoom)',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
            Expanded(
              child: ListView(
                children: markers.map((child) {
                  if (child is Map && child['type'] == 'Marker') {
                    final lat = child['props']['latitude'];
                    final lng = child['props']['longitude'];
                    final title = child['props']['title'] ?? 'Marker';
                    return ListTile(
                      leading: Icon(Icons.location_on, color: child['props']['color'] == 'danger' ? Colors.red : Colors.orange),
                      title: Text(title),
                      subtitle: Text('$lat, $lng'),
                    );
                  } else if (child is SduiNode && child.type == 'Marker') {
                    final lat = child.props['latitude'];
                    final lng = child.props['longitude'];
                    final title = child.props['title'] ?? 'Marker';
                    return ListTile(
                      leading: Icon(Icons.location_on, color: child.props['color'] == 'danger' ? Colors.red : Colors.orange),
                      title: Text(title),
                      subtitle: Text('$lat, $lng'),
                    );
                  }
                  return const SizedBox.shrink();
                }).toList(),
              ),
            ),
          ],
        ),
      );
    }

    return SizedBox(
      height: 300,
      child: ml.MapLibreMap(
        initialCameraPosition: ml.CameraPosition(
          target: ml.LatLng(centerLat, centerLng),
          zoom: zoom,
        ),
        onMapCreated: (controller) {
          // Attach CameraRuntimeController to MapLibreMapController
          ref.read(cameraRuntimeProvider).attach(controller);

          // Add markers dynamically in real environment
          for (var child in markers) {
            final childType = child is Map ? child['type'] : (child as SduiNode).type;
            final props = child is Map ? child['props'] : (child as SduiNode).props;
            
            if (childType == 'Marker') {
              final lat = (props['latitude'] as num).toDouble();
              final lng = (props['longitude'] as num).toDouble();
              controller.addSymbol(ml.SymbolOptions(
                geometry: ml.LatLng(lat, lng),
                iconImage: props['icon'] ?? 'warning',
                textField: props['title'],
              ), {'id': props['id']}); // Store metadata
            } else if (childType == 'Polyline') {
              final coords = props['coordinates'] as List<dynamic>;
              final geometry = coords.map((c) => ml.LatLng((c[0] as num).toDouble(), (c[1] as num).toDouble())).toList();
              
              controller.addLine(ml.LineOptions(
                geometry: geometry,
                lineColor: _getColor(props['color'] as String?),
                lineWidth: (props['width'] as num?)?.toDouble() ?? 2.0,
              ), {'id': props['id'], 'type': childType});
            } else if (childType == 'Polygon' || childType == 'Radius') {
              List<ml.LatLng> geometry = [];
              if (childType == 'Polygon') {
                final coords = props['coordinates'] as List<dynamic>;
                geometry = coords.map((c) => ml.LatLng((c[0] as num).toDouble(), (c[1] as num).toDouble())).toList();
              } else if (childType == 'Radius') {
                final centerArr = props['center'] as List<dynamic>;
                final center = ml.LatLng((centerArr[0] as num).toDouble(), (centerArr[1] as num).toDouble());
                final radiusKm = (props['radius_km'] as num).toDouble();
                geometry = nurisk_geo.GeoUtils.createBufferPolygon(center, radiusKm);
              }

              controller.addFill(ml.FillOptions(
                geometry: [geometry],
                fillColor: _getColor(props['fillColor'] as String?),
                fillOpacity: (props['opacity'] as num?)?.toDouble() ?? 0.3,
              ), {'id': props['id'], 'type': childType});
            }
          }
          
          controller.onFeatureTapped.add((point, coordinates, id, layerId, annotation) {
            // Check if it's a spatial layer tap based on metadata (requires keeping track or standardizing ID)
            // If the ID starts with evacuation or blast, we can dispatch PolygonSelectedEvent.
            // In a real app we read the metadata injected into the addSymbol/addFill call.
            if (id.toString().contains('zone') || id.toString().contains('radius')) {
              ref.read(copEventBusProvider).dispatch(PolygonSelectedEvent(polygonId: id.toString()));
            } else {
              ref.read(copEventBusProvider).dispatch(MarkerSelectedEvent(
                markerId: id.toString(),
                latitude: coordinates.latitude,
                longitude: coordinates.longitude,
              ));
            }
          });
        },
      ),
    );
  }

  String _getColor(String? colorStr) {
    switch (colorStr) {
      case 'danger': return '#f44336';
      case 'warning': return '#ff9800';
      case 'info': return '#2196f3';
      case 'success': return '#4caf50';
      default: return '#9e9e9e';
    }
  }
}
