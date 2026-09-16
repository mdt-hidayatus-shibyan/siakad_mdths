import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/auth_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../penilaian/penilaian_tab.dart';
import 'laporan_presensi_murid_screen.dart';
import 'laporan_presensi_ustadz_screen.dart';

class LaporanPengampuScreen extends StatelessWidget {
  const LaporanPengampuScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final user = context.watch<AuthProvider>().user;
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;

    return Scaffold(
      appBar: const CustomAppBar(
        titleText: 'Laporan Pengampu',
        subtitleText: 'Aktivitas & Tugas Mengajar',
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
        children: [
          // 1. BANNER IDENTITAS PENGAMPU
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
                  child: Icon(Icons.school_rounded, size: 24, color: primary),
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
                              user?.name ?? 'Ustadz Pengampu',
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
                              'Guru Pengampu',
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
                        'Rekapitulasi aktivitas mengajar & presensi mata pelajaran yang Anda ampu',
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
              const Text(
                'Laporan Aktivitas & Pengajaran',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
              ),
              Text(
                '3 Laporan Tersedia',
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

          // 3. DAFTAR KARTU LAPORAN PENGAMPU
          // 3.1 Presensi Murid Mapel Diampu
          _buildReportCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.how_to_reg_rounded,
            badgeText: 'Presensi Murid',
            title: 'Presensi Murid Pengampu',
            subtitle:
                'Rekapitulasi kehadiran murid pada jadwal mata pelajaran dan kelas yang Anda ajar.',
            onTap: () {
              HapticHelper.light();
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => const LaporanPresensiMuridScreen(),
                ),
              );
            },
          ),
          const SizedBox(height: 10),

          // 3.2 Presensi Mengajar Saya (Pribadi)
          _buildReportCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.badge_rounded,
            badgeText: 'Kehadiran Pribadi',
            title: 'Presensi Mengajar Saya',
            subtitle:
                'Rekapitulasi sesi mengajar, persentase kehadiran, dan riwayat jam masuk/keluar Anda.',
            onTap: () {
              HapticHelper.light();
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) =>
                      const LaporanPresensiUstadzScreen(isRuanganBinaan: false),
                ),
              );
            },
          ),
          const SizedBox(height: 10),

          // 3.3 Nilai Ujian yang Telah Diinput
          _buildReportCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.assessment_rounded,
            badgeText: 'Rekap Nilai',
            title: 'Nilai Ujian yang Diinput',
            subtitle:
                'Rekapitulasi dan form penilaian ujian murid pada mata pelajaran yang Anda ampu.',
            onTap: () {
              HapticHelper.light();
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const PenilaianTab()),
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
