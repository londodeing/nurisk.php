import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/models/auth_user_model.dart';
import '../../data/datasources/auth_remote_datasource.dart';
import '../../data/repositories/auth_repository.dart';
import '../../../../core/error/dio_exception_mapper.dart';
import '../notifiers/auth_state_provider.dart';
import '../../../../core/storage/secure_storage_service.dart';

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(AuthRemoteDatasource());
});

class AuthNotifier extends AsyncNotifier<AuthUserModel?> {
  @override
  Future<AuthUserModel?> build() async {
    final repo = ref.watch(authRepositoryProvider);
    return await repo.checkAuth();
  }

  Future<void> login(String noHp, String password) async {
    state = const AsyncValue.loading();
    try {
      final repo = ref.read(authRepositoryProvider);
      final user = await repo.login(noHp, password);
      
      // Ambil token yang disimpan oleh repo.login ke secure storage
      final token = await SecureStorageService.getToken();
      if (token != null) {
        // Sinkronisasi state token ke authStateProvider secara real-time
        await ref.read(authStateProvider.notifier).loginWithDetails(
          token: token,
          userId: user.id.toString(),
          userName: user.namaLengkap ?? '',
          role: user.namaPeran ?? 'relawan',
          scopeId: user.defaultScopeId?.toString() ?? '',
          scopeType: user.defaultScopeType ?? '',
          jabatan: '', // default empty
        );
      }
      
      state = AsyncValue.data(user);
    } catch (e, stack) {
      state = AsyncValue.error(DioExceptionMapper.toUserMessage(e), stack);
    }
  }

  Future<void> logout() async {
    try {
      final repo = ref.read(authRepositoryProvider);
      await repo.logout();

      // Reset state di authStateProvider
      await ref.read(authStateProvider.notifier).logout();

      state = const AsyncValue.data(null);
    } catch (e, stack) {
      state = AsyncValue.error(DioExceptionMapper.toUserMessage(e), stack);
    }
  }
}

final authProvider = AsyncNotifierProvider<AuthNotifier, AuthUserModel?>(() {
  return AuthNotifier();
});

