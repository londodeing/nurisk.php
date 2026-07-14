import 'package:flutter/material.dart';

/// Badge berwarna untuk status insiden & prioritas
class InsidenStatusBadge extends StatelessWidget {
  final String status;
  final bool small;

  const InsidenStatusBadge({super.key, required this.status, this.small = false});

  static _BadgeConfig _config(String status) {
    switch (status.toLowerCase()) {
      case 'draft':
        return _BadgeConfig('Draft', const Color(0xFF6B7280), const Color(0xFFF3F4F6));
      case 'terverifikasi':
        return _BadgeConfig('Terverifikasi', const Color(0xFF1D4ED8), const Color(0xFFDBEAFE));
      case 'respon':
        return _BadgeConfig('Respon', const Color(0xFFEA580C), const Color(0xFFFEF3C7));
      case 'pemulihan':
        return _BadgeConfig('Pemulihan', const Color(0xFFCA8A04), const Color(0xFFFEF9C3));
      case 'selesai':
        return _BadgeConfig('Selesai', const Color(0xFF15803D), const Color(0xFFDCFCE7));
      case 'dibatalkan':
        return _BadgeConfig('Dibatalkan', const Color(0xFFDC2626), const Color(0xFFFEE2E2));
      // Prioritas
      case 'rendah':
        return _BadgeConfig('Rendah', const Color(0xFF15803D), const Color(0xFFDCFCE7));
      case 'sedang':
        return _BadgeConfig('Sedang', const Color(0xFF1D4ED8), const Color(0xFFDBEAFE));
      case 'tinggi':
        return _BadgeConfig('Tinggi', const Color(0xFFEA580C), const Color(0xFFFEF3C7));
      case 'kritis':
        return _BadgeConfig('Kritis', const Color(0xFFDC2626), const Color(0xFFFEE2E2));
      default:
        return _BadgeConfig(status, const Color(0xFF6B7280), const Color(0xFFF3F4F6));
    }
  }

  @override
  Widget build(BuildContext context) {
    final cfg = _config(status);
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: small ? 6 : 8,
        vertical: small ? 2 : 4,
      ),
      decoration: BoxDecoration(
        color: cfg.background,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: cfg.color.withOpacity(0.3)),
      ),
      child: Text(
        cfg.label,
        style: TextStyle(
          color: cfg.color,
          fontSize: small ? 10 : 12,
          fontWeight: FontWeight.w600,
          letterSpacing: 0.3,
        ),
      ),
    );
  }
}

class _BadgeConfig {
  final String label;
  final Color color;
  final Color background;
  const _BadgeConfig(this.label, this.color, this.background);
}

/// Badge ikon prioritas (untuk card)
class PrioritasIcon extends StatelessWidget {
  final String prioritas;
  const PrioritasIcon({super.key, required this.prioritas});

  @override
  Widget build(BuildContext context) {
    Color color;
    IconData icon;
    switch (prioritas.toLowerCase()) {
      case 'kritis':
        color = const Color(0xFFDC2626);
        icon = Icons.warning_rounded;
        break;
      case 'tinggi':
        color = const Color(0xFFEA580C);
        icon = Icons.arrow_upward_rounded;
        break;
      case 'rendah':
        color = const Color(0xFF15803D);
        icon = Icons.arrow_downward_rounded;
        break;
      default:
        color = const Color(0xFF1D4ED8);
        icon = Icons.remove_rounded;
    }
    return Icon(icon, color: color, size: 18);
  }
}
