import '../datasources/auth_remote_datasource.dart';
import '../../domain/models/auth_user_model.dart';
import '../../domain/models/login_dto.dart';
import '../../../../core/storage/secure_storage_service.dart';

class AuthRepository {
  final AuthRemoteDatasource _datasource;

  AuthRepository(this._datasource);

  Future<AuthUserModel> login(String noHp, String password) async {
    final token = await _datasource.login(LoginDto(noHp: noHp, password: password));
    await SecureStorageService.saveToken(token);
    return await _datasource.fetchUser();
  }

  Future<AuthUserModel?> checkAuth() async {
    final token = await SecureStorageService.getToken();
    if (token == null) return null;
    
    try {
      return await _datasource.fetchUser();
    } catch (e) {
      // If fetching user fails (e.g., token expired), delete token
      await SecureStorageService.deleteToken();
      return null;
    }
  }

  Future<void> logout() async {
    try {
      await _datasource.logout();
    } catch (_) {
      // Ignore API errors on logout, we still want to clear local storage
    } finally {
      await SecureStorageService.deleteToken();
    }
  }
}
