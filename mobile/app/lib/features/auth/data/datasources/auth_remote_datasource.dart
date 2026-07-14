import 'package:dio/dio.dart';
import '../../../../core/api/api_client.dart';
import '../../domain/models/auth_user_model.dart';
import '../../domain/models/login_dto.dart';

class AuthRemoteDatasource {
  final Dio _client = ApiClient.instance;

  Future<String> login(LoginDto dto) async {
    final response = await _client.post('/auth/login', data: dto.toJson());
    // Assuming backend returns { "token": "...", "user": {...} }
    if (response.data != null && response.data['data'] != null && response.data['data']['token'] != null) {
      return response.data['data']['token'] as String;
    }
    throw Exception('Token not found in response');
  }

  Future<AuthUserModel> fetchUser() async {
    final response = await _client.get('/v1/auth/user');
    // Backend returns { success: true, data: {...} }
    final data = response.data['data'] ?? response.data;
    return AuthUserModel.fromJson(data);
  }

  Future<void> logout() async {
    await _client.post('/v1/auth/logout');
  }
}
