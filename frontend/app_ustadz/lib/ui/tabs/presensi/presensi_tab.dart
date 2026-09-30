import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/presensi_model.dart';
import '../../../providers/presensi_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';
import 'checkin_ustadz_sheet.dart';
import 'form_presensi_screen.dart';
import 'badal_presensi_screen.dart';

class PresensiTab extends StatefulWidget {
  final VoidCallback? onNavigateToUjian;
  final int initialSubTab;

  const PresensiTab({
    super.key,
    this.onNavigateToUjian,
    this.initialSubTab = 0,
  });

  @override
  State<PresensiTab> createState() => _PresensiTabState();
}

class _PresensiTabState extends State<PresensiTab>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  String _filterSesiMurid = 'Semua';
  String _filterSesiUstadz = 'Semua';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: 2,
      vsync: this,
      initialIndex: widget.initialSubTab.clamp(0, 1),
    );
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        setState(() {});
      }
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final p = context.read<PresensiProvider>();
      p.fetchSesi();
      p.fetchSesiUstadz();
      p.fetchDaftarBadal();
    });
  }

  @override
  void didUpdateWidget(covariant PresensiTab oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.initialSubTab != widget.initialSubTab) {
      _tabController.animateTo(widget.initialSubTab.clamp(0, 1));
    }
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _shiftDateMurid(int days) {
    HapticHelper.selection();
    setState(() => _filterSesiMurid = 'Semua');
    final p = context.read<PresensiProvider>();
    p.setSelectedDate(p.selectedDate.add(Duration(days: days)));
  }

  void _shiftDateUstadz(int days) {
    HapticHelper.selection();
    setState(() => _filterSesiUstadz = 'Semua');
    final p = context.read<PresensiProvider>();
    p.setSelectedDateUstadz(p.selectedDateUstadz.add(Duration(days: days)));
  }

  Future<void> _pickDateMurid(BuildContext context) async {
    final provider = context.read<PresensiProvider>();
    final picked = await showDatePicker(
      context: context,
      initialDate: provider.selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 90)),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );
    if (picked != null && picked != provider.selectedDate) {
      HapticHelper.light();
      setState(() => _filterSesiMurid = 'Semua');
      provider.setSelectedDate(picked);
    }
  }

  Future<void> _pickDateUstadz(BuildContext context) async {
    final provider = context.read<PresensiProvider>();
    final picked = await showDatePicker(
      context: context,
      initialDate: provider.selectedDateUstadz,
      firstDate: DateTime.now().subtract(const Duration(days: 90)),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );
    if (picked != null && picked != provider.selectedDateUstadz) {
      HapticHelper.light();
      setState(() => _filterSesiUstadz = 'Semua');
      provider.setSelectedDateUstadz(picked);
    }
  }

  void _showCheckinModal(BuildContext context, SesiPresensiUstadzItem sesi) {
    CheckinUstadzSheet.show(
      context,
      sesi,
      context.read<PresensiProvider>().selectedDateUstadz,
    );
  }

  Widget _buildMiniStat(String label, int count, Color color) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 7,
          height: 7,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 4),
        Text(
          '$label: $count',
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final presensi = context.watch<PresensiProvider>();

    return Scaffold(
      appBar: CustomAppBar(titleText: 'Presensi'),
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
                activeIcon: Icons.people_alt_rounded,
                inactiveIcon: Icons.people_alt_outlined,
                label: 'Presensi Murid',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
              SegmentedTabItem(
                activeIcon: Icons.badge_rounded,
                inactiveIcon: Icons.badge_outlined,
                label: 'Presensi Ustadz',
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
              children: [
                // ===================================================================
                // SUB-TAB 1: PRESENSI MURID (KBM)
                // ===================================================================
                RefreshIndicator(
                  onRefresh: () => presensi.fetchSesi(),
                  child: ListView(
                    padding: EdgeInsets.fromLTRB(
                      16,
                      16,
                      16,
                      120 + MediaQuery.of(context).padding.bottom,
                    ),
                    children: [
                      // 1. DATE PICKER SELECTOR BAR
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? AppColors.surfaceContainerLowDark
                              : Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: isDark
                                ? AppColors.outlineDark
                                : const Color(0xFFE2E8F0),
                          ),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            IconButton(
                              visualDensity: VisualDensity.compact,
                              icon: const Icon(Icons.chevron_left_rounded),
                              onPressed: () => _shiftDateMurid(-1),
                            ),
                            GestureDetector(
                              onTap: () => _pickDateMurid(context),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    Icons.calendar_today_rounded,
                                    size: 14,
                                    color: isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight,
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    DateHelper.formatTanggalIndo(
                                      presensi.selectedDate,
                                    ),
                                    style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            IconButton(
                              visualDensity: VisualDensity.compact,
                              icon: const Icon(Icons.chevron_right_rounded),
                              onPressed: () => _shiftDateMurid(1),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 12),

                      // BANNER GURU PENGGANTI (BADAL) - Hanya tampil jika KBM Reguler
                      if (!presensi.isEvent) ...[
                        InkWell(
                          onTap: () {
                            HapticHelper.medium();
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const BadalPresensiScreen(),
                              ),
                            );
                          },
                          borderRadius: BorderRadius.circular(16),
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 12,
                            ),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? AppColors.primaryContainerDark.withValues(
                                      alpha: 0.25,
                                    )
                                  : AppColors.primaryContainerLight.withValues(
                                      alpha: 0.5,
                                    ),
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(
                                color: isDark
                                    ? AppColors.primaryDark.withValues(
                                        alpha: 0.35,
                                      )
                                    : AppColors.primaryLight.withValues(
                                        alpha: 0.35,
                                      ),
                              ),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: isDark
                                        ? AppColors.primaryDark.withValues(
                                            alpha: 0.25,
                                          )
                                        : AppColors.primaryLight.withValues(
                                            alpha: 0.2,
                                          ),
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    Icons.swap_horiz_rounded,
                                    size: 20,
                                    color: isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight,
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Guru Pengganti (Badal)',
                                        style: TextStyle(
                                          fontSize: 13,
                                          fontWeight: FontWeight.bold,
                                          color: isDark
                                              ? AppColors.onPrimaryContainerDark
                                              : AppColors
                                                    .onPrimaryContainerLight,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        'Gantikan ustadz lain & isi presensi di kelas manapun',
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                Icon(
                                  Icons.chevron_right_rounded,
                                  size: 20,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                      ] else ...[
                        // BANNER KEGIATAN / ACARA KHUSUS (HARI EFEKTIF NON-KBM / HAFLAH / LOMBA)
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: isDark
                                ? AppColors.primaryContainerDark.withValues(
                                    alpha: 0.3,
                                  )
                                : AppColors.primaryContainerLight.withValues(
                                    alpha: 0.6,
                                  ),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: isDark
                                  ? AppColors.primaryDark.withValues(alpha: 0.4)
                                  : AppColors.primaryLight.withValues(
                                      alpha: 0.35,
                                    ),
                            ),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? AppColors.primaryDark.withValues(
                                          alpha: 0.25,
                                        )
                                      : AppColors.primaryLight.withValues(
                                          alpha: 0.2,
                                        ),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  Icons.celebration_rounded,
                                  size: 22,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Expanded(
                                          child: Text(
                                            presensi.eventInfo?.namaKegiatan ??
                                                'Agenda Madrasah',
                                            style: TextStyle(
                                              fontSize: 14,
                                              fontWeight: FontWeight.bold,
                                              color: isDark
                                                  ? AppColors
                                                        .onPrimaryContainerDark
                                                  : AppColors
                                                        .onPrimaryContainerLight,
                                            ),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(
                                            horizontal: 8,
                                            vertical: 2,
                                          ),
                                          decoration: BoxDecoration(
                                            color: isDark
                                                ? AppColors.primaryDark
                                                : AppColors.primaryLight,
                                            borderRadius: BorderRadius.circular(
                                              8,
                                            ),
                                          ),
                                          child: Text(
                                            presensi.eventInfo?.tipePresensi ==
                                                    'multi_sesi'
                                                ? 'Multi Sesi'
                                                : 'Presensi Harian',
                                            style: const TextStyle(
                                              fontSize: 10,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.white,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 3),
                                    Text(
                                      'Masa kegiatan madrasah • Presensi kehadiran murid',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),

                        // FILTER CHIP JIKA MULTI SESI
                        if (presensi.eventInfo != null &&
                            presensi.eventInfo!.tipePresensi == 'multi_sesi' &&
                            presensi.eventInfo!.sesiList.length > 1) ...[
                          SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: Row(
                              children: [
                                Padding(
                                  padding: const EdgeInsets.only(right: 8),
                                  child: ChoiceChip(
                                    label: const Text('Semua Sesi'),
                                    selected: _filterSesiMurid == 'Semua',
                                    onSelected: (val) {
                                      if (val) {
                                        setState(
                                          () => _filterSesiMurid = 'Semua',
                                        );
                                      }
                                    },
                                    selectedColor: isDark
                                        ? AppColors.primaryContainerDark
                                        : AppColors.primaryContainerLight,
                                  ),
                                ),
                                ...presensi.eventInfo!.sesiList.map((s) {
                                  final isSel = _filterSesiMurid == s;
                                  return Padding(
                                    padding: const EdgeInsets.only(right: 8),
                                    child: ChoiceChip(
                                      label: Text('Sesi $s'),
                                      selected: isSel,
                                      onSelected: (val) {
                                        if (val) {
                                          setState(() => _filterSesiMurid = s);
                                        }
                                      },
                                      selectedColor: isDark
                                          ? AppColors.primaryContainerDark
                                          : AppColors.primaryContainerLight,
                                    ),
                                  );
                                }),
                              ],
                            ),
                          ),
                          const SizedBox(height: 12),
                        ],
                      ],

                      // 1. Kondisi Khusus: MASA UJIAN MADRASAH (Tampilkan State Khusus & Jangan Tampilkan Presensi KBM)
                      if (presensi.isUjian) ...[
                        GlassCard(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 20,
                            vertical: 32,
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                width: 76,
                                height: 76,
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? const Color(0xFF2E1065)
                                      : const Color(0xFFF3E8FF),
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: AppColors.violetAccent.withValues(
                                      alpha: 0.5,
                                    ),
                                    width: 1.5,
                                  ),
                                ),
                                child: const Icon(
                                  Icons.assignment_turned_in_rounded,
                                  size: 40,
                                  color: AppColors.violetAccent,
                                ),
                              ),
                              const SizedBox(height: 18),
                              Text(
                                'Masa Ujian Madrasah',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? Colors.white
                                      : const Color(0xFF581C87),
                                ),
                              ),
                              const SizedBox(height: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 14,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? const Color(0xFF1E0A3C)
                                      : const Color(0xFFE9D5FF),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  presensi.namaUjian ??
                                      'Ujian Madrasah Sedang Berlangsung',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: isDark
                                        ? const Color(0xFFD8B4FE)
                                        : const Color(0xFF6B21A8),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 14),
                              Text(
                                'Presensi KBM reguler dinonaktifkan pada tanggal pelaksanaan ujian. Silakan gunakan modul Presensi Ujian untuk mencatat kehadiran murid.',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  fontSize: 12,
                                  color: isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E),
                                ),
                              ),
                              const SizedBox(height: 20),
                              FilledButton.icon(
                                onPressed: () {
                                  HapticHelper.medium();
                                  if (widget.onNavigateToUjian != null) {
                                    widget.onNavigateToUjian!();
                                  }
                                },
                                icon: const Icon(
                                  Icons.arrow_forward_rounded,
                                  size: 16,
                                ),
                                label: const Text('Buka Presensi Ujian'),
                                style: FilledButton.styleFrom(
                                  backgroundColor: AppColors.violetAccent,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 22,
                                    vertical: 12,
                                  ),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(16),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ] else if (presensi.isLibur) ...[
                        GlassCard(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 20,
                            vertical: 32,
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                width: 76,
                                height: 76,
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? const Color(0xFF382305)
                                      : const Color(0xFFFEF3C7),
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: AppColors.amberAccent.withValues(
                                      alpha: 0.5,
                                    ),
                                    width: 1.5,
                                  ),
                                ),
                                child: const Icon(
                                  Icons.beach_access_rounded,
                                  size: 40,
                                  color: AppColors.amberAccent,
                                ),
                              ),
                              const SizedBox(height: 18),
                              Text(
                                'Hari Libur Madrasah',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? Colors.white
                                      : const Color(0xFF92400E),
                                ),
                              ),
                              const SizedBox(height: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 14,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? const Color(0xFF241505)
                                      : const Color(0xFFFDE68A),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  presensi.keteranganLibur ??
                                      'Kegiatan Belajar Mengajar (KBM) Diliburkan',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: isDark
                                        ? AppColors.amberAccent
                                        : const Color(0xFF78350F),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 14),
                              Text(
                                'Presensi kehadiran murid tidak dibuka pada hari libur madrasah.',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  fontSize: 12,
                                  color: isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E),
                                ),
                              ),
                              const SizedBox(height: 20),
                              OutlinedButton.icon(
                                onPressed: () => _pickDateMurid(context),
                                icon: const Icon(
                                  Icons.edit_calendar_rounded,
                                  size: 16,
                                ),
                                label: const Text('Pilih Tanggal Lain'),
                                style: OutlinedButton.styleFrom(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 18,
                                    vertical: 8,
                                  ),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(16),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ] else ...[
                        // 2. Kondisi Hari Aktif KBM / Acara Khusus: Tampilkan Daftar Sesi
                        Builder(
                          builder: (context) {
                            final displayedMuridSesiList = presensi.sesiList
                                .where((s) {
                                  if (!presensi.isEvent) return true;
                                  if (_filterSesiMurid == 'Semua') return true;
                                  return (s.sesi ?? '').toLowerCase() ==
                                      _filterSesiMurid.toLowerCase();
                                })
                                .toList();

                            return Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  presensi.isEvent
                                      ? 'Daftar Presensi Murid (${displayedMuridSesiList.length} Sesi/Ruangan)'
                                      : 'Daftar Sesi Mengajar Murid',
                                  style: const TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 12),
                                if (presensi.isLoading)
                                  const ShimmerLoadingList(count: 3)
                                else if (displayedMuridSesiList.isEmpty)
                                  GlassCard(
                                    padding: const EdgeInsets.all(24),
                                    child: Center(
                                      child: Text(
                                        presensi.isEvent
                                            ? 'Tidak ada agenda presensi untuk filter sesi ini.'
                                            : 'Tidak ada jadwal mengajar pada tanggal ini.',
                                      ),
                                    ),
                                  )
                                else
                                  ...displayedMuridSesiList.map(
                                    (sesi) => GlassCard(
                                      margin: const EdgeInsets.only(bottom: 12),
                                      padding: const EdgeInsets.all(16),
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            mainAxisAlignment:
                                                MainAxisAlignment.spaceBetween,
                                            children: [
                                              Container(
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                      horizontal: 8,
                                                      vertical: 3,
                                                    ),
                                                decoration: BoxDecoration(
                                                  color: isDark
                                                      ? AppColors
                                                            .primaryContainerDark
                                                            .withValues(
                                                              alpha: 0.3,
                                                            )
                                                      : AppColors
                                                            .primaryContainerLight
                                                            .withValues(
                                                              alpha: 0.6,
                                                            ),
                                                  borderRadius:
                                                      BorderRadius.circular(8),
                                                  border: Border.all(
                                                    color: isDark
                                                        ? AppColors.primaryDark
                                                              .withValues(
                                                                alpha: 0.3,
                                                              )
                                                        : AppColors.primaryLight
                                                              .withValues(
                                                                alpha: 0.25,
                                                              ),
                                                  ),
                                                ),
                                                child: Text(
                                                  sesi.isEvent
                                                      ? 'Sesi ${sesi.sesi ?? sesi.jam}'
                                                      : sesi.jam,
                                                  style: TextStyle(
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.bold,
                                                    color: isDark
                                                        ? AppColors.primaryDark
                                                        : AppColors
                                                              .primaryLight,
                                                  ),
                                                ),
                                              ),
                                              Container(
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                      horizontal: 8,
                                                      vertical: 3,
                                                    ),
                                                decoration: BoxDecoration(
                                                  color: sesi.isBebasKbm
                                                      ? AppColors.amberAccent
                                                            .withValues(
                                                              alpha: 0.15,
                                                            )
                                                      : (sesi.sudahAbsen
                                                            ? (isDark
                                                                  ? AppColors
                                                                        .hadirBgDark
                                                                  : AppColors
                                                                        .hadirBgLight)
                                                            : (isDark
                                                                  ? const Color(
                                                                      0xFF451A03,
                                                                    )
                                                                  : const Color(
                                                                      0xFFFEF3C7,
                                                                    ))),
                                                  borderRadius:
                                                      BorderRadius.circular(8),
                                                  border: Border.all(
                                                    color: sesi.isBebasKbm
                                                        ? AppColors.amberAccent
                                                              .withValues(
                                                                alpha: 0.4,
                                                              )
                                                        : (sesi.sudahAbsen
                                                                  ? (isDark
                                                                        ? AppColors
                                                                              .hadirTextDark
                                                                        : const Color(
                                                                            0xFF86EFAC,
                                                                          ))
                                                                  : (isDark
                                                                        ? AppColors
                                                                              .sakitTextDark
                                                                        : const Color(
                                                                            0xFFFDE68A,
                                                                          )))
                                                              .withValues(
                                                                alpha: 0.5,
                                                              ),
                                                    width: 0.8,
                                                  ),
                                                ),
                                                child: Text(
                                                  sesi.isBebasKbm
                                                      ? '⏸ Bebas KBM'
                                                      : (sesi.sudahAbsen
                                                            ? (sesi.isEvent
                                                                  ? '✓ Selesai (${sesi.hadirCount}/${sesi.totalMurid})'
                                                                  : '✓ Selesai')
                                                            : '● Belum Presensi'),
                                                  style: TextStyle(
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.bold,
                                                    color: sesi.isBebasKbm
                                                        ? AppColors.amberAccent
                                                        : (sesi.sudahAbsen
                                                              ? (isDark
                                                                    ? AppColors
                                                                          .hadirTextDark
                                                                    : AppColors
                                                                          .hadirTextLight)
                                                              : (isDark
                                                                    ? AppColors
                                                                          .sakitTextDark
                                                                    : AppColors
                                                                          .sakitTextLight)),
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 10),
                                          Text(
                                            sesi.isEvent
                                                ? (sesi.namaKegiatan ??
                                                      sesi.pelajaran)
                                                : sesi.pelajaran,
                                            style: const TextStyle(
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            sesi.isEvent
                                                ? 'Ruangan: ${sesi.kelas} • Total: ${sesi.totalMurid} Murid'
                                                : 'Ruangan: ${sesi.kelas} • Guru: ${sesi.guru}',
                                            style: TextStyle(
                                              fontSize: 12,
                                              color: isDark
                                                  ? const Color(0xFF8D9387)
                                                  : const Color(0xFF73796E),
                                            ),
                                          ),
                                          if (sesi.isEvent &&
                                              sesi.sudahAbsen) ...[
                                            const SizedBox(height: 10),
                                            Container(
                                              padding:
                                                  const EdgeInsets.symmetric(
                                                    horizontal: 10,
                                                    vertical: 6,
                                                  ),
                                              decoration: BoxDecoration(
                                                color: isDark
                                                    ? AppColors
                                                          .surfaceContainerLowDark
                                                    : const Color(0xFFF8FAFC),
                                                borderRadius:
                                                    BorderRadius.circular(8),
                                                border: Border.all(
                                                  color: isDark
                                                      ? AppColors.outlineDark
                                                      : const Color(0xFFE2E8F0),
                                                ),
                                              ),
                                              child: Row(
                                                mainAxisAlignment:
                                                    MainAxisAlignment
                                                        .spaceAround,
                                                children: [
                                                  _buildMiniStat(
                                                    'Hadir',
                                                    sesi.hadirCount,
                                                    AppColors.hadirTextLight,
                                                  ),
                                                  _buildMiniStat(
                                                    'Sakit',
                                                    sesi.sakitCount,
                                                    AppColors.sakitTextLight,
                                                  ),
                                                  _buildMiniStat(
                                                    'Izin',
                                                    sesi.izinCount,
                                                    AppColors.izinTextLight,
                                                  ),
                                                  _buildMiniStat(
                                                    'Alpha',
                                                    sesi.alphaCount,
                                                    AppColors.alphaTextLight,
                                                  ),
                                                ],
                                              ),
                                            ),
                                          ],
                                          if (sesi.isBebasKbm) ...[
                                            const SizedBox(height: 8),
                                            Container(
                                              padding:
                                                  const EdgeInsets.symmetric(
                                                    horizontal: 10,
                                                    vertical: 6,
                                                  ),
                                              decoration: BoxDecoration(
                                                color: AppColors.amberAccent
                                                    .withValues(alpha: 0.12),
                                                borderRadius:
                                                    BorderRadius.circular(8),
                                                border: Border.all(
                                                  color: AppColors.amberAccent
                                                      .withValues(alpha: 0.25),
                                                ),
                                              ),
                                              child: Row(
                                                children: [
                                                  const Icon(
                                                    Icons.info_outline_rounded,
                                                    size: 14,
                                                    color:
                                                        AppColors.amberAccent,
                                                  ),
                                                  const SizedBox(width: 6),
                                                  Expanded(
                                                    child: Text(
                                                      sesi.keteranganBebasKbm !=
                                                                  null &&
                                                              sesi
                                                                  .keteranganBebasKbm!
                                                                  .isNotEmpty
                                                          ? 'Sesi Bebas KBM: ${sesi.keteranganBebasKbm}'
                                                          : 'Sesi Bebas KBM (Presensi tidak diberlakukan)',
                                                      style: TextStyle(
                                                        fontSize: 10.5,
                                                        fontWeight:
                                                            FontWeight.w600,
                                                        color: isDark
                                                            ? AppColors
                                                                  .amberAccent
                                                            : const Color(
                                                                0xFFB45309,
                                                              ),
                                                      ),
                                                      maxLines: 2,
                                                      overflow:
                                                          TextOverflow.ellipsis,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                          ],
                                          if (sesi.isMilikWali &&
                                              !sesi.isEvent) ...[
                                            const SizedBox(height: 6),
                                            Container(
                                              padding:
                                                  const EdgeInsets.symmetric(
                                                    horizontal: 8,
                                                    vertical: 2,
                                                  ),
                                              decoration: BoxDecoration(
                                                color: isDark
                                                    ? AppColors
                                                          .primaryContainerDark
                                                          .withValues(
                                                            alpha: 0.3,
                                                          )
                                                    : AppColors
                                                          .primaryContainerLight
                                                          .withValues(
                                                            alpha: 0.6,
                                                          ),
                                                borderRadius:
                                                    BorderRadius.circular(6),
                                                border: Border.all(
                                                  color: isDark
                                                      ? AppColors.primaryDark
                                                            .withValues(
                                                              alpha: 0.3,
                                                            )
                                                      : AppColors.primaryLight
                                                            .withValues(
                                                              alpha: 0.25,
                                                            ),
                                                ),
                                              ),
                                              child: Text(
                                                'Ruangan Binaan (Akses Wali Ruangan)',
                                                style: TextStyle(
                                                  fontSize: 10,
                                                  fontWeight: FontWeight.bold,
                                                  color: isDark
                                                      ? AppColors.primaryDark
                                                      : AppColors.primaryLight,
                                                ),
                                              ),
                                            ),
                                          ],
                                          if (!sesi.isBebasKbm) ...[
                                            const SizedBox(height: 14),
                                            SizedBox(
                                              width: double.infinity,
                                              height: 40,
                                              child: ElevatedButton.icon(
                                                onPressed: () {
                                                  HapticHelper.light();
                                                  if (sesi.isEvent) {
                                                    Navigator.push(
                                                      context,
                                                      MaterialPageRoute(
                                                        builder: (_) => FormPresensiScreen(
                                                          isEvent: true,
                                                          kalendarId: sesi
                                                              .kalendarPendidikanId,
                                                          ruanganId:
                                                              sesi.ruanganId,
                                                          sesi: sesi.sesi,
                                                          mapel:
                                                              sesi.namaKegiatan ??
                                                              sesi.pelajaran,
                                                          ruangan: sesi.kelas,
                                                          jam:
                                                              'Sesi ${sesi.sesi ?? ""}',
                                                          tanggal: presensi
                                                              .selectedDate,
                                                        ),
                                                      ),
                                                    );
                                                  } else {
                                                    Navigator.push(
                                                      context,
                                                      MaterialPageRoute(
                                                        builder: (_) =>
                                                            FormPresensiScreen(
                                                              jadwalId: sesi.id,
                                                              mapel: sesi
                                                                  .pelajaran,
                                                              ruangan:
                                                                  sesi.kelas,
                                                              jam: sesi.jam,
                                                              tanggal: presensi
                                                                  .selectedDate,
                                                            ),
                                                      ),
                                                    );
                                                  }
                                                },
                                                icon: Icon(
                                                  sesi.sudahAbsen
                                                      ? Icons.edit_note_rounded
                                                      : Icons
                                                            .how_to_reg_rounded,
                                                  size: 18,
                                                ),
                                                label: Text(
                                                  sesi.isEvent
                                                      ? (sesi.sudahAbsen
                                                            ? 'Edit Presensi Sesi ${sesi.sesi ?? ""}'
                                                            : 'Buka Presensi Sesi ${sesi.sesi ?? ""}')
                                                      : (sesi.sudahAbsen
                                                            ? 'Edit Presensi'
                                                            : 'Buka Presensi'),
                                                ),
                                              ),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ),
                                  ),
                              ],
                            );
                          },
                        ),
                      ],
                    ],
                  ),
                ),

                // ===================================================================
                // SUB-TAB 2: PRESENSI USTADZ (CHECK-IN PER JADWAL) & BADAL
                // ===================================================================
                RefreshIndicator(
                  onRefresh: () => presensi.fetchSesiUstadz(),
                  child: ListView(
                    padding: EdgeInsets.fromLTRB(
                      16,
                      16,
                      16,
                      120 + MediaQuery.of(context).padding.bottom,
                    ),
                    children: [
                      // 1. DATE PICKER SELECTOR BAR
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? AppColors.surfaceContainerLowDark
                              : Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: isDark
                                ? AppColors.outlineDark
                                : const Color(0xFFE2E8F0),
                          ),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            IconButton(
                              visualDensity: VisualDensity.compact,
                              icon: const Icon(Icons.chevron_left_rounded),
                              onPressed: () => _shiftDateUstadz(-1),
                            ),
                            GestureDetector(
                              onTap: () => _pickDateUstadz(context),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    Icons.calendar_today_rounded,
                                    size: 14,
                                    color: isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight,
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    DateHelper.formatTanggalIndo(
                                      presensi.selectedDateUstadz,
                                    ),
                                    style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            IconButton(
                              visualDensity: VisualDensity.compact,
                              icon: const Icon(Icons.chevron_right_rounded),
                              onPressed: () => _shiftDateUstadz(1),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 12),

                      // BANNER GURU PENGGANTI (BADAL) - Hanya jika KBM Reguler
                      if (!presensi.isEventUstadz) ...[
                        InkWell(
                          onTap: () {
                            HapticHelper.medium();
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const BadalPresensiScreen(),
                              ),
                            );
                          },
                          borderRadius: BorderRadius.circular(16),
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 12,
                            ),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? AppColors.primaryContainerDark.withValues(
                                      alpha: 0.25,
                                    )
                                  : AppColors.primaryContainerLight.withValues(
                                      alpha: 0.5,
                                    ),
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(
                                color: isDark
                                    ? AppColors.primaryDark.withValues(
                                        alpha: 0.35,
                                      )
                                    : AppColors.primaryLight.withValues(
                                        alpha: 0.35,
                                      ),
                              ),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: isDark
                                        ? AppColors.primaryDark.withValues(
                                            alpha: 0.25,
                                          )
                                        : AppColors.primaryLight.withValues(
                                            alpha: 0.2,
                                          ),
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    Icons.swap_horiz_rounded,
                                    size: 20,
                                    color: isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight,
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Guru Pengganti (Badal)',
                                        style: TextStyle(
                                          fontSize: 13,
                                          fontWeight: FontWeight.bold,
                                          color: isDark
                                              ? AppColors.onPrimaryContainerDark
                                              : AppColors
                                                    .onPrimaryContainerLight,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        'Gantikan ustadz lain & isi presensi di kelas manapun',
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                Icon(
                                  Icons.chevron_right_rounded,
                                  size: 20,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                      ] else ...[
                        // BANNER KEGIATAN / ACARA KHUSUS (USTADZ)
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: isDark
                                ? AppColors.primaryContainerDark.withValues(
                                    alpha: 0.3,
                                  )
                                : AppColors.primaryContainerLight.withValues(
                                    alpha: 0.6,
                                  ),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: isDark
                                  ? AppColors.primaryDark.withValues(alpha: 0.4)
                                  : AppColors.primaryLight.withValues(
                                      alpha: 0.35,
                                    ),
                            ),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? AppColors.primaryDark.withValues(
                                          alpha: 0.25,
                                        )
                                      : AppColors.primaryLight.withValues(
                                          alpha: 0.2,
                                        ),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  Icons.badge_rounded,
                                  size: 22,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Expanded(
                                          child: Text(
                                            presensi
                                                    .eventInfoUstadz
                                                    ?.namaKegiatan ??
                                                'Agenda Kegiatan Ustadz',
                                            style: TextStyle(
                                              fontSize: 14,
                                              fontWeight: FontWeight.bold,
                                              color: isDark
                                                  ? AppColors
                                                        .onPrimaryContainerDark
                                                  : AppColors
                                                        .onPrimaryContainerLight,
                                            ),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(
                                            horizontal: 8,
                                            vertical: 2,
                                          ),
                                          decoration: BoxDecoration(
                                            color: isDark
                                                ? AppColors.primaryDark
                                                : AppColors.primaryLight,
                                            borderRadius: BorderRadius.circular(
                                              8,
                                            ),
                                          ),
                                          child: Text(
                                            presensi
                                                        .eventInfoUstadz
                                                        ?.tipePresensi ==
                                                    'multi_sesi'
                                                ? 'Multi Sesi'
                                                : 'Presensi Harian',
                                            style: const TextStyle(
                                              fontSize: 10,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.white,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 3),
                                    Text(
                                      'Check-in kehadiran ustadz untuk agenda kegiatan madrasah',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),

                        // FILTER CHIP JIKA MULTI SESI
                        if (presensi.eventInfoUstadz != null &&
                            presensi.eventInfoUstadz!.tipePresensi ==
                                'multi_sesi' &&
                            presensi.eventInfoUstadz!.sesiList.length > 1) ...[
                          SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: Row(
                              children: [
                                Padding(
                                  padding: const EdgeInsets.only(right: 8),
                                  child: ChoiceChip(
                                    label: const Text('Semua Sesi'),
                                    selected: _filterSesiUstadz == 'Semua',
                                    onSelected: (val) {
                                      if (val) {
                                        setState(
                                          () => _filterSesiUstadz = 'Semua',
                                        );
                                      }
                                    },
                                    selectedColor: isDark
                                        ? AppColors.primaryContainerDark
                                        : AppColors.primaryContainerLight,
                                  ),
                                ),
                                ...presensi.eventInfoUstadz!.sesiList.map((s) {
                                  final isSel = _filterSesiUstadz == s;
                                  return Padding(
                                    padding: const EdgeInsets.only(right: 8),
                                    child: ChoiceChip(
                                      label: Text('Sesi $s'),
                                      selected: isSel,
                                      onSelected: (val) {
                                        if (val) {
                                          setState(() => _filterSesiUstadz = s);
                                        }
                                      },
                                      selectedColor: isDark
                                          ? AppColors.primaryContainerDark
                                          : AppColors.primaryContainerLight,
                                    ),
                                  );
                                }),
                              ],
                            ),
                          ),
                          const SizedBox(height: 12),
                        ],
                      ],

                      // 1. Kondisi Khusus: MASA UJIAN MADRASAH (Tampilkan State Khusus & Jangan Tampilkan Presensi Mengajar KBM)
                      if (presensi.isUjianUstadz) ...[
                        GlassCard(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 20,
                            vertical: 32,
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                width: 76,
                                height: 76,
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? const Color(0xFF2E1065)
                                      : const Color(0xFFF3E8FF),
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: AppColors.violetAccent.withValues(
                                      alpha: 0.5,
                                    ),
                                    width: 1.5,
                                  ),
                                ),
                                child: const Icon(
                                  Icons.assignment_turned_in_rounded,
                                  size: 40,
                                  color: AppColors.violetAccent,
                                ),
                              ),
                              const SizedBox(height: 18),
                              Text(
                                'Masa Ujian Madrasah',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? Colors.white
                                      : const Color(0xFF581C87),
                                ),
                              ),
                              const SizedBox(height: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 14,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? const Color(0xFF1E0A3C)
                                      : const Color(0xFFE9D5FF),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  presensi.namaUjianUstadz ??
                                      'Ujian Madrasah Sedang Berlangsung',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: isDark
                                        ? const Color(0xFFD8B4FE)
                                        : const Color(0xFF6B21A8),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 14),
                              Text(
                                'Presensi mengajar reguler ditiadakan pada tanggal pelaksanaan ujian. Silakan gunakan modul Presensi Pengawas Ujian untuk mencatat kehadiran dan berita acara.',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  fontSize: 12,
                                  color: isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E),
                                ),
                              ),
                              const SizedBox(height: 20),
                              FilledButton.icon(
                                onPressed: () {
                                  HapticHelper.medium();
                                  if (widget.onNavigateToUjian != null) {
                                    widget.onNavigateToUjian!();
                                  }
                                },
                                icon: const Icon(
                                  Icons.arrow_forward_rounded,
                                  size: 16,
                                ),
                                label: const Text('Buka Presensi Ujian'),
                                style: FilledButton.styleFrom(
                                  backgroundColor: AppColors.violetAccent,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 22,
                                    vertical: 12,
                                  ),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(16),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ] else if (presensi.isLiburUstadz) ...[
                        GlassCard(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 20,
                            vertical: 32,
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                width: 76,
                                height: 76,
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? const Color(0xFF382305)
                                      : const Color(0xFFFEF3C7),
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: AppColors.amberAccent.withValues(
                                      alpha: 0.5,
                                    ),
                                    width: 1.5,
                                  ),
                                ),
                                child: const Icon(
                                  Icons.beach_access_rounded,
                                  size: 40,
                                  color: AppColors.amberAccent,
                                ),
                              ),
                              const SizedBox(height: 18),
                              Text(
                                'Hari Libur Madrasah',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? Colors.white
                                      : const Color(0xFF92400E),
                                ),
                              ),
                              const SizedBox(height: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 14,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? const Color(0xFF241505)
                                      : const Color(0xFFFDE68A),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  presensi.keteranganLiburUstadz ??
                                      'Kegiatan Belajar Mengajar Diliburkan',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: isDark
                                        ? AppColors.amberAccent
                                        : const Color(0xFF78350F),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 14),
                              Text(
                                'Check-in presensi mengajar ustadz tidak dibuka pada hari libur madrasah.',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  fontSize: 12,
                                  color: isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),
                      ] else ...[
                        // 2. Sesi Mengajar Ustadz & Tombol Check-In Mandiri / Acara
                        Builder(
                          builder: (context) {
                            final displayedUstadzSesiList = presensi
                                .sesiUstadzList
                                .where((s) {
                                  if (!presensi.isEventUstadz) return true;
                                  if (_filterSesiUstadz == 'Semua') return true;
                                  return (s.sesi ?? '').toLowerCase() ==
                                      _filterSesiUstadz.toLowerCase();
                                })
                                .toList();

                            return Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      presensi.isEventUstadz
                                          ? 'Presensi Kehadiran Ustadz'
                                          : 'Jadwal Mengajar & Check-In',
                                      style: const TextStyle(
                                        fontSize: 15,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    Text(
                                      '${displayedUstadzSesiList.length} ${presensi.isEventUstadz ? 'Sesi' : 'Jadwal'}',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                        color: isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 10),
                                if (presensi.isLoadingUstadz)
                                  const ShimmerLoadingList(count: 2)
                                else if (displayedUstadzSesiList.isEmpty)
                                  GlassCard(
                                    padding: const EdgeInsets.all(20),
                                    child: Center(
                                      child: Text(
                                        presensi.isEventUstadz
                                            ? 'Tidak ada agenda sesi pada filter ini.'
                                            : 'Anda tidak memiliki jadwal mengajar pada tanggal ini.',
                                        style: const TextStyle(fontSize: 13),
                                      ),
                                    ),
                                  )
                                else
                                  ...displayedUstadzSesiList.map(
                                    (sesi) => GlassCard(
                                      margin: const EdgeInsets.only(bottom: 12),
                                      padding: const EdgeInsets.all(16),
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            mainAxisAlignment:
                                                MainAxisAlignment.spaceBetween,
                                            children: [
                                              Container(
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                      horizontal: 8,
                                                      vertical: 3,
                                                    ),
                                                decoration: BoxDecoration(
                                                  color: isDark
                                                      ? AppColors
                                                            .primaryContainerDark
                                                            .withValues(
                                                              alpha: 0.3,
                                                            )
                                                      : AppColors
                                                            .primaryContainerLight
                                                            .withValues(
                                                              alpha: 0.6,
                                                            ),
                                                  borderRadius:
                                                      BorderRadius.circular(8),
                                                  border: Border.all(
                                                    color: isDark
                                                        ? AppColors.primaryDark
                                                              .withValues(
                                                                alpha: 0.3,
                                                              )
                                                        : AppColors.primaryLight
                                                              .withValues(
                                                                alpha: 0.25,
                                                              ),
                                                  ),
                                                ),
                                                child: Text(
                                                  sesi.isEvent
                                                      ? 'Sesi ${sesi.sesi ?? ""}'
                                                      : sesi.jam,
                                                  style: TextStyle(
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.bold,
                                                    color: isDark
                                                        ? AppColors.primaryDark
                                                        : AppColors
                                                              .primaryLight,
                                                  ),
                                                ),
                                              ),
                                              Container(
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                      horizontal: 8,
                                                      vertical: 3,
                                                    ),
                                                decoration: BoxDecoration(
                                                  color:
                                                      sesi.isBebasKbm &&
                                                          !sesi.sudahCheckin
                                                      ? AppColors.amberAccent
                                                            .withValues(
                                                              alpha: 0.15,
                                                            )
                                                      : (sesi.sudahCheckin &&
                                                                sesi.status ==
                                                                    'Hadir'
                                                            ? (isDark
                                                                  ? AppColors
                                                                        .hadirBgDark
                                                                  : AppColors
                                                                        .hadirBgLight)
                                                            : (isDark
                                                                  ? const Color(
                                                                      0xFF451A03,
                                                                    )
                                                                  : const Color(
                                                                      0xFFFEF3C7,
                                                                    ))),
                                                  borderRadius:
                                                      BorderRadius.circular(8),
                                                  border: Border.all(
                                                    color:
                                                        sesi.isBebasKbm &&
                                                            !sesi.sudahCheckin
                                                        ? AppColors.amberAccent
                                                              .withValues(
                                                                alpha: 0.4,
                                                              )
                                                        : (sesi.sudahCheckin &&
                                                                      sesi.status ==
                                                                          'Hadir'
                                                                  ? (isDark
                                                                        ? AppColors
                                                                              .hadirTextDark
                                                                        : const Color(
                                                                            0xFF86EFAC,
                                                                          ))
                                                                  : (isDark
                                                                        ? AppColors
                                                                              .sakitTextDark
                                                                        : const Color(
                                                                            0xFFFDE68A,
                                                                          )))
                                                              .withValues(
                                                                alpha: 0.5,
                                                              ),
                                                    width: 0.8,
                                                  ),
                                                ),
                                                child: Text(
                                                  sesi.isBebasKbm &&
                                                          !sesi.sudahCheckin
                                                      ? '⏸ Bebas KBM'
                                                      : (sesi.sudahCheckin
                                                            ? (sesi.status ==
                                                                      'Hadir'
                                                                  ? '✓ Hadir'
                                                                  : '● ${sesi.status}')
                                                            : '● Belum Check-In'),
                                                  style: TextStyle(
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.bold,
                                                    color:
                                                        sesi.isBebasKbm &&
                                                            !sesi.sudahCheckin
                                                        ? AppColors.amberAccent
                                                        : (sesi.sudahCheckin &&
                                                                  sesi.status ==
                                                                      'Hadir'
                                                              ? (isDark
                                                                    ? AppColors
                                                                          .hadirTextDark
                                                                    : AppColors
                                                                          .hadirTextLight)
                                                              : (isDark
                                                                    ? AppColors
                                                                          .sakitTextDark
                                                                    : AppColors
                                                                          .sakitTextLight)),
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 10),
                                          Text(
                                            sesi.isEvent
                                                ? (sesi.namaKegiatan ??
                                                      sesi.mapel)
                                                : sesi.mapel,
                                            style: const TextStyle(
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            sesi.isEvent
                                                ? 'Agenda Kegiatan MDT • Sesi ${sesi.sesi ?? ""}'
                                                : 'Ruangan / Kelas: ${sesi.ruangan} • Guru: ${sesi.guruPengajar}',
                                            style: TextStyle(
                                              fontSize: 12,
                                              color: isDark
                                                  ? const Color(0xFF8D9387)
                                                  : const Color(0xFF73796E),
                                            ),
                                          ),
                                          if (sesi.isBebasKbm) ...[
                                            const SizedBox(height: 8),
                                            Container(
                                              padding:
                                                  const EdgeInsets.symmetric(
                                                    horizontal: 10,
                                                    vertical: 6,
                                                  ),
                                              decoration: BoxDecoration(
                                                color: AppColors.amberAccent
                                                    .withValues(alpha: 0.12),
                                                borderRadius:
                                                    BorderRadius.circular(8),
                                                border: Border.all(
                                                  color: AppColors.amberAccent
                                                      .withValues(alpha: 0.25),
                                                ),
                                              ),
                                              child: Row(
                                                children: [
                                                  const Icon(
                                                    Icons.info_outline_rounded,
                                                    size: 14,
                                                    color:
                                                        AppColors.amberAccent,
                                                  ),
                                                  const SizedBox(width: 6),
                                                  Expanded(
                                                    child: Text(
                                                      sesi.keteranganBebasKbm !=
                                                                  null &&
                                                              sesi
                                                                  .keteranganBebasKbm!
                                                                  .isNotEmpty
                                                          ? 'Sesi Bebas KBM: ${sesi.keteranganBebasKbm}'
                                                          : 'Sesi Bebas KBM (Presensi tidak diberlakukan)',
                                                      style: TextStyle(
                                                        fontSize: 10.5,
                                                        fontWeight:
                                                            FontWeight.w600,
                                                        color: isDark
                                                            ? AppColors
                                                                  .amberAccent
                                                            : const Color(
                                                                0xFFB45309,
                                                              ),
                                                      ),
                                                      maxLines: 2,
                                                      overflow:
                                                          TextOverflow.ellipsis,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                          ],
                                          if (sesi.isMilikWali &&
                                              !sesi.isEvent) ...[
                                            const SizedBox(height: 6),
                                            Container(
                                              padding:
                                                  const EdgeInsets.symmetric(
                                                    horizontal: 8,
                                                    vertical: 2,
                                                  ),
                                              decoration: BoxDecoration(
                                                color: isDark
                                                    ? AppColors
                                                          .primaryContainerDark
                                                          .withValues(
                                                            alpha: 0.3,
                                                          )
                                                    : AppColors
                                                          .primaryContainerLight
                                                          .withValues(
                                                            alpha: 0.6,
                                                          ),
                                                borderRadius:
                                                    BorderRadius.circular(6),
                                                border: Border.all(
                                                  color: isDark
                                                      ? AppColors.primaryDark
                                                            .withValues(
                                                              alpha: 0.3,
                                                            )
                                                      : AppColors.primaryLight
                                                            .withValues(
                                                              alpha: 0.25,
                                                            ),
                                                ),
                                              ),
                                              child: Text(
                                                'Ruangan Binaan (Tanggung Jawab Wali Ruangan)',
                                                style: TextStyle(
                                                  fontSize: 10,
                                                  fontWeight: FontWeight.bold,
                                                  color: isDark
                                                      ? AppColors.primaryDark
                                                      : AppColors.primaryLight,
                                                ),
                                              ),
                                            ),
                                          ],
                                          if (sesi.ustadzPenggantiNama !=
                                                  null &&
                                              !sesi.isEvent) ...[
                                            const SizedBox(height: 4),
                                            Text(
                                              'Digantikan (Badal): ${sesi.ustadzPenggantiNama}',
                                              style: TextStyle(
                                                fontSize: 11,
                                                fontWeight: FontWeight.bold,
                                                color: isDark
                                                    ? AppColors.skyBlueAccent
                                                    : const Color(0xFF0284C7),
                                              ),
                                            ),
                                          ],
                                          if (sesi.keterangan != null &&
                                              sesi.keterangan!.isNotEmpty) ...[
                                            const SizedBox(height: 2),
                                            Text(
                                              'Catatan: ${sesi.keterangan}',
                                              style: TextStyle(
                                                fontSize: 11,
                                                fontStyle: FontStyle.italic,
                                                color: isDark
                                                    ? const Color(0xFF8D9387)
                                                    : const Color(0xFF73796E),
                                              ),
                                            ),
                                          ],
                                          const SizedBox(height: 14),

                                          // Actions Row
                                          if (!sesi.isBebasKbm) ...[
                                            if (!sesi.sudahCheckin) ...[
                                              Row(
                                                children: [
                                                  // 1-Tap Quick Action "Check-In Hadir"
                                                  Expanded(
                                                    flex: 3,
                                                    child: ElevatedButton.icon(
                                                      onPressed: () async {
                                                        HapticHelper.medium();
                                                        final bool success;
                                                        if (sesi.isEvent) {
                                                          success = await presensi
                                                              .checkinKegiatanUstadz(
                                                                kalendarId:
                                                                    sesi.kalendarPendidikanId ??
                                                                    0,
                                                                sesi:
                                                                    sesi.sesi ??
                                                                    '',
                                                                status: 'Hadir',
                                                              );
                                                        } else {
                                                          success = await presensi
                                                              .checkinUstadz(
                                                                jadwalId: sesi
                                                                    .jadwalId,
                                                                status: 'Hadir',
                                                              );
                                                        }
                                                        if (context.mounted &&
                                                            success) {
                                                          ScaffoldMessenger.of(
                                                            context,
                                                          ).showSnackBar(
                                                            SnackBar(
                                                              content: Text(
                                                                sesi.isEvent
                                                                    ? 'Check-in Hadir Sesi ${sesi.sesi} berhasil!'
                                                                    : 'Check-in Hadir untuk ${sesi.guruPengajar} (${sesi.mapel}) berhasil!',
                                                              ),
                                                              backgroundColor:
                                                                  AppColors
                                                                      .hadirTextLight,
                                                              behavior:
                                                                  SnackBarBehavior
                                                                      .floating,
                                                              shape: RoundedRectangleBorder(
                                                                borderRadius:
                                                                    BorderRadius.circular(
                                                                      14,
                                                                    ),
                                                              ),
                                                            ),
                                                          );
                                                        }
                                                      },
                                                      icon: const Icon(
                                                        Icons.touch_app_rounded,
                                                        size: 18,
                                                      ),
                                                      label: Text(
                                                        sesi.isEvent
                                                            ? 'Check-In Hadir'
                                                            : (sesi.isMilikWali
                                                                  ? 'Check-In (${sesi.guruPengajar.split(" ").first})'
                                                                  : 'Check-In Hadir'),
                                                      ),
                                                    ),
                                                  ),
                                                  const SizedBox(width: 8),
                                                  // Opsi Badal / Izin / Sakit
                                                  Expanded(
                                                    flex: 2,
                                                    child: OutlinedButton(
                                                      onPressed: () =>
                                                          _showCheckinModal(
                                                            context,
                                                            sesi,
                                                          ),
                                                      child: Text(
                                                        sesi.isEvent
                                                            ? 'Izin / Sakit'
                                                            : 'Pengganti',
                                                        style: const TextStyle(
                                                          fontSize: 12,
                                                        ),
                                                      ),
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ] else ...[
                                              // Sudah Check-In -> Tombol Ubah Status
                                              SizedBox(
                                                width: double.infinity,
                                                height: 38,
                                                child: OutlinedButton.icon(
                                                  onPressed: () =>
                                                      _showCheckinModal(
                                                        context,
                                                        sesi,
                                                      ),
                                                  icon: const Icon(
                                                    Icons.edit_note_rounded,
                                                    size: 18,
                                                  ),
                                                  label: const Text(
                                                    'Ubah Status Presensi Sesi Ini',
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ],
                                        ],
                                      ),
                                    ),
                                  ),
                                const SizedBox(height: 16),
                              ],
                            );
                          },
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
