import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/models/auth_user_model.dart';
import '../../data/datasources/auth_remote_datasource.dart';
import '../../data/repositories/auth_repository.dart';
import '../../../../core/error/dio_exception_mapper.dart';

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
      state = AsyncValue.data(user);
    } catch (e, stack) {
      state = AsyncValue.error(DioExceptionMapper.toUserMessage(e), stack);
    }
  }

  Future<void> logout() async {
    state = const AsyncValue.loading();
    try {
      final repo = ref.read(authRepositoryProvider);
      await repo.logout();
      state = const AsyncValue.data(null);
    } catch (e, stack) {
      state = AsyncValue.error(DioExceptionMapper.toUserMessage(e), stack);
    }
  }
}

final authProvider = AsyncNotifierProvider<AuthNotifier, AuthUserModel?>(() {
  return AuthNotifier();
});
