import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/news_entity.dart';
import '../../data/repositories/news_repository_impl.dart';

class NewsListNotifier extends AsyncNotifier<List<NewsEntity>> {
  @override
  FutureOr<List<NewsEntity>> build() async {
    return ref.read(newsRepositoryProvider).getNews();
  }

  Future<void> fetchNews() async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      return ref.read(newsRepositoryProvider).getNews();
    });
  }
}

final newsListProvider = AsyncNotifierProvider.autoDispose<NewsListNotifier, List<NewsEntity>>(
  NewsListNotifier.new,
);

final newsDetailProvider = FutureProvider.autoDispose.family<NewsEntity?, String>((ref, arg) async {
  return ref.read(newsRepositoryProvider).getNewsDetail(arg);
});
