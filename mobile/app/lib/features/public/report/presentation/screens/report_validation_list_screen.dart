import 'package:flutter/material.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/router/app_router.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';
import 'package:nurisk_mobile/core/theme/nurisk_colors.dart';
import 'package:nurisk_mobile/features/public/report/presentation/notifiers/laporan_validation_provider.dart';
import 'package:nurisk_mobile/features/public/report/data/models/laporan_kejadian_model.dart';
import 'package:nurisk_mobile/features/auth/presentation/notifiers/auth_state_provider.dart';


class ReportValidationListScreen extends ConsumerStatefulWidget {
  const ReportValidationListScreen({super.key});

  @override
  ConsumerState<ReportValidationListScreen> createState() => _ReportValidationListScreenState();
}

class _ReportValidationListScreenState extends ConsumerState<ReportValidationListScreen> {
  List<dynamic> _pcnuList = [];
  bool _loadingPcnu = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      ref.read(laporanValidationProvider.notifier).fetchPendingLaporan();
      _fetchPcnuList();
    });
  }

  Future<void> _fetchPcnuList() async {
    setState(() => _loadingPcnu = true);
    try {
      final dio = ref.read(publicApiClientProvider);
      final res = await dio.get('wilayah/pcnu');
      if (mounted) {
        setState(() {
          _pcnuList = res.data['data'] as List<dynamic>;
          _loadingPcnu = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loadingPcnu = false);
      }
    }
  }

  void _showValidationDialog(LaporanKejadianModel laporan) {
    // Pastikan idPcnu dari laporan benar-benar ada di dalam _pcnuList, jika tidak set null
    final validPcnuIds = _pcnuList.map((e) => e['id'] is int ? e['id'] : int.tryParse(e['id']?.toString() ?? '')).toList();
    int? selectedPcnuId = validPcnuIds.contains(laporan.idPcnu) ? laporan.idPcnu : null;
    
    String selectedPrioritas = 'sedang';
    String selectedStatus = 'terverifikasi';
    final alamatCtrl = TextEditingController(text: laporan.alamatLengkap ?? '');
    final latCtrl = TextEditingController(text: laporan.latitude.toString());
    final longCtrl = TextEditingController(text: laporan.longitude.toString());

    bool isSubmitting = false;

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: Text('Validasi & Buat Insiden\n(${laporan.kodeKejadian})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextField(
                      controller: alamatCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(labelText: 'Alamat Lengkap (Revisi)', border: OutlineInputBorder()),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: TextField(controller: latCtrl, decoration: const InputDecoration(labelText: 'Latitude', border: OutlineInputBorder()), keyboardType: TextInputType.number)),
                        const SizedBox(width: 8),
                        Expanded(child: TextField(controller: longCtrl, decoration: const InputDecoration(labelText: 'Longitude', border: OutlineInputBorder()), keyboardType: TextInputType.number)),
                      ],
                    ),
                    const SizedBox(height: 12),
                    const Divider(),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<int>(
                      value: selectedPcnuId,
                      decoration: const InputDecoration(labelText: 'PCNU Tujuan *', border: OutlineInputBorder()),
                      items: _pcnuList.map<DropdownMenuItem<int>>((item) {
                        return DropdownMenuItem<int>(
                          value: item['id'] is int ? item['id'] : int.tryParse(item['id']?.toString() ?? ''),
                          child: Text(item['nama'] ?? '-'),
                        );
                      }).toList(),
                      onChanged: (val) {
                        setDialogState(() => selectedPcnuId = val);
                      },
                    ),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      value: selectedPrioritas,
                      decoration: const InputDecoration(labelText: 'Prioritas *', border: OutlineInputBorder()),
                      items: const [
                        DropdownMenuItem(value: 'rendah', child: Text('Rendah')),
                        DropdownMenuItem(value: 'sedang', child: Text('Sedang')),
                        DropdownMenuItem(value: 'tinggi', child: Text('Tinggi')),
                        DropdownMenuItem(value: 'kritis', child: Text('Kritis')),
                      ],
                      onChanged: (val) {
                        if (val != null) setDialogState(() => selectedPrioritas = val);
                      },
                    ),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      value: selectedStatus,
                      decoration: const InputDecoration(labelText: 'Status Insiden *', border: OutlineInputBorder()),
                      items: const [
                        DropdownMenuItem(value: 'terverifikasi', child: Text('Terverifikasi (Draft)')),
                        DropdownMenuItem(value: 'respon', child: Text('Respon (Tindakan Langsung)')),
                      ],
                      onChanged: (val) {
                        if (val != null) setDialogState(() => selectedStatus = val);
                      },
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Batal'),
                ),
                ElevatedButton(
                  onPressed: isSubmitting
                      ? null
                      : () async {
                          if (selectedPcnuId == null) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Harap pilih PCNU tujuan')),
                            );
                            return;
                          }
                          setDialogState(() => isSubmitting = true);
                          final success = await ref.read(laporanValidationProvider.notifier).validateLaporan(
                                idLaporan: laporan.id,
                                idPcnu: selectedPcnuId!,
                                prioritas: selectedPrioritas,
                                statusInsiden: selectedStatus,
                                alamatLengkap: alamatCtrl.text,
                                latitude: double.tryParse(latCtrl.text),
                                longitude: double.tryParse(longCtrl.text),
                              );
                          if (mounted) {
                            Navigator.pop(context);
                            if (success) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text('Laporan ${laporan.kodeKejadian} berhasil divalidasi.'), backgroundColor: NuriskColors.safeGreen),
                              );
                              // Laporan sudah dinyatakan valid, arahkan ke halaman assignment TRC.
                              // Setelah kembali, refresh daftar agar laporan yang sudah terproses tidak muncul lagi.
                              await context.push(
                                RoutePaths.trcAssignment,
                                extra: LaporanKejadianModel(
                                  id: laporan.id,
                                  kodeKejadian: laporan.kodeKejadian,
                                  idJenisBencana: laporan.idJenisBencana,
                                  namaPelapor: laporan.namaPelapor,
                                  hpPelapor: laporan.hpPelapor,
                                  keteranganSituasi: laporan.keteranganSituasi,
                                  titikKenal: laporan.titikKenal,
                                  waktuKejadian: laporan.waktuKejadian,
                                  latitude: double.tryParse(latCtrl.text) ?? 0.0,
                                  longitude: double.tryParse(longCtrl.text) ?? 0.0,
                                  alamatLengkap: alamatCtrl.text,
                                  isValid: 'ya',
                                  idPcnu: selectedPcnuId,
                                ),
                              );
                              // Refresh list setelah kembali dari Assignment
                              if (mounted) {
                                ref.read(laporanValidationProvider.notifier).fetchPendingLaporan();
                              }
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Gagal memvalidasi laporan.'), backgroundColor: NuriskColors.emergencyRed),
                              );
                            }
                          }
                        },
                  style: ElevatedButton.styleFrom(backgroundColor: NuriskColors.primary600, foregroundColor: NuriskColors.bgWhite),
                  child: isSubmitting ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: NuriskColors.bgWhite)) : const Text('Valid & Buat'),
                )
              ],
            );
          },
        );
      },
    );
  }

  void _showRejectDialog(LaporanKejadianModel laporan) {
    String selectedAlasan = 'hoax';
    final catatanCtrl = TextEditingController();
    bool isSubmitting = false;

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: Text('Tolak Laporan\n(${laporan.kodeKejadian})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  DropdownButtonFormField<String>(
                    value: selectedAlasan,
                    decoration: const InputDecoration(labelText: 'Alasan Penolakan *', border: OutlineInputBorder()),
                    items: const [
                      DropdownMenuItem(value: 'hoax', child: Text('Hoax / Palsu')),
                      DropdownMenuItem(value: 'duplikat', child: Text('Duplikat Laporan')),
                    ],
                    onChanged: (val) {
                      if (val != null) setDialogState(() => selectedAlasan = val);
                    },
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: catatanCtrl,
                    maxLines: 3,
                    decoration: const InputDecoration(labelText: 'Catatan Penolakan *', border: OutlineInputBorder(), hintText: 'Berikan penjelasan singkat...'),
                  )
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Batal'),
                ),
                ElevatedButton(
                  onPressed: isSubmitting
                      ? null
                      : () async {
                          if (catatanCtrl.text.trim().isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Catatan wajib diisi')),
                            );
                            return;
                          }
                          setDialogState(() => isSubmitting = true);
                          final success = await ref.read(laporanValidationProvider.notifier).tolakLaporan(
                                idLaporan: laporan.id,
                                alasan: selectedAlasan,
                                catatan: catatanCtrl.text.trim(),
                              );
                          if (mounted) {
                            Navigator.pop(context);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(success
                                    ? 'Laporan ${laporan.kodeKejadian} berhasil ditolak.'
                                    : 'Gagal menolak laporan.'),
                                backgroundColor: success ? NuriskColors.warningOrange : NuriskColors.emergencyRed,
                              ),
                            );
                          }
                        },
                  style: ElevatedButton.styleFrom(backgroundColor: NuriskColors.emergencyRed, foregroundColor: NuriskColors.bgWhite),
                  child: isSubmitting ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: NuriskColors.bgWhite)) : const Text('Tolak Laporan'),
                )
              ],
            );
          },
        );
      },
    );
  }

  void _showDetailBottomSheet(LaporanKejadianModel laporan) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (context) {
        return DraggableScrollableSheet(
          initialChildSize: 0.8,
          maxChildSize: 0.95,
          minChildSize: 0.5,
          expand: false,
          builder: (context, scrollController) {
            final authState = ref.read(authStateProvider);
            String baseUrl = dotenv.env['API_BASE_URL'] ?? const String.fromEnvironment('API_BASE_URL', defaultValue: 'https://nurisk.org/api');
            if (baseUrl.endsWith('/')) {
              baseUrl = baseUrl.substring(0, baseUrl.length - 1);
            }
            final proxyUrl = laporan.photoPath != null ? '$baseUrl/stream-media?path=${laporan.photoPath}&disk=s3' : null;

            return Column(
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: const BoxDecoration(
                    border: Border(bottom: BorderSide(color: NuriskColors.neutral200)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Detail Laporan',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(context),
                      )
                    ],
                  ),
                ),
                Expanded(
                  child: ListView(
                    controller: scrollController,
                    padding: const EdgeInsets.all(16),
                    children: [
                      if (proxyUrl != null) ...[
                        ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: Image.network(
                            proxyUrl,
                            headers: {
                              'Authorization': 'Bearer ${authState.token}',
                            },
                            width: double.infinity,
                            height: 200,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => Container(
                              height: 200,
                              color: NuriskColors.neutral200,
                              child: const Icon(Icons.broken_image, size: 50, color: NuriskColors.neutral500),
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                      ],
                      const Text('Kode Laporan', style: TextStyle(color: NuriskColors.neutral500, fontSize: 12)),
                      Text(laporan.kodeKejadian, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, fontFamily: 'monospace')),
                      const Divider(height: 24),
                      const Text('Informasi Pelapor', style: TextStyle(color: NuriskColors.neutral500, fontSize: 12)),
                      Text(laporan.namaPelapor, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      Text(laporan.hpPelapor, style: const TextStyle(fontSize: 14)),
                      const Divider(height: 24),
                      const Text('Waktu Kejadian', style: TextStyle(color: NuriskColors.neutral500, fontSize: 12)),
                      Text(laporan.waktuKejadian.toString(), style: const TextStyle(fontSize: 14)),
                      const Divider(height: 24),
                      const Text('Lokasi Kejadian', style: TextStyle(color: NuriskColors.neutral500, fontSize: 12)),
                      Text(laporan.titikKenal ?? 'Tidak ada titik kenal', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                      const SizedBox(height: 4),
                      Text(laporan.alamatLengkap ?? 'Alamat lengkap tidak tersedia', style: const TextStyle(fontSize: 14)),
                      const SizedBox(height: 4),
                      Text('Koordinat: ${laporan.latitude}, ${laporan.longitude}', style: const TextStyle(fontSize: 12, color: NuriskColors.infoBlue)),
                      const Divider(height: 24),
                      const Text('Keterangan Situasi', style: TextStyle(color: NuriskColors.neutral500, fontSize: 12)),
                      Text(laporan.keteranganSituasi, style: const TextStyle(fontSize: 14)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.all(16),
decoration: const BoxDecoration(
                    color: NuriskColors.bgWhite,
                    border: Border(top: BorderSide(color: NuriskColors.neutral200)),
                  ),
                  child: Row(
                    children: [
                        Expanded(
                          child: ElevatedButton(
                            onPressed: () {
                              context.pop(); // tutup bottom sheet
                              _showValidationDialog(laporan);
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: NuriskColors.primary600,
                              foregroundColor: NuriskColors.bgWhite,
                            ),
                            child: const Text('VALIDASI'),
                          ),
                        ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () {
                            Navigator.pop(context);
                            _showRejectDialog(laporan);
                          },
                                        style: OutlinedButton.styleFrom(foregroundColor: NuriskColors.emergencyRed, side: const BorderSide(color: NuriskColors.emergencyRed)),
                          child: const Text('TOLAK', style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ],
                  ),
                )
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final validationState = ref.watch(laporanValidationProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Validasi Laporan Pending'),
        backgroundColor: NuriskColors.primary700,
        foregroundColor: NuriskColors.bgWhite,
      ),
      body: validationState.isLoading
          ? const Center(child: CircularProgressIndicator(color: NuriskColors.primary600))
          : RefreshIndicator(
              onRefresh: () => ref.read(laporanValidationProvider.notifier).fetchPendingLaporan(),
              child: validationState.error != null
                  ? ListView(
                      children: [
                        const SizedBox(height: 100),
                        Center(
                          child: Column(
                            children: [
                              const Text('❌', style: TextStyle(fontSize: 48)),
                              const SizedBox(height: 8),
                              Text(validationState.error!, style: const TextStyle(color: NuriskColors.emergencyRed, fontStyle: FontStyle.italic)),
                            ],
                          ),
                        ),
                      ],
                    )
                  : validationState.pendingList.isEmpty
                      ? ListView(
                          children: const [
                            SizedBox(height: 100),
                            Center(
                              child: Column(
                                children: [
                                  Text('✅', style: TextStyle(fontSize: 48)),
                                  SizedBox(height: 8),
                                  Text('Semua bersih. Tidak ada laporan pending.', style: TextStyle(color: NuriskColors.neutral500, fontStyle: FontStyle.italic)),
                                ],
                              ),
                            ),
                          ],
                        )
                      : ListView.builder(
                      itemCount: validationState.pendingList.length,
                      itemBuilder: (context, index) {
                        final laporan = validationState.pendingList[index];
                        return Card(
                          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          elevation: 2,
                          clipBehavior: Clip.antiAlias,
                          child: InkWell(
                            onTap: () => _showDetailBottomSheet(laporan),
                            child: Padding(
                              padding: const EdgeInsets.all(16.0),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      laporan.kodeKejadian,
                                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, fontFamily: 'monospace'),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(color: NuriskColors.warningOrange.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(12)),
                                      child: const Text('MENUNGGU', style: TextStyle(color: NuriskColors.warningOrange, fontSize: 10, fontWeight: FontWeight.bold)),
                                    )
                                  ],
                                ),
                                const Divider(height: 20),
                                Text('Pelapor: ${laporan.namaPelapor} (${laporan.hpPelapor})', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                const SizedBox(height: 4),
                                Text('Jenis Bencana ID: ${laporan.idJenisBencana}', style: const TextStyle(fontSize: 13)),
                                const SizedBox(height: 4),
                                Text('Lokasi: ${laporan.titikKenal ?? '-'}', style: const TextStyle(fontSize: 13, color: NuriskColors.neutral500)),

                                const SizedBox(height: 8),
                                Text(laporan.keteranganSituasi, style: const TextStyle(fontSize: 13, color: NuriskColors.darkText)),
                                const SizedBox(height: 16),
                                Row(
                                  children: [
                                    Expanded(
                                      child: ElevatedButton(
                                        onPressed: () => _showValidationDialog(laporan),
                                        style: ElevatedButton.styleFrom(backgroundColor: NuriskColors.primary600, foregroundColor: NuriskColors.bgWhite, elevation: 0),
                                        child: const Text('VALIDASI', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: OutlinedButton(
                                        onPressed: () => _showRejectDialog(laporan),
                          style: OutlinedButton.styleFrom(foregroundColor: NuriskColors.emergencyRed, side: const BorderSide(color: NuriskColors.emergencyRed)),
                                        child: const Text('TOLAK', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                                      ),
                                    ),
                                  ],
                                )
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  ),
            ),
    );
  }
}
