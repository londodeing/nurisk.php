import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:nurisk_mobile/core/runtime/actions/action_dispatcher.dart';
import 'package:nurisk_mobile/core/runtime/actions/action_dispatcher_scope.dart';
import 'package:nurisk_mobile/core/runtime/actions/runtime_context.dart';
import 'package:nurisk_mobile/core/runtime/actions/handlers/navigate_handler.dart';
import 'package:nurisk_mobile/core/runtime/actions/handlers/submit_handler.dart';
import 'package:nurisk_mobile/core/runtime/actions/handlers/reload_handler.dart';
import 'package:nurisk_mobile/core/runtime/actions/handlers/toast_handler.dart';
import 'package:nurisk_mobile/core/runtime/actions/handlers/custom_action_handler.dart';
import 'package:nurisk_mobile/core/runtime/certification/certification_report.dart';
import 'sdui_node.dart';
import 'sdui_renderer.dart';

class SduiScreen extends StatefulWidget {
  final String title;
  final SduiNode rootNode;
  final Widget? floatingActionButton;
  final PreferredSizeWidget? appBar;
  final Future<void> Function()? onRefresh;
  final Dio? httpClient;
  final CertificationReport? certificationReport;

  const SduiScreen({
    super.key,
    required this.title,
    required this.rootNode,
    this.floatingActionButton,
    this.appBar,
    this.onRefresh,
    this.httpClient,
    this.certificationReport,
  });

  @override
  State<SduiScreen> createState() => _SduiScreenState();
}

class _SduiScreenState extends State<SduiScreen> {
  late SduiNode _rootNode;
  static int _buildCount = 0;

  @override
  void initState() {
    super.initState();
    _rootNode = widget.rootNode;
    final debugUuid = _rootNode.props['debug_uuid'] as String?;
    debugPrint('[FORENSIC] SduiScreen.initState — rootNode id=${_rootNode.id} type=${_rootNode.type} children=${_rootNode.children?.length ?? 0} debug_uuid=$debugUuid');
  }

  @override
  void didUpdateWidget(SduiScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.rootNode != oldWidget.rootNode) {
      debugPrint('[FORENSIC] SduiScreen.didUpdateWidget — rootNode changed, new id=${widget.rootNode.id}');
      _rootNode = widget.rootNode;
    }
  }

  Future<void> _applyOptimistic(Map<String, Map<String, dynamic>> patches) async {
    debugPrint('[FORENSIC] SduiScreen._applyOptimistic — patches=${patches.keys}');
    setState(() {
      for (final entry in patches.entries) {
        _rootNode = _rootNode.patchProps(entry.key, entry.value);
      }
    });
  }

  Future<void> _revertOptimistic() async {
    debugPrint('[FORENSIC] SduiScreen._revertOptimistic');
    setState(() {
      _rootNode = widget.rootNode;
    });
  }

  @override
  Widget build(BuildContext context) {
    _buildCount++;
    final debugUuid = _rootNode.props['debug_uuid'] as String?;
    debugPrint('[FORENSIC] SduiScreen.build #$_buildCount — _rootNode id=${_rootNode.id} type=${_rootNode.type} debug_uuid=$debugUuid');

    // F14: Widget tree dumps (first build + every 10th)
    if (_buildCount == 1 || _buildCount % 10 == 0) {
      debugPrint('[FORENSIC] ===== debugDumpApp (build #$_buildCount) =====');
      debugDumpApp();
      debugPrint('[FORENSIC] ===== debugDumpRenderTree (build #$_buildCount) =====');
      debugDumpRenderTree();
      debugPrint('[FORENSIC] ===== debugDumpLayerTree (build #$_buildCount) =====');
      debugDumpLayerTree();
    }
    // F6: Uncomment next line to prove build() is live (screen will show red error)
    // throw Exception('[FORENSIC] FATAL: SduiScreen.build() executed');
    final dispatcher = RuntimeActionDispatcher(
      context: RuntimeContext(
        sceneId: widget.rootNode.id,
        showToast: (String message, {String? action}) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(message),
              action: action != null
                  ? SnackBarAction(label: action, onPressed: () {})
                  : null,
            ),
          );
        },
        showConfirmDialog: (config) async {
          return await showDialog<bool>(
                context: context,
                builder: (ctx) => AlertDialog(
                  title: Text(config.title),
                  content: Text(config.message),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.of(ctx).pop(false),
                      child: Text(config.cancelLabel),
                    ),
                    TextButton(
                      onPressed: () => Navigator.of(ctx).pop(true),
                      child: Text(config.confirmLabel),
                    ),
                  ],
                ),
              ) ??
              false;
        },
        refreshScene: () async {
          if (widget.onRefresh != null) {
            await widget.onRefresh!();
          }
        },
        trackAnalytics: (String event, Map<String, dynamic>? data) {
          debugPrint('[Analytics] $event $data');
        },
        httpClient: widget.httpClient ?? Dio(),
        onOptimisticApply: _applyOptimistic,
        onOptimisticRevert: _revertOptimistic,
      ),
    );

    dispatcher.registerAll([
      NavigateHandler(),
      SubmitHandler(),
      ReloadHandler(),
      ToastHandler(),
      CustomActionHandler(),
    ]);

    return ActionDispatcherScope(
      dispatcher: dispatcher,
      child: Scaffold(
        appBar: widget.appBar ??
            AppBar(
              title: Text(widget.title),
              elevation: 0,
            ),
        body: SafeArea(
          child: Column(
            children: [
              if (widget.certificationReport != null && widget.certificationReport!.hasWarnings)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  color: Colors.orange.shade50,
                  child: Text(
                    '⚠ ${widget.certificationReport!.warnings.length} certification warning(s)',
                    style: TextStyle(color: Colors.orange.shade800, fontSize: 12),
                  ),
                ),
              Expanded(
                child: SduiRenderer(node: _rootNode, debugUuid: debugUuid),
              ),
            ],
          ),
        ),
        floatingActionButton: widget.floatingActionButton,
      ),
    );
  }
}
