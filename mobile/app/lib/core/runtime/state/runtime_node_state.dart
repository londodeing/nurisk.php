class RuntimeNodeState {
  final bool enabled;
  final bool visible;
  final bool loading;
  final bool readOnly;
  final bool selected;
  final bool expanded;

  const RuntimeNodeState({
    this.enabled = true,
    this.visible = true,
    this.loading = false,
    this.readOnly = false,
    this.selected = false,
    this.expanded = false,
  });

  static const empty = RuntimeNodeState();

  factory RuntimeNodeState.fromJson(Map<String, dynamic>? json) {
    if (json == null) return const RuntimeNodeState();
    return RuntimeNodeState(
      enabled: json['enabled'] as bool? ?? true,
      visible: json['visible'] as bool? ?? true,
      loading: json['loading'] as bool? ?? false,
      readOnly: json['readonly'] as bool? ?? false,
      selected: json['selected'] as bool? ?? false,
      expanded: json['expanded'] as bool? ?? false,
    );
  }

  RuntimeNodeState copyWith({
    bool? enabled,
    bool? visible,
    bool? loading,
    bool? readOnly,
    bool? selected,
    bool? expanded,
  }) {
    return RuntimeNodeState(
      enabled: enabled ?? this.enabled,
      visible: visible ?? this.visible,
      loading: loading ?? this.loading,
      readOnly: readOnly ?? this.readOnly,
      selected: selected ?? this.selected,
      expanded: expanded ?? this.expanded,
    );
  }
}
