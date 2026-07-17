import 'package:flutter/material.dart';
import 'nurisk_colors.dart';
import 'nurisk_text_theme.dart';

final ThemeData nuriskLightTheme = ThemeData(
  useMaterial3: true,
  colorScheme: ColorScheme.fromSeed(
    seedColor: NuriskColors.primary600,
    brightness: Brightness.light,
    error: NuriskColors.emergencyRed,
  ),
  scaffoldBackgroundColor: NuriskColors.bgWhite,
  textTheme: buildNuriskTextTheme(Brightness.light),
  elevatedButtonTheme: ElevatedButtonThemeData(
    style: ElevatedButton.styleFrom(
      backgroundColor: NuriskColors.primary600,
      foregroundColor: Colors.white,
      minimumSize: const Size(64, 48),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
    ),
  ),
  inputDecorationTheme: InputDecorationTheme(
    filled: true,
    fillColor: NuriskColors.primary50.withValues(alpha: 0.3),
    border: OutlineInputBorder(borderRadius: BorderRadius.circular(4)),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(4),
      borderSide: const BorderSide(color: NuriskColors.primary600, width: 1.5),
    ),
    errorBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(4),
      borderSide: const BorderSide(color: NuriskColors.emergencyRed, width: 1.5),
    ),
  ),
);

final ThemeData nuriskDarkTheme = ThemeData(
  useMaterial3: true,
  brightness: Brightness.dark,
  colorScheme: ColorScheme.fromSeed(
    seedColor: NuriskColors.primaryAccentDark,
    brightness: Brightness.dark,
  ),
  scaffoldBackgroundColor: NuriskColors.surfaceBase,
  textTheme: buildNuriskTextTheme(Brightness.dark),
  elevatedButtonTheme: ElevatedButtonThemeData(
    style: ElevatedButton.styleFrom(
      backgroundColor: NuriskColors.primaryAccentDark,
      foregroundColor: Colors.black,
      minimumSize: const Size(64, 48),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
    ),
  ),
  inputDecorationTheme: InputDecorationTheme(
    filled: true,
    fillColor: NuriskColors.borderDark.withValues(alpha: 0.3),
    border: OutlineInputBorder(borderRadius: BorderRadius.circular(4)),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(4),
      borderSide: const BorderSide(color: NuriskColors.primaryAccentDark, width: 1.5),
    ),
    errorBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(4),
      borderSide: const BorderSide(color: NuriskColors.emergencyRed, width: 1.5),
    ),
  ),
);
