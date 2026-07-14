import 'models/wilayah.dart';
import 'models/severity.dart';
import 'models/status.dart';
import 'models/master_data.dart';
import 'models/surat.dart';
import 'models/assessment.dart';
import 'models/display.dart';

abstract class MasterRepository {
  Future<List<JenisBencana>> getJenisBencana();
  Future<List<Severity>> getSeverity();
  Future<List<Prioritas>> getPrioritas();
  Future<List<StatusLaporan>> getStatusLaporan();
  Future<List<StatusInsiden>> getStatusInsiden();
  Future<List<StatusOperasi>> getStatusOperasi();
  Future<List<LevelRisiko>> getLevelRisiko();
  Future<List<SkalaKejadian>> getSkalaKejadian();
  Future<List<Satuan>> getSatuan();
  Future<List<Klaster>> getKlaster();
  Future<List<IconCop>> getIconCop();
  Future<Map<String, WarnaIndikator>> getWarnaIndikator();
  Future<List<ResourceJenis>> getResourceJenis();
  Future<List<KendaraanJenis>> getKendaraanJenis();
  Future<List<ShelterJenis>> getShelterJenis();
  Future<List<LogistikJenis>> getLogistikJenis();
  Future<List<RelawanJenis>> getRelawanJenis();
  Future<List<SuratJenis>> getSuratJenis();
  Future<List<JabatanTtd>> getJabatanTtd();
  Future<List<ApprovalStatus>> getApprovalStatus();
  Future<Map<String, List<String>>> getWorkflow();
  Future<List<AssessmentIndikator>> getAssessmentIndikator();
  Future<List<AssessmentKebutuhan>> getAssessmentKebutuhan();

  Future<List<Kabupaten>> getKabupaten();
  Future<List<Kecamatan>> getKecamatan(String idKab);
  Future<List<Desa>> getDesa(String idKec);

  Future<List<Pcnu>> getPcnuList();
  Future<List<Keahlian>> getKeahlian();

  void clearCache();
}
