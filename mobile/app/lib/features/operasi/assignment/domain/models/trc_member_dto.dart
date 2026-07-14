class TrcMemberDto {
  final int idPengguna;
  final String namaLengkap;
  final String alamatLengkap;
  final String noHp;
  final String statusKetersediaan;

  TrcMemberDto({
    required this.idPengguna,
    required this.namaLengkap,
    required this.alamatLengkap,
    required this.noHp,
    required this.statusKetersediaan,
  });

  factory TrcMemberDto.fromJson(Map<String, dynamic> json) {
    return TrcMemberDto(
      idPengguna: json['id_pengguna'] ?? 0,
      namaLengkap: json['nama_lengkap'] ?? json['no_hp'] ?? 'Tidak diketahui',
      alamatLengkap: json['alamat_lengkap'] ?? 'Alamat belum diisi',
      noHp: json['no_hp'] ?? '',
      statusKetersediaan: json['status_ketersediaan'] ?? 'unknown',
    );
  }
}
