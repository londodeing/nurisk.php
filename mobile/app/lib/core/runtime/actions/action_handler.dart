import 'action_result.dart';
import 'runtime_action.dart';
import 'runtime_context.dart';

abstract class ActionHandler {
  bool supports(RuntimeAction action);

  Future<ActionResult> execute(RuntimeAction action, RuntimeContext context);
}
