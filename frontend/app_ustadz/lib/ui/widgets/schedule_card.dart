import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';
import '../../data/models/dashboard_model.dart';
import 'glass_card.dart';

class ScheduleCard extends StatelessWidget {
  final JadwalHariIniItem item;
  final VoidCallback? onAbsenTap;

  const ScheduleCard({super.key, required this.item, this.onAbsenTap});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Jam Ke Pill Badge
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: isDark
                      ? const Color(0xFF101C10)
                      : const Color(0xFFE8F5E9),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                    color: isDark
                        ? AppColors.primaryDark.withValues(alpha: 0.3)
                        : AppColors.primaryLight.withValues(alpha: 0.2),
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.schedule_rounded,
                      size: 13,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                    const SizedBox(width: 5),
                    Text(
                      'Jam Ke-${item.jamKe} • ${item.jam}',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                  ],
                ),
              ),

              // Status Absen Badge
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: item.sudahAbsen
                      ? (isDark
                            ? AppColors.hadirBgDark
                            : AppColors.hadirBgLight)
                      : (isDark
                            ? const Color(0xFF451A03)
                            : const Color(0xFFFEF3C7)),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  item.sudahAbsen ? '✓ Sudah Absen' : '● Belum Absen',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: item.sudahAbsen
                        ? (isDark
                              ? AppColors.hadirTextDark
                              : AppColors.hadirTextLight)
                        : (isDark
                              ? AppColors.sakitTextDark
                              : AppColors.sakitTextLight),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: Text(
                  item.mapel,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              if (item.isTeamTeaching || !item.isUtama) ...[
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 2.5,
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
                          size: 12,
                          color: AppColors.amberAccent,
                        ),
                        const SizedBox(width: 3),
                      ],
                      Text(
                        item.peran,
                        style: TextStyle(
                          fontSize: 10,
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
          const SizedBox(height: 4),
          Row(
            children: [
              Icon(
                Icons.meeting_room_rounded,
                size: 14,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
              const SizedBox(width: 4),
              Text(
                'Ruang: ${item.kelas}',
                style: TextStyle(
                  fontSize: 12,
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
                    fontSize: 12,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                ),
                Expanded(
                  child: Text(
                    'Guru: ${item.guru}',
                    style: TextStyle(
                      fontSize: 12,
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
          if (!item.sudahAbsen && onAbsenTap != null) ...[
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              height: 38,
              child: ElevatedButton.icon(
                onPressed: onAbsenTap,
                icon: const Icon(Icons.fact_check_rounded, size: 16),
                label: const Text('Mulai Presensi Kelas'),
                style: ElevatedButton.styleFrom(
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
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
