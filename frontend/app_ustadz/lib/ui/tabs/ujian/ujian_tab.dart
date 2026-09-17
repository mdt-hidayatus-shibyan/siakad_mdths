import 'package:flutter/material.dart';
import '../../../core/theme/app_colors.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/segmented_tab_bar.dart';
import 'input_nilai_tab_view.dart';
import 'persyaratan_ujian_tab_view.dart';
import 'presensi_ujian_tab_view.dart';

class UjianTab extends StatefulWidget {
  const UjianTab({super.key});

  @override
  State<UjianTab> createState() => _UjianTabState();
}

class _UjianTabState extends State<UjianTab>
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
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Ujian Madrasah'),
      body: Column(
        children: [
          // Segmented Navigation Pill (3 Tabs)
          SegmentedTabBar(
            selectedIndex: _tabController.index,
            onTabChanged: (idx) {
              _tabController.animateTo(idx);
              setState(() {});
            },
            items: [
              SegmentedTabItem(
                activeIcon: Icons.verified_user_rounded,
                inactiveIcon: Icons.verified_user_outlined,
                label: 'Syarat Ujian',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
              SegmentedTabItem(
                activeIcon: Icons.fact_check_rounded,
                inactiveIcon: Icons.fact_check_outlined,
                label: 'Presensi',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
              SegmentedTabItem(
                activeIcon: Icons.edit_document,
                inactiveIcon: Icons.edit_note_rounded,
                label: 'Input Nilai',
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
                PersyaratanUjianTabView(),
                PresensiUjianTabView(),
                InputNilaiTabView(),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
