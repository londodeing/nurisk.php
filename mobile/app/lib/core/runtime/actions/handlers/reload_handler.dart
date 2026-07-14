import '../action_handler.dart';
import '../action_result.dart';
import '../runtime_action.dart';
import '../runtime_context.dart';

class ReloadHandler implements ActionHandler {
  @override
  bool supports(RuntimeAction action) => action.type == 'reload';

  @override
  Future<ActionResult> execute(RuntimeAction action, RuntimeContext context) async {
    await context.refreshScene();
    return const ActionResult(reload: true);
  }
}
