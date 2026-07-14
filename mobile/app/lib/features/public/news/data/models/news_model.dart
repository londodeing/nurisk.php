import '../../domain/entities/news_entity.dart';

class NewsModel {
  final String id;
  final String title;
  final String slug;
  final String excerpt;
  final String content;
  final String? imageUrl;
  final String? source;
  final DateTime publishedAt;
  final bool isFeatured;

  const NewsModel({
    required this.id,
    required this.title,
    required this.slug,
    required this.excerpt,
    required this.content,
    this.imageUrl,
    this.source,
    required this.publishedAt,
    this.isFeatured = false,
  });

  factory NewsModel.fromJson(Map<String, dynamic> json) {
    return NewsModel(
      id: json['id'].toString(),
      title: json['title'] ?? json['judul'] ?? '',
      slug: json['slug'] ?? '',
      excerpt: json['excerpt'] ?? json['ringkasan'] ?? '',
      content: json['content'] ?? json['konten'] ?? '',
      imageUrl: json['image_url'] ?? json['gambar'],
      source: json['source'] ?? json['sumber'],
      publishedAt: DateTime.parse(json['published_at'] ?? json['created_at'] ?? DateTime.now().toIso8601String()),
      isFeatured: json['is_featured'] ?? json['unggulan'] ?? false,
    );
  }

  NewsEntity toEntity() => NewsEntity(
    id: id,
    title: title,
    slug: slug,
    excerpt: excerpt,
    content: content,
    imageUrl: imageUrl,
    source: source,
    publishedAt: publishedAt,
    isFeatured: isFeatured,
  );
}
