import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:nurisk_mobile/core/master/models/wilayah.dart';
import 'package:nurisk_mobile/core/master/repositories/organization_repository.dart';

/// Fake Dio: sukses pada panggilan pertama, lalu melempar exception pada
/// panggilan berikutnya. Menguji fallback ke stale cache (REVIEW 8).
class FlakyDio with DioMixin implements Dio {
  FlakyDio() {
    options = BaseOptions();
  }
  int callCount = 0;

  @override
  Future<Response<T>> get<T>(
    String path, {
    Object? data,
    Map<String, dynamic>? queryParameters,
    Options? options,
    CancelToken? cancelToken,
    ProgressCallback? onReceiveProgress,
  }) async {
    callCount++;
    if (callCount > 1) {
      throw DioException(
        requestOptions: RequestOptions(path: path),
        type: DioExceptionType.connectionError,
      );
    }
    final resp = Response(
      data: {
        'data': [
          {'id': 1, 'nama': 'PCNU Kudus'},
          {'id': 2, 'nama': 'PCNU Semarang'},
        ],
      },
      statusCode: 200,
      requestOptions: RequestOptions(path: path),
    );
    return resp as Response<T>;
  }
}

/// Fake Dio yang selalu gagal — menguji propagasi error saat tidak ada cache.
class FailingDio with DioMixin implements Dio {
  FailingDio() {
    options = BaseOptions();
  }

  @override
  Future<Response<T>> get<T>(
    String path, {
    Object? data,
    Map<String, dynamic>? queryParameters,
    Options? options,
    CancelToken? cancelToken,
    ProgressCallback? onReceiveProgress,
  }) async {
    throw DioException(
      requestOptions: RequestOptions(path: path),
      type: DioExceptionType.connectionError,
    );
  }
}

/// Fake Dio yang SELALU sukses — menguji clearCache membuka request baru.
class HappyDio with DioMixin implements Dio {
  HappyDio() {
    options = BaseOptions();
  }
  int callCount = 0;

  @override
  Future<Response<T>> get<T>(
    String path, {
    Object? data,
    Map<String, dynamic>? queryParameters,
    Options? options,
    CancelToken? cancelToken,
    ProgressCallback? onReceiveProgress,
  }) async {
    callCount++;
    final resp = Response(
      data: {
        'data': [
          {'id': 1, 'nama': 'PCNU Kudus'},
        ],
      },
      statusCode: 200,
      requestOptions: RequestOptions(path: path),
    );
    return resp as Response<T>;
  }
}

void main() {
  group('OrganizationRepository — Tier C (organizational, TTL 24h)', () {
    test('getPcnuList fetches from API and parses', () async {
      final repo = OrganizationRepository(FlakyDio());
      final result = await repo.getPcnuList();
      expect(result, isA<List<Pcnu>>());
      expect(result.length, 2);
      expect(result.first, isA<Pcnu>());
      expect(result.first.nama, 'PCNU Kudus');
    });

    test('getPcnuList caches within TTL (only one network call)', () async {
      final dio = FlakyDio();
      final repo = OrganizationRepository(dio);
      await repo.getPcnuList();
      await repo.getPcnuList();
      expect(dio.callCount, 1);
    });

    test('REVIEW 8: clearCache invalidates TTL cache', () async {
      final dio = HappyDio();
      final repo = OrganizationRepository(dio);
      await repo.getPcnuList(); // call #1 → success + cache
      repo.clearCache();
      await repo.getPcnuList(); // cache kosong → request baru (call #2)
      expect(dio.callCount, 2);
    });

    test('REVIEW 8: on API failure with existing cache, returns stale cache', () async {
      final dio = FlakyDio();
      final repo = OrganizationRepository(dio);
      final first = await repo.getPcnuList(); // call #1 → sukses + cache
      final second = await repo.getPcnuList(); // call #2 → gagal → fallback cache
      expect(second, equals(first));
      expect(second.length, 2);
    });

    test('REVIEW 8: on API failure with NO cache, exception propagates', () async {
      final repo = OrganizationRepository(FailingDio());
      expect(() => repo.getPcnuList(), throwsA(isA<DioException>()));
    });
  });
}
