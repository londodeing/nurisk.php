import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../master/models/wilayah.dart';
import '../../master/repositories/sqlite_master_repository.dart';
import '../../master/providers/master_providers.dart' show sqliteMasterInitProvider, sqliteMasterRepositoryProvider;

// DEPRECATED: Gunakan core/master/providers/master_providers.dart → kabupatenProvider, kecamatanProvider, desaProvider
// Akan dihapus di Phase 2.5B

@Deprecated('Gunakan Kabupaten dari core/master/models/wilayah.dart')
class KabupatenData {
  final String idKab;
  final String namaKab;
  KabupatenData({required this.idKab, required this.namaKab});
}

@Deprecated('Gunakan Kecamatan dari core/master/models/wilayah.dart')
class KecamatanData {
  final String idKec;
  final String idKab;
  final String namaKec;
  KecamatanData({required this.idKec, required this.idKab, required this.namaKec});
}

@Deprecated('Gunakan Desa dari core/master/models/wilayah.dart')
class DesaData {
  final String idDesa;
  final String idKec;
  final String namaDesa;
  DesaData({required this.idDesa, required this.idKec, required this.namaDesa});
}

@Deprecated('Gunakan SQLiteMasterRepository dari core/master/repositories')
class MasterDatabase {
  Future<void> init() async {
    // Delegated to SQLiteMasterRepository via init provider
  }

  Future<List<KabupatenData>> getKabupaten() async {
    // Will be removed — use kabupatenProvider from core/master
    return [];
  }

  Future<List<KecamatanData>> getKecamatan(String idKab) async {
    return [];
  }

  Future<List<DesaData>> getDesa(String idKec) async {
    return [];
  }
}

@Deprecated('Gunakan sqliteMasterInitProvider dari core/master/providers')
final masterDbInitProvider = sqliteMasterInitProvider;

@Deprecated('Gunakan kabupatenProvider dari core/master/providers')
final kabupatenLocalProvider = FutureProvider<List<KabupatenData>>((ref) async {
  await ref.read(sqliteMasterInitProvider.future);
  final list = await ref.read(sqliteMasterRepositoryProvider).getKabupaten();
  return list.map((e) => KabupatenData(idKab: e.idKab, namaKab: e.namaKab)).toList();
});

@Deprecated('Gunakan kecamatanProvider dari core/master/providers')
final kecamatanLocalProvider = FutureProvider.family<List<KecamatanData>, String>((ref, idKab) async {
  await ref.read(sqliteMasterInitProvider.future);
  final list = await ref.read(sqliteMasterRepositoryProvider).getKecamatan(idKab);
  return list.map((e) => KecamatanData(idKec: e.idKec, idKab: e.idKab, namaKec: e.namaKec)).toList();
});

@Deprecated('Gunakan desaProvider dari core/master/providers')
final desaLocalProvider = FutureProvider.family<List<DesaData>, String>((ref, idKec) async {
  await ref.read(sqliteMasterInitProvider.future);
  final list = await ref.read(sqliteMasterRepositoryProvider).getDesa(idKec);
  return list.map((e) => DesaData(idDesa: e.idDesa, idKec: e.idKec, namaDesa: e.namaDesa)).toList();
});
