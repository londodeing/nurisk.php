import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/api_client.dart';
import 'package:nurisk_mobile/features/operasi/insiden/data/models/insiden_model.dart';

class InsidenDatasource {
  final Dio _client;

  InsidenDatasource(this._client);

  /// Daftar insiden (bisa difilter status, prioritas)
  Future<List<InsidenModel>> getInsidenList({
    String? status,
    String? prioritas,
    int page = 1,
  }) async {
    final response = await _client.get(
      'v1/insiden',
      queryParameters: {
        if (status != null && status.isNotEmpty) 'status': status,
        if (prioritas != null && prioritas.isNotEmpty) 'prioritas': prioritas,
        'page': page,
        'per_page': 20,
        'sort_by': 'dibuat_pada',
        'sort_order': 'desc',
      },
    );

    final List<dynamic> data = response.data['data'] ?? [];
    return data.map((e) => InsidenModel.fromJson(e)).toList();
  }

  /// Detail insiden lengkap (riwayat, penugasan, dll)
  Future<InsidenModel> getInsidenDetail(String uuid) async {
    final response = await _client.get('v1/insiden/$uuid');
    final data = response.data['data'] as Map<String, dynamic>;
    return InsidenModel.fromJson(data);
  }

  /// Ubah status insiden
  Future<Map<String, dynamic>> ubahStatus({
    required String uuid,
    required String statusBaru,
    String? alasan,
  }) async {
    final response = await _client.patch(
      'v1/insiden/$uuid/status',
      data: {
        'status': statusBaru,
        if (alasan != null && alasan.isNotEmpty) 'alasan': alasan,
      },
    );
    return response.data as Map<String, dynamic>;
  }
}

final insidenDatasourceProvider = Provider<InsidenDatasource>((ref) {
  return InsidenDatasource(ApiClient.instance);
});
