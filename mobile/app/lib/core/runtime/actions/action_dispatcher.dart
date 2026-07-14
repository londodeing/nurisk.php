import 'package:nurisk_mobile/core/runtime/state/runtime_node_state.dart';
import 'action_handler.dart';
import 'action_result.dart';
import 'runtime_action.dart';
import 'runtime_context.dart';

class RuntimeActionDispatcher {
  final List<ActionHandler> _handlers = [];
  final RuntimeContext context;

  RuntimeActionDispatcher({required this.context});

  void register(ActionHandler handler) {
    _handlers.add(handler);
  }

  void registerAll(List<ActionHandler> handlers) {
    _handlers.addAll(handlers);
  }

  Future<ActionResult> dispatch(RuntimeAction action, {RuntimeNodeState? nodeState}) async {
    // --------------------------------------------------
    // 0. Node state guard — reject if node is disabled
    // --------------------------------------------------
    if (nodeState != null && !nodeState.enabled) {
      context.trackAnalytics('action.blocked', {
        'type': action.type,
        'id': action.id,
        'reason': 'node_disabled',
      });
      return const ActionResult(success: false);
    }
    // --------------------------------------------------
    // 1. Analytics: before hook
    // --------------------------------------------------
    context.trackAnalytics('action.before', {'type': action.type, 'id': action.id});

    try {
      // --------------------------------------------------
      // 2. Confirm dialog (dispatcher concern, not handler)
      // --------------------------------------------------
      if (action.confirm != null) {
        final accepted = await context.showConfirmDialog(action.confirm!);
        if (!accepted) {
          context.trackAnalytics('action.cancelled', {'type': action.type, 'id': action.id});
          return const ActionResult(success: false);
        }
      }

      // --------------------------------------------------
      // 3. Find handler
      // --------------------------------------------------
      final handler = _handlers.cast<ActionHandler?>().firstWhere(
        (h) => h!.supports(action),
        orElse: () => null,
      );

      if (handler == null) {
        throw UnsupportedError('No handler registered for action type: ${action.type}');
      }

      // --------------------------------------------------
      // 4. Execute handler
      // --------------------------------------------------
      final result = await handler.execute(action, context);

      // --------------------------------------------------
      // 5. Process action chain (on_success / on_failure)
      // --------------------------------------------------
      final chain = result.success ? action.onSuccess : action.onFailure;
      if (chain != null && chain.isNotEmpty) {
        for (final nextAction in chain) {
          await dispatch(nextAction);
        }
      }

      // --------------------------------------------------
      // 6. Analytics: after hook
      // --------------------------------------------------
      context.trackAnalytics('action.after', {
        'type': action.type,
        'id': action.id,
        'success': result.success,
      });

      return result;
    } catch (e) {
      // --------------------------------------------------
      // 7. Error handling (dispatcher concern, not handler)
      // --------------------------------------------------
      context.trackAnalytics('action.failed', {
        'type': action.type,
        'id': action.id,
        'error': e.toString(),
      });

      // If there's an on_failure chain, process it
      if (action.onFailure != null && action.onFailure!.isNotEmpty) {
        for (final nextAction in action.onFailure!) {
          try {
            await dispatch(nextAction);
          } catch (_) {
            // Prevent infinite error loops
          }
        }
      }

      context.showToast('Terjadi kesalahan: ${e.toString()}');

      return ActionResult.failed(e);
    }
  }
}
