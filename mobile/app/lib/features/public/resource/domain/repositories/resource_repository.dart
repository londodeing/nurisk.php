import '../entities/resource_entity.dart';

abstract class ResourceRepository {
  Future<List<ResourceEntity>> getResources({int page = 1, int perPage = 20, String? category});
}
