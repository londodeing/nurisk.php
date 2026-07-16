import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class ShortcutsSection extends StatelessWidget {
  const ShortcutsSection({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 16, 14, 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 4,
                height: 16,
                decoration: BoxDecoration(
                  color: const Color(0xFF0F6B3C),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 10),
              Text(
                'Layanan Cepat',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: isDark ? Colors.white : const Color(0xFF1A1D1F),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _ShortcutCard(
                  icon: Icons.map_rounded,
                  label: 'Peta\nBencana',
                  color: const Color(0xFF2F80ED),
                  onTap: () => context.push('/p/map'),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _ShortcutCard(
                  icon: Icons.add_circle_outline_rounded,
                  label: 'Lapor\nKejadian',
                  color: const Color(0xFFD7263D),
                  onTap: () => context.push('/p/report'),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _ShortcutCard(
                  icon: Icons.inventory_2_rounded,
                  label: 'Resource\n',
                  color: const Color(0xFF1E9B5E),
                  onTap: () => context.push('/p/resource'),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _ShortcutCard(
                  icon: Icons.volunteer_activism_rounded,
                  label: 'Daftar\nRelawan',
                  color: const Color(0xFF9B51E0),
                  onTap: () {},
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _ShortcutCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _ShortcutCard({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 4),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: isDark
                  ? [color.withValues(alpha: 0.18), color.withValues(alpha: 0.05)]
                  : [color.withValues(alpha: 0.08), color.withValues(alpha: 0.02)],
            ),
            border: Border.all(
              color: isDark
                  ? color.withValues(alpha: 0.2)
                  : color.withValues(alpha: 0.1),
              width: 0.5,
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: color, size: 20),
              ),
              const SizedBox(height: 8),
              Text(
                label,
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                  color: isDark ? Colors.white60 : Colors.black45,
                  height: 1.3,
                ),
                textAlign: TextAlign.center,
                maxLines: 2,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
