import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/features/public/config/config_module.dart';
import 'package:nurisk_mobile/core/sdui/sdui_screen.dart';
import 'package:nurisk_mobile/core/sdui/sdui_node.dart';
import 'package:nurisk_mobile/core/sdui/sdui_renderer.dart';
import 'package:nurisk_mobile/core/sdui/sdui_error_boundary.dart';
import 'package:nurisk_mobile/core/sdui/sdui_remote_screen.dart';

/// Feature flag: use NSS 1.0 Runtime pipeline for the dashboard.
/// Set to true to enable the new SduiRemoteScreen-based implementation.
/// Once visual parity and behavior parity are confirmed, this flag
/// becomes the default and the legacy provider-based path is removed.
const bool kUseRuntimeDashboard = false;

class PublicDashboardScreen extends ConsumerWidget {
  const PublicDashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // Runtime mode: use SduiRemoteScreen with NSS 1.0 endpoint
    if (kUseRuntimeDashboard) {
      return SduiRemoteScreen(
        endpoint: 'public/dashboard/config?runtime=1',
        title: 'Dashboard Publik',
      );
    }

    // Legacy mode: use provider-based config fetching
    final configState = ref.watch(configProvider);

    final appBar = AppBar(
      title: const Text('Dashboard Publik'),
      centerTitle: true,
      actions: [
        IconButton(
          icon: const Icon(Icons.notifications_outlined),
          onPressed: () {},
        ),
      ],
    );

    return RefreshIndicator(
      onRefresh: () async {
        await ref.read(configProvider.notifier).refresh();
      },
      child: configState.when(
        data: (config) {
          final nodesJson = config.rawJson['nodes'] as List<dynamic>? ?? [];

          return SduiSafeBuilder(
            parser: () {
              return nodesJson.map((e) => SduiNode.fromJson(e)).toList();
            },
            builder: (data) {
              final nodes = data as List<SduiNode>;
              final rootNode = nodes.isNotEmpty
                  ? nodes.first
                  : const SduiNode(
                      id: 'fallback',
                      type: 'Container',
                      props: {'background': 'surface'},
                    );

              if (config.layoutType == 'scene') {
                return Scaffold(
                  appBar: appBar,
                  body: SduiRenderer(node: rootNode),
                );
              }

              return SduiScreen(
                title: 'Dashboard Publik',
                rootNode: rootNode,
                appBar: appBar,
              );
            },
          );
        },
        loading: () => Scaffold(
          appBar: appBar,
          body: const Center(child: CircularProgressIndicator()),
        ),
        error: (error, stack) => Scaffold(
          appBar: appBar,
          body: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.error_outline, color: Colors.red, size: 48),
                const SizedBox(height: 16),
                const Text('Gagal memuat konfigurasi SDUI dashboard.'),
                TextButton(
                  onPressed: () =>
                      ref.read(configProvider.notifier).refresh(),
                  child: const Text('Coba Lagi'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
