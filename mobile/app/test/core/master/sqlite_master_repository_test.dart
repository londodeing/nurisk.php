import 'package:drift/drift.dart';
import 'package:drift/native.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:nurisk_mobile/core/master/models/wilayah.dart';
import 'package:nurisk_mobile/core/master/repositories/sqlite_master_repository.dart';

NativeDatabase _openSeeded() {
  final db = NativeDatabase.memory();
  return db;
}

Future<void> _seed(NativeDatabase db) async {
  await db.ensureOpen(_TestDbUser());
  await db.runCustom('CREATE TABLE kabupaten (id_kab TEXT PRIMARY KEY, nama_kab TEXT NOT NULL);');
  await db.runCustom('CREATE TABLE kecamatan (id_kec TEXT PRIMARY KEY, id_kab TEXT NOT NULL, nama_kec TEXT NOT NULL);');
  await db.runCustom('CREATE TABLE desa (id_desa TEXT PRIMARY KEY, id_kec TEXT NOT NULL, nama_desa TEXT NOT NULL);');
  await db.runCustom(
    "INSERT INTO kabupaten VALUES ('3301', 'Kabupaten Cilacap'), ('3319', 'Kabupaten Kudus');",
  );
  await db.runCustom(
    "INSERT INTO kecamatan VALUES ('330101', '3301', 'Kedungreja'), ('331901', '3319', 'Kota Kudus');",
  );
  await db.runCustom(
    "INSERT INTO desa VALUES ('3301012001', '330101', 'Tambakreja'), ('3319012001', '331901', 'Bae');",
  );
}

class _TestDbUser implements QueryExecutorUser {
  @override
  int get schemaVersion => 1;

  @override
  Future<void> beforeOpen(QueryExecutor executor, OpeningDetails details) async {}
}

void main() {
  group('SQLiteMasterRepository — Tier B (semi-static)', () {
    late SQLiteMasterRepository repo;

    setUp(() async {
      final db = _openSeeded();
      await _seed(db);
      repo = SQLiteMasterRepository.withExecutor(db);
    });

    test('getKabupaten returns all ordered by name', () async {
      final result = await repo.getKabupaten();
      expect(result.length, 2);
      expect(result.first, isA<Kabupaten>());
      // Ordered by nama_kab ascending: "Kabupaten Cilacap" < "Kabupaten Kudus"
      expect(result.first.namaKab, 'Kabupaten Cilacap');
    });

    test('getKecamatan filters by idKab', () async {
      final result = await repo.getKecamatan('3319');
      expect(result.length, 1);
      expect(result.first.idKec, '331901');
      expect(result.first.namaKec, 'Kota Kudus');
    });

    test('getDesa filters by idKec', () async {
      final result = await repo.getDesa('330101');
      expect(result.length, 1);
      expect(result.first.idDesa, '3301012001');
      expect(result.first.namaDesa, 'Tambakreja');
    });

    test('getKecamatan with unknown id returns empty', () async {
      final result = await repo.getKecamatan('9999');
      expect(result, isEmpty);
    });
  });
}
