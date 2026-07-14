class AuthUserModel {
  final int id;
  final String noHp;
  final bool isTersedia;
  final String statusAkun;
  final String? namaLengkap;
  final String? namaPeran;
  final String? defaultScopeType;
  final int? defaultScopeId;

  const AuthUserModel({
    required this.id,
    required this.noHp,
    required this.isTersedia,
    required this.statusAkun,
    this.namaLengkap,
    this.namaPeran,
    this.defaultScopeType,
    this.defaultScopeId,
  });

  factory AuthUserModel.fromJson(Map<String, dynamic> json) {
    return AuthUserModel(
      id: json['id_pengguna'] as int,
      noHp: json['no_hp'] as String,
      isTersedia: json['is_tersedia'] == true || json['is_tersedia'] == 1,
      statusAkun: json['status_akun'] as String? ?? 'menunggu',
      namaLengkap: json['profil']?['nama_lengkap'] as String?,
      namaPeran: json['peran']?['nama_peran'] as String?,
      defaultScopeType: json['default_scope_type'] as String?,
      defaultScopeId: json['default_scope_id'] as int?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id_pengguna': id,
      'no_hp': noHp,
      'is_tersedia': isTersedia,
      'status_akun': statusAkun,
      'default_scope_type': defaultScopeType,
      'default_scope_id': defaultScopeId,
      'profil': {
        'nama_lengkap': namaLengkap,
      },
      'peran': {
        'nama_peran': namaPeran,
      },
    };
  }
}
