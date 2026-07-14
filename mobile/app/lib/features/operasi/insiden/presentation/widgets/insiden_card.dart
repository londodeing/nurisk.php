import 'package:flutter/material.dart';
import 'package:nurisk_mobile/features/operasi/insiden/data/models/insiden_model.dart';
import 'package:nurisk_mobile/features/operasi/insiden/presentation/widgets/insiden_status_badge.dart';

class InsidenCard extends StatelessWidget {
  final InsidenModel insiden;
  final VoidCallback onTap;

  const InsidenCard({super.key, required this.insiden, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 10,
              offset: const Offset(0, 3),
            ),
          ],
          border: Border.all(
            color: insiden.isLocked
                ? Colors.grey.shade300
                : _borderColor(insiden.status),
            width: 1.5,
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header row: kode + lock icon
              Row(
                children: [
                  Expanded(
                    child: Row(
                      children: [
                        const Icon(Icons.crisis_alert_rounded, size: 18, color: Color(0xFF374151)),
                        const SizedBox(width: 6),
                        Text(
                          insiden.kode,
                          style: const TextStyle(
                            fontFamily: 'monospace',
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: Color(0xFF111827),
                          ),
                        ),
                        if (insiden.isLocked) ...const [
                          SizedBox(width: 6),
                          Icon(Icons.lock_rounded, size: 14, color: Color(0xFF6B7280)),
                        ],
                      ],
                    ),
                  ),
                  PrioritasIcon(prioritas: insiden.prioritas),
                  const SizedBox(width: 8),
                  InsidenStatusBadge(status: insiden.status, small: true),
                ],
              ),
              const SizedBox(height: 10),

              // Jenis bencana & PCNU
              if (insiden.jenisBencana != null)
                Row(
                  children: [
                    const Icon(Icons.local_fire_department_rounded, size: 14, color: Color(0xFF9CA3AF)),
                    const SizedBox(width: 4),
                    Text(
                      insiden.jenisBencana!,
                      style: const TextStyle(fontSize: 13, color: Color(0xFF374151), fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
              if (insiden.pcnu != null) ...[
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(Icons.location_on_rounded, size: 14, color: Color(0xFF9CA3AF)),
                    const SizedBox(width: 4),
                    Text(
                      insiden.pcnu!,
                      style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280)),
                    ),
                  ],
                ),
              ],

              const SizedBox(height: 10),

              // Footer: waktu mulai + SPK status
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  if (insiden.waktuMulai != null)
                    Row(
                      children: [
                        const Icon(Icons.access_time_rounded, size: 12, color: Color(0xFF9CA3AF)),
                        const SizedBox(width: 4),
                        Text(
                          _formatDate(insiden.waktuMulai!),
                          style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF)),
                        ),
                      ],
                    )
                  else
                    const SizedBox(),
                  // SPK indicator
                  if (insiden.hasSuratTugas)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFDCFCE7),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.assignment_turned_in_rounded, size: 10, color: Color(0xFF15803D)),
                          SizedBox(width: 3),
                          Text('SPK Terbit', style: TextStyle(fontSize: 10, color: Color(0xFF15803D), fontWeight: FontWeight.w600)),
                        ],
                      ),
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Color _borderColor(String status) {
    switch (status) {
      case 'draft': return const Color(0xFFD1D5DB);
      case 'terverifikasi': return const Color(0xFF93C5FD);
      case 'respon': return const Color(0xFFFDBA74);
      case 'pemulihan': return const Color(0xFFFCD34D);
      case 'selesai': return const Color(0xFF86EFAC);
      case 'dibatalkan': return const Color(0xFFFCA5A5);
      default: return const Color(0xFFD1D5DB);
    }
  }

  String _formatDate(DateTime dt) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}';
  }
}
