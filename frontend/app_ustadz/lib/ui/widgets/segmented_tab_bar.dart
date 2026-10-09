import 'package:flutter/material.dart';
import '../../core/theme/app_motion.dart';
import '../../core/utils/haptic_helper.dart';

class SegmentedTabItem {
  final IconData activeIcon;
  final IconData inactiveIcon;
  final String label;
  final Color? activeColor;
  final Color? activeContainer;

  const SegmentedTabItem({
    required this.activeIcon,
    required this.inactiveIcon,
    required this.label,
    this.activeColor,
    this.activeContainer,
  });
}

/// Material 3 Expressive Segmented Tab Control with Animated Jelly-Pill Motion
class SegmentedTabBar extends StatelessWidget {
  final List<SegmentedTabItem> items;
  final int selectedIndex;
  final ValueChanged<int> onTabChanged;
  final EdgeInsetsGeometry margin;
  final Color? accentColor;
  final double borderRadius;

  const SegmentedTabBar({
    super.key,
    required this.items,
    required this.selectedIndex,
    required this.onTabChanged,
    this.margin = const EdgeInsets.fromLTRB(16, 12, 16, 8),
    this.accentColor,
    this.borderRadius = 20,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final colorScheme = theme.colorScheme;

    final defaultAccent =
        accentColor ??
        (isDark ? colorScheme.primary : colorScheme.primary);

    final containerBg = isDark
        ? colorScheme.surfaceContainerHigh
        : colorScheme.surfaceContainer;

    final innerRadius = (borderRadius - 4).clamp(10.0, 24.0);

    return Padding(
      padding: margin,
      child: Container(
        padding: const EdgeInsets.all(4),
        decoration: BoxDecoration(
          color: containerBg,
          borderRadius: BorderRadius.circular(borderRadius),
        ),
        child: Row(
          children: List.generate(items.length, (index) {
            final item = items[index];
            final isSelected = selectedIndex == index;
            final itemAccent = item.activeColor ?? defaultAccent;
            final itemContainer =
                item.activeContainer ??
                (isDark
                    ? colorScheme.secondaryContainer
                    : colorScheme.secondaryContainer);
            final inactiveColor = isDark
                ? colorScheme.onSurfaceVariant
                : colorScheme.onSurfaceVariant;

            return Expanded(
              child: M3ScaleOnPress(
                onTap: () {
                  if (selectedIndex != index) {
                    HapticHelper.segmentTick();
                    onTabChanged(index);
                  }
                },
                pressedScale: 0.95,
                borderRadius: BorderRadius.circular(innerRadius),
                child: AnimatedContainer(
                  duration: AppMotion.durationMedium1,
                  curve: AppMotion.emphasized,
                  padding: const EdgeInsets.symmetric(
                    vertical: 9,
                    horizontal: 6,
                  ),
                  decoration: BoxDecoration(
                    color: isSelected ? itemContainer : Colors.transparent,
                    borderRadius: BorderRadius.circular(innerRadius),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      AnimatedScale(
                        duration: AppMotion.durationShort2,
                        curve: AppMotion.expressiveSpring,
                        scale: isSelected ? 1.05 : 0.95,
                        child: Icon(
                          isSelected ? item.activeIcon : item.inactiveIcon,
                          size: 17,
                          color: isSelected ? itemAccent : inactiveColor,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Flexible(
                        child: Text(
                          item.label,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: isSelected
                                ? FontWeight.w800
                                : FontWeight.w600,
                            color: isSelected ? itemAccent : inactiveColor,
                            letterSpacing: -0.1,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          }),
        ),
      ),
    );
  }
}
