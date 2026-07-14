import 'package:drift/drift.dart';

class GovernanceSuratCache extends Table {
  TextColumn get id => text()();
  TextColumn get perihal => text().nullable()();
  TextColumn get pemohon => text().nullable()();
  TextColumn get waktu => text().nullable()();
  TextColumn get statusLocal => text().withDefault(const Constant('pending'))(); // pending, approved, rejected

  @override
  Set<Column> get primaryKey => {id};
}
