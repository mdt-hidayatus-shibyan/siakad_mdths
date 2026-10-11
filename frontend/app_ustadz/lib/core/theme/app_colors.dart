import 'package:flutter/material.dart';

/// Data class representing a customizable theme color preset with M3 Expressive dynamic surface roles
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

  // M3 Expressive Secondary & Tertiary roles
  final Color? secondaryLight;
  final Color? secondaryDark;
  final Color? secondaryContainerLight;
  final Color? secondaryContainerDark;
  final Color? onSecondaryLight;
  final Color? onSecondaryDark;
  final Color? onSecondaryContainerLight;
  final Color? onSecondaryContainerDark;
  final Color? tertiaryLight;
  final Color? tertiaryDark;
  final Color? tertiaryContainerLight;
  final Color? tertiaryContainerDark;

  // M3 Expressive Dynamic Tonal Surfaces (Tinted by Theme Accent)
  // Light Mode Surfaces
  final Color? surfaceLight;
  final Color? surfaceDimLight;
  final Color? surfaceBrightLight;
  final Color? surfaceContainerLowestLight;
  final Color? surfaceContainerLowLight;
  final Color? surfaceContainerLight;
  final Color? surfaceContainerHighLight;
  final Color? surfaceContainerHighestLight;
  final Color? outlineLight;
  final Color? outlineVariantLight;

  // Dark Mode Surfaces (Super AMOLED with Chromatic Tinted Containers)
  final Color? surfaceDark;
  final Color? surfaceDimDark;
  final Color? surfaceBrightDark;
  final Color? surfaceContainerLowestDark;
  final Color? surfaceContainerLowDark;
  final Color? surfaceContainerDark;
  final Color? surfaceContainerHighDark;
  final Color? surfaceContainerHighestDark;
  final Color? outlineDark;
  final Color? outlineVariantDark;

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
    this.secondaryLight,
    this.secondaryDark,
    this.secondaryContainerLight,
    this.secondaryContainerDark,
    this.onSecondaryLight,
    this.onSecondaryDark,
    this.onSecondaryContainerLight,
    this.onSecondaryContainerDark,
    this.tertiaryLight,
    this.tertiaryDark,
    this.tertiaryContainerLight,
    this.tertiaryContainerDark,
    this.surfaceLight,
    this.surfaceDimLight,
    this.surfaceBrightLight,
    this.surfaceContainerLowestLight,
    this.surfaceContainerLowLight,
    this.surfaceContainerLight,
    this.surfaceContainerHighLight,
    this.surfaceContainerHighestLight,
    this.outlineLight,
    this.outlineVariantLight,
    this.surfaceDark,
    this.surfaceDimDark,
    this.surfaceBrightDark,
    this.surfaceContainerLowestDark,
    this.surfaceContainerLowDark,
    this.surfaceContainerDark,
    this.surfaceContainerHighDark,
    this.surfaceContainerHighestDark,
    this.outlineDark,
    this.outlineVariantDark,
  });
}

/// Material 3 Expressive & Dynamic App Color Palette
class AppColors {
  AppColors._();

  // === 10 ACCENT COLORS (SESUAI GAMBAR) + EMERALD DEFAULT ===
  static const List<AppColorPreset> presets = [
    // 0. Hijau Madrasah (Default) - Sejuk, Religius & Asri
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
      secondaryLight: Color(0xFF526350),
      secondaryDark: Color(0xFFB9CCB5),
      secondaryContainerLight: Color(0xFFD5E8D0),
      secondaryContainerDark: Color(0xFF3A4B39),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF243424),
      onSecondaryContainerLight: Color(0xFF101F10),
      onSecondaryContainerDark: Color(0xFFD5E8D0),
      tertiaryLight: Color(0xFF39656B),
      tertiaryDark: Color(0xFFA1CED5),
      tertiaryContainerLight: Color(0xFFBCEBF2),
      tertiaryContainerDark: Color(0xFF1F4D53),

