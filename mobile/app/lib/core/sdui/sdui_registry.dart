import 'package:flutter/material.dart';
import 'sdui_node.dart';
import 'sdui_component.dart';

typedef SduiComponentBuilder = SduiComponent Function(SduiNode node);

class SduiRegistry {
  static final SduiRegistry instance = SduiRegistry._internal();
  SduiRegistry._internal();

  final Map<String, SduiComponentBuilder> _builders = {};

  void register(String type, SduiComponentBuilder builder) {
    _builders[type] = builder;
  }

  SduiComponentBuilder? getBuilder(String type) {
    return _builders[type];
  }
}
