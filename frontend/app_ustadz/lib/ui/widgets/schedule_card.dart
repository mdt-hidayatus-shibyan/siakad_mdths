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
          // 1. Header: Jam Ke & Status Presensi
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Jam Ke Pill Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: isDark
                      ? AppColors.primaryContainerDark.withValues(alpha: 0.3)
                      : AppColors.primaryContainerLight.withValues(alpha: 0.6),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: isDark
                        ? AppColors.primaryDark.withValues(alpha: 0.3)
                        : AppColors.primaryLight.withValues(alpha: 0.25),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.schedule_rounded,
                      size: 12,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'Jam Ke-${item.jamKe} • ${item.jam}',
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.bold,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                  ],
                ),
              ),

              // Status Absen Badge / Bebas KBM Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: item.isBebasKbm
                      ? AppColors.amberAccent.withValues(alpha: 0.15)
                      : (item.sudahAbsen
                            ? (isDark
                                  ? AppColors.hadirBgDark
                                  : AppColors.hadirBgLight)
                            : AppColors.amberAccent.withValues(alpha: 0.15)),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: item.isBebasKbm
                        ? AppColors.amberAccent.withValues(alpha: 0.5)
                        : (item.sudahAbsen
                              ? (isDark
                                        ? AppColors.hadirTextDark
                                        : const Color(0xFF86EFAC))
                                    .withValues(alpha: 0.5)
                              : AppColors.amberAccent.withValues(alpha: 0.4)),
                    width: 0.8,
                  ),
                ),
                child: Text(
                  item.isBebasKbm
                      ? '⏸ Bebas KBM'
                      : (item.sudahAbsen ? '✓ Sudah Absen' : '● Belum Absen'),
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.bold,
                    color: item.isBebasKbm
                        ? AppColors.amberAccent
                        : (item.sudahAbsen
                              ? (isDark
                                    ? AppColors.hadirTextDark
                                    : AppColors.hadirTextLight)
                              : AppColors.amberAccent),
                  ),
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
                      height: 32,
                      child: OutlinedButton.icon(
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
                            color:
                                (isDark
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
                      height: 32,
                      child: FilledButton.icon(
                        onPressed: onAbsenTap,
                        icon: Icon(
                          item.sudahAbsen
                              ? Icons.edit_note_rounded
                              : Icons.fact_check_rounded,
                          size: 15,
                        ),
                        label: Text(
                          item.sudahAbsen ? 'Ubah Presensi' : 'Presensi Murid',
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
                    ),
                  ),
                ],
              )
            else if (onAbsenTap != null)
              SizedBox(
                width: double.infinity,
                height: 32,
                child: FilledButton.icon(
                  onPressed: onAbsenTap,
                  icon: Icon(
                    item.sudahAbsen
                        ? Icons.edit_note_rounded
                        : Icons.fact_check_rounded,
                    size: 15,
                  ),
                  label: Text(
                    item.sudahAbsen ? 'Ubah Presensi' : 'Presensi Murid',
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
                height: 32,
                child: OutlinedButton.icon(
                  onPressed: onPresensiGuruTap,
                  icon: const Icon(Icons.badge_rounded, size: 14),
                  label: const Text(
                    'Presensi Ustadz',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                  ),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    side: BorderSide(
                      color:
                          (isDark
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
}
