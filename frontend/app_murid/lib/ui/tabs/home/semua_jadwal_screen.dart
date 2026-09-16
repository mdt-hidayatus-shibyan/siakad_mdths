import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/jadwal_model.dart';
import '../../../providers/dashboard_provider.dart';
import '../../widgets/child_switcher_bar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/glass_card.dart';

class SemuaJadwalScreen extends StatefulWidget {
  const SemuaJadwalScreen({super.key});

  @override
  State<SemuaJadwalScreen> createState() => _SemuaJadwalScreenState();
}

class _SemuaJadwalScreenState extends State<SemuaJadwalScreen> {
  String _selectedDay = 'Semua';

  final List<String> _days = const [
    'Semua',
    'Sabtu',
    'Ahad',
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
  ];

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final dashboard = context.watch<DashboardProvider>();
    final selectedAnak = dashboard.selectedAnak;
    final jadwalAnak = dashboard.jadwalAnak;
    final isLoading = dashboard.isLoadingJadwal;

    return Scaffold(
      backgroundColor: isDark ? AppColors.surfaceDark : AppColors.surfaceLight,
      appBar: const CustomAppBar(
        titleText: 'Jadwal Pelajaran',
        subtitleText: 'Jadwal KBM Mingguan Murid',
      ),
      body: selectedAnak == null
          ? const EmptyStateWidget(
              icon: Icons.child_care_rounded,
              title: 'Pilih Murid Terlebih Dahulu',
              subtitle:
                  'Silakan pilih profil murid untuk melihat jadwal pelajaran mingguan.',
            )
          : RefreshIndicator(
              onRefresh: () async {
                HapticHelper.light();
                await dashboard.fetchJadwalForAnak(selectedAnak.id);
              },
              color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 40),
                children: [
                  // Multi-Child Switcher Bar
                  const ChildSwitcherBar(margin: EdgeInsets.zero),
                  const SizedBox(height: 14),

                  // Hero Info Card Ruangan
                  _buildHeaderInfoCard(
                    ruangan: jadwalAnak?.ruangan ?? selectedAnak.ruangan ?? '-',
                    totalMapel: jadwalAnak?.jadwalMingguan.length ?? 0,
                    hariIni: jadwalAnak?.hariIni ?? 'Hari Ini',
                    isDark: isDark,
                  ),
                  const SizedBox(height: 16),

                  // Day Filter Chips
                  _buildDayFilterChips(isDark),
                  const SizedBox(height: 16),

                  // Schedule List
                  if (isLoading && jadwalAnak == null)
                    Padding(
                      padding: const EdgeInsets.only(top: 40),
                      child: Center(
                        child: CircularProgressIndicator(
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                      ),
                    )
                  else if (jadwalAnak == null ||
                      jadwalAnak.jadwalMingguan.isEmpty)
                    EmptyStateWidget(
                      icon: Icons.event_busy_rounded,
                      title: 'Belum Ada Jadwal Pelajaran',
                      subtitle:
                          'Jadwal pelajaran untuk ruangan ${selectedAnak.ruangan ?? "-"} belum diatur oleh admin madrasah.',
                    )
                  else
                    _buildScheduleContent(jadwalAnak, isDark),
                ],
              ),
            ),
    );
  }

  // =========================================================================
  // HEADER INFO CARD
  // =========================================================================
  Widget _buildHeaderInfoCard({
    required String ruangan,
    required int totalMapel,
    required String hariIni,
    required bool isDark,
  }) {
    return GlassCard(
      padding: const EdgeInsets.all(18),
      borderRadius: 24,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color:
                      (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                          .withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  Icons.calendar_month_rounded,
                  color: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                  size: 22,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Jadwal KBM Mingguan',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                        letterSpacing: -0.3,
                        color: isDark ? Colors.white : Colors.black87,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Ruangan: $ruangan',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: isDark
                      ? Colors.white.withValues(alpha: 0.08)
                      : Colors.black.withValues(alpha: 0.05),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.today_rounded,
                      size: 13,
                      color: isDark ? Colors.white70 : Colors.black87,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      hariIni,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: isDark ? Colors.white70 : Colors.black87,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Divider(height: 1),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildInfoItem(
                  label: 'Total Sesi KBM',
                  value: '$totalMapel Jam Pelajaran',
                  icon: Icons.menu_book_rounded,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _buildInfoItem(
                  label: 'Hari Belajar',
                  value: 'Sabtu - Kamis',
                  icon: Icons.date_range_rounded,
                  isDark: isDark,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildInfoItem({
    required String label,
    required String value,
    required IconData icon,
    required bool isDark,
  }) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: isDark
            ? Colors.white.withValues(alpha: 0.04)
            : Colors.black.withValues(alpha: 0.03),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        children: [
          Icon(
            icon,
            size: 15,
            color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white54 : Colors.black54,
                  ),
                ),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: isDark ? Colors.white : Colors.black87,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // DAY FILTER CHIPS
  // =========================================================================
  Widget _buildDayFilterChips(bool isDark) {
    return SizedBox(
      height: 38,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        physics: const BouncingScrollPhysics(),
        itemCount: _days.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final day = _days[index];
          final isSelected = _selectedDay == day;

          return InkWell(
            onTap: () {
              HapticHelper.light();
              setState(() {
                _selectedDay = day;
              });
            },
            borderRadius: BorderRadius.circular(14),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: isSelected
                    ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                    : (isDark
                          ? Colors.white.withValues(alpha: 0.06)
                          : Colors.black.withValues(alpha: 0.04)),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: isSelected
                      ? (isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight)
                      : (isDark
                            ? AppColors.outlineDark
                            : AppColors.outlineLight),
                  width: 1,
                ),
              ),
              child: Text(
                day,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: isSelected ? FontWeight.w900 : FontWeight.w700,
                  color: isSelected
                      ? (isDark ? Colors.black : Colors.white)
                      : (isDark ? Colors.white70 : Colors.black87),
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // SCHEDULE CONTENT (GROUPED OR FILTERED)
  // =========================================================================
  Widget _buildScheduleContent(JadwalDetailAnakModel jadwalAnak, bool isDark) {
    final daysToRender = _selectedDay == 'Semua'
        ? const ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis']
        : [_selectedDay];

    return Column(
      children: daysToRender.map((day) {
        final items = jadwalAnak.jadwalMingguan
            .where((j) => j.hari.toLowerCase() == day.toLowerCase())
            .toList();

        final isHariIni = day.toLowerCase() == jadwalAnak.hariIni.toLowerCase();

        if (_selectedDay != 'Semua' && items.isEmpty) {
          return Padding(
            padding: const EdgeInsets.only(top: 20),
            child: GlassCard(
              padding: const EdgeInsets.all(24),
              borderRadius: 20,
              child: Column(
                children: [
                  Icon(
                    Icons.event_note_rounded,
                    size: 36,
                    color: isDark ? Colors.white38 : Colors.black38,
                  ),
                  const SizedBox(height: 10),
                  Text(
                    'Tidak ada jam pelajaran untuk hari $day.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: isDark ? Colors.white60 : Colors.black54,
                    ),
                  ),
                ],
              ),
            ),
          );
        }

        if (_selectedDay == 'Semua' && items.isEmpty) {
          return const SizedBox.shrink();
        }

        return Padding(
          padding: const EdgeInsets.only(bottom: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Day Section Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 8,
                        height: 8,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: isHariIni
                              ? const Color(0xFF10B981)
                              : (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'HARI $day'.toUpperCase(),
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.8,
                          color: isDark ? Colors.white70 : Colors.black87,
                        ),
                      ),
                    ],
                  ),
                  if (isHariIni)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text(
                        'Hari Ini',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                          color: Color(0xFF10B981),
                        ),
                      ),
                    )
                  else
                    Text(
                      '${items.length} Pelajaran',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: isDark ? Colors.white38 : Colors.black38,
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 8),

              // Lessons Card
              GlassCard(
                padding: const EdgeInsets.all(16),
                borderRadius: 20,
                child: Column(
                  children: items.asMap().entries.map((entry) {
                    final idx = entry.key;
                    final j = entry.value;
                    final isLast = idx == items.length - 1;

                    return Column(
                      children: [
                        _buildLessonItem(j, isDark),
                        if (!isLast) const Divider(height: 16, indent: 48),
                      ],
                    );
                  }).toList(),
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildLessonItem(JadwalItemModel j, bool isDark) {
    return Row(
      children: [
        Container(
          width: 36,
          height: 36,
          decoration: BoxDecoration(
            color: (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                .withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(
            Icons.menu_book_rounded,
            size: 18,
            color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                j.mapel,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w800,
                  color: isDark ? Colors.white : Colors.black87,
                ),
              ),
              const SizedBox(height: 2),
              Row(
                children: [
                  Icon(
                    Icons.person_outline_rounded,
                    size: 12,
                    color: isDark ? Colors.white38 : Colors.black38,
                  ),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      '${j.jamKe ?? "Pelajaran"} • ${j.ustadz}',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                        color: isDark ? Colors.white54 : Colors.black54,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(width: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          decoration: BoxDecoration(
            color: (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                .withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Text(
            j.waktu,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w800,
              color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            ),
          ),
        ),
      ],
    );
  }
}
