import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/haptic_helper.dart';
import '../../providers/akademik_provider.dart';
import '../../providers/dashboard_provider.dart';
import '../../providers/keuangan_provider.dart';
import '../../providers/presensi_provider.dart';

class ChildSwitcherBar extends StatelessWidget {
  final EdgeInsetsGeometry? margin;

  const ChildSwitcherBar({super.key, this.margin});

  @override
  Widget build(BuildContext context) {
    final dashboard = context.watch<DashboardProvider>();
    final anakList = dashboard.anakList;
    final selected = dashboard.selectedAnak;

    if (anakList.isEmpty) return const SizedBox.shrink();

    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      margin: margin ?? const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: isDark
            ? AppColors.surfaceContainerLowDark
            : Colors.white.withValues(alpha: 0.9),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
          width: 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(left: 12, top: 6, bottom: 4),
            child: Row(
              children: [
                Icon(
                  Icons.family_restroom_rounded,
                  size: 15,
                  color: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                ),
                const SizedBox(width: 6),
                Text(
                  'PILIH MURID / ANAK (${anakList.length})',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.8,
                    color: isDark ? Colors.white60 : Colors.black54,
                  ),
                ),
              ],
            ),
          ),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
            child: Row(
              children: anakList.map((anak) {
                final isSelected = selected?.id == anak.id;

                return Padding(
                  padding: const EdgeInsets.only(right: 6),
                  child: InkWell(
                    onTap: () {
                      if (!isSelected) {
                        HapticHelper.medium();
                        dashboard.switchAnak(anak);
                        // Refresh active child data in other tabs
                        context.read<KeuanganProvider>().fetchAllKeuangan(
                          anak.id,
                          force: true,
                        );
                        context.read<PresensiProvider>().fetchPresensi(
                          anak.id,
                          force: true,
                        );
                        context.read<AkademikProvider>().fetchAkademik(
                          anak.id,
                          force: true,
                        );
                      }
                    },
                    borderRadius: BorderRadius.circular(16),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                      decoration: BoxDecoration(
                        color: isSelected
                            ? (isDark
                                  ? AppColors.primaryDark.withValues(
                                      alpha: 0.25,
                                    )
                                  : AppColors.primaryLight.withValues(
                                      alpha: 0.12,
                                    ))
                            : Colors.transparent,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isSelected
                              ? (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                              : Colors.transparent,
                          width: 1.5,
                        ),
                      ),
                      child: Row(
                        children: [
                          Container(
                            width: 28,
                            height: 28,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: isSelected
                                  ? (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                  : (isDark ? Colors.white12 : Colors.black12),
                            ),
                            child: Center(
                              child: Text(
                                anak.namaLengkap.isNotEmpty
                                    ? anak.namaLengkap
                                          .substring(0, 1)
                                          .toUpperCase()
                                    : 'A',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w900,
                                  color: isSelected
                                      ? Theme.of(context).colorScheme.onPrimary
                                      : (isDark
                                            ? Colors.white70
                                            : Colors.black87),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                anak.namaLengkap,
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: isSelected
                                      ? FontWeight.w900
                                      : FontWeight.w600,
                                  color: isSelected
                                      ? (isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight)
                                      : (isDark
                                            ? Colors.white
                                            : Colors.black87),
                                ),
                              ),
                              Text(
                                '${anak.ruangan ?? "-"} • NISM: ${anak.nism}',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w500,
                                  color: isDark
                                      ? Colors.white54
                                      : Colors.black54,
                                ),
                              ),
                            ],
                          ),
                          if (isSelected) ...[
                            const SizedBox(width: 6),
                            Icon(
                              Icons.check_circle_rounded,
                              size: 14,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }
}
