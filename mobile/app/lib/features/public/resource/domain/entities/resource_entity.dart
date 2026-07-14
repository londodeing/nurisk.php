class ResourceEntity {
  final int id;
  final String assetCode;
  final String name;
  final String category;
  final String? subCategory;
  final String? owner;
  final String? territory;
  final String readiness;
  final Map<String, dynamic>? metadata;

  const ResourceEntity({
    required this.id,
    required this.assetCode,
    required this.name,
    required this.category,
    this.subCategory,
    this.owner,
    this.territory,
    required this.readiness,
    this.metadata,
  });
}
