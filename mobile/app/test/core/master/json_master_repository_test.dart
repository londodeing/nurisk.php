import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:nurisk_mobile/core/master/models/wilayah.dart';
import 'package:nurisk_mobile/core/master/models/master_data.dart';
import 'package:nurisk_mobile/core/master/models/display.dart';
import 'package:nurisk_mobile/core/master/models/status.dart';
import 'package:nurisk_mobile/core/master/repositories/json_master_repository.dart';

void main() {
  late JsonMasterRepository repo;

  setUpAll(() {
    TestWidgetsFlutterBinding.ensureInitialized();
  });

  setUp(() {
    repo = JsonMasterRepository();
  });

  group('JsonMasterRepository — Tier A (immutable)', () {
    test('getJenisBencana returns parsed list with full fields', () async {
      final result = await repo.getJenisBencana();
      expect(result, isNotEmpty);
      expect(result.first, isA<JenisBencana>());
      expect(result.first.id, isA<int>());
      expect(result.first.nama, isNotEmpty);
      expect(result.first.slug, isNotEmpty);
    });

    test('getKeahlian returns list of Keahlian', () async {
      final result = await repo.getKeahlian();
      expect(result, isNotEmpty);
      expect(result.first, isA<Keahlian>());
    });

    test('getKlaster returns list of Klaster', () async {
      final result = await repo.getKlaster();
      expect(result, isNotEmpty);
      expect(result.first, isA<Klaster>());
    });

    test('getStatusInsiden returns ordered statuses', () async {
      final result = await repo.getStatusInsiden();
      expect(result.map((e) => e.id), contains('draft'));
      expect(result.map((e) => e.id), contains('selesai'));
    });

    test('getWarnaIndikator returns map keyed by status', () async {
      final result = await repo.getWarnaIndikator();
      expect(result, containsPair('draft', isA<WarnaIndikator>()));
      expect(result['draft']!.hex, isNotEmpty);
    });

    test('getWorkflow returns enum map', () async {
      final result = await repo.getWorkflow();
      expect(result, containsPair('status_insiden', isA<List<String>>()));
    });
  });

  group('JsonMasterRepository — caching (REVIEW 6)', () {
    test('second call returns cached parsed object (no re-parse)', () async {
      final first = await repo.getJenisBencana();
      final second = await repo.getJenisBencana();
      // Same instance from cache → identity equality
      expect(identical(first, second), isTrue);
    });

    test('clearCache forces re-read', () async {
      final first = await repo.getJenisBencana();
      repo.clearCache();
      final second = await repo.getJenisBencana();
      expect(identical(first, second), isFalse);
      expect(second, isNotEmpty);
    });
  });
}
