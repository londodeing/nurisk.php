import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import 'package:nurisk_mobile/core/utils/pdf_download_helper.dart';
import '../../domain/models/pleno_model.dart';
import '../notifiers/pleno_providers.dart';

class PlenoDetailScreen extends ConsumerStatefulWidget {
  final String uuidInsiden;
  final int idPleno;

  const PlenoDetailScreen({super.key, required this.uuidInsiden, required this.idPleno});

  @override
  ConsumerState<PlenoDetailScreen> createState() => _PlenoDetailScreenState();
}

class _PlenoDetailScreenState extends ConsumerState<PlenoDetailScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _keputusanController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    Future.microtask(() => ref.read(plenoDetailProvider.notifier).initialize(widget.uuidInsiden, widget.idPleno));
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(plenoDetailProvider);
    final notifier = ref.read(plenoDetailProvider.notifier);

    if (state.isLoading && state.pleno == null) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (state.error != null && state.pleno == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Detail Pleno')),
        body: Center(child: Text('Error: ${state.error}')),
      );
    }

    final pleno = state.pleno;
    if (pleno == null) return const Scaffold();

    final isDraft = pleno.statusPleno == 'draft' || pleno.statusPleno == 'ditinjau';

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Pleno'),
        actions: [
          IconButton(
            icon: const Icon(Icons.download_rounded),
            tooltip: 'Download PDF Hasil Pleno',
            onPressed: () {
              final dio = ref.read(authApiClientProvider);
              PdfDownloadHelper.downloadAndOpenPdf(
                context: context,
                dio: dio,
                endpoint: 'v1/insiden/${widget.uuidInsiden}/pleno/${pleno.idPleno}/pdf',
                fileName: 'pleno_${pleno.idPleno}.pdf',
              );
            },
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Info'),
            Tab(text: 'Keputusan'),
            Tab(text: 'Peserta'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildInfoTab(pleno),
          _buildKeputusanTab(pleno, notifier, isDraft),
          _buildPesertaTab(pleno, notifier, isDraft),
        ],
      ),
      bottomNavigationBar: isDraft ? Padding(
        padding: const EdgeInsets.all(16.0),
        child: ElevatedButton(
          style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
          onPressed: state.isSubmitting ? null : () async {
            final confirm = await showDialog<bool>(
              context: context,
              builder: (c) => AlertDialog(
                title: const Text('Finalisasi Pleno'),
                content: const Text('Apakah Anda yakin ingin memfinalisasi pleno ini? Tindakan ini akan mengunci pleno dan mengeksekusi automasi penugasan.'),
                actions: [
                  TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
                  TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Finalisasi')),
                ],
              ),
            );
            if (confirm == true) {
              final success = await notifier.finalizePleno();
              if (success && mounted) {
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pleno berhasil difinalisasi')));
              } else if (mounted) {
                final err = ref.read(plenoDetailProvider).error;
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err ?? 'Gagal memfinalisasi pleno'), backgroundColor: Colors.red));
              }
            }
          },
          child: state.isSubmitting ? const CircularProgressIndicator(color: Colors.white) : const Text('Finalisasi Pleno'),
        ),
      ) : null,
    );
  }

  Widget _buildInfoTab(PlenoModel pleno) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _infoRow('Nomor Pleno', pleno.nomorPleno ?? '-'),
        _infoRow('Status', pleno.statusPleno.toUpperCase()),
        _infoRow('Jenis', pleno.jenisPleno),
        _infoRow('Lokasi', pleno.lokasiPleno ?? '-'),
        _infoRow('Waktu Pleno', pleno.waktuPleno?.toString() ?? '-'),
        _infoRow('Pimpinan', pleno.namaPimpinan ?? '-'),
        _infoRow('Notulis', pleno.namaNotulis ?? '-'),
      ],
    );
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.grey)),
          const SizedBox(height: 4),
          Text(value, style: const TextStyle(fontSize: 16)),
        ],
      ),
    );
  }

  Widget _buildKeputusanTab(PlenoModel pleno, PlenoDetailNotifier notifier, bool isDraft) {
    return Column(
      children: [
        if (isDraft)
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                icon: const Icon(Icons.add),
                label: const Text('Tambah Keputusan Rapat'),
                onPressed: () => _showAddKeputusanDialog(context, pleno, notifier),
              ),
            ),
          ),
        Expanded(
          child: ListView.builder(
            itemCount: pleno.keputusan?.length ?? 0,
            itemBuilder: (context, index) {
              final kep = pleno.keputusan![index];
              return Card(
                margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                child: ListTile(
                  title: Text(kep.deskripsiKeputusan),
                  subtitle: Text('Status: ${kep.statusPelaksanaan ?? 'Draft'} | Kategori: ${kep.kategoriObjek ?? '-'}'),
                  trailing: isDraft ? IconButton(
                    icon: const Icon(Icons.delete, color: Colors.red),
                    onPressed: () => notifier.removeKeputusan(kep.idKeputusan),
                  ) : null,
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildPesertaTab(PlenoModel pleno, PlenoDetailNotifier notifier, bool isDraft) {
    final usersAsync = ref.watch(plenoUsersProvider);
    return Column(
      children: [
        if (isDraft)
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: Row(
              children: [
                Expanded(
                  child: usersAsync.when(
                    data: (users) => DropdownButtonFormField<int>(
                      decoration: const InputDecoration(labelText: 'Tambah Peserta', border: OutlineInputBorder(), isDense: true),
                      items: users.map((u) => DropdownMenuItem<int>(value: u['id_pengguna'] as int, child: Text(u['nama_lengkap']))).toList(),
                      onChanged: (val) async {
                        if (val != null) {
                           await notifier.addPeserta(val, 'Anggota');
                        }
                      },
                    ),
                    loading: () => const Center(child: CircularProgressIndicator()),
                    error: (err, _) => Text('Gagal memuat daftar pengguna: $err', style: const TextStyle(color: Colors.red)),
                  ),
                ),
              ],
            ),
          ),
        Expanded(
          child: ListView.builder(
            itemCount: pleno.peserta?.length ?? 0,
            itemBuilder: (context, index) {
              final p = pleno.peserta![index];
              return Card(
                margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                child: ListTile(
                  title: Text(p.namaLengkap ?? p.username ?? 'User ${p.idPengguna}'),
                  subtitle: Text('Peran: ${p.peranDalamRapat ?? '-'}'),
                  trailing: isDraft ? IconButton(
                    icon: const Icon(Icons.delete, color: Colors.red),
                    onPressed: () => notifier.removePeserta(p.idPeserta),
                  ) : null,
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  void _showAddKeputusanDialog(BuildContext context, PlenoModel pleno, PlenoDetailNotifier notifier) {
    showDialog(
      context: context,
      builder: (context) {
        return _AddKeputusanDialogContent(
          pleno: pleno,
          notifier: notifier,
        );
      },
    );
  }

  @override
  void dispose() {
    _tabController.dispose();
    _keputusanController.dispose();
    super.dispose();
  }
}

class _AddKeputusanDialogContent extends ConsumerStatefulWidget {
  final PlenoModel pleno;
  final PlenoDetailNotifier notifier;

  const _AddKeputusanDialogContent({
    required this.pleno,
    required this.notifier,
  });

  @override
  ConsumerState<_AddKeputusanDialogContent> createState() => _AddKeputusanDialogContentState();
}

class _AddKeputusanDialogContentState extends ConsumerState<_AddKeputusanDialogContent> {
  final _formKey = GlobalKey<FormState>();
  final _deskripsiController = TextEditingController();
  final _namaPosController = TextEditingController();
  final _lokasiPosController = TextEditingController();

  String _kategoriObjek = 'status_insiden';
  String _jenisKeputusan = 'perubahan_status_insiden';
  int? _idKoordinator;

  // Selected klasters
  final Map<int, bool> _selectedKlasters = {
    1: false, // Kesehatan
    2: false, // Penyelamatan & Evakuasi
    3: false, // Logistik
    4: false, // Dapur Umum
    5: false, // Keamanan
    6: false, // Pengungsian & Hunian
    7: false, // Pemulihan Awal
  };

  final List<Map<String, dynamic>> _klasterMaster = [
    {'id': 1, 'nama': 'Klaster Kesehatan'},
    {'id': 2, 'nama': 'Klaster Penyelamatan & Evakuasi'},
    {'id': 3, 'nama': 'Klaster Logistik'},
    {'id': 4, 'nama': 'Klaster Dapur Umum'},
    {'id': 5, 'nama': 'Klaster Keamanan'},
    {'id': 6, 'nama': 'Klaster Pengungsian & Hunian'},
    {'id': 7, 'nama': 'Klaster Pemulihan Awal'},
  ];

  @override
  void dispose() {
    _deskripsiController.dispose();
    _namaPosController.dispose();
    _lokasiPosController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    final Map<String, dynamic> payload = {};
    if (_kategoriObjek == 'aktivasi_posko') {
      payload['nama_posaju'] = _namaPosController.text.isNotEmpty ? _namaPosController.text : 'Pos Aju Utama';
      payload['lokasi_posaju'] = _lokasiPosController.text.isNotEmpty ? _lokasiPosController.text : widget.pleno.lokasiPleno ?? 'Posko';
      payload['id_koordinator'] = _idKoordinator;
    } else if (_kategoriObjek == 'aktivasi_klaster') {
      final List<int> selectedIds = [];
      _selectedKlasters.forEach((id, isSelected) {
        if (isSelected) selectedIds.add(id);
      });
      payload['jenis_klaster'] = selectedIds;
      payload['id_koordinator'] = _idKoordinator;
    }

    final success = await widget.notifier.addKeputusan({
      'kategori_objek': _kategoriObjek,
      'jenis_keputusan': _jenisKeputusan,
      'deskripsi_keputusan': _deskripsiController.text,
      'payload': payload,
    });

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Keputusan berhasil disimpan & dieksekusi.')));
      Navigator.pop(context);
    } else if (mounted) {
      final err = ref.read(plenoDetailProvider).error;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err ?? 'Gagal menyimpan keputusan'), backgroundColor: Colors.red));
    }
  }

  @override
  Widget build(BuildContext context) {
    final usersAsync = ref.watch(plenoUsersProvider);

    return AlertDialog(
      title: const Text('Tambah Keputusan Rapat'),
      content: SingleChildScrollView(
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              DropdownButtonFormField<String>(
                value: _kategoriObjek,
                decoration: const InputDecoration(labelText: 'Kategori Objek', border: OutlineInputBorder()),
                items: const [
                  DropdownMenuItem(value: 'status_insiden', child: Text('Status Insiden')),
                  DropdownMenuItem(value: 'aktivasi_posko', child: Text('Aktivasi Pos Aju')),
                  DropdownMenuItem(value: 'aktivasi_klaster', child: Text('Aktivasi Klaster Bantuan')),
                  DropdownMenuItem(value: 'mobilisasi_relawan', child: Text('Mobilisasi Relawan')),
                  DropdownMenuItem(value: 'eskalasi_wilayah', child: Text('Eskalasi Wilayah')),
                  DropdownMenuItem(value: 'logistik', child: Text('Logistik')),
                  DropdownMenuItem(value: 'lainnya', child: Text('Lainnya')),
                ],
                onChanged: (val) {
                  if (val != null) {
                    setState(() {
                      _kategoriObjek = val;
                      // Auto-adjust default keputusan type
                      if (val == 'aktivasi_posko') {
                        _jenisKeputusan = 'aktivasi_pos';
                      } else if (val == 'aktivasi_klaster') {
                        _jenisKeputusan = 'alokasi_sumberdaya';
                      } else if (val == 'mobilisasi_relawan') {
                        _jenisKeputusan = 'penunjukan_personil';
                      } else {
                        _jenisKeputusan = 'lainnya';
                      }
                    });
                  }
                },
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: _jenisKeputusan,
                decoration: const InputDecoration(labelText: 'Jenis Keputusan', border: OutlineInputBorder()),
                items: const [
                  DropdownMenuItem(value: 'penunjukan_personil', child: Text('Penunjukan Personil')),
                  DropdownMenuItem(value: 'aktivasi_pos', child: Text('Aktivasi Pos')),
                  DropdownMenuItem(value: 'perubahan_status_insiden', child: Text('Perubahan Status Insiden')),
                  DropdownMenuItem(value: 'alokasi_sumberdaya', child: Text('Alokasi Sumberdaya')),
                  DropdownMenuItem(value: 'lainnya', child: Text('Lainnya')),
                ],
                onChanged: (val) { if (val != null) setState(() => _jenisKeputusan = val); },
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _deskripsiController,
                maxLines: 2,
                decoration: const InputDecoration(labelText: 'Deskripsi Keputusan', border: OutlineInputBorder()),
                validator: (val) => val == null || val.isEmpty ? 'Wajib diisi' : null,
              ),
              const SizedBox(height: 16),

              // Dynamic fields for Pos Aju
              if (_kategoriObjek == 'aktivasi_posko') ...[
                const Divider(),
                const Text('Parameter Pos Aju', style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _namaPosController,
                  decoration: const InputDecoration(labelText: 'Nama Pos Aju', border: OutlineInputBorder()),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _lokasiPosController,
                  decoration: const InputDecoration(labelText: 'Lokasi Pos Aju', border: OutlineInputBorder()),
                ),
                const SizedBox(height: 12),
                usersAsync.when(
                  data: (users) => DropdownButtonFormField<int>(
                    decoration: const InputDecoration(labelText: 'Pilih Komandan Pos', border: OutlineInputBorder()),
                    items: users.map((u) => DropdownMenuItem<int>(value: u['id_pengguna'] as int, child: Text(u['nama_lengkap']))).toList(),
                    onChanged: (val) => setState(() => _idKoordinator = val),
                    validator: (val) => val == null ? 'Komandan harus ditunjuk' : null,
                  ),
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (err, _) => Text('Gagal memuat pengguna: $err', style: const TextStyle(color: Colors.red)),
                ),
              ],

              // Dynamic fields for Klaster Bantuan
              if (_kategoriObjek == 'aktivasi_klaster') ...[
                const Divider(),
                const Text('Pilih Klaster & Koordinator', style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                usersAsync.when(
                  data: (users) => DropdownButtonFormField<int>(
                    decoration: const InputDecoration(labelText: 'Koordinator Klaster', border: OutlineInputBorder()),
                    items: users.map((u) => DropdownMenuItem<int>(value: u['id_pengguna'] as int, child: Text(u['nama_lengkap']))).toList(),
                    onChanged: (val) => setState(() => _idKoordinator = val),
                    validator: (val) => val == null ? 'Koordinator harus ditunjuk' : null,
                  ),
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (err, _) => Text('Gagal memuat pengguna: $err', style: const TextStyle(color: Colors.red)),
                ),
                const SizedBox(height: 12),
                const Text('Pilih Bidang Klaster:', style: TextStyle(fontSize: 12, color: Colors.grey)),
                ..._klasterMaster.map((k) {
                  final int id = k['id'] as int;
                  return CheckboxListTile(
                    title: Text(k['nama'] as String, style: const TextStyle(fontSize: 13)),
                    value: _selectedKlasters[id] ?? false,
                    dense: true,
                    contentPadding: EdgeInsets.zero,
                    onChanged: (val) {
                      if (val != null) {
                        setState(() {
                          _selectedKlasters[id] = val;
                        });
                      }
                    },
                  );
                }).toList(),
              ],
            ],
          ),
        ),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
        ElevatedButton(onPressed: _submit, child: const Text('Simpan & Eksekusi')),
      ],
    );
  }
}
