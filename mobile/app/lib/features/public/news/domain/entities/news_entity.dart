class NewsEntity {
  final String id;
  final String title;
  final String slug;
  final String excerpt;
  final String content;
  final String? imageUrl;
  final String? source;
  final DateTime publishedAt;
  final bool isFeatured;

  const NewsEntity({
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
}
