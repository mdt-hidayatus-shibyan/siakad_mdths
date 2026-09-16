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

class PresensiTab extends StatefulWidget {
  final VoidCallback? onNavigateToUjian;

  const PresensiTab({super.key, this.onNavigateToUjian});

  @override
  State<PresensiTab> createState() => _PresensiTabState();
}

class _PresensiTabState extends State<PresensiTab>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
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
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _shiftDateMurid(int days) {
    HapticHelper.selection();
    final p = context.read<PresensiProvider>();
    p.setSelectedDate(p.selectedDate.add(Duration(days: days)));
  }

  void _shiftDateUstadz(int days) {
    HapticHelper.selection();
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
      provider.setSelectedDateUstadz(picked);
    }
  }

  void _showCheckinModal(BuildContext context, SesiPresensiUstadzItem sesi) {
    CheckinUstadzSheet.show(context, sesi);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final presensi = context.watch<PresensiProvider>();

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Presensi'),
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
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                    children: [
                      // 1. DATE PICKER SELECTOR BAR
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF162016)
                              : Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: isDark
                                ? const Color(0xFF263326)
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
                                'Presensi KBM reguler dinonaktifkan pada tanggal pelaksanaan ujian. Silakan gunakan modul Presensi Ujian untuk mencatat kehadiran santri.',
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
                                'Presensi kehadiran santri tidak dibuka pada hari libur madrasah.',
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
                        // 2. Kondisi Hari Aktif KBM: Tampilkan Daftar Sesi Mengajar
                        const Text(
                          'Daftar Sesi Mengajar Murid',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 12),

                        if (presensi.isLoading)
                          const ShimmerLoadingList(count: 3)
                        else if (presensi.sesiList.isEmpty)
                          const GlassCard(
                            padding: EdgeInsets.all(24),
                            child: Center(
                              child: Text(
                                'Tidak ada jadwal mengajar pada tanggal ini.',
                              ),
                            ),
                          )
                        else
                          ...presensi.sesiList.map(
                            (sesi) => GlassCard(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 3,
                                        ),
                                        decoration: BoxDecoration(
                                          color: isDark
                                              ? const Color(0xFF101710)
                                              : const Color(0xFFE8F5E9),
                                          borderRadius: BorderRadius.circular(
                                            8,
                                          ),
                                        ),
                                        child: Text(
                                          sesi.jam,
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                            color: isDark
                                                ? AppColors.primaryDark
                                                : AppColors.primaryLight,
                                          ),
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 3,
                                        ),
                                        decoration: BoxDecoration(
                                          color: sesi.sudahAbsen
                                              ? (isDark
                                                    ? AppColors.hadirBgDark
                                                    : AppColors.hadirBgLight)
                                              : (isDark
                                                    ? const Color(0xFF451A03)
                                                    : const Color(0xFFFEF3C7)),
                                          borderRadius: BorderRadius.circular(
                                            8,
                                          ),
                                        ),
                                        child: Text(
                                          sesi.sudahAbsen
                                              ? '✓ Selesai'
                                              : '● Belum Absen',
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                            color: sesi.sudahAbsen
                                                ? (isDark
                                                      ? AppColors.hadirTextDark
                                                      : AppColors
                                                            .hadirTextLight)
                                                : (isDark
                                                      ? AppColors.sakitTextDark
                                                      : AppColors
                                                            .sakitTextLight),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),
                                  Text(
                                    sesi.pelajaran,
                                    style: const TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Ruangan: ${sesi.kelas} • Guru: ${sesi.guru}',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E),
                                    ),
                                  ),
                                  if (sesi.isMilikWali) ...[
                                    const SizedBox(height: 6),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 8,
                                        vertical: 2,
                                      ),
                                      decoration: BoxDecoration(
                                        color: isDark
                                            ? const Color(0xFF241538)
                                            : const Color(0xFFF3E8FF),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        'Ruangan Binaan (Akses Wali Ruangan)',
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                          color: isDark
                                              ? AppColors.primaryDark
                                              : const Color.fromARGB(
                                                  255,
                                                  35,
                                                  180,
                                                  47,
                                                ),
                                        ),
                                      ),
                                    ),
                                  ],
                                  const SizedBox(height: 14),
                                  SizedBox(
                                    width: double.infinity,
                                    height: 40,
                                    child: ElevatedButton.icon(
                                      onPressed: () {
                                        HapticHelper.light();
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(
                                            builder: (_) => FormPresensiScreen(
                                              jadwalId: sesi.id,
                                              mapel: sesi.pelajaran,
                                              ruangan: sesi.kelas,
                                              jam: sesi.jam,
                                            ),
                                          ),
                                        );
                                      },
                                      icon: Icon(
                                        sesi.sudahAbsen
                                            ? Icons.edit_note_rounded
                                            : Icons.how_to_reg_rounded,
                                        size: 18,
                                      ),
                                      label: Text(
                                        sesi.sudahAbsen
                                            ? 'Edit Presensi Kelas'
                                            : 'Buka Absensi Kelas',
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
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
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                    children: [
                      // 1. DATE PICKER SELECTOR BAR
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF162016)
                              : Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: isDark
                                ? const Color(0xFF263326)
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
                        // 2. Sesi Mengajar Ustadz & Tombol Check-In Mandiri
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'Jadwal Mengajar & Check-In',
                              style: TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              '${presensi.sesiUstadzList.length} Jadwal',
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
                        else if (presensi.sesiUstadzList.isEmpty)
                          const GlassCard(
                            padding: EdgeInsets.all(20),
                            child: Center(
                              child: Text(
                                'Anda tidak memiliki jadwal mengajar pada tanggal ini.',
                                style: TextStyle(fontSize: 13),
                              ),
                            ),
                          )
                        else
                          ...presensi.sesiUstadzList.map(
                            (sesi) => GlassCard(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 3,
                                        ),
                                        decoration: BoxDecoration(
                                          color: isDark
                                              ? const Color(0xFF101710)
                                              : const Color(0xFFE8F5E9),
                                          borderRadius: BorderRadius.circular(
                                            8,
                                          ),
                                        ),
                                        child: Text(
                                          sesi.jam,
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                            color: isDark
                                                ? AppColors.primaryDark
                                                : AppColors.primaryLight,
                                          ),
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 3,
                                        ),
                                        decoration: BoxDecoration(
                                          color:
                                              sesi.sudahCheckin &&
                                                  sesi.status == 'Hadir'
                                              ? (isDark
                                                    ? AppColors.hadirBgDark
                                                    : AppColors.hadirBgLight)
                                              : (isDark
                                                    ? const Color(0xFF451A03)
                                                    : const Color(0xFFFEF3C7)),
                                          borderRadius: BorderRadius.circular(
                                            8,
                                          ),
                                        ),
                                        child: Text(
                                          sesi.sudahCheckin
                                              ? (sesi.status == 'Hadir'
                                                    ? '✓ Hadir'
                                                    : '● ${sesi.status}')
                                              : '● Belum Check-In',
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                            color:
                                                sesi.sudahCheckin &&
                                                    sesi.status == 'Hadir'
                                                ? (isDark
                                                      ? AppColors.hadirTextDark
                                                      : AppColors
                                                            .hadirTextLight)
                                                : (isDark
                                                      ? AppColors.sakitTextDark
                                                      : AppColors
                                                            .sakitTextLight),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),
                                  Text(
                                    sesi.mapel,
                                    style: const TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Ruangan / Kelas: ${sesi.ruangan} • Guru: ${sesi.guruPengajar}',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E),
                                    ),
                                  ),
                                  if (sesi.isMilikWali) ...[
                                    const SizedBox(height: 6),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 8,
                                        vertical: 2,
                                      ),
                                      decoration: BoxDecoration(
                                        color: isDark
                                            ? const Color(0xFF241538)
                                            : const Color(0xFFF3E8FF),
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(
                                          color: isDark
                                              ? AppColors.primaryDark
                                                    .withValues(alpha: 0.4)
                                              : const Color(0xFFD8B4FE),
                                        ),
                                      ),
                                      child: Text(
                                        'Ruangan Binaan (Tanggung Jawab Wali Ruangan)',
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                          color: isDark
                                              ? AppColors.primaryDark
                                              : const Color(0xFF6D28D9),
                                        ),
                                      ),
                                    ),
                                  ],
                                  if (sesi.ustadzPenggantiNama != null) ...[
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
                                  if (!sesi.sudahCheckin) ...[
                                    Row(
                                      children: [
                                        // 1-Tap Quick Action "Check-In Hadir"
                                        Expanded(
                                          flex: 3,
                                          child: ElevatedButton.icon(
                                            onPressed: () async {
                                              HapticHelper.medium();
                                              final success = await presensi
                                                  .checkinUstadz(
                                                    jadwalId: sesi.jadwalId,
                                                    status: 'Hadir',
                                                  );
                                              if (context.mounted && success) {
                                                ScaffoldMessenger.of(
                                                  context,
                                                ).showSnackBar(
                                                  SnackBar(
                                                    content: Text(
                                                      'Check-in Hadir untuk ${sesi.guruPengajar} (${sesi.mapel}) berhasil!',
                                                    ),
                                                    backgroundColor: AppColors
                                                        .hadirTextLight,
                                                    behavior: SnackBarBehavior
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
                                              sesi.isMilikWali
                                                  ? 'Check-In (${sesi.guruPengajar.split(" ").first})'
                                                  : 'Check-In Hadir',
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        // Opsi Badal / Izin
                                        Expanded(
                                          flex: 2,
                                          child: OutlinedButton(
                                            onPressed: () => _showCheckinModal(
                                              context,
                                              sesi,
                                            ),
                                            child: const Text(
                                              'Badal / Izin',
                                              style: TextStyle(fontSize: 12),
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
                                            _showCheckinModal(context, sesi),
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
                              ),
                            ),
                          ),
                        const SizedBox(height: 16),
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
