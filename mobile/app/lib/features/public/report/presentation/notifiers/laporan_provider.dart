import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/services/master_data/master_data_service.dart';
import 'package:nurisk_mobile/core/storage/master/master_database.dart';
import '../../data/models/jenis_bencana_model.dart';
import '../../data/models/wilayah_model.dart';

final jenisBencanaListProvider = FutureProvider.autoDispose<List<JenisBencanaModel>>((ref) async {
  final list = await ref.read(jenisBencanaMasterProvider.future);
  return list.map((e) => JenisBencanaModel(id: e.id, nama: e.nama)).toList();
});

final kabupatenListProvider = FutureProvider.autoDispose<List<WilayahModel>>((ref) async {
  await ref.read(masterDbInitProvider.future);
  final list = await ref.read(kabupatenLocalProvider.future);
  return list.map((e) => WilayahModel(id: e.idKab, nama: e.namaKab)).toList();
});

final kecamatanListProvider = FutureProvider.autoDispose.family<List<WilayahModel>, String>((ref, idKab) async {
  await ref.read(masterDbInitProvider.future);
  final list = await ref.read(kecamatanLocalProvider(idKab).future);
  return list.map((e) => WilayahModel(id: e.idKec, nama: e.namaKec)).toList();
});

final desaListProvider = FutureProvider.autoDispose.family<List<WilayahModel>, String>((ref, idKec) async {
  await ref.read(masterDbInitProvider.future);
  final list = await ref.read(desaLocalProvider(idKec).future);
  return list.map((e) => WilayahModel(id: e.idDesa, nama: e.namaDesa)).toList();
});
