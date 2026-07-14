import 'package:flutter/material.dart';
import 'action_dispatcher.dart';

class ActionDispatcherScope extends InheritedWidget {
  final RuntimeActionDispatcher dispatcher;

  const ActionDispatcherScope({
    super.key,
    required this.dispatcher,
    required super.child,
  });

  static RuntimeActionDispatcher of(BuildContext context) {
    final scope = context.dependOnInheritedWidgetOfExactType<ActionDispatcherScope>();
    assert(scope != null, 'No ActionDispatcherScope found in widget tree');
    return scope!.dispatcher;
  }

  @override
  bool updateShouldNotify(ActionDispatcherScope oldWidget) =>
      dispatcher != oldWidget.dispatcher;
}
