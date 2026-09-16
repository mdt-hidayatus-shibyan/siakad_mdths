import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/pelanggaran_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/segmented_tab_bar.dart';
import 'catat_pelanggaran_sheet.dart';
import 'harian_tab_view.dart';
import 'massal_tab_view.dart';
import 'referensi_pelanggaran_screen.dart';
import 'riwayat_tab_view.dart';

class PelanggaranTab extends StatefulWidget {
  const PelanggaranTab({super.key});

  @override
  State<PelanggaranTab> createState() => _PelanggaranTabState();
}

class _PelanggaranTabState extends State<PelanggaranTab>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        setState(() {});
      }
    });

    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PelanggaranProvider>().fetchAll();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _openCatatSheet() {
    HapticHelper.light();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const CatatPelanggaranSheet(),
    );
  }

  void _openReferensiScreen() {
    HapticHelper.light();
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const ReferensiPelanggaranScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Buku Kasus & Disiplin',
        actions: [
          CircularIconButton(
            tooltip: 'Katalog Referensi Sanksi',
            icon: Icons.rule_folder_outlined,
            iconSize: 20,
            iconColor: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            onPressed: _openReferensiScreen,
          ),
          CircularIconButton(
            tooltip: 'Catat Pelanggaran',
            icon: Icons.add_moderator_rounded,
            iconSize: 20,
            iconColor: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            onPressed: _openCatatSheet,
          ),
        ],
      ),
      body: Column(
        children: [
          // Segmented Navigation Pill (Consistent with Bottom Navigation)
          SegmentedTabBar(
            selectedIndex: _tabController.index,
            onTabChanged: (idx) {
              _tabController.animateTo(idx);
              setState(() {});
            },
            items: [
              SegmentedTabItem(
                activeIcon: Icons.today_rounded,
                inactiveIcon: Icons.today_outlined,
                label: 'Harian',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
              SegmentedTabItem(
                activeIcon: Icons.group_add_rounded,
                inactiveIcon: Icons.group_add_outlined,
                label: 'Massal',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
              SegmentedTabItem(
                activeIcon: Icons.history_rounded,
                inactiveIcon: Icons.history_outlined,
                label: 'Riwayat',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
            ],
          ),

          // Tab Views
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: const [
                // 1. Tab Harian
                HarianTabView(),

                // 2. Tab Massal
                MassalTabView(),

                // 3. Tab Riwayat
                RiwayatTabView(),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
