import 'package:flutter_test/flutter_test.dart';
import 'package:nurisk_mobile/core/sdui/sdui_diff_engine.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';

void main() {
  group('SDUI Diff Engine', () {
    test('Replace operation mutates the correct nested value', () {
      final Map<String, dynamic> original = {
        'scene': {
          'layers': [
            {
              'primitives': [
                {
                  'type': 'Marker',
                  'props': { 'latitude': -7.50, 'longitude': 110.90 }
                }
              ]
            }
          ]
        }
      };

      final patches = <Map<String, dynamic>>[
        {
          'op': 'replace',
          'path': '/scene/layers/0/primitives/0/props/latitude',
          'value': -7.55
        }
      ];

      final result = SduiDiffEngine.applyPatch(original, patches);
      
      expect(result['scene']['layers'][0]['primitives'][0]['props']['latitude'], -7.55);
      // Original should remain unmutated (deep copy check)
      expect(original['scene']['layers'][0]['primitives'][0]['props']['latitude'], -7.50);
    });

    test('Add operation appends to array', () {
      final Map<String, dynamic> original = {
        'scene': {
          'layers': [
            {
              'primitives': <dynamic>[]
            }
          ]
        }
      };

      final patches = <Map<String, dynamic>>[
        {
          'op': 'add',
          'path': '/scene/layers/0/primitives/-',
          'value': { 'type': 'Marker', 'props': { 'id': 'new_marker' } }
        }
      ];

      final result = SduiDiffEngine.applyPatch(original, patches);
      expect((result['scene']['layers'][0]['primitives'] as List).length, 1);
      expect(result['scene']['layers'][0]['primitives'][0]['props']['id'], 'new_marker');
    });

    test('Integration: Patching and parsing into SduiNode', () {
      final Map<String, dynamic> original = {
        'type': 'Scene',
        'props': {
          'scene': {
            'layers': [
              {
                'id': 'incident_layer',
                'primitives': [
                  { 'type': 'Marker', 'props': { 'title': 'Old Title' } }
                ]
              }
            ]
          }
        }
      };

      final patches = <Map<String, dynamic>>[
        { 'op': 'replace', 'path': '/props/scene/layers/0/primitives/0/props/title', 'value': 'New Title' }
      ];

      final result = SduiDiffEngine.applyPatch(original, patches);
      final node = SduiNode.fromJson(result);

      final layers = node.props['scene']['layers'] as List<dynamic>;
      expect(layers[0]['primitives'][0]['props']['title'], 'New Title');
    });
  });
}
