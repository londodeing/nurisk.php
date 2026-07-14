import 'package:flutter_test/flutter_test.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';

void main() {
  group('SDUI Recursive Parser', () {
    test('Can parse 50-level deep nested JSON payload without stack overflow', () {
      final jsonPayload = _generateDeepPayload(50);
      
      final node = SduiNode.fromJson(jsonPayload);
      
      expect(node.type, 'Container');
      expect(node.children?.length, 1);
      
      // Verify depth 50
      SduiNode? current = node;
      int depth = 1;
      while (current?.children != null && current!.children!.isNotEmpty) {
        current = current.children!.first;
        depth++;
      }
      
      expect(depth, 50);
      expect(current?.type, 'Text'); // The innermost node should be a Text
    });
  });
}

Map<String, dynamic> _generateDeepPayload(int maxDepth, {int currentDepth = 1}) {
  if (currentDepth == maxDepth) {
    return <String, dynamic>{
      'type': 'Text',
      'props': <String, dynamic>{'text': 'Deepest Node'}
    };
  }
  
  return <String, dynamic>{
    'type': currentDepth % 2 == 0 ? 'Row' : 'Container',
    'props': <String, dynamic>{},
    'children': [
      _generateDeepPayload(maxDepth, currentDepth: currentDepth + 1)
    ]
  };
}
