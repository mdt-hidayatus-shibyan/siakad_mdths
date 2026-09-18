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

  /// Daftar Penambahan Fitur Baru
  static const List<ChangelogEntry> newFeatures = [
    ChangelogEntry(
      title: 'Modul Catatan & Keluhan Ustadz',
      description:
          'Fitur pelaporan perkembangan murid, fasilitas madrasah, dan evaluasi umum dengan lampiran bukti foto kamera/galeri serta pilihan form select ruangan kelas.',
    ),
    ChangelogEntry(
      title: 'Menu Cepat & Pengumuman',
      description:
          'Akses cepat pada beranda untuk Kalender Akademik, Referensi Sanksi, Mapel, Jadwal Pelajaran, Catatan, Pengumuman, Laporan Pengampu, dan Tabungan.',
    ),
    ChangelogEntry(
      title: 'Presensi & Jurnal Mengajar Harian',
      description:
          'Pencatatan kehadiran murid dan ustadz secara realtime dengan verifikasi status dan jurnal materi KBM.',
    ),
    ChangelogEntry(
      title: 'Penilaian & Rekap Leger Nilai',
      description:
          'Pengelolaan nilai tugas, harian, UTS, UAS, dan rekapitulasi leger nilai murid per mata pelajaran.',
    ),
    ChangelogEntry(
      title: 'Buku Kasus & Disiplin Murid',
      description:
          'Pencatatan pelanggaran kedisiplinan dan katalog referensi pedoman sanksi pembinaan murid.',
    ),
    ChangelogEntry(
      title: 'Kas Ruangan & Tabungan',
      description:
          'Transparansi pembukuan kas kelas bagi wali ruangan dan monitoring tabungan pribadi ustadz/murid.',
    ),
    ChangelogEntry(
      title: 'Kustomisasi Tema & Tampilan',
      description:
          'Dukungan Mode Gelap Super AMOLED, Mode Terang, dan pilihan palet warna aksen aplikasi.',
    ),
  ];

  /// Daftar Perbaikan & Peningkatan Sistem
  static const List<ChangelogEntry> improvements = [
    ChangelogEntry(
      title: 'Penyempurnaan Form Pemilihan Ruangan',
      description:
          'Dropdown ruangan kelas kini diurutkan berdasarkan urutan level secara rapi dan terhubung otomatis dengan ID ruangan murid.',
    ),
    ChangelogEntry(
      title: 'Desain Tombol AppBar Melingkar',
      description:
          'Standardisasi tombol navigasi dan aksi pada AppBar menjadi Circular Button dengan pewarnaan tema konsisten.',
    ),
    ChangelogEntry(
      title: 'Standardisasi Istilah Baku',
      description:
          'Penggunaan istilah "Murid" secara konsisten di seluruh antarmuka dan basis data aplikasi.',
    ),
    ChangelogEntry(
      title: 'Optimasi Jaringan & Keamanan API',
      description:
          'Peningkatan kecepatan pengambilan data REST API dan keamanan sesi login menggunakan token Sanctum.',
    ),
  ];

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
