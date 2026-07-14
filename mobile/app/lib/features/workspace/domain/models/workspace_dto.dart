class WorkspaceDto {
  final Map<String, dynamic>? profil;
  final Map<String, dynamic>? jabatanAktif;
  final List<dynamic> keahlian;
  final List<dynamic> penugasan;
  final List<dynamic>? commandCenter;
  final List<dynamic>? alertInsiden;
  final int version;

  const WorkspaceDto({
    this.profil,
    this.jabatanAktif,
    this.keahlian = const [],
    this.penugasan = const [],
    this.commandCenter,
    this.alertInsiden,
    required this.version,
  });

  factory WorkspaceDto.fromJson(Map<String, dynamic> json) {
    return WorkspaceDto(
      profil: json['profil'] as Map<String, dynamic>?,
      jabatanAktif: json['jabatan_aktif'] as Map<String, dynamic>?,
      keahlian: json['keahlian'] as List<dynamic>? ?? [],
      penugasan: json['penugasan'] as List<dynamic>? ?? [],
      commandCenter: json['command_center'] as List<dynamic>?,
      alertInsiden: json['alert_insiden'] as List<dynamic>?,
      version: json['version'] as int? ?? 0,
    );
  }
}
