import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/widgets/skeleton.dart';
import '../notifiers/dashboard_kpi_provider.dart';

class KpiRowSection extends ConsumerWidget {
  const KpiRowSection({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final kpiState = ref.watch(dashboardKpiProvider);

    return kpiState.when(
      data: (kpi) => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
            Expanded(
              child: _KpiGlassCard(
                icon: Icons.local_fire_department_rounded,
                value: kpi.activeIncidents.toString(),
                label: 'Insiden Aktif',
                color: const Color(0xFFD7263D),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _KpiGlassCard(
                icon: Icons.inventory_2_rounded,
                value: kpi.deployedVolunteers.toString(),
                label: 'Gap Kebutuhan',
                color: const Color(0xFFE8822A),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _KpiGlassCard(
                icon: Icons.people_alt_rounded,
                value: kpi.impactedRegions.toString(),
                label: 'Korban Terdampak',
                color: const Color(0xFF1E9B5E),
              ),
            ),
          ],
        ),
      ),
      ),
      loading: () => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: List.generate(3, (_) => Expanded(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 4),
              child: _kpiSkeleton(),
            ),
          )),
        ),
      ),
      error: (_, _) => const SizedBox.shrink(),
    );
  }

  Widget _kpiSkeleton() {
    return Container(
      height: 84,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        color: const Color(0xFFF1F3F5),
      ),
      padding: const EdgeInsets.all(14),
      child: const Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Skeleton(width: 24, height: 24),
          SizedBox(height: 6),
          Skeleton(width: 36, height: 20),
          SizedBox(height: 2),
          Skeleton(width: 56, height: 10),
        ],
      ),
    );
  }
}

class _KpiGlassCard extends StatelessWidget {
  final IconData icon;
  final String value;
  final String label;
  final Color color;

  const _KpiGlassCard({
    required this.icon,
    required this.value,
    required this.label,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: isDark
              ? [color.withValues(alpha: 0.22), color.withValues(alpha: 0.06)]
              : [color.withValues(alpha: 0.10), color.withValues(alpha: 0.03)],
        ),
        border: Border.all(
          color: isDark
              ? color.withValues(alpha: 0.25)
              : color.withValues(alpha: 0.12),
          width: 0.5,
        ),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: isDark ? 0.08 : 0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 10),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 28,
            height: 28,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 16),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 22,
              height: 1.1,
              color: isDark ? Colors.white : color,
            ),
          ),
          Text(
            label,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w600,
              color: isDark ? Colors.white54 : Colors.black45,
              height: 1.2,
            ),
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}
