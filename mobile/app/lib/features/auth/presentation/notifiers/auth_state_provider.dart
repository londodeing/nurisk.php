import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';


class AuthState {
  final bool isAuthenticated;
  final bool isLoading;
  final String? token;
  final String? userId;
  final String? userName;
  
  // Lapis 2 & 3 Otorisasi
  final String? activeRole; // Lapis 1 (Role Global)
  final String? activeScopeId; // Lapis 3 (Scope Wilayah PCNU)
  final String? activeScopeType; // e.g. pcnu, pwnu
  final String? activeJabatan; // Lapis 2 (Jabatan Struktural)

  const AuthState({
    this.isAuthenticated = false,
    this.isLoading = true,
    this.token,
    this.userId,
    this.userName,
    this.activeRole,
    this.activeScopeId,
    this.activeScopeType,
    this.activeJabatan,
  });

  AuthState copyWith({
    bool? isAuthenticated,
    bool? isLoading,
    String? token,
    String? userId,
    String? userName,
    String? activeRole,
    String? activeScopeId,
    String? activeScopeType,
    String? activeJabatan,
  }) {
    return AuthState(
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      isLoading: isLoading ?? this.isLoading,
      token: token ?? this.token,
      userId: userId ?? this.userId,
      userName: userName ?? this.userName,
      activeRole: activeRole ?? this.activeRole,
      activeScopeId: activeScopeId ?? this.activeScopeId,
      activeScopeType: activeScopeType ?? this.activeScopeType,
      activeJabatan: activeJabatan ?? this.activeJabatan,
    );
  }
}

class AuthStateNotifier extends Notifier<AuthState> {
  final _storage = const FlutterSecureStorage();
  int _verifyAttempts = 0;
  static const int _maxVerifyAttempts = 3;

  @override
  AuthState build() {
    _loadState();
    return const AuthState(isLoading: true);
  }

  Future<void> _loadState() async {
    try {
      final token = await _storage.read(key: 'auth_token');
      
      if (token != null) {
        state = AuthState(
          isAuthenticated: true,
          isLoading: false,
          token: token,
          userId: await _storage.read(key: 'auth_user_id'),
          userName: await _storage.read(key: 'auth_user_name'),
          activeRole: await _storage.read(key: 'auth_active_role'),
          activeScopeId: await _storage.read(key: 'auth_active_scope_id'),
          activeScopeType: await _storage.read(key: 'auth_active_scope_type'),
          activeJabatan: await _storage.read(key: 'auth_active_jabatan'),
        );
        _verifyAttempts = 0;
        verifySessionWithDatabase();
      } else {
        state = const AuthState(isLoading: false);
      }
    } catch (e) {
      state = const AuthState(isLoading: false);
    }
  }

  Future<void> verifySessionWithDatabase() async {
    if (state.token == null) return;
    if (_verifyAttempts > _maxVerifyAttempts) return;
    _verifyAttempts++;
    try {
      final dio = ref.read(authApiClientProvider);
      final res = await dio.get('profile');
      if (res.statusCode == 401 || res.statusCode == 403) {
        await logout();
      }
    } catch (e) {
      if (e is DioException) {
        if (e.response?.statusCode == 401 || e.response?.statusCode == 403) {
          await logout();
        }
      }
    }
  }


  Future<void> login(String token, String userId, String userName) async {
    await _storage.write(key: 'auth_token', value: token);
    await _storage.write(key: 'auth_user_id', value: userId);
    await _storage.write(key: 'auth_user_name', value: userName);

    state = state.copyWith(
      isAuthenticated: true,
      token: token,
      userId: userId,
      userName: userName,
    );
  }

  Future<void> loginWithDetails({
    required String token,
    required String userId,
    required String userName,
    required String role,
    required String scopeId,
    required String scopeType,
    required String jabatan,
  }) async {
    await _storage.write(key: 'auth_token', value: token);
    await _storage.write(key: 'auth_user_id', value: userId);
    await _storage.write(key: 'auth_user_name', value: userName);
    await _storage.write(key: 'auth_active_role', value: role);
    await _storage.write(key: 'auth_active_scope_id', value: scopeId);
    await _storage.write(key: 'auth_active_scope_type', value: scopeType);
    await _storage.write(key: 'auth_active_jabatan', value: jabatan);

    state = state.copyWith(
      isAuthenticated: true,
      token: token,
      userId: userId,
      userName: userName,
      activeRole: role,
      activeScopeId: scopeId,
      activeScopeType: scopeType,
      activeJabatan: jabatan,
    );
  }

  Future<void> setMandate({
    required String role,
    required String scopeId,
    required String scopeType,
    required String jabatan,
  }) async {
    await _storage.write(key: 'auth_active_role', value: role);
    await _storage.write(key: 'auth_active_scope_id', value: scopeId);
    await _storage.write(key: 'auth_active_scope_type', value: scopeType);
    await _storage.write(key: 'auth_active_jabatan', value: jabatan);

    state = state.copyWith(
      activeRole: role,
      activeScopeId: scopeId,
      activeScopeType: scopeType,
      activeJabatan: jabatan,
    );
  }

  Future<void> logout() async {
    await _storage.deleteAll();
    state = const AuthState(isLoading: false);
  }

}

final authStateProvider = NotifierProvider<AuthStateNotifier, AuthState>(() {
  return AuthStateNotifier();
});
