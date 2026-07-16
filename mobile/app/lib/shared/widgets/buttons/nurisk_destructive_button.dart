import 'package:flutter/material.dart';
import '../../../core/theme/nurisk_colors.dart';
import '../../../core/theme/nurisk_radius.dart';

class NuriskDestructiveButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  const NuriskDestructiveButton({super.key, required this.label, required this.onPressed});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 48,
      child: ElevatedButton(
        onPressed: onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: NuriskColors.emergencyRed,
          foregroundColor: Colors.white,
          disabledBackgroundColor: NuriskColors.emergencyRed.withValues(alpha: 0.38),
          padding: const EdgeInsets.symmetric(horizontal: 20),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(NuriskRadius.sm)),
        ),
        child: Text(label),
      ),
    );
  }
}
