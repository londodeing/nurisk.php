import 'package:dio/dio.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import 'package:nurisk_mobile/core/services/navigation_service.dart';
import 'runtime_action.dart';

class RuntimeContext {
  final String? sceneId;
  final void Function(String message, {String? action}) showToast;
  final Future<bool> Function(ConfirmDialog config) showConfirmDialog;
  final Future<void> Function() refreshScene;
  final void Function(String event, Map<String, dynamic>? data) trackAnalytics;
  final Dio httpClient;
  final Future<void> Function(Map<String, Map<String, dynamic>> patches)? onOptimisticApply;
  final Future<void> Function()? onOptimisticRevert;

  const RuntimeContext({
    this.sceneId,
    required this.showToast,
    required this.showConfirmDialog,
    required this.refreshScene,
    required this.trackAnalytics,
    required this.httpClient,
    this.onOptimisticApply,
    this.onOptimisticRevert,
  });

  NavigationService get navigation => RuntimeServicesScope.instance.navigation;

  Future<void> applyOptimistic(Map<String, Map<String, dynamic>> patches) async {
    if (onOptimisticApply != null) {
      await onOptimisticApply!(patches);
    }
  }

  Future<void> revertOptimistic() async {
    if (onOptimisticRevert != null) {
      await onOptimisticRevert!();
    }
  }
}
