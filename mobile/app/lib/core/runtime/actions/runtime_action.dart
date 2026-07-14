class ConfirmDialog {
  final String title;
  final String message;
  final String confirmLabel;
  final String cancelLabel;

  const ConfirmDialog({
    required this.title,
    required this.message,
    this.confirmLabel = 'Ya',
    this.cancelLabel = 'Batal',
  });

  factory ConfirmDialog.fromJson(Map<String, dynamic> json) {
    return ConfirmDialog(
      title: json['title'] as String? ?? 'Konfirmasi',
      message: json['message'] as String? ?? '',
      confirmLabel: json['confirm_label'] as String? ?? 'Ya',
      cancelLabel: json['cancel_label'] as String? ?? 'Batal',
    );
  }
}

class RuntimeAction {
  final String type;
  final String? id;
  final Map<String, dynamic> payload;
  final List<RuntimeAction>? onSuccess;
  final List<RuntimeAction>? onFailure;
  final ConfirmDialog? confirm;
  final bool requiresAuth;

  const RuntimeAction({
    required this.type,
    this.id,
    this.payload = const {},
    this.onSuccess,
    this.onFailure,
    this.confirm,
    this.requiresAuth = false,
  });

  factory RuntimeAction.fromJson(Map<String, dynamic> json) {
    final type = json['type'] as String?;
    if (type == null) {
      throw const FormatException('RuntimeAction requires a "type" field.');
    }

    final id = json['id'] as String?;
    final requiresAuth = json['requires_auth'] == true;

    // Payload is everything except reserved keys
    final reserved = {'type', 'id', 'on_success', 'on_failure', 'confirm', 'analytics', 'requires_auth'};
    final payload = <String, dynamic>{};
    for (final entry in json.entries) {
      if (!reserved.contains(entry.key)) {
        payload[entry.key] = entry.value;
      }
    }

    // Parse on_success: can be a single object or a list
    List<RuntimeAction>? onSuccess;
    if (json['on_success'] != null) {
      if (json['on_success'] is List) {
        onSuccess = (json['on_success'] as List)
            .map((e) => RuntimeAction.fromJson(e as Map<String, dynamic>))
            .toList();
      } else if (json['on_success'] is Map) {
        onSuccess = [RuntimeAction.fromJson(json['on_success'] as Map<String, dynamic>)];
      }
    }

    // Parse on_failure
    List<RuntimeAction>? onFailure;
    if (json['on_failure'] != null) {
      if (json['on_failure'] is List) {
        onFailure = (json['on_failure'] as List)
            .map((e) => RuntimeAction.fromJson(e as Map<String, dynamic>))
            .toList();
      } else if (json['on_failure'] is Map) {
        onFailure = [RuntimeAction.fromJson(json['on_failure'] as Map<String, dynamic>)];
      }
    }

    // Parse confirm
    ConfirmDialog? confirm;
    if (json['confirm'] != null && json['confirm'] is Map) {
      confirm = ConfirmDialog.fromJson(json['confirm'] as Map<String, dynamic>);
    }

    return RuntimeAction(
      type: type,
      id: id,
      payload: payload,
      onSuccess: onSuccess,
      onFailure: onFailure,
      confirm: confirm,
      requiresAuth: requiresAuth,
    );
  }
}
