import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/resource_entity.dart';
import '../../data/repositories/resource_repository_impl.dart';

class ResourceListNotifier extends AsyncNotifier<List<ResourceEntity>> {
  String? _category;

  @override
  FutureOr<List<ResourceEntity>> build() async {
    return ref.read(resourceRepositoryProvider).getResources(category: _category);
  }

  void setCategory(String? category) {
    _category = category;
    ref.invalidateSelf();
  }

  Future<void> fetchResources() async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      return ref.read(resourceRepositoryProvider).getResources(category: _category);
    });
  }
}

final resourceListProvider = AsyncNotifierProvider.autoDispose<ResourceListNotifier, List<ResourceEntity>>(
  ResourceListNotifier.new,
);
