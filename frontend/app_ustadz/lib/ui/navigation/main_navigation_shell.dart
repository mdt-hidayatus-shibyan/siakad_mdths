import 'dart:async';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/services/bell_service.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_motion.dart';
import '../../core/utils/haptic_helper.dart';
import '../../providers/akademik_provider.dart';
import '../tabs/home/home_tab.dart';
import '../tabs/presensi/presensi_tab.dart';
import '../tabs/pelanggaran/pelanggaran_tab.dart';
import '../tabs/ujian/ujian_tab.dart';
import '../tabs/akun/akun_tab.dart';

class MainNavigationShell extends StatefulWidget {
  final int initialIndex;
  const MainNavigationShell({super.key, this.initialIndex = 0});

  static void navigateTo(
    BuildContext context,
    int tabIndex, {
    int? subTabIndex,
  }) {
    final state = context.findAncestorStateOfType<_MainNavigationShellState>();
    state?.navigateToTab(tabIndex, subTabIndex: subTabIndex);
  }

  @override
  State<MainNavigationShell> createState() => _MainNavigationShellState();
}

class _MainNavigationShellState extends State<MainNavigationShell> {
  late int _currentIndex;
  int _presensiSubTab = 0;
  StreamSubscription<BellEvent>? _bellSub;

  void navigateToTab(int tabIndex, {int? subTabIndex}) {
    HapticHelper.segmentTick();
    setState(() {
      if (subTabIndex != null && tabIndex == 1) {
        _presensiSubTab = subTabIndex;
      }
      _currentIndex = tabIndex;
    });
  }

