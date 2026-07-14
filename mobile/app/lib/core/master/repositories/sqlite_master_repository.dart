import 'dart:io';
import 'package:drift/drift.dart';
import 'package:drift/native.dart';
import 'package:flutter/services.dart';
import 'package:path_provider/path_provider.dart';
import '../models/wilayah.dart';

/// User kosong untuk membuka NativeDatabase secara mandiri (tanpa GeneratedDatabase).
class _MasterDbUser implements QueryExecutorUser {
  @override
  int get schemaVersion => 1;

  @override
  Future<void> beforeOpen(QueryExecutor executor, OpeningDetails details) async {}
}

class SQLiteMasterRepository {
  static const String _assetPath = 'assets/master/wilayah/master.db';
  static const String _dbName = 'nurisk_master.db';

  NativeDatabase? _db;
  bool _initialized = false;

  /// Constructor produksi: memerlukan pemanggilan [init()] sebelum query.
  SQLiteMasterRepository();

  /// Constructor untuk test: langsung pakai executor (in-memory), tanpa rootBundle.
  SQLiteMasterRepository.withExecutor(NativeDatabase executor)
      : _db = executor {
    _initialized = true;
  }

  Future<void> init() async {
    if (_initialized) return;
    final dir = await getApplicationDocumentsDirectory();
    final dbFile = File('${dir.path}/$_dbName');

    if (!await dbFile.exists()) {
      final data = await rootBundle.load(_assetPath);
      await dbFile.writeAsBytes(data.buffer.asUint8List());
    }

    _db = NativeDatabase(dbFile);
    await _db!.ensureOpen(_MasterDbUser());
    _initialized = true;
  }

  QueryExecutor get _executor {
    if (_db == null) throw StateError('SQLiteMasterRepository not initialized');
    return _db!;
  }

  Future<List<Kabupaten>> getKabupaten() async {
    final result = await _executor.runSelect(
      'SELECT id_kab, nama_kab FROM kabupaten ORDER BY nama_kab',
      [],
    );
    return result.map((row) => Kabupaten(
      idKab: row['id_kab'] as String,
      namaKab: row['nama_kab'] as String,
    )).toList();
  }

  Future<List<Kecamatan>> getKecamatan(String idKab) async {
    final result = await _executor.runSelect(
      'SELECT id_kec, id_kab, nama_kec FROM kecamatan WHERE id_kab = ? ORDER BY nama_kec',
      [idKab],
    );
    return result.map((row) => Kecamatan(
      idKec: row['id_kec'] as String,
      idKab: row['id_kab'] as String,
      namaKec: row['nama_kec'] as String,
    )).toList();
  }

  Future<List<Desa>> getDesa(String idKec) async {
    final result = await _executor.runSelect(
      'SELECT id_desa, id_kec, nama_desa FROM desa WHERE id_kec = ? ORDER BY nama_desa',
      [idKec],
    );
    return result.map((row) => Desa(
      idDesa: row['id_desa'] as String,
      idKec: row['id_kec'] as String,
      namaDesa: row['nama_desa'] as String,
    )).toList();
  }

  void clearCache() {
    // SQLite master (Tier B) is bundled and read-only. No cache to clear.
  }

  Future<void> close() async {
    await _db?.close();
  }
}
