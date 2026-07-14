class JenisBencana {
  final int id;
  final String nama;
  final String slug;
  final String kategori;
  final String ikonMap;

  const JenisBencana({
    required this.id,
    required this.nama,
    required this.slug,
    required this.kategori,
    required this.ikonMap,
  });
}

class Kabupaten {
  final String idKab;
  final String namaKab;

  const Kabupaten({required this.idKab, required this.namaKab});
}

class Kecamatan {
  final String idKec;
  final String idKab;
  final String namaKec;

  const Kecamatan({
    required this.idKec,
    required this.idKab,
    required this.namaKec,
  });
}

class Desa {
  final String idDesa;
  final String idKec;
  final String namaDesa;

  const Desa({
    required this.idDesa,
    required this.idKec,
    required this.namaDesa,
  });
}

class Keahlian {
  final int id;
  final String nama;
  final String deskripsi;

  const Keahlian({required this.id, required this.nama, required this.deskripsi});
}

class Pcnu {
  final int id;
  final String nama;

  const Pcnu({required this.id, required this.nama});
}
