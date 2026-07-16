import 'package:flutter/material.dart';
import '../../../core/theme/nurisk_colors.dart';
import '../../../core/theme/nurisk_radius.dart';

enum NuriskStatus { danger, warning, safe, info }

class NuriskStatusChip extends StatelessWidget {
  final String label;
  final NuriskStatus status;
  const NuriskStatusChip({super.key, required this.label, required this.status});

  Color get _bg {
    switch (status) {
      case NuriskStatus.danger: return NuriskColors.emergencyRed.withValues(alpha: 0.1);
      case NuriskStatus.warning: return NuriskColors.warningOrange.withValues(alpha: 0.1);
      case NuriskStatus.safe: return NuriskColors.safeGreen.withValues(alpha: 0.1);
      case NuriskStatus.info: return NuriskColors.infoBlue.withValues(alpha: 0.1);
    }
  }

  Color get _fg {
    switch (status) {
      case NuriskStatus.danger: return NuriskColors.emergencyRed;
      case NuriskStatus.warning: return NuriskColors.warningOrange;
      case NuriskStatus.safe: return NuriskColors.safeGreen;
      case NuriskStatus.info: return NuriskColors.infoBlue;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Semantics(
      label: label,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(color: _bg, borderRadius: BorderRadius.circular(NuriskRadius.full)),
        child: Text(label, style: TextStyle(color: _fg, fontSize: 11, fontWeight: FontWeight.w600)),
      ),
    );
  }
}
