import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_renderer.dart';

class SduiScene extends SduiComponent {
  const SduiScene({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final scene = node.props['scene'] as Map<String, dynamic>?;
    if (scene == null) return const Center(child: Text('Invalid Scene Data'));

    final camera = scene['camera'] as Map<String, dynamic>?;
    final layers = scene['layers'] as List<dynamic>? ?? [];
    final panels = scene['panels'] as Map<String, dynamic>? ?? {};

    // Base Map Node
    final mapNode = SduiNode(
      id: 'map_base_layer',
      type: 'Map',
      props: {
        'center_lat': camera?['center_lat'] ?? -7.595,
        'center_lng': camera?['center_lng'] ?? 110.952,
        'zoom': camera?['zoom'] ?? 12.0,
        'bearing': camera?['bearing'] ?? 0.0,
        'tilt': camera?['tilt'] ?? 0.0,
        'layers': layers
      },
      children: [], // Flatten primitives into Map or handle natively in SduiMap
    );

    // Build map widget
    final mapWidget = SduiRenderer(node: mapNode);

    // Build overlay panels
    final topBarNode = _buildPanelNode(panels['top_bar']);
    final rightOverlayNode = _buildPanelNode(panels['overlay_right']);
    final bottomSheetNode = _buildPanelNode(panels['bottom_sheet']);

    return Stack(
      children: [
        // 1. Base Map Layer
        Positioned.fill(child: mapWidget),

        // 2. Overlays in a SafeArea Column
        Positioned(
          top: 0,
          left: 0,
          right: 0,
          child: SafeArea(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (topBarNode != null) SduiRenderer(node: topBarNode),
                if (rightOverlayNode != null) ...[
                  const SizedBox(height: 8),
                  Align(
                    alignment: Alignment.topRight,
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 380),
                      child: SduiRenderer(node: rightOverlayNode),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),

        // 4. Bottom Sheet (Details)
        if (bottomSheetNode != null)
          Positioned(
            left: 16,
            right: 16,
            bottom: 24,
            child: SduiRenderer(node: bottomSheetNode),
          ),
      ],
    );
  }

  SduiNode? _buildPanelNode(dynamic panelData) {
    if (panelData == null) return null;
    return SduiNode.fromJson(panelData as Map<String, dynamic>);
  }
}
