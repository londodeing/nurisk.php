import 'package:dio/dio.dart';
import '../../../../core/api/api_client.dart';
import '../../domain/models/workspace_dto.dart';

class WorkspaceRemoteDatasource {
  final Dio _client = ApiClient.instance;

  Future<WorkspaceDto> fetchWorkspace() async {
    final response = await _client.get('/account/home');
    
    // The backend returns { "success": true, "data": { "profil": ... } }
    final payload = response.data['data'] ?? response.data;
    
    return WorkspaceDto.fromJson(payload);
  }

  Future<bool> toggleAvailability(int idPengguna) async {
    final response = await _client.post(
      '/v1/profil/toggle-tersedia',
      data: {'id_pengguna': idPengguna},
    );
    return response.statusCode == 200;
  }
}
