import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/runtime/actions/action_dispatcher_scope.dart';
import 'package:nurisk_mobile/core/runtime/actions/runtime_action.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_renderer.dart';
import 'package:nurisk_mobile/core/sdui/sdui_nss_utils.dart';

class SduiContainer extends SduiComponent {
  const SduiContainer({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final padding = SduiNssUtils.parseEdgeInsets(node.props['padding']);
    final margin = SduiNssUtils.parseEdgeInsets(node.props['margin']);
    final radius = SduiNssUtils.parseRadius(node.props['radius'] as String?);
    
    final bgColor = SduiNssUtils.parseColor(node.props['background'] as String?);
    debugPrint('[FORENSIC_ACTION] SduiContainer id=${node.id} actions=${node.actions.keys} actions.isNotEmpty=${node.actions.isNotEmpty} hasOnTap=${node.actions['on_tap'] != null} bgColor=$bgColor');

    Widget childWidget = const SizedBox.shrink();
    if (node.children != null && node.children!.isNotEmpty) {
      if (node.children!.length == 1) {
        childWidget = SduiRenderer(node: node.children!.first);
      } else {
        childWidget = Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: node.children!.map((c) => SduiRenderer(node: c)).toList(),
        );
      }
    }

    Widget container = Container(
      padding: padding,
      margin: margin,
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: radius,
      ),
      child: childWidget,
    );

    if (node.actions.isNotEmpty && node.actions['on_tap'] != null) {
      final actionJson = node.actions['on_tap'] as Map<String, dynamic>;
      debugPrint('[FORENSIC_ACTION] SduiContainer id=${node.id} CREATING INKWELL with actionType=${actionJson['type']}');
      return Listener(
        onPointerDown: (event) {
          debugPrint('[FORENSIC_GESTURE] id=${node.id} POINTER DOWN position=${event.position}');
          WidgetsBinding.instance.addPostFrameCallback((_) {
            debugPrint('========== FORENSIC: debugDumpApp ==========');
            debugDumpApp();
            debugPrint('========== FORENSIC: debugDumpRenderTree ==========');
            debugDumpRenderTree();
            debugPrint('========== FORENSIC: debugDumpLayerTree ==========');
            debugDumpLayerTree();
            debugPrint('========== FORENSIC: END DUMPS ==========');
          });
        },
        onPointerMove: (event) => debugPrint('[FORENSIC_GESTURE] id=${node.id} POINTER MOVE position=${event.position}'),
        onPointerUp: (event) => debugPrint('[FORENSIC_GESTURE] id=${node.id} POINTER UP position=${event.position}'),
        child: Padding(
          padding: margin,
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              onTapDown: (details) => debugPrint('[FORENSIC_GESTURE] id=${node.id} onTapDown global=${details.globalPosition} local=${details.localPosition}'),
              onTapCancel: () => debugPrint('[FORENSIC_GESTURE] id=${node.id} onTapCancel'),
              onTapUp: (details) => debugPrint('[FORENSIC_GESTURE] id=${node.id} onTapUp global=${details.globalPosition}'),
              onTap: () {
                debugPrint('[FORENSIC_ACTION] SduiContainer id=${node.id} ONTAP FIRED');
                debugPrint('[FORENSIC_ACTION] SduiContainer id=${node.id} actionJson=$actionJson');
                final dispatcher = ActionDispatcherScope.of(context);
                final action = RuntimeAction.fromJson(actionJson);
                debugPrint('[FORENSIC_ACTION] SduiContainer id=${node.id} action type=${action.type} payload.keys=${action.payload.keys}');
                dispatcher.dispatch(action, nodeState: node.state);
              },
              borderRadius: radius,
              child: Container(
                padding: padding,
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: radius,
                ),
                child: childWidget,
              ),
            ),
          ),
        ),
      );
    }

    return container;
  }
}
