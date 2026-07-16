import 'package:flutter/animation.dart';

class NuriskMotion {
  NuriskMotion._();
  static const Duration micro = Duration(milliseconds: 150);
  static const Duration standard = Duration(milliseconds: 200);
  static const Duration pageTransition = Duration(milliseconds: 300);
  static const Duration sheetIn = Duration(milliseconds: 260);
  static const Duration sheetOut = Duration(milliseconds: 200);
  static const Duration expandCollapse = Duration(milliseconds: 220);
  static const Duration heroTransition = Duration(milliseconds: 320);
  static const Duration bannerIn = Duration(milliseconds: 240);
  static const Duration bannerOut = Duration(milliseconds: 180);
  static const Curve standardCurve = Curves.easeInOut;
  static const Curve emphasizedDecelerate = Curves.easeOutCubic;
  static const Curve emphasizedAccelerate = Curves.easeInCubic;
}
