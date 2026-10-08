import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Material 3 Expressive Compact Typography (Pixel Google Sans / Plus Jakarta Sans style)
class AppTypography {
  AppTypography._();

  // Arabic Font for Bismillah, Hadith, Du'a, Quran
  static TextStyle arabic({
    double fontSize = 18,
    FontWeight fontWeight = FontWeight.normal,
    Color? color,
  }) {
    return GoogleFonts.amiri(
      fontSize: fontSize,
      fontWeight: fontWeight,
      color: color,
      height: 1.6,
    );
  }

  // Latin Modern M3 Expressive Compact Typography
  static TextTheme textTheme(Color textColor) {
    return GoogleFonts.plusJakartaSansTextTheme().copyWith(
      displayLarge: GoogleFonts.plusJakartaSans(
        fontSize: 26,
        fontWeight: FontWeight.w800,
        color: textColor,
        letterSpacing: -0.5,
        height: 1.15,
      ),
      displayMedium: GoogleFonts.plusJakartaSans(
        fontSize: 22,
        fontWeight: FontWeight.w800,
        color: textColor,
        letterSpacing: -0.4,
        height: 1.2,
      ),
      displaySmall: GoogleFonts.plusJakartaSans(
        fontSize: 20,
        fontWeight: FontWeight.w700,
        color: textColor,
        letterSpacing: -0.3,
        height: 1.2,
      ),
      headlineLarge: GoogleFonts.plusJakartaSans(
        fontSize: 20,
        fontWeight: FontWeight.w800,
        color: textColor,
        letterSpacing: -0.3,
        height: 1.25,
      ),
      headlineMedium: GoogleFonts.plusJakartaSans(
        fontSize: 17,
        fontWeight: FontWeight.w700,
        color: textColor,
        letterSpacing: -0.2,
        height: 1.25,
      ),
      headlineSmall: GoogleFonts.plusJakartaSans(
        fontSize: 15,
        fontWeight: FontWeight.w700,
        color: textColor,
        height: 1.3,
      ),
      titleLarge: GoogleFonts.plusJakartaSans(
        fontSize: 15,
        fontWeight: FontWeight.w800,
        color: textColor,
        letterSpacing: -0.1,
      ),
      titleMedium: GoogleFonts.plusJakartaSans(
        fontSize: 13.5,
        fontWeight: FontWeight.w700,
        color: textColor,
        letterSpacing: -0.1,
      ),
      titleSmall: GoogleFonts.plusJakartaSans(
        fontSize: 12,
        fontWeight: FontWeight.w700,
        color: textColor,
      ),
      bodyLarge: GoogleFonts.plusJakartaSans(
        fontSize: 13.5,
        fontWeight: FontWeight.w500,
        color: textColor,
        height: 1.4,
      ),
      bodyMedium: GoogleFonts.plusJakartaSans(
        fontSize: 12.5,
        fontWeight: FontWeight.w400,
        color: textColor,
        height: 1.4,
      ),
      bodySmall: GoogleFonts.plusJakartaSans(
        fontSize: 11,
        fontWeight: FontWeight.w400,
        color: textColor.withValues(alpha: 0.7),
        height: 1.35,
      ),
      labelLarge: GoogleFonts.plusJakartaSans(
        fontSize: 12.5,
        fontWeight: FontWeight.w700,
        color: textColor,
        letterSpacing: 0.1,
      ),
      labelMedium: GoogleFonts.plusJakartaSans(
        fontSize: 11,
        fontWeight: FontWeight.w700,
        color: textColor,
        letterSpacing: 0.2,
      ),
      labelSmall: GoogleFonts.plusJakartaSans(
        fontSize: 10,
        fontWeight: FontWeight.w600,
        color: textColor.withValues(alpha: 0.65),
        letterSpacing: 0.3,
      ),
    );
  }
}
