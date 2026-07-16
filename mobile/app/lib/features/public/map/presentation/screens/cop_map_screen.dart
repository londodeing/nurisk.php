import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:maplibre_gl/maplibre_gl.dart';
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import 'package:nurisk_mobile/core/runtime/app_lifecycle_service.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';

import '../notifiers/map_layer_notifier.dart';
import '../../data/datasources/map_layer_datasource.dart';
import 'package:nurisk_mobile/features/map/presentation/widgets/layer_control_bottom_sheet.dart';
import '../../domain/services/incident_geo_json_bridge.dart';
import 'package:nurisk_mobile/features/public/incident/presentation/notifiers/incident_provider.dart';
import 'package:nurisk_mobile/features/public/incident/domain/entities/incident_entity.dart';

class CopMapScreen extends ConsumerStatefulWidget {
  const CopMapScreen({super.key});

  @override
  ConsumerState<CopMapScreen> createState() => _CopMapScreenState();
}

class _CopMapScreenState extends ConsumerState<CopMapScreen> with AppLifecycleObserver {
  MapLibreMapController? mapController;
  Timer? _liveUpdateTimer;
  final Set<String> _attachedSources = {};
  LatLng? _lastUserLocation;

  @override
  void initState() {
    super.initState();
    RuntimeServicesScope.instance.lifecycle.registerObserver(this);
    _startLiveUpdates();
  }

  void _startLiveUpdates() {
    _liveUpdateTimer = Timer.periodic(const Duration(seconds: 30), (timer) {
      if (mapController != null) {
        final notifier = ref.read(mapLayerNotifierProvider.notifier);
        final activeLayers = ref.read(mapLayerNotifierProvider).activeLayerIds;
        for (var layerId in activeLayers) {
          notifier.refreshLayer(layerId, mapController);
        }
      }
    });
  }

  @override
  void dispose() {
    _liveUpdateTimer?.cancel();
    RuntimeServicesScope.instance.lifecycle.unregisterObserver(this);
    super.dispose();
  }

  @override
  void onBackground() {
    _liveUpdateTimer?.cancel();
    _liveUpdateTimer = null;
    RuntimeLogger.i('Map rendering paused', screen: 'cop_map');
  }

  @override
  void onForeground() {
    _startLiveUpdates();
    _refreshActiveLayers();
    RuntimeLogger.i('Map rendering resumed', screen: 'cop_map');
  }

  Future<void> _refreshActiveLayers() async {
    if (mapController == null) return;
    final notifier = ref.read(mapLayerNotifierProvider.notifier);
    final activeLayers = ref.read(mapLayerNotifierProvider).activeLayerIds;
    for (var layerId in activeLayers) {
      await notifier.refreshLayer(layerId, mapController);
    }
  }

  Future<void> _updateIncidentMarkers() async {
    final feedState = ref.read(incidentFeedProvider).asData?.value;
    if (feedState == null || mapController == null) return;

    final geoJson = incidentsToGeoJson(feedState.incidents);
    try {
      if (_attachedSources.contains('feed_incidents')) {
        await mapController!.removeLayer('feed_incidents');
        await mapController!.removeSource('feed_incidents');
        _attachedSources.remove('feed_incidents');
      }
      await mapController!.addSource(
        'feed_incidents',
        GeojsonSourceProperties(data: geoJson),
      );
      await mapController!.addLayer(
        'feed_incidents',
        'feed_incidents',
        CircleLayerProperties(
          circleRadius: 6,
          circleColor: ['get', 'color'],
          circleStrokeWidth: 2,
          circleStrokeColor: '#ffffff',
        ),
      );
      _attachedSources.add('feed_incidents');
    } catch (e) {
      RuntimeLogger.w('Failed to update incident markers: $e', screen: 'cop_map');
    }
  }

