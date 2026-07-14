import 'dart:convert';
import 'package:flutter/services.dart';
import '../models/wilayah.dart';
import '../models/severity.dart';
import '../models/status.dart';
import '../models/master_data.dart';
import '../models/surat.dart';
import '../models/assessment.dart';
import '../models/display.dart';

class JsonMasterRepository {
  final Map<String, Object> _cache = {};

  Future<List<T>> _loadList<T>(String path, T Function(Map<String, dynamic>) fromJson) async {
    if (_cache.containsKey(path)) {
      return _cache[path] as List<T>;
    }
    final string = await rootBundle.loadString(path);
    final list = json.decode(string) as List<dynamic>;
    final result = list.map((e) => fromJson(e as Map<String, dynamic>)).toList();
    _cache[path] = result;
    return result;
  }

  Future<Map<String, dynamic>> _loadMap(String path) async {
    if (_cache.containsKey(path)) {
      return _cache[path] as Map<String, dynamic>;
    }
    final string = await rootBundle.loadString(path);
    final result = json.decode(string) as Map<String, dynamic>;
    _cache[path] = result;
    return result;
  }

  Future<List<JenisBencana>> getJenisBencana() => _loadList(
    'assets/master/bencana/jenis.json',
    (j) => JenisBencana(
      id: j['id'] as int,
      nama: j['nama'] as String,
      slug: j['slug'] as String,
      kategori: j['kategori'] as String,
      ikonMap: j['ikon_map'] as String,
    ),
  );

  Future<List<Severity>> getSeverity() => _loadList(
    'assets/master/severity/master.json',
    (j) => Severity(
      id: j['id'] as String,
      nama: j['nama'] as String,
      skor: j['skor'] as int,
      warna: j['warna'] as String,
    ),
  );

  Future<List<Prioritas>> getPrioritas() => _loadList(
    'assets/master/prioritas.json',
    (j) => Prioritas(
      id: j['id'] as String,
      nama: j['nama'] as String,
      skor: j['skor'] as int,
      warna: j['warna'] as String,
    ),
  );

  Future<List<StatusLaporan>> getStatusLaporan() => _loadList(
    'assets/master/status/laporan.json',
    (j) => StatusLaporan(
      id: j['id'] as String,
      nama: j['nama'] as String,
      warna: j['warna'] as String,
      urutan: j['urutan'] as int,
    ),
  );

  Future<List<StatusInsiden>> getStatusInsiden() => _loadList(
    'assets/master/status/insiden.json',
    (j) => StatusInsiden(
      id: j['id'] as String,
      nama: j['nama'] as String,
      warna: j['warna'] as String,
      urutan: j['urutan'] as int,
    ),
  );

  Future<List<StatusOperasi>> getStatusOperasi() => _loadList(
    'assets/master/status/operasi.json',
    (j) => StatusOperasi(
      id: j['id'] as String,
      nama: j['nama'] as String,
      warna: j['warna'] as String,
      urutan: j['urutan'] as int,
    ),
  );

  Future<List<LevelRisiko>> getLevelRisiko() => _loadList(
    'assets/master/level_risiko.json',
    (j) => LevelRisiko(
      id: j['id'] as String,
      nama: j['nama'] as String,
      skor: j['skor'] as int,
      warna: j['warna'] as String,
    ),
  );

  Future<List<SkalaKejadian>> getSkalaKejadian() => _loadList(
    'assets/master/skala_kejadian.json',
    (j) => SkalaKejadian(
      id: j['id'] as String,
      nama: j['nama'] as String,
      level: j['level'] as int,
    ),
  );

  Future<List<Satuan>> getSatuan() => _loadList(
    'assets/master/satuan.json',
    (j) => Satuan(
      id: j['id'] as String,
      nama: j['nama'] as String,
      singkatan: j['singkatan'] as String,
    ),
  );

  Future<List<Klaster>> getKlaster() => _loadList(
    'assets/master/klaster/data.json',
    (j) => Klaster(
      id: j['id'] as int,
      nama: j['nama'] as String,
      deskripsi: (j['deskripsi'] as String?) ?? '',
    ),
  );

  Future<List<IconCop>> getIconCop() async {
    if (_cache.containsKey('assets/master/icon_cop/data.json')) {
      return _cache['assets/master/icon_cop/data.json'] as List<IconCop>;
    }
    final map = await _loadMap('assets/master/icon_cop/data.json');
    final result = map.entries.map((e) => IconCop(slug: e.key, icon: e.value as String)).toList();
    _cache['assets/master/icon_cop/data.json'] = result;
    return result;
  }

