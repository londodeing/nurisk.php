import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import '../notifiers/news_provider.dart';
import '../widgets/news_card.dart';

class NewsListScreen extends ConsumerWidget {
  const NewsListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final newsAsync = ref.watch(newsListProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Berita & Artikel')),
      body: newsAsync.when(
        data: (news) {
          if (news.isEmpty) {
            return const Center(
              child: Text('Belum ada berita', style: TextStyle(color: Colors.grey)),
            );
          }
          return ListView.builder(
            padding: const EdgeInsets.symmetric(vertical: 8),
            itemCount: news.length,
            itemBuilder: (context, index) {
              final item = news[index];
              return NewsCard(
                news: item,
                onTap: () => ref.read(runtimeServicesProvider).navigation.push(
                  '/public/news/${item.slug}',
                ),
              );
            },
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, _) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 16),
              const Text('Gagal memuat berita'),
              TextButton(
                onPressed: () => ref.invalidate(newsListProvider),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
