import 'package:dio/dio.dart';
import '../action_handler.dart';
import '../action_result.dart';
import '../runtime_action.dart';
import '../runtime_context.dart';

class SubmitHandler implements ActionHandler {
  @override
  bool supports(RuntimeAction action) => action.type == 'submit';

  @override
  Future<ActionResult> execute(RuntimeAction action, RuntimeContext context) async {
    final endpoint = action.payload['endpoint'] as String?;
    final methodStr = (action.payload['method'] as String? ?? 'POST').toUpperCase();
    final fields = action.payload['fields'] as Map<String, dynamic>? ?? {};

    if (endpoint == null) {
      throw const FormatException('Submit action requires an "endpoint" field in payload.');
    }

    late Response res;
    switch (methodStr) {
      case 'GET':
        res = await context.httpClient.get(endpoint, queryParameters: fields);
        break;
      case 'POST':
        res = await context.httpClient.post(endpoint, data: fields);
        break;
      case 'PUT':
        res = await context.httpClient.put(endpoint, data: fields);
        break;
      case 'DELETE':
        res = await context.httpClient.delete(endpoint, data: fields);
        break;
      default:
        res = await context.httpClient.post(endpoint, data: fields);
    }

    if (res.statusCode != null && res.statusCode! >= 200 && res.statusCode! < 300) {
      return ActionResult.success();
    }

    throw Exception('Submit failed: ${res.statusCode} ${res.statusMessage}');
  }
}
