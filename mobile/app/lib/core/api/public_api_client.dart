import 'package:dio/dio.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'dart:developer';

final publicApiClientProvider = Provider<Dio>((ref) {
  final dio = Dio();
  
  String baseUrl = dotenv.env['API_BASE_URL'] ?? 'https://nurisk.org/api/';
  if (!baseUrl.endsWith('/')) {
    baseUrl += '/';
  }
  
  dio.options = BaseOptions(
    baseUrl: baseUrl,
    connectTimeout: const Duration(seconds: 10),
    receiveTimeout: const Duration(seconds: 15),
    sendTimeout: const Duration(seconds: 15),
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      // 'Accept-Encoding': 'gzip, deflate, br', // Enable compression
    },
  );

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) {
        // [F0.5] STRICT ARCHITECTURE VIOLATION GUARD
        if (options.headers.containsKey('Authorization')) {
          log('❌ ARCHITECTURE VIOLATION: Auth header detected in PublicApiClient.', level: 1000);
          options.headers.remove('Authorization');
        }
        
        // Caching Policy Placeholder (stale-while-revalidate)
        // options.extra['use_cache'] = true;
        
        log('Public API Request: [${options.method}] ${options.uri}');
        return handler.next(options);
      },
      onResponse: (response, handler) {
        log('Public API Response: [${response.statusCode}] ${response.requestOptions.uri}');
        return handler.next(response);
      },
      onError: (DioException e, handler) {
        log('Public API Error: [${e.response?.statusCode}] ${e.requestOptions.uri}', error: e);
        
        // Retry logic could be implemented here using dio_retry
        // No 401 token refresh handling here (Strictly prohibited)
        return handler.next(e);
      },
    ),
  );

  return dio;
});
