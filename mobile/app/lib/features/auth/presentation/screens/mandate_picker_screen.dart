import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import '../notifiers/auth_state_provider.dart';

class MandatePickerScreen extends ConsumerStatefulWidget {
  final String userId;
  final String userName;
  final List<dynamic> mandates;

  const MandatePickerScreen({
    Key? key,
    required this.userId,
    required this.userName,
    required this.mandates,
  }) : super(key: key);

  @override
  ConsumerState<MandatePickerScreen> createState() => _MandatePickerScreenState();
}

class _MandatePickerScreenState extends ConsumerState<MandatePickerScreen> {
  bool _isLoading = false;

  Future<void> _selectMandate(String mandateId) async {
    setState(() => _isLoading = true);

    try {
      final dio = ref.read(authApiClientProvider);
      final res = await dio.post('auth/mandate', data: {
        'mandate_id': mandateId,
      });

      if (res.statusCode == 200 && res.data['success'] == true) {
        if (!mounted) return;

        final data = res.data['data'];
        await ref.read(authStateProvider.notifier).setMandate(
          role: data['role'],
          scopeId: data['scope_id'].toString(),
          scopeType: data['scope_type'],
          jabatan: data['jabatan_name'],
        );

        if (!mounted) return;
        ref.read(runtimeServicesProvider).navigation.goHome();
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memilih mandat: ${res.data['message'] ?? 'Error'}')),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Terjadi kesalahan jaringan atau server.')),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pilih Mandat'),
        automaticallyImplyLeading: false,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Selamat datang, ${widget.userName}',
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Pilih kapasitas Anda untuk sesi login ini:',
                    style: TextStyle(color: Colors.grey),
                  ),
                  const SizedBox(height: 24),
                  Expanded(
                    child: ListView.builder(
                      itemCount: widget.mandates.length,
                      itemBuilder: (context, index) {
                        final mandate = widget.mandates[index];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          elevation: 2,
                          child: ListTile(
                            contentPadding: const EdgeInsets.all(16),
                            leading: const CircleAvatar(
                              backgroundColor: Colors.green,
                              child: Icon(Icons.badge, color: Colors.white),
                            ),
                            title: Text(
                              mandate['role'],
                              style: const TextStyle(fontWeight: FontWeight.bold),
                            ),
                            subtitle: Text('Wilayah: ${mandate['territory']}'),
                            trailing: const Icon(Icons.chevron_right),
                            onTap: () => _selectMandate(mandate['id']),
                          ),
                        );
                      },
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
