class SuratJenis {
  final int id;
  final String kode;
  final String nama;
  final String kategori;

  const SuratJenis({
    required this.id,
    required this.kode,
    required this.nama,
    required this.kategori,
  });
}

class JabatanTtd {
  final int id;
  final String nama;
  final int urutan;

  const JabatanTtd({required this.id, required this.nama, required this.urutan});
}
