import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';

final trcQueueProvider = FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final dio = ref.watch(authApiClientProvider);
  final res = await dio.get('public/dashboard/trc/queue');
  if (res.statusCode == 200 && res.data['status'] == 'success') {
    return res.data['data'] as List<dynamic>;
  }
  throw Exception('Gagal memuat antrean TRC');
});

class TrcAssessmentQueueWidget extends ConsumerWidget {
  const TrcAssessmentQueueWidget({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final queueState = ref.watch(trcQueueProvider);

    return queueState.when(
      data: (queue) {
        if (queue.isEmpty) {
          return const Padding(
            padding: EdgeInsets.all(16.0),
            child: Card(
              child: Padding(
                padding: EdgeInsets.all(24.0),
                child: Center(
                  child: Text('Tidak ada antrean assessment saat ini.', style: TextStyle(color: Colors.grey)),
                ),
              ),
            ),
          );
        }

        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Padding(
                padding: EdgeInsets.only(bottom: 8.0),
                child: Text(
                  'Antrean Assessment (Tugas Aktif)',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ),
              ...queue.map((tugas) {
                return Card(
                  elevation: 2,
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    leading: const CircleAvatar(
                      backgroundColor: Colors.orange,
                      child: Icon(Icons.assignment, color: Colors.white),
                    ),
                    title: Text(tugas['jenis_bencana'] ?? 'Insiden Bencana', style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Kode: ${tugas['kode_kejadian']}'),
                        Text(tugas['deskripsi'] ?? '', maxLines: 2, overflow: TextOverflow.ellipsis),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.green.shade100,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            (tugas['status'] as String).toUpperCase(),
                            style: TextStyle(fontSize: 10, color: Colors.green.shade800, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ),
                    isThreeLine: true,
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () {
                      final uuidInsiden = tugas['uuid_insiden'];
                      if (uuidInsiden != null) {
                        ref.read(runtimeServicesProvider).navigation.push('/assessment/$uuidInsiden');
                      }
                    },
                  ),
                );
              }),
            ],
          ),
        );
      },
      loading: () => const Center(child: Padding(
        padding: EdgeInsets.all(24.0),
        child: CircularProgressIndicator(),
      )),
      error: (err, stack) => Padding(
        padding: const EdgeInsets.all(16.0),
        child: Card(
          color: Colors.red.shade50,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              children: [
                const Icon(Icons.error_outline, color: Colors.red),
                const SizedBox(width: 8),
                Expanded(child: Text('Gagal memuat tugas TRC: $err')),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
