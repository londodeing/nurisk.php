import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import '../storage/secure_storage_service.dart';

class ApiClient {
  static final Dio instance = _createDio();

  static Dio _createDio() {
    var baseUrl = dotenv.env['API_BASE_URL'] ?? 'http://10.0.2.2:8000';
    if (!baseUrl.endsWith('/')) {
      baseUrl += '/';
    }
    if (!baseUrl.endsWith('api/')) {
      baseUrl += 'api/';
    }
    
    var dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      receiveTimeout: const Duration(seconds: 15),
      connectTimeout: const Duration(seconds: 15),
      sendTimeout: const Duration(seconds: 15),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await SecureStorageService.getToken();
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          if (kDebugMode) {
            debugPrint('[API Request] ${options.method} ${options.uri}');
          }
          return handler.next(options);
        },
        onResponse: (response, handler) {
          if (kDebugMode) {
            debugPrint('[API Response] ${response.statusCode} ${response.requestOptions.uri}');
          }
          return handler.next(response);
        },
        onError: (DioException e, handler) async {
          if (kDebugMode) {
            debugPrint('[API Error] ${e.response?.statusCode} ${e.requestOptions.uri}');
            debugPrint('[API Error Data] ${e.response?.data}');
          }
          
          if (e.response?.statusCode == 401) {
            // Token expired or invalid
            await SecureStorageService.deleteToken();
            // Optional: trigger navigation to login if possible, usually better handled in auth repository/provider
          }
          return handler.next(e);
        },
      ),
    );

    return dio;
  }
}
