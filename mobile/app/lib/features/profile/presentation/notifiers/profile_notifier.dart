import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import '../../../auth/presentation/notifiers/auth_state_provider.dart';
import '../../data/models/profile_data_model.dart';

class ProfileNotifier extends AsyncNotifier<ProfileData?> {
  @override
  FutureOr<ProfileData?> build() async {
    final authState = ref.watch(authStateProvider);
    if (!authState.isAuthenticated) {
      return null;
    }
    return _fetchProfileData();
  }

  Future<ProfileData> _fetchProfileData() async {
    final dio = ref.read(authApiClientProvider);
    final res = await dio.get('profile');
    if (res.statusCode == 200 && res.data['success'] == true) {
      return ProfileData.fromJson(res.data['data']);
    } else {
      throw Exception(res.data['message'] ?? 'Gagal memuat data profil.');
    }
  }

  Future<void> fetchProfile() async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      final authState = ref.read(authStateProvider);
      if (!authState.isAuthenticated) {
        return null;
      }
      return _fetchProfileData();
    });
  }

  void setGuest() {
    state = const AsyncValue.data(null);
  }
}

final profileProvider = AsyncNotifierProvider<ProfileNotifier, ProfileData?>(ProfileNotifier.new);
