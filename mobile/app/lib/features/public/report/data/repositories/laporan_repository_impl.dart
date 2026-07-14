import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/laporan_kejadian_entity.dart';
import '../../domain/repositories/laporan_repository.dart';
import '../datasources/laporan_remote_datasource.dart';

class LaporanRepositoryImpl implements LaporanRepository {
  final LaporanRemoteDatasource remoteDatasource;

  LaporanRepositoryImpl(this.remoteDatasource);

  @override
  Future<LaporanKejadianEntity> createLaporan({
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
  }) {
    return remoteDatasource.createLaporan(
      idJenisBencana: idJenisBencana,
      namaPelapor: namaPelapor,
      hpPelapor: hpPelapor,
      keteranganSituasi: keteranganSituasi,
      titikKenal: titikKenal,
      waktuKejadian: waktuKejadian,
      latitude: latitude,
      longitude: longitude,
      idKab: idKab,
      idKec: idKec,
      idDesa: idDesa,
      fotoPath: fotoPath,
    );
  }
}

final laporanRepositoryProvider = Provider<LaporanRepository>((ref) {
  return LaporanRepositoryImpl(ref.watch(laporanRemoteDatasourceProvider));
});