  Future<void> _goToMyLocation() async {
    if (mapController == null) return;
    final loc = _lastUserLocation;
    if (loc != null) {
      await mapController!.animateCamera(
        CameraUpdate.newLatLngZoom(loc, 14.0),
      );
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Lokasi belum tersedia. Pastikan GPS aktif.')),
        );
      }
    }
  }

  void _onMapCreated(MapLibreMapController controller) async {
    mapController = controller;

    controller.onFeatureTapped.add((point, coordinates, id, layerId, annotation) {
      _handleFeatureTapped(id, coordinates);
    });

    await _updateIncidentMarkers();

    try {
      final datasource = ref.read(mapLayerDatasourceProvider);
      final config = await datasource.fetchMapConfig();
      final groups = config['groups'] as List<dynamic>?;
      if (groups == null) return;
      for (var group in groups) {
        final layers = group['layers'] as List<dynamic>?;
        if (layers == null) continue;
        for (var layer in layers) {
          if (layer['default_visible'] == true) {
            ref.read(mapLayerNotifierProvider.notifier).toggleLayer(layer['id'], controller);
          }
        }
      }
    } catch (e) {
      RuntimeLogger.w('Failed to auto-load map layers: $e', screen: 'cop_map');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(DioExceptionMapper.toUserMessage(e))),
        );
      }
    }
  }

  void _handleFeatureTapped(String id, LatLng coordinates) async {
    if (mapController == null) return;

    await mapController!.animateCamera(
      CameraUpdate.newLatLngZoom(coordinates, 14.0),
    );

    if (!mounted) return;

    final feedState = ref.read(incidentFeedProvider).asData?.value;
    if (feedState == null) return;

    final incident = feedState.incidents.where((i) => i.id == id).firstOrNull;
    if (incident == null || !mounted) return;

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) => SafeArea(
        child: _buildIncidentSummary(incident),
      ),
    );
  }

  Widget _buildIncidentSummary(IncidentEntity inc) {
    final needs = inc.needsNumeric.entries.toList();
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
      decoration: BoxDecoration(
        color: Theme.of(context).scaffoldBackgroundColor,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.1),
            blurRadius: 10,
            spreadRadius: 2,
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(bottom: 16),
              decoration: BoxDecoration(
                color: Colors.grey.shade400,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          Text(inc.title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 12),
          _summaryRow(Icons.category, 'Jenis', inc.category),
          if (inc.kode != null && inc.kode!.isNotEmpty)
            _summaryRow(Icons.tag, 'Kode', inc.kode!),
          _summaryRow(Icons.info_outline, 'Status', inc.status),
          _summaryRow(Icons.location_on, 'Lokasi', inc.district),
          _summaryRow(Icons.access_time, 'Mulai', _formatDate(inc.occurredAt)),
          if (inc.korbanSummary > 0)
            _summaryRow(Icons.people, 'Korban', '$inc.korbanSummary jiwa terdampak'),
          if (needs.isNotEmpty) ...[
            const Divider(height: 20),
            const Text('Gap Kebutuhan:', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
            const SizedBox(height: 4),
            ...needs.map((e) => Padding(
              padding: const EdgeInsets.symmetric(vertical: 2, horizontal: 4),
              child: Row(
                children: [
                  Expanded(child: Text(e.key, style: const TextStyle(fontSize: 13))),
                  Text('${e.value}', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                ],
              ),
            )),
          ],
        ],
      ),
    );
  }

  Widget _summaryRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 16, color: Colors.grey.shade600),
          const SizedBox(width: 8),
          SizedBox(
            width: 60,
            child: Text(label, style: TextStyle(fontSize: 13, color: Colors.grey.shade600)),
          ),
          Expanded(child: Text(value, style: const TextStyle(fontSize: 14))),
        ],
      ),
    );
  }

  String _formatDate(DateTime dt) {
    final months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}, ${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }

  @override
  Widget build(BuildContext context) {
    ref.listen(incidentFeedProvider, (previous, next) {
      if (next.asData?.value != null && mapController != null) {
        _updateIncidentMarkers();
      }
    });

    ref.listen<MapLayerState>(mapLayerNotifierProvider, (previous, current) {
      if (current.error != null && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(current.error!),
            backgroundColor: Colors.red.shade700,
            duration: const Duration(seconds: 4),
          ),
        );
      }
    });

    return Scaffold(
      body: Stack(
        children: [
          MapLibreMap(
            onMapCreated: _onMapCreated,
            initialCameraPosition: const CameraPosition(
              target: LatLng(-7.250000, 110.400000),
              zoom: 9.0,
            ),
            styleString: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
            myLocationEnabled: true,
            myLocationRenderMode: MyLocationRenderMode.compass,
            trackCameraPosition: true,
            onUserLocationUpdated: (location) {
              _lastUserLocation = location.position;
            },
          ),

          Positioned(
            top: 48,
            right: 16,
            child: Column(
              children: [
                FloatingActionButton.small(
                  heroTag: 'layer_selector',
                  onPressed: () {
                    final currentActive = Set<String>.from(
                      ref.read(mapLayerNotifierProvider).activeLayerIds,
                    );
                    showModalBottomSheet(
                      context: context,
                      backgroundColor: Colors.transparent,
                      builder: (ctx) => LayerControlBottomSheet(
                        onLayersChanged: (activeLayers) {
                          final newActive = activeLayers.toSet();
                          final toAdd = newActive.difference(currentActive);
                          final toRemove = currentActive.difference(newActive);
                          for (var layerId in toRemove) {
                            ref.read(mapLayerNotifierProvider.notifier).toggleLayer(layerId, mapController);
                          }
                          for (var layerId in toAdd) {
                            ref.read(mapLayerNotifierProvider.notifier).toggleLayer(layerId, mapController);
                          }
                          currentActive
                            ..clear()
                            ..addAll(newActive);
                        },
                      ),
                    );
                  },
                  child: const Icon(Icons.layers),
                ),
              ],
            ),
          ),

          Positioned(
            right: 16,
            bottom: MediaQuery.of(context).padding.bottom + 80,
            child: FloatingActionButton(
              heroTag: 'my_location',
              onPressed: _goToMyLocation,
              child: const Icon(Icons.my_location),
            ),
          ),
        ],
      ),
    );
  }
}
