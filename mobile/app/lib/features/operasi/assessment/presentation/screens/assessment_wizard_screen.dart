import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import 'package:nurisk_mobile/features/public/report/presentation/notifiers/laporan_provider.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import '../notifiers/assessment_provider.dart';

class AssessmentWizardScreen extends ConsumerStatefulWidget {
  final String uuidInsiden;

  const AssessmentWizardScreen({Key? key, required this.uuidInsiden}) : super(key: key);

  @override
  ConsumerState<AssessmentWizardScreen> createState() => _AssessmentWizardScreenState();
}

class _AssessmentWizardScreenState extends ConsumerState<AssessmentWizardScreen> {
  final _formKey1 = GlobalKey<FormState>();
  final _formKey2 = GlobalKey<FormState>();
  final _formKey3 = GlobalKey<FormState>();

  // Controllers for Step 1: Informasi & Lokasi
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
  String? _alamatGps;

  // Controllers for Step 2: Dampak Kerusakan
  // Korban Jiwa
  final _meninggalController = TextEditingController(text: '0');
  final _hilangController = TextEditingController(text: '0');
  final _lukaBeratController = TextEditingController(text: '0');
  final _lukaRinganController = TextEditingController(text: '0');
  final _terdampakJiwaController = TextEditingController(text: '0');
  final _pengungsiJiwaController = TextEditingController(text: '0');
  final _pengungsiKkController = TextEditingController(text: '0');
  
  // Kerusakan Rumah
  final _rumahBeratController = TextEditingController(text: '0');
  final _rumahSedangController = TextEditingController(text: '0');
  final _rumahRinganController = TextEditingController(text: '0');

  // Fasum
  final _fasumSanitasiController = TextEditingController(text: '0');
  final _fasumPendidikanController = TextEditingController(text: '0');
  final _fasumKesehatanController = TextEditingController(text: '0');
  final _fasumIbadahController = TextEditingController(text: '0');
  final _fasumJembatanController = TextEditingController(text: '0');

  // Sarana Vital & Lingkungan
  final _vitalAirController = TextEditingController(text: '0');
  final _vitalListrikController = TextEditingController(text: '0');
  final _vitalTelkomController = TextEditingController(text: '0');
  final _vitalJalanController = TextEditingController(text: '0');
  final _lingkunganSawahController = TextEditingController(text: '0');
  final _lingkunganTernakController = TextEditingController(text: '0');

  // Controllers for Step 3: Kebutuhan Lapangan
  final _kondisiMutakhirController = TextEditingController();
  final _upayaPenangananController = TextEditingController();
  final _kebutuhanDanaController = TextEditingController();
  final _kebutuhanLogistikController = TextEditingController();
  final _kebutuhanMedisController = TextEditingController();
  final _kebutuhanRelawanController = TextEditingController();

  // Kebutuhan Mendesak
  final _kebutuhanSembakoController = TextEditingController(text: '0');
  final _kebutuhanBerasController = TextEditingController(text: '0');
  final _kebutuhanMieController = TextEditingController(text: '0');
  final _kebutuhanAirController = TextEditingController(text: '0');
  final _kebutuhanSelimutController = TextEditingController(text: '0');
  final _kebutuhanObatController = TextEditingController(text: '0');

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(assessmentProvider.notifier).initialize(widget.uuidInsiden);
      
      // Default date
      final now = DateTime.now();
      _eventDateController.text = "${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}";
      _eventTimeController.text = "${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}";
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
    _pengungsiJiwaController.dispose();
    _pengungsiKkController.dispose();

    _rumahBeratController.dispose();
    _rumahSedangController.dispose();
    _rumahRinganController.dispose();

    _fasumSanitasiController.dispose();
    _fasumPendidikanController.dispose();
    _fasumKesehatanController.dispose();
    _fasumIbadahController.dispose();
    _fasumJembatanController.dispose();

    _vitalAirController.dispose();
    _vitalListrikController.dispose();
    _vitalTelkomController.dispose();
    _vitalJalanController.dispose();
    _lingkunganSawahController.dispose();
    _lingkunganTernakController.dispose();

    _kondisiMutakhirController.dispose();
    _upayaPenangananController.dispose();
    _kebutuhanDanaController.dispose();
    _kebutuhanLogistikController.dispose();
    _kebutuhanMedisController.dispose();
    _kebutuhanRelawanController.dispose();

