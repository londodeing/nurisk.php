import 'package:dio/dio.dart';
import 'package:nurisk_mobile/core/api/api_client.dart';
import 'package:nurisk_mobile/features/operasi/assignment/domain/models/trc_member_dto.dart';

class TrcAssignmentDatasource {
  final Dio _client = ApiClient.instance;

  Future<List<TrcMemberDto>> getAvailableTrc({int? idPcnu}) async {
    final Map<String, dynamic> queryParams = {};
    if (idPcnu != null) {
      queryParams['id_pcnu'] = idPcnu;
    }
    
    final response = await _client.get(
      '/public/dashboard/trc/assignable',
      queryParameters: queryParams,
    );
    final List<dynamic> data = response.data['data'] ?? [];
    return data.map((e) => TrcMemberDto.fromJson(e)).toList();
  }

  Future<bool> eskalasiInsiden({
    required int laporanId,
    required List<int> petugasTrcIds,
    required String prioritas,
    int? idPcnu,
  }) async {
    final payload = {
      'petugas_trc_ids': petugasTrcIds,
      'prioritas': prioritas,
      'status_insiden': 'terverifikasi',
    };
    if (idPcnu != null) {
      payload['id_pcnu'] = idPcnu;
    }

    final response = await _client.post(
      '/laporan/$laporanId/eskalasi-insiden',
      data: payload,
    );
    return response.statusCode == 201 || response.statusCode == 200;
  }
}
