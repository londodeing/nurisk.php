import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/runtime/actions/action_dispatcher_scope.dart';
import 'package:nurisk_mobile/core/runtime/actions/runtime_action.dart';
import 'package:nurisk_mobile/core/sdui/sdui_component.dart';
import 'package:nurisk_mobile/core/sdui/sdui_renderer.dart';

import 'dart:ui';

class SduiCard extends SduiComponent {
  const SduiCard({super.key, required super.node});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    if (node.children != null && node.children!.isNotEmpty) {
      return Container(
        margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(24),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 16,
              offset: const Offset(0, 8),
            )
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(24),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 12, sigmaY: 12),
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.65),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: Colors.white.withOpacity(0.5), width: 1.5),
              ),
              child: Material(
                type: MaterialType.transparency,
                child: InkWell(
                  onTap: node.actions['on_tap'] != null
                      ? () {
                          final dispatcher = ActionDispatcherScope.of(context);
                          final action = RuntimeAction.fromJson(
                              node.actions['on_tap'] as Map<String, dynamic>);
                          dispatcher.dispatch(action, nodeState: node.state);
                        }
                      : null,
                  borderRadius: BorderRadius.circular(24),
                  child: Padding(
                    padding: const EdgeInsets.all(20.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: node.children!.map((n) => SduiRenderer(node: n)).toList(),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
      );
    }

    final title = node.props['title'] ?? '';
    final description = node.props['description'] ?? '';

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 16,
            offset: const Offset(0, 8),
          )
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(24),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 12, sigmaY: 12),
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.65),
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: Colors.white.withOpacity(0.5), width: 1.5),
            ),
            child: Material(
              type: MaterialType.transparency,
              child: InkWell(
                onTap: node.actions['on_tap'] != null
                    ? () {
                        final dispatcher = ActionDispatcherScope.of(context);
                        final action = RuntimeAction.fromJson(
                            node.actions['on_tap'] as Map<String, dynamic>);
                        dispatcher.dispatch(action, nodeState: node.state);
                      }
                    : null,
                borderRadius: BorderRadius.circular(24),
                child: ListTile(
                  contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                  leading: const Icon(Icons.info, color: Colors.blueAccent, size: 32),
                  title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
                  subtitle: Text(description),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
