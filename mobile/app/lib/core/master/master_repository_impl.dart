import 'master_repository.dart';
import 'models/wilayah.dart';
import 'models/severity.dart';
import 'models/status.dart';
import 'models/master_data.dart';
import 'models/surat.dart';
import 'models/assessment.dart';
import 'models/display.dart';
import 'models/kebutuhan_numerik.dart';
import 'repositories/json_master_repository.dart';
import 'repositories/sqlite_master_repository.dart';
import 'repositories/organization_repository.dart';

class MasterRepositoryImpl implements MasterRepository {
  final JsonMasterRepository jsonRepo;
  final SQLiteMasterRepository sqliteRepo;
  final OrganizationRepository orgRepo;

  MasterRepositoryImpl({
    required this.jsonRepo,
    required this.sqliteRepo,
    required this.orgRepo,
  });

  @override Future<List<JenisBencana>> getJenisBencana() => jsonRepo.getJenisBencana();
  @override Future<List<Severity>> getSeverity() => jsonRepo.getSeverity();
  @override Future<List<Prioritas>> getPrioritas() => jsonRepo.getPrioritas();
  @override Future<List<StatusLaporan>> getStatusLaporan() => jsonRepo.getStatusLaporan();
  @override Future<List<StatusInsiden>> getStatusInsiden() => jsonRepo.getStatusInsiden();
  @override Future<List<StatusOperasi>> getStatusOperasi() => jsonRepo.getStatusOperasi();
  @override Future<List<LevelRisiko>> getLevelRisiko() => jsonRepo.getLevelRisiko();
  @override Future<List<SkalaKejadian>> getSkalaKejadian() => jsonRepo.getSkalaKejadian();
  @override Future<List<Satuan>> getSatuan() => jsonRepo.getSatuan();
  @override Future<List<Klaster>> getKlaster() => jsonRepo.getKlaster();
  @override Future<List<IconCop>> getIconCop() => jsonRepo.getIconCop();
  @override Future<Map<String, WarnaIndikator>> getWarnaIndikator() => jsonRepo.getWarnaIndikator();
  @override Future<List<ResourceJenis>> getResourceJenis() => jsonRepo.getResourceJenis();
  @override Future<List<KendaraanJenis>> getKendaraanJenis() => jsonRepo.getKendaraanJenis();
  @override Future<List<ShelterJenis>> getShelterJenis() => jsonRepo.getShelterJenis();
  @override Future<List<LogistikJenis>> getLogistikJenis() => jsonRepo.getLogistikJenis();
  @override Future<List<RelawanJenis>> getRelawanJenis() => jsonRepo.getRelawanJenis();
  @override Future<List<SuratJenis>> getSuratJenis() => jsonRepo.getSuratJenis();
  @override Future<List<JabatanTtd>> getJabatanTtd() => jsonRepo.getJabatanTtd();
  @override Future<List<ApprovalStatus>> getApprovalStatus() => jsonRepo.getApprovalStatus();
  @override Future<Map<String, List<String>>> getWorkflow() => jsonRepo.getWorkflow();
  @override Future<List<AssessmentIndikator>> getAssessmentIndikator() => jsonRepo.getAssessmentIndikator();
  @override Future<List<AssessmentKebutuhan>> getAssessmentKebutuhan() => jsonRepo.getAssessmentKebutuhan();

  @override Future<List<Kabupaten>> getKabupaten() => sqliteRepo.getKabupaten();
  @override Future<List<Kecamatan>> getKecamatan(String idKab) => sqliteRepo.getKecamatan(idKab);
  @override Future<List<Desa>> getDesa(String idKec) => sqliteRepo.getDesa(idKec);
  @override Future<List<KebutuhanNumerikMaster>> getKebutuhanNumerikMaster() => jsonRepo.getKebutuhanNumerikMaster();

  @override Future<List<Pcnu>> getPcnuList() => orgRepo.getPcnuList();
  @override Future<List<Keahlian>> getKeahlian() => jsonRepo.getKeahlian();

  @override
  void clearCache() {
    jsonRepo.clearCache();
    sqliteRepo.clearCache();
    orgRepo.clearCache();
  }
}
