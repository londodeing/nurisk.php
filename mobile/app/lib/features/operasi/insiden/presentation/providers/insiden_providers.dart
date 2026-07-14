import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/features/operasi/insiden/data/datasources/insiden_datasource.dart';
import 'package:nurisk_mobile/features/operasi/insiden/data/models/insiden_model.dart';

// ─── State: Daftar ────────────────────────────────────────
class InsidenListState {
  final bool isLoading;
  final List<InsidenModel> items;
  final String? error;
  final String? filterStatus;
  final String? filterPrioritas;

  const InsidenListState({
    this.isLoading = false,
    this.items = const [],
    this.error,
    this.filterStatus,
    this.filterPrioritas,
  });

  InsidenListState copyWith({
    bool? isLoading,
    List<InsidenModel>? items,
    String? error,
    String? filterStatus,
    String? filterPrioritas,
    bool clearError = false,
  }) {
    return InsidenListState(
      isLoading: isLoading ?? this.isLoading,
      items: items ?? this.items,
      error: clearError ? null : (error ?? this.error),
      filterStatus: filterStatus ?? this.filterStatus,
      filterPrioritas: filterPrioritas ?? this.filterPrioritas,
    );
  }
}

// ─── Notifier: Daftar ─────────────────────────────────────
class InsidenListNotifier extends Notifier<InsidenListState> {
  @override
  InsidenListState build() {
    Future.microtask(() => fetch());
    return const InsidenListState();
  }

  Future<void> fetch() async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final ds = ref.read(insidenDatasourceProvider);
      final items = await ds.getInsidenList(
        status: state.filterStatus,
        prioritas: state.filterPrioritas,
      );
      state = state.copyWith(isLoading: false, items: items);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  void setFilter({String? status, String? prioritas}) {
    state = state.copyWith(
      filterStatus: status,
      filterPrioritas: prioritas,
    );
    fetch();
  }

  void clearFilter() {
    state = const InsidenListState();
    fetch();
  }
}

final insidenListProvider = NotifierProvider<InsidenListNotifier, InsidenListState>(
  InsidenListNotifier.new,
);

// ─── State: Detail ────────────────────────────────────────
class InsidenDetailState {
  final bool isLoading;
  final InsidenModel? insiden;
  final String? error;

  const InsidenDetailState({this.isLoading = false, this.insiden, this.error});

  InsidenDetailState copyWith({
    bool? isLoading,
    InsidenModel? insiden,
    String? error,
    bool clearError = false,
  }) {
    return InsidenDetailState(
      isLoading: isLoading ?? this.isLoading,
      insiden: insiden ?? this.insiden,
      error: clearError ? null : (error ?? this.error),
    );
  }
}

class InsidenDetailNotifier extends AsyncNotifier<InsidenModel> {
  final int id;
  InsidenDetailNotifier(this.id);

  @override
  Future<InsidenModel> build() async {
    return _fetch();
  }

  Future<InsidenModel> _fetch() async {
    final ds = ref.read(insidenDatasourceProvider);
    return ds.getInsidenDetail(id);
  }

  Future<bool> ubahStatus(String statusBaru, {String? alasan}) async {
    try {
      final ds = ref.read(insidenDatasourceProvider);
      await ds.ubahStatus(id: id, statusBaru: statusBaru, alasan: alasan);
      ref.invalidateSelf();
      ref.invalidate(insidenListProvider);
      return true;
    } catch (e) {
      return false;
    }
  }

  Future<void> refresh() async {
    ref.invalidateSelf();
  }
}

/// Provider factory — create one per insiden ID
final insidenDetailFamily = <int, AsyncNotifierProvider<InsidenDetailNotifier, InsidenModel>>{};

AsyncNotifierProvider<InsidenDetailNotifier, InsidenModel> insidenDetailProvider(int id) {
  return insidenDetailFamily.putIfAbsent(
    id,
    () => AsyncNotifierProvider<InsidenDetailNotifier, InsidenModel>(
      () => InsidenDetailNotifier(id),
    ),
  );
}
