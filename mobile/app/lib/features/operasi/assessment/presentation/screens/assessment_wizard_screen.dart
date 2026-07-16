import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import 'package:nurisk_mobile/core/utils/pdf_download_helper.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import 'package:nurisk_mobile/features/public/report/presentation/notifiers/laporan_provider.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import 'package:nurisk_mobile/features/operasi/insiden/data/models/insiden_model.dart';
import 'package:nurisk_mobile/core/master/providers/master_providers.dart';
import '../notifiers/assessment_provider.dart';

class AssessmentWizardScreen extends ConsumerStatefulWidget {
  final String uuidInsiden;
  final dynamic insiden; // Bisa mengirim LaporanAsalModel atau InsidenModel
  final int? targetAssessmentId;

  const AssessmentWizardScreen({
    super.key,
    required this.uuidInsiden,
    this.insiden,
    this.targetAssessmentId,
  });

  @override
  ConsumerState<AssessmentWizardScreen> createState() => _AssessmentWizardScreenState();
}

class _AssessmentWizardScreenState extends ConsumerState<AssessmentWizardScreen> {
  final _formKey1 = GlobalKey<FormState>();
  final _formKey2 = GlobalKey<FormState>();
  final _formKey3 = GlobalKey<FormState>();
  final _formKey4 = GlobalKey<FormState>();
  final _formKey5 = GlobalKey<FormState>();
  final _formKey6 = GlobalKey<FormState>();

  // Step 1: Informasi Utama & Lokasi
  String _jenisLaporan = 'kaji_cepat';
  final _waktuAssessmentDateController = TextEditingController();
  final _waktuAssessmentTimeController = TextEditingController();
  String? _idKab;
  String? _idKec;
  String? _idDesa;
  final _cakupanWilayahController = TextEditingController();
  final _regionTerdampakController = TextEditingController();
  final _latitudeController = TextEditingController();
  final _longitudeController = TextEditingController();

  // Step 2: Biodata Kejadian & Narasi
  final _tanggalMulaiController = TextEditingController();
  final _jamMulaiController = TextEditingController();
  final _kronologiSingkatController = TextEditingController();
  String _skalaKejadian = 'lokal';
  final _penyebabUtamaController = TextEditingController();

  String _faseNarasi = 'saat_bencana';
  final _judulNarasiController = TextEditingController();
  final _isiNarasiController = TextEditingController();

  final _sebaranDampakController = TextEditingController();
  final _upayaPenangananController = TextEditingController();
  final _kendalaLapanganController = TextEditingController();
  final _kendalaTambahanController = TextEditingController();
  final _rekomendasiAksiController = TextEditingController();

  // Step 3: Dampak Korban Jiwa & Pengungsi
  final _meninggalController = TextEditingController(text: '0');
  final _hilangController = TextEditingController(text: '0');
  final _lukaBeratController = TextEditingController(text: '0');
  final _lukaRinganController = TextEditingController(text: '0');
  final _terdampakJiwaController = TextEditingController(text: '0');
  final _terdampakKkController = TextEditingController(text: '0');
  final _pengungsiJiwaController = TextEditingController(text: '0');
  final _pengungsiKkController = TextEditingController(text: '0');
  final _pengungsiBalitaController = TextEditingController(text: '0');
  final _pengungsiLansiaController = TextEditingController(text: '0');
  final _pengungsiDisabilitasController = TextEditingController(text: '0');
  final _pengungsiIbuHamilController = TextEditingController(text: '0');

  // Step 4: Dampak Infrastruktur & Fasilitas
  final _rumahBeratController = TextEditingController(text: '0');
  final _rumahSedangController = TextEditingController(text: '0');
  final _rumahRinganController = TextEditingController(text: '0');
  final _rumahTerendamController = TextEditingController(text: '0');
  final _rumahTerancamController = TextEditingController(text: '0');

  final _fasumKesehatanController = TextEditingController(text: '0');
  final _fasumPendidikanController = TextEditingController(text: '0');
  final _fasumIbadahController = TextEditingController(text: '0');
  final _fasumPerkantoranController = TextEditingController(text: '0');
  final _fasumPasarController = TextEditingController(text: '0');
  final _fasumSanitasiController = TextEditingController(text: '0');

  final _vitalJalanController = TextEditingController(text: '0');
  final _vitalJembatanPutusController = TextEditingController(text: '0');
  final _vitalJembatanRusakController = TextEditingController(text: '0');
  final _vitalListrikController = TextEditingController(text: '0');
  final _vitalTelekomunikasiController = TextEditingController(text: '0');
  final _vitalAirBersihController = TextEditingController(text: '0');

  // Step 5: Dampak Lingkungan & Ekonomi
  final _lingkunganPertanianController = TextEditingController(text: '0');
  final _lingkunganHutanController = TextEditingController(text: '0');
  final _lingkunganUnggasController = TextEditingController(text: '0');
  final _lingkunganKakiEmpatController = TextEditingController(text: '0');
  final _lingkunganKolamController = TextEditingController(text: '0');
  final _lingkunganNelayanController = TextEditingController(text: '0');
  String? _ekonomiPersentase;

  // Step 6: Kebutuhan Lapangan
  final Map<int, TextEditingController> _kebutuhanControllers = {};

  final _kebutuhanRelawanController = TextEditingController();
  final _kebutuhanLogistikController = TextEditingController();
  final _kebutuhanPeralatanController = TextEditingController();
  final _kebutuhanMedisController = TextEditingController();
  final _kebutuhanPanganController = TextEditingController();
  final _kebutuhanLainnyaController = TextEditingController();

  bool _isLocating = false;
  String? _gpsError;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final notifier = ref.read(assessmentProvider.notifier);
      await notifier.initialize(widget.uuidInsiden, targetAssessmentId: widget.targetAssessmentId);

      final state = ref.read(assessmentProvider);
      
