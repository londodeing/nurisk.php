import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/news_entity.dart';
import '../../domain/repositories/news_repository.dart';
import '../datasources/news_remote_datasource.dart';

final newsRepositoryProvider = Provider<NewsRepository>((ref) {
  return NewsRepositoryImpl(ref.read(newsRemoteDatasourceProvider));
});

class NewsRepositoryImpl implements NewsRepository {
  final NewsRemoteDatasource _datasource;

  NewsRepositoryImpl(this._datasource);

  @override
  Future<List<NewsEntity>> getNews({int page = 1, int perPage = 10}) async {
    final models = await _datasource.getNews(page: page, perPage: perPage);
    return models.map((m) => m.toEntity()).toList();
  }

  @override
  Future<NewsEntity?> getNewsDetail(String slug) async {
    final model = await _datasource.getNewsDetail(slug);
    return model?.toEntity();
  }
}
