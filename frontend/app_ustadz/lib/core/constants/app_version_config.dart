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
/// KONFIGURASI VERSI, CHANGELOG & PENGEMBANG (MANUAL & MANDIRI)
/// ============================================================================
/// File ini adalah Single Source of Truth untuk informasi versi aplikasi.
/// Tidak terhubung ke server API agar data yang tampil di aplikasi pengguna
/// selalu akurat 100% sesuai dengan versi aplikasi yang sedang terpasang di HP.
///
/// 📝 PETUNJUK PENGUBAHAN:
/// 1. Untuk mengubah versi: ganti nilai [version], [buildNumber], & [releaseDate].
/// 2. Untuk menambah fitur baru: tambahkan entri [ChangelogEntry] pada [newFeatures].
/// 3. Untuk menambah perbaikan: tambahkan entri [ChangelogEntry] pada [improvements].
/// ============================================================================
class AppVersionConfig {
  AppVersionConfig._();

  // ---------------------------------------------------------------------------
  // 1. INFORMASI & NOMOR VERSI APLIKASI
  // ---------------------------------------------------------------------------
  static const String appTitle = 'Ustadz - MDTHS';
  static const String appSubtitle = 'MDT Hidayatus Shibyan';
  static const String version = '1.8.2';
  static const String buildNumber = '2026.09.11';
  static const String releaseDate = 'September 2026';
  static const String statusBadge = 'Versi Rilis';
  static const bool isLatest = true;

  // ---------------------------------------------------------------------------
  // 2. CATATAN RILIS (CHANGELOG)
  // ---------------------------------------------------------------------------
  static const String releaseSubtitle = 'Stable Release - September 2026';
  static const String releaseBadge = 'Versi Aktif';

  /// 🌟 DAFTAR PENAMBAHAN FITUR BARU
  /// Ubah / tambahkan fitur baru di sini:
  static const List<ChangelogEntry> newFeatures = [
    ChangelogEntry(
      title: 'Presensi Event',
      description:
          'Menambahkan fitur presensi untuk event khusus, memungkinkan ustadz dan murid untuk melakukan presensi secara digital pada kegiatan tertentu di madrasah.',
    ),
    ChangelogEntry(
      title: 'Signal Indikator Koneksi',
      description:
          'Menambahkan indikator sinyal koneksi di App_Bar aplikasi, sehingga pengguna dapat melihat kualitas koneksi internet mereka saat menggunakan aplikasi.',
    ),
  ];

  /// 🛠️ DAFTAR PERBAIKAN & PENINGKATAN SISTEM
  /// Ubah / tambahkan perbaikan bug atau peningkatan sistem di sini:
  static const List<ChangelogEntry> improvements = [
    ChangelogEntry(
      title: 'Perbaikan Bug',
      description:
          'Perbaikan bug minor dan peningkatan stabilitas aplikasi untuk memastikan pengalaman pengguna yang lebih lancar dan bebas dari gangguan teknis.',
    ),
  ];

  // ---------------------------------------------------------------------------
  // 3. INFORMASI PENGEMBANG (DEVELOPER)
  // ---------------------------------------------------------------------------
  static const String devName = 'Mikyal Adly Ghoffar Hasin';
  static const String devRole = 'Sekretaris & Pengembang Sistem Informasi';
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