  Future<Map<String, WarnaIndikator>> getWarnaIndikator() async {
    final cacheKey = 'assets/master/warna_indikator.json';
    if (_cache.containsKey(cacheKey)) {
      return _cache[cacheKey] as Map<String, WarnaIndikator>;
    }
    final map = await _loadMap(cacheKey);
    final result = map.map((key, value) {
      final v = value as Map<String, dynamic>;
      return MapEntry(key, WarnaIndikator(
        bg: v['bg'] as String,
        text: v['text'] as String,
        hex: v['hex'] as String,
      ));
    });
    _cache[cacheKey] = result;
    return result;
  }

  Future<List<ResourceJenis>> getResourceJenis() => _loadList(
    'assets/master/resource/jenis.json',
    (j) => ResourceJenis(id: j['id'] as String, nama: j['nama'] as String),
  );

  Future<List<KendaraanJenis>> getKendaraanJenis() => _loadList(
    'assets/master/kendaraan/jenis.json',
    (j) => KendaraanJenis(
      id: j['id'] as int,
      nama: j['nama'] as String,
      ikon: j['ikon'] as String,
    ),
  );

  Future<List<ShelterJenis>> getShelterJenis() => _loadList(
    'assets/master/shelter/jenis.json',
    (j) => ShelterJenis(
      id: j['id'] as int,
      nama: j['nama'] as String,
      kapasitas: j['kapasitas'] as int,
    ),
  );

  Future<List<LogistikJenis>> getLogistikJenis() => _loadList(
    'assets/master/logistik/jenis.json',
    (j) => LogistikJenis(id: j['id'] as int, nama: j['nama'] as String),
  );

  Future<List<RelawanJenis>> getRelawanJenis() => _loadList(
    'assets/master/relawan/jenis.json',
    (j) => RelawanJenis(
      id: j['id'] as int,
      nama: j['nama'] as String,
      deskripsi: (j['deskripsi'] as String?) ?? '',
    ),
  );

  Future<List<Keahlian>> getKeahlian() => _loadList(
    'assets/master/relawan/jenis.json',
    (j) => Keahlian(
      id: j['id'] as int,
      nama: j['nama'] as String,
      deskripsi: (j['deskripsi'] as String?) ?? '',
    ),
  );

  Future<List<SuratJenis>> getSuratJenis() => _loadList(
    'assets/master/surat/jenis.json',
    (j) => SuratJenis(
      id: j['id'] as int,
      kode: j['kode'] as String,
      nama: j['nama'] as String,
      kategori: j['kategori'] as String,
    ),
  );

  Future<List<JabatanTtd>> getJabatanTtd() => _loadList(
    'assets/master/surat/jabatan_ttd.json',
    (j) => JabatanTtd(
      id: j['id'] as int,
      nama: j['nama'] as String,
      urutan: j['urutan'] as int,
    ),
  );

  Future<List<ApprovalStatus>> getApprovalStatus() => _loadList(
    'assets/master/approval/status.json',
    (j) => ApprovalStatus(
      id: j['id'] as String,
      nama: j['nama'] as String,
      urutan: j['urutan'] as int,
      warna: j['warna'] as String,
    ),
  );

  Future<Map<String, List<String>>> getWorkflow() async {
    final cacheKey = 'assets/master/workflow/enum.json';
    if (_cache.containsKey(cacheKey)) {
      return _cache[cacheKey] as Map<String, List<String>>;
    }
    final map = await _loadMap(cacheKey);
    final result = map.map((key, value) => MapEntry(key, (value as List<dynamic>).cast<String>()));
    _cache[cacheKey] = result;
    return result;
  }

  Future<List<AssessmentIndikator>> getAssessmentIndikator() => _loadList(
    'assets/master/assessment/indikator.json',
    (j) => AssessmentIndikator(
      kode: j['kode'] as String,
      nama: j['nama'] as String,
      domain: j['domain'] as String,
      bobot: j['bobot'] as int,
      satuan: j['satuan'] as String,
    ),
  );

  Future<List<AssessmentKebutuhan>> getAssessmentKebutuhan() => _loadList(
    'assets/master/assessment/kebutuhan_numerik.json',
    (j) => AssessmentKebutuhan(
      kode: j['kode'] as String,
      nama: j['nama'] as String,
      satuan: j['satuan'] as String,
      kategori: j['kategori'] as String,
    ),
  );

  void clearCache() {
    _cache.clear();
  }
}
