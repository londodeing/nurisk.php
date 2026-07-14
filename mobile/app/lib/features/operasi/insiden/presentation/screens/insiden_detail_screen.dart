import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/router/app_router.dart';
import 'package:nurisk_mobile/features/operasi/insiden/data/models/insiden_model.dart';
import 'package:nurisk_mobile/features/operasi/insiden/presentation/providers/insiden_providers.dart';
import 'package:nurisk_mobile/features/operasi/insiden/presentation/widgets/insiden_status_badge.dart';

class InsidenDetailScreen extends ConsumerStatefulWidget {
  final int insidenId;
  const InsidenDetailScreen({super.key, required this.insidenId});

  @override
  ConsumerState<InsidenDetailScreen> createState() => _InsidenDetailScreenState();
}

class _InsidenDetailScreenState extends ConsumerState<InsidenDetailScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _showUbahStatusDialog(InsidenModel insiden) async {
    final transitions = insiden.allowedTransitions;
    if (transitions.isEmpty) return;

    String? selectedStatus;
    final TextEditingController alasanCtrl = TextEditingController();

    await showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(builder: (ctx, setDialog) {
        return AlertDialog(
          title: const Text('Ubah Status Insiden', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ...transitions.map((t) => InkWell(
                onTap: () => setDialog(() => selectedStatus = t),
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 6),
                  child: Row(
                    children: [
                      Radio<String>(
                        value: t,
                        groupValue: selectedStatus,
                        onChanged: (v) => setDialog(() => selectedStatus = v),
                        activeColor: const Color(0xFF166534),
                      ),
                      InsidenStatusBadge(status: t),
                    ],
                  ),
                ),
              )),
              if (selectedStatus == 'dibatalkan') ...[
                const SizedBox(height: 8),
                TextField(
                  controller: alasanCtrl,
                  maxLines: 2,
                  decoration: const InputDecoration(
                    labelText: 'Alasan Pembatalan *',
                    border: OutlineInputBorder(),
                    hintText: 'Berikan alasan pembatalan...',
                  ),
                ),
              ],
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF166534), foregroundColor: Colors.white),
              onPressed: selectedStatus == null
                  ? null
                  : () async {
                      Navigator.pop(ctx);
                      final ok = await ref
                          .read(insidenDetailProvider(widget.insidenId).notifier)
                          .ubahStatus(selectedStatus!, alasan: alasanCtrl.text.trim());
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                          content: Text(ok ? 'Status berhasil diubah.' : 'Gagal mengubah status.'),
                          backgroundColor: ok ? const Color(0xFF166534) : Colors.red,
                        ));
                      }
                    },
              child: const Text('Simpan'),
            ),
          ],
        );
      }),
    );
  }

  @override
  Widget build(BuildContext context) {
    final insidenAsync = ref.watch(insidenDetailProvider(widget.insidenId));

    return insidenAsync.when(
      loading: () => Scaffold(
        appBar: AppBar(backgroundColor: const Color(0xFF166534), foregroundColor: Colors.white),
        body: const Center(child: CircularProgressIndicator(color: Color(0xFF166534))),
      ),
      error: (e, _) => Scaffold(
        appBar: AppBar(backgroundColor: const Color(0xFF166534), foregroundColor: Colors.white, title: const Text('Error')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 8),
              Text(e.toString(), textAlign: TextAlign.center),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.read(insidenDetailProvider(widget.insidenId).notifier).refresh(),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      ),
      data: (insiden) {
        return Scaffold(
          backgroundColor: const Color(0xFFF9FAFB),
          appBar: AppBar(
            backgroundColor: const Color(0xFF166534),
            foregroundColor: Colors.white,
            elevation: 0,
            title: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(insiden.kode, style: const TextStyle(fontFamily: 'monospace', fontWeight: FontWeight.bold, fontSize: 16)),
                Text(
                  insiden.jenisBencana ?? 'Insiden',
                  style: const TextStyle(fontSize: 11, color: Colors.white70),
                ),
              ],
            ),
            actions: [
              IconButton(
                icon: const Icon(Icons.refresh_rounded),
                onPressed: () => ref.read(insidenDetailProvider(widget.insidenId).notifier).refresh(),
              ),
            ],
            bottom: TabBar(
              controller: _tabController,
              indicatorColor: Colors.white,
              labelColor: Colors.white,
              unselectedLabelColor: Colors.white60,
              labelStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
              isScrollable: true,
              tabAlignment: TabAlignment.start,
              tabs: const [
                Tab(text: 'Ringkasan'),
                Tab(text: 'Assessment'),
                Tab(text: 'Personel'),
                Tab(text: 'Jurnal'),
              ],
            ),
          ),
          body: TabBarView(
            controller: _tabController,
            children: [
              _RingkasanTab(insiden: insiden, onUbahStatus: () => _showUbahStatusDialog(insiden)),
              _AssessmentTab(insiden: insiden),
              _PersonelTab(insiden: insiden),
              _JurnalTab(insiden: insiden),
            ],
          ),
        );
      },
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// TAB 1: RINGKASAN
// ─────────────────────────────────────────────────────────────────────────────
class _RingkasanTab extends StatelessWidget {
  final InsidenModel insiden;
  final VoidCallback onUbahStatus;

