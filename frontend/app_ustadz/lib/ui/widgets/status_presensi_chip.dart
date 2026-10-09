import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_motion.dart';

/// Material 3 Expressive Tonal Status Attendance Chip (H, I, S, A, D)
class StatusPresensiChip extends StatelessWidget {
  final String status; // 'H', 'I', 'S', 'A', 'D'
  final String? label; // e.g. 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispen'
  final bool isSelected;
  final VoidCallback? onTap;

  const StatusPresensiChip({
    super.key,
    required this.status,
    this.label,
    required this.isSelected,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final colorScheme = theme.colorScheme;

    Color activeBg;
    Color activeText;
    Color activeBorder;

    switch (status) {
      case 'H':
        activeBg = isDark ? AppColors.hadirBgDark : AppColors.hadirBgLight;
        activeText = isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight;
        activeBorder = isDark ? AppColors.hadirBorderDark : AppColors.hadirBorderLight;
        break;
      case 'I':
        activeBg = isDark ? AppColors.izinBgDark : AppColors.izinBgLight;
        activeText = isDark ? AppColors.izinTextDark : AppColors.izinTextLight;
        activeBorder = isDark ? AppColors.izinBorderDark : AppColors.izinBorderLight;
        break;
      case 'S':
        activeBg = isDark ? AppColors.sakitBgDark : AppColors.sakitBgLight;
        activeText = isDark ? AppColors.sakitTextDark : AppColors.sakitTextLight;
        activeBorder = isDark ? AppColors.sakitBorderDark : AppColors.sakitBorderLight;
        break;
      case 'A':
        activeBg = isDark ? AppColors.alphaBgDark : AppColors.alphaBgLight;
        activeText = isDark ? AppColors.alphaTextDark : AppColors.alphaTextLight;
        activeBorder = isDark ? AppColors.alphaBorderDark : AppColors.alphaBorderLight;
        break;
      case 'B':
      case 'D':
      default:
        activeBg = isDark ? AppColors.dispensasiBgDark : AppColors.dispensasiBgLight;
        activeText = isDark ? AppColors.dispensasiTextDark : AppColors.dispensasiTextLight;
        activeBorder = isDark ? AppColors.dispensasiBorderDark : AppColors.dispensasiBorderLight;
        break;
    }

    final inactiveBg = isDark
        ? colorScheme.surfaceContainerHigh
        : colorScheme.surfaceContainer;
    final inactiveText = isDark
        ? colorScheme.onSurfaceVariant
        : colorScheme.onSurfaceVariant;
    final inactiveBorder = isDark
        ? colorScheme.outlineVariant.withValues(alpha: 0.3)
        : colorScheme.outlineVariant.withValues(alpha: 0.6);

    Widget chip = AnimatedContainer(
      duration: AppMotion.durationShort2,
      curve: AppMotion.emphasized,
      padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 4),
      decoration: BoxDecoration(
        color: isSelected ? activeBg : inactiveBg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isSelected ? activeBorder : inactiveBorder,
          width: isSelected ? 1.5 : 0.8,
        ),
        boxShadow: isSelected && !isDark
            ? [
                BoxShadow(
                  color: activeBorder.withValues(alpha: 0.25),
                  blurRadius: 4,
                  offset: const Offset(0, 1.5),
                ),
              ]
            : null,
      ),
      alignment: Alignment.center,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          AnimatedScale(
            duration: AppMotion.durationShort2,
            curve: AppMotion.expressiveSpring,
            scale: isSelected ? 1.08 : 1.0,
            child: Text(
              status,
              style: TextStyle(
                fontSize: 13.5,
                fontWeight: isSelected ? FontWeight.w900 : FontWeight.w700,
                color: isSelected ? activeText : inactiveText,
              ),
            ),
          ),
          if (label != null) ...[
            const SizedBox(height: 1.5),
            Text(
              label!,
              style: TextStyle(
                fontSize: 9,
                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w500,
                color: isSelected
                    ? activeText.withValues(alpha: 0.95)
                    : inactiveText.withValues(alpha: 0.8),
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ],
      ),
    );

    if (onTap != null) {
      chip = M3ScaleOnPress(
        onTap: onTap,
        pressedScale: 0.90,
        borderRadius: BorderRadius.circular(12),
        child: chip,
      );
    }

    return chip;
  }
}
