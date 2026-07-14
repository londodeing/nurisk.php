class LaporanKejadianEntity {
  final int id;
  final String kodeKejadian;
  final int idJenisBencana;
  final String namaPelapor;
  final String hpPelapor;
  final String keteranganSituasi;
  final String? titikKenal;
  final String? alamatLengkap;
  final DateTime waktuKejadian;
  final double latitude;
  final double longitude;
  final String? idKab;
  final String? idKec;
  final String? idDesa;
  final String? photoPath;
  final String? mediaUrl;
  final String isValid;
  final String? alasanTolak;
  final String? catatanValidasi;
  final int? idPcnu;
  final String? pcnu;
  final int? idPetugasTrc;
  final DateTime? dibuatPada;

  const LaporanKejadianEntity({
    required this.id,
    required this.kodeKejadian,
    required this.idJenisBencana,
    required this.namaPelapor,
    required this.hpPelapor,
    required this.keteranganSituasi,
    this.titikKenal,
    this.alamatLengkap,
    required this.waktuKejadian,
    required this.latitude,
    required this.longitude,
    this.idKab,
    this.idKec,
    this.idDesa,
    this.photoPath,
    this.mediaUrl,
    required this.isValid,
    this.alasanTolak,
    this.catatanValidasi,
    this.idPcnu,
    this.pcnu,
    this.idPetugasTrc,
    this.dibuatPada,
  });
}
