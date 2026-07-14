class AssessmentIndikator {
  final String kode;
  final String nama;
  final String domain;
  final int bobot;
  final String satuan;

  const AssessmentIndikator({
    required this.kode,
    required this.nama,
    required this.domain,
    required this.bobot,
    required this.satuan,
  });
}

class AssessmentKebutuhan {
  final String kode;
  final String nama;
  final String satuan;
  final String kategori;

  const AssessmentKebutuhan({
    required this.kode,
    required this.nama,
    required this.satuan,
    required this.kategori,
  });
}
