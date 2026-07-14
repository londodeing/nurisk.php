import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'sdui_node.dart';

abstract class SduiComponent extends ConsumerWidget {
  final SduiNode node;

  const SduiComponent({super.key, required this.node});
}
