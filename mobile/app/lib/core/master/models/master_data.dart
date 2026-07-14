class Klaster {
  final int id;
  final String nama;
  final String deskripsi;

  const Klaster({required this.id, required this.nama, required this.deskripsi});
}

class ResourceJenis {
  final String id;
  final String nama;

  const ResourceJenis({required this.id, required this.nama});
}

class KendaraanJenis {
  final int id;
  final String nama;
  final String ikon;

  const KendaraanJenis({required this.id, required this.nama, required this.ikon});
}

class ShelterJenis {
  final int id;
  final String nama;
  final int kapasitas;

  const ShelterJenis({required this.id, required this.nama, required this.kapasitas});
}

class LogistikJenis {
  final int id;
  final String nama;

  const LogistikJenis({required this.id, required this.nama});
}

class RelawanJenis {
  final int id;
  final String nama;
  final String deskripsi;

  const RelawanJenis({required this.id, required this.nama, required this.deskripsi});
}
