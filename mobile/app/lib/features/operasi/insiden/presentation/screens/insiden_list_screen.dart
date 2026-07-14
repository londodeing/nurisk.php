import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/router/app_router.dart';
import 'package:nurisk_mobile/features/operasi/insiden/presentation/providers/insiden_providers.dart';
import 'package:nurisk_mobile/features/operasi/insiden/presentation/widgets/insiden_card.dart';
import 'package:nurisk_mobile/features/operasi/insiden/presentation/widgets/insiden_status_badge.dart';

class InsidenListScreen extends ConsumerStatefulWidget {
  const InsidenListScreen({super.key});

  @override
  ConsumerState<InsidenListScreen> createState() => _InsidenListScreenState();
}

class _InsidenListScreenState extends ConsumerState<InsidenListScreen> {
  String? _selectedStatus;
  String? _selectedPrioritas;

  static const _statusOptions = [
    ('', 'Semua Status'),
    ('draft', 'Draft'),
    ('terverifikasi', 'Terverifikasi'),
    ('respon', 'Respon'),
    ('pemulihan', 'Pemulihan'),
    ('selesai', 'Selesai'),
    ('dibatalkan', 'Dibatalkan'),
  ];

  static const _prioritasOptions = [
    ('', 'Semua Prioritas'),
    ('rendah', 'Rendah'),
    ('sedang', 'Sedang'),
    ('tinggi', 'Tinggi'),
    ('kritis', 'Kritis'),
  ];

  void _applyFilter() {
    ref.read(insidenListProvider.notifier).setFilter(
          status: _selectedStatus?.isEmpty == true ? null : _selectedStatus,
          prioritas: _selectedPrioritas?.isEmpty == true ? null : _selectedPrioritas,
        );
  }

  void _showFilterSheet() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return StatefulBuilder(builder: (ctx, setSheetState) {
          return Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Filter Insiden', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                const Text('Status', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF6B7280))),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: _statusOptions.map((opt) {
                    final selected = (_selectedStatus ?? '') == opt.$1;
                    return ChoiceChip(
                      label: Text(opt.$2),
                      selected: selected,
                      selectedColor: const Color(0xFF166534),
                      labelStyle: TextStyle(
                        color: selected ? Colors.white : const Color(0xFF374151),
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                      onSelected: (_) => setSheetState(() => _selectedStatus = opt.$1),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 16),
                const Text('Prioritas', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF6B7280))),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: _prioritasOptions.map((opt) {
                    final selected = (_selectedPrioritas ?? '') == opt.$1;
                    return ChoiceChip(
                      label: Text(opt.$2),
                      selected: selected,
                      selectedColor: const Color(0xFF166534),
                      labelStyle: TextStyle(
                        color: selected ? Colors.white : const Color(0xFF374151),
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                      onSelected: (_) => setSheetState(() => _selectedPrioritas = opt.$1),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () {
                          setSheetState(() {
                            _selectedStatus = null;
                            _selectedPrioritas = null;
                          });
                          setState(() {
                            _selectedStatus = null;
                            _selectedPrioritas = null;
                          });
                          ref.read(insidenListProvider.notifier).clearFilter();
                          Navigator.pop(ctx);
                        },
                        child: const Text('Reset'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF166534), foregroundColor: Colors.white),
                        onPressed: () {
                          setState(() {});
                          _applyFilter();
                          Navigator.pop(ctx);
                        },
                        child: const Text('Terapkan'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        });
      },
    );
  }

  bool get _hasFilter => (_selectedStatus != null && _selectedStatus!.isNotEmpty) ||
      (_selectedPrioritas != null && _selectedPrioritas!.isNotEmpty);

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(insidenListProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      appBar: AppBar(
        title: const Text('Insiden Operasional'),
        backgroundColor: const Color(0xFF166534),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          Stack(
            children: [
              IconButton(
                icon: const Icon(Icons.tune_rounded),
                onPressed: _showFilterSheet,
              ),
              if (_hasFilter)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    width: 8,
                    height: 8,
                    decoration: const BoxDecoration(color: Colors.orange, shape: BoxShape.circle),
                  ),
                ),
            ],
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: () => ref.read(insidenListProvider.notifier).fetch(),
          ),
        ],
      ),
      body: Column(
        children: [
          // Active filter chips
          if (_hasFilter)
            Container(
              width: double.infinity,
              color: const Color(0xFFDCFCE7),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Wrap(
                spacing: 8,
                children: [
                  if (_selectedStatus != null && _selectedStatus!.isNotEmpty)
                    InsidenStatusBadge(status: _selectedStatus!),
                  if (_selectedPrioritas != null && _selectedPrioritas!.isNotEmpty)
                    InsidenStatusBadge(status: _selectedPrioritas!),
                ],
              ),
            ),

          // Content
          Expanded(
            child: state.isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF166534)))
                : state.error != null
                    ? _ErrorView(error: state.error!, onRetry: () => ref.read(insidenListProvider.notifier).fetch())
                    : state.items.isEmpty
                        ? _EmptyView(hasFilter: _hasFilter)
                        : RefreshIndicator(
                            color: const Color(0xFF166534),
                            onRefresh: () async => ref.read(insidenListProvider.notifier).fetch(),
                            child: ListView.builder(
                              padding: const EdgeInsets.only(top: 8, bottom: 24),
                              itemCount: state.items.length,
                              itemBuilder: (_, i) {
                                final insiden = state.items[i];
                                return InsidenCard(
                                  insiden: insiden,
                                  onTap: () => context.push(
                                    RoutePaths.insidenDetail.replaceFirst(':id', insiden.id.toString()),
                                  ),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  final String error;
  final VoidCallback onRetry;
  const _ErrorView({required this.error, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.wifi_off_rounded, size: 64, color: Color(0xFF9CA3AF)),
          const SizedBox(height: 16),
          const Text('Gagal memuat data', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 8),
          Text(error, textAlign: TextAlign.center, style: const TextStyle(color: Color(0xFF6B7280), fontSize: 13)),
          const SizedBox(height: 20),
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF166534), foregroundColor: Colors.white),
            icon: const Icon(Icons.refresh_rounded, size: 18),
            label: const Text('Coba Lagi'),
            onPressed: onRetry,
          ),
        ],
      ),
    );
  }
}

class _EmptyView extends StatelessWidget {
  final bool hasFilter;
  const _EmptyView({required this.hasFilter});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            hasFilter ? '🔍' : '📋',
            style: const TextStyle(fontSize: 56),
          ),
          const SizedBox(height: 16),
          Text(
            hasFilter ? 'Tidak ada insiden dengan filter ini' : 'Belum ada insiden tercatat',
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          ),
          const SizedBox(height: 8),
          Text(
            hasFilter ? 'Coba ubah filter pencarian.' : 'Insiden akan muncul setelah laporan diverifikasi.',
            textAlign: TextAlign.center,
            style: const TextStyle(color: Color(0xFF6B7280), fontSize: 13),
          ),
        ],
      ),
    );
  }
}
