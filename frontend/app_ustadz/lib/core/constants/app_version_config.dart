import 'package:flutter/material.dart';

/// Model item catatan rilis (changelog)
class ChangelogEntry {
  final String title;
  final String description;

  const ChangelogEntry({required this.title, required this.description});
}

/// Model item detail informasi pengembang
class DeveloperInfoEntry {
  final IconData icon;
  final String label;
  final String value;

  const DeveloperInfoEntry({
    required this.icon,
    required this.label,
    required this.value,
  });
}

/// ============================================================================
/// KONFIGURASI VERSI, CHANGELOG & PENGEMBANG (SINGLE SOURCE OF TRUTH)
/// ============================================================================
/// Cukup ubah nilai di file ini ketika ada pembaruan/update versi baru.
/// Seluruh tampilan di "Tentang Aplikasi" otomatis mengikuti data di sini.
/// ============================================================================
class AppVersionConfig {
  AppVersionConfig._();

  // ---------------------------------------------------------------------------
  // 1. INFORMASI & VERSI APLIKASI
  // ---------------------------------------------------------------------------
  static const String appTitle = 'Ustadz - MDTHS';
  static const String appSubtitle = 'MDT Hidayatus Shibyan';
  static const String version = '1.0.0';
  static const String buildNumber = '2026.09';
  static const String releaseDate = 'September 2026';
  static const String statusBadge = 'Versi Terbaru';
  static const bool isLatest = true;

  // ---------------------------------------------------------------------------
  // 2. CATATAN RILIS (CHANGELOG)
  // ---------------------------------------------------------------------------
  static const String releaseSubtitle = 'Rilis Perdana • September 2026';
  static const String releaseBadge = 'Rilis Saat Ini';

  /// Daftar Penambahan Fitur Baru (Kosong secara default, diisi via API / rilis aktif)
  static const List<ChangelogEntry> newFeatures = [];

  /// Daftar Perbaikan & Peningkatan Sistem (Kosong secara default, diisi via API / rilis aktif)
  static const List<ChangelogEntry> improvements = [];

  // ---------------------------------------------------------------------------
  // 3. INFORMASI PENGEMBANG (DEVELOPER)
  // ---------------------------------------------------------------------------
  static const String devName = 'Mikyal Adly Ghoffar Hasin';
  static const String devRole = 'Lead Developer & Tim IT';
  static const String devInstitution = 'MDT Hidayatus Shibyan';
  static const String devDescription =
      'Aplikasi ini dirancang dan dikembangkan untuk mendukung digitalisasi tata kelola madrasah, presensi KBM, evaluasi catatan murid, serta transparansi pelaporan terpadu.';

  static const List<DeveloperInfoEntry> devDetails = [
    DeveloperInfoEntry(
      icon: Icons.developer_mode_rounded,
      label: 'Framework & Bahasa',
      value: 'Flutter (Dart)',
    ),
    DeveloperInfoEntry(
      icon: Icons.dns_rounded,
      label: 'Backend & Server',
      value: 'Laravel REST API & MySQL',
    ),
    DeveloperInfoEntry(
      icon: Icons.security_rounded,
      label: 'Keamanan Autentikasi',
      value: 'Laravel Sanctum Token',
    ),
    DeveloperInfoEntry(
      icon: Icons.domain_rounded,
      label: 'Lembaga',
      value: 'MDT Hidayatus Shibyan',
    ),
  ];

  static const List<String> techStacks = [
    'Flutter',
    'Dart',
    'Laravel',
    'REST API',
    'MySQL',
    'Provider',
  ];

  // ---------------------------------------------------------------------------
  // 4. FOOTER & HAK CIPTA
  // ---------------------------------------------------------------------------
  static const String copyrightYear = '2026';
  static const String copyrightOwner = 'MDT Hidayatus Shibyan';
  static const String copyrightSubtitle =
      'All Rights Reserved • SIAKAD MDTHS Mobile';
}
