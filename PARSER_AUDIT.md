# PARSER AUDIT REPORT

This audit covers [SduiNode.fromJson](file:///home/londo/nurisk/mobile/app/lib/core/sdui/sdui_node.dart) and deserializer logic.

## 1. Type Casting Vulnerabilities in `SduiNode.fromJson`
- **Dynamic List Mapping**:
  ```dart
  children = (json['children'] as List)
      .map((child) => SduiNode.fromJson(child as Map<String, dynamic>))
      .toList();
  ```
  If `children` contains any item that is not a `Map<String, dynamic>` (e.g. `null` or a primitive value), `child as Map<String, dynamic>` throws a `TypeError` (e.g. `type 'String' is not a subtype of type 'Map<String, dynamic>'`).
- **Map Properties Casting**:
  ```dart
  final props = json['props'] as Map<String, dynamic>? ?? {};
  final actions = json['actions'] as Map<String, dynamic>? ?? {};
  ```
  If `props` or `actions` are defined as empty arrays `[]` in PHP (which PHP encodes as `[]` in JSON instead of `{}` when empty), the client will throw a `TypeError` trying to cast a `List` to `Map<String, dynamic>`.

## 2. Model Deserializer Vulnerabilities (`ConfigModel.fromJson` & `AccountHomeData.fromJson`)
- **`ConfigModel.fromJson`**:
  ```dart
  widgets: List<dynamic>.from(json['nodes'] ?? []),
  featureFlags: Map<String, bool>.from(json['feature_flags'] ?? {}),
  ```
  If `nodes` is not a `List`, or `feature_flags` contains non-bool values, this will throw a `TypeError` at runtime.
- **`AccountHomeData.fromJson`**:
  ```dart
  nodes: rawNodes.map((e) => SduiNode.fromJson(e as Map<String, dynamic>)).toList(),
  ```
  Forces type cast `e as Map<String, dynamic>`. If the backend returns list items of other types, a `TypeError` is raised.

## 3. Component Property Parsing Vulnerabilities
Many SDUI components read properties from the `props` map and cast them directly without boundary checks:
- **`SduiContainer`**:
  ```dart
  final paddingArr = node.props['padding'] as List<dynamic>? ?? [0, 0, 0, 0];
  final padding = EdgeInsets.fromLTRB(
    (paddingArr[0] as num?)?.toDouble() ?? 0.0,
    (paddingArr[1] as num?)?.toDouble() ?? 0.0,
    (paddingArr[2] as num?)?.toDouble() ?? 0.0,
    (paddingArr[3] as num?)?.toDouble() ?? 0.0,
  );
  ```
  If `paddingArr` contains fewer than 4 elements, accessing `paddingArr[2]` or `paddingArr[3]` will throw a `RangeError` (Index out of range), causing a rendering exception (which can result in a WSOD if not caught by a boundary).
- **`SduiGrid`**:
  ```dart
  final int columns = (layout == 'responsiveGrid' || layout == 'auto')
      ? (screenWidth / 120).floor().clamp(2, 4)
      : (node.props['columns'] as int? ?? 3);
  ```
  If `node.props['columns']` is passed as a `double` or a `String` from the BFF, the direct cast `as int?` will throw a `TypeError`.
