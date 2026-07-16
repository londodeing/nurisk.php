import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/models/pleno_model.dart';
import '../../data/repositories/pleno_repository.dart';

// ---------------------------------------------------------
// Pleno List Provider
// ---------------------------------------------------------
class PlenoListState {
  final bool isLoading;
  final List<PlenoModel> plenos;
  final String? error;
  final String? uuidInsiden;

  PlenoListState({
    this.isLoading = false,
    this.plenos = const [],
    this.error,
    this.uuidInsiden,
  });

  PlenoListState copyWith({
    bool? isLoading,
    List<PlenoModel>? plenos,
    String? error,
    String? uuidInsiden,
  }) {
    return PlenoListState(
      isLoading: isLoading ?? this.isLoading,
      plenos: plenos ?? this.plenos,
      error: error,
      uuidInsiden: uuidInsiden ?? this.uuidInsiden,
    );
  }
}

final plenoListProvider =
    NotifierProvider<PlenoListNotifier, PlenoListState>(PlenoListNotifier.new);

class PlenoListNotifier extends Notifier<PlenoListState> {
  @override
  PlenoListState build() {
    return PlenoListState();
  }

  Future<void> initialize(String uuidInsiden) async {
    state = state.copyWith(uuidInsiden: uuidInsiden);
    await fetchPlenos();
  }

  Future<void> fetchPlenos() async {
    if (state.uuidInsiden == null) return;
    state = state.copyWith(isLoading: true, error: null);
    try {
      final repository = ref.read(plenoRepositoryProvider);
      final list = await repository.fetchPlenoList(state.uuidInsiden!);
      state = state.copyWith(isLoading: false, plenos: list);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }
}

// ---------------------------------------------------------
// Pleno Detail Provider
// ---------------------------------------------------------
class PlenoDetailState {
  final bool isLoading;
  final PlenoModel? pleno;
  final String? error;
  final bool isSubmitting;
  final String? uuidInsiden;
  final int? idPleno;

  PlenoDetailState({
    this.isLoading = false,
    this.pleno,
    this.error,
    this.isSubmitting = false,
    this.uuidInsiden,
    this.idPleno,
  });

  PlenoDetailState copyWith({
    bool? isLoading,
    PlenoModel? pleno,
    String? error,
    bool? isSubmitting,
    String? uuidInsiden,
    int? idPleno,
  }) {
    return PlenoDetailState(
      isLoading: isLoading ?? this.isLoading,
      pleno: pleno ?? this.pleno,
      error: error,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      uuidInsiden: uuidInsiden ?? this.uuidInsiden,
      idPleno: idPleno ?? this.idPleno,
    );
  }
}

final plenoDetailProvider =
    NotifierProvider<PlenoDetailNotifier, PlenoDetailState>(PlenoDetailNotifier.new);

class PlenoDetailNotifier extends Notifier<PlenoDetailState> {
  @override
  PlenoDetailState build() {
    return PlenoDetailState();
  }

  Future<void> initialize(String uuidInsiden, int idPleno) async {
    state = state.copyWith(uuidInsiden: uuidInsiden, idPleno: idPleno);
    await fetchDetail();
  }

  Future<void> fetchDetail() async {
    if (state.uuidInsiden == null || state.idPleno == null) return;
    state = state.copyWith(isLoading: true, error: null);
    try {
      final repository = ref.read(plenoRepositoryProvider);
      final detail = await repository.fetchPlenoDetail(state.uuidInsiden!, state.idPleno!);
      state = state.copyWith(isLoading: false, pleno: detail);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<bool> finalizePleno() async {
    if (state.uuidInsiden == null || state.idPleno == null) return false;
    state = state.copyWith(isSubmitting: true, error: null);
    try {
      final repository = ref.read(plenoRepositoryProvider);
      await repository.finalizePleno(state.uuidInsiden!, state.idPleno!);
      await fetchDetail();
      return true;
    } catch (e) {
      state = state.copyWith(isSubmitting: false, error: e.toString());
      return false;
    }
  }

  Future<bool> addKeputusan(Map<String, dynamic> payload) async {
    if (state.uuidInsiden == null || state.idPleno == null) return false;
    state = state.copyWith(isSubmitting: true, error: null);
    try {
      final repository = ref.read(plenoRepositoryProvider);
      await repository.addKeputusan(state.uuidInsiden!, state.idPleno!, payload);
      await fetchDetail();
      return true;
    } catch (e) {
      state = state.copyWith(isSubmitting: false, error: e.toString());
      return false;
    }
  }

  Future<bool> removeKeputusan(int idKeputusan) async {
    if (state.uuidInsiden == null || state.idPleno == null) return false;
    state = state.copyWith(isSubmitting: true, error: null);
    try {
      final repository = ref.read(plenoRepositoryProvider);
      await repository.removeKeputusan(state.uuidInsiden!, state.idPleno!, idKeputusan);
      await fetchDetail();
      return true;
    } catch (e) {
      state = state.copyWith(isSubmitting: false, error: e.toString());
      return false;
    }
  }

  Future<bool> addPeserta(int idPengguna, String peran) async {
    if (state.uuidInsiden == null || state.idPleno == null) return false;
    state = state.copyWith(isSubmitting: true, error: null);
    try {
      final repository = ref.read(plenoRepositoryProvider);
      await repository.addPeserta(state.uuidInsiden!, state.idPleno!, {
        'id_pengguna': idPengguna,
        'peran_dalam_rapat': peran,
      });
      await fetchDetail();
      return true;
    } catch (e) {
      state = state.copyWith(isSubmitting: false, error: e.toString());
      return false;
    }
  }

  Future<bool> removePeserta(int idPeserta) async {
    if (state.uuidInsiden == null || state.idPleno == null) return false;
    state = state.copyWith(isSubmitting: true, error: null);
    try {
      final repository = ref.read(plenoRepositoryProvider);
      await repository.removePeserta(state.uuidInsiden!, state.idPleno!, idPeserta);
      await fetchDetail();
      return true;
    } catch (e) {
      state = state.copyWith(isSubmitting: false, error: e.toString());
      return false;
    }
  }
}

// Real users list provider fetching from backend
final plenoUsersProvider = FutureProvider<List<Map<String, dynamic>>>((ref) async {
  final repo = ref.read(plenoRepositoryProvider);
  return await repo.fetchUsers();
});
