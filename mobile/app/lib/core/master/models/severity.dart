class Severity {
  final String id;
  final String nama;
  final int skor;
  final String warna;

  const Severity({
    required this.id,
    required this.nama,
    required this.skor,
    required this.warna,
  });
}

class Prioritas {
  final String id;
  final String nama;
  final int skor;
  final String warna;

  const Prioritas({
    required this.id,
    required this.nama,
    required this.skor,
    required this.warna,
  });
}

class LevelRisiko {
  final String id;
  final String nama;
  final int skor;
  final String warna;

  const LevelRisiko({
    required this.id,
    required this.nama,
    required this.skor,
    required this.warna,
  });
}

class SkalaKejadian {
  final String id;
  final String nama;
  final int level;

  const SkalaKejadian({required this.id, required this.nama, required this.level});
}

class Satuan {
  final String id;
  final String nama;
  final String singkatan;

  const Satuan({required this.id, required this.nama, required this.singkatan});
}
