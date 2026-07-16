import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../notifiers/pleno_providers.dart';

class PlenoListScreen extends ConsumerStatefulWidget {
  final String uuidInsiden;

  const PlenoListScreen({super.key, required this.uuidInsiden});

  @override
  ConsumerState<PlenoListScreen> createState() => _PlenoListScreenState();
}

class _PlenoListScreenState extends ConsumerState<PlenoListScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(plenoListProvider.notifier).initialize(widget.uuidInsiden));
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(plenoListProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Daftar Rapat Pleno'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.read(plenoListProvider.notifier).fetchPlenos(),
          ),
        ],
      ),
      body: _buildBody(state, context),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/insiden/${widget.uuidInsiden}/pleno/create'),
        icon: const Icon(Icons.add),
        label: const Text('Pleno Baru'),
      ),
    );
  }

  Widget _buildBody(PlenoListState state, BuildContext context) {
    if (state.isLoading && state.plenos.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (state.error != null && state.plenos.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text('Terjadi kesalahan: ${state.error}', textAlign: TextAlign.center),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => ref.read(plenoListProvider.notifier).fetchPlenos(),
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (state.plenos.isEmpty) {
      return const Center(child: Text('Belum ada data rapat pleno.'));
    }

    return RefreshIndicator(
      onRefresh: () async {
        await ref.read(plenoListProvider.notifier).fetchPlenos();
      },
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: state.plenos.length,
        itemBuilder: (context, index) {
          final pleno = state.plenos[index];
          return Card(
            elevation: 2,
            margin: const EdgeInsets.only(bottom: 12),
            child: ListTile(
              title: Text(pleno.nomorPleno ?? 'Draf Pleno Baru'),
              subtitle: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Jenis: ${pleno.jenisPleno}'),
                  Text('Status: ${pleno.statusPleno.toUpperCase()}'),
                  if (pleno.waktuPleno != null)
                    Text('Waktu: ${pleno.waktuPleno.toString().split(' ')[0]}'),
                ],
              ),
              trailing: const Icon(Icons.chevron_right),
              isThreeLine: true,
              onTap: () {
                context.push('/insiden/${widget.uuidInsiden}/pleno/${pleno.idPleno}');
              },
            ),
          );
        },
      ),
    );
  }
}
