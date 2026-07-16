import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';
import '../master_repository.dart';
import '../master_repository_impl.dart';
import '../repositories/json_master_repository.dart';
import '../repositories/sqlite_master_repository.dart';
import '../repositories/organization_repository.dart';
import '../models/wilayah.dart';
import '../models/severity.dart';
import '../models/status.dart';
import '../models/master_data.dart';
import '../models/surat.dart';
import '../models/assessment.dart';
import '../models/display.dart';
import '../models/kebutuhan_numerik.dart';

// === Core Repository Providers ===

final jsonMasterRepositoryProvider = Provider<JsonMasterRepository>((ref) {
  return JsonMasterRepository();
});

final sqliteMasterRepositoryProvider = Provider<SQLiteMasterRepository>((ref) {
  return SQLiteMasterRepository();
});

final sqliteMasterInitProvider = FutureProvider<void>((ref) async {
  await ref.read(sqliteMasterRepositoryProvider).init();
});

final organizationRepositoryProvider = Provider<OrganizationRepository>((ref) {
  return OrganizationRepository(ref.watch(publicApiClientProvider));
});

final masterRepositoryProvider = Provider<MasterRepository>((ref) {
  return MasterRepositoryImpl(
    jsonRepo: ref.watch(jsonMasterRepositoryProvider),
    sqliteRepo: ref.watch(sqliteMasterRepositoryProvider),
    orgRepo: ref.watch(organizationRepositoryProvider),
  );
});

// === Data Providers — Tier A (JSON) ===

final jenisBencanaProvider = FutureProvider<List<JenisBencana>>((ref) {
  return ref.read(masterRepositoryProvider).getJenisBencana();
});

final severityProvider = FutureProvider<List<Severity>>((ref) {
  return ref.read(masterRepositoryProvider).getSeverity();
});

final prioritasProvider = FutureProvider<List<Prioritas>>((ref) {
  return ref.read(masterRepositoryProvider).getPrioritas();
});

final statusLaporanProvider = FutureProvider<List<StatusLaporan>>((ref) {
  return ref.read(masterRepositoryProvider).getStatusLaporan();
});

final statusInsidenProvider = FutureProvider<List<StatusInsiden>>((ref) {
  return ref.read(masterRepositoryProvider).getStatusInsiden();
});

final statusOperasiProvider = FutureProvider<List<StatusOperasi>>((ref) {
  return ref.read(masterRepositoryProvider).getStatusOperasi();
});

final levelRisikoProvider = FutureProvider<List<LevelRisiko>>((ref) {
  return ref.read(masterRepositoryProvider).getLevelRisiko();
});

final skalaKejadianProvider = FutureProvider<List<SkalaKejadian>>((ref) {
  return ref.read(masterRepositoryProvider).getSkalaKejadian();
});

final satuanProvider = FutureProvider<List<Satuan>>((ref) {
  return ref.read(masterRepositoryProvider).getSatuan();
});

final klasterProvider = FutureProvider<List<Klaster>>((ref) {
  return ref.read(masterRepositoryProvider).getKlaster();
});

final iconCopProvider = FutureProvider<List<IconCop>>((ref) {
  return ref.read(masterRepositoryProvider).getIconCop();
});

final resourceJenisProvider = FutureProvider<List<ResourceJenis>>((ref) {
  return ref.read(masterRepositoryProvider).getResourceJenis();
});

final kendaraanJenisProvider = FutureProvider<List<KendaraanJenis>>((ref) {
  return ref.read(masterRepositoryProvider).getKendaraanJenis();
});

final shelterJenisProvider = FutureProvider<List<ShelterJenis>>((ref) {
  return ref.read(masterRepositoryProvider).getShelterJenis();
});

final logistikJenisProvider = FutureProvider<List<LogistikJenis>>((ref) {
  return ref.read(masterRepositoryProvider).getLogistikJenis();
});

final relawanJenisProvider = FutureProvider<List<RelawanJenis>>((ref) {
  return ref.read(masterRepositoryProvider).getRelawanJenis();
});

final suratJenisProvider = FutureProvider<List<SuratJenis>>((ref) {
  return ref.read(masterRepositoryProvider).getSuratJenis();
});

final jabatanTtdProvider = FutureProvider<List<JabatanTtd>>((ref) {
  return ref.read(masterRepositoryProvider).getJabatanTtd();
});

final approvalStatusProvider = FutureProvider<List<ApprovalStatus>>((ref) {
  return ref.read(masterRepositoryProvider).getApprovalStatus();
});

final assessmentIndikatorProvider = FutureProvider<List<AssessmentIndikator>>((ref) {
  return ref.read(masterRepositoryProvider).getAssessmentIndikator();
});

final assessmentKebutuhanProvider = FutureProvider<List<AssessmentKebutuhan>>((ref) {
  return ref.read(masterRepositoryProvider).getAssessmentKebutuhan();
});

