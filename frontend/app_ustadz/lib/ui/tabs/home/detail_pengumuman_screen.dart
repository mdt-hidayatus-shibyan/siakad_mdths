import 'package:flutter/material.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/file_download_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/dashboard_model.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';

class DetailPengumumanScreen extends StatelessWidget {
  final PengumumanItem pengumuman;

  const DetailPengumumanScreen({super.key, required this.pengumuman});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final p = pengumuman;
    final hasPdf = p.lampiranPdfUrl != null && p.lampiranPdfUrl!.isNotEmpty;

    final isPenting = p.tipe.toLowerCase() == 'penting';
    final isKegiatan = p.tipe.toLowerCase() == 'kegiatan';
    final isLibur = p.tipe.toLowerCase() == 'libur';

    final Color badgeBg;
    final Color badgeText;
    final Color badgeBorder;
    final IconData badgeIcon;

    if (isPenting) {
      badgeBg = isDark ? const Color(0xFF3B1212) : const Color(0xFFFEE2E2);
      badgeText = AppColors.roseDanger;
      badgeBorder = AppColors.roseDanger.withValues(alpha: 0.3);
      badgeIcon = Icons.warning_amber_rounded;
    } else if (isKegiatan) {
      badgeBg = isDark ? const Color(0xFF0F2313) : const Color(0xFFD1FAE5);
      badgeText = isDark ? AppColors.primaryDark : const Color(0xFF059669);
      badgeBorder = (isDark ? AppColors.primaryDark : const Color(0xFF059669))
          .withValues(alpha: 0.3);
      badgeIcon = Icons.calendar_month_rounded;
    } else if (isLibur) {
      badgeBg = isDark ? const Color(0xFF382305) : const Color(0xFFFEF3C7);
      badgeText = AppColors.amberAccent;
      badgeBorder = AppColors.amberAccent.withValues(alpha: 0.3);
      badgeIcon = Icons.beach_access_rounded;
    } else {
      badgeBg = isDark ? const Color(0xFF0C243B) : const Color(0xFFE0F2FE);
      badgeText = isDark ? AppColors.skyBlueAccent : const Color(0xFF0284C7);
      badgeBorder = (isDark ? AppColors.skyBlueAccent : const Color(0xFF0284C7))
          .withValues(alpha: 0.3);
      badgeIcon = Icons.info_outline_rounded;
    }

