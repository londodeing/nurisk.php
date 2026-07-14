import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

class StatusOperasionalCard extends StatelessWidget {
  final List<dynamic> penugasan;

  const StatusOperasionalCard({super.key, required this.penugasan});

  @override
  Widget build(BuildContext context) {
    if (penugasan.isEmpty) {
      return Card(
        margin: const EdgeInsets.only(top: 12, left: 16, right: 16),
        elevation: 0,
        color: Colors.grey.shade100,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: BorderSide(color: Colors.grey.shade300, width: 1),
        ),
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            children: [
              Icon(Icons.assignment_turned_in, size: 48, color: Colors.grey.shade400),
              const SizedBox(height: 12),
              Text(
                'Tidak Ada Penugasan',
                style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey.shade700, fontSize: 16),
              ),
              const SizedBox(height: 4),
              Text(
                'Anda belum memiliki penugasan operasional aktif.',
                style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      );
    }

    return Column(
      children: penugasan.map((entry) {
        final p = entry as Map<String, dynamic>;
        final peranMap = _getPeranMapping(p['peran_otoritas']?.toString() ?? '');
        final statusMap = _getStatusInsidenMapping(p['status_insiden']?.toString() ?? '');

        final cardColor = peranMap['bg'] as Color;
        final fgColor = peranMap['color'] as Color;
        final iconData = peranMap['icon'] as IconData;
        final label = peranMap['label'] as String;

        return Card(
          margin: const EdgeInsets.only(top: 12, left: 16, right: 16),
          elevation: 2,
          color: cardColor,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: BorderSide(color: fgColor.withOpacity(0.2), width: 1),
          ),
          child: InkWell(
            borderRadius: BorderRadius.circular(16),
            onTap: () {
              context.push('/assessment/${p['uuid_insiden']}');
            },
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.6),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(iconData, color: fgColor, size: 32),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '$label Asesmen',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: fgColor),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Insiden ${p['nama_bencana'] ?? ''} - ${p['kode_kejadian'] ?? ''}',
                          style: TextStyle(fontSize: 13, color: fgColor.withOpacity(0.85)),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: statusMap['bg'] as Color,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                statusMap['label'] as String,
                                style: TextStyle(
                                  fontSize: 10,
                                  color: statusMap['color'] as Color,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            const Spacer(),
                            Text(
                              'Buka Form',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: fgColor),
                            ),
                            Icon(Icons.chevron_right, size: 20, color: fgColor),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      }).toList(),
    );
  }

  Map<String, dynamic> _getPeranMapping(String peran) {
    switch (peran) {
      case 'komandan_insiden':
        return {'label': 'Komandan', 'bg': Colors.red.shade50, 'color': Colors.red.shade800, 'icon': Icons.star};
      case 'trc':
        return {'label': 'Tim Reaksi Cepat', 'bg': Colors.orange.shade50, 'color': Colors.orange.shade900, 'icon': Icons.flash_on};
      case 'relawan':
        return {'label': 'Relawan', 'bg': Colors.blue.shade50, 'color': Colors.blue.shade800, 'icon': Icons.handshake};
      case 'medis':
        return {'label': 'Tim Medis', 'bg': Colors.green.shade50, 'color': Colors.green.shade800, 'icon': Icons.favorite};
      case 'logistik':
        return {'label': 'Logistik', 'bg': Colors.cyan.shade50, 'color': Colors.cyan.shade800, 'icon': Icons.inventory};
      case 'operator':
        return {'label': 'Operator', 'bg': Colors.indigo.shade50, 'color': Colors.indigo.shade800, 'icon': Icons.monitor};
      default:
        return {'label': 'Petugas', 'bg': Colors.grey.shade100, 'color': Colors.grey.shade800, 'icon': Icons.person};
    }
  }

  Map<String, dynamic> _getStatusInsidenMapping(String status) {
    switch (status) {
      case 'draft':
        return {'label': 'Draft', 'bg': Colors.grey.shade300, 'color': Colors.grey.shade800};
      case 'terverifikasi':
        return {'label': 'Terverifikasi', 'bg': Colors.blue.shade200, 'color': Colors.blue.shade900};
      case 'respon':
        return {'label': 'RESPON', 'bg': Colors.orange.shade600, 'color': Colors.white};
      case 'pemulihan':
        return {'label': 'Pemulihan', 'bg': Colors.purple.shade200, 'color': Colors.purple.shade900};
      case 'selesai':
        return {'label': 'Selesai', 'bg': Colors.green.shade600, 'color': Colors.white};
      case 'dibatalkan':
        return {'label': 'Dibatalkan', 'bg': Colors.red.shade600, 'color': Colors.white};
      default:
        return {'label': status.toUpperCase(), 'bg': Colors.grey.shade300, 'color': Colors.grey.shade800};
    }
  }
}
