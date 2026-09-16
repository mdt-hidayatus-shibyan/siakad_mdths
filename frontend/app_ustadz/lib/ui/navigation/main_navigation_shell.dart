import 'dart:ui';
import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/haptic_helper.dart';
import '../tabs/home/home_tab.dart';
import '../tabs/presensi/presensi_tab.dart';
import '../tabs/pelanggaran/pelanggaran_tab.dart';
import '../tabs/ujian/ujian_tab.dart';
import '../tabs/akun/akun_tab.dart';

class MainNavigationShell extends StatefulWidget {
  final int initialIndex;
  const MainNavigationShell({super.key, this.initialIndex = 0});

  @override
  State<MainNavigationShell> createState() => _MainNavigationShellState();
}

class _MainNavigationShellState extends State<MainNavigationShell> {
  late int _currentIndex;

  List<Widget> get _tabs => [
    const HomeTab(),
    PresensiTab(onNavigateToUjian: () => _onTabSelected(3)),
    const PelanggaranTab(),
    const UjianTab(),
    const AkunTab(),
  ];

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
  }

  void _onTabSelected(int index) {
    if (_currentIndex != index) {
      HapticHelper.segmentTick();
      setState(() {
        _currentIndex = index;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      extendBody: true, // Content flows smoothly behind floating navigation
      body: IndexedStack(index: _currentIndex, children: _tabs),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(32),
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
              child: Container(
                height: 66,
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                decoration: BoxDecoration(
                  color: isDark
                      ? const Color(0xFF121712).withValues(alpha: 0.95)
                      : Colors.white.withValues(alpha: 0.95),
                  borderRadius: BorderRadius.circular(32),
                  border: Border.all(
                    color: isDark
                        ? AppColors.outlineDark.withValues(alpha: 0.6)
                        : const Color(0xFFE2E8F0),
                    width: 1,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(
                        alpha: isDark ? 0.40 : 0.08,
                      ),
                      blurRadius: 20,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    _buildNavItem(
                      index: 0,
                      activeIcon: Icons.home_rounded,
                      inactiveIcon: Icons.home_outlined,
                      label: 'Beranda',
                      isDark: isDark,
                    ),
                    _buildNavItem(
                      index: 1,
                      activeIcon: Icons.how_to_reg_rounded,
                      inactiveIcon: Icons.how_to_reg_outlined,
                      label: 'Presensi',
                      isDark: isDark,
                    ),
                    _buildNavItem(
                      index: 2,
                      activeIcon: Icons.warning_amber_rounded,
                      inactiveIcon: Icons.warning_amber_outlined,
                      label: 'Pelanggaran',
                      isDark: isDark,
                    ),
                    _buildNavItem(
                      index: 3,
                      activeIcon: Icons.assignment_turned_in_rounded,
                      inactiveIcon: Icons.assignment_outlined,
                      label: 'Ujian',
                      isDark: isDark,
                    ),
                    _buildNavItem(
                      index: 4,
                      activeIcon: Icons.person_rounded,
                      inactiveIcon: Icons.person_outline_rounded,
                      label: 'Akun',
                      isDark: isDark,
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem({
    required int index,
    required IconData activeIcon,
    required IconData inactiveIcon,
    required String label,
    required bool isDark,
  }) {
    final isSelected = _currentIndex == index;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final inactiveColor = isDark
        ? const Color(0xFF94A3B8)
        : const Color(0xFF64748B);
    final activeBgColor = isDark
        ? AppColors.primaryDark.withValues(alpha: 0.15)
        : AppColors.primaryLight.withValues(alpha: 0.10);

    return Expanded(
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _onTabSelected(index),
          borderRadius: BorderRadius.circular(20),
          splashColor: primaryColor.withValues(alpha: 0.12),
          highlightColor: primaryColor.withValues(alpha: 0.06),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            curve: Curves.easeOutCubic,
            margin: const EdgeInsets.symmetric(horizontal: 2, vertical: 3),
            padding: const EdgeInsets.symmetric(vertical: 4),
            decoration: BoxDecoration(
              color: isSelected ? activeBgColor : Colors.transparent,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  isSelected ? activeIcon : inactiveIcon,
                  size: 22,
                  color: isSelected ? primaryColor : inactiveColor,
                ),
                const SizedBox(height: 3),
                Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                    color: isSelected ? primaryColor : inactiveColor,
                    letterSpacing: -0.2,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
