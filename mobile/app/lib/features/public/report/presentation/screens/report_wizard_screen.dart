import 'dart:io';
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import 'package:nurisk_mobile/features/auth/presentation/notifiers/auth_state_provider.dart';
import 'package:nurisk_mobile/features/profile/presentation/notifiers/profile_notifier.dart';
import '../../presentation/notifiers/laporan_provider.dart';
import '../../data/repositories/laporan_repository_impl.dart';

class ReportWizardScreen extends ConsumerStatefulWidget {
  const ReportWizardScreen({Key? key}) : super(key: key);

  @override
  ConsumerState<ReportWizardScreen> createState() => _ReportWizardScreenState();
}

class _ReportWizardScreenState extends ConsumerState<ReportWizardScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _autoFillProfile();
    });
  }

  void _autoFillProfile() {
    final authState = ref.read(authStateProvider);
    if (authState.isAuthenticated) {
      if (authState.userName != null) {
        _namaController.text = authState.userName!;
      }
      
      // Attempt to get phone number from profile provider if available
      try {
        final profileState = ref.read(profileProvider);
        if (profileState is AsyncData && profileState.value != null) {
          final profileData = profileState.value!;
          if (profileData.identity.containsKey('no_hp') && profileData.identity['no_hp'] != null) {
            _hpController.text = profileData.identity['no_hp'].toString();
          }
          if (_namaController.text.isEmpty && profileData.identity.containsKey('nama_lengkap')) {
            _namaController.text = profileData.identity['nama_lengkap'].toString();
          }
        }
      } catch (e) {
        // Ignore
      }
    }
  }

  int _currentStep = 0;
  bool _isSubmitting = false;
  bool _isSuccess = false;
  String? _submitError;

  final _namaController = TextEditingController();
  final _hpController = TextEditingController();
  final _titikKenalController = TextEditingController();
  final _deskripsiController = TextEditingController();

  int? _idJenisBencana;
  String? _idKab;
  String? _idKec;
  String? _idDesa;
  DateTime _waktuKejadian = DateTime.now();
  double? _latitude;
  double? _longitude;
  String? _alamatGps;
  File? _fotoFile;

  bool _isLocating = false;
  String? _gpsError;

  @override
  void dispose() {
    _namaController.dispose();
    _hpController.dispose();
    _titikKenalController.dispose();
    _deskripsiController.dispose();
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
      _latitude = result.point!.latitude;
      _longitude = result.point!.longitude;
      _alamatGps = result.point!.toDisplayString();
      _isLocating = false;
      _gpsError = null;
    });
  }

  Future<void> _pickFoto() async {
    final media = ref.read(runtimeServicesProvider).media;
    final result = await media.takePhoto();

    if (!mounted) return;

    if (result.isSuccess) {
      setState(() {
        _fotoFile = result.file;
      });
    } else if (result.isPermissionDenied) {
      _showError(result.message ?? 'Izin kamera ditolak.');
    } else if (!result.isCancelled) {
      _showError(result.message ?? 'Gagal mengambil foto.');
    }
  }

  Future<void> _pickFotoGallery() async {
    final media = ref.read(runtimeServicesProvider).media;
    final result = await media.pickFromGallery();

    if (!mounted) return;

    if (result.isSuccess) {
      setState(() {
        _fotoFile = result.file;
      });
    } else if (result.isPermissionDenied) {
      _showError(result.message ?? 'Izin galeri ditolak.');
    } else if (!result.isCancelled) {
      _showError(result.message ?? 'Gagal memilih foto.');
    }
  }

  Future<void> _selectDateTime() async {
    final now = DateTime.now();
    final date = await showDatePicker(
      context: context,
      initialDate: _waktuKejadian,
      firstDate: DateTime(2020),
      lastDate: now,
      helpText: 'Pilih tanggal kejadian',
    );

    if (!mounted) return;
    if (date == null) return;

    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_waktuKejadian),
      helpText: 'Pilih waktu kejadian',
    );

    if (!mounted) return;
    if (time == null) return;

    setState(() {
      _waktuKejadian = DateTime(
        date.year,
        date.month,
        date.day,
        time.hour,
        time.minute,
      );
    });
  }

  void _nextStep() {
    if (_isSubmitting || _isSuccess) return;

    if (_currentStep == 0) {
      if (_namaController.text.trim().isEmpty) {
        _showError('Nama lengkap harus diisi');
        return;
      }
      if (_hpController.text.trim().length < 10 || _hpController.text.trim().length > 20) {
        _showError('Nomor HP harus 10-20 digit');
        return;
      }
    }

    if (_currentStep == 1) {
      if (_latitude == null || _longitude == null) {
        _showError('Lokasi GPS wajib diisi. Klik "Dapatkan Lokasi Saya".');
        return;
      }
      if (_idKab == null) {
        _showError('Pilih Kabupaten/Kota');
        return;
      }
      if (_idKec == null) {
        _showError('Pilih Kecamatan');
        return;
      }
      if (_idDesa == null) {
        _showError('Pilih Desa/Kelurahan');
        return;
      }
      if (_titikKenalController.text.trim().isEmpty) {
        _showError('Titik kenal lokasi harus diisi');
        return;
      }
    }

    if (_currentStep == 2) {
      if (_idJenisBencana == null) {
        _showError('Pilih jenis bencana');
        return;
      }
      if (_deskripsiController.text.trim().isEmpty) {
        _showError('Deskripsi kejadian harus diisi');
        return;
      }
    }

    if (_currentStep == 3) {
      if (_fotoFile == null) {
        _showError('Ambil foto kejadian');
        return;
      }
    }

    if (_currentStep == 4) {
      _submitReport();
      return;
    }

    setState(() {
      _currentStep++;
    });
  }

  void _previousStep() {
    if (_currentStep > 0) {
      setState(() {
        _currentStep--;
      });
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: Colors.red),
    );
  }

  Future<void> _submitReport() async {
    if (_isSubmitting || _isSuccess) return;

    setState(() {
      _isSubmitting = true;
      _submitError = null;
    });

    try {
      final repository = ref.read(laporanRepositoryProvider);
      final result = await repository.createLaporan(
        idJenisBencana: _idJenisBencana!,
        namaPelapor: _namaController.text.trim(),
        hpPelapor: _hpController.text.trim(),
        keteranganSituasi: _deskripsiController.text.trim(),
        titikKenal: _titikKenalController.text.trim(),
        waktuKejadian: _waktuKejadian,
        latitude: _latitude!,
        longitude: _longitude!,
        idKab: _idKab,
        idKec: _idKec,
        idDesa: _idDesa,
        fotoPath: _fotoFile?.path,
      );

      if (mounted) {
        setState(() {
          _isSuccess = true;
        });
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            title: const Text('Laporan Berhasil'),
            content: Text(
              'Terima kasih ${_namaController.text.trim()}.\n'
              'Kode laporan Anda: ${result.kodeKejadian}\n'
              'Tim NU Peduli akan menindaklanjuti laporan Anda.',
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(context);
                  ref.read(runtimeServicesProvider).navigation.pop();
                },
                child: const Text('Selesai'),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _submitError = DioExceptionMapper.toUserMessage(e));
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Lapor Bencana'),
        centerTitle: true,
        elevation: 0,
        scrolledUnderElevation: 1,
        backgroundColor: Colors.transparent,
        flexibleSpace: ClipRRect(
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
            child: Container(
              color: Theme.of(context).scaffoldBackgroundColor.withValues(alpha: 0.6),
            ),
          ),
        ),
        surfaceTintColor: Colors.transparent,
      ),
      body: Stepper(
        type: StepperType.vertical,
        currentStep: _currentStep,
        onStepContinue: _nextStep,
        onStepCancel: _previousStep,
        controlsBuilder: (context, details) {
          return Padding(
            padding: const EdgeInsets.only(top: 16.0),
            child: Row(
              children: [
                ElevatedButton(
                  onPressed: (_isSubmitting || _isSuccess) ? null : details.onStepContinue,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Theme.of(context).primaryColor,
                    foregroundColor: Colors.white,
                  ),
                  child: _isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : Text(_currentStep == 4 ? 'Kirim Laporan' : 'Selanjutnya'),
                ),
                if (_currentStep > 0)
                  TextButton(
                    onPressed: details.onStepCancel,
                    child: const Text('Kembali'),
                  ),
              ],
            ),
          );
        },
        steps: [
          Step(
            title: const Text('Data Pelapor'),
            content: Column(
              children: [
                TextFormField(
                  controller: _namaController,
                  decoration: const InputDecoration(
                    labelText: 'Nama Lengkap *',
                    hintText: 'Nama lengkap pelapor',
                    border: OutlineInputBorder(),
                  ),
                  textCapitalization: TextCapitalization.words,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _hpController,
                  decoration: const InputDecoration(
                    labelText: 'Nomor HP *',
                    hintText: 'Contoh: 08123456789',
                    border: OutlineInputBorder(),
                  ),
                  keyboardType: TextInputType.phone,
                  maxLength: 20,
                ),
              ],
            ),
            isActive: _currentStep >= 0,
            state: _currentStep > 0 ? StepState.complete : StepState.indexed,
          ),
          Step(
            title: const Text('Lokasi Kejadian'),
            content: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
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
                            ? 'Mendapatkan lokasi...'
                            : 'Dapatkan Lokasi Saya'),
                      ),
                    ),
                  ],
                ),
                if (_latitude != null && _longitude != null) ...[
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.green.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.green.shade200),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.check_circle, color: Colors.green, size: 20),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            _alamatGps ?? '${_latitude!.toStringAsFixed(6)}, ${_longitude!.toStringAsFixed(6)}',
                            style: const TextStyle(color: Colors.green, fontWeight: FontWeight.w600),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
                if (_gpsError != null) ...[
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.red.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.red.shade200),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.warning, color: Colors.red, size: 20),
                        const SizedBox(width: 8),
                        Expanded(child: Text(_gpsError!, style: TextStyle(color: Colors.red.shade700))),
                      ],
                    ),
                  ),
                ],
                const SizedBox(height: 16),
                ref.watch(kabupatenListProvider).when(
                      data: (kabList) => DropdownButtonFormField<String>(
                        decoration: const InputDecoration(
                          labelText: 'Kabupaten / Kota *',
                          border: OutlineInputBorder(),
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
                      loading: () => const SizedBox(
                        height: 56,
                        child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
                      ),
                      error: (err, _) => Text(DioExceptionMapper.toUserMessage(err)),
                    ),
                if (_idKab != null) ...[
                  const SizedBox(height: 12),
                  ref.watch(kecamatanListProvider(_idKab!)).when(
                        data: (kecList) => DropdownButtonFormField<String>(
                          decoration: const InputDecoration(
                            labelText: 'Kecamatan *',
                            border: OutlineInputBorder(),
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
                        loading: () => const SizedBox(
                          height: 56,
                          child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
                        ),
                        error: (err, _) => Text(DioExceptionMapper.toUserMessage(err)),
                      ),
                ],
                if (_idKec != null) ...[
                  const SizedBox(height: 12),
                  ref.watch(desaListProvider(_idKec!)).when(
                        data: (desaList) => DropdownButtonFormField<String>(
                          decoration: const InputDecoration(
                            labelText: 'Desa / Kelurahan *',
                            border: OutlineInputBorder(),
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
                        loading: () => const SizedBox(
                          height: 56,
                          child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
                        ),
                        error: (err, _) => Text(DioExceptionMapper.toUserMessage(err)),
                      ),
                ],
                const SizedBox(height: 12),
                TextFormField(
                  controller: _titikKenalController,
                  decoration: const InputDecoration(
                    labelText: 'Titik Kenal *',
                    hintText: 'Contoh: Depan Masjid Al-Akbar, atau nama jalan',
                    border: OutlineInputBorder(),
                  ),
                  textCapitalization: TextCapitalization.sentences,
                ),
              ],
            ),
            isActive: _currentStep >= 1,
            state: _currentStep > 1 ? StepState.complete : StepState.indexed,
          ),
          Step(
            title: const Text('Detail Kejadian'),
            content: Column(
              children: [
                ref.watch(jenisBencanaListProvider).when(
                      data: (jenisList) => DropdownButtonFormField<int>(
                        decoration: const InputDecoration(
                          labelText: 'Jenis Kejadian *',
                          border: OutlineInputBorder(),
                        ),
                        value: _idJenisBencana,
                        items: jenisList
                            .map((j) => DropdownMenuItem(value: j.id, child: Text(j.nama)))
                            .toList(),
                        onChanged: (val) {
                          setState(() {
                            _idJenisBencana = val;
                          });
                        },
                      ),
                      loading: () => const SizedBox(
                        height: 56,
                        child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
                      ),
                      error: (err, _) => Text(DioExceptionMapper.toUserMessage(err)),
                    ),
                const SizedBox(height: 12),
                InkWell(
                  onTap: _selectDateTime,
                  child: InputDecorator(
                    decoration: const InputDecoration(
                      labelText: 'Waktu Kejadian *',
                      border: OutlineInputBorder(),
                      suffixIcon: Icon(Icons.calendar_today),
                    ),
                    child: Text(
                      '${_waktuKejadian.day.toString().padLeft(2, '0')}/'
                      '${_waktuKejadian.month.toString().padLeft(2, '0')}/'
                      '${_waktuKejadian.year} '
                      '${_waktuKejadian.hour.toString().padLeft(2, '0')}:'
                      '${_waktuKejadian.minute.toString().padLeft(2, '0')}',
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _deskripsiController,
                  maxLines: 4,
                  maxLength: 2000,
                  decoration: const InputDecoration(
                    labelText: 'Deskripsi Kejadian *',
                    hintText: 'Jelaskan kronologi kejadian, dampak, dan bantuan yang dibutuhkan.',
                    border: OutlineInputBorder(),
                    alignLabelWithHint: true,
                  ),
                  textCapitalization: TextCapitalization.sentences,
                ),
              ],
            ),
            isActive: _currentStep >= 2,
            state: _currentStep > 2 ? StepState.complete : StepState.indexed,
          ),
          Step(
            title: const Text('Foto Kejadian'),
            content: Column(
              children: [
                Container(
                  height: 200,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: _fotoFile != null ? null : Colors.grey[100],
                    border: Border.all(color: Colors.grey, style: BorderStyle.solid),
                    borderRadius: BorderRadius.circular(12),
                    image: _fotoFile != null
                        ? DecorationImage(
                            image: FileImage(_fotoFile!),
                            fit: BoxFit.cover,
                          )
                        : null,
                  ),
                  child: _fotoFile == null
                      ? const Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.camera_alt, size: 48, color: Colors.grey),
                            SizedBox(height: 8),
                            Text('Tap untuk mengambil foto', style: TextStyle(color: Colors.grey)),
                          ],
                        )
                      : null,
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _pickFoto,
                        icon: const Icon(Icons.camera_alt),
                        label: const Text('Ambil Foto'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _pickFotoGallery,
                        icon: const Icon(Icons.photo_library),
                        label: const Text('Dari Galeri'),
                      ),
                    ),
                  ],
                ),
                if (_fotoFile != null) ...[
                  const SizedBox(height: 8),
                  TextButton.icon(
                    onPressed: () => setState(() => _fotoFile = null),
                    icon: const Icon(Icons.delete, color: Colors.red),
                    label: const Text('Hapus Foto', style: TextStyle(color: Colors.red)),
                  ),
                ],
              ],
            ),
            isActive: _currentStep >= 3,
            state: _currentStep > 3 ? StepState.complete : StepState.indexed,
          ),
          Step(
            title: const Text('Review & Submit'),
            content: Column(
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _reviewItem('Nama', _namaController.text.trim()),
                      _reviewItem('No. HP', _hpController.text.trim()),
                      const Divider(),
                      _reviewItem(
                        'Lokasi GPS',
                        _alamatGps ?? '${_latitude?.toStringAsFixed(6)}, ${_longitude?.toStringAsFixed(6)}',
                      ),
                      _reviewItem('Titik Kenal', _titikKenalController.text.trim()),
                      const Divider(),
                      _reviewItem(
                        'Waktu Kejadian',
                        '${_waktuKejadian.day.toString().padLeft(2, '0')}/'
                        '${_waktuKejadian.month.toString().padLeft(2, '0')}/'
                        '${_waktuKejadian.year} '
                        '${_waktuKejadian.hour.toString().padLeft(2, '0')}:'
                        '${_waktuKejadian.minute.toString().padLeft(2, '0')}',
                      ),
                      _reviewItem('Foto', _fotoFile != null ? 'Terlampir' : 'Kosong'),
                      const Divider(),
                      Text(
                        _deskripsiController.text.trim(),
                        style: const TextStyle(fontSize: 14),
                      ),
                    ],
                  ),
                ),
                if (_submitError != null) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.red.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.red.shade200),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.error_outline, color: Colors.red, size: 20),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Gagal mengirim: $_submitError',
                            style: TextStyle(color: Colors.red.shade700, fontSize: 13),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),
                  OutlinedButton.icon(
                    onPressed: _isSubmitting ? null : _submitReport,
                    icon: const Icon(Icons.refresh),
                    label: const Text('Coba Kirim Ulang'),
                  ),
                ],
              ],
            ),
            isActive: _currentStep >= 4,
          ),
        ],
      ),
    );
  }

  Widget _reviewItem(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
          ),
          Expanded(child: Text(value, style: const TextStyle(fontSize: 13))),
        ],
      ),
    );
  }
}