  const _RingkasanTab({required this.insiden, required this.onUbahStatus});

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: const Color(0xFF166534),
      onRefresh: () async {},
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Locked warning
          if (insiden.isLocked)
            Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFFEE2E2),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFFCA5A5)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.lock_rounded, color: Color(0xFFDC2626), size: 18),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Insiden ini telah dikunci. Tidak ada perubahan yang dapat dilakukan.',
                      style: TextStyle(color: Color(0xFFDC2626), fontSize: 13),
                    ),
                  ),
                ],
              ),
            ),

          // Header status + prioritas
          _SectionCard(
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Status', style: TextStyle(fontSize: 11, color: Color(0xFF6B7280))),
                        const SizedBox(height: 4),
                        InsidenStatusBadge(status: insiden.status),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        const Text('Prioritas', style: TextStyle(fontSize: 11, color: Color(0xFF6B7280))),
                        const SizedBox(height: 4),
                        InsidenStatusBadge(status: insiden.prioritas),
                      ],
                    ),
                  ],
                ),
                if (!insiden.isLocked && insiden.allowedTransitions.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  const Divider(height: 1),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      icon: const Icon(Icons.swap_horiz_rounded, size: 18),
                      label: const Text('Ubah Status'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF166534),
                        side: const BorderSide(color: Color(0xFF166534)),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      onPressed: onUbahStatus,
                    ),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 12),

          // Info Umum
          _SectionCard(
            title: 'Informasi Umum',
            child: Column(
              children: [
                _InfoRow(label: 'PCNU', value: insiden.pcnu ?? '-'),
                _InfoRow(label: 'Jenis Bencana', value: insiden.jenisBencana ?? '-'),
                _InfoRow(label: 'Waktu Mulai', value: insiden.waktuMulai != null ? _formatDateTime(insiden.waktuMulai!) : '-'),
                _InfoRow(label: 'Waktu Selesai', value: insiden.waktuSelesai != null ? _formatDateTime(insiden.waktuSelesai!) : '-'),
                _InfoRow(label: 'No. SPK', value: insiden.noSpkAssesment ?? 'Belum diterbitkan'),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // Laporan Asal
          if (insiden.laporanAsal != null)
            _SectionCard(
              title: 'Laporan Kejadian Asal',
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _InfoRow(label: 'Kode', value: insiden.laporanAsal!.kode),
                  _InfoRow(label: 'Pelapor', value: insiden.laporanAsal!.namaPelapor),
                  _InfoRow(label: 'Kontak', value: insiden.laporanAsal!.hpPelapor),
                  if (insiden.laporanAsal!.waktuKejadian != null)
                    _InfoRow(label: 'Waktu Kejadian', value: _formatDateTime(insiden.laporanAsal!.waktuKejadian!)),
                  if (insiden.laporanAsal!.alamatLengkap != null)
                    _InfoRow(label: 'Alamat', value: insiden.laporanAsal!.alamatLengkap!),
                  const SizedBox(height: 8),
                  const Text('Keterangan Situasi', style: TextStyle(fontSize: 11, color: Color(0xFF6B7280))),
                  const SizedBox(height: 4),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF9FAFB),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFE5E7EB)),
                    ),
                    child: Text(
                      insiden.laporanAsal!.keteranganSituasi,
                      style: const TextStyle(fontSize: 13, color: Color(0xFF374151)),
                    ),
                  ),
                ],
              ),
            ),
          const SizedBox(height: 12),

          // Riwayat Status
          _SectionCard(
            title: 'Riwayat Status',
            child: insiden.riwayatStatus.isEmpty
                ? const Text('Belum ada riwayat transisi status.', style: TextStyle(color: Color(0xFF9CA3AF), fontSize: 13))
                : Column(
                    children: insiden.riwayatStatus.map((r) {
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Column(
                              children: [
                                Container(
                                  width: 10,
                                  height: 10,
                                  decoration: const BoxDecoration(
                                    color: Color(0xFF166534),
                                    shape: BoxShape.circle,
                                  ),
                                ),
                                if (r != insiden.riwayatStatus.last)
                                  Container(width: 2, height: 24, color: const Color(0xFFD1FAE5)),
                              ],
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  InsidenStatusBadge(status: r.status, small: true),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${r.pengubah ?? 'Sistem'} — ${r.waktu != null ? _formatDateTime(r.waktu!) : '-'}',
                                    style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF)),
                                  ),
                                  if (r.alasan != null && r.alasan!.isNotEmpty)
                                    Text(r.alasan!, style: const TextStyle(fontSize: 11, color: Color(0xFF6B7280), fontStyle: FontStyle.italic)),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  static String _formatDateTime(DateTime dt) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    final hour = dt.hour.toString().padLeft(2, '0');
    final min = dt.minute.toString().padLeft(2, '0');
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}, $hour:$min';
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// TAB 2: ASSESSMENT
// ─────────────────────────────────────────────────────────────────────────────
class _AssessmentTab extends ConsumerWidget {
  final InsidenModel insiden;
  const _AssessmentTab({required this.insiden});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    if (!insiden.hasSuratTugas) {
      // SPK belum diterbitkan
      return ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFEF3C7),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFFCD34D)),
            ),
            child: const Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(Icons.warning_amber_rounded, color: Color(0xFFB45309), size: 24),
                SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Surat Tugas Belum Diterbitkan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF92400E))),
                      SizedBox(height: 4),
                      Text(
                        'Assessment tidak dapat dibuat sebelum Surat Tugas (SPK) diterbitkan oleh admin.',
                        style: TextStyle(fontSize: 12, color: Color(0xFFB45309)),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          const Center(
            child: Column(
              children: [
                Text('⏳', style: TextStyle(fontSize: 48)),
                SizedBox(height: 12),
                Text('Menunggu Surat Perintah Kerja', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                SizedBox(height: 6),
                Text(
                  'Surat Tugas diterbitkan oleh Admin PCNU dari halaman Validasi Laporan.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Color(0xFF6B7280), fontSize: 13),
                ),
              ],
            ),
          ),
        ],
      );
    }

    // SPK sudah ada — tampilkan assessment
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // SPK info
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: const Color(0xFFDCFCE7),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFF86EFAC)),
          ),
          child: Row(
            children: [
              const Icon(Icons.assignment_turned_in_rounded, color: Color(0xFF166534), size: 20),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Surat Tugas Diterbitkan', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF166834))),
                    Text('No: ${insiden.noSpkAssesment}', style: const TextStyle(fontSize: 11, color: Color(0xFF166834))),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Assessment Button (untuk TRC)
        if (!insiden.isLocked)
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              icon: const Icon(Icons.add_circle_outline_rounded, size: 20),
              label: const Text('Mulai Assessment Baru'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF166534),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: () {
                // Navigate to assessment form
                context.push(RoutePaths.assessmentForm.replaceFirst(':uuidInsiden', insiden.uuid));
              },
            ),
          ),
        const SizedBox(height: 16),

        // List of existing assessments
        if (insiden.assessments.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(24),
              child: Column(
                children: [
                  Text('📋', style: TextStyle(fontSize: 40)),
                  SizedBox(height: 10),
                  Text('Belum ada assessment', style: TextStyle(fontWeight: FontWeight.bold)),
                  SizedBox(height: 4),
                  Text('Tap tombol di atas untuk memulai assessment.', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF6B7280), fontSize: 13)),
                ],
              ),
            ),
          )
        else
          ...insiden.assessments.map((a) => Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFE5E7EB)),
              boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 6, offset: const Offset(0, 2))],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: a.isLatest ? const Color(0xFFDCFCE7) : const Color(0xFFF3F4F6),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    a.isLatest ? 'Terkini' : 'Lama',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: a.isLatest ? const Color(0xFF166534) : const Color(0xFF6B7280),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        a.waktuAssesment != null ? _formatDateTime(a.waktuAssesment!) : '-',
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
                      ),
                      if (a.namaPetugas != null)
                        Text(a.namaPetugas!, style: const TextStyle(fontSize: 11, color: Color(0xFF6B7280))),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right_rounded, color: Color(0xFF9CA3AF)),
              ],
            ),
          )),
      ],
    );
  }

  static String _formatDateTime(DateTime dt) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}, ${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// TAB 3: PERSONEL
// ─────────────────────────────────────────────────────────────────────────────
class _PersonelTab extends StatelessWidget {
  final InsidenModel insiden;
  const _PersonelTab({required this.insiden});

  @override
  Widget build(BuildContext context) {
    final activeStatuses = {'assigned', 'notified', 'accepted', 'on_route', 'on_site', 'aktif', 'draft'};
    final activePenugasan = insiden.penugasan.where((p) => activeStatuses.contains(p.statusPenugasan)).toList();

    if (activePenugasan.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text('👥', style: TextStyle(fontSize: 48)),
            SizedBox(height: 12),
            Text('Belum ada personel bertugas', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            SizedBox(height: 4),
            Text('Personel akan tampil setelah ada penugasan aktif.', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF6B7280), fontSize: 13)),
          ],
        ),
      );
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Text('${activePenugasan.length} Personel Aktif', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF374151))),
        const SizedBox(height: 12),
        ...activePenugasan.map((p) => Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFE5E7EB)),
            boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 6, offset: const Offset(0, 2))],
          ),
          child: Row(
            children: [
              CircleAvatar(
                backgroundColor: const Color(0xFFDCFCE7),
                child: Text(
                  p.namaPersonel.isNotEmpty ? p.namaPersonel[0].toUpperCase() : '?',
                  style: const TextStyle(color: Color(0xFF166534), fontWeight: FontWeight.bold),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(p.namaPersonel, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(color: const Color(0xFFDBEAFE), borderRadius: BorderRadius.circular(6)),
                          child: Text(
                            p.peranOtoritas.toUpperCase(),
                            style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: Color(0xFF1D4ED8)),
                          ),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          p.statusPenugasan.replaceAll('_', ' '),
                          style: const TextStyle(fontSize: 11, color: Color(0xFF6B7280)),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              if (p.waktuMulai != null)
                Text(
                  '${p.waktuMulai!.day}/${p.waktuMulai!.month}',
                  style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF)),
                ),
            ],
          ),
        )),
      ],
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// TAB 4: JURNAL
// ─────────────────────────────────────────────────────────────────────────────
class _JurnalTab extends StatelessWidget {
  final InsidenModel insiden;
  const _JurnalTab({required this.insiden});

