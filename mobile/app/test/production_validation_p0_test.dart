import 'dart:io';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:nurisk_mobile/core/sdui/sdui_screen.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_registry_initializer.dart';

void main() {
  setUpAll(() {
    SduiRegistryInitializer.initialize();
  });

  int countJsonNodes(Map<String, dynamic> json) {
    int count = 1; // Current node
    if (json.containsKey('children') && json['children'] is List) {
      for (var child in json['children']) {
        count += countJsonNodes(child as Map<String, dynamic>);
      }
    }
    return count;
  }

  int countParsedNodes(SduiNode node) {
    int count = 1;
    if (node.children != null) {
      for (var child in node.children!) {
        count += countParsedNodes(child);
      }
    }
    return count;
  }

  testWidgets('P0-004: Node Count Verification & Widget Tree Dump', (WidgetTester tester) async {
    print('--- Sprint P0 Validation ---');
    
    // Load JSON Dump
    final file = File('test/test_data/runtime_dump.json');
    if (!file.existsSync()) {
      fail('runtime_dump.json not found');
    }
    
    final rawJson = file.readAsStringSync();
    final jsonMap = jsonDecode(rawJson);
    
    // P0-002: JSON Dump Node Count
    final jsonNodeCount = countJsonNodes(jsonMap);
    print('[P0-002] Backend JSON Node Count: $jsonNodeCount');

    // Parse to SDUI Node
    final rootNode = SduiNode.fromJson(jsonMap);
    
    // P0-004: Parser Node Count Verification
    final parsedNodeCount = countParsedNodes(rootNode);
    print('[P0-004] Parser Node Count: $parsedNodeCount');
    
    expect(parsedNodeCount, jsonNodeCount, reason: 'Parser dropped some nodes!');

    // P0-005: Unknown Component Check
    // (This happens during widget rendering if a component is missing in the registry)

    // Render in Flutter
    await tester.pumpWidget(MaterialApp(
      home: SduiScreen(
        title: 'Account Workspace (Runtime)',
        rootNode: rootNode,
      ),
    ));

    await tester.pumpAndSettle();

    // P0-003: Flutter Inspector Widget Tree Dump
    print('\n[P0-003] WIDGET TREE DUMP (Extract):');
    print(tester.allWidgets.take(20).join('\n'));
    
    // Check for "Unknown Component" texts
    final unknownFinder = find.textContaining('Unknown Component');
    if (tester.any(unknownFinder)) {
      print('\n[P0-005] ❌ UNKNOWN COMPONENTS FOUND:');
      final elements = tester.elementList(unknownFinder);
      for (var el in elements) {
        print('  - ${el.widget.toString()}');
      }
      fail('Unknown components found in Runtime output');
    } else {
      print('\n[P0-005] ✅ No unknown components found. Registry is fully compatible.');
    }
  });
}
