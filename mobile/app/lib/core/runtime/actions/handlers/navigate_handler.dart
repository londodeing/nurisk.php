import '../action_handler.dart';
import '../action_result.dart';
import '../runtime_action.dart';
import '../runtime_context.dart';

class NavigateHandler implements ActionHandler {
  @override
  bool supports(RuntimeAction action) => action.type == 'navigate';

  @override
  Future<ActionResult> execute(RuntimeAction action, RuntimeContext context) async {
    final target = action.payload['target'] as String?;
    if (target == null) {
      throw const FormatException('Navigate action requires a "target" field in payload.');
    }

    final replace = action.payload['replace'] as bool? ?? false;
    final clearStack = action.payload['clear_stack'] as bool? ?? false;

    if (clearStack || replace) {
      context.navigation.go(target);
    } else {
      context.navigation.push(target);
    }

    return ActionResult.success();
  }
}
