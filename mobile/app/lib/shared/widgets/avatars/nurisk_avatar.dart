import 'package:flutter/material.dart';
import '../../../core/theme/nurisk_colors.dart';

enum NuriskRole { volunteer, operator, commander }

class NuriskAvatar extends StatelessWidget {
  final String initials;
  final NuriskRole role;
  const NuriskAvatar({super.key, required this.initials, required this.role});

  Color get _ringColor {
    switch (role) {
      case NuriskRole.volunteer: return NuriskColors.infoBlue;
      case NuriskRole.operator: return NuriskColors.primary600;
      case NuriskRole.commander: return NuriskColors.emergencyRed;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 40, height: 40,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        border: Border.all(color: _ringColor, width: 2),
        color: NuriskColors.primary100,
      ),
      alignment: Alignment.center,
      child: Text(initials, style: TextStyle(color: NuriskColors.primary700, fontWeight: FontWeight.w600)),
    );
  }
}
