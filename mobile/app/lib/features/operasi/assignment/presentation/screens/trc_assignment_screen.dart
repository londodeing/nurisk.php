import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/features/public/report/data/models/laporan_kejadian_model.dart';
import 'package:nurisk_mobile/features/operasi/assignment/presentation/notifiers/trc_assignment_provider.dart';
import 'package:nurisk_mobile/features/operasi/assignment/domain/models/trc_member_dto.dart';

class TrcAssignmentScreen extends ConsumerWidget {
  final LaporanKejadianModel laporan;

  const TrcAssignmentScreen({Key? key, required this.laporan}) : super(key: key);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final trcState = ref.watch(trcAssignmentNotifierProvider);
    final availableTrc = ref.watch(availableTrcProvider(laporan.idPcnu));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Penerbitan Surat Tugas TRC'),
        backgroundColor: Colors.green.shade700,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildReportSummary(context),
            const SizedBox(height: 24),
            const Text(
              'Prioritas Insiden',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            _buildPrioritySelector(ref, trcState.prioritas),
            const SizedBox(height: 24),
            const Text(
              'Pilih Anggota TRC (Opsional)',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            availableTrc.when(
              data: (trcList) {
                if (trcList.isEmpty) {
                  return Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.orange.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.orange.shade200),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.warning_amber_rounded, color: Colors.orange),
                        SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Tidak ada anggota TRC yang berstatus siap bertugas saat ini.',
                            style: TextStyle(color: Colors.orange),
                          ),
                        ),
                      ],
                    ),
                  );
                }
                return ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: trcList.length,
                  itemBuilder: (context, index) {
                    final trc = trcList[index];
                    final isSelected = trcState.selectedTrcIds.contains(trc.idPengguna);
                    return CheckboxListTile(
                      title: Text(trc.namaLengkap, style: const TextStyle(fontWeight: FontWeight.bold)),
                      subtitle: Padding(
                        padding: const EdgeInsets.only(top: 4.0),
                        child: Text('${trc.noHp}\n${trc.alamatLengkap}', style: TextStyle(fontSize: 12, color: Colors.grey.shade700)),
                      ),
                      value: isSelected,
                      onChanged: (val) {
                        ref.read(trcAssignmentNotifierProvider.notifier).toggleTrcSelection(trc.idPengguna);
                      },
                      activeColor: Colors.green.shade700,
                      controlAffinity: ListTileControlAffinity.leading,
                      contentPadding: EdgeInsets.zero,
                    );
                  },
                );
              },
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, s) => Text('Gagal memuat TRC: $e', style: const TextStyle(color: Colors.red)),
            ),
          ],
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, -5),
            ),
          ],
        ),
        child: ElevatedButton(
          onPressed: trcState.isSubmitting
              ? null
              : () async {
                  try {
                    final success = await ref.read(trcAssignmentNotifierProvider.notifier).submitEskalasi(
                          laporanId: laporan.id,
                          idPcnu: laporan.idPcnu,
                        );
                    if (context.mounted) {
                      if (success) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Surat Tugas berhasil diterbitkan.')),
                        );
                        context.pop();
                      }
                    }
                  } catch (e) {
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text(e.toString().replaceAll('Exception: ', ''))),
                      );
                    }
                  }
                },
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.green.shade700,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          ),
          child: trcState.isSubmitting
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                )
              : const Text(
                  'TERBITKAN SURAT TUGAS',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
        ),
      ),
    );
  }

  Widget _buildReportSummary(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        border: Border.all(color: Colors.grey.shade300),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Eskalasi Laporan', style: TextStyle(color: Colors.grey, fontSize: 12)),
          const SizedBox(height: 4),
          Text(laporan.kodeKejadian, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
          const SizedBox(height: 8),
          Row(
            children: [
              const Icon(Icons.location_on, size: 16, color: Colors.grey),
              const SizedBox(width: 4),
              Expanded(
                child: Text(
                  laporan.titikKenal ?? laporan.alamatLengkap ?? 'Lokasi tidak diketahui',
                  style: const TextStyle(fontSize: 14),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPrioritySelector(WidgetRef ref, String currentPriority) {
    final priorities = [
      {'value': 'rendah', 'label': 'Rendah', 'color': Colors.blue},
      {'value': 'sedang', 'label': 'Sedang', 'color': Colors.orange},
      {'value': 'tinggi', 'label': 'Tinggi', 'color': Colors.red},
      {'value': 'kritis', 'label': 'Kritis', 'color': Colors.purple},
    ];

    return Wrap(
      spacing: 8,
      children: priorities.map((p) {
        final isSelected = currentPriority == p['value'];
        final color = p['color'] as Color;
        return ChoiceChip(
          label: Text(p['label'] as String),
          selected: isSelected,
          selectedColor: color.withOpacity(0.2),
          labelStyle: TextStyle(
            color: isSelected ? color : Colors.grey.shade700,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          ),
          onSelected: (selected) {
            if (selected) {
              ref.read(trcAssignmentNotifierProvider.notifier).setPrioritas(p['value'] as String);
            }
          },
        );
      }).toList(),
    );
  }
}
