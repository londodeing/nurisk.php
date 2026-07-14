import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';
import '../models/laporan_kejadian_model.dart';
import '../models/tracking_step_model.dart';
import '../models/jenis_bencana_model.dart';
import '../models/wilayah_model.dart';

abstract class LaporanRemoteDatasource {
  Future<LaporanKejadianModel> createLaporan({
    required int idJenisBencana,
    required String namaPelapor,
    required String hpPelapor,
    required String keteranganSituasi,
    String? titikKenal,
    required DateTime waktuKejadian,
    required double latitude,
    required double longitude,
    String? idKab,
    String? idKec,
    String? idDesa,
    String? fotoPath,
  });

  Future<List<TrackingStepModel>> getTracking(String kodeKejadian);

  Future<List<JenisBencanaModel>> getJenisBencana();

  Future<List<WilayahModel>> getKabupaten();

  Future<List<WilayahModel>> getKecamatan(String idKab);

  Future<List<WilayahModel>> getDesa(String idKec);

  Future<List<LaporanKejadianModel>> getPendingLaporan(Dio authDio);

  Future<bool> validasiLaporan(Dio authDio, int idLaporan, int idPcnu, String prioritas, String statusInsiden, String? alamatLengkap, double? latitude, double? longitude);

  Future<bool> tolakLaporan(Dio authDio, int idLaporan, String alasan, String catatan);
}


class LaporanRemoteDatasourceImpl implements LaporanRemoteDatasource {
  final Dio dio;

  LaporanRemoteDatasourceImpl(this.dio);

  @override
  Future<LaporanKejadianModel> createLaporan({
    required int idJenisBencana,
    required String namaPelapor,
    required String hpPelapor,
    required String keteranganSituasi,
    String? titikKenal,
    required DateTime waktuKejadian,
    required double latitude,
    required double longitude,
    String? idKab,
    String? idKec,
    String? idDesa,
    String? fotoPath,
  }) async {
    final formData = FormData.fromMap({
      'id_jenis_bencana': idJenisBencana,
      'nama_pelapor': namaPelapor,
      'hp_pelapor': hpPelapor,
      'keterangan_situasi': keteranganSituasi,
      'titik_kenal': titikKenal ?? '',
      'waktu_kejadian': waktuKejadian.toIso8601String(),
      'latitude': latitude.toString(),
      'longitude': longitude.toString(),
      if (idKab != null) 'id_kab': idKab,
      if (idKec != null) 'id_kec': idKec,
      if (idDesa != null) 'id_desa': idDesa,
      if (fotoPath != null)
        'foto': await MultipartFile.fromFile(
          fotoPath,
          filename: fotoPath.split('/').last,
        ),
    });

    final response = await dio.post(
      'lapor',
      data: formData,
      options: Options(
        headers: {'Content-Type': 'multipart/form-data'},
      ),
    );

    if (response.statusCode == 201 || response.statusCode == 200) {
      final data = response.data['data'] as Map<String, dynamic>;
      return LaporanKejadianModel.fromJson(data);
    }

    throw Exception(response.data?['message'] ?? 'Gagal mengirim laporan');
  }

  @override
  Future<List<TrackingStepModel>> getTracking(String kodeKejadian) async {
    final response = await dio.get('laporan/$kodeKejadian/tracking');

    if (response.statusCode == 200) {
      final List data = response.data['data'] ?? [];
      return data.map((json) => TrackingStepModel.fromJson(json)).toList();
    }

    throw Exception(response.data?['message'] ?? 'Laporan tidak ditemukan');
  }

  @override
  Future<List<JenisBencanaModel>> getJenisBencana() async {
    final response = await dio.get('master/jenis-bencana');

    if (response.statusCode == 200) {
      final List data = response.data['data'] is List ? response.data['data'] : [];
      return data.map((json) => JenisBencanaModel.fromJson(json)).toList();
    }

    throw Exception('Gagal memuat jenis bencana');
  }

  @override
  Future<List<WilayahModel>> getKabupaten() async {
    final response = await dio.get('wilayah/kabupaten');

    if (response.statusCode == 200) {
      final List data = response.data is List ? response.data : (response.data['data'] ?? []);
      return data.map((json) => WilayahModel.fromKabupatenJson(json)).toList();
    }

    throw Exception('Gagal memuat kabupaten');
  }

  @override
  Future<List<WilayahModel>> getKecamatan(String idKab) async {
    final response = await dio.get('wilayah/kecamatan', queryParameters: {'id_kab': idKab});

    if (response.statusCode == 200) {
      final List data = response.data is List ? response.data : (response.data['data'] ?? []);
      return data.map((json) => WilayahModel.fromKecamatanJson(json)).toList();
    }

    throw Exception('Gagal memuat kecamatan');
  }

  @override
  Future<List<WilayahModel>> getDesa(String idKec) async {
    final response = await dio.get('wilayah/desa', queryParameters: {'id_kec': idKec});

    if (response.statusCode == 200) {
      final List data = response.data is List ? response.data : (response.data['data'] ?? []);
      return data.map((json) => WilayahModel.fromDesaJson(json)).toList();
    }

    throw Exception('Gagal memuat desa');
  }

  @override
  Future<List<LaporanKejadianModel>> getPendingLaporan(Dio authDio) async {
    final response = await authDio.get('laporan', queryParameters: {'is_valid': 'menunggu'});
    if (response.statusCode == 200) {
      final List list = response.data['data'] ?? [];
      return list.map((json) => LaporanKejadianModel.fromJson(json)).toList();
    }
    throw Exception('Gagal memuat antrean laporan pending');
  }

  @override
  Future<bool> validasiLaporan(Dio authDio, int idLaporan, int idPcnu, String prioritas, String statusInsiden, String? alamatLengkap, double? latitude, double? longitude) async {
    // Hanya validasi laporan (ubah status is_valid menjadi 'ya') dan update lokasi.
    // Pembuatan insiden & penugasan TRC dilakukan terpisah dari TrcAssignmentScreen.
    final validasiResponse = await authDio.patch(
      'laporan/$idLaporan/validasi',
      data: {
        'is_valid': 'ya',
        if (alamatLengkap != null && alamatLengkap.isNotEmpty) 'alamat_lengkap': alamatLengkap,
        if (latitude != null && latitude != 0.0) 'latitude': latitude,
        if (longitude != null && longitude != 0.0) 'longitude': longitude,
        'id_pcnu': idPcnu,
      },
    );

    if (validasiResponse.statusCode != 200) {
      throw Exception(validasiResponse.data?['message'] ?? 'Gagal memvalidasi laporan');
    }

    return true;
  }

  @override
  Future<bool> tolakLaporan(Dio authDio, int idLaporan, String alasan, String catatan) async {
    final response = await authDio.post(
      'laporan/$idLaporan/validasi', // Validasi status tolak
      data: {
        'is_valid': 'tidak',
        'alasan_tolak': alasan,
        'catatan_validasi': catatan,
      },
    );
    return response.statusCode == 200;
  }
}

final laporanRemoteDatasourceProvider = Provider<LaporanRemoteDatasource>((ref) {
  return LaporanRemoteDatasourceImpl(ref.watch(publicApiClientProvider));
});

