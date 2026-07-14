import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../notifiers/news_provider.dart';

class NewsDetailScreen extends ConsumerWidget {
  final String slug;

  const NewsDetailScreen({super.key, required this.slug});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailAsync = ref.watch(newsDetailProvider(slug));

    return Scaffold(
      appBar: AppBar(title: const Text('Detail Berita')),
      body: detailAsync.when(
        data: (news) {
          if (news == null) {
            return const Center(
              child: Text('Berita tidak ditemukan', style: TextStyle(color: Colors.grey)),
            );
          }
          
          return SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (news.imageUrl != null) ...[
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8.0),
                    child: Image.network(
                      news.imageUrl!,
                      width: double.infinity,
                      height: 200,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) => Container(
                        width: double.infinity,
                        height: 200,
                        color: Colors.grey[300],
                        child: const Icon(Icons.broken_image, size: 64, color: Colors.grey),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                ],
                Text(
                  news.title,
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.calendar_today, size: 14, color: Colors.grey),
                    const SizedBox(width: 4),
                    Text(
                      news.publishedAt.toLocal().toString().split(' ')[0], // Simple format
                      style: const TextStyle(color: Colors.grey, fontSize: 12),
                    ),
                    const Spacer(),
                    if (news.source != null) ...[
                      const Icon(Icons.source, size: 14, color: Colors.grey),
                      const SizedBox(width: 4),
                      Text(
                        news.source!,
                        style: const TextStyle(color: Colors.grey, fontSize: 12),
                      ),
                    ]
                  ],
                ),
                const SizedBox(height: 24),
                Text(
                  news.content,
                  style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                    height: 1.6,
                  ),
                ),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, _) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 16),
              const Text('Gagal memuat detail berita'),
              TextButton(
                onPressed: () => ref.invalidate(newsDetailProvider(slug)),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
