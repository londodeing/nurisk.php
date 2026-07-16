import 'package:flutter/material.dart';
import 'package:nurisk_mobile/features/public/incident/domain/entities/incident_entity.dart';

void incidentDetailSheet(BuildContext context, IncidentEntity incident) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (_) => _IncidentDetailSheet(incident: incident),
  );
}

class _IncidentDetailSheet extends StatelessWidget {
  final IncidentEntity incident;

  const _IncidentDetailSheet({required this.incident});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bg = isDark ? const Color(0xFF161B19) : Colors.white;
    final fg = isDark ? Colors.white : const Color(0xFF1A1D1F);
    final muted = isDark ? Colors.white38 : Colors.black38;

    return DraggableScrollableSheet(
      initialChildSize: 0.52,
      minChildSize: 0.3,
      maxChildSize: 0.85,
      builder: (_, scrollCtrl) => Container(
        decoration: BoxDecoration(
          color: bg,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.15),
              blurRadius: 20,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: ListView(
          controller: scrollCtrl,
          padding: const EdgeInsets.fromLTRB(20, 10, 20, 32),
          children: [
            Center(
              child: Container(
                width: 36,
                height: 4,
                margin: const EdgeInsets.only(bottom: 14),
                decoration: BoxDecoration(
                  color: isDark ? Colors.white12 : Colors.black12,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 42,
                  height: 42,
                  decoration: BoxDecoration(
                    color: _catColor(incident.category).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    _catIcon(incident.category),
                    color: _catColor(incident.category),
                    size: 22,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _formatTitle(incident),
                        style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: fg, height: 1.3),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Icon(Icons.location_on_rounded, size: 12, color: muted),
                          const SizedBox(width: 3),
                          Flexible(
                            child: Text(
                              incident.district,
                              style: TextStyle(fontSize: 11, color: muted),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: _catColor(incident.category).withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              incident.category,
                              style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: _catColor(incident.category)),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: _statusColor(incident.status).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    _statusLabel(incident.status),
                    style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: _statusColor(incident.status)),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
            _section('Informasi Kejadian', muted, fg, [
              _infoRow('Waktu', _formatTime(incident.occurredAt), muted, fg),
              if (incident.kode != null) _infoRow('Kode', incident.kode!, muted, fg),
              _infoRow('Korban', '${incident.korbanSummary} jiwa terdampak', muted, fg),
            ]),
            if (incident.needsNumeric.isNotEmpty) ...[
              const SizedBox(height: 16),
              _section('Kebutuhan Mendesak', muted, fg,
                incident.needsNumeric.entries.map((e) => _infoRow(e.key, '${e.value}', muted, fg)).toList(),
              ),
            ],
          ],
        ),
      ),
    );
  }

  String _formatTitle(IncidentEntity inc) {
    final cat = _capitalize(inc.category);
    final loc = inc.district.isNotEmpty ? inc.district : 'Wilayah Tidak Diketahui';
    return '$cat — $loc';
  }

  String _capitalize(String s) {
    if (s.isEmpty) return s;
    return s[0].toUpperCase() + s.substring(1).toLowerCase();
  }

  String _formatTime(DateTime dt) {
    return '${dt.day}/${dt.month}/${dt.year} ${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }

  Widget _section(String title, Color muted, Color fg, List<Widget> rows) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              width: 3,
              height: 14,
              decoration: BoxDecoration(
                color: const Color(0xFF2F80ED),
                borderRadius: BorderRadius.circular(1.5),
              ),
            ),
            const SizedBox(width: 8),
            Text(title, style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: fg)),
          ],
        ),
        const SizedBox(height: 10),
        ...rows,
      ],
    );
  }

  Widget _infoRow(String label, String value, Color muted, Color fg) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: TextStyle(fontSize: 13, color: muted, fontWeight: FontWeight.w500)),
          ),
          Expanded(
            child: Text(value, style: TextStyle(fontSize: 13, color: fg, fontWeight: FontWeight.w500)),
          ),
        ],
      ),
    );
  }

  Color _catColor(String cat) {
    switch (cat.toUpperCase()) {
      case 'BANJIR': return const Color(0xFF2F80ED);
      case 'LONGSOR': return const Color(0xFF9B51E0);
      case 'KEBAKARAN': return const Color(0xFFD7263D);
      case 'GEMPA': return const Color(0xFFF2994A);
      default: return const Color(0xFF27AE60);
    }
  }

  IconData _catIcon(String cat) {
    switch (cat.toUpperCase()) {
      case 'BANJIR': return Icons.water_drop_rounded;
      case 'LONGSOR': return Icons.terrain_rounded;
      case 'KEBAKARAN': return Icons.local_fire_department_rounded;
      case 'GEMPA': return Icons.vibration_rounded;
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
}