      // Jika formData memiliki data (karena edit mode), maka populate
      if (state.formData.isNotEmpty) {
        _populateControllers(state.formData);
      } else {
        // Jika create mode, auto-fill dari objek insiden
        _autoFillFromInsiden();
      }
    });
  }

  void _autoFillFromInsiden() {
    final now = DateTime.now();
    _waktuAssessmentDateController.text = "${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}";
    _waktuAssessmentTimeController.text = "${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}";

    if (widget.insiden != null && widget.insiden is InsidenModel) {
      final insidenObj = widget.insiden as InsidenModel;
      
      final dt = insidenObj.waktuMulai ?? insidenObj.laporanAsal?.waktuKejadian;
      if (dt != null) {
        _tanggalMulaiController.text = "${dt.year}-${dt.month.toString().padLeft(2, '0')}-${dt.day.toString().padLeft(2, '0')}";
        _jamMulaiController.text = "${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}";
      }

      if (insidenObj.laporanAsal != null) {
        final lapor = insidenObj.laporanAsal!;
        _latitudeController.text = lapor.latitude?.toString() ?? '';
        _longitudeController.text = lapor.longitude?.toString() ?? '';
        _kronologiSingkatController.text = lapor.keteranganSituasi;
        if (lapor.alamatLengkap != null && lapor.alamatLengkap!.isNotEmpty) {
          _cakupanWilayahController.text = lapor.alamatLengkap!;
        } else if (lapor.titikKenal != null && lapor.titikKenal!.isNotEmpty) {
          _cakupanWilayahController.text = lapor.titikKenal!;
        }
      }
    }
    setState(() {});
  }

  void _populateControllers(Map<String, dynamic> data) {
    if (data.isEmpty) return;

    // Step 1
    _jenisLaporan = data['jenis_laporan'] ?? 'kaji_cepat';
    if (data['waktu_assesment'] != null) {
      final dt = DateTime.tryParse(data['waktu_assesment']);
      if (dt != null) {
        _waktuAssessmentDateController.text = "${dt.year}-${dt.month.toString().padLeft(2, '0')}-${dt.day.toString().padLeft(2, '0')}";
        _waktuAssessmentTimeController.text = "${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}";
      }
    }
    _cakupanWilayahController.text = data['cakupan_wilayah_deskripsi'] ?? '';
    _latitudeController.text = data['latitude']?.toString() ?? '';
    _longitudeController.text = data['longitude']?.toString() ?? '';

    if (data['lokasi_detail'] != null) {
      final ld = data['lokasi_detail'];
      if (ld['id_kec'] != null) _idKec = ld['id_kec'].toString();
      if (ld['id_desa'] != null) _idDesa = ld['id_desa'].toString();
      _regionTerdampakController.text = ld['region_terdampak'] ?? '';
    }

    // Step 2
    if (data['biodata_kejadian'] != null) {
      final bk = data['biodata_kejadian'];
      _tanggalMulaiController.text = bk['tanggal_mulai_kejadian'] ?? '';
      _jamMulaiController.text = bk['jam_mulai_kejadian'] ?? '';
      _kronologiSingkatController.text = bk['kronologi_singkat'] ?? '';
      _skalaKejadian = bk['skala_kejadian'] ?? 'lokal';
      _penyebabUtamaController.text = bk['penyebab_utama'] ?? '';
    }

    if (data['narasi_kejadian'] != null) {
      final nk = data['narasi_kejadian'];
      _faseNarasi = nk['fase'] ?? 'saat_bencana';
      _judulNarasiController.text = nk['judul_narasi'] ?? '';
      _isiNarasiController.text = nk['isi_narasi'] ?? '';
    }

    if (data['narasi_detail'] != null) {
      final nd = data['narasi_detail'];
      _sebaranDampakController.text = nd['sebaran_dampak'] ?? '';
      _upayaPenangananController.text = nd['upaya_penanganan'] ?? '';
      _kendalaLapanganController.text = nd['kendala_lapangan'] ?? '';
      _kendalaTambahanController.text = nd['kendala_tambahan'] ?? '';
      _rekomendasiAksiController.text = nd['rekomendasi_aksi'] ?? '';
    }

    // Step 3
    if (data['dampak_manusia'] != null) {
      final dm = data['dampak_manusia'];
      _meninggalController.text = dm['meninggal']?.toString() ?? '0';
      _hilangController.text = dm['hilang']?.toString() ?? '0';
      _lukaBeratController.text = dm['luka_berat']?.toString() ?? '0';
      _lukaRinganController.text = dm['luka_ringan']?.toString() ?? '0';
      _terdampakJiwaController.text = dm['menderita_mengungsi']?.toString() ?? '0';
      _terdampakKkController.text = dm['terdampak_kk']?.toString() ?? '0';
      _pengungsiJiwaController.text = dm['pengungsi_jiwa']?.toString() ?? '0';
      _pengungsiKkController.text = dm['pengungsi_kk']?.toString() ?? '0';
      _pengungsiBalitaController.text = dm['pengungsi_balita']?.toString() ?? '0';
      _pengungsiLansiaController.text = dm['pengungsi_lansia']?.toString() ?? '0';
      _pengungsiDisabilitasController.text = dm['pengungsi_disabilitas']?.toString() ?? '0';
      _pengungsiIbuHamilController.text = dm['pengungsi_ibu_hamil']?.toString() ?? '0';
    }

    // Step 4
    if (data['dampak_infrastruktur'] != null) {
      final di = data['dampak_infrastruktur'];
      _rumahBeratController.text = di['rumah_rusak_berat']?.toString() ?? '0';
      _rumahSedangController.text = di['rumah_rusak_sedang']?.toString() ?? '0';
      _rumahRinganController.text = di['rumah_rusak_ringan']?.toString() ?? '0';
      _rumahTerendamController.text = di['rumah_terendam']?.toString() ?? '0';
      _rumahTerancamController.text = di['rumah_terancam']?.toString() ?? '0';

      _fasumKesehatanController.text = di['fasilitas_kesehatan_rusak']?.toString() ?? '0';
      _fasumPendidikanController.text = di['fasilitas_pendidikan_rusak']?.toString() ?? '0';
      _fasumIbadahController.text = di['tempat_ibadah_rusak']?.toString() ?? '0';
      _fasumPerkantoranController.text = di['kantor_pemerintah_rusak']?.toString() ?? '0';
      _fasumPasarController.text = di['pasar']?.toString() ?? '0';
      _fasumSanitasiController.text = di['sanitasi']?.toString() ?? '0';

      _vitalJalanController.text = di['jalan_rusak_km']?.toString() ?? '0';
      _vitalJembatanPutusController.text = di['jembatan_putus']?.toString() ?? '0';
      _vitalJembatanRusakController.text = di['jembatan_rusak']?.toString() ?? '0';
      _vitalListrikController.text = di['jaringan_listrik_padam_kk']?.toString() ?? '0';
      _vitalTelekomunikasiController.text = di['jaringan_komunikasi_putus']?.toString() ?? '0';
      _vitalAirBersihController.text = di['sarana_air_bersih_rusak']?.toString() ?? '0';
    }

    // Step 5
    if (data['dampak_lingkungan'] != null) {
      final dl = data['dampak_lingkungan'];
      _lingkunganPertanianController.text = dl['lahan_pertanian_rusak_ha']?.toString() ?? '0';
      _lingkunganHutanController.text = dl['hutan_terdampak_ha']?.toString() ?? '0';
      _lingkunganUnggasController.text = dl['unggas']?.toString() ?? '0';
      _lingkunganKakiEmpatController.text = dl['kaki_empat']?.toString() ?? '0';
      _lingkunganKolamController.text = dl['perikanan_kolam']?.toString() ?? '0';
      _lingkunganNelayanController.text = dl['perikanan_nelayan']?.toString() ?? '0';
    }
    if (data['dampak_ekonomi'] != null) {
      final de = data['dampak_ekonomi'];
      _ekonomiPersentase = de['persentase_ekonomi_terdampak']?.toString();
    }

    // Step 6
    if (data['kebutuhan_numerik'] != null) {
      final kn = data['kebutuhan_numerik'] as List;
      for (var item in kn) {
        if (item['id_item'] != null) {
          final idItem = int.tryParse(item['id_item'].toString());
          if (idItem != null) {
            _kebutuhanControllers[idItem] = TextEditingController(text: item['jumlah_dibutuhkan']?.toString() ?? '0');
          }
        }
      }
    }

    if (data['kebutuhan_lanjutan'] != null) {
      final kl = data['kebutuhan_lanjutan'];
      _kebutuhanRelawanController.text = kl['kebutuhan_relawan'] ?? '';
      _kebutuhanLogistikController.text = kl['kebutuhan_logistik'] ?? '';
      _kebutuhanPeralatanController.text = kl['kebutuhan_peralatan'] ?? '';
      _kebutuhanMedisController.text = kl['kebutuhan_medis'] ?? '';
      _kebutuhanPanganController.text = kl['kebutuhan_pangan'] ?? '';
      _kebutuhanLainnyaController.text = kl['kebutuhan_lainnya'] ?? '';
    }

    setState(() {});
  }

  Future<void> _getCurrentLocation() async {
    setState(() {
      _isLocating = true;
      _gpsError = null;
    });
    final geo = ref.read(runtimeServicesProvider).geo;
    final result = await geo.getCurrentPosition();
    if (!mounted) return;
    if (!result.isSuccess) {
      setState(() {
        _gpsError = result.message ?? 'Gagal mendapatkan lokasi. Periksa GPS dan izin lokasi.';
        _isLocating = false;
      });
      return;
    }
    if (result.point!.isMocked) {
      setState(() {
        _gpsError = 'GPS terdeteksi palsu (mock). Matikan mode mock GPS untuk mendapatkan lokasi akurat.';
        _isLocating = false;
      });
      return;
    }
    setState(() {
      _latitudeController.text = result.point!.latitude.toString();
      _longitudeController.text = result.point!.longitude.toString();
      _isLocating = false;
    });
  }

  void _onStepContinue(int currentStep, AssessmentNotifier notifier) async {
    bool valid = false;

    if (currentStep == 0) {
      if (_formKey1.currentState!.validate()) {
        if (_idKec == null || _idDesa == null) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Pilih Kecamatan dan Desa'), backgroundColor: Colors.red),
          );
          return;
        }
        notifier.updateFormData({
          'jenis_laporan': _jenisLaporan,
          'waktu_assesment': "${_waktuAssessmentDateController.text} ${_waktuAssessmentTimeController.text}:00",
          'cakupan_wilayah_deskripsi': _cakupanWilayahController.text,
          'latitude': double.tryParse(_latitudeController.text),
          'longitude': double.tryParse(_longitudeController.text),
          'lokasi_detail': {
            'id_kec': _idKec,
            'id_desa': _idDesa,
            'region_terdampak': _regionTerdampakController.text,
          }
        });
        valid = true;
      }
    } else if (currentStep == 1) {
      if (_formKey2.currentState!.validate()) {
        notifier.updateFormData({
          'biodata_kejadian': {
            'tanggal_mulai_kejadian': _tanggalMulaiController.text,
            'jam_mulai_kejadian': _jamMulaiController.text.isEmpty ? null : _jamMulaiController.text,
            'kronologi_singkat': _kronologiSingkatController.text,
            'skala_kejadian': _skalaKejadian,
            'penyebab_utama': _penyebabUtamaController.text,
          },
          'narasi_kejadian': {
            'fase': _faseNarasi,
            'judul_narasi': _judulNarasiController.text,
            'isi_narasi': _isiNarasiController.text,
          },
          'narasi_detail': {
            'sebaran_dampak': _sebaranDampakController.text,
            'upaya_penanganan': _upayaPenangananController.text,
            'kendala_lapangan': _kendalaLapanganController.text,
            'kendala_tambahan': _kendalaTambahanController.text,
            'rekomendasi_aksi': _rekomendasiAksiController.text,
          }
        });
        valid = true;
      }
    } else if (currentStep == 2) {
      if (_formKey3.currentState!.validate()) {
        notifier.updateFormData({
          'dampak_manusia': {
            'meninggal': int.tryParse(_meninggalController.text) ?? 0,
            'hilang': int.tryParse(_hilangController.text) ?? 0,
            'luka_berat': int.tryParse(_lukaBeratController.text) ?? 0,
            'luka_ringan': int.tryParse(_lukaRinganController.text) ?? 0,
            'menderita_mengungsi': int.tryParse(_terdampakJiwaController.text) ?? 0,
            'terdampak_kk': int.tryParse(_terdampakKkController.text) ?? 0,
            'pengungsi_jiwa': int.tryParse(_pengungsiJiwaController.text) ?? 0,
            'pengungsi_kk': int.tryParse(_pengungsiKkController.text) ?? 0,
            'pengungsi_balita': int.tryParse(_pengungsiBalitaController.text) ?? 0,
            'pengungsi_lansia': int.tryParse(_pengungsiLansiaController.text) ?? 0,
            'pengungsi_disabilitas': int.tryParse(_pengungsiDisabilitasController.text) ?? 0,
            'pengungsi_ibu_hamil': int.tryParse(_pengungsiIbuHamilController.text) ?? 0,
          },
        });
        valid = true;
      }
    } else if (currentStep == 3) {
      if (_formKey4.currentState!.validate()) {
        notifier.updateFormData({
          'dampak_infrastruktur': {
            'rumah_rusak_berat': int.tryParse(_rumahBeratController.text) ?? 0,
            'rumah_rusak_sedang': int.tryParse(_rumahSedangController.text) ?? 0,
            'rumah_rusak_ringan': int.tryParse(_rumahRinganController.text) ?? 0,
            'rumah_terendam': int.tryParse(_rumahTerendamController.text) ?? 0,
            'rumah_terancam': int.tryParse(_rumahTerancamController.text) ?? 0,
            'fasilitas_kesehatan_rusak': int.tryParse(_fasumKesehatanController.text) ?? 0,
            'fasilitas_pendidikan_rusak': int.tryParse(_fasumPendidikanController.text) ?? 0,
            'tempat_ibadah_rusak': int.tryParse(_fasumIbadahController.text) ?? 0,
            'kantor_pemerintah_rusak': int.tryParse(_fasumPerkantoranController.text) ?? 0,
            'pasar': int.tryParse(_fasumPasarController.text) ?? 0,
            'sanitasi': int.tryParse(_fasumSanitasiController.text) ?? 0,
            'jalan_rusak_km': double.tryParse(_vitalJalanController.text) ?? 0,
            'jembatan_putus': int.tryParse(_vitalJembatanPutusController.text) ?? 0,
            'jembatan_rusak': int.tryParse(_vitalJembatanRusakController.text) ?? 0,
            'jaringan_listrik_padam_kk': int.tryParse(_vitalListrikController.text) ?? 0,
            'jaringan_komunikasi_putus': int.tryParse(_vitalTelekomunikasiController.text) ?? 0,
            'sarana_air_bersih_rusak': int.tryParse(_vitalAirBersihController.text) ?? 0,
          }
        });
        valid = true;
      }
    } else if (currentStep == 4) {
      if (_formKey5.currentState!.validate()) {
        notifier.updateFormData({
          'dampak_lingkungan': {
            'lahan_pertanian_rusak_ha': double.tryParse(_lingkunganPertanianController.text) ?? 0.0,
            'hutan_terdampak_ha': double.tryParse(_lingkunganHutanController.text) ?? 0.0,
            'unggas': int.tryParse(_lingkunganUnggasController.text) ?? 0,
            'kaki_empat': int.tryParse(_lingkunganKakiEmpatController.text) ?? 0,
            'perikanan_kolam': double.tryParse(_lingkunganKolamController.text) ?? 0.0,
            'perikanan_nelayan': int.tryParse(_lingkunganNelayanController.text) ?? 0,
          },
          'dampak_ekonomi': {
            'persentase_ekonomi_terdampak': _ekonomiPersentase,
          }
        });
        valid = true;
      }
    } else if (currentStep == 5) {
      if (_formKey6.currentState!.validate()) {
        List<Map<String, dynamic>> kebutuhanList = [];
        final needsMaster = ref.read(kebutuhanNumerikMasterProvider).value ?? [];
        for (var item in needsMaster) {
           final ctrl = _kebutuhanControllers[item.idItem];
           final jml = double.tryParse(ctrl?.text ?? '0') ?? 0;
           if (jml > 0) {
              kebutuhanList.add({
                 'id_item': item.idItem,
                 'nama_item': item.namaItem, 
                 'jumlah_dibutuhkan': jml,
                 'satuan': item.satuanDefault ?? 'unit',
                 'prioritas': 'normal',
                 'jumlah_tersedia': 0,
              });
           }
        }
        notifier.updateFormData({
          'kebutuhan_numerik': kebutuhanList,
          'kebutuhan_lanjutan': {
            'kebutuhan_relawan': _kebutuhanRelawanController.text,
            'kebutuhan_logistik': _kebutuhanLogistikController.text,
            'kebutuhan_peralatan': _kebutuhanPeralatanController.text,
            'kebutuhan_medis': _kebutuhanMedisController.text,
            'kebutuhan_pangan': _kebutuhanPanganController.text,
            'kebutuhan_lainnya': _kebutuhanLainnyaController.text,
          }
        });
        valid = true;
      }
    } else if (currentStep == 6) {
      // Force aggregate all fields to ensure no data is lost if user skipped clicking 'Lanjutkan'
      List<Map<String, dynamic>> kebutuhanList = [];
      final needsMaster = ref.read(kebutuhanNumerikMasterProvider).value ?? [];
      for (var item in needsMaster) {
         final ctrl = _kebutuhanControllers[item.idItem];
         final jml = double.tryParse(ctrl?.text ?? '0') ?? 0;
         if (jml > 0) {
            kebutuhanList.add({
               'id_item': item.idItem,
               'nama_item': item.namaItem,
               'jumlah_dibutuhkan': jml,
               'satuan': item.satuanDefault ?? 'unit',
               'prioritas': 'normal',
               'jumlah_tersedia': 0,
            });
         }
      }
      notifier.updateFormData({
        'jenis_laporan': _jenisLaporan,
        'waktu_assesment': "${_waktuAssessmentDateController.text} ${_waktuAssessmentTimeController.text}:00",
        'cakupan_wilayah_deskripsi': _cakupanWilayahController.text,
        'latitude': double.tryParse(_latitudeController.text),
        'longitude': double.tryParse(_longitudeController.text),
        'lokasi_detail': {
          'id_kec': _idKec,
          'id_desa': _idDesa,
          'region_terdampak': _regionTerdampakController.text,
        },
        'biodata_kejadian': {
          'tanggal_mulai_kejadian': _tanggalMulaiController.text,
          'jam_mulai_kejadian': _jamMulaiController.text.isEmpty ? null : _jamMulaiController.text,
          'kronologi_singkat': _kronologiSingkatController.text,
          'skala_kejadian': _skalaKejadian,
          'penyebab_utama': _penyebabUtamaController.text,
        },
        'narasi_kejadian': {
          'fase': _faseNarasi,
          'judul_narasi': _judulNarasiController.text,
          'isi_narasi': _isiNarasiController.text,
        },
        'narasi_detail': {
          'sebaran_dampak': _sebaranDampakController.text,
          'upaya_penanganan': _upayaPenangananController.text,
          'kendala_lapangan': _kendalaLapanganController.text,
          'kendala_tambahan': _kendalaTambahanController.text,
          'rekomendasi_aksi': _rekomendasiAksiController.text,
        },
        'dampak_manusia': {
          'meninggal': int.tryParse(_meninggalController.text) ?? 0,
          'hilang': int.tryParse(_hilangController.text) ?? 0,
          'luka_berat': int.tryParse(_lukaBeratController.text) ?? 0,
          'luka_ringan': int.tryParse(_lukaRinganController.text) ?? 0,
          'menderita_mengungsi': int.tryParse(_terdampakJiwaController.text) ?? 0,
          'terdampak_kk': int.tryParse(_terdampakKkController.text) ?? 0,
          'pengungsi_jiwa': int.tryParse(_pengungsiJiwaController.text) ?? 0,
          'pengungsi_kk': int.tryParse(_pengungsiKkController.text) ?? 0,
          'pengungsi_balita': int.tryParse(_pengungsiBalitaController.text) ?? 0,
          'pengungsi_lansia': int.tryParse(_pengungsiLansiaController.text) ?? 0,
          'pengungsi_disabilitas': int.tryParse(_pengungsiDisabilitasController.text) ?? 0,
          'pengungsi_ibu_hamil': int.tryParse(_pengungsiIbuHamilController.text) ?? 0,
        },
        'dampak_infrastruktur': {
          'rumah_rusak_berat': int.tryParse(_rumahBeratController.text) ?? 0,
          'rumah_rusak_sedang': int.tryParse(_rumahSedangController.text) ?? 0,
          'rumah_rusak_ringan': int.tryParse(_rumahRinganController.text) ?? 0,
          'rumah_terendam': int.tryParse(_rumahTerendamController.text) ?? 0,
          'rumah_terancam': int.tryParse(_rumahTerancamController.text) ?? 0,
          'fasilitas_kesehatan_rusak': int.tryParse(_fasumKesehatanController.text) ?? 0,
          'fasilitas_pendidikan_rusak': int.tryParse(_fasumPendidikanController.text) ?? 0,
          'tempat_ibadah_rusak': int.tryParse(_fasumIbadahController.text) ?? 0,
          'kantor_pemerintah_rusak': int.tryParse(_fasumPerkantoranController.text) ?? 0,
          'pasar': int.tryParse(_fasumPasarController.text) ?? 0,
          'sanitasi': int.tryParse(_fasumSanitasiController.text) ?? 0,
          'jalan_rusak_km': double.tryParse(_vitalJalanController.text) ?? 0,
          'jembatan_putus': int.tryParse(_vitalJembatanPutusController.text) ?? 0,
          'jembatan_rusak': int.tryParse(_vitalJembatanRusakController.text) ?? 0,
          'jaringan_listrik_padam_kk': int.tryParse(_vitalListrikController.text) ?? 0,
          'jaringan_komunikasi_putus': int.tryParse(_vitalTelekomunikasiController.text) ?? 0,
          'sarana_air_bersih_rusak': int.tryParse(_vitalAirBersihController.text) ?? 0,
        },
        'dampak_lingkungan': {
          'lahan_pertanian_rusak_ha': double.tryParse(_lingkunganPertanianController.text) ?? 0.0,
          'hutan_terdampak_ha': double.tryParse(_lingkunganHutanController.text) ?? 0.0,
          'unggas': int.tryParse(_lingkunganUnggasController.text) ?? 0,
          'kaki_empat': int.tryParse(_lingkunganKakiEmpatController.text) ?? 0,
          'perikanan_kolam': double.tryParse(_lingkunganKolamController.text) ?? 0.0,
          'perikanan_nelayan': int.tryParse(_lingkunganNelayanController.text) ?? 0,
        },
        'dampak_ekonomi': {
          'persentase_ekonomi_terdampak': _ekonomiPersentase,
        },
        'kebutuhan_numerik': kebutuhanList,
        'kebutuhan_lanjutan': {
          'kebutuhan_relawan': _kebutuhanRelawanController.text,
          'kebutuhan_logistik': _kebutuhanLogistikController.text,
          'kebutuhan_peralatan': _kebutuhanPeralatanController.text,
          'kebutuhan_medis': _kebutuhanMedisController.text,
          'kebutuhan_pangan': _kebutuhanPanganController.text,
          'kebutuhan_lainnya': _kebutuhanLainnyaController.text,
        }
      });
      final success = await notifier.submitAssessment(widget.uuidInsiden);
      if (success && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Assessment berhasil disubmit!')));
        context.pop();
      } else if (mounted) {
        final err = ref.read(assessmentProvider).error;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err ?? 'Gagal submit'), backgroundColor: Colors.red));
      }
      return;
    }

    if (valid) notifier.nextStep();
  }

  Widget _buildTextField(TextEditingController controller, String label, {bool isNumber = false, int maxLines = 1, bool required = false, String? Function(String?)? validator}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12.0),
      child: TextFormField(
        controller: controller,
        keyboardType: isNumber ? const TextInputType.numberWithOptions(decimal: true) : TextInputType.text,
        maxLines: maxLines,
        decoration: InputDecoration(
          labelText: required ? '$label *' : label,
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          filled: true,
          fillColor: Colors.white,
        ),
        validator: validator ?? (required
            ? (value) {
                if (value == null || value.trim().isEmpty) return 'Wajib diisi';
                return null;
              }
            : null),
      ),
    );
  }

  Widget _buildKabupatenDropdown() {
    final asyncData = ref.watch(kabupatenProvider);
    return asyncData.when(
      data: (list) {
        return DropdownButtonFormField<String>(
          value: _idKab != null && list.any((k) => k.idKab == _idKab) ? _idKab : null,
          decoration: InputDecoration(
            labelText: 'Kabupaten/Kota *',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            filled: true,
            fillColor: Colors.white,
          ),
          items: list.map((k) => DropdownMenuItem(value: k.idKab, child: Text(k.namaKab))).toList(),
          onChanged: (val) {
            setState(() {
              _idKab = val;
              _idKec = null;
              _idDesa = null;
            });
          },
          validator: (v) => v == null ? 'Pilih Kabupaten/Kota' : null,
        );
      },
      loading: () => const CircularProgressIndicator(),
      error: (e, st) => Text('Gagal memuat kabupaten: $e'),
    );
  }

  Widget _buildKecamatanDropdown() {
    if (_idKab == null) return const SizedBox();
    final asyncData = ref.watch(kecamatanProvider(_idKab!));
    return asyncData.when(
      data: (list) {
        return DropdownButtonFormField<String>(
          value: _idKec != null && list.any((k) => k.idKec == _idKec) ? _idKec : null,
          decoration: InputDecoration(
            labelText: 'Kecamatan *',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            filled: true,
            fillColor: Colors.white,
          ),
          items: list.map((k) => DropdownMenuItem(value: k.idKec, child: Text(k.namaKec))).toList(),
          onChanged: (val) {
            setState(() {
              _idKec = val;
              _idDesa = null;
            });
          },
          validator: (v) => v == null ? 'Pilih Kecamatan' : null,
        );
      },
      loading: () => const CircularProgressIndicator(),
      error: (e, st) => Text('Gagal memuat kecamatan: $e'),
    );
  }

  Widget _buildDesaDropdown() {
    if (_idKec == null) return const SizedBox();
    final asyncData = ref.watch(desaProvider(_idKec!));
    return asyncData.when(
      data: (list) {
        return DropdownButtonFormField<String>(
          value: _idDesa != null && list.any((d) => d.idDesa == _idDesa) ? _idDesa : null,
          decoration: InputDecoration(
            labelText: 'Desa/Kelurahan *',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            filled: true,
            fillColor: Colors.white,
          ),
          items: list.map((d) => DropdownMenuItem(value: d.idDesa, child: Text(d.namaDesa))).toList(),
          onChanged: (val) => setState(() => _idDesa = val),
          validator: (v) => v == null ? 'Pilih Desa' : null,
        );
      },
      loading: () => const CircularProgressIndicator(),
      error: (e, st) => Text('Gagal memuat desa: $e'),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16.0),
      child: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.indigo)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(assessmentProvider);
    final notifier = ref.read(assessmentProvider.notifier);

    if (state.isLoading && state.currentStep == 0) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Form Assessment Komprehensif'),
        actions: widget.targetAssessmentId != null
            ? [
                IconButton(
                  icon: const Icon(Icons.download_rounded),
                  tooltip: 'Unduh PDF',
                  onPressed: () {
                    final dio = ref.read(authApiClientProvider);
                    PdfDownloadHelper.downloadAndOpenPdf(
                      context: context,
                      dio: dio,
                      endpoint: 'v1/assessment/${widget.targetAssessmentId}/pdf',
                      fileName: 'assessment_${widget.targetAssessmentId}.pdf',
                    );
                  },
                ),
              ]
            : null,
      ),
      body: Stepper(
        type: StepperType.vertical,
        currentStep: state.currentStep,
        onStepContinue: () => _onStepContinue(state.currentStep, notifier),
        onStepCancel: () => notifier.previousStep(),
        onStepTapped: (step) => notifier.setStep(step),
        steps: [
          Step(
            title: const Text('Informasi Utama & Lokasi'),
            content: Form(
              key: _formKey1,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  DropdownButtonFormField<String>(
                    initialValue: _jenisLaporan,
                    decoration: const InputDecoration(labelText: 'Jenis Laporan *', border: OutlineInputBorder(), isDense: true),
                    items: const [
                      DropdownMenuItem(value: 'kaji_cepat', child: Text('Kaji Cepat')),
                      DropdownMenuItem(value: 'pendataan_lanjutan', child: Text('Pendataan Lanjutan')),
                    ],
                    onChanged: (val) { if (val != null) setState(() => _jenisLaporan = val); },
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(child: _buildTextField(_waktuAssessmentDateController, 'Tgl Assessment (YYYY-MM-DD)', required: true)),
                      const SizedBox(width: 8),
                      Expanded(child: _buildTextField(_waktuAssessmentTimeController, 'Waktu Assessment (HH:MM)', required: true)),
                    ],
                  ),
                  _buildTextField(_cakupanWilayahController, 'Alamat Spesifik / Cakupan Wilayah (min 10 karakter)', maxLines: 2, required: true, validator: (val) {
                    if (val == null || val.trim().length < 10) return 'Minimal 10 karakter';
                    return null;
                  }),
                  _buildTextField(_regionTerdampakController, 'Region Terdampak (Opsional)', maxLines: 2),
                  const SizedBox(height: 12),
                  _buildKabupatenDropdown(),
                  const SizedBox(height: 12),
                  _buildKecamatanDropdown(),
                  const SizedBox(height: 12),
                  _buildDesaDropdown(),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _isLocating ? null : _getCurrentLocation,
                          icon: _isLocating
                              ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                              : const Icon(Icons.my_location),
                          label: Text(_isLocating ? 'Mencari...' : 'Dapatkan GPS'),
                        ),
                      ),
                    ],
                  ),
                  if (_gpsError != null) ...[
                    const SizedBox(height: 8),
                    Text(_gpsError!, style: const TextStyle(color: Colors.red, fontSize: 12)),
                  ],
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(child: _buildTextField(_latitudeController, 'Latitude', isNumber: true)),
                      const SizedBox(width: 12),
                      Expanded(child: _buildTextField(_longitudeController, 'Longitude', isNumber: true)),
                    ],
                  ),
                ],
              ),
            ),
            isActive: state.currentStep >= 0,
            state: state.currentStep > 0 ? StepState.complete : StepState.editing,
          ),
          Step(
            title: const Text('Biodata Kejadian & Narasi'),
            content: Form(
              key: _formKey2,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Biodata Bencana'),
                  Row(
                    children: [
                      Expanded(child: _buildTextField(_tanggalMulaiController, 'Tgl Mulai (YYYY-MM-DD)', required: true)),
                      const SizedBox(width: 8),
                      Expanded(child: _buildTextField(_jamMulaiController, 'Jam Mulai (HH:MM)')),
                    ],
                  ),
                  _buildTextField(_kronologiSingkatController, 'Kronologi Singkat', maxLines: 3, required: true),
                  DropdownButtonFormField<String>(
                    initialValue: _skalaKejadian,
                    decoration: const InputDecoration(labelText: 'Skala Kejadian', border: OutlineInputBorder(), isDense: true),
                    items: const [
                      DropdownMenuItem(value: 'lokal', child: Text('Lokal')),
                      DropdownMenuItem(value: 'kecamatan', child: Text('Kecamatan')),
                      DropdownMenuItem(value: 'kabupaten', child: Text('Kabupaten')),
                      DropdownMenuItem(value: 'provinsi', child: Text('Provinsi')),
                      DropdownMenuItem(value: 'nasional', child: Text('Nasional')),
                    ],
                    onChanged: (val) { if (val != null) setState(() => _skalaKejadian = val); },
                  ),
                  const SizedBox(height: 12),
                  _buildTextField(_penyebabUtamaController, 'Penyebab Utama'),

                  _buildSectionHeader('Narasi Kejadian'),
                  DropdownButtonFormField<String>(
                    initialValue: _faseNarasi,
                    decoration: const InputDecoration(labelText: 'Fase Narasi *', border: OutlineInputBorder(), isDense: true),
                    items: const [
                      DropdownMenuItem(value: 'pra_bencana', child: Text('Pra Bencana')),
                      DropdownMenuItem(value: 'saat_bencana', child: Text('Saat Bencana')),
                      DropdownMenuItem(value: 'pasca_bencana', child: Text('Pasca Bencana')),
                    ],
                    onChanged: (val) { if (val != null) setState(() => _faseNarasi = val); },
                  ),
                  const SizedBox(height: 12),
                  _buildTextField(_judulNarasiController, 'Judul Narasi', required: true),
                  _buildTextField(_isiNarasiController, 'Isi Narasi', maxLines: 4, required: true),

                  _buildSectionHeader('Detail Narasi'),
                  _buildTextField(_sebaranDampakController, 'Sebaran Dampak', maxLines: 2),
                  _buildTextField(_upayaPenangananController, 'Upaya Penanganan', maxLines: 2),
                  _buildTextField(_kendalaLapanganController, 'Kendala Lapangan', maxLines: 2),
                  _buildTextField(_kendalaTambahanController, 'Kendala Tambahan', maxLines: 2),
                  _buildTextField(_rekomendasiAksiController, 'Rekomendasi Aksi', maxLines: 2),
                ],
              ),
            ),
            isActive: state.currentStep >= 1,
            state: state.currentStep > 1 ? StepState.complete : (state.currentStep == 1 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Dampak Korban Jiwa & Pengungsi'),
            content: Form(
              key: _formKey3,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Meninggal / Luka-Luka'),
                  Row(children: [ Expanded(child: _buildTextField(_meninggalController, 'Meninggal', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_hilangController, 'Hilang', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_lukaBeratController, 'Luka Berat', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_lukaRinganController, 'Luka Ringan', isNumber: true)) ]),
                  
                  _buildSectionHeader('Terdampak & Pengungsi'),
                  Row(children: [ Expanded(child: _buildTextField(_terdampakJiwaController, 'Terdampak (Jiwa)', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_terdampakKkController, 'Terdampak (KK)', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_pengungsiJiwaController, 'Pengungsi Jiwa', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_pengungsiKkController, 'Pengungsi KK', isNumber: true)) ]),
                  
                  _buildSectionHeader('Kelompok Rentan (Pengungsi)'),
                  Row(children: [ Expanded(child: _buildTextField(_pengungsiBalitaController, 'Balita', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_pengungsiLansiaController, 'Lansia', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_pengungsiDisabilitasController, 'Disabilitas', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_pengungsiIbuHamilController, 'Ibu Hamil', isNumber: true)) ]),
                ],
              ),
            ),
            isActive: state.currentStep >= 2,
            state: state.currentStep > 2 ? StepState.complete : (state.currentStep == 2 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Dampak Infrastruktur & Fasilitas'),
            content: Form(
              key: _formKey4,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Kerusakan Rumah (Unit)'),
                  Row(children: [ Expanded(child: _buildTextField(_rumahBeratController, 'Rusak Berat', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_rumahSedangController, 'Rusak Sedang', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_rumahRinganController, 'Rusak Ringan', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_rumahTerendamController, 'Terendam', isNumber: true)) ]),
                  _buildTextField(_rumahTerancamController, 'Terancam', isNumber: true),

                  _buildSectionHeader('Fasilitas Umum & Sosial (Unit)'),
                  Row(children: [ Expanded(child: _buildTextField(_fasumKesehatanController, 'Kesehatan', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_fasumPendidikanController, 'Pendidikan', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_fasumIbadahController, 'Ibadah', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_fasumPerkantoranController, 'Perkantoran', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_fasumPasarController, 'Pasar', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_fasumSanitasiController, 'Sanitasi', isNumber: true)) ]),
                  
                  _buildSectionHeader('Infrastruktur Vital'),
                  Row(children: [ Expanded(child: _buildTextField(_vitalJalanController, 'Jalan Rusak (Km)', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_vitalListrikController, 'Listrik Padam (KK)', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_vitalJembatanPutusController, 'Jembatan Putus', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_vitalJembatanRusakController, 'Jembatan Rusak', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_vitalTelekomunikasiController, 'Komunikasi Putus', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_vitalAirBersihController, 'Air Bersih Rusak', isNumber: true)) ]),
                ],
              ),
            ),
            isActive: state.currentStep >= 3,
            state: state.currentStep > 3 ? StepState.complete : (state.currentStep == 3 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Dampak Lingkungan & Ekonomi'),
            content: Form(
              key: _formKey5,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Lingkungan & Peternakan'),
                  Row(children: [ Expanded(child: _buildTextField(_lingkunganPertanianController, 'Pertanian Rusak (Ha)', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_lingkunganHutanController, 'Hutan Terdampak (Ha)', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_lingkunganUnggasController, 'Unggas Mati (Ekor)', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_lingkunganKakiEmpatController, 'Kaki Empat Mati', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_lingkunganKolamController, 'Kolam Ikan (Ha)', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_lingkunganNelayanController, 'Nelayan (Unit)', isNumber: true)) ]),
                  
                  _buildSectionHeader('Ekonomi'),
                  DropdownButtonFormField<String>(
                    value: _ekonomiPersentase,
                    decoration: const InputDecoration(labelText: 'Persentase Ekonomi Terdampak', border: OutlineInputBorder(), isDense: true),
                    items: const [
                      DropdownMenuItem(value: '< 25%', child: Text('< 25%')),
                      DropdownMenuItem(value: '25% - 50%', child: Text('25% - 50%')),
                      DropdownMenuItem(value: '51% - 75%', child: Text('51% - 75%')),
                      DropdownMenuItem(value: '> 75%', child: Text('> 75%')),
                    ],
                    onChanged: (val) { if (val != null) setState(() => _ekonomiPersentase = val); },
                  ),
                ],
              ),
            ),
            isActive: state.currentStep >= 4,
            state: state.currentStep > 4 ? StepState.complete : (state.currentStep == 4 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Kebutuhan Lapangan'),
            content: Form(
              key: _formKey6,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Kebutuhan Numerik (Master)'),
                  ref.watch(kebutuhanNumerikMasterProvider).when(
                    data: (needs) {
                      if (needs.isEmpty) return const Text('Tidak ada master kebutuhan numerik.');
                      return Column(
                        children: needs.map((item) {
                          if (!_kebutuhanControllers.containsKey(item.idItem)) {
                            _kebutuhanControllers[item.idItem] = TextEditingController(text: '0');
                          }
                          return Row(
                            children: [
                              Expanded(flex: 2, child: Text(item.namaItem)),
                              Expanded(
                                flex: 1,
                                child: _buildTextField(_kebutuhanControllers[item.idItem]!, 'Jumlah', isNumber: true)
                              ),
                              const SizedBox(width: 8),
                              SizedBox(width: 50, child: Text(item.satuanDefault)),
                            ],
                          );
                        }).toList(),
                      );
                    },
                    loading: () => const CircularProgressIndicator(),
                    error: (err, _) => Text(DioExceptionMapper.toUserMessage(err)),
                  ),

                  _buildSectionHeader('Kebutuhan Lanjutan / Narasi'),
                  _buildTextField(_kebutuhanRelawanController, 'Kebutuhan Relawan', maxLines: 2),
                  _buildTextField(_kebutuhanLogistikController, 'Kebutuhan Logistik', maxLines: 2),
                  _buildTextField(_kebutuhanPeralatanController, 'Kebutuhan Peralatan', maxLines: 2),
                  _buildTextField(_kebutuhanMedisController, 'Kebutuhan Medis', maxLines: 2),
                  _buildTextField(_kebutuhanPanganController, 'Kebutuhan Pangan', maxLines: 2),
                  _buildTextField(_kebutuhanLainnyaController, 'Kebutuhan Lainnya', maxLines: 2),
                ],
              ),
            ),
            isActive: state.currentStep >= 5,
            state: state.currentStep > 5 ? StepState.complete : (state.currentStep == 5 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Konfirmasi & Simpan'),
            content: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Pastikan semua data telah akurat sesuai standar 1:1. Dengan menyimpan, assessment akan diproses oleh sistem.'),
                const SizedBox(height: 16),
                if (state.isLoading) const Center(child: CircularProgressIndicator()),
              ],
            ),
            isActive: state.currentStep >= 6,
            state: state.currentStep == 6 ? StepState.editing : StepState.indexed,
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _waktuAssessmentDateController.dispose();
    _waktuAssessmentTimeController.dispose();
    _cakupanWilayahController.dispose();
    _regionTerdampakController.dispose();
    _latitudeController.dispose();
    _longitudeController.dispose();
    _tanggalMulaiController.dispose();
    _jamMulaiController.dispose();
    _kronologiSingkatController.dispose();
    _penyebabUtamaController.dispose();
    _judulNarasiController.dispose();
    _isiNarasiController.dispose();
    _sebaranDampakController.dispose();
    _upayaPenangananController.dispose();
    _kendalaLapanganController.dispose();
    _kendalaTambahanController.dispose();
    _rekomendasiAksiController.dispose();
    _meninggalController.dispose();
    _hilangController.dispose();
    _lukaBeratController.dispose();
    _lukaRinganController.dispose();
    _terdampakJiwaController.dispose();
    _terdampakKkController.dispose();
    _pengungsiJiwaController.dispose();
    _pengungsiKkController.dispose();
    _pengungsiBalitaController.dispose();
    _pengungsiLansiaController.dispose();
    _pengungsiDisabilitasController.dispose();
    _pengungsiIbuHamilController.dispose();
    _rumahBeratController.dispose();
    _rumahSedangController.dispose();
    _rumahRinganController.dispose();
    _rumahTerendamController.dispose();
    _rumahTerancamController.dispose();
    _fasumKesehatanController.dispose();
    _fasumPendidikanController.dispose();
    _fasumIbadahController.dispose();
    _fasumPerkantoranController.dispose();
    _fasumPasarController.dispose();
    _fasumSanitasiController.dispose();
    _vitalJalanController.dispose();
    _vitalJembatanPutusController.dispose();
    _vitalJembatanRusakController.dispose();
    _vitalListrikController.dispose();
    _vitalTelekomunikasiController.dispose();
    _vitalAirBersihController.dispose();
    _lingkunganPertanianController.dispose();
    _lingkunganHutanController.dispose();
    _lingkunganUnggasController.dispose();
    _lingkunganKakiEmpatController.dispose();
    _lingkunganKolamController.dispose();
    _lingkunganNelayanController.dispose();
    // _ekonomiPersentaseController.dispose(); removed
    for (var controller in _kebutuhanControllers.values) {
      controller.dispose();
    }
    _kebutuhanRelawanController.dispose();
    _kebutuhanLogistikController.dispose();
    _kebutuhanPeralatanController.dispose();
    _kebutuhanMedisController.dispose();
    _kebutuhanPanganController.dispose();
    _kebutuhanLainnyaController.dispose();
    super.dispose();
  }
}
