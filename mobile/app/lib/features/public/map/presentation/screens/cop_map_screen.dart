import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:maplibre_gl/maplibre_gl.dart';
import 'package:nurisk_mobile/core/diagnostics/runtime_logger.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import 'package:nurisk_mobile/core/runtime/app_lifecycle_service.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';

import '../providers/selected_operational_object_provider.dart';
import '../providers/operational_filter_provider.dart';
import '../notifiers/map_layer_notifier.dart';
import '../../data/datasources/map_layer_datasource.dart';
import '../widgets/operational_bottom_sheet.dart';
import 'package:nurisk_mobile/features/map/presentation/widgets/layer_control_bottom_sheet.dart';
import '../widgets/filter_control_bottom_sheet.dart';
import 'dart:async';

class CopMapScreen extends ConsumerStatefulWidget {
  const CopMapScreen({super.key});

  @override
  ConsumerState<CopMapScreen> createState() => _CopMapScreenState();
}

class _CopMapScreenState extends ConsumerState<CopMapScreen> with AppLifecycleObserver {
  MapLibreMapController? mapController;
  Timer? _liveUpdateTimer;

  @override
  void initState() {
    super.initState();
    RuntimeServicesScope.instance.lifecycle.registerObserver(this);
    _startLiveUpdates();
  }

  void _startLiveUpdates() {
    _liveUpdateTimer = Timer.periodic(const Duration(seconds: 30), (timer) {
      if (mapController != null) {
        final activeLayers = ref.read(mapLayerNotifierProvider).activeLayerIds;
        for (var layerId in activeLayers) {
          ref.read(mapLayerNotifierProvider.notifier).toggleLayer(layerId, mapController);
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
    final activeLayers = ref.read(mapLayerNotifierProvider).activeLayerIds;
    for (var layerId in activeLayers) {
      ref.read(mapLayerNotifierProvider.notifier).toggleLayer(layerId, mapController);
    }
  }

  void _onMapCreated(MapLibreMapController controller) async {
    mapController = controller;

    controller.onFeatureTapped.add((point, coordinates, id, layerId, annotation) {
      _handleFeatureTapped(id, coordinates);
    });

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

  void _handleFeatureTapped(dynamic id, LatLng coordinates) async {
    if (mapController == null) return;

    mapController!.animateCamera(
      CameraUpdate.newLatLngZoom(coordinates, 14.0),
    );
  }

  @override
  Widget build(BuildContext context) {
    ref.listen(operationalFilterProvider, (previous, next) {
      if (mapController != null) {
        // Apply MapLibre micro-filters based on next.status, next.severity etc
      }
    });

    final selectedObject = ref.watch(selectedOperationalObjectProvider);

    return Scaffold(
      body: Stack(
        children: [
          MapLibreMap(
            onMapCreated: _onMapCreated,
            initialCameraPosition: const CameraPosition(
              target: LatLng(-6.200000, 106.816666),
              zoom: 12.0,
            ),
            styleString: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
            myLocationEnabled: true,
            myLocationRenderMode: MyLocationRenderMode.compass,
            trackCameraPosition: true,
          ),

          Positioned(
            top: 48,
            right: 16,
            child: Column(
              children: [
                FloatingActionButton.small(
                  heroTag: 'layer_selector',
                  onPressed: () {
                    showModalBottomSheet(
                      context: context,
                      backgroundColor: Colors.transparent,
                      builder: (ctx) => LayerControlBottomSheet(
                        onLayersChanged: (activeLayers) {
                          for (var layerId in activeLayers) {
                            ref.read(mapLayerNotifierProvider.notifier).toggleLayer(layerId, mapController);
                          }
                        },
                      ),
                    );
                  },
                  child: const Icon(Icons.layers),
                ),
                const SizedBox(height: 8),
                FloatingActionButton.small(
                  heroTag: 'filter',
                  onPressed: () {
                    showModalBottomSheet(
                      context: context,
                      backgroundColor: Colors.transparent,
                      builder: (ctx) => const FilterControlBottomSheet(),
                    );
                  },
                  child: const Icon(Icons.filter_list),
                ),
              ],
            ),
          ),

          if (selectedObject != null)
            const OperationalBottomSheet()
          else
            _buildPlaceholderBottomSheet(),
        ],
      ),
    );
  }

  Widget _buildPlaceholderBottomSheet() {
    return DraggableScrollableSheet(
      initialChildSize: 0.15,
      minChildSize: 0.1,
      maxChildSize: 0.9,
      builder: (BuildContext context, ScrollController scrollController) {
        return Container(
          decoration: BoxDecoration(
            color: Theme.of(context).scaffoldBackgroundColor,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.1),
                blurRadius: 10,
                spreadRadius: 2,
              )
            ],
          ),
          child: CustomScrollView(
            controller: scrollController,
            slivers: [
              SliverToBoxAdapter(
                child: Center(
                  child: Container(
                    margin: const EdgeInsets.symmetric(vertical: 12),
                    width: 40,
                    height: 5,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade400,
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
              ),
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16.0),
                  child: Text(
                    'Situational Awareness',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
