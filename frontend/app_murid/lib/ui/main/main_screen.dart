import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/haptic_helper.dart';
import '../../providers/akademik_provider.dart';
import '../../providers/dashboard_provider.dart';
import '../../providers/keuangan_provider.dart';
import '../../providers/presensi_provider.dart';
import '../tabs/akademik/akademik_tab.dart';
import '../tabs/akun/akun_tab.dart';
import '../tabs/home/home_tab.dart';
import '../tabs/presensi/presensi_tab.dart';
import '../tabs/tagihan/tagihan_tab.dart';

class MainScreen extends StatefulWidget {
  final int initialIndex;
  const MainScreen({super.key, this.initialIndex = 0});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  late int _currentIndex;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initData();
    });
  }

  void _initData() async {
    final dashboard = context.read<DashboardProvider>();
    await dashboard.fetchDashboard();

    if (dashboard.selectedAnak != null) {
      final id = dashboard.selectedAnak!.id;
      if (mounted) {
        context.read<KeuanganProvider>().fetchAllKeuangan(id);
        context.read<PresensiProvider>().fetchPresensi(id);
        context.read<AkademikProvider>().fetchAkademik(id);
      }
    }
  }

  void _onTabChanged(int index) {
    if (_currentIndex == index) return;
    HapticHelper.segmentTick();
    setState(() {
      _currentIndex = index;
    });

    final selectedAnak = context.read<DashboardProvider>().selectedAnak;
    if (selectedAnak != null) {
      if (index == 1) {
        context.read<KeuanganProvider>().fetchAllKeuangan(selectedAnak.id);
      } else if (index == 2) {
        context.read<PresensiProvider>().fetchPresensi(selectedAnak.id);
      } else if (index == 3) {
        context.read<AkademikProvider>().fetchAkademik(selectedAnak.id);
      }
    }
  }

  List<Widget> get _tabs => [
    HomeTab(onNavigateTab: _onTabChanged),
    const TagihanTab(),
    const PresensiTab(),
    const AkademikTab(),
    const AkunTab(),
  ];

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      extendBody: true, // Content flows smoothly behind floating navigation
      backgroundColor: isDark ? AppColors.surfaceDark : AppColors.surfaceLight,
      body: IndexedStack(index: _currentIndex, children: _tabs),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
          child: Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(32),
              boxShadow: [
                // 1. Ambient & bottom drop shadow
                BoxShadow(
                  color: isDark
                      ? Colors.black.withValues(alpha: 0.65)
                      : const Color(0xFF0F172A).withValues(alpha: 0.14),
                  blurRadius: 24,
                  spreadRadius: 2,
                  offset: const Offset(0, 8),
                ),
                // 2. Top boundary shadow to separate clearly from scrolling content
                BoxShadow(
                  color: isDark
                      ? Colors.black.withValues(alpha: 0.40)
                      : const Color(0xFF0F172A).withValues(alpha: 0.08),
                  blurRadius: 12,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(32),
              child: BackdropFilter(
                filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
                child: Container(
                  height: 66,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 6,
                    vertical: 4,
                  ),
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
                        activeIcon: Icons.receipt_long_rounded,
                        inactiveIcon: Icons.receipt_long_outlined,
                        label: 'Tagihan',
                        isDark: isDark,
                      ),
                      _buildNavItem(
                        index: 2,
                        activeIcon: Icons.event_available_rounded,
                        inactiveIcon: Icons.event_available_outlined,
                        label: 'Presensi',
                        isDark: isDark,
                      ),
                      _buildNavItem(
                        index: 3,
                        activeIcon: Icons.auto_stories_rounded,
                        inactiveIcon: Icons.auto_stories_outlined,
                        label: 'Akademik',
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
          onTap: () => _onTabChanged(index),
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
