import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../data/repositories/pleno_repository.dart';
import '../notifiers/pleno_providers.dart';

class PlenoFormScreen extends ConsumerStatefulWidget {
  final String uuidInsiden;

  const PlenoFormScreen({super.key, required this.uuidInsiden});

  @override
  ConsumerState<PlenoFormScreen> createState() => _PlenoFormScreenState();
}

class _PlenoFormScreenState extends ConsumerState<PlenoFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nomorController = TextEditingController();
  final _lokasiController = TextEditingController();
  final _waktuDateController = TextEditingController();
  final _waktuTimeController = TextEditingController();
  String _jenisPleno = 'evaluasi_rutin';
  int? _idPimpinan;
  int? _idNotulis;
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _waktuDateController.text = "${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}";
    _waktuTimeController.text = "${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}";
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_idPimpinan == null || _idNotulis == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pimpinan dan Notulis harus dipilih')));
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final repository = ref.read(plenoRepositoryProvider);
      final payload = {
        'nomor_pleno': _nomorController.text.isNotEmpty ? _nomorController.text : null,
        'jenis_pleno': _jenisPleno,
        'waktu_pleno': "${_waktuDateController.text} ${_waktuTimeController.text}:00",
        'lokasi_pleno': _lokasiController.text.isNotEmpty ? _lokasiController.text : 'Posko Utama',
        'pimpinan_pleno': _idPimpinan,
        'notulis_pleno': _idNotulis,
      };

      await repository.createPleno(widget.uuidInsiden, payload);
      ref.read(plenoListProvider.notifier).fetchPlenos();
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pleno berhasil dibuat.')));
        context.pop();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal membuat pleno: $e'), backgroundColor: Colors.red));
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final usersAsync = ref.watch(plenoUsersProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Buat Pleno Baru')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextFormField(
                controller: _nomorController,
                decoration: const InputDecoration(labelText: 'Nomor Pleno (Opsional)', border: OutlineInputBorder()),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                value: _jenisPleno,
                decoration: const InputDecoration(labelText: 'Jenis Pleno', border: OutlineInputBorder()),
                items: const [
                  DropdownMenuItem(value: 'evaluasi_rutin', child: Text('Evaluasi Rutin')),
                  DropdownMenuItem(value: 'aktivasi_operasi', child: Text('Aktivasi Operasi')),
                  DropdownMenuItem(value: 'perpanjangan_operasi', child: Text('Perpanjangan Operasi')),
                  DropdownMenuItem(value: 'penutupan_operasi', child: Text('Penutupan Operasi')),
                  DropdownMenuItem(value: 'eskalasi_wilayah', child: Text('Eskalasi Wilayah')),
                  DropdownMenuItem(value: 'khusus', child: Text('Khusus')),
                ],
                onChanged: (val) { if (val != null) setState(() => _jenisPleno = val); },
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _waktuDateController,
                      decoration: const InputDecoration(labelText: 'Tanggal (YYYY-MM-DD)', border: OutlineInputBorder()),
                      validator: (val) => val == null || val.isEmpty ? 'Wajib diisi' : null,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: TextFormField(
                      controller: _waktuTimeController,
                      decoration: const InputDecoration(labelText: 'Jam (HH:MM)', border: OutlineInputBorder()),
                      validator: (val) => val == null || val.isEmpty ? 'Wajib diisi' : null,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _lokasiController,
                decoration: const InputDecoration(labelText: 'Lokasi Pleno (Opsional)', border: OutlineInputBorder()),
              ),
              const SizedBox(height: 16),
              usersAsync.when(
                data: (users) => DropdownButtonFormField<int>(
                  decoration: const InputDecoration(labelText: 'Pimpinan Pleno', border: OutlineInputBorder()),
                  items: users.map((u) => DropdownMenuItem<int>(value: u['id_pengguna'] as int, child: Text(u['nama_lengkap']))).toList(),
                  onChanged: (val) => setState(() => _idPimpinan = val),
                  validator: (val) => val == null ? 'Wajib memilih pimpinan' : null,
                ),
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (err, _) => Text('Gagal memuat daftar pengguna: $err', style: const TextStyle(color: Colors.red)),
              ),
              const SizedBox(height: 16),
              usersAsync.when(
                data: (users) => DropdownButtonFormField<int>(
                  decoration: const InputDecoration(labelText: 'Notulis', border: OutlineInputBorder()),
                  items: users.map((u) => DropdownMenuItem<int>(value: u['id_pengguna'] as int, child: Text(u['nama_lengkap']))).toList(),
                  onChanged: (val) => setState(() => _idNotulis = val),
                  validator: (val) => val == null ? 'Wajib memilih notulis' : null,
                ),
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (err, _) => Text('Gagal memuat daftar pengguna: $err', style: const TextStyle(color: Colors.red)),
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submit,
                  child: _isSubmitting ? const CircularProgressIndicator() : const Text('Simpan Draft Pleno'),
                ),
              )
            ],
          ),
        ),
      ),
    );
  }
}
