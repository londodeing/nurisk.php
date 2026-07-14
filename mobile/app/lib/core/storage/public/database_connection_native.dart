import 'dart:io';
import 'package:drift/drift.dart';
import 'package:drift/native.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

Future<QueryExecutor> connect() async {
  final dir = await getApplicationDocumentsDirectory();
  final dbPath = p.join(dir.path, 'nurisk_public.db');
  return NativeDatabase(File(dbPath));
}
