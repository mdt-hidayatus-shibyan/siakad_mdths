import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/app_version_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';

class TentangAplikasiScreen extends StatefulWidget {
  const TentangAplikasiScreen({super.key});

  @override
  State<TentangAplikasiScreen> createState() => _TentangAplikasiScreenState();
}

class _TentangAplikasiScreenState extends State<TentangAplikasiScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AppVersionProvider>().fetchAppVersion();
    });
  }

  Future<void> _handleRefresh() async {
    HapticHelper.light();
    await context.read<AppVersionProvider>().fetchAppVersion(refresh: true);
    HapticHelper.medium();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;

    final versionProvider = context.watch<AppVersionProvider>();
    final versionData = versionProvider.appVersion;

    return Scaffold(
      appBar: const CustomAppBar(
        titleText: 'Tentang Aplikasi',
        subtitleText: 'Versi & Catatan Pembaruan',
      ),
      body: RefreshIndicator(
        onRefresh: _handleRefresh,
        color: primaryColor,
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          physics: const AlwaysScrollableScrollPhysics(
            parent: BouncingScrollPhysics(),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Loading Indicator banner jika sedang sinkronisasi
              if (versionProvider.isLoading)
                Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Center(
                    child: SizedBox(
                      height: 2,
                      child: LinearProgressIndicator(
                        backgroundColor: primaryColor.withValues(alpha: 0.1),
                        valueColor: AlwaysStoppedAnimation<Color>(primaryColor),
                      ),
                    ),
                  ),
                ),

              // 1. HERO HEADER: Logo, Nama Aplikasi & Badge Versi Aktif
              GlassCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 22,
                ),
                borderRadius: 24,
                child: SizedBox(
                  width: double.infinity,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      // Logo container
                      Container(
                        width: 80,
                        height: 80,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: isDark
                              ? const Color(0xFF1B231B)
                              : Colors.white,
                          border: Border.all(
                            color: primaryColor.withValues(alpha: 0.35),
                            width: 2.5,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: primaryColor.withValues(
                                alpha: isDark ? 0.3 : 0.15,
                              ),
                              blurRadius: 18,
                              offset: const Offset(0, 5),
                            ),
                          ],
                        ),
                        child: ClipOval(
                          child: Padding(
                            padding: const EdgeInsets.all(8),
                            child: Image.asset(
                              'assets/logo_mdt.png',
                              fit: BoxFit.contain,
                              errorBuilder: (_, __, ___) => Icon(
                                Icons.school_rounded,
                                size: 36,
                                color: primaryColor,
                              ),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 14),

                      // App Title & Subtitle
                      Text(
                        versionData.appTitle,
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w900,
                          letterSpacing: -0.4,
                          color: isDark
                              ? Colors.white
                              : const Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        versionData.appSubtitle,
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: isDark ? Colors.white70 : Colors.black54,
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Version & Status Badge
                      Wrap(
                        alignment: WrapAlignment.center,
                        spacing: 8,
                        runSpacing: 6,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 5,
                            ),
                            decoration: BoxDecoration(
                              color: primaryColor.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: primaryColor.withValues(alpha: 0.25),
                              ),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  Icons.verified_rounded,
                                  size: 14,
                                  color: primaryColor,
                                ),
                                const SizedBox(width: 5),
                                Text(
                                  'Versi ${versionData.version} (Build ${versionData.buildNumber})',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                    color: primaryColor,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          if (versionData.isLatest)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 5,
                              ),
                              decoration: BoxDecoration(
                                color: const Color(
                                  0xFF10B981,
                                ).withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: const Color(
                                    0xFF10B981,
                                  ).withValues(alpha: 0.25),
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.check_circle_outline_rounded,
                                    size: 13,
                                    color: Color(0xFF10B981),
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    versionData.statusBadge,
                                    style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: Color(0xFF10B981),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 18),

              // 2. HEADER SECTION: CATATAN RILIS & PEMBARUAN
              Align(
                alignment: Alignment.centerLeft,
                child: Padding(
                  padding: const EdgeInsets.only(left: 4),
                  child: Text(
                    'Catatan Rilis & Pembaruan',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 0.2,
                      color: isDark ? Colors.white70 : Colors.black87,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 8),

              // 3. KARTU CHANGELOG
              GlassCard(
                padding: const EdgeInsets.all(18),
                borderRadius: 24,
                child: SizedBox(
                  width: double.infinity,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Version Header
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(7),
                                decoration: BoxDecoration(
                                  color: primaryColor.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Icon(
                                  Icons.new_releases_rounded,
                                  size: 18,
                                  color: primaryColor,
                                ),
                              ),
                              const SizedBox(width: 10),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Versi ${versionData.version}',
                                    style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.bold,
                                      color: isDark
                                          ? Colors.white
                                          : Colors.black87,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    versionData.releaseSubtitle,
                                    style: TextStyle(
                                      fontSize: 11,
                                      color: isDark
                                          ? Colors.white54
                                          : Colors.black45,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: primaryColor.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              versionData.releaseBadge,
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: primaryColor,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      const Divider(height: 1),
                      const SizedBox(height: 14),

                      // SUB-SECTION: PENAMBAHAN FITUR BARU
                      const Row(
                        children: [
                          Icon(
                            Icons.add_circle_outline_rounded,
                            size: 16,
                            color: Color(0xFF10B981),
                          ),
                          SizedBox(width: 6),
                          Text(
                            'Penambahan Fitur Baru',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF10B981),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      if (versionData.newFeatures.isNotEmpty) ...[
                        ...versionData.newFeatures.map(
                          (feat) => _buildChangelogBullet(
                            title: feat.title,
                            desc: feat.description,
                            isDark: isDark,
                            primaryColor: primaryColor,
                          ),
                        ),
                      ] else ...[
                        Padding(
                          padding: const EdgeInsets.only(left: 4, bottom: 12),
                          child: Text(
                            'Tidak ada penambahan fitur baru pada versi ini.',
                            style: TextStyle(
                              fontSize: 12,
                              fontStyle: FontStyle.italic,
                              color: isDark
                                  ? const Color(0xFF8D9387)
                                  : const Color(0xFF73796E),
                            ),
                          ),
                        ),
                      ],

                      const SizedBox(height: 6),
                      const Divider(height: 1),
                      const SizedBox(height: 14),

                      // SUB-SECTION: PERBAIKAN & PENINGKATAN SISTEM
                      const Row(
                        children: [
                          Icon(
                            Icons.build_circle_outlined,
                            size: 16,
                            color: Color(0xFF3B82F6),
                          ),
                          SizedBox(width: 6),
                          Text(
                            'Perbaikan & Peningkatan Sistem',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF3B82F6),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      if (versionData.improvements.isNotEmpty) ...[
                        ...versionData.improvements.map(
                          (imp) => _buildChangelogBullet(
                            title: imp.title,
                            desc: imp.description,
                            isDark: isDark,
                            primaryColor: primaryColor,
                          ),
                        ),
                      ] else ...[
                        Padding(
                          padding: const EdgeInsets.only(left: 4, bottom: 4),
                          child: Text(
                            'Tidak ada perbaikan & peningkatan sistem pada versi ini.',
                            style: TextStyle(
                              fontSize: 12,
                              fontStyle: FontStyle.italic,
                              color: isDark
                                  ? const Color(0xFF8D9387)
                                  : const Color(0xFF73796E),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 18),

              // 4. HEADER SECTION: INFORMASI PENGEMBANG
              Align(
                alignment: Alignment.centerLeft,
                child: Padding(
                  padding: const EdgeInsets.only(left: 4),
                  child: Text(
                    'Informasi Pengembang',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 0.2,
                      color: isDark ? Colors.white70 : Colors.black87,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 8),

              // 5. KARTU INFORMASI PENGEMBANG
              GlassCard(
                padding: const EdgeInsets.all(18),
                borderRadius: 24,
                child: SizedBox(
                  width: double.infinity,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Developer Header
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: primaryColor.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(
                                color: primaryColor.withValues(alpha: 0.25),
                              ),
                            ),
                            child: Icon(
                              Icons.code_rounded,
                              size: 22,
                              color: primaryColor,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  versionData.devName,
                                  style: TextStyle(
                                    fontSize: 14.5,
                                    fontWeight: FontWeight.bold,
                                    color: isDark
                                        ? Colors.white
                                        : Colors.black87,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  '${versionData.devRole} • ${versionData.devInstitution}',
                                  style: TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w500,
                                    color: isDark
                                        ? Colors.white60
                                        : Colors.black54,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      Text(
                        versionData.devDescription,
                        style: TextStyle(
                          fontSize: 11.5,
                          height: 1.45,
                          color: isDark ? Colors.white70 : Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 14),
                      const Divider(height: 1),
                      const SizedBox(height: 12),

                      // Detail Developer Info
                      ...versionData.devDetails.map(
                        (item) => _buildDevInfoRow(
                          icon: item.icon,
                          label: item.label,
                          value: item.value,
                          primaryColor: primaryColor,
                          isDark: isDark,
                        ),
                      ),

                      const SizedBox(height: 8),

                      // Tech Stack Badges
                      Wrap(
                        spacing: 6,
                        runSpacing: 6,
                        children: versionData.techStacks
                            .map(
                              (stack) =>
                                  _buildTechBadge(stack, primaryColor, isDark),
                            )
                            .toList(),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 28),

              // 6. FOOTER COPYRIGHT
              Center(
                child: Column(
                  children: [
                    Text(
                      '© ${versionData.copyrightYear} ${versionData.copyrightOwner}',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: isDark ? Colors.white38 : Colors.black45,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      versionData.copyrightSubtitle,
                      style: TextStyle(
                        fontSize: 10,
                        color: isDark ? Colors.white24 : Colors.black26,
                      ),
                    ),
                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildChangelogBullet({
    required String title,
    required String desc,
    required bool isDark,
    required Color primaryColor,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            margin: const EdgeInsets.only(top: 5),
            width: 6,
            height: 6,
            decoration: BoxDecoration(
              color: primaryColor,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white : const Color(0xFF1E293B),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  desc,
                  style: TextStyle(
                    fontSize: 11,
                    height: 1.4,
                    color: isDark ? Colors.white60 : Colors.black54,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDevInfoRow({
    required IconData icon,
    required String label,
    required String value,
    required Color primaryColor,
    required bool isDark,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Icon(icon, size: 16, color: primaryColor),
          const SizedBox(width: 8),
          Text(
            label,
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w500,
              color: isDark ? Colors.white60 : Colors.black54,
            ),
          ),
          const Spacer(),
          Text(
            value,
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.bold,
              color: isDark ? Colors.white : const Color(0xFF0F172A),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTechBadge(String name, Color primaryColor, bool isDark) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: primaryColor.withValues(alpha: isDark ? 0.12 : 0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: primaryColor.withValues(alpha: 0.2),
          width: 0.8,
        ),
      ),
      child: Text(
        name,
        style: TextStyle(
          fontSize: 10.5,
          fontWeight: FontWeight.w600,
          color: primaryColor,
        ),
      ),
    );
  }
}