    _kebutuhanSembakoController.dispose();
    _kebutuhanBerasController.dispose();
    _kebutuhanMieController.dispose();
    _kebutuhanAirController.dispose();
    _kebutuhanSelimutController.dispose();
    _kebutuhanObatController.dispose();
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
      _alamatGps = result.point!.toDisplayString();
      _isLocating = false;
      _gpsError = null;
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
          'event_date': _eventDateController.text,
          'event_time': _eventTimeController.text,
          'id_kecamatan': _idKec,
          'id_desa': _idDesa,
          'alamat_spesifik': _alamatController.text,
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
            'pengungsi_jiwa': int.tryParse(_pengungsiJiwaController.text) ?? 0,
            'pengungsi_kk': int.tryParse(_pengungsiKkController.text) ?? 0,
          },
          'dampak_rumah': {
            'berat': int.tryParse(_rumahBeratController.text) ?? 0,
            'sedang': int.tryParse(_rumahSedangController.text) ?? 0,
            'ringan': int.tryParse(_rumahRinganController.text) ?? 0,
          },
          'dampak_fasum': {
            'sanitas': int.tryParse(_fasumSanitasiController.text) ?? 0,
            'pendidikan': int.tryParse(_fasumPendidikanController.text) ?? 0,
            'kesehatan': int.tryParse(_fasumKesehatanController.text) ?? 0,
            'ibadah': int.tryParse(_fasumIbadahController.text) ?? 0,
            'jembatan': int.tryParse(_fasumJembatanController.text) ?? 0,
          },
          'dampak_vital': {
            'air': int.tryParse(_vitalAirController.text) ?? 0,
            'listrik': int.tryParse(_vitalListrikController.text) ?? 0,
            'telkom': int.tryParse(_vitalTelkomController.text) ?? 0,
            'jalan': double.tryParse(_vitalJalanController.text) ?? 0.0,
          },
          'dampak_lingkungan': {
            'sawah': double.tryParse(_lingkunganSawahController.text) ?? 0.0,
            'ternak': int.tryParse(_lingkunganTernakController.text) ?? 0,
          }
        });
        valid = true;
      }
    } else if (currentStep == 2) {
      if (_formKey3.currentState!.validate()) {
        notifier.updateFormData({
          'kondisi_mutakhir': _kondisiMutakhirController.text,
          'upaya_penanganan': _upayaPenangananController.text,
          'kebutuhan': {
            'dana': _kebutuhanDanaController.text,
            'logistik': _kebutuhanLogistikController.text,
            'medis': _kebutuhanMedisController.text,
            'relawan': _kebutuhanRelawanController.text,
          },
          'needs_numeric': {
            'sembako': int.tryParse(_kebutuhanSembakoController.text) ?? 0,
            'beras': int.tryParse(_kebutuhanBerasController.text) ?? 0,
            'mie_instan': int.tryParse(_kebutuhanMieController.text) ?? 0,
            'air_bersih': int.tryParse(_kebutuhanAirController.text) ?? 0,
            'selimut': int.tryParse(_kebutuhanSelimutController.text) ?? 0,
            'obat_obatan': int.tryParse(_kebutuhanObatController.text) ?? 0,
          }
        });
        valid = true;
      }
    } else if (currentStep == 3) {
      // Submit form
      final success = await notifier.submitAssessment(widget.uuidInsiden);
      if (success) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Assessment berhasil disubmit!')),
          );
          // Return to detail screen by popping the wizard
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
                  _buildTextField(_eventDateController, 'Tanggal Kejadian (YYYY-MM-DD)', required: true),
                  _buildTextField(_eventTimeController, 'Waktu Kejadian (HH:MM)'),
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
                          items: kabList
                              .map((k) => DropdownMenuItem(value: k.id, child: Text(k.nama)))
                              .toList(),
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
                            decoration: const InputDecoration(
                              labelText: 'Kecamatan *',
                              border: OutlineInputBorder(),
                              isDense: true,
                            ),
                            value: _idKec,
                            items: kecList
                                .map((k) => DropdownMenuItem(value: k.id, child: Text(k.nama)))
                                .toList(),
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
                            decoration: const InputDecoration(
                              labelText: 'Desa / Kelurahan *',
                              border: OutlineInputBorder(),
                              isDense: true,
                            ),
                            value: _idDesa,
                            items: desaList
                                .map((d) => DropdownMenuItem(value: d.id, child: Text(d.nama)))
                                .toList(),
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
                  _buildTextField(_alamatController, 'Alamat Spesifik', maxLines: 2),
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _isLocating ? null : _getCurrentLocation,
                          icon: _isLocating
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : const Icon(Icons.my_location),
                          label: Text(_isLocating
                              ? 'Mencari...'
                              : 'Dapatkan GPS'),
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
            title: const Text('Dampak Kerusakan'),
            content: Form(
              key: _formKey2,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader('Korban Jiwa'),
                  Row(children: [ Expanded(child: _buildTextField(_meninggalController, 'Meninggal', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_hilangController, 'Hilang', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_lukaBeratController, 'Luka Berat', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_lukaRinganController, 'Luka Ringan', isNumber: true)) ]),
                  _buildTextField(_terdampakJiwaController, 'Terdampak Jiwa', isNumber: true),
                  Row(children: [ Expanded(child: _buildTextField(_pengungsiJiwaController, 'Pengungsi Jiwa', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_pengungsiKkController, 'Pengungsi KK', isNumber: true)) ]),
                  
                  _buildSectionHeader('Kerusakan Rumah'),
                  Row(children: [ Expanded(child: _buildTextField(_rumahBeratController, 'Berat', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_rumahSedangController, 'Sedang', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_rumahRinganController, 'Ringan', isNumber: true)) ]),

                  _buildSectionHeader('Fasilitas Umum Rusak'),
                  Row(children: [ Expanded(child: _buildTextField(_fasumSanitasiController, 'Sanitasi', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_fasumPendidikanController, 'Pendidikan', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_fasumKesehatanController, 'Kesehatan', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_fasumIbadahController, 'Ibadah', isNumber: true)) ]),
                  _buildTextField(_fasumJembatanController, 'Jembatan', isNumber: true),

                  _buildSectionHeader('Sarana Vital & Lingkungan'),
                  Row(children: [ Expanded(child: _buildTextField(_vitalAirController, 'Air Bersih', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_vitalListrikController, 'Listrik', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_vitalTelkomController, 'Telkom', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_vitalJalanController, 'Jalan (km)', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_lingkunganSawahController, 'Sawah (ha)', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_lingkunganTernakController, 'Ternak', isNumber: true)) ]),
                ],
              ),
            ),
            isActive: state.currentStep >= 1,
            state: state.currentStep > 1 ? StepState.complete : (state.currentStep == 1 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Kebutuhan Lapangan'),
            content: Form(
              key: _formKey3,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildTextField(_kondisiMutakhirController, 'Kondisi Mutakhir / Narasi', maxLines: 3),
                  _buildTextField(_upayaPenangananController, 'Upaya Penanganan', maxLines: 3),
                  
                  _buildSectionHeader('Kebutuhan Umum'),
                  _buildTextField(_kebutuhanDanaController, 'Kebutuhan Dana', maxLines: 2),
                  _buildTextField(_kebutuhanLogistikController, 'Kebutuhan Logistik', maxLines: 2),
                  _buildTextField(_kebutuhanMedisController, 'Kebutuhan Medis', maxLines: 2),
                  _buildTextField(_kebutuhanRelawanController, 'Kebutuhan Relawan', maxLines: 2),

                  _buildSectionHeader('Kebutuhan Mendesak (Numerik)'),
                  Row(children: [ Expanded(child: _buildTextField(_kebutuhanSembakoController, 'Paket Sembako', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_kebutuhanBerasController, 'Beras (kg)', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_kebutuhanAirController, 'Air (L)', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_kebutuhanMieController, 'Mie (Dus)', isNumber: true)) ]),
                  Row(children: [ Expanded(child: _buildTextField(_kebutuhanSelimutController, 'Selimut', isNumber: true)), const SizedBox(width: 8), Expanded(child: _buildTextField(_kebutuhanObatController, 'Paket Obat', isNumber: true)) ]),
                ],
              ),
            ),
            isActive: state.currentStep >= 2,
            state: state.currentStep > 2 ? StepState.complete : (state.currentStep == 2 ? StepState.editing : StepState.indexed),
          ),
          Step(
            title: const Text('Konfirmasi & Simpan'),
            content: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Pastikan semua data telah akurat. Dengan menyimpan, assessment akan diproses oleh sistem.'),
                const SizedBox(height: 16),
                if (state.isLoading) const Center(child: CircularProgressIndicator()),
              ],
            ),
            isActive: state.currentStep >= 3,
            state: state.currentStep == 3 ? StepState.editing : StepState.indexed,
          ),
        ],
      ),
    );
  }
}
