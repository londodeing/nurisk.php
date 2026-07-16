import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import '../../domain/models/pleno_model.dart';

final plenoRepositoryProvider = Provider<PlenoRepository>((ref) {
  final dio = ref.watch(authApiClientProvider);
  return PlenoRepository(dio);
});

class PlenoRepository {
  final Dio _dio;

  PlenoRepository(this._dio);

  Future<List<Map<String, dynamic>>> fetchUsers() async {
    try {
      final response = await _dio.get('admin/pengguna', queryParameters: {
        'status_akun': 'aktif',
        'per_page': 100,
      });
      final list = response.data['data'] as List;
      return list.map((u) => {
        'id_pengguna': u['id'] as int,
        'nama_lengkap': u['nama_lengkap'] ?? u['email'] ?? 'User ${u['id']}',
      }).toList();
    } catch (e) {
      rethrow;
    }
  }

  Future<List<PlenoModel>> fetchPlenoList(String uuidInsiden, {int page = 1, int perPage = 15}) async {
    try {
      final response = await _dio.get(
        'v1/insiden/$uuidInsiden/pleno',
        queryParameters: {'page': page, 'per_page': perPage},
      );
      final data = response.data['data']['data'] as List;
      return data.map((json) => PlenoModel.fromJson(json)).toList();
    } catch (e) {
      rethrow;
    }
  }

  Future<PlenoModel> fetchPlenoDetail(String uuidInsiden, int idPleno) async {
    try {
      final response = await _dio.get('v1/insiden/$uuidInsiden/pleno/$idPleno');
      return PlenoModel.fromJson(response.data['data']);
    } catch (e) {
      rethrow;
    }
  }

  Future<PlenoModel> createPleno(String uuidInsiden, Map<String, dynamic> data) async {
    try {
      final response = await _dio.post('v1/insiden/$uuidInsiden/pleno', data: data);
      return PlenoModel.fromJson(response.data['data']);
    } catch (e) {
      rethrow;
    }
  }

  Future<PlenoModel> updatePleno(String uuidInsiden, int idPleno, Map<String, dynamic> data) async {
    try {
      final response = await _dio.put('v1/insiden/$uuidInsiden/pleno/$idPleno', data: data);
      return PlenoModel.fromJson(response.data['data']);
    } catch (e) {
      rethrow;
    }
  }

  Future<void> finalizePleno(String uuidInsiden, int idPleno) async {
    try {
      await _dio.post('v1/insiden/$uuidInsiden/pleno/$idPleno/finalisasi');
    } catch (e) {
      rethrow;
    }
  }

  Future<PlenoKeputusanModel> addKeputusan(String uuidInsiden, int idPleno, Map<String, dynamic> data) async {
    try {
      final response = await _dio.post('v1/insiden/$uuidInsiden/pleno/$idPleno/keputusan', data: data);
      return PlenoKeputusanModel.fromJson(response.data['data']);
    } catch (e) {
      rethrow;
    }
  }

  Future<void> removeKeputusan(String uuidInsiden, int idPleno, int idKeputusan) async {
    try {
      await _dio.delete('v1/insiden/$uuidInsiden/pleno/$idPleno/keputusan/$idKeputusan');
    } catch (e) {
      rethrow;
    }
  }

  Future<PlenoPesertaModel> addPeserta(String uuidInsiden, int idPleno, Map<String, dynamic> data) async {
    try {
      final response = await _dio.post('v1/insiden/$uuidInsiden/pleno/$idPleno/peserta', data: data);
      return PlenoPesertaModel.fromJson(response.data['data']);
    } catch (e) {
      rethrow;
    }
  }

  Future<void> removePeserta(String uuidInsiden, int idPleno, int idPeserta) async {
    try {
      await _dio.delete('v1/insiden/$uuidInsiden/pleno/$idPleno/peserta/$idPeserta');
    } catch (e) {
      rethrow;
    }
  }
}
