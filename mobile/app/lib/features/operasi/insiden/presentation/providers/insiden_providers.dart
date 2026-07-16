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

class InsidenDetailNotifier extends Notifier<InsidenDetailState> {
  final String uuid;
  InsidenDetailNotifier(this.uuid);

  @override
  InsidenDetailState build() {
    _fetch();
    return const InsidenDetailState(isLoading: true);
  }

  Future<void> _fetch() async {
    state = const InsidenDetailState(isLoading: true);
    try {
      final ds = ref.read(insidenDatasourceProvider);
      final insiden = await ds.getInsidenDetail(uuid);
      state = InsidenDetailState(isLoading: false, insiden: insiden);
    } catch (e) {
      state = InsidenDetailState(isLoading: false, error: e.toString());
    }
  }

  Future<bool> ubahStatus(String statusBaru, {String? alasan}) async {
    try {
      final ds = ref.read(insidenDatasourceProvider);
      await ds.ubahStatus(uuid: uuid, statusBaru: statusBaru, alasan: alasan);
      _fetch();
      ref.invalidate(insidenListProvider);
      return true;
    } catch (e) {
      return false;
    }
  }

  Future<void> refresh() async {
    _fetch();
  }
}

/// Provider factory — create one per insiden UUID
final insidenDetailFamily = <String, NotifierProvider<InsidenDetailNotifier, InsidenDetailState>>{};

NotifierProvider<InsidenDetailNotifier, InsidenDetailState> insidenDetailProvider(String uuid) {
  return insidenDetailFamily.putIfAbsent(
    uuid,
    () => NotifierProvider<InsidenDetailNotifier, InsidenDetailState>(
      () => InsidenDetailNotifier(uuid),
    ),
  );
}
