import os

code = """
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import 'package:nurisk_mobile/features/public/report/presentation/notifiers/laporan_provider.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import 'package:nurisk_mobile/features/operasi/insiden/data/models/insiden_model.dart';
import 'package:nurisk_mobile/core/master/providers/master_providers.dart';
import '../notifiers/assessment_provider.dart';

class AssessmentWizardScreen extends ConsumerStatefulWidget {
  final String uuidInsiden;
  final dynamic insiden; 

  const AssessmentWizardScreen({Key? key, required this.uuidInsiden, this.insiden}) : super(key: key);

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

  // Controllers for Step 0: Informasi & Lokasi
  final _eventDateController = TextEditingController();
  final _eventTimeController = TextEditingController();
  String? _idKab;
  String? _idKec;
  String? _idDesa;
  final _alamatController = TextEditingController();
  final _latitudeController = TextEditingController();
  final _longitudeController = TextEditingController();
  String _jenisLaporan = 'kaji_cepat';
  bool _isLocating = false;
  String? _gpsError;

  // Controllers for Step 1: Dampak Manusia & Rentan
  final _meninggalController = TextEditingController(text: '0');
  final _hilangController = TextEditingController(text: '0');
  final _lukaBeratController = TextEditingController(text: '0');
  final _lukaRinganController = TextEditingController(text: '0');
  final _terdampakJiwaController = TextEditingController(text: '0');
  final _terdampakKkController = TextEditingController(text: '0');
  final _pengungsiJiwaController = TextEditingController(text: '0');
  final _pengungsiKkController = TextEditingController(text: '0');
  // Rentan
  final _rentanBalitaController = TextEditingController(text: '0');
  final _rentanLansiaController = TextEditingController(text: '0');
  final _rentanDisabilitasController = TextEditingController(text: '0');
  final _rentanIbuHamilController = TextEditingController(text: '0');

  // Controllers for Step 2: Infrastruktur & Rumah
  final _rumahBeratController = TextEditingController(text: '0');
  final _rumahSedangController = TextEditingController(text: '0');
  final _rumahRinganController = TextEditingController(text: '0');
  final _rumahTerendamController = TextEditingController(text: '0');
  final _rumahTerancamController = TextEditingController(text: '0');

  final _fasumPendidikanController = TextEditingController(text: '0');
  final _fasumKesehatanController = TextEditingController(text: '0');
  final _fasumIbadahController = TextEditingController(text: '0');
  final _fasumPerkantoranController = TextEditingController(text: '0');
  final _fasumJembatanPutusController = TextEditingController(text: '0');
  final _fasumJembatanRusakController = TextEditingController(text: '0');
  final _fasumSanitasiController = TextEditingController(text: '0');

  // Controllers for Step 3: Sarana Vital
  final _vitalListrikController = TextEditingController(text: '0');
  final _vitalTelkomController = TextEditingController(text: '0');
  final _vitalJalanController = TextEditingController(text: '0');
  final _vitalAirBersihController = TextEditingController(text: '0');

  // Controllers for Step 4: Lingkungan & Ekonomi
  final _lingkunganSawahController = TextEditingController(text: '0');
  final _lingkunganIrigasiController = TextEditingController(text: '0');
  final _lingkunganTernakUnggasController = TextEditingController(text: '0');
  final _lingkunganTernakKakiEmpatController = TextEditingController(text: '0');
  final _ekonomiPersentaseController = TextEditingController(text: '0');

  // Controllers for Step 5: Kebutuhan & Narasi
  final _kondisiMutakhirController = TextEditingController();
  final _upayaPenangananController = TextEditingController();
  // Dynamic kebutuhan_numerik map
  final Map<int, TextEditingController> _kebutuhanControllers = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(assessmentProvider.notifier).initialize(widget.uuidInsiden);
      
      final insidenObj = widget.insiden;
      DateTime? eventDateTime;
      String? defaultAddress;
      double? defaultLat;
      double? defaultLng;

      if (insidenObj is InsidenModel) {
        eventDateTime = insidenObj.waktuMulai;
        if (insidenObj.laporanAsal != null) {
          final lapor = insidenObj.laporanAsal!;
          eventDateTime ??= lapor.waktuKejadian;
          defaultLat = lapor.latitude;
          defaultLng = lapor.longitude;
          if (lapor.alamatLengkap != null && lapor.alamatLengkap!.isNotEmpty) {
            defaultAddress = lapor.alamatLengkap;
          } else if (lapor.titikKenal != null && lapor.titikKenal!.isNotEmpty) {
            defaultAddress = lapor.titikKenal;
          }
        }
      }
      
      final now = eventDateTime ?? DateTime.now();
      _eventDateController.text = "${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}";
      _eventTimeController.text = "${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}";
      
      if (defaultLat != null) _latitudeController.text = defaultLat.toString();
      if (defaultLng != null) _longitudeController.text = defaultLng.toString();
      if (defaultAddress != null) _alamatController.text = defaultAddress;
    });
  }

  @override
  void dispose() {
    _eventDateController.dispose();
    _eventTimeController.dispose();
    _alamatController.dispose();
    _latitudeController.dispose();
    _longitudeController.dispose();
    
    _meninggalController.dispose();
    _hilangController.dispose();
    _lukaBeratController.dispose();
    _lukaRinganController.dispose();
    _terdampakJiwaController.dispose();
    _terdampakKkController.dispose();
    _pengungsiJiwaController.dispose();
    _pengungsiKkController.dispose();
    
    _rentanBalitaController.dispose();
    _rentanLansiaController.dispose();
    _rentanDisabilitasController.dispose();
    _rentanIbuHamilController.dispose();

    _rumahBeratController.dispose();
    _rumahSedangController.dispose();
    _rumahRinganController.dispose();
    _rumahTerendamController.dispose();
    _rumahTerancamController.dispose();

    _fasumPendidikanController.dispose();
    _fasumKesehatanController.dispose();
    _fasumIbadahController.dispose();
    _fasumPerkantoranController.dispose();
    _fasumJembatanPutusController.dispose();
    _fasumJembatanRusakController.dispose();
    _fasumSanitasiController.dispose();

    _vitalListrikController.dispose();
    _vitalTelkomController.dispose();
    _vitalJalanController.dispose();
    _vitalAirBersihController.dispose();

    _lingkunganSawahController.dispose();
    _lingkunganIrigasiController.dispose();
    _lingkunganTernakUnggasController.dispose();
    _lingkunganTernakKakiEmpatController.dispose();
    _ekonomiPersentaseController.dispose();

    _kondisiMutakhirController.dispose();
    _upayaPenangananController.dispose();
    
    for (var controller in _kebutuhanControllers.values) {
      controller.dispose();
    }
    
    super.dispose();
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
          'waktu_assesment': "${_eventDateController.text} ${_eventTimeController.text}:00",
          'id_kecamatan': _idKec,
          'id_desa': _idDesa,
          'cakupan_wilayah_deskripsi': _alamatController.text,
          'latitude': double.tryParse(_latitudeController.text),
          'longitude': double.tryParse(_longitudeController.text),
          'jenis_laporan': _jenisLaporan,
        });
        valid = true;
      }
    } else if (currentStep == 1) {
      if (_formKey2.currentState!.validate()) {
        notifier.updateFormData({
          'dampak_manusia': {
            'meninggal': int.tryParse(_meninggalController.text) ?? 0,
            'hilang': int.tryParse(_hilangController.text) ?? 0,
            'luka_berat': int.tryParse(_lukaBeratController.text) ?? 0,
            'luka_ringan': int.tryParse(_lukaRinganController.text) ?? 0,
            'dampak_manusia': int.tryParse(_terdampakJiwaController.text) ?? 0,
            'terdampak_kk': int.tryParse(_terdampakKkController.text) ?? 0,
            'pengungsi_jiwa': int.tryParse(_pengungsiJiwaController.text) ?? 0,
            'pengungsi_kk': int.tryParse(_pengungsiKkController.text) ?? 0,
            'pengungsi_balita': int.tryParse(_rentanBalitaController.text) ?? 0,
            'pengungsi_lansia': int.tryParse(_rentanLansiaController.text) ?? 0,
            'pengungsi_disabilitas': int.tryParse(_rentanDisabilitasController.text) ?? 0,
            'pengungsi_ibu_hamil': int.tryParse(_rentanIbuHamilController.text) ?? 0,
          },
        });
        valid = true;
      }
    } else if (currentStep == 2) {
      if (_formKey3.currentState!.validate()) {
        notifier.updateFormData({
          'dampak_infrastruktur': {
            'rumah_rusak_berat': int.tryParse(_rumahBeratController.text) ?? 0,
            'rumah_rusak_sedang': int.tryParse(_rumahSedangController.text) ?? 0,
            'rumah_rusak_ringan': int.tryParse(_rumahRinganController.text) ?? 0,
            'rumah_terendam': int.tryParse(_rumahTerendamController.text) ?? 0,
            'rumah_terancam': int.tryParse(_rumahTerancamController.text) ?? 0,
            'fasilitas_pendidikan_rusak': int.tryParse(_fasumPendidikanController.text) ?? 0,
            'fasilitas_kesehatan_rusak': int.tryParse(_fasumKesehatanController.text) ?? 0,
            'tempat_ibadah_rusak': int.tryParse(_fasumIbadahController.text) ?? 0,
            'kantor_pemerintah_rusak': int.tryParse(_fasumPerkantoranController.text) ?? 0,
            'jembatan_putus': int.tryParse(_fasumJembatanPutusController.text) ?? 0,
            'jembatan_rusak': int.tryParse(_fasumJembatanRusakController.text) ?? 0,
            'sanitasi': int.tryParse(_fasumSanitasiController.text) ?? 0,
          }
        });
        valid = true;
      }
    } else if (currentStep == 3) {
      if (_formKey4.currentState!.validate()) {
        final existingInfra = Map<String, dynamic>.from(ref.read(assessmentProvider).formData['dampak_infrastruktur'] ?? {});
        existingInfra.addAll({
            'jaringan_listrik_padam_kk': int.tryParse(_vitalListrikController.text) ?? 0,
            'jaringan_komunikasi_putus': int.tryParse(_vitalTelkomController.text) ?? 0,
            'jalan_rusak_km': double.tryParse(_vitalJalanController.text) ?? 0.0,
            'sarana_air_bersih_rusak': int.tryParse(_vitalAirBersihController.text) ?? 0,
        });
        notifier.updateFormData({
          'dampak_infrastruktur': existingInfra
        });
        valid = true;
      }
    } else if (currentStep == 4) {
      if (_formKey5.currentState!.validate()) {
        final existingInfra = Map<String, dynamic>.from(ref.read(assessmentProvider).formData['dampak_infrastruktur'] ?? {});
        existingInfra.addAll({
            'sawah_ha': double.tryParse(_lingkunganSawahController.text) ?? 0.0,
            'irigasi': double.tryParse(_lingkunganIrigasiController.text) ?? 0.0,
        });
        notifier.updateFormData({
          'dampak_infrastruktur': existingInfra,
          'dampak_lingkungan': {
            'unggas': int.tryParse(_lingkunganTernakUnggasController.text) ?? 0,
            'kaki_empat': int.tryParse(_lingkunganTernakKakiEmpatController.text) ?? 0,
          },
          'dampak_ekonomi': {
            'persentase_ekonomi_terdampak': _ekonomiPersentaseController.text,
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
                 'nama_kebutuhan': item.namaItem, // Backend handles string match for V1 + V2 sync
                 'jumlah': jml,
                 'satuan': item.satuanDefault,
              });
           }
        }
      
        notifier.updateFormData({
          'cakupan_wilayah_deskripsi': _kondisiMutakhirController.text, // Backend usually expects narasi in some text fields, will put it in cakupan_wilayah_deskripsi if none exists
          'kebutuhan_mendesak': kebutuhanList,
        });
        valid = true;
      }
    } else if (currentStep == 6) {
      // Submit form
      final success = await notifier.submitAssessment(widget.uuidInsiden);
      if (success) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Assessment berhasil disubmit!')),
          );
          context.pop();
        }
      } else {
        if (mounted) {
          final err = ref.read(assessmentProvider).error;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(err ?? 'Gagal submit'), backgroundColor: Colors.red),
          );
        }
      }
      return;
    }

    if (valid) {
      notifier.nextStep();
    }
  }

  Widget _buildTextField(TextEditingController controller, String label, {bool isNumber = false, int maxLines = 1, bool required = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12.0),
      child: TextFormField(
        controller: controller,
        keyboardType: isNumber ? const TextInputType.numberWithOptions(decimal: true) : TextInputType.text,
        maxLines: maxLines,
        decoration: InputDecoration(
          labelText: label,
          border: const OutlineInputBorder(),
          isDense: true,
        ),
        validator: required ? (val) => val == null || val.isEmpty ? 'Wajib diisi' : null : null,
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16.0),
      child: Text(
        title,
        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.indigo),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(assessmentProvider);
    final notifier = ref.read(assessmentProvider.notifier);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Form Assessment Komprehensif'),
      ),
      body: Stepper(
        type: StepperType.vertical,
        currentStep: state.currentStep,
        onStepContinue: () => _onStepContinue(state.currentStep, notifier),
        onStepCancel: () => notifier.previousStep(),
        onStepTapped: (step) => notifier.setStep(step),
        steps: [
          Step(
            title: const Text('Informasi & Lokasi'),
            content: Form(
              key: _formKey1,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildTextField(_eventDateController, 'Tanggal Assessment (YYYY-MM-DD)', required: true),
                  _buildTextField(_eventTimeController, 'Waktu Assessment (HH:MM)'),
                  DropdownButtonFormField<String>(
                    value: _jenisLaporan,
                    decoration: const InputDecoration(labelText: 'Jenis Laporan', border: OutlineInputBorder(), isDense: true),
                    items: const [
                      DropdownMenuItem(value: 'kaji_cepat', child: Text('Kaji Cepat')),
                      DropdownMenuItem(value: 'pendataan_lanjutan', child: Text('Pendataan Lanjutan')),
                    ],
                    onChanged: (val) {
                      if (val != null) setState(() => _jenisLaporan = val);
                    },
                  ),
                  const SizedBox(height: 12),
                  ref.watch(kabupatenListProvider).when(
                        data: (kabList) => DropdownButtonFormField<String>(
                          decoration: const InputDecoration(
                            labelText: 'Kabupaten / Kota *',
                            border: OutlineInputBorder(),
                            isDense: true,
                          ),
                          value: _idKab,
                          items: kabList.map((k) => DropdownMenuItem(value: k.id, child: Text(k.nama))).toList(),
                          onChanged: (val) {
                            setState(() {
                              _idKab = val;
                              _idKec = null;
                              _idDesa = null;
                            });
                          },
                        ),
                        loading: () => const Center(child: CircularProgressIndicator()),
                        error: (err, _) => Text(DioExceptionMapper.toUserMessage(err)),
                      ),
                  const SizedBox(height: 12),
                  if (_idKab != null) ...[
                    ref.watch(kecamatanListProvider(_idKab!)).when(
                          data: (kecList) => DropdownButtonFormField<String>(
                            decoration: const InputDecoration(labelText: 'Kecamatan *', border: OutlineInputBorder(), isDense: true),
                            value: _idKec,
                            items: kecList.map((k) => DropdownMenuItem(value: k.id, child: Text(k.nama))).toList(),
                            onChanged: (val) {
                              setState(() {
                                _idKec = val;
                                _idDesa = null;
                              });
                            },
                          ),
                          loading: () => const Center(child: CircularProgressIndicator()),
                          error: (err, _) => Text(DioExceptionMapper.toUserMessage(err)),
                        ),
                    const SizedBox(height: 12),
                  ],
                  if (_idKec != null) ...[
                    ref.watch(desaListProvider(_idKec!)).when(
                          data: (desaList) => DropdownButtonFormField<String>(
                            decoration: const InputDecoration(labelText: 'Desa / Kelurahan *', border: OutlineInputBorder(), isDense: true),
                            value: _idDesa,
                            items: desaList.map((d) => DropdownMenuItem(value: d.id, child: Text(d.nama))).toList(),
                            onChanged: (val) {
                              setState(() {
                                _idDesa = val;
                              });
                            },
                          ),
                          loading: () => const Center(child: CircularProgressIndicator()),
                          error: (err, _) => Text(DioExceptionMapper.toUserMessage(err)),
                        ),
                    const SizedBox(height: 12),
                  ],
                  _buildTextField(_alamatController, 'Alamat / Cakupan Deskripsi', maxLines: 2),
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
            title: const Text('Dampak Manusia & Rentan'),
            content: Form(
              key: _formKey2,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Korban Jiwa'),
                  Row(children: [ Expanded(child: _buildTextField(_meninggalController, 'Meninggal', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_hilangController, 'Hilang', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_lukaBeratController, 'Luka Berat', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_lukaRinganController, 'Luka Ringan', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_terdampakJiwaController, 'Terdampak (Jiwa)', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_terdampakKkController, 'Terdampak (KK)', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_pengungsiJiwaController, 'Pengungsi Jiwa', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_pengungsiKkController, 'Pengungsi KK', isNumber: true)) ]),
                  
                  _buildSectionHeader('Kelompok Rentan (Jiwa)'),
                  Row(children: [ Expanded(child: _buildTextField(_rentanBalitaController, 'Balita', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_rentanLansiaController, 'Lansia', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_rentanIbuHamilController, 'Ibu Hamil', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_rentanDisabilitasController, 'Disabilitas', isNumber: true)) ]),
                ],
              ),
            ),
            isActive: state.currentStep >= 1,
            state: state.currentStep > 1 ? StepState.complete : (state.currentStep == 1 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Infrastruktur & Rumah'),
            content: Form(
              key: _formKey3,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Kerusakan Rumah (Unit)'),
                  Row(children: [ Expanded(child: _buildTextField(_rumahBeratController, 'Rusak Berat', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_rumahSedangController, 'Rusak Sedang', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_rumahRinganController, 'Rusak Ringan', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_rumahTerendamController, 'Terendam', isNumber: true)) ]),
                  _buildTextField(_rumahTerancamController, 'Terancam', isNumber: true),

                  _buildSectionHeader('Fasilitas Umum (Unit)'),
                  Row(children: [ Expanded(child: _buildTextField(_fasumPendidikanController, 'Pendidikan', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_fasumKesehatanController, 'Kesehatan', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_fasumIbadahController, 'Ibadah', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_fasumPerkantoranController, 'Perkantoran', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_fasumJembatanPutusController, 'Jembatan Putus', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_fasumJembatanRusakController, 'Jembatan Rusak', isNumber: true)) ]),
                  _buildTextField(_fasumSanitasiController, 'Sanitasi', isNumber: true),
                ],
              ),
            ),
            isActive: state.currentStep >= 2,
            state: state.currentStep > 2 ? StepState.complete : (state.currentStep == 2 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Sarana Vital'),
            content: Form(
              key: _formKey4,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Dampak Infrastruktur Vital'),
                  _buildTextField(_vitalJalanController, 'Jalan Rusak (Km)', isNumber: true),
                  _buildTextField(_vitalListrikController, 'Listrik Padam (KK)', isNumber: true),
                  _buildTextField(_vitalTelkomController, 'Komunikasi Putus (Tower/Area)', isNumber: true),
                  _buildTextField(_vitalAirBersihController, 'Sarana Air Bersih Rusak (Unit)', isNumber: true),
                ],
              ),
            ),
            isActive: state.currentStep >= 3,
            state: state.currentStep > 3 ? StepState.complete : (state.currentStep == 3 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Lingkungan & Ekonomi'),
            content: Form(
              key: _formKey5,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Pertanian & Peternakan'),
                  Row(children: [ Expanded(child: _buildTextField(_lingkunganSawahController, 'Sawah Rusak (Ha)', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_lingkunganIrigasiController, 'Irigasi Rusak (Km)', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_lingkunganTernakKakiEmpatController, 'Ternak Sapi/Kambing Mati', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_lingkunganTernakUnggasController, 'Unggas Mati', isNumber: true)) ]),
                  _buildSectionHeader('Ekonomi'),
                  _buildTextField(_ekonomiPersentaseController, 'Persentase Ekonomi Terdampak (e.g. 50%)', isNumber: true),
                ],
              ),
            ),
            isActive: state.currentStep >= 4,
            state: state.currentStep > 4 ? StepState.complete : (state.currentStep == 4 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Kebutuhan & Narasi'),
            content: Form(
              key: _formKey6,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Narasi Kondisi'),
                  _buildTextField(_kondisiMutakhirController, 'Kondisi Mutakhir', maxLines: 3),
                  
                  _buildSectionHeader('Kebutuhan Numerik (Dari Master)'),
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
}
"""

with open('mobile/app/lib/features/operasi/assessment/presentation/screens/assessment_wizard_screen.dart', 'w') as f:
    f.write(code)
