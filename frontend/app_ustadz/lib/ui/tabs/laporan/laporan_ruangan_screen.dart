import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/auth_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../penilaian/leger_screen.dart';
import 'laporan_pelanggaran_screen.dart';
import 'laporan_presensi_murid_screen.dart';
import 'laporan_presensi_ustadz_screen.dart';

class LaporanRuanganScreen extends StatelessWidget {
  const LaporanRuanganScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final user = context.watch<AuthProvider>().user;
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;

    final ruanganName = user?.ruanganWali ?? 'Ruangan Binaan';

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Laporan $ruanganName',
        subtitleText: 'Rekapitulasi Kelas Binaan',
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
        children: [
          // 1. BANNER IDENTITAS KELAS BINAAN
          GlassCard(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Container(
                  width: 46,
                  height: 46,
                  decoration: BoxDecoration(
                    color: primary.withValues(alpha: isDark ? 0.2 : 0.12),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: primary.withValues(alpha: 0.3)),
                  ),
                  child: Icon(
                    Icons.admin_panel_settings_rounded,
                    size: 24,
                    color: primary,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(
                            child: Text(
                              ruanganName,
                              style: const TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.bold,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 7,
                              vertical: 2.5,
                            ),
                            decoration: BoxDecoration(
                              color: primary.withValues(
                                alpha: isDark ? 0.25 : 0.15,
                              ),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              'Wali Ruangan',
                              style: TextStyle(
                                fontSize: 9.5,
                                fontWeight: FontWeight.bold,
                                color: primary,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'Rekapitulasi data & laporan terpadu kelas binaan Anda',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark
                              ? const Color(0xFF94A3B8)
                              : const Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // 2. HEADER SECTION
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Daftar Laporan Ruangan ($ruanganName)',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(
                '4 Laporan Tersedia',
                style: TextStyle(
                  fontSize: 11,
                  color: isDark
                      ? const Color(0xFF94A3B8)
                      : const Color(0xFF64748B),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // 3. DAFTAR KARTU LAPORAN
          // 3.1 Presensi Murid Kelas Binaan
          _buildReportCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.how_to_reg_rounded,
            badgeText: 'Kehadiran Murid',
            title: 'Presensi Murid',
            subtitle:
                'Rekapitulasi kehadiran murid, hari efektif, & detail kehadiran per murid kelas binaan.',
            onTap: () {
              HapticHelper.light();
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => LaporanPresensiMuridScreen(
                    initialRuanganId: user?.ruanganWaliId,
                  ),
                ),
              );
            },
          ),
          const SizedBox(height: 10),

          // 3.2 Presensi Ustadz Pengajar di Ruangan
          _buildReportCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.badge_rounded,
            badgeText: 'Jam Mengajar',
            title: 'Presensi Ustadz',
            subtitle:
                'Rekapitulasi jam mengajar, sesi kehadiran & riwayat ustadz pengajar di kelas binaan.',
            onTap: () {
              HapticHelper.light();
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => LaporanPresensiUstadzScreen(
                    initialRuanganId: user?.ruanganWaliId,
                    isRuanganBinaan: true,
                  ),
                ),
              );
            },
          ),
          const SizedBox(height: 10),

          // 3.3 Laporan Ujian & Leger Kelas
          _buildReportCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.assessment_rounded,
            badgeText: 'Leger & Nilai',
            title: 'Laporan Ujian',
            subtitle:
                'Leger nilai ujian, rata-rata kelas, peringkat bintang pelajar, dan ketuntasan ujian Murid.',
            onTap: () {
              HapticHelper.light();
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => LegerRuanganScreen(
                    ruanganId: user?.ruanganWaliId ?? 0,
                    ruanganName: ruanganName,
                  ),
                ),
              );
            },
          ),
          const SizedBox(height: 10),

          // 3.4 Laporan Buku Kasus & Pelanggaran
          _buildReportCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.rule_folder_rounded,
            badgeText: 'Buku Kasus',
            title: 'Laporan Pelanggaran',
            subtitle:
                'Buku kasus disiplin, akumulasi poin sanksi, dan leaderboard kedisiplinan murid.',
            onTap: () {
              HapticHelper.light();
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => const LaporanPelanggaranScreen(),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildReportCard({
    required BuildContext context,
    required bool isDark,
    required Color primary,
    required IconData icon,
    required String badgeText,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return GlassCard(
      padding: EdgeInsets.zero,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: primary.withValues(alpha: isDark ? 0.18 : 0.12),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: primary.withValues(alpha: 0.35)),
                ),
                child: Center(child: Icon(icon, size: 24, color: primary)),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Flexible(
                          child: Text(
                            title,
                            style: const TextStyle(
                              fontSize: 14.5,
                              fontWeight: FontWeight.bold,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 7,
                            vertical: 2.5,
                          ),
                          decoration: BoxDecoration(
                            color: primary.withValues(
                              alpha: isDark ? 0.22 : 0.12,
                            ),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            badgeText,
                            style: TextStyle(
                              fontSize: 9.5,
                              fontWeight: FontWeight.bold,
                              color: primary,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 5),
                    Text(
                      subtitle,
                      style: TextStyle(
                        fontSize: 11.5,
                        height: 1.35,
                        color: isDark
                            ? const Color(0xFF94A3B8)
                            : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 6),
              Padding(
                padding: const EdgeInsets.only(top: 10),
                child: Icon(
                  Icons.chevron_right_rounded,
                  size: 20,
                  color: isDark
                      ? const Color(0xFF64748B)
                      : const Color(0xFF94A3B8),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
