import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  int _currentStep = 0; // 0: Pilih Jenis, 1: Akun, 2: Biodata, 3: Domisili, 4: Keahlian/Scope
  String? _selectedJenis;

  // Step 1 controllers
  final _phoneCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _passConfirmCtrl = TextEditingController();

  // Step 2 controllers
  final _nameCtrl = TextEditingController();
  final _nikCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();

  // Step 3 state
  String? _selectedKabId;
  String? _selectedKecId;
  String? _selectedDesaId;
  final _alamatCtrl = TextEditingController();

  List<dynamic> _kabupatenList = [];
  List<dynamic> _kecamatanList = [];
  List<dynamic> _desaList = [];
  bool _loadingRegions = false;

  // Step 4 state
  List<dynamic> _keahlianList = [];
  final List<int> _selectedKeahlianIds = [];
  List<dynamic> _pcnuList = [];
  int? _selectedPcnuId;
  bool _loadingKeahlianPcnu = false;

  bool _isSubmitting = false;
  String? _errorMsg;

  @override
  void dispose() {
    _phoneCtrl.dispose();
    _passCtrl.dispose();
    _passConfirmCtrl.dispose();
    _nameCtrl.dispose();
    _nikCtrl.dispose();
    _emailCtrl.dispose();
    _alamatCtrl.dispose();
    super.dispose();
  }

  // Region loading methods
  Future<void> _fetchKabupaten() async {
    setState(() => _loadingRegions = true);
    try {
      final dio = ref.read(publicApiClientProvider);
      final res = await dio.get('wilayah/kabupaten');
      if (!mounted) return;
      setState(() {
        _kabupatenList = res.data as List<dynamic>;
        _loadingRegions = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMsg = 'Gagal memuat daftar kabupaten.';
        _loadingRegions = false;
      });
    }
  }

  Future<void> _fetchKecamatan(String kabId) async {
    setState(() {
      _loadingRegions = true;
      _kecamatanList = [];
      _desaList = [];
      _selectedKecId = null;
      _selectedDesaId = null;
    });
    try {
      final dio = ref.read(publicApiClientProvider);
      final res = await dio.get('wilayah/kecamatan', queryParameters: {'id_kab': kabId});
      if (!mounted) return;
      setState(() {
        _kecamatanList = res.data as List<dynamic>;
        _loadingRegions = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMsg = 'Gagal memuat daftar kecamatan.';
        _loadingRegions = false;
      });
    }
  }

  Future<void> _fetchDesa(String kecId) async {
    setState(() {
      _loadingRegions = true;
      _desaList = [];
      _selectedDesaId = null;
    });
    try {
      final dio = ref.read(publicApiClientProvider);
      final res = await dio.get('wilayah/desa', queryParameters: {'id_kec': kecId});
      if (!mounted) return;
      setState(() {
        _desaList = res.data as List<dynamic>;
        _loadingRegions = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMsg = 'Gagal memuat daftar desa/kelurahan.';
        _loadingRegions = false;
      });
    }
  }

  // Keahlian and PCNU loading methods
  Future<void> _fetchKeahlianAndPcnu() async {
    setState(() => _loadingKeahlianPcnu = true);
    try {
      final dio = ref.read(publicApiClientProvider);
      final resKeahlian = await dio.get('keahlian');
      if (!mounted) return;

      List<dynamic> fetchedPcnu = [];
      if (_selectedJenis == 'trc_pcnu' || _selectedJenis == 'trc_pwnu' || _selectedJenis == 'admin_pcnu') {
        final resPcnu = await dio.get('wilayah/pcnu');
        if (!mounted) return;
        fetchedPcnu = resPcnu.data['data'] as List<dynamic>;
      }

      setState(() {
        _keahlianList = resKeahlian.data['data'] as List<dynamic>;
        _pcnuList = fetchedPcnu;
        _loadingKeahlianPcnu = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMsg = 'Gagal memuat data pendukung.';
        _loadingKeahlianPcnu = false;
      });
    }
  }

  Future<void> _register() async {
    setState(() {
      _isSubmitting = true;
      _errorMsg = null;
    });

    try {
      final dio = ref.read(publicApiClientProvider);
      final res = await dio.post('auth/register/$_selectedJenis', data: {
        'jenis': _selectedJenis,
        'no_hp': _phoneCtrl.text,
        'kata_sandi': _passCtrl.text,
        'kata_sandi_confirmation': _passConfirmCtrl.text,
        'nama_lengkap': _nameCtrl.text,
        'nik': _nikCtrl.text.isEmpty ? null : _nikCtrl.text,
        'email': _emailCtrl.text.isEmpty ? null : _emailCtrl.text,
        'id_kabupaten': _selectedKabId,
        'id_kecamatan': _selectedKecId,
        'id_desa': _selectedDesaId,
        'alamat_deskriptif': _alamatCtrl.text,
        'keahlian': _selectedKeahlianIds,
        if (_selectedJenis != 'admin_pcnu') 'id_pcnu': _selectedPcnuId,
      });

      if (res.statusCode == 200 || res.statusCode == 201) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pendaftaran berhasil diajukan! Menunggu persetujuan.')),
        );
        ref.read(runtimeServicesProvider).navigation.pop();
      } else {
        setState(() {
          _errorMsg = res.data['message'] ?? 'Pendaftaran gagal.';
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMsg = 'Terjadi kesalahan sistem atau koneksi.';
      });
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  // Validator helpers
  bool _validateStep1() {
    if (_phoneCtrl.text.trim().isEmpty) {
      setState(() => _errorMsg = 'Nomor HP wajib diisi.');
      return false;
    }
    if (_passCtrl.text.length < 8) {
      setState(() => _errorMsg = 'Kata Sandi minimal 8 karakter.');
      return false;
    }
    if (_passCtrl.text != _passConfirmCtrl.text) {
      setState(() => _errorMsg = 'Konfirmasi Kata Sandi tidak cocok.');
      return false;
    }
    setState(() => _errorMsg = null);
    return true;
  }

  bool _validateStep2() {
    if (_nameCtrl.text.trim().isEmpty) {
      setState(() => _errorMsg = 'Nama Lengkap wajib diisi sesuai KTP.');
      return false;
    }
    setState(() => _errorMsg = null);
    return true;
  }

  bool _validateStep3() {
    if (_selectedKabId == null) {
      setState(() => _errorMsg = 'Kabupaten wajib dipilih.');
      return false;
    }
    if (_selectedKecId == null) {
      setState(() => _errorMsg = 'Kecamatan wajib dipilih.');
      return false;
    }
    if (_selectedDesaId == null) {
      setState(() => _errorMsg = 'Desa wajib dipilih.');
      return false;
    }
    if (_alamatCtrl.text.trim().isEmpty) {
      setState(() => _errorMsg = 'Alamat Lengkap wajib diisi.');
      return false;
    }
    setState(() => _errorMsg = null);
    return true;
  }

  bool _validateStep4() {
    if (_selectedJenis != 'relawan' && _selectedJenis != 'admin_pwnu' && _selectedJenis != 'admin_pcnu') {
      if (_selectedPcnuId == null) {
        setState(() => _errorMsg = 'PCNU Asal wajib dipilih.');
        return false;
      }
    }
    setState(() => _errorMsg = null);
    return true;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_currentStep == 0 ? 'Pilih Jenis Akun' : 'Form Pendaftaran (Langkah $_currentStep dari 4)'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            if (_currentStep > 0) {
              setState(() {
                _currentStep--;
                _errorMsg = null;
              });
            } else {
              ref.read(runtimeServicesProvider).navigation.pop();
            }
          },
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (_currentStep > 0) ...[
                _buildProgressBar(),
                const SizedBox(height: 20),
              ],
              if (_errorMsg != null) ...[
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    border: Border.all(color: Colors.red.shade200),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _errorMsg!,
                    style: TextStyle(color: Colors.red.shade800, fontSize: 13),
                  ),
                ),
                const SizedBox(height: 20),
              ],
              _buildStepContent(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildProgressBar() {
    return Row(
      children: List.generate(4, (index) {
        final stepNum = index + 1;
        final isDone = stepNum < _currentStep;
        final isCurrent = stepNum == _currentStep;

        return Expanded(
          child: Row(
            children: [
              CircleAvatar(
                radius: 14,
                backgroundColor: isDone
                    ? Colors.green
                    : isCurrent
                        ? Colors.green.shade100
                        : Colors.grey.shade200,
                child: Text(
                  isDone ? '✓' : '$stepNum',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: isDone
                        ? Colors.white
                        : isCurrent
                            ? Colors.green.shade800
                            : Colors.grey.shade600,
                  ),
                ),
              ),
              if (index < 3)
                Expanded(
                  child: Container(
                    height: 2,
                    color: isDone ? Colors.green : Colors.grey.shade300,
                  ),
                ),
            ],
          ),
        );
      }),
    );
  }

  Widget _buildStepContent() {
    switch (_currentStep) {
      case 0:
        return _buildStep0PilihJenis();
      case 1:
        return _buildStep1Akun();
      case 2:
        return _buildStep2Biodata();
      case 3:
        return _buildStep3Domisili();
      case 4:
        return _buildStep4Keahlian();
      default:
        return const SizedBox.shrink();
    }
  }

  // STEP 0: PILIH JENIS AKUN
  Widget _buildStep0PilihJenis() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text(
          'Bergabung dengan NU Peduli',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 8),
        const Text(
          'Pilih jenis akun pendaftaran Anda di NU Peduli Jawa Tengah',
          style: TextStyle(color: Colors.grey),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 24),
        _buildJenisCard(
          'relawan',
          '🤝',
          'Relawan Umum',
          'Bergabung sebagai relawan NU dalam kegiatan kemanusiaan.',
          'Aktif langsung',
          Colors.green,
        ),
        _buildJenisCard(
          'trc_pcnu',
          '🚑',
          'TRC PCNU',
          'Tim Reaksi Cepat tingkat cabang — relawan terlatih.',
          'Perlu persetujuan Admin PCNU',
          Colors.amber.shade800,
        ),
        _buildJenisCard(
          'trc_pwnu',
          '⚡',
          'TRC PWNU',
          'Tim Reaksi Cepat tingkat wilayah — lintas cabang.',
          'Perlu persetujuan PCNU + PWNU',
          Colors.orange.shade800,
        ),
        _buildJenisCard(
          'admin_pcnu',
          '🏢',
          'Admin PCNU',
          'Operator sistem level cabang.',
          'Perlu persetujuan Admin PWNU',
          Colors.purple,
        ),
        _buildJenisCard(
          'admin_pwnu',
          '🏛️',
          'Admin PWNU',
          'Operator sistem tingkat wilayah — akses penuh Jawa Tengah.',
          'Perlu persetujuan Super Admin',
          Colors.red,
        ),
      ],
    );
  }

  Widget _buildJenisCard(
    String id,
    String emoji,
    String title,
    String desc,
    String badgeText,
    Color color,
  ) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: Colors.grey.shade200, width: 1.5),
      ),
      child: InkWell(
        onTap: () {
          setState(() {
            _selectedJenis = id;
            _currentStep = 1;
          });
        },
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(emoji, style: const TextStyle(fontSize: 32)),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      desc,
                      style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: color.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        badgeText,
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: color,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right, color: Colors.grey),
            ],
          ),
        ),
      ),
    );
  }

  // STEP 1: DATA AKUN
  Widget _buildStep1Akun() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text(
          'Langkah 1: Data Akun',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _phoneCtrl,
          keyboardType: TextInputType.phone,
          decoration: const InputDecoration(
            labelText: 'Nomor HP *',
            hintText: 'Contoh: 08123456789',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _passCtrl,
          obscureText: true,
          decoration: const InputDecoration(
            labelText: 'Kata Sandi * (min. 8 karakter)',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _passConfirmCtrl,
          obscureText: true,
          decoration: const InputDecoration(
            labelText: 'Konfirmasi Kata Sandi *',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 24),
        ElevatedButton(
          onPressed: () {
            if (_validateStep1()) {
              setState(() => _currentStep = 2);
            }
          },
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.green,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 16),
          ),
          child: const Text('Lanjutkan →'),
        ),
      ],
    );
  }

  // STEP 2: BIODATA
  Widget _buildStep2Biodata() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text(
          'Langkah 2: Biodata Diri',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _nameCtrl,
          decoration: const InputDecoration(
            labelText: 'Nama Lengkap * (Sesuai KTP)',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _nikCtrl,
          keyboardType: TextInputType.number,
          maxLength: 16,
          decoration: const InputDecoration(
            labelText: 'NIK (16 digit, Opsional)',
            border: OutlineInputBorder(),
            counterText: '',
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _emailCtrl,
          keyboardType: TextInputType.emailAddress,
          decoration: const InputDecoration(
            labelText: 'Email (Opsional)',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 24),
        Row(
          children: [
            Expanded(
              child: OutlinedButton(
                onPressed: () => setState(() => _currentStep = 1),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Text('← Kembali'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: ElevatedButton(
                onPressed: () {
                  if (_validateStep2()) {
                    _fetchKabupaten();
                    setState(() => _currentStep = 3);
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Text('Lanjutkan →'),
              ),
            ),
          ],
        ),
      ],
    );
  }

  // STEP 3: DOMISILI
  Widget _buildStep3Domisili() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text(
          'Langkah 3: Wilayah Domisili',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 16),
        if (_loadingRegions) ...[
          const Center(
            child: Padding(
              padding: EdgeInsets.all(24.0),
              child: CircularProgressIndicator(),
            ),
          ),
        ] else ...[
          // Kabupaten Dropdown
          DropdownButtonFormField<String>(
            value: _selectedKabId,
            hint: const Text('— Pilih Kabupaten/Kota —'),
            decoration: const InputDecoration(
              labelText: 'Kabupaten/Kota *',
              border: OutlineInputBorder(),
            ),
            items: _kabupatenList.map<DropdownMenuItem<String>>((item) {
              return DropdownMenuItem<String>(
                value: item['id_kab'].toString(),
                child: Text(item['nama_kab']),
              );
            }).toList(),
            onChanged: (val) {
              if (val != null) {
                setState(() => _selectedKabId = val);
                _fetchKecamatan(val);
              }
            },
          ),
          const SizedBox(height: 16),
          // Kecamatan Dropdown
          DropdownButtonFormField<String>(
            value: _selectedKecId,
            hint: const Text('— Pilih Kecamatan —'),
            decoration: const InputDecoration(
              labelText: 'Kecamatan *',
              border: OutlineInputBorder(),
            ),
            items: _kecamatanList.map<DropdownMenuItem<String>>((item) {
              return DropdownMenuItem<String>(
                value: item['id_kec'].toString(),
                child: Text(item['nama_kec']),
              );
            }).toList(),
            onChanged: _selectedKabId == null
                ? null
                : (val) {
                    if (val != null) {
                      setState(() => _selectedKecId = val);
                      _fetchDesa(val);
                    }
                  },
          ),
          const SizedBox(height: 16),
          // Desa Dropdown
          DropdownButtonFormField<String>(
            value: _selectedDesaId,
            hint: const Text('— Pilih Desa —'),
            decoration: const InputDecoration(
              labelText: 'Desa/Kelurahan *',
              border: OutlineInputBorder(),
            ),
            items: _desaList.map<DropdownMenuItem<String>>((item) {
              return DropdownMenuItem<String>(
                value: item['id_desa'].toString(),
                child: Text(item['nama_desa']),
              );
            }).toList(),
            onChanged: _selectedKecId == null
                ? null
                : (val) {
                    setState(() => _selectedDesaId = val);
                  },
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _alamatCtrl,
            decoration: const InputDecoration(
              labelText: 'Alamat Lengkap * (RT/RW, Dusun, Jalan)',
              border: OutlineInputBorder(),
            ),
          ),
        ],
        const SizedBox(height: 24),
        Row(
          children: [
            Expanded(
              child: OutlinedButton(
                onPressed: () => setState(() => _currentStep = 2),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Text('← Kembali'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: ElevatedButton(
                onPressed: () {
                  if (_validateStep3()) {
                    _fetchKeahlianAndPcnu();
                    setState(() => _currentStep = 4);
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Text('Lanjutkan →'),
              ),
            ),
          ],
        ),
      ],
    );
  }

  // STEP 4: KEAHLIAN & PENEMPATAN
  Widget _buildStep4Keahlian() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text(
          'Langkah 4: Keahlian & Penugasan',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 16),
        if (_loadingKeahlianPcnu) ...[
          const Center(
            child: Padding(
              padding: EdgeInsets.all(24.0),
              child: CircularProgressIndicator(),
            ),
          ),
        ] else ...[
          // Keahlian Checkboxes
          const Text(
            'Keahlian yang Dimiliki',
            style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          ..._keahlianList.map((item) {
            final id = item['id_keahlian'] as int;
            final name = item['nama_keahlian'] as String;
            final isChecked = _selectedKeahlianIds.contains(id);

            return CheckboxListTile(
              title: Text(name),
              subtitle: item['deskripsi'] != null ? Text(item['deskripsi']) : null,
              value: isChecked,
              controlAffinity: ListTileControlAffinity.leading,
              contentPadding: EdgeInsets.zero,
              onChanged: (checked) {
                setState(() {
                  if (checked == true) {
                    _selectedKeahlianIds.add(id);
                  } else {
                    _selectedKeahlianIds.remove(id);
                  }
                });
              },
            );
          }).toList(),
          const SizedBox(height: 24),
          // PCNU Penempatan (if required)
          if (_selectedJenis == 'admin_pcnu') ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.amber.shade50,
                border: Border.all(color: Colors.amber.shade200),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Row(
                children: [
                  Icon(Icons.info_outline, color: Colors.orange),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Informasi Penempatan: Sebagai Admin PCNU, penugasan Anda akan ditentukan secara otomatis berdasarkan Kabupaten/Kota domisili Anda.',
                      style: TextStyle(fontSize: 12),
                    ),
                  ),
                ],
              ),
            ),
          ] else if (_pcnuList.isNotEmpty) ...[
            DropdownButtonFormField<int>(
              value: _selectedPcnuId,
              hint: const Text('— Pilih PCNU —'),
              decoration: const InputDecoration(
                labelText: 'PCNU Asal *',
                border: OutlineInputBorder(),
              ),
              items: _pcnuList.map<DropdownMenuItem<int>>((item) {
                return DropdownMenuItem<int>(
                  value: item['id'] as int,
                  child: Text(item['nama']),
                );
              }).toList(),
              onChanged: (val) {
                setState(() => _selectedPcnuId = val);
              },
            ),
          ],
        ],
        const SizedBox(height: 32),
        Row(
          children: [
            Expanded(
              child: OutlinedButton(
                onPressed: () => setState(() => _currentStep = 3),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Text('← Kembali'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: ElevatedButton(
                onPressed: _isSubmitting
                    ? null
                    : () {
                        if (_validateStep4()) {
                          _register();
                        }
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isSubmitting
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : const Text('Daftar Sekarang ✓'),
              ),
            ),
          ],
        ),
      ],
    );
  }
}
