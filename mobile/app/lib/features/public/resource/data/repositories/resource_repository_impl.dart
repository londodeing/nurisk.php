import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/resource_entity.dart';
import '../../domain/repositories/resource_repository.dart';
import '../datasources/resource_remote_datasource.dart';

final resourceRepositoryProvider = Provider<ResourceRepository>((ref) {
  return ResourceRepositoryImpl(ref.read(resourceRemoteDatasourceProvider));
});

class ResourceRepositoryImpl implements ResourceRepository {
  final ResourceRemoteDatasource _datasource;

  ResourceRepositoryImpl(this._datasource);

  @override
  Future<List<ResourceEntity>> getResources({int page = 1, int perPage = 20, String? category}) async {
    final models = await _datasource.getResources(page: page, perPage: perPage, category: category);
    return models.map((m) => m.toEntity()).toList();
  }
}
