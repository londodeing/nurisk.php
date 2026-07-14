import 'package:nurisk_mobile/core/runtime/state/runtime_node_state.dart';

class SduiNode {
  final String id;
  final String type;
  final String? key;
  final RuntimeNodeState state;
  final int? version;
  final bool dirty;
  final Map<String, dynamic> props;
  final Map<String, dynamic> actions;
  final List<SduiNode>? children;

  const SduiNode({
    required this.id,
    required this.type,
    this.key,
    this.state = RuntimeNodeState.empty,
    this.version,
    this.dirty = false,
    this.props = const {},
    this.actions = const {},
    this.children,
  });

  bool get visible => state.visible;
  bool get enabled => state.enabled;

  SduiNode copyWith({
    String? id,
    String? type,
    String? key,
    RuntimeNodeState? state,
    int? version,
    bool? dirty,
    Map<String, dynamic>? props,
    Map<String, dynamic>? actions,
    List<SduiNode>? children,
  }) {
    return SduiNode(
      id: id ?? this.id,
      type: type ?? this.type,
      key: key ?? this.key,
      state: state ?? this.state,
      version: version ?? this.version,
      dirty: dirty ?? this.dirty,
      props: props ?? this.props,
      actions: actions ?? this.actions,
      children: children ?? this.children,
    );
  }

  SduiNode patchProps(String targetId, Map<String, dynamic> newProps) {
    if (id == targetId) {
      return copyWith(props: {...props, ...newProps});
    }
    if (children == null || children!.isEmpty) return this;
    var changed = false;
    final patched = children!.map((child) {
      final result = child.patchProps(targetId, newProps);
      if (result != child) changed = true;
      return result;
    }).toList();
    return changed ? copyWith(children: patched) : this;
  }

  factory SduiNode.fromJson(Map<String, dynamic> json) {
    if (json['type'] == null) {
      throw const FormatException('SDUI Node requires a "type" field.');
    }

    final type = json['type'] as String;
    final id = json['id'] as String? ?? 'auto-${type.toLowerCase()}-${DateTime.now().microsecondsSinceEpoch}';

    // Parse state: prefer 'state' object, fallback to legacy root-level fields
    RuntimeNodeState state;
    if (json['state'] != null && json['state'] is Map) {
      state = RuntimeNodeState.fromJson(json['state'] as Map<String, dynamic>);
    } else {
      state = RuntimeNodeState(
        visible: json['visible'] as bool? ?? true,
        enabled: json['enabled'] as bool? ?? true,
      );
    }

    final key = json['key'] as String?;
    final version = json['version'] as int?;
    final dirty = json['dirty'] as bool? ?? false;

    final props = json['props'] as Map<String, dynamic>? ?? {};
    final actions = json['actions'] as Map<String, dynamic>? ?? {};

    List<SduiNode>? children;
    if (json['children'] != null && json['children'] is List) {
      children = (json['children'] as List)
          .map((child) => SduiNode.fromJson(child as Map<String, dynamic>))
          .toList();
    }

    return SduiNode(
      id: id,
      type: type,
      key: key,
      state: state,
      version: version,
      dirty: dirty,
      props: props,
      actions: actions,
      children: children,
    );
  }
}
