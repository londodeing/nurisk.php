import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import 'package:nurisk_mobile/core/storage/public/database_provider.dart';
import 'package:nurisk_mobile/core/storage/public/public_database.dart';
import 'package:nurisk_mobile/features/auth/presentation/notifiers/auth_state_provider.dart';
import 'package:drift/drift.dart' as drift;

class GovernanceState {
  final bool isLoading;
  final List<dynamic> pendingSurat;
  final List<dynamic> pendingPleno;
  final String? error;

  const GovernanceState({
    this.isLoading = false,
    this.pendingSurat = const [],
    this.pendingPleno = const [],
    this.error,
  });

  GovernanceState copyWith({
    bool? isLoading,
    List<dynamic>? pendingSurat,
    List<dynamic>? pendingPleno,
    String? error,
  }) {
    return GovernanceState(
      isLoading: isLoading ?? this.isLoading,
      pendingSurat: pendingSurat ?? this.pendingSurat,
      pendingPleno: pendingPleno ?? this.pendingPleno,
      error: error ?? this.error,
    );
  }
}

class GovernanceNotifier extends Notifier<GovernanceState> {
  @override
  GovernanceState build() {
    final authState = ref.watch(authStateProvider);
    if (authState.token != null && authState.activeScopeId != null) {
      Future.microtask(() => fetchPendingDecisions());
    }
    return const GovernanceState();
  }

  Future<void> fetchPendingDecisions() async {
    final authState = ref.read(authStateProvider);
    final db = ref.read(publicDatabaseProvider);
    
    if (authState.token == null || authState.activeScopeId == null) {
      state = state.copyWith(error: 'Sesi tidak valid, silakan login ulang.');
      return;
    }

    state = state.copyWith(isLoading: true, error: null);
    try {
      final dio = ref.read(authApiClientProvider);
      final res = await dio.get('governance/pending');

      if (res.statusCode == 200 && res.data['success'] == true) {
        final data = res.data['data'] ?? {};
        final surat = data['surat'] ?? [];
        final pleno = data['pleno'] ?? [];

        // Update local database cache as the source of truth if db is available
        if (db != null) {
          await db.transaction(() async {
            await db.delete(db.governanceSuratCache).go();
            await db.delete(db.governancePlenoCache).go();

            for (var s in surat) {
              await db.into(db.governanceSuratCache).insertOnConflictUpdate(
                GovernanceSuratCacheCompanion(
                  id: drift.Value(s['id'].toString()),
                  perihal: drift.Value(s['perihal']?.toString()),
                  pemohon: drift.Value(s['pemohon']?.toString()),
                  waktu: drift.Value(s['waktu']?.toString()),
                ),
              );
            }

            for (var p in pleno) {
              await db.into(db.governancePlenoCache).insertOnConflictUpdate(
                GovernancePlenoCacheCompanion(
                  id: drift.Value(p['id'].toString()),
                  judul: drift.Value(p['judul']?.toString()),
                  insiden: drift.Value(p['insiden']?.toString()),
                  waktu: drift.Value(p['waktu']?.toString()),
                ),
              );
            }
          });
        }

        state = state.copyWith(
          isLoading: false,
          pendingSurat: surat,
          pendingPleno: pleno,
        );
      } else {
        await _loadFromLocalCache();
        state = state.copyWith(
          isLoading: false,
          error: res.data['message'] ?? 'Gagal memuat persetujuan tertunda',
        );
      }
    } catch (e) {
      await _loadFromLocalCache();
      state = state.copyWith(
        isLoading: false,
        error: 'Terjadi kesalahan jaringan. Menampilkan data lokal (Offline).',
      );
    }
  }

  Future<void> _loadFromLocalCache() async {
    final db = ref.read(publicDatabaseProvider);
    if (db == null) return;

    final suratCache = await db.select(db.governanceSuratCache).get();
    final plenoCache = await db.select(db.governancePlenoCache).get();

    state = state.copyWith(
      pendingSurat: suratCache.map((s) => {
        'id': s.id,
        'perihal': s.perihal,
        'pemohon': s.pemohon,
        'waktu': s.waktu,
      }).toList(),
      pendingPleno: plenoCache.map((p) => {
        'id': p.id,
        'judul': p.judul,
        'insiden': p.insiden,
        'waktu': p.waktu,
      }).toList(),
    );
  }

  Future<bool> processDecision(String decisionId, String type, String action, {String? notes}) async {
    final authState = ref.read(authStateProvider);
    final db = ref.read(publicDatabaseProvider);
    if (authState.token == null) return false;

    // Tandai status lokal terlebih dahulu
    if (db != null) {
      if (type == 'surat') {
        await (db.update(db.governanceSuratCache)
              ..where((t) => t.id.equals(decisionId)))
            .write(GovernanceSuratCacheCompanion(
              statusLocal: drift.Value(action == 'approve' ? 'approved' : 'rejected'),
            ));
      } else {
        await (db.update(db.governancePlenoCache)
              ..where((t) => t.id.equals(decisionId)))
            .write(GovernancePlenoCacheCompanion(
              statusLocal: drift.Value(action == 'approve' ? 'approved' : 'rejected'),
            ));
      }
    }

    try {
      final dio = ref.read(authApiClientProvider);
      final res = await dio.post(
        'governance/process',
        data: {
          'id': decisionId,
          'type': type,
          'action': action,
          'notes': notes ?? '',
        },
      );

      if (res.statusCode == 200 && res.data['success'] == true) {
        // Hapus dari cache jika sukses sinkron ke server
        if (db != null) {
          if (type == 'surat') {
            await (db.delete(db.governanceSuratCache)..where((t) => t.id.equals(decisionId))).go();
          } else {
            await (db.delete(db.governancePlenoCache)..where((t) => t.id.equals(decisionId))).go();
          }
        }
        await fetchPendingDecisions(); // Refresh
        return true;
      }
      return false;
    } catch (e) {
      // Offline fallback: tetap kembalikan true agar UI merespons perubahan lokal
      await _loadFromLocalCache();
      return true;
    }
  }
}

final governanceProvider = NotifierProvider<GovernanceNotifier, GovernanceState>(() {
  return GovernanceNotifier();
});

