import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/router/app_router.dart';
import '../../../../core/theme/nurisk_colors.dart';
import '../../../../core/theme/nurisk_radius.dart';
import '../../../../core/theme/nurisk_spacing.dart';
import '../providers/workspace_provider.dart';
import '../../../auth/presentation/providers/auth_provider.dart';
import '../widgets/status_operasional_card.dart';
import '../widgets/command_center_card.dart';

class WorkspaceScreen extends ConsumerWidget {
  const WorkspaceScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final workspaceAsync = ref.watch(workspaceProvider);

    return Scaffold(
      backgroundColor: NuriskColors.neutral50,
      body: workspaceAsync.when(
        data: (data) {
          final profil = data.profil;
          if (profil == null) {
            return _buildUnauthenticatedView(context, ref);
          }
          return _buildAuthenticatedView(context, ref, data);
        },
        loading: () => const Center(
          child: CircularProgressIndicator(color: NuriskColors.primary600),
        ),
        error: (err, stack) => _buildErrorView(context, ref, err),
      ),
    );
  }

  Widget _buildErrorView(BuildContext context, WidgetRef ref, Object err) {
    return SafeArea(
      child: Center(
        child: Padding(
          padding: const EdgeInsets.all(NuriskSpacing.xl),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.cloud_off_rounded, size: 56, color: NuriskColors.neutral400),
              const SizedBox(height: NuriskSpacing.lg),
              Text(
                'Terjadi Kesalahan',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: NuriskColors.neutral900,
                ),
              ),
              const SizedBox(height: NuriskSpacing.sm),
              Text(
                err.toString(),
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 14, color: NuriskColors.neutral600),
              ),
              const SizedBox(height: NuriskSpacing.xl),
              ElevatedButton.icon(
                onPressed: () => ref.invalidate(workspaceProvider),
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('Coba Lagi'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: NuriskColors.primary600,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(NuriskRadius.sm),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─────────────────────────────────────────────────────────
  // UNAUTHENTICATED VIEW — Ilustrasi hero + Teks singkat + CTA
  // ─────────────────────────────────────────────────────────
  Widget _buildUnauthenticatedView(BuildContext context, WidgetRef ref) {
    return SafeArea(
      child: LayoutBuilder(
        builder: (context, constraints) {
          final availableHeight = constraints.maxHeight;
          return SingleChildScrollView(
            child: ConstrainedBox(
              constraints: BoxConstraints(minHeight: availableHeight),
              child: IntrinsicHeight(
                child: Column(
                  children: [
                    _buildIlustrasiSection(context, constraints),
                    const SizedBox(height: 16),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: NuriskSpacing.xl),
                      child: Column(
                        children: [
                          Text(
                            'Sistem Informasi Bencana NU',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.w700,
                              color: NuriskColors.neutral900,
                            ),
                          ),
                          const SizedBox(height: NuriskSpacing.sm),
                          Text(
                            'NURISK (NU Risk Information System).\n'
                            'Mendukung pelaporan, koordinasi, asesmen,\n'
                            'dan respons bencana secara real-time.',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 14,
              height: 1.5,
                              color: NuriskColors.neutral600,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                    _buildActionButtons(context),
                    const SizedBox(height: NuriskSpacing.xl),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildIlustrasiSection(BuildContext context, BoxConstraints constraints) {
    final illustrationHeight = constraints.maxHeight * 0.38;
    return SizedBox(
      width: double.infinity,
      height: illustrationHeight.clamp(180, 320),
      child: SvgPicture.asset(
        'assets/profil/profil.svg',
        fit: BoxFit.contain,
        placeholderBuilder: (context) => Container(
          width: double.infinity,
          decoration: BoxDecoration(
            color: NuriskColors.primary50,
            borderRadius: BorderRadius.circular(NuriskRadius.md),
          ),
          child: Icon(
            Icons.volunteer_activism_rounded,
            size: 100,
            color: NuriskColors.primary200,
          ),
        ),
      ),
    );
  }

  Widget _buildActionButtons(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(
        NuriskSpacing.xl,
        0,
        NuriskSpacing.xl,
        0,
      ),
      child: Column(
        children: [
          SizedBox(
            width: double.infinity,
            height: 52,
            child: ElevatedButton.icon(
              onPressed: () => context.push(RoutePaths.login),
              icon: const Icon(Icons.login_rounded, size: 20),
              label: const Text(
                'Masuk ke NURISK',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w600,
                  letterSpacing: 0.3,
                ),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: NuriskColors.primary600,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(NuriskRadius.sm),
                ),
              ),
            ),
          ),
          const SizedBox(height: NuriskSpacing.md),
          SizedBox(
            width: double.infinity,
            height: 52,
            child: OutlinedButton.icon(
              onPressed: () => context.push(RoutePaths.register),
              icon: const Icon(Icons.person_add_rounded, size: 20),
              label: const Text(
                'Gabung sebagai Relawan',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w600,
                  letterSpacing: 0.3,
                ),
              ),
              style: OutlinedButton.styleFrom(
                foregroundColor: NuriskColors.primary700,
                side: BorderSide(color: NuriskColors.primary300, width: 1.5),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(NuriskRadius.sm),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ─────────────────────────────────────────────────────────
  // AUTHENTICATED VIEW — Existing workspace functionality
  // ─────────────────────────────────────────────────────────
  Widget _buildAuthenticatedView(
    BuildContext context,
    WidgetRef ref,
    dynamic data,
  ) {
    final profil = data.profil as Map<String, dynamic>;
    final userName = profil['nama_lengkap'] ?? profil['no_hp'] ?? 'Pengguna';

    return RefreshIndicator(
      color: NuriskColors.primary600,
      onRefresh: () async {
        ref.invalidate(workspaceProvider);
        await ref.read(workspaceProvider.future);
      },
      child: CustomScrollView(
        slivers: [
          _buildProfileSliverAppBar(context, ref, profil, userName),
          SliverToBoxAdapter(child: _buildProfileContent(context, ref, data, profil)),
        ],
      ),
    );
  }

  Widget _buildProfileSliverAppBar(
    BuildContext context,
    WidgetRef ref,
    Map<String, dynamic> profil,
    String userName,
  ) {
    return SliverAppBar(
      expandedHeight: 200,
      pinned: true,
      backgroundColor: NuriskColors.primary700,
      foregroundColor: Colors.white,
      systemOverlayStyle: const SystemUiOverlayStyle(
        statusBarIconBrightness: Brightness.light,
      ),
      flexibleSpace: FlexibleSpaceBar(
        background: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [
                NuriskColors.primary900,
                NuriskColors.primary600,
              ],
            ),
          ),
          child: SafeArea(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const SizedBox(height: 56),
                CircleAvatar(
                  radius: 36,
                  backgroundColor: Colors.white.withValues(alpha: 0.2),
                  child: Text(
                    _getInitials(userName),
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  userName,
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                Text(
                  profil['nama_peran'] ?? 'Relawan',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: Colors.white.withValues(alpha: 0.8),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.refresh_rounded),
          tooltip: 'Muat ulang',
          onPressed: () => ref.invalidate(workspaceProvider),
        ),
        IconButton(
          icon: const Icon(Icons.logout_rounded),
          tooltip: 'Keluar',
          onPressed: () {
            showDialog(
              context: context,
              builder: (ctx) => AlertDialog(
                title: const Text('Keluar'),
                content: const Text('Yakin ingin keluar dari akun ini?'),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(ctx),
                    child: Text('Batal',
                        style: TextStyle(color: NuriskColors.neutral600)),
                  ),
                  ElevatedButton(
                    onPressed: () {
                      Navigator.pop(ctx);
                      ref.read(authProvider.notifier).logout();
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: NuriskColors.emergencyRed,
                      foregroundColor: Colors.white,
                    ),
                    child: const Text('Keluar'),
                  ),
                ],
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildProfileContent(
    BuildContext context,
    WidgetRef ref,
    dynamic data,
    Map<String, dynamic> profil,
  ) {
    return Padding(
      padding: const EdgeInsets.all(NuriskSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildAvailabilitySection(profil, ref),

          if (data.keahlian.isNotEmpty) ...[
            const SizedBox(height: NuriskSpacing.lg),
            _buildSectionLabel('Keahlian (${data.keahlian.length})'),
            const SizedBox(height: NuriskSpacing.sm),
            _buildKeahlianWrap(data.keahlian),
          ],
          if (profil['nama_peran'] != 'publik' && profil['nama_peran'] != null) ...[
            const SizedBox(height: NuriskSpacing.lg),
            _buildSectionLabel('Menu Operasional'),
            const SizedBox(height: NuriskSpacing.sm),
            if (profil['nama_peran'] == 'pwnu' ||
                profil['nama_peran'] == 'pcnu' ||
                profil['nama_peran'] == 'operator' ||
                profil['nama_peran'] == 'admin') ...[
              _buildMenuCard(
                context: context,
                icon: Icons.playlist_add_check_rounded,
                title: 'Validasi Laporan Publik',
                subtitle: 'Tinjau laporan kejadian dari masyarakat',
                color: NuriskColors.emergencyRed,
                onTap: () => context.push(RoutePaths.reportValidation),
              ),
              const SizedBox(height: NuriskSpacing.sm),
              _buildMenuCard(
                context: context,
                icon: Icons.crisis_alert_rounded,
                title: 'Insiden Operasional',
                subtitle: 'Pantau dan kelola insiden bencana',
                color: NuriskColors.primary600,
                onTap: () => context.push(RoutePaths.insidenList),
              ),
            ],
            if (data.commandCenter != null)
              CommandCenterCard(
                commandCenter: data.commandCenter,
                alertInsiden: data.alertInsiden,
              ),

          ],
          const SizedBox(height: NuriskSpacing.xxxl),
        ],
      ),
    );
  }

  Widget _buildAvailabilitySection(Map<String, dynamic> profil, WidgetRef ref) {
    if (profil['nama_peran'] == 'publik' || profil['status_akun'] != 'aktif') {
      return const SizedBox.shrink();
    }

    return Consumer(
      builder: (context, ref, child) {
        final isTersedia =
            (profil['is_tersedia'] == 1 || profil['is_tersedia'] == true);
        return Card(
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(NuriskRadius.md),
            side: BorderSide(color: NuriskColors.neutral200),
          ),
          child: Padding(
            padding: const EdgeInsets.all(NuriskSpacing.lg),
            child: Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: isTersedia
                        ? NuriskColors.safeGreen.withValues(alpha: 0.1)
                        : NuriskColors.neutral200,
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    isTersedia ? Icons.check_circle_rounded : Icons.timer_rounded,
                    color: isTersedia ? NuriskColors.safeGreen : NuriskColors.neutral500,
                    size: 22,
                  ),
                ),
                const SizedBox(width: NuriskSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Status Ketersediaan',
                        style: TextStyle(
                          fontSize: 12,
                          color: NuriskColors.neutral600,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        isTersedia ? 'Tersedia untuk Penugasan' : 'Tidak Tersedia',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                          color: isTersedia
                              ? NuriskColors.safeGreen
                              : NuriskColors.neutral700,
                        ),
                      ),
                    ],
                  ),
                ),
                Switch(
                  value: isTersedia,
                  activeTrackColor: NuriskColors.safeGreen.withValues(alpha: 0.4),
                  activeThumbColor: NuriskColors.safeGreen,
                  onChanged: (_) async {
                    if (isTersedia) {
                      final confirm = await showDialog<bool>(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          title: const Text('Tandai Tidak Tersedia?'),
                          content: const Text(
                            'Status Anda akan berubah menjadi tidak tersedia untuk penugasan.',
                          ),
                          actions: [
                            TextButton(
                              onPressed: () => Navigator.pop(ctx, false),
                              child: Text('Batal',
                                  style: TextStyle(color: NuriskColors.neutral600)),
                            ),
                            ElevatedButton(
                              onPressed: () => Navigator.pop(ctx, true),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: NuriskColors.primary600,
                                foregroundColor: Colors.white,
                              ),
                              child: const Text('Ya'),
                            ),
                          ],
                        ),
                      );
                      if (confirm != true) return;
                    }
                    ref.read(workspaceProvider.notifier).toggleAvailability();
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildSectionLabel(String label) {
    return Padding(
      padding: const EdgeInsets.only(left: 4),
      child: Text(
        label.toUpperCase(),
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: NuriskColors.neutral500,
          letterSpacing: 0.8,
        ),
      ),
    );
  }

  Widget _buildJabatanCard(Map<String, dynamic>? jabatan) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(NuriskRadius.md),
        side: BorderSide(color: NuriskColors.neutral200),
      ),
      child: Padding(
        padding: const EdgeInsets.all(NuriskSpacing.lg),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: Colors.amber.withValues(alpha: 0.15),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.star_rounded, color: Color(0xFFF59E0B), size: 24),
            ),
            const SizedBox(width: NuriskSpacing.md),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    jabatan?['nama_jabatan'] ?? '',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w600,
                      color: NuriskColors.neutral900,
                    ),
                  ),
                  if (jabatan?['nama_pcnu'] != null)
                    Text(
                      jabatan!['nama_pcnu'],
                      style: TextStyle(fontSize: 12, color: NuriskColors.neutral500),
                    ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: NuriskColors.safeGreen.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(NuriskRadius.full),
              ),
              child: Text(
                'Aktif',
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                  color: NuriskColors.safeGreen,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildKeahlianWrap(List<dynamic> keahlian) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: keahlian.map((k) {
        final nama = k['nama_keahlian'] ?? '';
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: NuriskColors.primary50,
            borderRadius: BorderRadius.circular(NuriskRadius.full),
            border: Border.all(color: NuriskColors.primary100),
          ),
          child: Text(
            nama,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w500,
              color: NuriskColors.primary700,
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildMenuCard({
    required BuildContext context,
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(NuriskRadius.md),
        side: BorderSide(color: color.withValues(alpha: 0.15)),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(NuriskRadius.md),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(NuriskSpacing.lg),
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 24),
              ),
              const SizedBox(width: NuriskSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                        color: NuriskColors.neutral900,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: TextStyle(
                        fontSize: 12,
                        color: NuriskColors.neutral500,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(Icons.chevron_right_rounded, color: color.withValues(alpha: 0.5)),
            ],
          ),
        ),
      ),
    );
  }

  String _getInitials(String name) {
    final parts = name.trim().split(' ');
    if (parts.length >= 2) {
      return '${parts.first[0]}${parts.last[0]}'.toUpperCase();
    }
    return name.isNotEmpty ? name[0].toUpperCase() : '?';
  }
}

// ─────────────────────────────────────────────────────────
// AUTH BOTTOM SHEET MODAL

