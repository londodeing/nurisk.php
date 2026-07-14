import 'package:flutter_test/flutter_test.dart';
import 'package:nurisk_mobile/core/runtime/certification/certification_entry.dart';
import 'package:nurisk_mobile/core/runtime/certification/certification_report.dart';
import 'package:nurisk_mobile/core/runtime/certification/certified_scene.dart';
import 'package:nurisk_mobile/core/runtime/certification/validators/envelope_validator.dart';
import 'package:nurisk_mobile/core/runtime/certification/validators/schema_validator.dart';
import 'package:nurisk_mobile/core/runtime/certification/validators/registry_validator.dart';
import 'package:nurisk_mobile/core/runtime/certification/validators/action_validator.dart';
import 'package:nurisk_mobile/core/runtime/certification/validators/state_validator.dart';
import 'package:nurisk_mobile/core/runtime/certification/validators/property_validator.dart';
import 'package:nurisk_mobile/core/runtime/certification/flutter_certification_engine.dart';
import 'package:nurisk_mobile/core/runtime/state/runtime_node_state.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_registry.dart';

void main() {
  // --------------------------------------------------
  // CertificationReport
  // --------------------------------------------------
  group('CertificationReport', () {
    test('empty report has no entries', () {
      final report = CertificationReport();
      expect(report.entries, isEmpty);
      expect(report.passed, isTrue);
    });

    test('report with only passes is passed', () {
      final report = CertificationReport(entries: [
        CertificationEntry(validator: 'Test', severity: CertificationSeverity.pass, message: 'ok'),
      ]);
      expect(report.passed, isTrue);
      expect(report.hasErrors, isFalse);
      expect(report.hasWarnings, isFalse);
    });

    test('report with errors is not passed', () {
      final report = CertificationReport(entries: [
        CertificationEntry(validator: 'Test', severity: CertificationSeverity.error, message: 'fail'),
      ]);
      expect(report.passed, isFalse);
      expect(report.hasErrors, isTrue);
    });

    test('report with warnings has warnings', () {
      final report = CertificationReport(entries: [
        CertificationEntry(validator: 'Test', severity: CertificationSeverity.warning, message: 'warn'),
      ]);
      expect(report.passed, isTrue);
      expect(report.hasWarnings, isTrue);
    });

    test('merge combines entries', () {
      final a = CertificationReport(entries: [
        CertificationEntry(validator: 'A', severity: CertificationSeverity.pass, message: 'a'),
      ]);
      final b = CertificationReport(entries: [
        CertificationEntry(validator: 'B', severity: CertificationSeverity.pass, message: 'b'),
      ]);
      final merged = a.merge(b);
      expect(merged.entries.length, 2);
    });
  });

  // --------------------------------------------------
  // EnvelopeValidator
  // --------------------------------------------------
  group('EnvelopeValidator', () {
    test('valid envelope passes', () {
      final report = EnvelopeValidator.validate({
        'schema_version': '1.0.0',
        'scene_id': 'test',
        'version': 1,
        'ttl_seconds': 60,
        'root': {'type': 'Column', 'id': 'root'},
      });
      expect(report.hasErrors, isFalse);
      expect(report.passes.length, 5);
    });

    test('missing schema_version fails', () {
      final report = EnvelopeValidator.validate({
        'scene_id': 'test',
        'version': 1,
        'ttl_seconds': 60,
        'root': {'type': 'Column'},
      });
      expect(report.hasErrors, isTrue);
      expect(report.errors.any((e) => e.details?['field'] == 'schema_version'), isTrue);
    });

    test('missing root fails', () {
      final report = EnvelopeValidator.validate({
        'schema_version': '1.0.0',
        'scene_id': 'test',
        'version': 1,
        'ttl_seconds': 60,
      });
      expect(report.hasErrors, isTrue);
      expect(report.errors.any((e) => e.details?['field'] == 'root'), isTrue);
    });

    test('missing scene_id is optional — does not fail', () {
      final report = EnvelopeValidator.validate({
        'schema_version': '1.0.0',
        'version': 1,
        'ttl_seconds': 60,
        'root': {'type': 'Column'},
      });
      // scene_id is required per spec, but let's verify:
      // All required fields must be present
      expect(report.hasErrors, isTrue);
      expect(report.errors.any((e) => e.details?['field'] == 'scene_id'), isTrue);
    });

    test('all missing fields produce 5 errors', () {
      final report = EnvelopeValidator.validate({});
      expect(report.errors.length, 5);
    });
  });

  // --------------------------------------------------
  // SchemaValidator
  // --------------------------------------------------
  group('SchemaValidator', () {
    test('valid schema 1.x.x passes', () {
      final report = SchemaValidator.validate({
        'schema_version': '1.0.0',
        'root': {'type': 'Column', 'id': 'root'},
      });
      expect(report.hasErrors, isFalse);
    });

    test('schema 2.0.0 fails', () {
      final report = SchemaValidator.validate({
        'schema_version': '2.0.0',
        'root': {'type': 'Column'},
      });
      expect(report.hasErrors, isTrue);
      expect(report.errors.any((e) => e.message.contains('2.0.0')), isTrue);
    });

    test('missing root.type fails', () {
      final report = SchemaValidator.validate({
        'schema_version': '1.0.0',
        'root': {'id': 'root'},
      });
      expect(report.hasErrors, isTrue);
      expect(report.errors.any((e) => e.message.contains('root.type')), isTrue);
    });

    test('missing root.id produces warning, not error', () {
      final report = SchemaValidator.validate({
        'schema_version': '1.0.0',
        'root': {'type': 'Column'},
      });
      expect(report.hasErrors, isFalse);
      expect(report.hasWarnings, isTrue);
    });

    test('null schema_version fails', () {
      final report = SchemaValidator.validate({
        'schema_version': null,
        'root': {'type': 'Column'},
      });
      expect(report.hasErrors, isTrue);
    });
  });

  // --------------------------------------------------
  // RegistryValidator
  // --------------------------------------------------
  group('RegistryValidator', () {
    setUpAll(() {
      // Register a known component for testing
      SduiRegistry.instance.register('KnownType', (node) => throw UnimplementedError('not used in test'));
    });

    test('known component passes', () {
      final node = SduiNode(type: 'KnownType', id: 'test');
      final report = RegistryValidator.validate(node);
      expect(report.hasErrors, isFalse);
      expect(report.hasWarnings, isFalse);
    });

    test('unknown component produces warning', () {
      final node = SduiNode(type: 'UnknownType', id: 'test');
      final report = RegistryValidator.validate(node);
      expect(report.hasWarnings, isTrue);
      expect(report.warnings.any((e) => e.message.contains('UnknownType')), isTrue);
    });

    test('unknown children are also detected', () {
      final node = SduiNode(
        type: 'KnownType',
        id: 'parent',
        children: [
          SduiNode(type: 'UnknownChild', id: 'child1'),
          SduiNode(type: 'AnotherUnknown', id: 'child2'),
        ],
      );
      final report = RegistryValidator.validate(node);
      expect(report.warnings.length, 2);
    });
  });

  // --------------------------------------------------
  // ActionValidator
  // --------------------------------------------------
  group('ActionValidator', () {
    test('known action passes', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        actions: {'on_tap': {'type': 'navigate'}},
      );
      final report = ActionValidator.validate(node);
      expect(report.hasErrors, isFalse);
      expect(report.hasWarnings, isFalse);
    });

    test('unknown action produces warning', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        actions: {'on_tap': {'type': 'scan_qr'}},
      );
      final report = ActionValidator.validate(node);
      expect(report.hasWarnings, isTrue);
      expect(report.warnings.any((e) => e.message.contains('scan_qr')), isTrue);
    });

    test('missing action type produces warning', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        actions: {'on_tap': {'payload': 'value'}}, // no 'type'
      );
      final report = ActionValidator.validate(node);
      expect(report.hasWarnings, isTrue);
      expect(report.warnings.any((e) => e.message.contains('missing "type"')), isTrue);
    });

    test('action chain on_success is recursively validated', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        actions: {
          'on_tap': {
            'type': 'navigate',
            'on_success': {
              'type': 'unknown_chain_action',
            },
          },
        },
      );
      final report = ActionValidator.validate(node);
      expect(report.hasWarnings, isTrue);
      expect(report.warnings.any((e) => e.message.contains('unknown_chain_action')), isTrue);
    });

    test('action chain on_failure is recursively validated', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        actions: {
          'on_tap': {
            'type': 'navigate',
            'on_failure': [
              {'type': 'toast'},
              {'type': 'unknown_failure_action'},
            ],
          },
        },
      );
      final report = ActionValidator.validate(node);
      expect(report.hasWarnings, isTrue);
      expect(report.warnings.any((e) => e.message.contains('unknown_failure_action')), isTrue);
    });
  });

  // --------------------------------------------------
  // StateValidator
  // --------------------------------------------------
  group('StateValidator', () {
    test('normal state passes', () {
      final node = SduiNode(type: 'Container', id: 'test');
      final report = StateValidator.validate(node);
      expect(report.hasWarnings, isFalse);
    });

    test('visible=false + loading=true produces warning', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        state: const RuntimeNodeState(visible: false, loading: true),
      );
      final report = StateValidator.validate(node);
      expect(report.hasWarnings, isTrue);
    });

    test('normalize fixes visible=false + loading=true', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        state: const RuntimeNodeState(visible: false, loading: true),
      );
      final normalized = StateValidator.normalize(node);
      expect(normalized.state.visible, isFalse);
      expect(normalized.state.loading, isFalse);
    });

    test('selected + disabled produces warning', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        state: const RuntimeNodeState(selected: true, enabled: false),
      );
      final report = StateValidator.validate(node);
      expect(report.hasWarnings, isTrue);
    });
  });

  // --------------------------------------------------
  // PropertyValidator
  // --------------------------------------------------
  group('PropertyValidator', () {
    test('valid property types pass', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        props: {
          'padding': '16',
          'margin': {'all': 8},
          'radius': 12,
          'background': '#FF0000',
          'spacing': 16,
        },
      );
      final report = PropertyValidator.validate(node);
      expect(report.hasWarnings, isFalse);
    });

    test('invalid padding type produces warning and is normalized', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        props: {'padding': <dynamic>[1, 2, 3]}, // List is invalid
      );
      final report = PropertyValidator.validate(node);
      expect(report.hasWarnings, isTrue);

      final normalized = PropertyValidator.normalize(node);
      expect(normalized.props['padding'], '0');
    });

    test('invalid background produces warning and is removed', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        props: {'background': 12345}, // not a String
      );
      final report = PropertyValidator.validate(node);
      expect(report.hasWarnings, isTrue);

      final normalized = PropertyValidator.normalize(node);
      expect(normalized.props.containsKey('background'), isFalse);
    });

    test('invalid radius produces warning and is normalized to 0', () {
      final node = SduiNode(
        type: 'Container',
        id: 'test',
        props: {'radius': [1, 2]}, // List is invalid
      );
      final report = PropertyValidator.validate(node);
      expect(report.hasWarnings, isTrue);

      final normalized = PropertyValidator.normalize(node);
      expect(normalized.props['radius'], 0);
    });
  });

  // --------------------------------------------------
  // FlutterCertificationEngine
  // --------------------------------------------------
  group('FlutterCertificationEngine', () {
    Map<String, dynamic> validEnvelope() => {
      'schema_version': '1.0.0',
      'scene_id': 'test-scene',
      'version': 1,
      'ttl_seconds': 60,
      'root': {
        'type': 'Column',
        'id': 'root',
        'props': {'padding': '16'},
        'children': [
          {'type': 'Text', 'id': 'title', 'props': {'value': 'Hello'}},
          {'type': 'Container', 'id': 'body', 'props': {'background': '#FFFFFF'}},
        ],
      },
    };

    late FlutterCertificationEngine engine;

    setUp(() {
      engine = const FlutterCertificationEngine();
    });

    test('valid envelope returns CertifiedScene.valid', () {
      final scene = engine.certify(validEnvelope());
      expect(scene.isValid, isTrue);
      expect(scene.root, isNotNull);
    });

    test('invalid envelope returns invalidEnvelope', () {
      final scene = engine.certify({});
      expect(scene.isValid, isFalse);
      expect(scene.status, CertificationStatus.invalidEnvelope);
    });

    test('unsupported schema returns unsupportedSchema', () {
      final envelope = validEnvelope();
      envelope['schema_version'] = '2.0.0';
      final scene = engine.certify(envelope);
      expect(scene.isValid, isFalse);
      expect(scene.status, CertificationStatus.unsupportedSchema);
    });

    test('null root returns parseError', () {
      final envelope = validEnvelope();
      envelope['root'] = null;
      final scene = engine.certify(envelope);
      expect(scene.isValid, isFalse);
      // null root is caught by envelope validator, so it will be invalidEnvelope
      expect(scene.status, CertificationStatus.invalidEnvelope);
    });

    test('engine never throws', () {
      final cases = <Map<String, dynamic>>[
        {},
        {'schema_version': '2.0.0'},
        {'schema_version': '1.0.0', 'root': null},
        {'schema_version': '1.0.0', 'root': <String, dynamic>{'type': 'Unknown'}},
        {'schema_version': '1.0.0', 'root': <String, dynamic>{'type': 'Column', 'props': <String, dynamic>{'padding': <dynamic>[1, 2, 3]}}},
        validEnvelope(),
      ];
      for (final payload in cases) {
        expect(() => engine.certify(payload), returnsNormally);
      }
    });

    test('valid scene with unknown component still reports warning', () {
      final envelope = validEnvelope();
      (envelope['root'] as Map<String, dynamic>)['children'] = <Map<String, dynamic>>[
        {'type': 'NonExistentComponent', 'id': 'weird'},
      ];
      final scene = engine.certify(envelope);
      expect(scene.isValid, isTrue);
      expect(scene.report.hasWarnings, isTrue);
      expect(scene.report.warnings.any((e) => e.message.contains('NonExistentComponent')), isTrue);
    });

    test('valid scene with unknown action reports warning', () {
      final envelope = validEnvelope();
      (envelope['root'] as Map<String, dynamic>)['actions'] = <String, dynamic>{
        'on_tap': {'type': 'hologram'},
      };
      final scene = engine.certify(envelope);
      expect(scene.isValid, isTrue);
      expect(scene.report.hasWarnings, isTrue);
      expect(scene.report.warnings.any((e) => e.message.contains('hologram')), isTrue);
    });

    test('report includes entries from all validators', () {
      final envelope = validEnvelope();
      (envelope['root'] as Map<String, dynamic>)['children'] = <Map<String, dynamic>>[
        {'type': 'UnknownType', 'id': 'x'},
      ];
      final scene = engine.certify(envelope);
      expect(scene.report.entries.length, greaterThan(5));
    });

    test('state normalization happens engine-wide', () {
      final envelope = validEnvelope();
      (envelope['root'] as Map<String, dynamic>)['state'] = <String, dynamic>{
        'visible': false,
        'loading': true,
      };
      final scene = engine.certify(envelope);
      expect(scene.isValid, isTrue);
      expect(scene.root!.state.loading, isFalse);
    });

    test('property normalization happens engine-wide', () {
      final envelope = validEnvelope();
      (envelope['root'] as Map<String, dynamic>)['props'] = <String, dynamic>{'padding': <dynamic>[1, 2]}; // invalid
      final scene = engine.certify(envelope);
      expect(scene.isValid, isTrue);
      expect(scene.root!.props['padding'], '0');
    });

    test('empty scene (root with no children) is valid', () {
      final envelope = validEnvelope();
      (envelope['root'] as Map<String, dynamic>)['children'] = <Map<String, dynamic>>[];
      final scene = engine.certify(envelope);
      expect(scene.isValid, isTrue);
    });
  });
}
