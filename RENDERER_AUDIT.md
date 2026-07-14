# RENDERER AUDIT REPORT

This audit covers [SduiRenderer](file:///home/londo/nurisk/mobile/app/lib/core/sdui/sdui_renderer.dart) and its components' build methods.

## 1. Recursion Vulnerabilities
- **Direct Child Recursion**:
  `SduiContainer`, `SduiRow`, and `SduiColumn` map their children directly back to `SduiRenderer`. If the backend returns a JSON payload with a nested reference loop (e.g. Container containing a Row containing the same Container), the client will enter an infinite recursion loop during `build()` and crash with a stack overflow.
- **`SduiRemoteNode` Dynamic Recursion**:
  ```dart
  final asyncNode = ref.watch(remoteNodeProvider(source));
  return asyncNode.when(
    data: (remoteNode) => SduiRenderer(node: remoteNode),
    ...
  ```
  If `source` points to an endpoint that returns a `RemoteNode` referencing the same (or another resolving) URL, the app will trigger infinite recursive network calls and Widget rebuilds, leading to a freeze or out-of-memory crash.

## 2. Layout Overflows and Constraints
- **Horizontal Flex Overflow (`SduiRow`)**:
  `SduiRow` places child components in a horizontal `Row` without wrapping them in `Flexible` or `Expanded` by default:
  ```dart
  final children = node.children?.map((childNode) {
    Widget w = SduiRenderer(node: childNode);
    ...
    return w;
  }).toList() ?? [];
  return Row(crossAxisAlignment: cross, children: children);
  ```
  If the sum of widths of these child nodes exceeds the physical screen width, a `RenderFlex` horizontal overflow exception is thrown (yellow/black tape in debug, clipped in release).
- **Vertical Scrolling**:
  Mitigated by [SduiScreen](file:///home/londo/nurisk/mobile/app/lib/core/sdui/sdui_screen.dart)'s root `SingleChildScrollView` layout, allowing unlimited vertical content.

## 3. Null Widget Returns
- All registered builders in `SduiRegistryInitializer` return a concrete `Widget` instance.
- In components like `SduiContainer` or `SduiRemoteNode`, empty states safely return `SizedBox.shrink()` or empty widgets instead of `null`, preventing primitive layout crashes.
