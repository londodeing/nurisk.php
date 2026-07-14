import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/components/sdui_map.dart';
import 'package:nurisk_mobile/core/sdui/sdui_registry_initializer.dart';

void main() {
  setUpAll(() {
    SduiRegistryInitializer.initialize();
  });

  group('SDUI Map Primitive & Children Rendering', () {
    testWidgets('Map renders center info and child markers semantically', (WidgetTester tester) async {
      final jsonPayload = {
        'type': 'Map',
        'props': {
          'center_lat': -7.595,
          'center_lng': 110.952,
          'zoom': 12.0
        },
        'children': [
          {
            'type': 'Marker',
            'props': {
              'latitude': -7.595,
              'longitude': 110.952,
              'title': 'Posko Utama',
              'color': 'danger'
            }
          },
          {
            'type': 'Marker',
            'props': {
              'latitude': -7.600,
              'longitude': 110.960,
              'title': 'Banjir Luapan',
              'color': 'warning'
            }
          }
        ]
      };

      final node = SduiNode.fromJson(jsonPayload);
      final widget = ProviderScope(
        child: MaterialApp(
          home: Scaffold(
            body: SduiMap(node: node),
          ),
        ),
      );

      await tester.pumpWidget(widget);

      // Verify that the Map component header and markers render in test mode
      expect(find.text('Map Center: -7.595, 110.952 (Zoom: 12.0)'), findsOneWidget);
      expect(find.text('Posko Utama'), findsOneWidget);
      expect(find.text('Banjir Luapan'), findsOneWidget);
    });
  });
}
