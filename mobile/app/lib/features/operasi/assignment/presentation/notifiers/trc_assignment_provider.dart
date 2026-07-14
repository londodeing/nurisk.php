import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart' as dio;
import 'package:nurisk_mobile/features/operasi/assignment/data/datasources/trc_assignment_datasource.dart';
import 'package:nurisk_mobile/features/operasi/assignment/domain/models/trc_member_dto.dart';
import 'package:nurisk_mobile/features/public/report/presentation/notifiers/laporan_validation_provider.dart';

final trcAssignmentDatasourceProvider = Provider((ref) => TrcAssignmentDatasource());

final availableTrcProvider = FutureProvider.autoDispose.family<List<TrcMemberDto>, int?>((ref, idPcnu) async {
  final datasource = ref.read(trcAssignmentDatasourceProvider);
  return await datasource.getAvailableTrc(idPcnu: idPcnu);
});

class TrcAssignmentState {
  final List<int> selectedTrcIds;
  final String prioritas;
  final bool isSubmitting;

  TrcAssignmentState({
    this.selectedTrcIds = const [],
    this.prioritas = 'sedang',
    this.isSubmitting = false,
  });

  TrcAssignmentState copyWith({
    List<int>? selectedTrcIds,
    String? prioritas,
    bool? isSubmitting,
  }) {
    return TrcAssignmentState(
      selectedTrcIds: selectedTrcIds ?? this.selectedTrcIds,
      prioritas: prioritas ?? this.prioritas,
      isSubmitting: isSubmitting ?? this.isSubmitting,
    );
  }
}

class TrcAssignmentNotifier extends Notifier<TrcAssignmentState> {
  @override
  TrcAssignmentState build() => TrcAssignmentState();

  void toggleTrcSelection(int id) {
    if (state.selectedTrcIds.contains(id)) {
      state = state.copyWith(
        selectedTrcIds: state.selectedTrcIds.where((e) => e != id).toList(),
      );
    } else {
      state = state.copyWith(
        selectedTrcIds: [...state.selectedTrcIds, id],
      );
    }
  }

  void setPrioritas(String prioritas) {
    state = state.copyWith(prioritas: prioritas);
  }

  Future<bool> submitEskalasi({required int laporanId, int? idPcnu}) async {
    state = state.copyWith(isSubmitting: true);
    try {
      final datasource = ref.read(trcAssignmentDatasourceProvider);
      final success = await datasource.eskalasiInsiden(
        laporanId: laporanId,
        petugasTrcIds: state.selectedTrcIds,
        prioritas: state.prioritas,
        idPcnu: idPcnu,
      );
      if (success) {
        // Refresh laporan list
        ref.invalidate(laporanValidationProvider);
      }
      return success;
    } catch (e) {
      if (e is dio.DioException) {
        final data = e.response?.data;
        if (data is Map && data.containsKey('message')) {
          throw Exception(data['message']);
        }
      }
      throw Exception('Terjadi kesalahan pada server');
    } finally {
      state = state.copyWith(isSubmitting: false);
    }
  }
}

final trcAssignmentNotifierProvider = NotifierProvider<TrcAssignmentNotifier, TrcAssignmentState>(() {
  return TrcAssignmentNotifier();
});
