import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../notifiers/dashboard_orchestrator_provider.dart';
import '../widgets/kpi_row_section.dart';
import '../widgets/early_warning_section.dart';
import '../widgets/weather_strip_section.dart';
import '../widgets/incident_section.dart';
import '../widgets/donation_qr_card.dart';

class PublicDashboardScreen extends ConsumerWidget {
  const PublicDashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bg = isDark ? const Color(0xFF0D1110) : const Color(0xFFF5F6F8);
    final surface = isDark ? const Color(0xFF121614) : Colors.white;

    return Scaffold(
      backgroundColor: bg,
      appBar: AppBar(
        title: const Text('NURISK'),
        centerTitle: true,
        elevation: 0,
        scrolledUnderElevation: 1,
        backgroundColor: Colors.transparent,
        flexibleSpace: ClipRRect(
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
            child: Container(
              color: (isDark ? const Color(0xFF121614) : const Color(0xFFF8F9FA)).withValues(alpha: 0.6),
            ),
          ),
        ),
        surfaceTintColor: Colors.transparent,
        actions: [
          IconButton(
            icon: Icon(
              Icons.notifications_outlined,
              color: isDark ? Colors.white60 : Colors.black45,
              size: 22,
            ),
            onPressed: () {},
            tooltip: 'Notifikasi',
            splashRadius: 20,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.read(dashboardOrchestratorProvider).refreshAll(),
        child: CustomScrollView(
          physics: const BouncingScrollPhysics(),
          slivers: [
            SliverToBoxAdapter(
              child: Container(
                decoration: BoxDecoration(
                  color: surface,
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.04),
                      blurRadius: 12,
                      offset: const Offset(0, -4),
                    ),
                  ],
                ),
                child: const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(height: 24),
                    EarlyWarningSection(),
                    _SectionGap(),
                    KpiRowSection(),
                    _SectionGap(),
                    WeatherStripSection(),
                    IncidentSection(),
                    DonationQrCard(),
                    SizedBox(height: 32),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SectionGap extends StatelessWidget {
  const _SectionGap();

  @override
  Widget build(BuildContext context) {
    return const SizedBox(height: 8);
  }
}
