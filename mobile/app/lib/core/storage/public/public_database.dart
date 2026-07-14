import 'package:drift/drift.dart';
import 'tables/weather_table.dart';
import 'tables/incident_table.dart';
import 'tables/warning_table.dart';
import 'tables/dashboard_table.dart';
import 'tables/governance_surat_table.dart';
import 'tables/governance_pleno_table.dart';

part 'public_database.g.dart';

@DriftDatabase(tables: [WeatherCache, IncidentCache, WarningCache, DashboardKPICache, GovernanceSuratCache, GovernancePlenoCache])
class PublicDatabase extends _$PublicDatabase {
  PublicDatabase(super.e);

  @override
  int get schemaVersion => 2;
}