// === Data Providers — Tier B (SQLite) ===

final kabupatenProvider = FutureProvider<List<Kabupaten>>((ref) async {
  await ref.read(sqliteMasterInitProvider.future);
  return ref.read(masterRepositoryProvider).getKabupaten();
});

final kecamatanProvider = FutureProvider.family<List<Kecamatan>, String>((ref, idKab) async {
  await ref.read(sqliteMasterInitProvider.future);
  return ref.read(masterRepositoryProvider).getKecamatan(idKab);
});

final desaProvider = FutureProvider.family<List<Desa>, String>((ref, idKec) async {
  await ref.read(sqliteMasterInitProvider.future);
  return ref.read(masterRepositoryProvider).getDesa(idKec);
});

final kebutuhanNumerikMasterProvider = FutureProvider<List<KebutuhanNumerikMaster>>((ref) async {
  return [
    KebutuhanNumerikMaster(idItem: 1, kodeItem: 'sembako', namaItem: 'Paket Sembako', satuanDefault: 'paket', kategori: 'pangan', urutan: 1),
    KebutuhanNumerikMaster(idItem: 2, kodeItem: 'beras', namaItem: 'Beras', satuanDefault: 'kg', kategori: 'pangan', urutan: 2),
    KebutuhanNumerikMaster(idItem: 3, kodeItem: 'mie_instan', namaItem: 'Mie Instan', satuanDefault: 'dus', kategori: 'pangan', urutan: 3),
    KebutuhanNumerikMaster(idItem: 4, kodeItem: 'air_bersih', namaItem: 'Air Bersih', satuanDefault: 'liter', kategori: 'pangan', urutan: 4),
    KebutuhanNumerikMaster(idItem: 5, kodeItem: 'selimut', namaItem: 'Selimut', satuanDefault: 'lembar', kategori: 'sandang', urutan: 5),
    KebutuhanNumerikMaster(idItem: 6, kodeItem: 'pakaian', namaItem: 'Pakaian Layak Pakai', satuanDefault: 'set', kategori: 'sandang', urutan: 6),
    KebutuhanNumerikMaster(idItem: 7, kodeItem: 'matras', namaItem: 'Matras / Tikar', satuanDefault: 'lembar', kategori: 'papan', urutan: 7),
    KebutuhanNumerikMaster(idItem: 8, kodeItem: 'tenda', namaItem: 'Tenda Pengungsian', satuanDefault: 'unit', kategori: 'papan', urutan: 8),
    KebutuhanNumerikMaster(idItem: 9, kodeItem: 'terpal', namaItem: 'Terpal', satuanDefault: 'lembar', kategori: 'papan', urutan: 9),
    KebutuhanNumerikMaster(idItem: 10, kodeItem: 'obat_obatan', namaItem: 'Paket Obat-obatan', satuanDefault: 'paket', kategori: 'kesehatan', urutan: 10),
    KebutuhanNumerikMaster(idItem: 11, kodeItem: 'masker', namaItem: 'Masker', satuanDefault: 'lusin', kategori: 'kesehatan', urutan: 11),
    KebutuhanNumerikMaster(idItem: 12, kodeItem: 'pampers', namaItem: 'Popok / Pampers', satuanDefault: 'dus', kategori: 'kesehatan', urutan: 12),
    KebutuhanNumerikMaster(idItem: 13, kodeItem: 'susu_bayi', namaItem: 'Susu Bayi / Formula', satuanDefault: 'kaleng', kategori: 'kesehatan', urutan: 13),
    KebutuhanNumerikMaster(idItem: 14, kodeItem: 'perahu', namaItem: 'Perahu Karet', satuanDefault: 'unit', kategori: 'peralatan', urutan: 14),
    KebutuhanNumerikMaster(idItem: 15, kodeItem: 'pompa_air', namaItem: 'Pompa Air', satuanDefault: 'unit', kategori: 'peralatan', urutan: 15),
    KebutuhanNumerikMaster(idItem: 16, kodeItem: 'genset', namaItem: 'Genset Portabel', satuanDefault: 'unit', kategori: 'peralatan', urutan: 16),
  ];
});

// === Data Providers — Tier C (Organization + TTL) ===

final pcnuListProvider = FutureProvider<List<Pcnu>>((ref) {
  return ref.read(masterRepositoryProvider).getPcnuList();
});

final keahlianProvider = FutureProvider<List<Keahlian>>((ref) {
  return ref.read(masterRepositoryProvider).getKeahlian();
});

// === Cache Invalidation (call on logout) ===

final clearMasterCacheProvider = Provider<void Function()>((ref) {
  return () {
    ref.read(masterRepositoryProvider).clearCache();
    ref.invalidate(pcnuListProvider);
    ref.invalidate(keahlianProvider);
  };
});
