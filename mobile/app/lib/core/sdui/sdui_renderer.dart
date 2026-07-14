import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:nurisk_mobile/core/runtime/state/runtime_state_widget.dart';
import 'components/sdui_unknown_component.dart';
import 'sdui_node.dart';
import 'sdui_registry.dart';

class SduiRenderer extends StatelessWidget {
  final SduiNode node;
  final String? debugUuid;

  const SduiRenderer({super.key, required this.node, this.debugUuid});

  @override
  Widget build(BuildContext context) {
    final builder = SduiRegistry.instance.getBuilder(node.type);

    debugPrint('[FORENSIC_RENDER] node id=${node.id} type=${node.type} builder=${builder != null} children=${node.children?.length ?? 0} debug_uuid=$debugUuid');

    Widget child;
    if (builder != null) {
      child = builder(node);
    } else {
      debugPrint('[FORENSIC_RENDER] UNKNOWN TYPE: ${node.type} — using SduiUnknownComponent');
      child = SduiUnknownComponent(node: node);
    }

    return RuntimeStateWidget(
      state: node.state,
      child: child,
    );
  }
}
