import 'package:dio/dio.dart';
import '../action_handler.dart';
import '../action_result.dart';
import '../runtime_action.dart';
import '../runtime_context.dart';

class CustomActionHandler implements ActionHandler {
  @override
  bool supports(RuntimeAction action) => action.type == 'action';

  @override
  Future<ActionResult> execute(RuntimeAction action, RuntimeContext context) async {
    final actionType = action.payload['action_type'] as String?;
    if (actionType == null) {
      throw const FormatException('Custom action requires an "action_type" field in payload.');
    }

    final endpoint = action.payload['endpoint'] as String?;
    if (endpoint == null) {
      throw const FormatException('Custom action requires an "endpoint" field in payload.');
    }

    final method = (action.payload['method'] as String? ?? 'POST').toUpperCase();
    final body = action.payload['body'] as Map<String, dynamic>? ?? {};
    final optimistic = action.payload['optimistic'] == true;

    if (optimistic) {
      final rawPatches = action.payload['optimistic_patches'] as Map<String, dynamic>? ?? {};
      final patches = rawPatches.map((key, value) => MapEntry(key, value as Map<String, dynamic>));
      if (patches.isNotEmpty) {
        await context.applyOptimistic(patches);
      }
    }

    try {
      late Response res;
      switch (method) {
        case 'GET':
          res = await context.httpClient.get(endpoint, queryParameters: body);
          break;
        case 'POST':
          res = await context.httpClient.post(endpoint, data: body);
          break;
        case 'PUT':
          res = await context.httpClient.put(endpoint, data: body);
          break;
        case 'DELETE':
          res = await context.httpClient.delete(endpoint, data: body);
          break;
        default:
          res = await context.httpClient.post(endpoint, data: body);
      }

      if (res.statusCode != null && res.statusCode! >= 200 && res.statusCode! < 300) {
        return ActionResult.success();
      }

      if (optimistic) {
        await context.revertOptimistic();
      }
      throw Exception('Custom action failed: ${res.statusCode} ${res.statusMessage}');
    } catch (e) {
      if (optimistic) {
        await context.revertOptimistic();
      }
      rethrow;
    }
  }
}
