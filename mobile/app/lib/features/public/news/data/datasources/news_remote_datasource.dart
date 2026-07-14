import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';
import '../models/news_model.dart';

final newsRemoteDatasourceProvider = Provider<NewsRemoteDatasource>((ref) {
  return NewsRemoteDatasource(ref.read(publicApiClientProvider));
});

class NewsRemoteDatasource {
  final Dio _dio;

  NewsRemoteDatasource(this._dio);

  Future<List<NewsModel>> getNews({int page = 1, int perPage = 10}) async {
    try {
      final res = await _dio.get('public/news', queryParameters: {
        'page': page,
        'per_page': perPage,
      });
      final data = res.data['data'] as List<dynamic>? ?? [];
      return data.map((e) => NewsModel.fromJson(e as Map<String, dynamic>)).toList();
    } catch (e) {
      throw Exception('Gagal memuat berita: $e');
    }
  }

  Future<NewsModel?> getNewsDetail(String slug) async {
    try {
      final res = await _dio.get('public/news/$slug');
      if (res.statusCode == 200 && res.data['data'] != null) {
        return NewsModel.fromJson(res.data['data'] as Map<String, dynamic>);
      }
      return null;
    } catch (e) {
      throw Exception('Gagal memuat detail berita: $e');
    }
  }
}