  List<Widget> get _tabs => [
    HomeTab(
      onNavigateToPresensiGuru: () => navigateToTab(1, subTabIndex: 1),
      onNavigateToPresensiMurid: () => navigateToTab(1, subTabIndex: 0),
    ),
    PresensiTab(
      key: ValueKey('presensi_tab_$_presensiSubTab'),
      initialSubTab: _presensiSubTab,
      onNavigateToUjian: () => _onTabSelected(3),
    ),
    const PelanggaranTab(),
    const UjianTab(),
    const AkunTab(),
  ];

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<AkademikProvider>().fetchJadwalPelajaran();
      }
    });

    _bellSub = BellService.instance.onBellEvent.listen((event) {
      if (mounted) {
        _showBellModal(event);
      }
    });
  }

  @override
  void dispose() {
    _bellSub?.cancel();
    super.dispose();
  }

  void _showBellModal(BellEvent event) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final colorScheme = theme.colorScheme;
    final isJam1 = event.jam == 1;

    showModalBottomSheet(
      context: context,
      isDismissible: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return SafeArea(
          top: false,
          child: Container(
            margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
            decoration: BoxDecoration(
              color: isDark
                  ? colorScheme.surfaceContainerHigh
                  : colorScheme.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(28),
              border: Border.all(
                color: isDark
                    ? colorScheme.outlineVariant.withValues(alpha: 0.3)
                    : colorScheme.outlineVariant.withValues(alpha: 0.6),
                width: 0.8,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: isDark ? 0.4 : 0.08),
                  blurRadius: 24,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 36,
                  height: 4.5,
                  decoration: BoxDecoration(
                    color: isDark ? Colors.white24 : Colors.black12,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
                const SizedBox(height: 18),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: (isJam1
                            ? colorScheme.primary
                            : AppColors.amberAccent)
                        .withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Icon(
                    Icons.notifications_active_rounded,
                    color: isJam1
                        ? colorScheme.primary
                        : AppColors.amberAccent,
                    size: 32,
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  event.title,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  event.description,
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 13,
                    color: isDark ? Colors.white70 : Colors.black54,
                  ),
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        onPressed: () {
                          BellService.instance.stopSound();
                          Navigator.pop(ctx);
                        },
                        child: const Text('Tutup'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: FilledButton.icon(
                        style: FilledButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        onPressed: () {
                          BellService.instance.stopSound();
                          Navigator.pop(ctx);
                          _onTabSelected(1);
                        },
                        icon: const Icon(Icons.how_to_reg_rounded, size: 18),
                        label: const Text('Buka Presensi'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
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
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final colorScheme = theme.colorScheme;

    return Scaffold(
      extendBody: true,
      body: IndexedStack(index: _currentIndex, children: _tabs),
      bottomNavigationBar: _buildFloatingBottomBar(context, colorScheme, isDark),
    );
  }

  Widget _buildFloatingBottomBar(
    BuildContext context,
    ColorScheme colorScheme,
    bool isDark,
  ) {
    final bottomInset = MediaQuery.of(context).padding.bottom;
    // Pada mobile web / device tanpa inset, 12dp agar melayang rapat & pas
    // Pada device dengan gesture pill/3-button, float 8dp di atas inset sistem
    final effectiveBottomMargin = math.max(12.0, bottomInset + 8.0);

    return Align(
      alignment: Alignment.bottomCenter,
      heightFactor: 1.0,
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 500),
        child: Padding(
          padding: EdgeInsets.fromLTRB(16, 0, 16, effectiveBottomMargin),
          child: Container(
            height: 64,
            decoration: BoxDecoration(
              color: isDark
                  ? colorScheme.surfaceContainerHigh
                  : colorScheme.surfaceContainerHighest,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(
                color: isDark
                    ? colorScheme.outlineVariant.withValues(alpha: 0.35)
                    : colorScheme.outlineVariant.withValues(alpha: 0.30),
                width: 1.0,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: isDark ? 0.38 : 0.08),
                  blurRadius: 20,
                  offset: const Offset(0, 6),
                ),
                BoxShadow(
                  color: Colors.black.withValues(alpha: isDark ? 0.20 : 0.03),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                _buildNavItem(
                  index: 0,
                  icon: Icons.home_outlined,
                  selectedIcon: Icons.home_rounded,
                  label: 'Beranda',
                  colorScheme: colorScheme,
                  isDark: isDark,
                ),
                _buildNavItem(
                  index: 1,
                  icon: Icons.how_to_reg_outlined,
                  selectedIcon: Icons.how_to_reg_rounded,
                  label: 'Presensi',
                  colorScheme: colorScheme,
                  isDark: isDark,
                ),
                _buildNavItem(
                  index: 2,
                  icon: Icons.warning_amber_outlined,
                  selectedIcon: Icons.warning_amber_rounded,
                  label: 'Pelanggaran',
                  colorScheme: colorScheme,
                  isDark: isDark,
                ),
                _buildNavItem(
                  index: 3,
                  icon: Icons.assignment_outlined,
                  selectedIcon: Icons.assignment_turned_in_rounded,
                  label: 'Ujian',
                  colorScheme: colorScheme,
                  isDark: isDark,
                ),
                _buildNavItem(
                  index: 4,
                  icon: Icons.person_outline_rounded,
                  selectedIcon: Icons.person_rounded,
                  label: 'Akun',
                  colorScheme: colorScheme,
                  isDark: isDark,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem({
    required int index,
    required IconData icon,
    required IconData selectedIcon,
    required String label,
    required ColorScheme colorScheme,
    required bool isDark,
  }) {
    final isSelected = _currentIndex == index;
    final inactiveColor = isDark
        ? const Color(0xFF90968B)
        : const Color(0xFF6B7265);

    return Expanded(
      child: M3ScaleOnPress(
        onTap: () => _onTabSelected(index),
        pressedScale: 0.92,
        borderRadius: BorderRadius.circular(20),
        enableFeedback: false,
        child: Container(
          color: Colors.transparent,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              AnimatedContainer(
                duration: AppMotion.durationShort2,
                curve: AppMotion.emphasized,
                width: 44,
                height: 28,
                decoration: BoxDecoration(
                  color: isSelected
                      ? colorScheme.primary.withValues(alpha: isDark ? 0.22 : 0.14)
                      : Colors.transparent,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Center(
                  child: Icon(
                    isSelected ? selectedIcon : icon,
                    size: 20,
                    color: isSelected ? colorScheme.primary : inactiveColor,
                  ),
                ),
              ),
              const SizedBox(height: 3),
              AnimatedDefaultTextStyle(
                duration: AppMotion.durationShort2,
                curve: AppMotion.emphasized,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 10.5,
                  fontWeight: isSelected ? FontWeight.w700 : FontWeight.w600,
                  color: isSelected ? colorScheme.primary : inactiveColor,
                  letterSpacing: -0.2,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                child: Text(label),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
