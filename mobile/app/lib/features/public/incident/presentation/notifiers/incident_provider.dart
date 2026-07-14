import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import '../../domain/entities/incident_entity.dart';
import '../../data/repositories/incident_repository_impl.dart';

// Represents the state of the paginated feed
class IncidentFeedState {
  final List<IncidentEntity> incidents;
  final int currentPage;
  final bool isLastPage;
  final bool isLoadingMore;
  final String? loadMoreError;

  IncidentFeedState({
    this.incidents = const [],
    this.currentPage = 1,
    this.isLastPage = false,
    this.isLoadingMore = false,
    this.loadMoreError,
  });

  IncidentFeedState copyWith({
    List<IncidentEntity>? incidents,
    int? currentPage,
    bool? isLastPage,
    bool? isLoadingMore,
    String? loadMoreError,
  }) {
    return IncidentFeedState(
      incidents: incidents ?? this.incidents,
      currentPage: currentPage ?? this.currentPage,
      isLastPage: isLastPage ?? this.isLastPage,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
      loadMoreError: loadMoreError, // can be null to clear error
    );
  }
}

final incidentFeedProvider = AsyncNotifierProvider<IncidentNotifier, IncidentFeedState>(IncidentNotifier.new);

class IncidentNotifier extends AsyncNotifier<IncidentFeedState> {
  static const int _limit = 10;

  @override
  Future<IncidentFeedState> build() async {
    final repository = ref.read(incidentRepositoryProvider);
    final initialData = await repository.getLatestIncidents(page: 1, limit: _limit);
    
    return IncidentFeedState(
      incidents: initialData,
      currentPage: 1,
      isLastPage: initialData.length < _limit,
    );
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      final repository = ref.read(incidentRepositoryProvider);
      final refreshedData = await repository.getLatestIncidents(page: 1, limit: _limit);
      return IncidentFeedState(
        incidents: refreshedData,
        currentPage: 1,
        isLastPage: refreshedData.length < _limit,
      );
    });
  }

  Future<void> loadMore() async {
    if (state.value == null || state.value!.isLastPage || state.value!.isLoadingMore) {
      return;
    }

    final currentState = state.value!;
    
    // Set loading more state
    state = AsyncValue.data(currentState.copyWith(isLoadingMore: true, loadMoreError: null));

    try {
      final repository = ref.read(incidentRepositoryProvider);
      final nextPage = currentState.currentPage + 1;
      
      final newData = await repository.getLatestIncidents(page: nextPage, limit: _limit);
      
      state = AsyncValue.data(currentState.copyWith(
        incidents: [...currentState.incidents, ...newData],
        currentPage: nextPage,
        isLastPage: newData.length < _limit,
        isLoadingMore: false,
      ));
    } catch (e) {
      // Revert loading state and set error
      state = AsyncValue.data(currentState.copyWith(
        isLoadingMore: false,
        loadMoreError: DioExceptionMapper.toUserMessage(e),
      ));
    }
  }
}
