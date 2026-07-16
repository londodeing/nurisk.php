import 'package:flutter/material.dart';
import '../../../core/theme/nurisk_colors.dart';
import '../../../core/theme/nurisk_radius.dart';

class NuriskSecondaryButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  const NuriskSecondaryButton({super.key, required this.label, required this.onPressed});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 48,
      child: OutlinedButton(
        onPressed: onPressed,
        style: OutlinedButton.styleFrom(
          foregroundColor: NuriskColors.primary600,
          side: const BorderSide(color: NuriskColors.primary600, width: 1.5),
          padding: const EdgeInsets.symmetric(horizontal: 20),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(NuriskRadius.sm)),
        ),
        child: Text(label),
      ),
    );
  }
}