      // Dynamic Tinted Surfaces - Light Mode (Sage Mint Tint)
      surfaceLight: Color(0xFFF7FBF4),
      surfaceDimLight: Color(0xFFEFEFE7),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFF1F7EE),
      surfaceContainerLight: Color(0xFFEAF3E6),
      surfaceContainerHighLight: Color(0xFFE3EDE0),
      surfaceContainerHighestLight: Color(0xFFDCE7D9),
      outlineLight: Color(0xFF727970),
      outlineVariantLight: Color(0xFFC2CBC0),

      // Dynamic Tinted Surfaces - Dark Mode (Super AMOLED with dark emerald containers)
      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF1B271B),
      surfaceContainerLowestDark: Color(0xFF060D06),
      surfaceContainerLowDark: Color(0xFF0D180D),
      surfaceContainerDark: Color(0xFF142414),
      surfaceContainerHighDark: Color(0xFF1C2F1C),
      surfaceContainerHighestDark: Color(0xFF243B24),
      outlineDark: Color(0xFF8C958A),
      outlineVariantDark: Color(0xFF2B3C2B),
    ),

    // 1. Biru Langit (Sky Blue - Lingkaran 1)
    AppColorPreset(
      key: 'sky_blue',
      name: 'Biru Langit',
      subtitle: 'Cerah, modern, & profesional',
      primaryLight: Color(0xFF0284C7),
      primaryDark: Color(0xFF38BDF8),
      primaryContainerLight: Color(0xFFE0F2FE),
      primaryContainerDark: Color(0xFF075985),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF082F49),
      onPrimaryContainerLight: Color(0xFF0369A1),
      onPrimaryContainerDark: Color(0xFFBAE6FD),
      previewColor: Color(0xFF00A3FF),
      secondaryLight: Color(0xFF4F616E),
      secondaryDark: Color(0xFFB6C9D8),
      secondaryContainerLight: Color(0xFFD2E5F5),
      secondaryContainerDark: Color(0xFF374955),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF21323E),
      onSecondaryContainerLight: Color(0xFF0A1D28),
      onSecondaryContainerDark: Color(0xFFD2E5F5),
      tertiaryLight: Color(0xFF63597C),
      tertiaryDark: Color(0xFFCDC0E9),
      tertiaryContainerLight: Color(0xFFE9DCFF),
      tertiaryContainerDark: Color(0xFF4B4263),

      surfaceLight: Color(0xFFF6FAFD),
      surfaceDimLight: Color(0xFFECF3F9),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFEFF5FA),
      surfaceContainerLight: Color(0xFFE5F0F8),
      surfaceContainerHighLight: Color(0xFFDBEAF4),
      surfaceContainerHighestLight: Color(0xFFD1E4F0),
      outlineLight: Color(0xFF6F7982),
      outlineVariantLight: Color(0xFFBFCAD5),

      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF14202B),
      surfaceContainerLowestDark: Color(0xFF060B10),
      surfaceContainerLowDark: Color(0xFF0C1622),
      surfaceContainerDark: Color(0xFF122132),
      surfaceContainerHighDark: Color(0xFF1A2D42),
      surfaceContainerHighestDark: Color(0xFF233A52),
      outlineDark: Color(0xFF88939D),
      outlineVariantDark: Color(0xFF27384A),
    ),

    // 2. Tosca Mint (Aqua - Lingkaran 2)
    AppColorPreset(
      key: 'mint',
      name: 'Tosca Mint',
      subtitle: 'Sejuk, jernih, & menyegarkan',
      primaryLight: Color(0xFF0D9488),
      primaryDark: Color(0xFF2DD4BF),
      primaryContainerLight: Color(0xFFCCFBF1),
      primaryContainerDark: Color(0xFF115E59),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF042F2E),
      onPrimaryContainerLight: Color(0xFF134E4A),
      onPrimaryContainerDark: Color(0xFF99F6E4),
      previewColor: Color(0xFF2DD4BF),
      secondaryLight: Color(0xFF4A635E),
      secondaryDark: Color(0xFFB1CCC5),
      secondaryContainerLight: Color(0xFFCCE8E1),
      secondaryContainerDark: Color(0xFF334B46),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF1D3530),
      onSecondaryContainerLight: Color(0xFF05201C),
      onSecondaryContainerDark: Color(0xFFCCE8E1),
      tertiaryLight: Color(0xFF456179),
      tertiaryDark: Color(0xFFACC9E6),
      tertiaryContainerLight: Color(0xFFCDE5FF),
      tertiaryContainerDark: Color(0xFF2D4960),

      surfaceLight: Color(0xFFF5FAF9),
      surfaceDimLight: Color(0xFFECF4F2),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFEEF6F4),
      surfaceContainerLight: Color(0xFFE4F2EE),
      surfaceContainerHighLight: Color(0xFFDAECE7),
      surfaceContainerHighestLight: Color(0xFFD0E6E1),
      outlineLight: Color(0xFF6E7A77),
      outlineVariantLight: Color(0xFFBECDC9),

      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF132321),
      surfaceContainerLowestDark: Color(0xFF050E0C),
      surfaceContainerLowDark: Color(0xFF0B1715),
      surfaceContainerDark: Color(0xFF112320),
      surfaceContainerHighDark: Color(0xFF18302B),
      surfaceContainerHighestDark: Color(0xFF203E38),
      outlineDark: Color(0xFF879490),
      outlineVariantDark: Color(0xFF253935),
    ),

    // 3. Hijau Lemon (Lime - Lingkaran 3)
    AppColorPreset(
      key: 'lime',
      name: 'Hijau Lemon',
      subtitle: 'Energik, segar, & ceria',
      primaryLight: Color(0xFF4D7C0F),
      primaryDark: Color(0xFFA3E635),
      primaryContainerLight: Color(0xFFECFCCB),
      primaryContainerDark: Color(0xFF365314),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF1A2E05),
      onPrimaryContainerLight: Color(0xFF365314),
      onPrimaryContainerDark: Color(0xFFD9F99D),
      previewColor: Color(0xFFA3E635),
      secondaryLight: Color(0xFF55624C),
      secondaryDark: Color(0xFFBDCBB0),
      secondaryContainerLight: Color(0xFFD9E7CC),
      secondaryContainerDark: Color(0xFF3D4A35),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF273420),
      onSecondaryContainerLight: Color(0xFF131F0D),
      onSecondaryContainerDark: Color(0xFFD9E7CC),
      tertiaryLight: Color(0xFF386663),
      tertiaryDark: Color(0xFFA0D0CD),
      tertiaryContainerLight: Color(0xFFBCECE9),
      tertiaryContainerDark: Color(0xFF1F4E4B),

      surfaceLight: Color(0xFFF8FAF4),
      surfaceDimLight: Color(0xFFEFF3EB),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFF2F6ED),
      surfaceContainerLight: Color(0xFFEAF1E4),
      surfaceContainerHighLight: Color(0xFFE2EBDC),
      surfaceContainerHighestLight: Color(0xFFD9E5D3),
      outlineLight: Color(0xFF737A6F),
      outlineVariantLight: Color(0xFFC3C9BE),

      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF1A2613),
      surfaceContainerLowestDark: Color(0xFF070E04),
      surfaceContainerLowDark: Color(0xFF0F1A0A),
      surfaceContainerDark: Color(0xFF16250F),
      surfaceContainerHighDark: Color(0xFF1F3216),
      surfaceContainerHighestDark: Color(0xFF2A421F),
      outlineDark: Color(0xFF8D9588),
      outlineVariantDark: Color(0xFF2C3E21),
    ),

    // 4. Kuning Mentari (Sunflower - Lingkaran 4)
    AppColorPreset(
      key: 'yellow',
      name: 'Kuning Mentari',
      subtitle: 'Hangat, terang, & penuh semangat',
      primaryLight: Color(0xFFB45309),
      primaryDark: Color(0xFFFACC15),
      primaryContainerLight: Color(0xFFFEF9C3),
      primaryContainerDark: Color(0xFF713F12),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF422006),
      onPrimaryContainerLight: Color(0xFF713F12),
      onPrimaryContainerDark: Color(0xFFFEF08A),
      previewColor: Color(0xFFFACC15),
      secondaryLight: Color(0xFF6C5D47),
      secondaryDark: Color(0xFFD8C4A9),
      secondaryContainerLight: Color(0xFFF5E0C4),
      secondaryContainerDark: Color(0xFF534631),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF3B2F1C),
      onSecondaryContainerLight: Color(0xFF241A09),
      onSecondaryContainerDark: Color(0xFFF5E0C4),
      tertiaryLight: Color(0xFF4F6546),
      tertiaryDark: Color(0xFFB5CDAA),
      tertiaryContainerLight: Color(0xFFD1EAC5),
      tertiaryContainerDark: Color(0xFF384D30),

      surfaceLight: Color(0xFFFCFAF4),
      surfaceDimLight: Color(0xFFF6F2E8),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFF7F4EA),
      surfaceContainerLight: Color(0xFFF2ECE0),
      surfaceContainerHighLight: Color(0xFFECE4D5),
      surfaceContainerHighestLight: Color(0xFFE5DCCB),
      outlineLight: Color(0xFF7C766B),
      outlineVariantLight: Color(0xFFCDC6BA),

      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF262013),
      surfaceContainerLowestDark: Color(0xFF0F0C05),
      surfaceContainerLowDark: Color(0xFF191509),
      surfaceContainerDark: Color(0xFF241E0E),
      surfaceContainerHighDark: Color(0xFF312914),
      surfaceContainerHighestDark: Color(0xFF40361C),
      outlineDark: Color(0xFF948E83),
      outlineVariantDark: Color(0xFF3C3322),
    ),

    // 5. Oranye Senja (Tangerine - Lingkaran 5)
    AppColorPreset(
      key: 'orange',
      name: 'Oranye Senja',
      subtitle: 'Hangat, antusias, & ramah',
      primaryLight: Color(0xFFEA580C),
      primaryDark: Color(0xFFFB923C),
      primaryContainerLight: Color(0xFFFFEDD5),
      primaryContainerDark: Color(0xFF7C2D12),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF431407),
      onPrimaryContainerLight: Color(0xFF9A3412),
      onPrimaryContainerDark: Color(0xFFFED7AA),
      previewColor: Color(0xFFFB923C),
      secondaryLight: Color(0xFF74584C),
      secondaryDark: Color(0xFFE3BFA8),
      secondaryContainerLight: Color(0xFFFFDBCF),
      secondaryContainerDark: Color(0xFF5B4136),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF422C21),
      onSecondaryContainerLight: Color(0xFF2B170E),
      onSecondaryContainerDark: Color(0xFFFFDBCF),
      tertiaryLight: Color(0xFF675F36),
      tertiaryDark: Color(0xFFD2C798),
      tertiaryContainerLight: Color(0xFFEEE3B2),
      tertiaryContainerDark: Color(0xFF4E4721),

      surfaceLight: Color(0xFFFCF8F5),
      surfaceDimLight: Color(0xFFF7EFE9),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFF9F1EB),
      surfaceContainerLight: Color(0xFFF3E7DF),
      surfaceContainerHighLight: Color(0xFFEDDDD3),
      surfaceContainerHighestLight: Color(0xFFE6D3C7),
      outlineLight: Color(0xFF7E736E),
      outlineVariantLight: Color(0xFFD0C3BC),

      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF271A13),
      surfaceContainerLowestDark: Color(0xFF100805),
      surfaceContainerLowDark: Color(0xFF1A0E08),
      surfaceContainerDark: Color(0xFF26170E),
      surfaceContainerHighDark: Color(0xFF342014),
      surfaceContainerHighestDark: Color(0xFF442B1C),
      outlineDark: Color(0xFF968B84),
      outlineVariantDark: Color(0xFF402A1F),
    ),

    // 6. Merah Coral (Salmon - Lingkaran 6, Terpilih di Gambar)
    AppColorPreset(
      key: 'coral',
      name: 'Merah Coral',
      subtitle: 'Elegan, dinamis, & memikat (Sesuai Gambar)',
      primaryLight: Color(0xFFE11D48),
      primaryDark: Color(0xFFFB7185),
      primaryContainerLight: Color(0xFFFFE4E6),
      primaryContainerDark: Color(0xFF881337),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF4C0519),
      onPrimaryContainerLight: Color(0xFF9F1239),
      onPrimaryContainerDark: Color(0xFFFECDD3),
      previewColor: Color(0xFFFB7185),
      secondaryLight: Color(0xFF78565B),
      secondaryDark: Color(0xFFE7BDC1),
      secondaryContainerLight: Color(0xFFFFD9DC),
      secondaryContainerDark: Color(0xFF5E3F43),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF452A2E),
      onSecondaryContainerLight: Color(0xFF2E151A),
      onSecondaryContainerDark: Color(0xFFFFD9DC),
      tertiaryLight: Color(0xFF7A583B),
      tertiaryDark: Color(0xFFECBE99),
      tertiaryContainerLight: Color(0xFFFFDDBF),
      tertiaryContainerDark: Color(0xFF604126),

      surfaceLight: Color(0xFFFCF6F7),
      surfaceDimLight: Color(0xFFF7ECED),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFF8EFF1),
      surfaceContainerLight: Color(0xFFF3E3E6),
      surfaceContainerHighLight: Color(0xFFEED8DC),
      surfaceContainerHighestLight: Color(0xFFE7CCD1),
      outlineLight: Color(0xFF7E7275),
      outlineVariantLight: Color(0xFFD1C1C4),

      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF281418),
      surfaceContainerLowestDark: Color(0xFF100608),
      surfaceContainerLowDark: Color(0xFF1A0A0E),
      surfaceContainerDark: Color(0xFF271016),
      surfaceContainerHighDark: Color(0xFF36161F),
      surfaceContainerHighestDark: Color(0xFF471E29),
      outlineDark: Color(0xFF978A8D),
      outlineVariantDark: Color(0xFF402129),
    ),

    // 7. Pink Manis (Bubblegum - Lingkaran 7)
    AppColorPreset(
      key: 'pink',
      name: 'Pink Manis',
      subtitle: 'Lembut, manis, & anggun',
      primaryLight: Color(0xFFDB2777),
      primaryDark: Color(0xFFF472B6),
      primaryContainerLight: Color(0xFFFCE7F3),
      primaryContainerDark: Color(0xFF831843),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF500724),
      onPrimaryContainerLight: Color(0xFF9D174D),
      onPrimaryContainerDark: Color(0xFFFBCFE8),
      previewColor: Color(0xFFF472B6),
      secondaryLight: Color(0xFF765666),
      secondaryDark: Color(0xFFE5BDCF),
      secondaryContainerLight: Color(0xFFFFD9E9),
      secondaryContainerDark: Color(0xFF5C3F4E),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF442937),
      onSecondaryContainerLight: Color(0xFF2D1422),
      onSecondaryContainerDark: Color(0xFFFFD9E9),
      tertiaryLight: Color(0xFF775940),
      tertiaryDark: Color(0xFFE7BF9E),
      tertiaryContainerLight: Color(0xFFFFDDC3),
      tertiaryContainerDark: Color(0xFF5D422B),

      surfaceLight: Color(0xFFFCF6F9),
      surfaceDimLight: Color(0xFFF8EDF2),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFF9EFF4),
      surfaceContainerLight: Color(0xFFF4E3EC),
      surfaceContainerHighLight: Color(0xFFEFD8E4),
      surfaceContainerHighestLight: Color(0xFFE9CCDC),
      outlineLight: Color(0xFF7E727A),
      outlineVariantLight: Color(0xFFD1C1CA),

      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF281421),
      surfaceContainerLowestDark: Color(0xFF10060D),
      surfaceContainerLowDark: Color(0xFF1A0A15),
      surfaceContainerDark: Color(0xFF271020),
      surfaceContainerHighDark: Color(0xFF36162C),
      surfaceContainerHighestDark: Color(0xFF481E3B),
      outlineDark: Color(0xFF978992),
      outlineVariantDark: Color(0xFF412134),
    ),

    // 8. Ungu Lavender (Lilac - Lingkaran 8)
    AppColorPreset(
      key: 'lavender',
      name: 'Ungu Lavender',
      subtitle: 'Prestisius, artistik, & damai',
      primaryLight: Color(0xFF7C3AED),
      primaryDark: Color(0xFFA78BFA),
      primaryContainerLight: Color(0xFFEDE9FE),
      primaryContainerDark: Color(0xFF4C1D95),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF2E1065),
      onPrimaryContainerLight: Color(0xFF5B21B6),
      onPrimaryContainerDark: Color(0xFFDDD6FE),
      previewColor: Color(0xFFA78BFA),
      secondaryLight: Color(0xFF625B71),
      secondaryDark: Color(0xFFCBC2DB),
      secondaryContainerLight: Color(0xFFE8DEF8),
      secondaryContainerDark: Color(0xFF4A4458),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF332D41),
      onSecondaryContainerLight: Color(0xFF1E192B),
      onSecondaryContainerDark: Color(0xFFE8DEF8),
      tertiaryLight: Color(0xFF7D5260),
      tertiaryDark: Color(0xFFEFB8C8),
      tertiaryContainerLight: Color(0xFFFFD8E4),
      tertiaryContainerDark: Color(0xFF633B48),

      surfaceLight: Color(0xFFFAF7FD),
      surfaceDimLight: Color(0xFFF3EDFA),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFF4EEFB),
      surfaceContainerLight: Color(0xFFECE3F7),
      surfaceContainerHighLight: Color(0xFFE4D8F3),
      surfaceContainerHighestLight: Color(0xFFDCCDED),
      outlineLight: Color(0xFF787383),
      outlineVariantLight: Color(0xFFC9C3D4),

      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF21192C),
      surfaceContainerLowestDark: Color(0xFF0C0712),
      surfaceContainerLowDark: Color(0xFF140E20),
      surfaceContainerDark: Color(0xFF1E1430),
      surfaceContainerHighDark: Color(0xFF291B40),
      surfaceContainerHighestDark: Color(0xFF352452),
      outlineDark: Color(0xFF918B9D),
      outlineVariantDark: Color(0xFF352948),
    ),

    // 9. Hijau Sage (Celadon - Lingkaran 9)
    AppColorPreset(
      key: 'sage',
      name: 'Hijau Sage',
      subtitle: 'Alami, teduh, & sejuk di mata',
      primaryLight: Color(0xFF43654E),
      primaryDark: Color(0xFF94A89A),
      primaryContainerLight: Color(0xFFD6E6DA),
      primaryContainerDark: Color(0xFF253E2E),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF122317),
      onPrimaryContainerLight: Color(0xFF233B2B),
      onPrimaryContainerDark: Color(0xFFC1DEC7),
      previewColor: Color(0xFF94A89A),
      secondaryLight: Color(0xFF516356),
      secondaryDark: Color(0xFFB8CCBE),
      secondaryContainerLight: Color(0xFFD4E8DA),
      secondaryContainerDark: Color(0xFF394B3F),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF233529),
      onSecondaryContainerLight: Color(0xFF0F2015),
      onSecondaryContainerDark: Color(0xFFD4E8DA),
      tertiaryLight: Color(0xFF3E656B),
      tertiaryDark: Color(0xFFA5CED5),
      tertiaryContainerLight: Color(0xFFC1EBF2),
      tertiaryContainerDark: Color(0xFF244D53),

      surfaceLight: Color(0xFFF7FAF8),
      surfaceDimLight: Color(0xFFEEF3EF),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFF0F6F2),
      surfaceContainerLight: Color(0xFFE7EFE9),
      surfaceContainerHighLight: Color(0xFFDEE8E1),
      surfaceContainerHighestLight: Color(0xFFD5E1D8),
      outlineLight: Color(0xFF717973),
      outlineVariantLight: Color(0xFFC1C9C3),

      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF18231C),
      surfaceContainerLowestDark: Color(0xFF070E0A),
      surfaceContainerLowDark: Color(0xFF0D1711),
      surfaceContainerDark: Color(0xFF132219),
      surfaceContainerHighDark: Color(0xFF1B2E22),
      surfaceContainerHighestDark: Color(0xFF243B2D),
      outlineDark: Color(0xFF8B948D),
      outlineVariantDark: Color(0xFF283B2F),
    ),

    // 10. Krem Pasir (Sand Gold - Lingkaran 10)
    AppColorPreset(
      key: 'sand',
      name: 'Krem Pasir',
      subtitle: 'Klasik, estetik, & hangat',
      primaryLight: Color(0xFFB45309),
      primaryDark: Color(0xFFE8BA78),
      primaryContainerLight: Color(0xFFFEF3C7),
      primaryContainerDark: Color(0xFF78350F),
      onPrimaryLight: Color(0xFFFFFFFF),
      onPrimaryDark: Color(0xFF451A03),
      onPrimaryContainerLight: Color(0xFF78350F),
      onPrimaryContainerDark: Color(0xFFFDE68A),
      previewColor: Color(0xFFE8BA78),
      secondaryLight: Color(0xFF705B40),
      secondaryDark: Color(0xFFDDC3A1),
      secondaryContainerLight: Color(0xFFFBDFAF),
      secondaryContainerDark: Color(0xFF57432A),
      onSecondaryLight: Color(0xFFFFFFFF),
      onSecondaryDark: Color(0xFF3E2D16),
      onSecondaryContainerLight: Color(0xFF271904),
      onSecondaryContainerDark: Color(0xFFFBDFAF),
      tertiaryLight: Color(0xFF53643E),
      tertiaryDark: Color(0xFFBACD9E),
      tertiaryContainerLight: Color(0xFFD6E9B9),
      tertiaryContainerDark: Color(0xFF3C4C28),

      surfaceLight: Color(0xFFFCF9F4),
      surfaceDimLight: Color(0xFFF6F0E6),
      surfaceBrightLight: Color(0xFFFFFFFF),
      surfaceContainerLowestLight: Color(0xFFFFFFFF),
      surfaceContainerLowLight: Color(0xFFF7F2E8),
      surfaceContainerLight: Color(0xFFF1E9DB),
      surfaceContainerHighLight: Color(0xFFEAE0CF),
      surfaceContainerHighestLight: Color(0xFFE3D6C2),
      outlineLight: Color(0xFF7C756B),
      outlineVariantLight: Color(0xFFCDC5B9),

      surfaceDark: Color(0xFF000000),
      surfaceDimDark: Color(0xFF000000),
      surfaceBrightDark: Color(0xFF261D12),
      surfaceContainerLowestDark: Color(0xFF100B05),
      surfaceContainerLowDark: Color(0xFF1A1209),
      surfaceContainerDark: Color(0xFF261B0E),
      surfaceContainerHighDark: Color(0xFF332514),
      surfaceContainerHighestDark: Color(0xFF42311C),
      outlineDark: Color(0xFF958E83),
      outlineVariantDark: Color(0xFF3E3022),
    ),
  ];

  static AppColorPreset _activePreset = presets[0];

  static AppColorPreset get activePreset => _activePreset;

  static AppColorPreset getPreset(String key) {
    // Backward compatibility mapping for previously saved preset keys
    final normalizedKey = switch (key) {
      'ocean' => 'sky_blue',
      'teal' => 'mint',
      'violet' => 'lavender',
      'amber' => 'sand',
      'pink_maroon' => 'coral',
      _ => key,
    };
    return presets.firstWhere(
      (p) => p.key == normalizedKey,
      orElse: () => presets[0],
    );
  }

  static void setPreset(String key) {
    _activePreset = getPreset(key);
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

  // === DYNAMIC SECONDARY ROLES ===
  static Color get secondaryLight =>
      _activePreset.secondaryLight ?? _activePreset.primaryLight.withValues(alpha: 0.85);
  static Color get secondaryDark =>
      _activePreset.secondaryDark ?? _activePreset.primaryDark.withValues(alpha: 0.85);
  static Color get secondaryContainerLight =>
      _activePreset.secondaryContainerLight ?? _activePreset.primaryContainerLight;
  static Color get secondaryContainerDark =>
      _activePreset.secondaryContainerDark ?? _activePreset.primaryContainerDark;
  static Color get onSecondaryLight =>
      _activePreset.onSecondaryLight ?? _activePreset.onPrimaryLight;
  static Color get onSecondaryDark =>
      _activePreset.onSecondaryDark ?? _activePreset.onPrimaryDark;
  static Color get onSecondaryContainerLight =>
      _activePreset.onSecondaryContainerLight ?? _activePreset.onPrimaryContainerLight;
  static Color get onSecondaryContainerDark =>
      _activePreset.onSecondaryContainerDark ?? _activePreset.onPrimaryContainerDark;

  // === DYNAMIC TERTIARY ROLES ===
  static Color get tertiaryLight =>
      _activePreset.tertiaryLight ?? const Color(0xFF0D9488);
  static Color get tertiaryDark =>
      _activePreset.tertiaryDark ?? const Color(0xFF2DD4BF);
  static Color get tertiaryContainerLight =>
      _activePreset.tertiaryContainerLight ?? const Color(0xFFCCFBF1);
  static Color get tertiaryContainerDark =>
      _activePreset.tertiaryContainerDark ?? const Color(0xFF115E59);

  // === MATERIAL 3 TONAL SURFACE CONTAINERS (LIGHT MODE - DYNAMIC ACCENT TINTED) ===
  static Color get surfaceLight =>
      _activePreset.surfaceLight ?? const Color(0xFFF7FBF4);
  static Color get surfaceDimLight =>
      _activePreset.surfaceDimLight ?? const Color(0xFFEFEFE7);
  static Color get surfaceBrightLight =>
      _activePreset.surfaceBrightLight ?? const Color(0xFFFFFFFF);
  static Color get surfaceContainerLowestLight =>
      _activePreset.surfaceContainerLowestLight ?? const Color(0xFFFFFFFF);
  static Color get surfaceContainerLowLight =>
      _activePreset.surfaceContainerLowLight ?? const Color(0xFFF1F7EE);
  static Color get surfaceContainerLight =>
      _activePreset.surfaceContainerLight ?? const Color(0xFFEAF3E6);
  static Color get surfaceContainerHighLight =>
      _activePreset.surfaceContainerHighLight ?? const Color(0xFFE3EDE0);
  static Color get surfaceContainerHighestLight =>
      _activePreset.surfaceContainerHighestLight ?? const Color(0xFFDCE7D9);

  // === MATERIAL 3 TONAL SURFACE CONTAINERS (DARK MODE - DYNAMIC ACCENT TINTED) ===
  static Color get surfaceDark =>
      _activePreset.surfaceDark ?? const Color(0xFF000000);
  static Color get surfaceDimDark =>
      _activePreset.surfaceDimDark ?? const Color(0xFF000000);
  static Color get surfaceBrightDark =>
      _activePreset.surfaceBrightDark ?? const Color(0xFF1B271B);
  static Color get surfaceContainerLowestDark =>
      _activePreset.surfaceContainerLowestDark ?? const Color(0xFF060D06);
  static Color get surfaceContainerLowDark =>
      _activePreset.surfaceContainerLowDark ?? const Color(0xFF0D180D);
  static Color get surfaceContainerDark =>
      _activePreset.surfaceContainerDark ?? const Color(0xFF142414);
  static Color get surfaceContainerHighDark =>
      _activePreset.surfaceContainerHighDark ?? const Color(0xFF1C2F1C);
  static Color get surfaceContainerHighestDark =>
      _activePreset.surfaceContainerHighestDark ?? const Color(0xFF243B24);

  // Backward-compatible Card colors (dynamically linked to active preset's tonal surface)
  static Color get cardGlassLight => surfaceContainerLowLight;
  static Color get cardGlassDark => surfaceContainerDark;

  // === OUTLINE & BORDERS (DYNAMIC) ===
  static Color get outlineLight =>
      _activePreset.outlineLight ?? const Color(0xFF727970);
  static Color get outlineVariantLight =>
      _activePreset.outlineVariantLight ?? const Color(0xFFC2CBC0);
  static Color get outlineDark =>
      _activePreset.outlineDark ?? const Color(0xFF8C958A);
  static Color get outlineVariantDark =>
      _activePreset.outlineVariantDark ?? const Color(0xFF2B3C2B);

  // === ACCENT PALETTE ===
  static const Color amberAccent = Color(0xFFF59E0B);
  static const Color skyBlueAccent = Color(0xFF0284C7);
  static const Color violetAccent = Color(0xFF7C3AED);
  static const Color roseDanger = Color(0xFFDC2626);

  // === SEMANTIC STATUS PRESENSI TONAL (H, I, S, A, D) ===
  static const Color hadirTextLight = Color(0xFF0B6623);
  static const Color hadirBgLight = Color(0xFFD1F4DB);
  static const Color hadirBorderLight = Color(0xFFA3E7B7);
  static const Color hadirTextDark = Color(0xFF4ADE80);
  static const Color hadirBgDark = Color(0xFF0F3818);
  static const Color hadirBorderDark = Color(0xFF1B5E20);

  static const Color izinTextLight = Color(0xFF1E40AF);
  static const Color izinBgLight = Color(0xFFDBEAFE);
  static const Color izinBorderLight = Color(0xFF93C5FD);
  static const Color izinTextDark = Color(0xFF60A5FA);
  static const Color izinBgDark = Color(0xFF172554);
  static const Color izinBorderDark = Color(0xFF1E3A8A);

  static const Color sakitTextLight = Color(0xFF92400E);
  static const Color sakitBgLight = Color(0xFFFEF3C7);
  static const Color sakitBorderLight = Color(0xFFFDE68A);
  static const Color sakitTextDark = Color(0xFFFBBF24);
  static const Color sakitBgDark = Color(0xFF451A03);
  static const Color sakitBorderDark = Color(0xFF78350F);

  static const Color alphaTextLight = Color(0xFF991B1B);
  static const Color alphaBgLight = Color(0xFFFEE2E2);
  static const Color alphaBorderLight = Color(0xFFFCA5A5);
  static const Color alphaTextDark = Color(0xFFF87171);
  static const Color alphaBgDark = Color(0xFF450A0A);
  static const Color alphaBorderDark = Color(0xFF7F1D1D);

  static const Color dispensasiTextLight = Color(0xFF6B21A8);
  static const Color dispensasiBgLight = Color(0xFFF3E8FF);
  static const Color dispensasiBorderLight = Color(0xFFD8B4FE);
  static const Color dispensasiTextDark = Color(0xFFC084FC);
  static const Color dispensasiBgDark = Color(0xFF3B0764);
  static const Color dispensasiBorderDark = Color(0xFF581C87);
}
