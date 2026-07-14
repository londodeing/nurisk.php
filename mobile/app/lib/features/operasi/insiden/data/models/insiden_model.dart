class InsidenModel {
  final int id;
  final String uuid;
  final String kode;
  final String status;
  final String labelStatus;
  final String prioritas;
  final bool isLocked;
  final String? jenisBencana;
  final String? pcnu;
  final int? idPcnu;
  final String? noSpkAssesment;
  final String? tglSpkAssesment;
  final DateTime? waktuMulai;
  final DateTime? waktuSelesai;
  final DateTime? dibuatPada;
  final List<RiwayatStatusModel> riwayatStatus;
  final LaporanAsalModel? laporanAsal;
  final List<PenugasanModel> penugasan;
  final List<AssessmentSummaryModel> assessments;
  final List<JurnalModel> jurnal;

  InsidenModel({
    required this.id,
    required this.uuid,
    required this.kode,
    required this.status,
    required this.labelStatus,
    required this.prioritas,
    required this.isLocked,
    this.jenisBencana,
    this.pcnu,
    this.idPcnu,
    this.noSpkAssesment,
    this.tglSpkAssesment,
    this.waktuMulai,
    this.waktuSelesai,
    this.dibuatPada,
    this.riwayatStatus = const [],
    this.laporanAsal,
    this.penugasan = const [],
    this.assessments = const [],
    this.jurnal = const [],
  });

  factory InsidenModel.fromJson(Map<String, dynamic> json) {
    // pcnu: API list returns string, detail returns {'id': x, 'nama': y}
    final pcnuRaw = json['pcnu'];
    final String? pcnuNama = pcnuRaw is Map ? pcnuRaw['nama'] : (pcnuRaw as String?);
    final int? pcnuId = pcnuRaw is Map ? (pcnuRaw['id'] is int ? pcnuRaw['id'] : int.tryParse(pcnuRaw['id']?.toString() ?? '')) : null;

    // jenis_bencana: can be string or Map
    final jbRaw = json['jenis_bencana'];
    final String? jbNama = jbRaw is Map ? jbRaw['nama'] : (jbRaw as String?);

    return InsidenModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '') ?? 0,
      uuid: json['uuid'] ?? '',
      kode: json['kode'] ?? '',
      status: json['status'] ?? 'draft',
      labelStatus: json['label_status'] ?? json['status'] ?? 'Draft',
      prioritas: json['prioritas'] ?? 'sedang',
      isLocked: json['is_locked'] == true || json['is_locked'] == 1,
      jenisBencana: jbNama,
      pcnu: pcnuNama,
      idPcnu: pcnuId ?? (json['id_pcnu'] is int ? json['id_pcnu'] : int.tryParse(json['id_pcnu']?.toString() ?? '')),
      noSpkAssesment: json['no_spk_assesment'],
      tglSpkAssesment: json['tgl_spk_assesment'],
      waktuMulai: json['waktu_mulai'] != null ? DateTime.tryParse(json['waktu_mulai']) : null,
      waktuSelesai: json['waktu_selesai'] != null ? DateTime.tryParse(json['waktu_selesai']) : null,
      dibuatPada: json['dibuat_pada'] != null ? DateTime.tryParse(json['dibuat_pada']) : null,
      riwayatStatus: (json['riwayat_status'] as List? ?? [])
          .map((e) => RiwayatStatusModel.fromJson(e))
          .toList(),
      laporanAsal: json['laporan_asal'] != null ? LaporanAsalModel.fromJson(json['laporan_asal']) : null,
      penugasan: (json['penugasan'] as List? ?? [])
          .map((e) => PenugasanModel.fromJson(e))
          .toList(),
      assessments: (json['assessments'] as List? ?? [])
          .map((e) => AssessmentSummaryModel.fromJson(e))
          .toList(),
      jurnal: (json['jurnal'] as List? ?? [])
          .map((e) => JurnalModel.fromJson(e))
          .toList(),
    );
  }

  bool get hasSuratTugas => noSpkAssesment != null && noSpkAssesment!.isNotEmpty;

  List<String> get allowedTransitions {
    const transitions = {
      'draft': ['terverifikasi'],
      'terverifikasi': ['respon', 'dibatalkan'],
      'respon': ['pemulihan', 'dibatalkan'],
      'pemulihan': ['selesai', 'dibatalkan'],
      'selesai': <String>[],
      'dibatalkan': <String>[],
    };
    return transitions[status] ?? [];
  }
}

class RiwayatStatusModel {
  final String status;
  final String? pengubah;
  final DateTime? waktu;
  final String? alasan;

