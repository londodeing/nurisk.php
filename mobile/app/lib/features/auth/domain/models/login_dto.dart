class LoginDto {
  final String noHp;
  final String password;

  const LoginDto({
    required this.noHp,
    required this.password,
  });

  Map<String, dynamic> toJson() {
    return {
      'no_hp': noHp,
      'kata_sandi': password,
    };
  }
}
