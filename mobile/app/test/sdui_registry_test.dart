import 'package:flutter_test/flutter_test.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_registry.dart';
import 'package:nurisk_mobile/core/sdui/sdui_registry_initializer.dart';
import 'package:nurisk_mobile/core/sdui/sdui_renderer.dart';
import 'package:nurisk_mobile/core/sdui/components/sdui_unknown_component.dart';
import 'package:flutter/material.dart';

import 'package:flutter_riverpod/flutter_riverpod.dart';

void main() {
  setUpAll(() {
    SduiRegistryInitializer.initialize();
  });

  group('SDUI Registry Coverage & Fallback', () {
    // Types that need a Flex parent (Expanded, Flexible)
    final flexTypes = {'Expanded', 'Flexible'};

    final primitives = [
      'Container', 'Row', 'Column', 'Text', 'Icon', 'Card',
      'ListView', 'Badge',
      'Expanded', 'Flexible', 'SizedBox', 'AspectRatio', 'Divider',
      'Grid', 'Timeline', 'BottomSheet', 'Chart', 'Checkbox',
      'Dialog', 'Dropdown', 'FormField', 'Map', 'Scene', 'Switch', 'Tabs',
    ];

    for (final type in primitives) {
      testWidgets('Registry can resolve and build primitive: $type', (WidgetTester tester) async {
        final registry = SduiRegistry.instance;
        final builder = registry.getBuilder(type);

        expect(builder, isNotNull, reason: 'Builder for $type is not registered');

        final node = SduiNode.fromJson({'type': type});
        final component = builder!(node);

        // Expanded and Flexible must be wrapped in a Row to satisfy Flex parent constraint
        Widget body = component;
        if (flexTypes.contains(type)) {
          body = Row(children: [component]);
        }

        await tester.pumpWidget(ProviderScope(
          child: MaterialApp(
            home: Scaffold(body: body),
          ),
        ));

        expect(tester.takeException(), isNull);
      });
    }

    testWidgets('Renderer produces SduiUnknownComponent for unknown types', (WidgetTester tester) async {
      final registry = SduiRegistry.instance;

      // Unknown type has no registered builder
      final builder = registry.getBuilder('SuperWeirdCard');
      expect(builder, isNull);

      // SduiRenderer wraps unknown types in SduiUnknownComponent
      final node = SduiNode.fromJson({'type': 'SuperWeirdCard'});
      await tester.pumpWidget(ProviderScope(
        child: MaterialApp(
          home: Scaffold(body: SduiRenderer(node: node)),
        ),
      ));

      expect(tester.takeException(), isNull);
      expect(find.text('Unsupported Component: SuperWeirdCard'), findsOneWidget);
    });
  });
}
