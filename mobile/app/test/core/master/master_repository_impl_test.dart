import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:dio/dio.dart';
import 'package:nurisk_mobile/core/master/master_repository.dart';
import 'package:nurisk_mobile/core/master/master_repository_impl.dart';
import 'package:nurisk_mobile/core/master/models/wilayah.dart';
import 'package:nurisk_mobile/core/master/repositories/json_master_repository.dart';
import 'package:nurisk_mobile/core/master/repositories/organization_repository.dart';
import 'package:nurisk_mobile/core/master/repositories/sqlite_master_repository.dart';
import 'package:drift/native.dart';

import '../../helpers/memory_sqlite_master_repository.dart';

void main() {
  group('MasterRepositoryImpl — delegation & independence (REVIEW 2)', () {
    late MasterRepository repo;

    setUpAll(() {
      TestWidgetsFlutterBinding.ensureInitialized();
    });

    setUp(() async {
      // JsonMasterRepository membaca asset asli via rootBundle (binding di-init di test runner)
      final jsonRepo = JsonMasterRepository();
      final sqliteRepo = await MemorySQLiteMasterRepository.seeded();
      final orgRepo = OrganizationRepository(_FakeAlwaysEmptyDio());
      repo = MasterRepositoryImpl(
        jsonRepo: jsonRepo,
        sqliteRepo: sqliteRepo,
        orgRepo: orgRepo,
      );
    });

    test('getJenisBencana delegates to JSON repo (Tier A)', () async {
      final result = await repo.getJenisBencana();
      expect(result, isNotEmpty);
      expect(result.first, isA<JenisBencana>());
    });

    test('getKabupaten delegates to SQLite repo (Tier B)', () async {
      final result = await repo.getKabupaten();
      expect(result, isNotEmpty);
      expect(result.first, isA<Kabupaten>());
    });

    test('clearCache does not throw', () {
      expect(() => repo.clearCache(), returnsNormally);
    });

    test('MasterRepositoryImpl implements MasterRepository interface', () {
      expect(repo, isA<MasterRepository>());
    });
  });
}

class _FakeAlwaysEmptyDio with DioMixin implements Dio {
  _FakeAlwaysEmptyDio() {
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
    final resp = Response(
      data: {'data': []},
      statusCode: 200,
      requestOptions: RequestOptions(path: path),
    );
    return resp as Response<T>;
  }
}
