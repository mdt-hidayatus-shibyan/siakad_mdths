import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';
import '../../data/models/dashboard_model.dart';
import 'glass_card.dart';

class ScheduleCard extends StatelessWidget {
  final JadwalHariIniItem item;
  final VoidCallback? onAbsenTap;
  final VoidCallback? onPresensiGuruTap;

  const ScheduleCard({
    super.key,
    required this.item,
    this.onAbsenTap,
    this.onPresensiGuruTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Header: Jam Ke & Status Presensi Ringkas
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Jam Ke Pill Badge
              Flexible(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(
                    color: isDark
                        ? AppColors.primaryContainerDark.withValues(alpha: 0.3)
                        : AppColors.primaryContainerLight.withValues(alpha: 0.6),
                    borderRadius: BorderRadius.circular(7),
                    border: Border.all(
                      color: isDark
                          ? AppColors.primaryDark.withValues(alpha: 0.3)
                          : AppColors.primaryLight.withValues(alpha: 0.25),
                      width: 0.8,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.schedule_rounded,
                        size: 11.5,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                      const SizedBox(width: 3.5),
                      Flexible(
                        child: Text(
                          'Jam Ke-${item.jamKe} • ${item.jam}',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 6),

              // Status Presensi Compact
              if (item.isBebasKbm)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.amberAccent.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: AppColors.amberAccent.withValues(alpha: 0.3),
                      width: 0.8,
                    ),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.pause_circle_outline_rounded,
                        size: 11,
                        color: AppColors.amberAccent,
                      ),
                      SizedBox(width: 3),
                      Text(
                        'Bebas KBM',
                        style: TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.w600,
                          color: AppColors.amberAccent,
                        ),
                      ),
                    ],
                  ),
                )
              else
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(
                    color: isDark
                        ? Colors.white.withValues(alpha: 0.05)
                        : Colors.black.withValues(alpha: 0.04),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: isDark
                          ? Colors.white.withValues(alpha: 0.08)
                          : Colors.black.withValues(alpha: 0.06),
                      width: 0.8,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Status Ustadz
                      _buildInlineStatusDot(
                        label: item.sudahAbsenUstadz
                            ? 'U: ${item.statusPresensiUstadz}'
                            : 'U: Belum',
                        isSuccess: item.sudahAbsenUstadz,
                        statusType: item.sudahAbsenUstadz
                            ? item.statusPresensiUstadz
                            : null,
                        isDark: isDark,
                      ),
                      Container(
                        margin: const EdgeInsets.symmetric(horizontal: 5),
                        width: 1,
                        height: 9,
                        color: isDark
                            ? Colors.white.withValues(alpha: 0.2)
                            : Colors.black.withValues(alpha: 0.15),
                      ),
                      // Status Murid
                      _buildInlineStatusDot(
                        label: item.sudahAbsenMurid ? 'M: Sudah' : 'M: Belum',
                        isSuccess: item.sudahAbsenMurid,
                        isDark: isDark,
                      ),
                    ],
                  ),
                ),
            ],
          ),
          const SizedBox(height: 8),

          // 2. Mapel & Peran
          Row(
            children: [
              Expanded(
                child: Text(
                  item.mapel,
                  style: const TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.bold,
                    letterSpacing: -0.2,
                  ),
                ),
              ),
              if (item.isTeamTeaching || !item.isUtama) ...[
                const SizedBox(width: 6),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 6,
                    vertical: 2,
                  ),
                  decoration: BoxDecoration(
                    color: item.isUtama
                        ? (isDark
                              ? const Color(0xFF3B2E05)
                              : const Color(0xFFFEF3C7))
                        : (isDark
                              ? const Color(0xFF0C2A45)
                              : const Color(0xFFE0F2FE)),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: item.isUtama
                          ? (isDark
                                ? AppColors.amberAccent.withValues(alpha: 0.4)
                                : const Color(0xFFFCD34D))
                          : (isDark
                                ? AppColors.skyBlueAccent.withValues(alpha: 0.4)
                                : const Color(0xFFBAE6FD)),
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (item.isUtama) ...[
                        const Icon(
                          Icons.star_rounded,
                          size: 11,
                          color: AppColors.amberAccent,
                        ),
                        const SizedBox(width: 2),
                      ],
                      Text(
                        item.peran,
                        style: TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.bold,
                          color: item.isUtama
                              ? (isDark
                                    ? AppColors.amberAccent
                                    : const Color(0xFFB45309))
                              : (isDark
                                    ? AppColors.skyBlueAccent
                                    : const Color(0xFF0369A1)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
          const SizedBox(height: 3),

          // 3. Info Ruang & Guru
          Row(
            children: [
              Icon(
                Icons.meeting_room_rounded,
                size: 13,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
              const SizedBox(width: 4),
              Text(
                'Ruang: ${item.kelas}',
                style: TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w500,
                  color: isDark
                      ? const Color(0xFF8D9387)
                      : const Color(0xFF73796E),
                ),
              ),
              if (item.guru != null && item.guru!.isNotEmpty) ...[
                Text(
                  ' • ',
                  style: TextStyle(
                    fontSize: 11.5,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                ),
                Expanded(
                  child: Text(
                    'Guru: ${item.guru}',
                    style: TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w500,
                      color: isDark
                          ? const Color(0xFF8D9387)
                          : const Color(0xFF73796E),
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ],
          ),

          // Bebas KBM Notice Banner
          if (item.isBebasKbm) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: AppColors.amberAccent.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: AppColors.amberAccent.withValues(alpha: 0.25),
                ),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.info_outline_rounded,
                    size: 14,
                    color: AppColors.amberAccent,
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      item.keteranganBebasKbm != null &&
                              item.keteranganBebasKbm!.isNotEmpty
                          ? 'Sesi Bebas KBM: ${item.keteranganBebasKbm}'
                          : 'Sesi Bebas KBM (Presensi tidak diberlakukan)',
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w600,
                        color: isDark
                            ? AppColors.amberAccent
                            : const Color(0xFFB45309),
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
          ],

          // 4. Baris Tombol Aksi Compact
          if ((onAbsenTap != null || onPresensiGuruTap != null) &&
              !item.isBebasKbm) ...[
            const SizedBox(height: 10),
            if (onAbsenTap != null && onPresensiGuruTap != null)
              Row(
                children: [
                  Expanded(
                    child: SizedBox(
                      height: 34,
                      child: item.sudahAbsenUstadz
                          ? OutlinedButton.icon(
                              onPressed: onPresensiGuruTap,
                              icon: Icon(
                                Icons.check_circle_rounded,
                                size: 14,
                                color: isDark
                                    ? AppColors.hadirTextDark
                                    : AppColors.hadirTextLight,
                              ),
                              label: Text(
                                '✓ Ustadz (${item.statusPresensiUstadz})',
                                style: TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.hadirTextDark
                                      : AppColors.hadirTextLight,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              style: OutlinedButton.styleFrom(
                                backgroundColor: (isDark
                                        ? AppColors.hadirBgDark
                                        : AppColors.hadirBgLight)
                                    .withValues(alpha: 0.5),
                                padding:
                                    const EdgeInsets.symmetric(horizontal: 6),
                                side: BorderSide(
                                  color: (isDark
                                          ? AppColors.hadirTextDark
                                          : const Color(0xFF86EFAC))
                                      .withValues(alpha: 0.5),
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(10),
                                ),
                              ),
                            )
                          : OutlinedButton.icon(
                              onPressed: onPresensiGuruTap,
                              icon: const Icon(Icons.badge_rounded, size: 14),
                              label: const Text(
                                'Presensi Ustadz',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                                padding:
                                    const EdgeInsets.symmetric(horizontal: 6),
                                side: BorderSide(
                                  color: (isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight)
                                      .withValues(alpha: 0.5),
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(10),
                                ),
                              ),
                            ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: SizedBox(
                      height: 34,
                      child: FilledButton.icon(
                        onPressed: onAbsenTap,
                        icon: Icon(
                          item.sudahAbsenMurid
                              ? Icons.edit_note_rounded
                              : Icons.fact_check_rounded,
                          size: 15,
                        ),
                        label: Text(
                          item.sudahAbsenMurid
                              ? 'Ubah Presensi'
                              : 'Presensi Murid',
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        style: FilledButton.styleFrom(
                          backgroundColor: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                          foregroundColor: isDark
                              ? AppColors.onPrimaryDark
                              : AppColors.onPrimaryLight,
                          padding: const EdgeInsets.symmetric(horizontal: 6),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              )
            else if (onAbsenTap != null)
              SizedBox(
                width: double.infinity,
                height: 34,
                child: FilledButton.icon(
                  onPressed: onAbsenTap,
                  icon: Icon(
                    item.sudahAbsenMurid
                        ? Icons.edit_note_rounded
                        : Icons.fact_check_rounded,
                    size: 15,
                  ),
                  label: Text(
                    item.sudahAbsenMurid ? 'Ubah Presensi Murid' : 'Presensi Murid',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  style: FilledButton.styleFrom(
                    backgroundColor: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                    foregroundColor: isDark
                        ? AppColors.onPrimaryDark
                        : AppColors.onPrimaryLight,
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
              )
            else if (onPresensiGuruTap != null)
              SizedBox(
                width: double.infinity,
                height: 34,
                child: item.sudahAbsenUstadz
                    ? OutlinedButton.icon(
                        onPressed: onPresensiGuruTap,
                        icon: Icon(
                          Icons.check_circle_rounded,
                          size: 14,
                          color: isDark
                              ? AppColors.hadirTextDark
                              : AppColors.hadirTextLight,
                        ),
                        label: Text(
                          '✓ Ustadz (${item.statusPresensiUstadz})',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: isDark
                                ? AppColors.hadirTextDark
                                : AppColors.hadirTextLight,
                          ),
                        ),
                        style: OutlinedButton.styleFrom(
                          backgroundColor: (isDark
                                  ? AppColors.hadirBgDark
                                  : AppColors.hadirBgLight)
                              .withValues(alpha: 0.5),
                          padding: const EdgeInsets.symmetric(horizontal: 8),
                          side: BorderSide(
                            color: (isDark
                                    ? AppColors.hadirTextDark
                                    : const Color(0xFF86EFAC))
                                .withValues(alpha: 0.5),
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      )
                    : OutlinedButton.icon(
                        onPressed: onPresensiGuruTap,
                        icon: const Icon(Icons.badge_rounded, size: 14),
                        label: const Text(
                          'Presensi Ustadz',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                          padding: const EdgeInsets.symmetric(horizontal: 8),
                          side: BorderSide(
                            color: (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                .withValues(alpha: 0.5),
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      ),
              ),
          ],
        ],
      ),
    );
  }

  Widget _buildInlineStatusDot({
    required String label,
    required bool isSuccess,
    required bool isDark,
    String? statusType,
  }) {
    final Color dotColor;
    final Color textColor;

    if (isSuccess) {
      if (statusType == 'Izin') {
        dotColor = isDark ? AppColors.izinTextDark : AppColors.izinTextLight;
        textColor = isDark ? AppColors.izinTextDark : AppColors.izinTextLight;
      } else if (statusType == 'Sakit') {
        dotColor = isDark ? AppColors.sakitTextDark : AppColors.sakitTextLight;
        textColor = isDark ? AppColors.sakitTextDark : AppColors.sakitTextLight;
      } else if (statusType == 'Alpha') {
        dotColor = isDark ? AppColors.alphaTextDark : AppColors.alphaTextLight;
        textColor = isDark ? AppColors.alphaTextDark : AppColors.alphaTextLight;
      } else {
        dotColor = isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight;
        textColor = isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight;
      }
    } else {
      dotColor = isDark ? const Color(0xFF9E9E9E) : const Color(0xFF757575);
      textColor = isDark ? const Color(0xFF9E9E9E) : const Color(0xFF757575);
    }

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 5.5,
          height: 5.5,
          decoration: BoxDecoration(
            color: dotColor,
            shape: BoxShape.circle,
          ),
        ),
        const SizedBox(width: 3.5),
        Text(
          label,
          style: TextStyle(
            fontSize: 9.5,
            fontWeight: FontWeight.w600,
            color: textColor,
          ),
        ),
      ],
    );
  }
}
