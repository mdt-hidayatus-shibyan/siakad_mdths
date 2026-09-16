import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/file_download_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/dashboard_model.dart';
import '../../../providers/dashboard_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class PengumumanScreen extends StatefulWidget {
  const PengumumanScreen({super.key});

  @override
  State<PengumumanScreen> createState() => _PengumumanScreenState();
}

class _PengumumanScreenState extends State<PengumumanScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<DashboardProvider>().fetchPengumuman();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final dashboard = context.watch<DashboardProvider>();

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Pengumuman Madrasah'),
      body: RefreshIndicator(
        onRefresh: () => context.read<DashboardProvider>().fetchPengumuman(),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (dashboard.isLoading)
              const ShimmerLoadingList(count: 3, height: 100)
            else if (dashboard.allPengumumanList.isEmpty)
              GlassCard(
                padding: const EdgeInsets.all(32),
                child: Center(
                  child: Column(
                    children: [
                      Icon(
                        Icons.campaign_outlined,
                        size: 48,
                        color: isDark ? Colors.white24 : Colors.black26,
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'Tidak ada pengumuman saat ini.',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Informasi dan pemberitahuan madrasah akan muncul di sini.',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 12,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF73796E),
                        ),
                      ),
                    ],
                  ),
                ),
              )
            else
              ...dashboard.allPengumumanList.map(
                (p) => _buildItemCard(context, p, isDark),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildItemCard(BuildContext context, PengumumanItem p, bool isDark) {
    final isPenting = p.tipe.toLowerCase() == 'penting';
    final isKegiatan = p.tipe.toLowerCase() == 'kegiatan';
    final isLibur = p.tipe.toLowerCase() == 'libur';

    final Color badgeBg;
    final Color badgeText;
    final IconData badgeIcon;

    if (isPenting) {
      badgeBg = isDark ? const Color(0xFF3B1212) : const Color(0xFFFEE2E2);
      badgeText = AppColors.roseDanger;
      badgeIcon = Icons.error_outline_rounded;
    } else if (isKegiatan) {
      badgeBg = isDark ? const Color(0xFF0F2313) : const Color(0xFFD1FAE5);
      badgeText = isDark ? AppColors.primaryDark : const Color(0xFF059669);
      badgeIcon = Icons.event_available_rounded;
    } else if (isLibur) {
      badgeBg = isDark ? const Color(0xFF382305) : const Color(0xFFFEF3C7);
      badgeText = AppColors.amberAccent;
      badgeIcon = Icons.beach_access_rounded;
    } else {
      badgeBg = isDark ? const Color(0xFF0C243B) : const Color(0xFFE0F2FE);
      badgeText = isDark ? AppColors.skyBlueAccent : const Color(0xFF0284C7);
      badgeIcon = Icons.info_outline_rounded;
    }

    final hasPdf = p.lampiranPdfUrl != null && p.lampiranPdfUrl!.isNotEmpty;

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      onTap: () {
        HapticHelper.light();
        _showDetailSheet(context, p, isDark);
      },
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 3,
                    ),
                    decoration: BoxDecoration(
                      color: badgeBg,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(badgeIcon, size: 12, color: badgeText),
                        const SizedBox(width: 4),
                        Text(
                          p.tipe,
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: badgeText,
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (hasPdf) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 7,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF3B1212)
                            : const Color(0xFFFEE2E2),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: AppColors.roseDanger.withValues(alpha: 0.3),
                        ),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.picture_as_pdf_rounded,
                            size: 11,
                            color: AppColors.roseDanger,
                          ),
                          SizedBox(width: 3),
                          Text(
                            'PDF',
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w900,
                              color: AppColors.roseDanger,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
              Text(
                p.tanggalMulai,
                style: TextStyle(
                  fontSize: 11,
                  color: isDark
                      ? const Color(0xFF8D9387)
                      : const Color(0xFF73796E),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            p.judul,
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 6),
          Text(
            p.konten,
            maxLines: 3,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: 13,
              height: 1.4,
              color: isDark ? const Color(0xFFCCCCCC) : const Color(0xFF4B5563),
            ),
          ),
          if (hasPdf) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                Icon(
                  Icons.attachment_rounded,
                  size: 14,
                  color: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                ),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    p.namaFilePdf ?? 'Dokumen PDF terlampir',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  void _showDetailSheet(BuildContext context, PengumumanItem p, bool isDark) {
    final hasPdf = p.lampiranPdfUrl != null && p.lampiranPdfUrl!.isNotEmpty;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(context).size.height * 0.85,
          ),
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF141914) : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            border: Border.all(
              color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
            ),
          ),
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: isDark ? Colors.white24 : Colors.black12,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 9,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: isDark
                          ? AppColors.primaryContainerDark
                          : AppColors.primaryContainerLight,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      p.tipe,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                  ),
                  Text(
                    p.tanggalMulai,
                    style: TextStyle(
                      fontSize: 12,
                      color: isDark
                          ? const Color(0xFF8D9387)
                          : const Color(0xFF73796E),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                p.judul,
                style: const TextStyle(
                  fontSize: 17,
                  fontWeight: FontWeight.bold,
                  height: 1.3,
                ),
              ),
              const SizedBox(height: 12),
              Divider(
                color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
              ),
              const SizedBox(height: 10),
              Flexible(
                child: SingleChildScrollView(
                  child: Text(
                    p.konten,
                    style: TextStyle(
                      fontSize: 14,
                      height: 1.6,
                      color: isDark
                          ? const Color(0xFFE4E4E7)
                          : const Color(0xFF27272A),
                    ),
                  ),
                ),
              ),
              if (hasPdf) ...[
                const SizedBox(height: 18),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFE11D48),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      elevation: 0,
                    ),
                    onPressed: () {
                      HapticHelper.medium();
                      FileDownloadHelper.downloadAndOpen(
                        context,
                        url: p.lampiranPdfUrl!,
                        fileName: p.namaFilePdf ?? 'Pengumuman_${p.id}.pdf',
                      );
                    },
                    icon: const Icon(Icons.download_rounded, size: 20),
                    label: Text(
                      'Unduh & Buka PDF (${p.namaFilePdf ?? "Lampiran.pdf"})',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
              ],
            ],
          ),
        );
      },
    );
  }
}
