import 'package:flutter/material.dart';
import '../../../core/theme/nurisk_radius.dart';
import '../../../core/theme/nurisk_spacing.dart';

class NuriskCard extends StatelessWidget {
  final Widget child;
  const NuriskCard({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(NuriskRadius.md)),
      child: Padding(
        padding: const EdgeInsets.all(NuriskSpacing.lg),
        child: child,
      ),
    );
  }
}
