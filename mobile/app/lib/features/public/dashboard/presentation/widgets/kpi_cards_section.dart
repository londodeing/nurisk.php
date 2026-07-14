import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/widgets/skeleton.dart';
import '../notifiers/dashboard_kpi_provider.dart';

class KpiCardsSection extends ConsumerWidget {
  const KpiCardsSection({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final kpiState = ref.watch(dashboardKpiProvider);

    return kpiState.when(
      data: (kpi) => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
        child: GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisSpacing: 16.0,
          mainAxisSpacing: 16.0,
          childAspectRatio: 1.5,
          children: [
            _buildKpiCard(context, 'Insiden Aktif', kpi.activeIncidents.toString(), Icons.local_fire_department, Colors.red),
            _buildKpiCard(context, 'Personel Aktif', kpi.verifiedIncidents.toString(), Icons.group, Colors.green),
            _buildKpiCard(context, 'Korban Terdampak', kpi.impactedRegions.toString(), Icons.personal_injury, Colors.blue),
            _buildKpiCard(context, 'Kebutuhan Mendesak', kpi.deployedVolunteers.toString(), Icons.warning, Colors.orange),
          ],
        ),
      ),
      loading: () => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
        child: GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisSpacing: 16.0,
          mainAxisSpacing: 16.0,
          childAspectRatio: 1.5,
          children: List.generate(4, (index) => _buildKpiCardSkeleton()),
        ),
      ),
      error: (error, stack) => Padding(
        padding: const EdgeInsets.all(16.0),
        child: Card(
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              children: [
                const Icon(Icons.error_outline, color: Colors.red),
                const SizedBox(height: 8),
                const Text('Gagal memuat statistik KPI.'),
                TextButton(
                  onPressed: () => ref.read(dashboardKpiProvider.notifier).refresh(),
                  child: const Text('Coba Lagi'),
                )
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildKpiCardSkeleton() {
    return const Card(
      elevation: 2,
      child: Padding(
        padding: EdgeInsets.all(12.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Skeleton(width: 20, height: 20),
                SizedBox(width: 8),
                Skeleton(width: 40, height: 24),
              ],
            ),
            SizedBox(height: 8),
            Skeleton(width: 80, height: 12),
          ],
        ),
      ),
    );
  }

  Widget _buildKpiCard(BuildContext context, String title, String value, IconData icon, Color color) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(icon, size: 20, color: color),
                const SizedBox(width: 8),
                Text(
                  value,
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              title,
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ],
        ),
      ),
    );
  }
}
