import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/components/sdui_scene.dart';
import 'package:nurisk_mobile/core/sdui/sdui_registry_initializer.dart';

void main() {
  setUpAll(() {
    SduiRegistryInitializer.initialize();
  });

  group('SDUI Scene Primitive & Layout', () {
    testWidgets('Scene renders map layer and overlay panels correctly', (WidgetTester tester) async {
      final jsonPayload = {
        'type': 'Scene',
        'props': {
          'scene': {
            'camera': {
              'center_lat': -7.595,
              'center_lng': 110.952,
              'zoom': 12.0
            },
            'layers': [
              {
                'id': 'incident_layer',
                'visible': true,
                'primitives': [
                  {
                    'type': 'Marker',
                    'props': {
                      'latitude': -7.595,
                      'longitude': 110.952,
                      'title': 'Test Incident',
                      'color': 'danger'
                    }
                  }
                ]
              }
            ],
            'panels': {
              'top_bar': {
                'type': 'Container',
                'props': {'padding': [16, 16, 16, 16]},
                'children': [
                  {'type': 'Text', 'props': {'text': 'Top Bar Overlay', 'style': 'headline'}}
                ]
              },
              'bottom_sheet': {
                'type': 'BottomSheet',
                'children': [
                  {'type': 'Text', 'props': {'text': 'Bottom Detail Text'}}
                ]
              }
            }
          }
        }
      };

      final node = SduiNode.fromJson(jsonPayload);
      final widget = ProviderScope(
        child: MaterialApp(
          home: Scaffold(
            body: SduiScene(node: node),
          ),
        ),
      );

      await tester.pumpWidget(widget);

      // Verify Map Center Text
      expect(find.text('Map Center: -7.595, 110.952 (Zoom: 12.0)'), findsOneWidget);
      // Verify Layer Marker Text
      expect(find.text('Test Incident'), findsOneWidget);
      
      // Verify Top Bar
      expect(find.text('Top Bar Overlay'), findsOneWidget);

      // Verify Bottom Sheet
      expect(find.text('Bottom Detail Text'), findsOneWidget);
    });
  });
}
