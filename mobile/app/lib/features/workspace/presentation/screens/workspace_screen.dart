import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/router/app_router.dart';
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
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text('Akun & Pusat Komando'),
        backgroundColor: Colors.green.shade700,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () {
              ref.read(authProvider.notifier).logout();
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              ref.invalidate(workspaceProvider);
            },
          ),
        ],
      ),
      body: workspaceAsync.when(
        data: (data) {
          final profil = data.profil;
          
          if (profil == null) {
            return _buildUnauthenticatedView(context, ref);
          }

          final userName = profil['nama_lengkap'] ?? profil['no_hp'] ?? 'Pengguna';
          
          return RefreshIndicator(
            onRefresh: () async {
              ref.invalidate(workspaceProvider);
              await ref.read(workspaceProvider.future);
            },
            child: ListView(
              padding: const EdgeInsets.all(16.0),
              children: [
                // Profile Section
                Card(
                  elevation: 2,
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            CircleAvatar(
                              radius: 30,
                              backgroundColor: Colors.green.shade100,
                              child: Icon(Icons.person, size: 36, color: Colors.green.shade700),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    userName,
                                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                                  ),
                                  Text(
                                    profil['nama_peran'] ?? 'Guest',
                                    style: TextStyle(color: Colors.grey.shade600),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        
                        // Toggle Availability
                        if (profil['nama_peran'] != 'publik' && profil['status_akun'] == 'aktif') ...[
                          const SizedBox(height: 16),
                          Consumer(
                            builder: (context, ref, child) {
                              final isTersedia = (profil['is_tersedia'] == 1 || profil['is_tersedia'] == true);
                              return InkWell(
                                onTap: () async {
                                  if (isTersedia) {
                                    final confirm = await showDialog<bool>(
                                      context: context,
                                      builder: (context) => AlertDialog(
                                        title: const Text('Tandai Tidak Tersedia?'),
                                        content: const Text('Status Anda akan berubah menjadi tidak tersedia untuk penugasan.'),
                                        actions: [
                                          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
                                          ElevatedButton(onPressed: () => Navigator.pop(context, true), child: const Text('Ya')),
                                        ],
                                      ),
                                    );
                                    if (confirm != true) return;
                                  }
                                  ref.read(workspaceProvider.notifier).toggleAvailability();
                                },
                                borderRadius: BorderRadius.circular(24),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                                  decoration: BoxDecoration(
                                    color: isTersedia ? Colors.green.shade100 : Colors.grey.shade200,
                                    borderRadius: BorderRadius.circular(24),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Container(
                                        width: 10,
                                        height: 10,
                                        decoration: BoxDecoration(
                                          shape: BoxShape.circle,
                                          color: isTersedia ? Colors.green.shade700 : Colors.grey.shade500,
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Text(
                                        isTersedia ? 'Tersedia' : 'Tidak Tersedia',
                                        style: TextStyle(
                                          fontWeight: FontWeight.bold,
                                          color: isTersedia ? Colors.green.shade700 : Colors.grey.shade700,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
                        ],
                        
                        if (data.jabatanAktif != null) ...[
                          const Divider(height: 24),
                          Text(
                            'Jabatan Aktif',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey.shade600),
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              const Icon(Icons.star, color: Colors.orange, size: 20),
                              const SizedBox(width: 8),
                              Text(
                                data.jabatanAktif!['nama_jabatan'] ?? '',
                                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                        ],
                        
                        if (data.keahlian.isNotEmpty) ...[
                          const Divider(height: 24),
                          Text(
                            'Keahlian (${data.keahlian.length})',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey.shade600),
                          ),
                          const SizedBox(height: 8),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: data.keahlian.map((k) => Chip(
                              label: Text(k['nama_keahlian'] ?? '', style: const TextStyle(fontSize: 12)),
                              backgroundColor: Colors.green.shade50,
                              side: BorderSide(color: Colors.green.shade200),
                            )).toList(),
                          ),
                        ]
                      ],
                    ),
                  ),
                ),
                if (profil['nama_peran'] != 'publik') ...[
                  if (profil['nama_peran'] == 'pwnu' || profil['nama_peran'] == 'pcnu' || profil['nama_peran'] == 'operator' || profil['nama_peran'] == 'admin')
                    Card(
                      margin: const EdgeInsets.only(top: 12, left: 16, right: 16),
                      elevation: 2,
                      color: Colors.red.shade50,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      child: ListTile(
                        leading: Icon(Icons.playlist_add_check_circle, color: Colors.red.shade700, size: 32),
                        title: Text(
                          'Validasi Laporan Publik',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.red.shade800),
                        ),
                        subtitle: Text(
                          'Tinjau laporan kejadian dari masyarakat',
                          style: TextStyle(fontSize: 12, color: Colors.red.shade600),
                        ),
                        trailing: Icon(Icons.chevron_right, color: Colors.red.shade700),
                        onTap: () {
                          context.push(RoutePaths.reportValidation);
                        },
                      ),
                    ),

                    // Insiden Card
                    Card(
                      margin: const EdgeInsets.only(top: 12, left: 16, right: 16),
                      elevation: 2,
                      color: const Color(0xFFF0FDF4),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      child: ListTile(
                        leading: const Icon(Icons.crisis_alert_rounded, color: Color(0xFF166534), size: 32),
                        title: const Text(
                          'Insiden Operasional',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF166534)),
                        ),
                        subtitle: const Text(
                          'Pantau dan kelola insiden bencana',
                          style: TextStyle(fontSize: 12, color: Color(0xFF15803D)),
                        ),
                        trailing: const Icon(Icons.chevron_right, color: Color(0xFF166534)),
                        onTap: () {
                          context.push(RoutePaths.insidenList);
                        },
                      ),
                    ),

                  if (data.commandCenter != null)
                    CommandCenterCard(
                      commandCenter: data.commandCenter,
                      alertInsiden: data.alertInsiden,
                    ),
                ],

                const SizedBox(height: 32),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 16),
              Text('Terjadi Kesalahan:\n$err', textAlign: TextAlign.center),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.invalidate(workspaceProvider),
                child: const Text('Coba Lagi'),
              ),
              const SizedBox(height: 16),
              TextButton(
                onPressed: () => ref.read(authProvider.notifier).logout(),
                child: const Text('Logout', style: TextStyle(color: Colors.red)),
              )
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildUnauthenticatedView(BuildContext context, WidgetRef ref) {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Card(
            color: Colors.green.shade700,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            child: Padding(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                children: [
                  const Icon(Icons.shield_outlined, size: 64, color: Colors.white),
                  const SizedBox(height: 16),
                  const Text(
                    'Anda belum login',
                    style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Silakan login untuk mengakses fitur pusat komando dan operasional NURISK.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.white70, fontSize: 14),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: Colors.green.shade700,
                      minimumSize: const Size(double.infinity, 48),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    onPressed: () => GoRouter.of(context).push('/auth/login'),
                    child: const Text('Masuk ke NURISK', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                  const SizedBox(height: 12),
                  TextButton(
                    style: TextButton.styleFrom(
                      foregroundColor: Colors.white,
                      minimumSize: const Size(double.infinity, 48),
                    ),
                    onPressed: () => GoRouter.of(context).push('/auth/register'),
                    child: const Text('Belum punya akun? Daftar'),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