  const RiwayatStatusModel({
    required this.status,
    this.pengubah,
    this.waktu,
    this.alasan,
  });

  factory RiwayatStatusModel.fromJson(Map<String, dynamic> json) {
    return RiwayatStatusModel(
      status: json['status'] ?? json['status_baru'] ?? '',
      pengubah: json['pengubah'] ?? json['pengguna'],
      waktu: json['waktu'] != null ? DateTime.tryParse(json['waktu']) : null,
      alasan: json['alasan'],
    );
  }
}

class LaporanAsalModel {
  final int id;
  final String kode;
  final String namaPelapor;
  final String hpPelapor;
  final String keteranganSituasi;
  final String? titikKenal;
  final String? alamatLengkap;
  final double? latitude;
  final double? longitude;
  final DateTime? waktuKejadian;
  final String? photoPath;
  final String? mediaUrl;

  const LaporanAsalModel({
    required this.id,
    required this.kode,
    required this.namaPelapor,
    required this.hpPelapor,
    required this.keteranganSituasi,
    this.titikKenal,
    this.alamatLengkap,
    this.latitude,
    this.longitude,
    this.waktuKejadian,
    this.photoPath,
    this.mediaUrl,
  });

  factory LaporanAsalModel.fromJson(Map<String, dynamic> json) {
    return LaporanAsalModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '') ?? 0,
      kode: json['kode_kejadian'] ?? json['kode'] ?? '',
      namaPelapor: json['nama_pelapor'] ?? '',
      hpPelapor: json['hp_pelapor'] ?? '',
      keteranganSituasi: json['keterangan_situasi'] ?? '',
      titikKenal: json['titik_kenal'],
      alamatLengkap: json['alamat_lengkap'],
      latitude: (json['latitude'] is double) ? json['latitude'] : double.tryParse(json['latitude']?.toString() ?? ''),
      longitude: (json['longitude'] is double) ? json['longitude'] : double.tryParse(json['longitude']?.toString() ?? ''),
      waktuKejadian: json['waktu_kejadian'] != null ? DateTime.tryParse(json['waktu_kejadian']) : null,
      photoPath: json['photo_path'],
      mediaUrl: json['media_url'],
    );
  }
}

class PenugasanModel {
  final int id;
  final String namaPersonel;
  final String peranOtoritas;
  final String statusPenugasan;
  final DateTime? waktuMulai;

  const PenugasanModel({
    required this.id,
    required this.namaPersonel,
    required this.peranOtoritas,
    required this.statusPenugasan,
    this.waktuMulai,
  });

  factory PenugasanModel.fromJson(Map<String, dynamic> json) {
    return PenugasanModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '') ?? 0,
      namaPersonel: json['nama_personel'] ?? json['pengguna'] ?? '-',
      peranOtoritas: json['peran_otoritas'] ?? 'trc',
      statusPenugasan: json['status_penugasan'] ?? 'assigned',
      waktuMulai: json['waktu_mulai'] != null ? DateTime.tryParse(json['waktu_mulai']) : null,
    );
  }
}

class AssessmentSummaryModel {
  final int id;
  final String uuid;
  final bool isLatest;
  final DateTime? waktuAssesment;
  final String? namaPetugas;

  const AssessmentSummaryModel({
    required this.id,
    required this.uuid,
    required this.isLatest,
    this.waktuAssesment,
    this.namaPetugas,
  });

  factory AssessmentSummaryModel.fromJson(Map<String, dynamic> json) {
    return AssessmentSummaryModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '') ?? 0,
      uuid: json['uuid'] ?? '',
      isLatest: json['is_latest'] == true || json['is_latest'] == 1,
      waktuAssesment: json['waktu_assesment'] != null ? DateTime.tryParse(json['waktu_assesment']) : null,
      namaPetugas: json['nama_petugas'] ?? json['petugas'],
    );
  }
}

class JurnalModel {
  final String judulEvent;
  final String? deskripsiEvent;
  final String? kategoriEvent;
  final DateTime? dibuatPada;

  const JurnalModel({
    required this.judulEvent,
    this.deskripsiEvent,
    this.kategoriEvent,
    this.dibuatPada,
  });

  factory JurnalModel.fromJson(Map<String, dynamic> json) {
    return JurnalModel(
      judulEvent: json['judul_event'] ?? '',
      deskripsiEvent: json['deskripsi_event'],
      kategoriEvent: json['kategori_event'],
      dibuatPada: json['dibuat_pada'] != null ? DateTime.tryParse(json['dibuat_pada']) : null,
    );
  }
}