  @override
  Widget build(BuildContext context) {
    if (insiden.jurnal.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text('📝', style: TextStyle(fontSize: 48)),
            SizedBox(height: 12),
            Text('Belum ada jurnal operasional', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            SizedBox(height: 4),
            Text('Aktivitas operasional akan dicatat di sini.', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF6B7280), fontSize: 13)),
          ],
        ),
      );
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: insiden.jurnal.map((j) => Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFE5E7EB)),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 6, offset: const Offset(0, 2))],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 40,
              child: Text(
                j.dibuatPada != null
                    ? '${j.dibuatPada!.hour.toString().padLeft(2, '0')}:${j.dibuatPada!.minute.toString().padLeft(2, '0')}'
                    : '--:--',
                style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF), fontFamily: 'monospace'),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(j.judulEvent, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF111827))),
                  if (j.deskripsiEvent != null && j.deskripsiEvent!.isNotEmpty) ...[
                    const SizedBox(height: 3),
                    Text(j.deskripsiEvent!, style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
                  ],
                ],
              ),
            ),
          ],
        ),
      )).toList(),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// SHARED WIDGETS
// ─────────────────────────────────────────────────────────────────────────────
class _SectionCard extends StatelessWidget {
  final String? title;
  final Widget child;
  const _SectionCard({this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
        border: Border.all(color: const Color(0xFFF3F4F6)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (title != null) ...[
            Text(title!, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF374151))),
            const SizedBox(height: 12),
          ],
          child,
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final String label;
  final String value;
  const _InfoRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(label, style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
          ),
          const Text(':', style: TextStyle(color: Color(0xFF9CA3AF))),
          const SizedBox(width: 8),
          Expanded(
            child: Text(value, style: const TextStyle(fontSize: 13, color: Color(0xFF111827), fontWeight: FontWeight.w500)),
          ),
        ],
      ),
    );
  }
}
