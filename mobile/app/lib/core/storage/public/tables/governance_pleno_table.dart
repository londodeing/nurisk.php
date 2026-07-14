import 'package:drift/drift.dart';

class GovernancePlenoCache extends Table {
  TextColumn get id => text()();
  TextColumn get judul => text().nullable()();
  TextColumn get insiden => text().nullable()();
  TextColumn get waktu => text().nullable()();
  TextColumn get statusLocal => text().withDefault(const Constant('pending'))(); // pending, approved, rejected

  @override
  Set<Column> get primaryKey => {id};
}
