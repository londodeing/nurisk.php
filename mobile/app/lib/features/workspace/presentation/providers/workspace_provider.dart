import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/models/workspace_dto.dart';
import '../../data/datasources/workspace_remote_datasource.dart';
import '../../data/repositories/workspace_repository.dart';

final workspaceRepositoryProvider = Provider<WorkspaceRepository>((ref) {
  return WorkspaceRepository(WorkspaceRemoteDatasource());
});

class WorkspaceNotifier extends AsyncNotifier<WorkspaceDto> {
  @override
  Future<WorkspaceDto> build() async {
    final repository = ref.watch(workspaceRepositoryProvider);
    return await repository.fetchWorkspace();
  }

  Future<void> toggleAvailability() async {
    final current = state.value;
    if (current == null || current.profil == null) return;

    final profil = Map<String, dynamic>.from(current.profil!);
    final idPengguna = profil['id_pengguna'] as int?;
    if (idPengguna == null) return;

    final currentStatus = (profil['is_tersedia'] == 1 || profil['is_tersedia'] == true);
    final nextStatus = !currentStatus;

    // Optimistic update
    profil['is_tersedia'] = nextStatus ? 1 : 0;
    state = AsyncValue.data(WorkspaceDto(
      profil: profil,
      jabatanAktif: current.jabatanAktif,
      keahlian: current.keahlian,
      penugasan: current.penugasan,
      commandCenter: current.commandCenter,
      alertInsiden: current.alertInsiden,
      version: current.version,
    ));

    try {
      final repository = ref.read(workspaceRepositoryProvider);
      await repository.toggleAvailability(idPengguna);
    } catch (e, stack) {
      // Revert on error
      profil['is_tersedia'] = currentStatus ? 1 : 0;
      state = AsyncValue.data(WorkspaceDto(
        profil: profil,
        jabatanAktif: current.jabatanAktif,
        keahlian: current.keahlian,
        penugasan: current.penugasan,
        commandCenter: current.commandCenter,
        alertInsiden: current.alertInsiden,
        version: current.version,
      ));
      state = AsyncValue<WorkspaceDto>.error(e, stack).copyWithPrevious(state);
    }
  }
}

final workspaceProvider = AsyncNotifierProvider<WorkspaceNotifier, WorkspaceDto>(() {
  return WorkspaceNotifier();
});
