import 'package:drift/drift.dart';
import 'package:drift/native.dart';
import 'package:nurisk_mobile/core/master/repositories/sqlite_master_repository.dart';

/// User kosong untuk membuka NativeDatabase di test.
class _TestDbUser implements QueryExecutorUser {
  @override
  int get schemaVersion => 1;

  @override
  Future<void> beforeOpen(QueryExecutor executor, OpeningDetails details) async {}
}

/// Helper test: membuat SQLiteMasterRepository in-memory yang sudah di-seed
/// dengan minimal 1 kabupaten agar test delegation Tier B bisa berjalan.
class MemorySQLiteMasterRepository {
  static Future<SQLiteMasterRepository> seeded() async {
    final db = NativeDatabase.memory();
    await db.ensureOpen(_TestDbUser());
    await db.runCustom('CREATE TABLE kabupaten (id_kab TEXT PRIMARY KEY, nama_kab TEXT NOT NULL);');
    await db.runCustom('CREATE TABLE kecamatan (id_kec TEXT PRIMARY KEY, id_kab TEXT NOT NULL, nama_kec TEXT NOT NULL);');
    await db.runCustom('CREATE TABLE desa (id_desa TEXT PRIMARY KEY, id_kec TEXT NOT NULL, nama_desa TEXT NOT NULL);');
    await db.runCustom(
      "INSERT INTO kabupaten VALUES ('3301', 'Kabupaten Cilacap');",
    );
    return SQLiteMasterRepository.withExecutor(db);
  }
}
