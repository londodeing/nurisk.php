import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'database_connection.dart'
    if (dart.library.js_util) 'database_connection_web.dart'
    if (dart.library.io) 'database_connection_native.dart' as impl;
import 'public_database.dart';

PublicDatabase? _dbInstance;

final publicDatabaseProvider = Provider<PublicDatabase?>((ref) => _dbInstance);

Future<PublicDatabase> createPublicDatabase() async {
  if (_dbInstance != null) return _dbInstance!;
  final connection = await impl.connect();
  _dbInstance = PublicDatabase(connection);
  return _dbInstance!;
}
