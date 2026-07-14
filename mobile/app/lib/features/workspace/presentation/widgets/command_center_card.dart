import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class CommandCenterCard extends StatelessWidget {
  final List<dynamic>? commandCenter;
  final List<dynamic>? alertInsiden;

  const CommandCenterCard({
    super.key,
    this.commandCenter,
    this.alertInsiden,
  });

  @override
  Widget build(BuildContext context) {
    if (commandCenter == null) {
      return const SizedBox.shrink(); // Not authorized
    }

    final alerts = alertInsiden ?? [];
    final activeCount = commandCenter!.length;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (alerts.isNotEmpty)
          Container(
            margin: const EdgeInsets.only(top: 12, left: 16, right: 16),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.orange.shade100,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(Icons.warning, color: Colors.orange.shade800),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '${alerts.length} insiden belum terbentuk klaster',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: Colors.orange.shade900,
                        ),
                      ),
                      const SizedBox(height: 4),
                      ...alerts.take(2).map((a) {
                        final alertData = a as Map<String, dynamic>;
                        return Padding(
                          padding: const EdgeInsets.only(top: 4),
                          child: Text(
                            '${alertData['kode_kejadian']} — ${alertData['nama_bencana']}, ${alertData['nama_pcnu'] ?? ''}',
                            style: TextStyle(fontSize: 12, color: Colors.orange.shade900),
                          ),
                        );
                      }),
                    ],
                  ),
                ),
              ],
            ),
          ),
          
        Padding(
          padding: const EdgeInsets.only(top: 16, left: 16, right: 16, bottom: 8),
          child: Row(
            children: [
              const Expanded(
                child: Text(
                  'Pusat Komando',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ),
              Row(
                children: [
                  Icon(
                    Icons.check_circle,
                    size: 16,
                    color: activeCount > 0 ? Colors.green : Colors.grey.shade500,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    '$activeCount Insiden Aktif',
                    style: const TextStyle(fontSize: 12),
                  ),
                ],
              ),
            ],
          ),
        ),

        if (activeCount == 0)
          Container(
            margin: const EdgeInsets.only(bottom: 12, left: 16, right: 16),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 4))
              ],
            ),
            child: Column(
              children: [
                Icon(Icons.check_circle, size: 48, color: Colors.green.shade600),
                const SizedBox(height: 8),
                const Text(
                  'Tidak ada insiden aktif',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 4),
                Text(
                  'Seluruh insiden dalam lingkup Anda sudah selesai atau tidak ada laporan baru',
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          )
        else
          ...commandCenter!.asMap().entries.map((entry) {
            final c = entry.value as Map<String, dynamic>;
            final prio = _getPrioritasMapping(c['prioritas']?.toString() ?? '');
            final stat = _getStatusInsidenMapping(c['status_insiden']?.toString() ?? '');
            final cmd = _getCommandStateMapping(c['command_state']?.toString() ?? '');
            final klasterForeground = (c['nama_klaster'] == 'Tunggu Aktivasi') ? Colors.orange.shade800 : Colors.green.shade700;

            return InkWell(
              onTap: () {
                context.push('/incident/${c['id_insiden']}');
              },
              child: Container(
                margin: const EdgeInsets.only(bottom: 12, left: 16, right: 16),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 4))
                  ],
                ),
                child: Column(
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Text(
                            c['kode_kejadian']?.toString() ?? '',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                        ),
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: prio['bg'] as Color,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                prio['label'] as String,
                                style: TextStyle(fontSize: 10, color: prio['color'] as Color, fontWeight: FontWeight.bold),
                              ),
                            ),
                            const SizedBox(width: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: stat['bg'] as Color,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                stat['label'] as String,
                                style: TextStyle(fontSize: 10, color: stat['color'] as Color, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(Icons.group, size: 16, color: klasterForeground),
                        const SizedBox(width: 8),
                        Text(
                          c['nama_klaster']?.toString() ?? '',
                          style: TextStyle(fontSize: 14, color: klasterForeground),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    const Divider(height: 1),
                    const SizedBox(height: 12),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        Column(
                          children: [
                            Text(
                              '${c['lama_kejadian_hari'] ?? 0}',
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: ((c['lama_kejadian_hari'] as int?) ?? 0) > 7 ? Colors.red : Colors.black87,
                              ),
                            ),
                            Text('Hari', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                          ],
                        ),
                        Container(width: 1, height: 32, color: Colors.grey.shade300),
                        Column(
                          children: [
                            Text(
                              '${c['jumlah_sitrep'] ?? 0}',
                              style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.black87),
                            ),
                            Text('Sitrep', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                          ],
                        ),
                        Container(width: 1, height: 32, color: Colors.grey.shade300),
                        Column(
                          children: [
                            Text(
                              cmd['label'] as String,
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: cmd['color'] as Color),
                            ),
                            Text('Status Ops', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: () {
                          context.push('/assessment/${c['id_insiden']}');
                        },
                        icon: const Icon(Icons.assignment),
                        label: const Text('Lakukan Asesmen / Review'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.green.shade700,
                          side: BorderSide(color: Colors.green.shade700),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          }),
      ],
    );
  }

  Map<String, dynamic> _getPrioritasMapping(String p) {
    switch (p) {
      case 'kritis':
        return {'label': 'KRITIS', 'bg': Colors.red.shade600, 'color': Colors.white};
      case 'tinggi':
        return {'label': 'TINGGI', 'bg': Colors.orange.shade600, 'color': Colors.white};
      case 'sedang':
        return {'label': 'SEDANG', 'bg': Colors.blue.shade600, 'color': Colors.white};
      case 'rendah':
      default:
        return {'label': 'RENDAH', 'bg': Colors.grey.shade200, 'color': Colors.grey.shade700};
    }
  }

  Map<String, dynamic> _getStatusInsidenMapping(String status) {
    switch (status) {
      case 'draft':
        return {'label': 'Draft', 'bg': Colors.grey.shade200, 'color': Colors.grey.shade700};
      case 'terverifikasi':
        return {'label': 'Terverifikasi', 'bg': Colors.blue.shade100, 'color': Colors.blue.shade800};
      case 'respon':
        return {'label': 'RESPON', 'bg': Colors.orange.shade600, 'color': Colors.white};
      case 'pemulihan':
        return {'label': 'Pemulihan', 'bg': Colors.purple.shade100, 'color': Colors.purple.shade800};
      case 'selesai':
        return {'label': 'Selesai', 'bg': Colors.green.shade600, 'color': Colors.white};
      case 'dibatalkan':
        return {'label': 'Dibatalkan', 'bg': Colors.red.shade600, 'color': Colors.white};
      default:
        return {'label': status.toUpperCase(), 'bg': Colors.grey.shade200, 'color': Colors.grey.shade800};
    }
  }

  Map<String, dynamic> _getCommandStateMapping(String c) {
    switch (c) {
      case 'monitoring':
        return {'label': 'Monitoring', 'color': Colors.grey.shade600};
      case 'siaga':
        return {'label': 'SIAGA', 'color': Colors.orange.shade800};
      case 'tanggap_darurat':
        return {'label': 'TANGGAP', 'color': Colors.red.shade800};
      case 'pemulihan':
        return {'label': 'Pemulihan', 'color': Colors.purple.shade800};
      case 'selesai':
        return {'label': 'Selesai', 'color': Colors.green.shade800};
      default:
        return {'label': c.toUpperCase(), 'color': Colors.black87};
    }
  }
}
