import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/haptic_helper.dart';

class SegmentedTabBarItem {
  final String label;
  final IconData icon;

  const SegmentedTabBarItem({required this.label, required this.icon});
}

class SegmentedTabBar extends StatelessWidget {
  final TabController controller;
  final List<SegmentedTabBarItem> tabs;
  final EdgeInsetsGeometry margin;

  const SegmentedTabBar({
    super.key,
    required this.controller,
    required this.tabs,
    this.margin = const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      margin: margin,
      height: 48,
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: isDark ? AppColors.surfaceContainerDark : Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(
          color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
          width: 1,
        ),
      ),
      child: AnimatedBuilder(
        animation: controller,
        builder: (context, _) {
          return Row(
            children: List.generate(tabs.length, (index) {
              final isSelected = controller.index == index;
              final tab = tabs[index];

              return Expanded(
                child: GestureDetector(
                  onTap: () {
                    HapticHelper.selection();
                    controller.animateTo(index);
                  },
                  behavior: HitTestBehavior.opaque,
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    curve: Curves.easeInOut,
                    decoration: BoxDecoration(
                      color: isSelected
                          ? Theme.of(context).colorScheme.primary
                          : Colors.transparent,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Center(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            tab.icon,
                            size: 16,
                            color: isSelected
                                ? Theme.of(context).colorScheme.onPrimary
                                : (isDark ? Colors.white60 : Colors.black54),
                          ),
                          const SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              tab.label,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: isSelected
                                    ? FontWeight.w900
                                    : FontWeight.w600,
                                color: isSelected
                                    ? Theme.of(context).colorScheme.onPrimary
                                    : (isDark
                                          ? Colors.white60
                                          : Colors.black54),
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              );
            }),
          );
        },
      ),
    );
  }
}
