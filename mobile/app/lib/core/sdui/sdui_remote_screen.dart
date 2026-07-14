import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import 'package:nurisk_mobile/core/runtime/certification/flutter_certification_engine.dart';
import 'package:nurisk_mobile/core/runtime/certification/certified_scene.dart';
import 'package:nurisk_mobile/core/sdui/sdui_screen.dart';
import 'package:nurisk_mobile/core/sdui/sdui_error_boundary.dart';
import 'package:nurisk_mobile/core/sdui/components/certification_error_widget.dart';
import 'package:nurisk_mobile/core/sdui/components/unsupported_schema_widget.dart';
import 'package:nurisk_mobile/core/sdui/components/minimal_error_screen.dart';

class SduiRemoteScreen extends ConsumerStatefulWidget {
  final String endpoint;
  final String title;

  const SduiRemoteScreen({super.key, required this.endpoint, required this.title});

  @override
  ConsumerState<SduiRemoteScreen> createState() => _SduiRemoteScreenState();
}

class _SduiRemoteScreenState extends ConsumerState<SduiRemoteScreen> {
  static const _engine = FlutterCertificationEngine();

  bool _loading = true;
  CertifiedScene? _certifiedScene;
  String? _networkError;

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    try {
      final dio = ref.read(authApiClientProvider);
      final res = await dio.get(widget.endpoint);
      final scene = _engine.certify(res.data as Map<String, dynamic>);
      setState(() {
        _certifiedScene = scene;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _networkError = e.toString();
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return Scaffold(
        appBar: AppBar(title: Text(widget.title)),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_networkError != null) {
      return MinimalErrorScreen(
        title: widget.title,
        message: 'Gagal memuat: $_networkError',
      );
    }

    final scene = _certifiedScene!;

    // Certification error — show fallback
    if (!scene.isValid) {
      return _buildCertificationError(scene);
    }

    return SduiSafeBuilder(
      parser: () => scene.root!,
      builder: (data) {
        return SduiScreen(
          title: widget.title,
          rootNode: data,
          certificationReport: scene.report,
        );
      },
    );
  }

  Widget _buildCertificationError(CertifiedScene scene) {
    switch (scene.status) {
      case CertificationStatus.unsupportedSchema:
        final schemaVersion = scene.report.entries
            .where((e) => e.details?.containsKey('schema_version') ?? false)
            .firstOrNull
            ?.details?['schema_version'] as String? ?? 'unknown';
        return Scaffold(
          appBar: AppBar(title: Text(widget.title)),
          body: UnsupportedSchemaWidget(schemaVersion: schemaVersion),
        );
      case CertificationStatus.invalidEnvelope:
      case CertificationStatus.parseError:
        return Scaffold(
          appBar: AppBar(title: Text(widget.title)),
          body: CertificationErrorWidget(report: scene.report),
        );
      case CertificationStatus.valid:
        return MinimalErrorScreen(title: widget.title);
    }
  }
}
