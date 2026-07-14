import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import 'package:nurisk_mobile/features/public/report/data/datasources/laporan_remote_datasource.dart';
import 'package:nurisk_mobile/features/public/report/data/models/laporan_kejadian_model.dart';


class LaporanValidationState {
  final bool isLoading;
  final List<LaporanKejadianModel> pendingList;
  final String? error;

  const LaporanValidationState({
    this.isLoading = false,
    this.pendingList = const [],
    this.error,
  });

  LaporanValidationState copyWith({
    bool? isLoading,
    List<LaporanKejadianModel>? pendingList,
    String? error,
  }) {
    return LaporanValidationState(
      isLoading: isLoading ?? this.isLoading,
      pendingList: pendingList ?? this.pendingList,
      error: error ?? this.error,
    );
  }
}

class LaporanValidationNotifier extends Notifier<LaporanValidationState> {
  @override
  LaporanValidationState build() {
    return const LaporanValidationState();
  }

  Future<void> fetchPendingLaporan() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final authDio = ref.read(authApiClientProvider);
      final ds = ref.read(laporanRemoteDatasourceProvider);
      final list = await ds.getPendingLaporan(authDio);
      state = state.copyWith(isLoading: false, pendingList: list);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<bool> validateLaporan({
    required int idLaporan,
    required int idPcnu,
    required String prioritas,
    required String statusInsiden,
    String? alamatLengkap,
    double? latitude,
    double? longitude,
  }) async {
    try {
      final authDio = ref.read(authApiClientProvider);
      final ds = ref.read(laporanRemoteDatasourceProvider);
      final success = await ds.validasiLaporan(
        authDio, 
        idLaporan, 
        idPcnu, 
        prioritas, 
        statusInsiden,
        alamatLengkap,
        latitude,
        longitude,
      );
      if (success) {
        await fetchPendingLaporan();
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  Future<bool> tolakLaporan({
    required int idLaporan,
    required String alasan,
    required String catatan,
  }) async {
    try {
      final authDio = ref.read(authApiClientProvider);
      final ds = ref.read(laporanRemoteDatasourceProvider);
      final success = await ds.tolakLaporan(authDio, idLaporan, alasan, catatan);
      if (success) {
        await fetchPendingLaporan();
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }
}

final laporanValidationProvider = NotifierProvider<LaporanValidationNotifier, LaporanValidationState>(() {
  return LaporanValidationNotifier();
});
