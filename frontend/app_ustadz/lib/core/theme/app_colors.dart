import 'package:flutter/material.dart';

/// Data class representing a customizable theme color preset
class AppColorPreset {
  final String key;
  final String name;
  final String subtitle;
  final Color primaryLight;
  final Color primaryDark;
  final Color primaryContainerLight;
  final Color primaryContainerDark;
  final Color onPrimaryLight;
  final Color onPrimaryDark;
  final Color onPrimaryContainerLight;
  final Color onPrimaryContainerDark;
  final Color previewColor;

  const AppColorPreset({
    required this.key,
    required this.name,
    required this.subtitle,
    required this.primaryLight,
    required this.primaryDark,
    required this.primaryContainerLight,
    required this.primaryContainerDark,
    this.onPrimaryLight = Colors.white,
    required this.onPrimaryDark,
    required this.onPrimaryContainerLight,
    required this.onPrimaryContainerDark,
    required this.previewColor,
  });
}

/// Material 3 Expressive & Dynamic App Color Palette
class AppColors {
  AppColors._();

  // === 5 SUITABLE COLOR PRESETS ===
  static const List<AppColorPreset> presets = [
    // 1. Hijau Madrasah (Default) - Sejuk, Religius & Asri
    AppColorPreset(
      key: 'emerald',
      name: 'Hijau Madrasah',
      subtitle: 'Nuansa asri, sejuk, & berkah (Default)',
      primaryLight: Color(0xFF146C2E),
      primaryDark: Color(0xFF3BC05B),
      primaryContainerLight: Color(0xFFDCFCE7),
      primaryContainerDark: Color(0xFF00531E),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF003911),
      onPrimaryContainerLight: Color(0xFF14532D),
      onPrimaryContainerDark: Color(0xFFA6FBAA),
      previewColor: Color(0xFF146C2E),
    ),
    // 2. Biru Samudra - Modern, Tenang & Profesional
    AppColorPreset(
      key: 'ocean',
      name: 'Biru Samudra',
      subtitle: 'Modern, tenang, & profesional',
      primaryLight: Color(0xFF0284C7),
      primaryDark: Color(0xFF38BDF8),
      primaryContainerLight: Color(0xFFE0F2FE),
      primaryContainerDark: Color(0xFF075985),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF082F49),
      onPrimaryContainerLight: Color(0xFF0369A1),
      onPrimaryContainerDark: Color(0xFFBAE6FD),
      previewColor: Color(0xFF0284C7),
    ),
    // 3. Tosca Islami - Teduh, Bersih & Harmonis
    AppColorPreset(
      key: 'teal',
      name: 'Tosca Islami',
      subtitle: 'Teduh, bersih, & harmonis',
      primaryLight: Color(0xFF0D9488),
      primaryDark: Color(0xFF2DD4BF),
      primaryContainerLight: Color(0xFFCCFBF1),
      primaryContainerDark: Color(0xFF115E59),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF042F2E),
      onPrimaryContainerLight: Color(0xFF134E4A),
      onPrimaryContainerDark: Color(0xFF99F6E4),
      previewColor: Color(0xFF0D9488),
    ),
    // 4. Ungu Elegan - Prestisius, Mewah & Edukatif
    AppColorPreset(
      key: 'violet',
      name: 'Ungu Elegan',
      subtitle: 'Prestisius, mewah, & berwibawa',
      primaryLight: Color(0xFF7C3AED),
      primaryDark: Color(0xFFA78BFA),
      primaryContainerLight: Color(0xFFEDE9FE),
      primaryContainerDark: Color(0xFF4C1D95),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF2E1065),
      onPrimaryContainerLight: Color(0xFF5B21B6),
      onPrimaryContainerDark: Color(0xFFDDD6FE),
      previewColor: Color(0xFF7C3AED),
    ),
    // 5. Cokelat Klasik - Hangat, Klasik & Penuh Khidmah
    AppColorPreset(
      key: 'amber',
      name: 'Cokelat Klasik',
      subtitle: 'Hangat, klasik, & penuh khidmah',
      primaryLight: Color(0xFFB45309),
      primaryDark: Color(0xFFFBBF24),
      primaryContainerLight: Color(0xFFFEF3C7),
      primaryContainerDark: Color(0xFF78350F),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF451A03),
      onPrimaryContainerLight: Color(0xFF78350F),
      onPrimaryContainerDark: Color(0xFFFDE68A),
      previewColor: Color(0xFFB45309),
    ),
    // 6. Pink Maroon - Anggun, Lembut & Mempesona
    AppColorPreset(
      key: 'pink_maroon',
      name: 'Pink Maroon',
      subtitle: 'Anggun, lembut, & mempesona',
      primaryLight: Color(0xFF9F1239),
      primaryDark: Color(0xFFFB7185),
      primaryContainerLight: Color(0xFFFFE4E6),
      primaryContainerDark: Color(0xFF4C0519),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF4C0519),
      onPrimaryContainerLight: Color(0xFF881337),
      onPrimaryContainerDark: Color(0xFFFECDD3),
      previewColor: Color(0xFF9F1239),
    ),
  ];

  static AppColorPreset _activePreset = presets[0];

  static AppColorPreset get activePreset => _activePreset;

  static void setPreset(String key) {
    _activePreset = presets.firstWhere(
      (p) => p.key == key,
      orElse: () => presets[0],
    );
  }

  // === DYNAMIC BRAND PRIMARY ===
  static Color get primaryLight => _activePreset.primaryLight;
  static Color get primaryDark => _activePreset.primaryDark;
  static Color get onPrimaryLight => _activePreset.onPrimaryLight;
  static Color get onPrimaryDark => _activePreset.onPrimaryDark;

  // === DYNAMIC PRIMARY CONTAINERS ===
  static Color get primaryContainerLight => _activePreset.primaryContainerLight;
  static Color get primaryContainerDark => _activePreset.primaryContainerDark;
  static Color get onPrimaryContainerLight =>
      _activePreset.onPrimaryContainerLight;
  static Color get onPrimaryContainerDark =>
      _activePreset.onPrimaryContainerDark;

  // === OLED TRUE BLACK SURFACES ===
  static const Color surfaceLight = Color(0xFFFAF9F6); // Warm Clean Canvas
  static const Color surfaceDark = Color(0xFF000000); // Super AMOLED True Black

  static const Color cardGlassLight = Color(0xD9FFFFFF);
  static const Color cardGlassDark = Color(0xA6181F18);

  static const Color surfaceContainerLowDark = Color(0xFF080D08);
  static const Color surfaceContainerDark = Color(0xFF101710);
  static const Color surfaceContainerHighDark = Color(0xFF182218);

  // === OUTLINE & BORDERS ===
  static const Color outlineLight = Color(0xFFE4E4E7); // Zinc 200
  static const Color outlineDark = Color(0xFF272F27); // Deep Zinc

  // === ACCENT PALETTE ===
  static const Color amberAccent = Color(0xFFF59E0B);
  static const Color skyBlueAccent = Color(0xFF0284C7);
  static const Color violetAccent = Color(0xFF7C3AED);
  static const Color roseDanger = Color(0xFFDC2626);

  // === SEMANTIC STATUS PRESENSI (H, I, S, A, D) ===
  static const Color hadirTextLight = Color(0xFF15803D);
  static const Color hadirBgLight = Color(0xFFDCFCE7);
  static const Color hadirTextDark = Color(0xFF4ADE80);
  static const Color hadirBgDark = Color(0xFF052E16);

  static const Color izinTextLight = Color(0xFF1D4ED8);
  static const Color izinBgLight = Color(0xFFDBEAFE);
  static const Color izinTextDark = Color(0xFF60A5FA);
  static const Color izinBgDark = Color(0xFF172554);

  static const Color sakitTextLight = Color(0xFFB45309);
  static const Color sakitBgLight = Color(0xFFFEF3C7);
  static const Color sakitTextDark = Color(0xFFFBBF24);
  static const Color sakitBgDark = Color(0xFF451A03);

  static const Color alphaTextLight = Color(0xFFB91C1C);
  static const Color alphaBgLight = Color(0xFFFEE2E2);
  static const Color alphaTextDark = Color(0xFFF87171);
  static const Color alphaBgDark = Color(0xFF450A0A);

  static const Color dispensasiTextLight = Color(0xFF6D28D9);
  static const Color dispensasiBgLight = Color(0xFFF3E8FF);
  static const Color dispensasiTextDark = Color(0xFFC084FC);
  static const Color dispensasiBgDark = Color(0xFF3B0764);
}
