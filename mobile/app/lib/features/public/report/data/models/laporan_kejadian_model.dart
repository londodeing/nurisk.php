import '../../domain/entities/laporan_kejadian_entity.dart';

class LaporanKejadianModel extends LaporanKejadianEntity {
  const LaporanKejadianModel({
    required super.id,
    required super.kodeKejadian,
    required super.idJenisBencana,
    required super.namaPelapor,
    required super.hpPelapor,
    required super.keteranganSituasi,
    super.titikKenal,
    super.alamatLengkap,
    required super.waktuKejadian,
    required super.latitude,
    required super.longitude,
    super.idKab,
    super.idKec,
    super.idDesa,
    super.photoPath,
    super.mediaUrl,
    required super.isValid,
    super.alasanTolak,
    super.catatanValidasi,
    super.idPcnu,
    super.pcnu,
    super.idPetugasTrc,
    super.dibuatPada,
  });

  factory LaporanKejadianModel.fromJson(Map<String, dynamic> json) {
    return LaporanKejadianModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '') ?? 0,
      kodeKejadian: json['kode_kejadian'] ?? '',
      idJenisBencana: json['id_jenis_bencana'] is int
          ? json['id_jenis_bencana']
          : int.tryParse(json['id_jenis_bencana']?.toString() ?? '0') ?? 0,
      namaPelapor: json['nama_pelapor'] ?? '',
      hpPelapor: json['hp_pelapor'] ?? '',
      keteranganSituasi: json['keterangan_situasi'] ?? json['keterangan'] ?? '',
      titikKenal: json['titik_kenal'],
      alamatLengkap: json['alamat_lengkap'],
      waktuKejadian: DateTime.tryParse(json['waktu_kejadian'] ?? json['waktu'] ?? '') ?? DateTime.now(),
      latitude: (json['latitude'] is double
          ? json['latitude']
          : double.tryParse(json['latitude']?.toString() ?? '0')) ?? 0.0,
      longitude: (json['longitude'] is double
          ? json['longitude']
          : double.tryParse(json['longitude']?.toString() ?? '0')) ?? 0.0,
      idKab: json['id_kab'],
      idKec: json['id_kec'],
      idDesa: json['id_desa'],
      photoPath: json['photo_path'] ?? json['photo'],
      mediaUrl: json['media_url'],
      isValid: json['is_valid'] ?? 'menunggu',
      alasanTolak: json['alasan_tolak'],
      catatanValidasi: json['catatan_validasi'],
      idPcnu: json['id_pcnu'] is int ? json['id_pcnu'] : int.tryParse(json['id_pcnu']?.toString() ?? ''),
      pcnu: json['pcnu'],
      idPetugasTrc: json['id_petugas_trc'] is int
          ? json['id_petugas_trc']
          : int.tryParse(json['id_petugas_trc']?.toString() ?? ''),
      dibuatPada: DateTime.tryParse(json['dibuat_pada'] ?? ''),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id_jenis_bencana': idJenisBencana,
      'nama_pelapor': namaPelapor,
      'hp_pelapor': hpPelapor,
      'keterangan_situasi': keteranganSituasi,
      'titik_kenal': titikKenal,
      'waktu_kejadian': waktuKejadian.toIso8601String(),
      'id_kab': idKab,
      'id_kec': idKec,
      'id_desa': idDesa,
      'latitude': latitude,
      'longitude': longitude,
    };
  }
}
