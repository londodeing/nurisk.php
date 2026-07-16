class PlenoModel {
  final int idPleno;
  final int idInsiden;
  final String? nomorPleno;
  final String jenisPleno;
  final String? lokasiPleno;
  final String statusPleno;
  final DateTime? waktuPleno;
  final DateTime? waktuDifinalisasi;
  final int? pimpinanPleno;
  final int? notulisPleno;

  final String? namaPimpinan;
  final String? namaNotulis;

  final List<PlenoKeputusanModel>? keputusan;
  final List<PlenoPesertaModel>? peserta;

  PlenoModel({
    required this.idPleno,
    required this.idInsiden,
    this.nomorPleno,
    required this.jenisPleno,
    this.lokasiPleno,
    required this.statusPleno,
    this.waktuPleno,
    this.waktuDifinalisasi,
    this.pimpinanPleno,
    this.notulisPleno,
    this.namaPimpinan,
    this.namaNotulis,
    this.keputusan,
    this.peserta,
  });

  factory PlenoModel.fromJson(Map<String, dynamic> json) {
    return PlenoModel(
      idPleno: json['id_pleno'] as int,
      idInsiden: json['id_insiden'] as int,
      nomorPleno: json['nomor_pleno'] as String?,
      jenisPleno: json['jenis_pleno'] ?? 'unknown',
      lokasiPleno: json['lokasi_pleno'] as String?,
      statusPleno: json['status_pleno'] ?? 'draft',
      waktuPleno: json['waktu_pleno'] != null ? DateTime.tryParse(json['waktu_pleno']) : null,
      waktuDifinalisasi: json['waktu_difinalisasi'] != null ? DateTime.tryParse(json['waktu_difinalisasi']) : null,
      pimpinanPleno: json['pimpinan_pleno'] as int?,
      notulisPleno: json['notulis_pleno'] as int?,
      namaPimpinan: json['pimpinan']?['profil']?['nama_lengkap'] ?? json['pimpinan']?['username'],
      namaNotulis: json['notulis']?['profil']?['nama_lengkap'] ?? json['notulis']?['username'],
      keputusan: (json['keputusan'] as List<dynamic>?)
          ?.map((e) => PlenoKeputusanModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      peserta: (json['peserta'] as List<dynamic>?)
          ?.map((e) => PlenoPesertaModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class PlenoKeputusanModel {
  final int idKeputusan;
  final int idPleno;
  final String deskripsiKeputusan;
  final String? kategoriObjek;
  final String? statusPelaksanaan;

  PlenoKeputusanModel({
    required this.idKeputusan,
    required this.idPleno,
    required this.deskripsiKeputusan,
    this.kategoriObjek,
    this.statusPelaksanaan,
  });

  factory PlenoKeputusanModel.fromJson(Map<String, dynamic> json) {
    return PlenoKeputusanModel(
      idKeputusan: json['id_keputusan'] as int,
      idPleno: json['id_pleno'] as int,
      deskripsiKeputusan: json['deskripsi_keputusan'] ?? '',
      kategoriObjek: json['kategori_objek'] as String?,
      statusPelaksanaan: json['status_pelaksanaan'] as String?,
    );
  }
}

class PlenoPesertaModel {
  final int idPeserta;
  final int idPleno;
  final int idPengguna;
  final String? peranDalamRapat;
  final String? statusKehadiran;
  final String? namaLengkap;
  final String? username;

  PlenoPesertaModel({
    required this.idPeserta,
    required this.idPleno,
    required this.idPengguna,
    this.peranDalamRapat,
    this.statusKehadiran,
    this.namaLengkap,
    this.username,
  });

  factory PlenoPesertaModel.fromJson(Map<String, dynamic> json) {
    return PlenoPesertaModel(
      idPeserta: json['id_peserta_pleno'] ?? json['id_peserta'] ?? 0,
      idPleno: json['id_pleno'] as int,
      idPengguna: json['id_pengguna'] as int,
      peranDalamRapat: json['peran_dalam_rapat'] as String?,
      statusKehadiran: json['status_kehadiran'] as String?,
      namaLengkap: json['pengguna']?['profil']?['nama_lengkap'],
      username: json['pengguna']?['username'],
    );
  }
}
