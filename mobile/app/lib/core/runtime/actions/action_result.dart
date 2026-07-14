import 'runtime_action.dart';

class ActionResult {
  final bool success;
  final String? toast;
  final bool reload;
  final bool refresh;
  final String? navigateTarget;
  final List<RuntimeAction>? nextActions;
  final Object? error;

  const ActionResult({
    this.success = true,
    this.toast,
    this.reload = false,
    this.refresh = false,
    this.navigateTarget,
    this.nextActions,
    this.error,
  });

  factory ActionResult.success() => const ActionResult();

  factory ActionResult.failed(Object error) => ActionResult(success: false, error: error);

  ActionResult copyWith({
    bool? success,
    String? toast,
    bool? reload,
    bool? refresh,
    String? navigateTarget,
    List<RuntimeAction>? nextActions,
    Object? error,
  }) {
    return ActionResult(
      success: success ?? this.success,
      toast: toast ?? this.toast,
      reload: reload ?? this.reload,
      refresh: refresh ?? this.refresh,
      navigateTarget: navigateTarget ?? this.navigateTarget,
      nextActions: nextActions ?? this.nextActions,
      error: error ?? this.error,
    );
  }
}
