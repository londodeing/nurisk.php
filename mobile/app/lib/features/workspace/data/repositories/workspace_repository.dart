import '../../domain/models/workspace_dto.dart';
import '../datasources/workspace_remote_datasource.dart';

class WorkspaceRepository {
  final WorkspaceRemoteDatasource _datasource;

  WorkspaceRepository(this._datasource);

  Future<WorkspaceDto> fetchWorkspace() async {
    return await _datasource.fetchWorkspace();
  }

  Future<void> toggleAvailability(int idPengguna) async {
    await _datasource.toggleAvailability(idPengguna);
  }
}
