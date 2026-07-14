import '../entities/news_entity.dart';

abstract class NewsRepository {
  Future<List<NewsEntity>> getNews({int page = 1, int perPage = 10});
  Future<NewsEntity?> getNewsDetail(String slug);
}
