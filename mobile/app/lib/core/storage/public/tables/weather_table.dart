import 'package:drift/drift.dart';

class WeatherCache extends Table {
  TextColumn get id => text()();
  TextColumn get dataJson => text()();
  DateTimeColumn get updatedAt => dateTime()();
  
  @override
  Set<Column> get primaryKey => {id};
}
