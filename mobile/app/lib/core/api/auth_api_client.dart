import 'package:dio/dio.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/logger/app_logger.dart';
import 'package:nurisk_mobile/features/auth/presentation/notifiers/auth_state_provider.dart';

final authApiClientProvider = Provider<Dio>((ref) {
  final dio = Dio();

  String baseUrl = dotenv.env['API_BASE_URL'] ?? 'http://10.0.2.2:8000/api/';
  if (!baseUrl.endsWith('/')) {
    baseUrl += '/';
  }

  dio.options = BaseOptions(
    baseUrl: baseUrl,
    connectTimeout: const Duration(seconds: 10),
    receiveTimeout: const Duration(seconds: 15),
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  );

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) {
        final authState = ref.read(authStateProvider);
        if (authState.isAuthenticated && authState.token != null) {
          options.headers['Authorization'] = 'Bearer ${authState.token}';
          if (authState.activeScopeId != null) options.headers['X-Scope-Id'] = authState.activeScopeId;
          if (authState.activeScopeType != null) options.headers['X-Scope-Type'] = authState.activeScopeType;
          if (authState.activeRole != null) options.headers['X-Role'] = authState.activeRole;
        }
        appLogger.i('[AUTH] Request: [${options.method}] ${options.uri}');
        return handler.next(options);
      },
      onResponse: (response, handler) {
        appLogger.i('[AUTH] Response: [${response.statusCode}] ${response.requestOptions.uri}');
        return handler.next(response);
      },
      onError: (DioException e, handler) {
        appLogger.e('[AUTH] Error: [${e.response?.statusCode}] ${e.requestOptions.uri}', e);
        return handler.next(e);
      },

    ),
  );

  return dio;
});
