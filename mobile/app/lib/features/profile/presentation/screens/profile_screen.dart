import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import 'package:nurisk_mobile/core/router/app_router.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import '../../../auth/presentation/notifiers/auth_state_provider.dart';
import '../notifiers/profile_notifier.dart';
import '../../data/models/profile_data_model.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profileAsync = ref.watch(profileProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil & Pusat Komando'),
        elevation: 0,
        backgroundColor: Colors.green.shade700,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              ref.read(profileProvider.notifier).fetchProfile();
            },
          ),
        ],
      ),
      body: profileAsync.when(
        data: (profileData) {
          if (profileData == null) {
            return _buildGuestProfile(context);
          }
          return _buildCommandCenter(context, ref, profileData);
        },
        loading: () => const Center(
          child: CircularProgressIndicator(),
        ),
        error: (err, stack) => Center(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.error_outline, size: 64, color: Colors.red),
                const SizedBox(height: 16),
                Text(
                  DioExceptionMapper.toUserMessage(err),
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: () {
                    ref.read(profileProvider.notifier).fetchProfile();
                  },
                  child: const Text('Coba Lagi'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // GUEST PROFILE
  Widget _buildGuestProfile(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const SizedBox(height: 20),
          Center(
            child: CircleAvatar(
              radius: 50,
              backgroundColor: Colors.grey.shade100,
              child: Icon(Icons.account_circle, size: 80, color: Colors.grey.shade400),
            ),
          ),
          const SizedBox(height: 20),
          const Text(
            'Sesi Belum Aktif',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          const Text(
            'Masuk atau daftar untuk mengakses fitur Pusat Komando Personal, kaji cepat bencana, dan manajemen posko.',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.grey),
          ),
          const SizedBox(height: 32),
          ElevatedButton.icon(
            onPressed: () => RuntimeServicesScope.instance.navigation.goToLogin(),
            icon: const Icon(Icons.login),
            label: const Text('Masuk Ke Akun Saya'),
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              backgroundColor: Colors.green.shade700,
              foregroundColor: Colors.white,
            ),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: () => RuntimeServicesScope.instance.navigation.goToRegister(),
            icon: const Icon(Icons.app_registration),
            label: const Text('Daftar Akun Baru'),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
          ),
          const SizedBox(height: 40),
          const Divider(),
          const SizedBox(height: 20),
          const Text(
            'MENU PUBLIK & INFORMASI',
            style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey),
          ),
          const SizedBox(height: 12),
          _buildGuestMenuTile(
            Icons.volunteer_activism_outlined,
            'Gabung Relawan NU Peduli',
            'Ajukan diri Anda untuk aksi sosial kemanusiaan.',
            () => RuntimeServicesScope.instance.navigation.goToRegister(),
          ),
          _buildGuestMenuTile(
            Icons.favorite_border,
            'Donasi Kebencanaan',
            'Dukung pendanaan dapur umum dan logistik darurat.',
            () {},
          ),
          _buildGuestMenuTile(
            Icons.search,
            'Lacak Laporan Kejadian',
            'Pantau status verifikasi laporan bencana Anda.',
            () {},
          ),
          _buildGuestMenuTile(
            Icons.help_outline,
            'FAQ & Panduan Aplikasi',
            'Pelajari panduan dasar penggunaan aplikasi NURISK.',
            () {},
          ),
        ],
      ),
    );
  }

  Widget _buildGuestMenuTile(IconData icon, String title, String subtitle, VoidCallback onTap) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        leading: Icon(icon, color: Colors.green),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
        subtitle: Text(subtitle, style: const TextStyle(fontSize: 12)),
        trailing: const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }

  // COMMAND CENTER PROFILE
  Widget _buildCommandCenter(BuildContext context, WidgetRef ref, ProfileData data) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Section 1: Identity Card
          _buildIdentityCard(context, data.identity, data.activeMandate),
          const SizedBox(height: 16),

          // Section 2: Mandate Card
          _buildMandateCard(context, ref, data.activeMandate),
          const SizedBox(height: 16),

          // Card Perlu Perhatian (Tindak Lanjut Validasi Laporan) untuk Admin PCNU/PWNU
          if (data.activeMandate['role'] == 'pcnu' || data.activeMandate['role'] == 'pwnu' || data.activeMandate['role'] == 'super_admin') ...[
            _buildAttentionCard(context, ref),
            const SizedBox(height: 16),
          ],


          // Section 3: Personal KPI
          if (data.statistics.isNotEmpty) ...[
            _buildKpiSection(context, data.statistics),
            const SizedBox(height: 16),
          ],

          // Section 4: Quick Action
          if (data.quickActions.isNotEmpty) ...[
            _buildQuickActionsSection(context, data.quickActions),
            const SizedBox(height: 16),
          ],

          // Section 5: My Tasks
          if (data.tasks.isNotEmpty) ...[
            _buildTasksSection(context, data.tasks),
            const SizedBox(height: 16),
          ],

          // Section 6: Organization
          _buildOrganizationSection(context, data.organization),
          const SizedBox(height: 16),

          // Section 7: Resources
          if (data.resources.isNotEmpty) ...[
            _buildResourcesSection(context, data.resources),
            const SizedBox(height: 16),
          ],

          // Section 8: Recent Activity
          if (data.activities.isNotEmpty) ...[
            _buildActivitiesSection(context, data.activities),
            const SizedBox(height: 16),
          ],

          // Section 9: Settings
          _buildSettingsSection(context, data.settingsConfig),
          const SizedBox(height: 24),

          // Section 10: Logout
          ElevatedButton.icon(
            onPressed: () async {
              await ref.read(authStateProvider.notifier).logout();
            },
            icon: const Icon(Icons.logout),
            label: const Text('Keluar (Logout)'),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red.shade50,
              foregroundColor: Colors.red.shade700,
              elevation: 0,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
                side: BorderSide(color: Colors.red.shade100),
              ),
            ),
          ),
          const SizedBox(height: 40),
        ],
      ),
    );
  }

  // WIDGET BUILDERS

  Widget _buildIdentityCard(BuildContext context, Map<String, dynamic> identity, Map<String, dynamic> mandate) {
    final String initial = identity['name'] != null && identity['name'].isNotEmpty
        ? identity['name'][0].toUpperCase()
        : '?';

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Row(
          children: [
            CircleAvatar(
              radius: 36,
              backgroundColor: Colors.green.shade100,
              child: Text(
                initial,
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: Colors.green.shade800),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    identity['name'] ?? 'Nama Pengguna',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.blue.shade50,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          identity['call_sign'] ?? '-',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.blue.shade700),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.green.shade50,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          (identity['online_status'] ?? 'siaga').toUpperCase(),
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.green.shade700),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    mandate['position'] ?? '-',
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMandateCard(BuildContext context, WidgetRef ref, Map<String, dynamic> mandate) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      color: Colors.green.shade50,
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Row(
          children: [
            const Icon(Icons.verified_user, color: Colors.green),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Mandat Aktif', style: TextStyle(fontSize: 11, color: Colors.green, fontWeight: FontWeight.bold)),
                  Text(
                    mandate['position'] ?? '-',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ],
              ),
            ),
            TextButton(
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Membuka Pilihan Mandat.')),
                );
              },
              child: const Text('Ganti Mandat', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }


  Widget _buildAttentionCard(BuildContext context, WidgetRef ref) {
    return Card(

      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      color: Colors.red.shade50,
      child: ListTile(
        leading: Icon(Icons.warning, color: Colors.red.shade700),
        title: const Text(
          'Perlu Perhatian',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.red),
        ),
        subtitle: const Text(
          'Ada laporan masuk menunggu verifikasi dan validasi.',
          style: TextStyle(fontSize: 12),
        ),
        trailing: const Icon(Icons.chevron_right, color: Colors.red),
        onTap: () {
          RuntimeServicesScope.instance.navigation.push(RoutePaths.reportValidation);
        },
      ),
    );
  }


  Widget _buildKpiSection(BuildContext context, List<dynamic> stats) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        crossAxisSpacing: 8,
        mainAxisSpacing: 8,
        childAspectRatio: 1.8,
      ),
      itemCount: stats.length,
      itemBuilder: (context, index) {
        final item = stats[index];
        return Card(
          elevation: 1,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                item['value']?.toString() ?? '0',
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.green),
              ),
              const SizedBox(height: 2),
              Text(
                item['label'] ?? '-',
                style: const TextStyle(fontSize: 10, color: Colors.black54),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildQuickActionsSection(BuildContext context, List<dynamic> actions) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Aksi Cepat', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            const SizedBox(height: 12),
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 3,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
                childAspectRatio: 1.1,
              ),
              itemCount: actions.length,
              itemBuilder: (context, index) {
                final action = actions[index];
                final actionDetails = _mapActionType(action['action_type'] ?? '');

                return InkWell(
                  onTap: () {
                    final String route = actionDetails.route;
                    if (route == RoutePaths.profile || route.isEmpty) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('Aksi "${action['title']}" sedang dipersiapkan.')),
                      );
                    } else {
                      RuntimeServicesScope.instance.navigation.push(route);
                    }
                  },
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Stack(
                        children: [
                          CircleAvatar(
                            radius: 24,
                            backgroundColor: actionDetails.color.withOpacity(0.1),
                            child: Icon(actionDetails.icon, color: actionDetails.color),
                          ),
                          if (action['badge_count'] != null && action['badge_count'] > 0)
                            Positioned(
                              right: 0,
                              top: 0,
                              child: CircleAvatar(
                                radius: 8,
                                backgroundColor: Colors.red,
                                child: Text(
                                  action['badge_count'].toString(),
                                  style: const TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.bold),
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        action['title'] ?? '-',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTasksSection(BuildContext context, List<dynamic> tasks) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Tugas Hari Ini', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            const SizedBox(height: 8),
            ...tasks.map((task) => ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.assignment_turned_in_outlined, color: Colors.amber),
                  title: Text(task['title'] ?? '-', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                  subtitle: Text('Kategori: ${task['category'] ?? '-'}', style: const TextStyle(fontSize: 11)),
                  trailing: const Icon(Icons.chevron_right),
                )),
          ],
        ),
      ),
    );
  }

  Widget _buildOrganizationSection(BuildContext context, Map<String, dynamic> org) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        leading: const Icon(Icons.corporate_fare_outlined, color: Colors.blue),
        title: Text(org['name'] ?? '-', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
        subtitle: Text(org['office_address'] ?? '-', style: const TextStyle(fontSize: 11)),
      ),
    );
  }

  Widget _buildResourcesSection(BuildContext context, List<dynamic> res) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Sumber Daya Terpenuhi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            const SizedBox(height: 8),
            ...res.map((item) => ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.local_shipping_outlined, color: Colors.green),
                  title: Text(item['name'] ?? '-', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                  subtitle: Text('Status: ${item['status']?.toUpperCase() ?? '-'}', style: const TextStyle(fontSize: 11)),
                )),
          ],
        ),
      ),
    );
  }

  Widget _buildActivitiesSection(BuildContext context, List<dynamic> acts) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Timeline Aktivitas Terbaru', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            const SizedBox(height: 8),
            ...acts.map((act) => ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.history_toggle_off, color: Colors.grey),
                  title: Text(act['action'] ?? '-', style: const TextStyle(fontSize: 13)),
                  subtitle: Text(
                    act['time'] != null ? DateTime.parse(act['time']).toLocal().toString().split('.')[0] : '-',
                    style: const TextStyle(fontSize: 11),
                  ),
                )),
          ],
        ),
      ),
    );
  }

  Widget _buildSettingsSection(BuildContext context, Map<String, dynamic> cfg) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Column(
        children: [
          ListTile(
            leading: const Icon(Icons.offline_bolt_outlined, color: Colors.teal),
            title: const Text('Mode Offline', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
            subtitle: Text(cfg['offline_mode_ready'] == true ? 'Data Tersinkronisasi' : 'Koneksi Lemah'),
            trailing: Switch(value: cfg['offline_mode_ready'] == true, onChanged: (_) {}),
          ),
          const Divider(height: 1),
          ListTile(
            leading: const Icon(Icons.fingerprint, color: Colors.blue),
            title: const Text('Autentikasi Biometrik', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
            subtitle: const Text('Gunakan sidik jari untuk login cepat'),
            trailing: Switch(value: cfg['biometric_enabled'] == true, onChanged: (_) {}),
          ),
        ],
      ),
    );
  }

  ({Color color, IconData icon, String route}) _mapActionType(String actionType) {
    switch (actionType) {
      case 'ACTION_APPROVAL':
        return (color: Colors.blue.shade600, icon: Icons.gavel, route: RoutePaths.executive);
      case 'ACTION_SURAT':
        return (color: Colors.green.shade600, icon: Icons.mail, route: RoutePaths.profile);
      case 'ACTION_DASHBOARD':
        return (color: Colors.red.shade600, icon: Icons.dashboard, route: RoutePaths.executive);
      case 'ACTION_ASSESSMENT':
        return (color: Colors.orange.shade600, icon: Icons.assignment, route: RoutePaths.assessmentForm.replaceAll(':uuidInsiden', 'demo-uuid'));
      case 'ACTION_MAP':
        return (color: Colors.cyan.shade600, icon: Icons.map, route: RoutePaths.map);
      case 'ACTION_UPLOAD_EVIDENCE':
        return (color: Colors.green.shade600, icon: Icons.photo_camera, route: RoutePaths.profile);
      case 'ACTION_JOIN_MISSION':
        return (color: Colors.red.shade600, icon: Icons.volunteer_activism, route: RoutePaths.map);
      case 'ACTION_TRAINING':
        return (color: Colors.purple.shade600, icon: Icons.school, route: RoutePaths.profile);
      case 'ACTION_BADGE':
        return (color: Colors.amber.shade600, icon: Icons.emoji_events, route: RoutePaths.profile);
      case 'ACTION_VALIDATE_LAPORAN':
        return (color: Colors.green.shade600, icon: Icons.playlist_add_check, route: RoutePaths.reportValidation);
      default:
        return (color: Colors.grey, icon: Icons.help_outline, route: RoutePaths.profile);
    }
  }
}
