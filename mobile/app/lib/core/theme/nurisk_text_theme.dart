import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

TextTheme buildNuriskTextTheme() {
  final base = GoogleFonts.plusJakartaSansTextTheme();
  return base.copyWith(
    displayLarge: base.displayLarge?.copyWith(fontSize: 32, height: 40 / 32, fontWeight: FontWeight.w700, letterSpacing: -0.2),
    headlineLarge: base.headlineLarge?.copyWith(fontSize: 28, height: 36 / 28, fontWeight: FontWeight.w700, letterSpacing: -0.2),
    titleLarge: base.titleLarge?.copyWith(fontSize: 22, height: 28 / 22, fontWeight: FontWeight.w600),
    titleMedium: base.titleMedium?.copyWith(fontSize: 18, height: 24 / 18, fontWeight: FontWeight.w600),
    bodyLarge: base.bodyLarge?.copyWith(fontSize: 16, height: 1.5, fontWeight: FontWeight.w400),
    bodyMedium: base.bodyMedium?.copyWith(fontSize: 14, height: 20 / 14, fontWeight: FontWeight.w400, letterSpacing: 0.1),
    bodySmall: base.bodySmall?.copyWith(fontSize: 12, height: 16 / 12, fontWeight: FontWeight.w400, letterSpacing: 0.2),
    labelSmall: base.labelSmall?.copyWith(fontSize: 11, height: 16 / 11, fontWeight: FontWeight.w600, letterSpacing: 0.6),
    labelLarge: base.labelLarge?.copyWith(fontSize: 14, height: 20 / 14, fontWeight: FontWeight.w600, letterSpacing: 0.2),
  );
}
