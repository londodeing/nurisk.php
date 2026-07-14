import 'package:nurisk_mobile/core/sdui/sdui_node.dart';

class NssSceneResponse {
  final String schemaVersion;
  final String sceneId;
  final int version;
  final int ttlSeconds;
  final SduiNode root;

  const NssSceneResponse({
    required this.schemaVersion,
    required this.sceneId,
    required this.version,
    required this.ttlSeconds,
    required this.root,
  });

  factory NssSceneResponse.fromJson(Map<String, dynamic> json) {
    // --------------------------------------------------
    // 1. schema_version — wajib, menentukan kompatibilitas
    // --------------------------------------------------
    final schemaVersion = json['schema_version'] as String?;
    if (schemaVersion == null) {
      throw const FormatException(
        'NSS Error: Missing "schema_version" in envelope. '
        'All NSS 1.0 responses must include schema_version.',
      );
    }
    if (!schemaVersion.startsWith('1.')) {
      throw FormatException(
        'NSS Error: Incompatible schema_version "$schemaVersion". '
        'Expected 1.x.x. This client does not support schema $schemaVersion.',
      );
    }

    // --------------------------------------------------
    // 2. scene_id — identitas scene (optional, untuk logging/diff)
    // --------------------------------------------------
    final sceneId = json['scene_id'] as String? ?? 'unknown';

    // --------------------------------------------------
    // 3. version — timestamp versi data (optional, untuk cache)
    // --------------------------------------------------
    final version = json['version'] as int? ?? 0;

    // --------------------------------------------------
    // 4. ttl_seconds — waktu hidup cache (optional)
    // --------------------------------------------------
    final ttlSeconds = json['ttl_seconds'] as int? ?? 0;

    // --------------------------------------------------
    // 5. root — root runtime tree (wajib)
    // --------------------------------------------------
    final rootJson = json['root'] as Map<String, dynamic>?;
    if (rootJson == null) {
      throw const FormatException(
        'NSS Error: Missing "root" field in envelope. '
        'The runtime tree root is required.',
      );
    }

    final root = SduiNode.fromJson(rootJson);

    return NssSceneResponse(
      schemaVersion: schemaVersion,
      sceneId: sceneId,
      version: version,
      ttlSeconds: ttlSeconds,
      root: root,
    );
  }
}
