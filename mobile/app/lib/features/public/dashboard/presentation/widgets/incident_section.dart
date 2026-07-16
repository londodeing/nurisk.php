import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/widgets/skeleton.dart';
import 'package:nurisk_mobile/features/public/incident/domain/entities/incident_entity.dart';
import 'package:nurisk_mobile/features/public/incident/presentation/notifiers/incident_provider.dart';
import 'incident_detail_sheet.dart';

class IncidentSection extends ConsumerWidget {
  final void Function(IncidentEntity)? onIncidentTap;

  const IncidentSection({super.key, this.onIncidentTap});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final feedAsync = ref.watch(incidentFeedProvider);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(18, 20, 18, 10),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    width: 4,
                    height: 18,
                    decoration: BoxDecoration(
                      color: const Color(0xFFD7263D),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(width: 10),
                  const Text(
                    'Insiden Terkini',
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700),
                  ),
                ],
              ),
              TextButton(
                onPressed: () {},
                style: const ButtonStyle(
                  padding: WidgetStatePropertyAll(EdgeInsets.symmetric(horizontal: 10)),
                  minimumSize: WidgetStatePropertyAll(Size.zero),
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  visualDensity: VisualDensity.compact,
                ),
                child: const Text(
                  'Lihat Semua',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
        ),
        feedAsync.when(
          data: (feed) {
            if (feed.incidents.isEmpty) {
              return _emptyState(context);
            }
            final items = feed.incidents.take(6).toList();
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 14),
              child: Column(
                children: List.generate(items.length, (i) {
                  final inc = items[i];
                  return Padding(
                    padding: EdgeInsets.only(top: i > 0 ? 8 : 0),
                    child: _IncidentCompactCard(
                      incident: inc,
                      onTap: () => onIncidentTap != null
                          ? onIncidentTap!(inc)
                          : _showDetailSheet(context, inc),
                    ),
                  );
                }),
              ),
            );
          },
          loading: () => _loadingState(context),
          error: (_, _) => _errorState(context),
        ),
      ],
    );
  }

  Widget _emptyState(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 32),
      child: Center(
        child: Column(
          children: [
            Icon(
              Icons.inbox_rounded,
              size: 40,
              color: isDark ? Colors.white12 : Colors.grey.shade300,
            ),
            const SizedBox(height: 10),
            Text(
              'Belum ada insiden tercatat',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: isDark ? Colors.white38 : Colors.grey.shade500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _errorState(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 32),
      child: Center(
        child: Column(
          children: [
            Icon(
              Icons.cloud_off_rounded,
              size: 36,
              color: isDark ? Colors.white24 : Colors.grey.shade400,
            ),
            const SizedBox(height: 8),
            Text(
              'Gagal memuat insiden',
              style: TextStyle(
                fontSize: 13,
                color: isDark ? Colors.white38 : Colors.grey.shade500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showDetailSheet(BuildContext context, IncidentEntity incident) {
    incidentDetailSheet(context, incident);
  }

  Widget _loadingState(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14),
      child: Column(
        children: List.generate(3, (i) => Padding(
          padding: EdgeInsets.only(top: i > 0 ? 8 : 0),
          child: _incidentSkeleton(context),
        )),
      ),
    );
  }

  Widget _incidentSkeleton(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      height: 54,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(12),
        color: isDark ? const Color(0xFF1B211D) : const Color(0xFFF1F3F5),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 12),
      child: const Row(
        children: [
          Skeleton(width: 36, height: 36, borderRadius: BorderRadius.all(Radius.circular(10))),
          SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Skeleton(width: 160, height: 14),
                SizedBox(height: 4),
                Skeleton(width: 100, height: 10),
              ],
            ),
          ),
          Skeleton(width: 48, height: 18),
        ],
      ),
    );
  }
}

class _IncidentCompactCard extends StatelessWidget {
  final IncidentEntity incident;
  final VoidCallback onTap;

  const _IncidentCompactCard({required this.incident, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final catColor = _categoryColor(incident.category);
    final statusColor = _statusColor(incident.status);

    return Material(
      color: isDark ? const Color(0xFF161B19) : Colors.white,
      borderRadius: BorderRadius.circular(14),
      elevation: 0,
      shadowColor: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.fromLTRB(10, 12, 12, 12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isDark ? Colors.white10 : Colors.grey.shade200,
              width: 0.5,
            ),
          ),
          child: Row(
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: catColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  _categoryIcon(incident.category),
                  color: catColor,
                  size: 20,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _formatTitle(incident),
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 13,
                        height: 1.3,
                        color: isDark ? Colors.white : const Color(0xFF1A1D1F),
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 3),
                    Row(
                      children: [
                        Icon(Icons.people_rounded, size: 10,
                            color: isDark ? Colors.white30 : Colors.black26),
                        const SizedBox(width: 3),
                        Text(
                          '${_formatCompact(incident.korbanSummary)} jiwa',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w500,
                            color: isDark ? Colors.white38 : Colors.black38,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: statusColor.withValues(alpha: 0.2),
                    width: 0.5,
                  ),
                ),
                child: Text(
                  _statusLabel(incident.status),
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.w700,
                    color: statusColor,
                    letterSpacing: 0.3,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// Format title as: "{Jenis Bencana} — {Kecamatan/PCNU}"
  String _formatTitle(IncidentEntity inc) {
    final cat = _capitalize(inc.category);
    final loc = inc.district.isNotEmpty ? inc.district : 'Wilayah Tidak Diketahui';
    return '$cat — $loc';
  }

  String _capitalize(String s) {
    if (s.isEmpty) return s;
    return s[0].toUpperCase() + s.substring(1).toLowerCase();
  }

  Color _categoryColor(String cat) {
    switch (cat.toUpperCase()) {
      case 'BANJIR': return const Color(0xFF2F80ED);
      case 'LONGSOR': return const Color(0xFF9B51E0);
      case 'KEBAKARAN': return const Color(0xFFD7263D);
      case 'GEMPA': return const Color(0xFFF2994A);
      case 'ANGIN': return const Color(0xFF56CCF2);
      case 'KEKERINGAN': return const Color(0xFFE8822A);
      default: return const Color(0xFF27AE60);
    }
  }

  IconData _categoryIcon(String cat) {
    switch (cat.toUpperCase()) {
      case 'BANJIR': return Icons.water_drop_rounded;
      case 'LONGSOR': return Icons.terrain_rounded;
      case 'KEBAKARAN': return Icons.local_fire_department_rounded;
      case 'GEMPA': return Icons.vibration_rounded;
      case 'ANGIN': return Icons.air_rounded;
      case 'KEKERINGAN': return Icons.water_drop_outlined;
      default: return Icons.warning_amber_rounded;
    }
  }

  Color _statusColor(String status) {
    switch (status.toUpperCase()) {
      case 'DRAFT': return Colors.grey;
      case 'TERVERIFIKASI': return const Color(0xFF27AE60);
      case 'RESPON': return const Color(0xFFE8822A);
      case 'PEMULIHAN': return const Color(0xFF2F80ED);
      case 'SELESAI': return Colors.grey;
      default: return Colors.grey;
    }
  }

  String _statusLabel(String status) {
    switch (status.toUpperCase()) {
      case 'DRAFT': return 'DRAFT';
      case 'TERVERIFIKASI': return 'TERVERIFIKASI';
      case 'RESPON': return 'RESPON';
      case 'PEMULIHAN': return 'PEMULIHAN';
      case 'SELESAI': return 'SELESAI';
      default: return status.toUpperCase();
    }
  }

  String _formatCompact(int n) {
    if (n >= 1000) return '${(n / 1000).toStringAsFixed(1)}k';
    return n.toString();
  }
}