    return Scaffold(
      appBar: const CustomAppBar(
        titleText: 'Detail Pengumuman',
        subtitleText: 'Informasi resmi dan pemberitahuan madrasah.',
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
        children: [
          // ===================================================================
          // 1. KONTEN UTAMA PENGUMUMAN
          // ===================================================================
          GlassCard(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header Badges & Tanggal Publikasi
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  children: [
                    // Badge Tipe
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: badgeBg,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: badgeBorder),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(badgeIcon, size: 13, color: badgeText),
                          const SizedBox(width: 4),
                          Text(
                            p.tipe.toUpperCase(),
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.5,
                              color: badgeText,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Badge Lampiran PDF jika ada
                    if (hasPdf)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF3B1212)
                              : const Color(0xFFFEE2E2),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: AppColors.roseDanger.withValues(alpha: 0.3),
                          ),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.picture_as_pdf_rounded,
                              size: 13,
                              color: AppColors.roseDanger,
                            ),
                            SizedBox(width: 4),
                            Text(
                              'ADA LAMPIRAN PDF',
                              style: TextStyle(
                                fontSize: 9.5,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 0.4,
                                color: AppColors.roseDanger,
                              ),
                            ),
                          ],
                        ),
                      ),

                    // Waktu Publikasi
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.access_time_rounded,
                          size: 13,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF73796E),
                        ),
                        const SizedBox(width: 4),
                        Text(
                          'Dipublikasikan ${p.createdAtFormat ?? p.tanggalMulai}',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Judul Pengumuman
                Text(
                  p.judul,
                  style: const TextStyle(
                    fontSize: 19,
                    fontWeight: FontWeight.w900,
                    height: 1.35,
                    letterSpacing: -0.3,
                  ),
                ),
                const SizedBox(height: 14),

                Divider(
                  color: isDark
                      ? AppColors.outlineDark.withValues(alpha: 0.5)
                      : AppColors.outlineLight.withValues(alpha: 0.7),
                  thickness: 1,
                ),
                const SizedBox(height: 14),

                // Isi Konten Pengumuman
                SelectableText(
                  p.konten,
                  style: TextStyle(
                    fontSize: 14,
                    height: 1.75,
                    letterSpacing: 0.15,
                    color: isDark
                        ? const Color(0xFFE2E8F0)
                        : const Color(0xFF334155),
                  ),
                ),

                // =============================================================
                // LAMPIRAN PDF BOX (SEPERTI PADA BACKEND SHOW)
                // =============================================================
                if (hasPdf) ...[
                  const SizedBox(height: 24),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF240A10)
                          : const Color(0xFFFFF1F2),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: AppColors.roseDanger.withValues(
                          alpha: isDark ? 0.35 : 0.25,
                        ),
                        width: 1.2,
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(
                              Icons.attachment_rounded,
                              size: 16,
                              color: AppColors.roseDanger,
                            ),
                            SizedBox(width: 6),
                            Text(
                              'LAMPIRAN DOKUMEN RESMI',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 0.6,
                                color: AppColors.roseDanger,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Container(
                              width: 46,
                              height: 46,
                              decoration: BoxDecoration(
                                color: AppColors.roseDanger,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: [
                                  BoxShadow(
                                    color: AppColors.roseDanger.withValues(
                                      alpha: 0.3,
                                    ),
                                    blurRadius: 8,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: const Center(
                                child: Icon(
                                  Icons.picture_as_pdf_rounded,
                                  color: Colors.white,
                                  size: 26,
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    p.namaFilePdf ?? 'Dokumen_Lampiran.pdf',
                                    style: const TextStyle(
                                      fontSize: 13.5,
                                      fontWeight: FontWeight.bold,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    'Format Dokumen: Portable Document Format (.PDF)',
                                    style: TextStyle(
                                      fontSize: 11,
                                      color: isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E),
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 14),
                        SizedBox(
                          width: double.infinity,
                          height: 44,
                          child: ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.roseDanger,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              elevation: 0,
                            ),
                            onPressed: () {
                              HapticHelper.medium();
                              FileDownloadHelper.downloadAndOpen(
                                context,
                                url: p.lampiranPdfUrl!,
                                fileName:
                                    p.namaFilePdf ?? 'Pengumuman_${p.id}.pdf',
                              );
                            },
                            icon: const Icon(Icons.download_rounded, size: 18),
                            label: const Text(
                              'Buka / Unduh Dokumen PDF',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 16),

          // ===================================================================
          // 2. PANEL INFORMASI PUBLIKASI (SIDEBAR META INFO DARI BACKEND)
          // ===================================================================
          GlassCard(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.info_outline_rounded,
                      size: 17,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                    const SizedBox(width: 8),
                    const Text(
                      'Informasi Publikasi',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Divider(
                  color: isDark
                      ? AppColors.outlineDark.withValues(alpha: 0.5)
                      : AppColors.outlineLight.withValues(alpha: 0.7),
                ),
                const SizedBox(height: 10),

                // 2.1 Status Pengumuman
                _buildMetaRow(
                  label: 'STATUS PENGUMUMAN',
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: p.status == 'Terbit'
                              ? (isDark
                                    ? const Color(0xFF0F2313)
                                    : const Color(0xFFD1FAE5))
                              : (isDark
                                    ? const Color(0xFF382305)
                                    : const Color(0xFFFEF3C7)),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: p.status == 'Terbit'
                                ? (isDark
                                          ? AppColors.primaryDark
                                          : const Color(0xFF059669))
                                      .withValues(alpha: 0.3)
                                : AppColors.amberAccent.withValues(alpha: 0.3),
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              width: 6,
                              height: 6,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: p.status == 'Terbit'
                                    ? (isDark
                                          ? AppColors.primaryDark
                                          : const Color(0xFF059669))
                                    : AppColors.amberAccent,
                              ),
                            ),
                            const SizedBox(width: 5),
                            Text(
                              p.status,
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.bold,
                                color: p.status == 'Terbit'
                                    ? (isDark
                                          ? AppColors.primaryDark
                                          : const Color(0xFF059669))
                                    : AppColors.amberAccent,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 12),
                // 2.2 Target Pembaca
                _buildMetaRow(
                  label: 'TARGET PEMBACA',
                  child: Row(
                    children: [
                      Icon(
                        Icons.people_alt_rounded,
                        size: 14,
                        color: isDark
                            ? const Color(0xFF8D9387)
                            : const Color(0xFF73796E),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        p.targetAudience,
                        style: const TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 12),
                // 2.3 Periode Berlaku
                _buildMetaRow(
                  label: 'PERIODE BERLAKU',
                  child: Row(
                    children: [
                      Icon(
                        Icons.date_range_rounded,
                        size: 14,
                        color: isDark
                            ? const Color(0xFF8D9387)
                            : const Color(0xFF73796E),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          p.periodeBerlaku ?? p.tanggalMulai,
                          style: const TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 12),
                // 2.4 Ditulis Oleh
                _buildMetaRow(
                  label: 'DITULIS OLEH',
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color:
                              (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                  .withValues(alpha: 0.15),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          Icons.person_rounded,
                          size: 12,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        p.penulis,
                        style: const TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 12),
                // 2.5 Status Lampiran File
                _buildMetaRow(
                  label: 'LAMPIRAN FILE',
                  child: hasPdf
                      ? const Row(
                          children: [
                            Icon(
                              Icons.picture_as_pdf_rounded,
                              size: 14,
                              color: AppColors.roseDanger,
                            ),
                            SizedBox(width: 6),
                            Text(
                              'Ada Dokumen PDF',
                              style: TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.bold,
                                color: AppColors.roseDanger,
                              ),
                            ),
                          ],
                        )
                      : Text(
                          'Tidak ada lampiran',
                          style: TextStyle(
                            fontSize: 12,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E),
                          ),
                        ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMetaRow({required String label, required Widget child}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 9.5,
            fontWeight: FontWeight.w900,
            letterSpacing: 0.6,
            color: Color(0xFF94A3B8),
          ),
        ),
        const SizedBox(height: 4),
        child,
      ],
    );
  }
}
