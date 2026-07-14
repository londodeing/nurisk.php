import '../action_handler.dart';
import '../action_result.dart';
import '../runtime_action.dart';
import '../runtime_context.dart';

class ToastHandler implements ActionHandler {
  @override
  bool supports(RuntimeAction action) => action.type == 'toast' || action.type == 'snackbar';

  @override
  Future<ActionResult> execute(RuntimeAction action, RuntimeContext context) async {
    final message = action.payload['message'] as String? ?? 'Tersimpan';
    context.showToast(message);
    return ActionResult.success();
  }
}
