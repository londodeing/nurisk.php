import '../../domain/entities/resource_entity.dart';

class ResourceModel {
  final int id;
  final String assetCode;
  final String name;
  final String category;
  final String? subCategory;
  final String? owner;
  final String? territory;
  final String readiness;
  final Map<String, dynamic>? metadata;

  const ResourceModel({
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

  factory ResourceModel.fromJson(Map<String, dynamic> json) {
    return ResourceModel(
      id: json['id'] as int,
      assetCode: json['asset_code'] as String,
      name: json['name'] as String,
      category: json['category'] as String,
      subCategory: json['sub_category'] as String?,
      owner: json['owner'] as String?,
      territory: json['territory'] as String?,
      readiness: json['readiness'] as String,
      metadata: json['metadata'] as Map<String, dynamic>?,
    );
  }

  ResourceEntity toEntity() => ResourceEntity(
    id: id,
    assetCode: assetCode,
    name: name,
    category: category,
    subCategory: subCategory,
    owner: owner,
    territory: territory,
    readiness: readiness,
    metadata: metadata,
  );
}
