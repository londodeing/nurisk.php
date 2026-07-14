import '../entities/laporan_kejadian_entity.dart';

abstract class LaporanRepository {
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
  });
}
