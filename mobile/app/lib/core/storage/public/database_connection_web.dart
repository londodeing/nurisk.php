import 'package:drift/drift.dart';
import 'package:drift/web.dart';

Future<QueryExecutor> connect() async {
  return WebDatabase.withStorage(DriftWebStorage.volatile());
}
