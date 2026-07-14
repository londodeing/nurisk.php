import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:nurisk_mobile/core/sdui/sdui_screen.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_registry_initializer.dart';
import 'dart:convert';

void main() {
  setUpAll(() {
    SduiRegistryInitializer.initialize();
  });

  Widget buildTestableSduiScreen(SduiNode rootNode) {
    return MaterialApp(
      home: SduiScreen(
        title: 'SDUI Golden Test',
        rootNode: rootNode,
      ),
    );
  }

  group('3-Stage SDUI Verification Pipeline', () {
    final roles = ['Guest', 'TRC', 'PCNU', 'PWNU', 'SuperAdmin', 'Executive', 'Relawan'];

    for (var role in roles) {
      testWidgets('Stage 1 & 2 & 3: JSON -> Node -> Pixel Rendering ($role)', (WidgetTester tester) async {
        // Slightly modify payload per role for rendering diffs
        final rawJson = '''
        {
          "schema_version": "1.0",
          "screen": "Dashboard",
          "layout": "vertical",
          "nodes": [
            {
              "id": "container_1",
              "type": "Container",
              "props": { "background": "primary" },
              "children": [
                {
                  "id": "row_1",
                  "type": "Row",
                  "children": [
                    { "id": "icon_1", "type": "Icon", "props": { "name": "person", "foreground": "white" } },
                    { "id": "text_1", "type": "Text", "props": { "text": "Role: $role", "foreground": "white", "style": "headline" } }
                  ]
                }
              ]
            },
            {
              "id": "card_1",
              "type": "Card",
              "children": [
                {
                  "id": "row_2",
                  "type": "Row",
                  "children": [
                    { "id": "icon_2", "type": "Icon", "props": { "name": "cloud", "foreground": "info" } },
                    { "id": "text_2", "type": "Text", "props": { "text": "Cerah Berawan" } }
                  ]
                }
              ]
            }
          ]
        }
        ''';

        // STAGE 1: JSON Snapshot Test (BFF Contract Simulation)
        final parsedJson = jsonDecode(rawJson);
        expect(parsedJson['schema_version'], '1.0');
        expect(parsedJson['nodes'], isA<List>());

        // STAGE 2: Primitive Snapshot Test (Node Transformation)
        final nodesData = parsedJson['nodes'] as List<dynamic>;
        final nodes = nodesData.map((e) => SduiNode.fromJson(e)).toList();
        
        expect(nodes.length, 2);
        expect(nodes[0].type, 'Container');
        expect(nodes[1].type, 'Card');
        expect(nodes[0].children![0].type, 'Row');

        // STAGE 3: Pixel Golden Test (Flutter Rendering)
        // Wrap nodes in a root Column node to satisfy rootNode requirement
        final rootNode = SduiNode(
          id: 'root',
          type: 'Column',
          children: nodes,
        );

        await tester.pumpWidget(buildTestableSduiScreen(rootNode));
        
        expect(find.text('Role: $role'), findsOneWidget);
        expect(find.text('Cerah Berawan'), findsOneWidget);
        expect(find.byIcon(Icons.person), findsOneWidget);
        expect(find.byIcon(Icons.cloud), findsOneWidget);

        await expectLater(
          find.byType(MaterialApp),
          matchesGoldenFile('goldens/sdui_dashboard_${role.toLowerCase()}.png'),
        );
      });
    }
  });
}
