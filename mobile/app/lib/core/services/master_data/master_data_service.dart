import 'dart:convert';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'models/jenis_bencana_model.dart';
import 'models/wilayah_model.dart';

// DEPRECATED: Gunakan core/master/providers/master_providers.dart
// Akan dihapus di Phase 2.5B

@Deprecated('Gunakan jsonMasterRepositoryProvider dari core/master')
final jsonMasterLoaderProvider = Provider<JsonMasterLoader>((ref) {
  return JsonMasterLoader();
});

@Deprecated('Gunakan jenisBencanaProvider dari core/master')
final jenisBencanaMasterProvider = FutureProvider<List<JenisBencanaMasterModel>>((ref) async {
  final loader = ref.watch(jsonMasterLoaderProvider);
  return loader.loadJenisBencana();
});

@Deprecated('Gunakan kabupatenProvider dari core/master')
final kabupatenMasterProvider = FutureProvider<List<WilayahMasterModel>>((ref) async {
  final loader = ref.watch(jsonMasterLoaderProvider);
  return loader.loadKabupaten();
});

@Deprecated('Gunakan kecamatanProvider dari core/master')
final kecamatanMasterProvider = FutureProvider.family<List<WilayahMasterModel>, String>((ref, idKab) async {
  final loader = ref.watch(jsonMasterLoaderProvider);
  return loader.loadKecamatan(idKab);
});

@Deprecated('Gunakan desaProvider dari core/master')
final desaMasterProvider = FutureProvider.family<List<WilayahMasterModel>, String>((ref, idKec) async {
  final loader = ref.watch(jsonMasterLoaderProvider);
  return loader.loadDesa(idKec);
});

@Deprecated('Gunakan JsonMasterRepository dari core/master')
class JsonMasterLoader {
  List<Map<String, dynamic>>? _cachedKabupaten;
  List<Map<String, dynamic>>? _cachedKecamatan;
  List<Map<String, dynamic>>? _cachedDesa;

  Future<List<JenisBencanaMasterModel>> loadJenisBencana() async {
    final string = await rootBundle.loadString('assets/master/bencana/jenis.json');
    final list = json.decode(string) as List<dynamic>;
    return list.map((e) => JenisBencanaMasterModel.fromJson(e)).toList();
  }

  Future<List<WilayahMasterModel>> loadKabupaten() async {
    if (_cachedKabupaten != null) {
      return _cachedKabupaten!.map((e) => WilayahMasterModel.fromKabupatenJson(e)).toList();
    }
    final string = await rootBundle.loadString('assets/master/wilayah/kabupaten.json');
    final list = json.decode(string) as List<dynamic>;
    _cachedKabupaten = list.cast<Map<String, dynamic>>();
    return _cachedKabupaten!.map((e) => WilayahMasterModel.fromKabupatenJson(e)).toList();
  }

  Future<List<WilayahMasterModel>> loadKecamatan(String idKab) async {
    if (_cachedKecamatan == null) {
      final string = await rootBundle.loadString('assets/master/wilayah/kecamatan.json');
      final list = json.decode(string) as List<dynamic>;
      _cachedKecamatan = list.cast<Map<String, dynamic>>();
    }
    final filtered = _cachedKecamatan!.where((e) => e['id_kab'] == idKab).toList();
    return filtered.map((e) => WilayahMasterModel.fromKecamatanJson(e)).toList();
  }

  Future<List<WilayahMasterModel>> loadDesa(String idKec) async {
    if (_cachedDesa == null) {
      final string = await rootBundle.loadString('assets/master/wilayah/desa.json');
      final list = json.decode(string) as List<dynamic>;
      _cachedDesa = list.cast<Map<String, dynamic>>();
    }
    final filtered = _cachedDesa!.where((e) => e['id_kec'] == idKec).toList();
    return filtered.map((e) => WilayahMasterModel.fromDesaJson(e)).toList();
  }
}
