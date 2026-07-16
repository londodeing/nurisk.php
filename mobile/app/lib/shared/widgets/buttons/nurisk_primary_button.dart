import 'package:flutter/material.dart';
import '../../../core/theme/nurisk_colors.dart';
import '../../../core/theme/nurisk_radius.dart';

class NuriskPrimaryButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  const NuriskPrimaryButton({super.key, required this.label, required this.onPressed, this.isLoading = false});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 48,
      child: ElevatedButton(
        onPressed: isLoading ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: NuriskColors.primary600,
          foregroundColor: Colors.white,
          disabledBackgroundColor: NuriskColors.primary600.withValues(alpha: 0.38),
          padding: const EdgeInsets.symmetric(horizontal: 20),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(NuriskRadius.sm)),
        ),
        child: isLoading
            ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
            : Text(label),
      ),
    );
  }
}
