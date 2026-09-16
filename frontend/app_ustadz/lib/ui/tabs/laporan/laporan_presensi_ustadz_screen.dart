import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/laporan_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';

class LaporanPresensiUstadzScreen extends StatefulWidget {
  final int? initialRuanganId;
  final bool isRuanganBinaan;

  const LaporanPresensiUstadzScreen({
    super.key,
    this.initialRuanganId,
    this.isRuanganBinaan = false,
  });

  @override
  State<LaporanPresensiUstadzScreen> createState() =>
      _LaporanPresensiUstadzScreenState();
}

class _LaporanPresensiUstadzScreenState
    extends State<LaporanPresensiUstadzScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  int? _selectedRuanganId;
  int? _selectedUstadzId;
  int? _selectedSemesterId;
  int? _selectedBulanId;
  final String _selectedStatus = 'Semua';
  bool _isSemesterInitialized = false;
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) setState(() {});
    });

    _selectedRuanganId = widget.initialRuanganId;

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });

    _searchController.addListener(() {
      setState(() {
        _searchQuery = _searchController.text.trim().toLowerCase();
      });
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _loadData() {
    context.read<LaporanProvider>().fetchPresensiUstadz(
      ruanganId: _selectedRuanganId,
      ustadzId: widget.isRuanganBinaan ? _selectedUstadzId : null,
      semesterId: _selectedSemesterId,
      bulanHijriyahId: _selectedBulanId,
      status: _selectedStatus == 'Semua' ? null : _selectedStatus,
      isPribadi: !widget.isRuanganBinaan,
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;
    final provider = context.watch<LaporanProvider>();
    final data = provider.presensiUstadzData;
    final isWaliMode = widget.isRuanganBinaan;

    final ruanganList = data?.ruanganList ?? [];
    final semesterList = data?.semesterList ?? [];
    final allBulanList = data?.bulanHijriyahList ?? [];
    final daftarUstadz = data?.daftarUstadz ?? [];

    if (!_isSemesterInitialized && data != null) {
      _selectedSemesterId = data.selectedSemesterId;
      _isSemesterInitialized = true;
    }

    if (_selectedRuanganId == null && ruanganList.isNotEmpty && isWaliMode) {
      _selectedRuanganId = ruanganList.first.id;
    }

    // Filter daftar bulan yang hanya masuk pada semester terpilih
    final filteredBulanList = allBulanList.where((b) {
      if (_selectedSemesterId == null) return true;
      return b.semesterId == _selectedSemesterId;
    }).toList();

    // Reset pilihan bulan jika bulan terpilih sebelumnya tidak ada di semester yang baru dipilih
    if (_selectedBulanId != null && filteredBulanList.isNotEmpty) {
      final existsInFiltered = filteredBulanList.any(
        (b) => b.id == _selectedBulanId,
      );
      if (!existsInFiltered) {
        _selectedBulanId = null;
      }
    }

    final allRekapUstadz = data?.rekapUstadz ?? [];
    final filteredRekapUstadz = allRekapUstadz.where((u) {
      if (_searchQuery.isEmpty) return true;
      final matchNama = u.nama.toLowerCase().contains(_searchQuery);
      final matchMapel = u.mapelList.any(
        (m) => m.toLowerCase().contains(_searchQuery),
      );
      return matchNama || matchMapel;
    }).toList();

    final allRiwayat = data?.riwayat ?? [];
    final filteredRiwayat = allRiwayat.where((r) {
      if (_searchQuery.isEmpty) return true;
      return r.mapel.toLowerCase().contains(_searchQuery) ||
          r.namaUstadz.toLowerCase().contains(_searchQuery) ||
          r.tanggal.toLowerCase().contains(_searchQuery) ||
          (r.hariTanggal?.toLowerCase().contains(_searchQuery) ?? false) ||
          r.keterangan.toLowerCase().contains(_searchQuery);
    }).toList();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: !isWaliMode
            ? 'Presensi Mengajar Saya'
            : 'Laporan Presensi Ustadz',
        subtitleText: !isWaliMode
            ? 'Kehadiran Mengajar Pribadi (${data?.namaRuangan ?? "Semua Kelas"})'
            : (data?.namaRuangan.isNotEmpty == true
                  ? '${data?.namaRuangan} (${data?.levelNama})'
                  : 'Kelas Binaan'),
      ),
      body: RefreshIndicator(
        onRefresh: () async => _loadData(),
        color: primary,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
          children: [
            // 1. FORM SELECT FILTER (Ruangan, Ustadz Pengajar, Semester, Bulan Hijriyah)
            GlassCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(Icons.tune_rounded, size: 16, color: primary),
                      const SizedBox(width: 6),
                      Text(
                        !isWaliMode
                            ? 'Filter Presensi Mengajar'
                            : 'Filter Laporan Presensi Ustadz',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // 1.1 Form Select Ruangan Kelas
                  if (ruanganList.length > 1 || !isWaliMode) ...[
                    DropdownButtonFormField<int?>(
                      key: ValueKey('ruangan_$_selectedRuanganId'),
                      initialValue: _selectedRuanganId,
                      isExpanded: true,
                      decoration: const InputDecoration(
                        labelText: 'Ruangan Kelas',
                        prefixIcon: Icon(Icons.meeting_room_rounded, size: 18),
                        contentPadding: EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 10,
                        ),
                      ),
                      items: [
                        if (!isWaliMode)
                          const DropdownMenuItem<int?>(
                            value: null,
                            child: Text(
                              'Semua Kelas yang Diampu',
                              style: TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w600,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ...ruanganList.map((r) {
                          return DropdownMenuItem<int?>(
                            value: r.id,
                            child: Text(
                              '${r.namaRuangan} (${r.levelNama})',
                              style: const TextStyle(fontSize: 12.5),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }),
                      ],
                      onChanged: (val) {
                        if (val != _selectedRuanganId) {
                          HapticHelper.light();
                          setState(() => _selectedRuanganId = val);
                          _loadData();
                        }
                      },
                    ),
                    const SizedBox(height: 12),
                  ],

                  // 1.2 Form Select Ustadz Pengajar (HANYA DITAMPILKAN DI MODE WALI RUANGAN KELAS BINAAN)
                  if (isWaliMode) ...[
                    DropdownButtonFormField<int?>(
                      key: ValueKey('ustadz_$_selectedUstadzId'),
                      initialValue: _selectedUstadzId,
                      isExpanded: true,
                      decoration: const InputDecoration(
                        labelText: 'Pilih Ustadz Pengajar',
                        prefixIcon: Icon(Icons.person_pin_rounded, size: 18),
                        contentPadding: EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 10,
                        ),
                      ),
                      items: [
                        const DropdownMenuItem<int?>(
                          value: null,
                          child: Text(
                            'Semua Ustadz Pengajar (Rekap Kelas)',
                            style: TextStyle(
                              fontSize: 12.5,
                              fontWeight: FontWeight.w600,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        ...daftarUstadz.map((u) {
                          return DropdownMenuItem<int?>(
                            value: u.id,
                            child: Text(
                              u.nama,
                              style: const TextStyle(fontSize: 12.5),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }),
                      ],
                      onChanged: (val) {
                        if (val != _selectedUstadzId) {
                          HapticHelper.light();
                          setState(() => _selectedUstadzId = val);
                          _loadData();
                        }
                      },
                    ),
                    const SizedBox(height: 12),
                  ],

                  // 1.3 Form Select Semester
                  DropdownButtonFormField<int?>(
                    key: ValueKey('semester_$_selectedSemesterId'),
                    initialValue: _selectedSemesterId,
                    isExpanded: true,
                    decoration: const InputDecoration(
                      labelText: 'Pilih Semester',
                      prefixIcon: Icon(Icons.date_range_rounded, size: 18),
                      contentPadding: EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 10,
                      ),
                    ),
                    items: [
                      const DropdownMenuItem<int?>(
                        value: null,
                        child: Text(
                          'Semua Semester',
                          style: TextStyle(fontSize: 12.5),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      ...semesterList.map((sem) {
                        return DropdownMenuItem<int?>(
                          value: sem.id,
                          child: Text(
                            sem.namaSemester,
                            style: const TextStyle(fontSize: 12.5),
                            overflow: TextOverflow.ellipsis,
                          ),
                        );
                      }),
                    ],
                    onChanged: (val) {
                      HapticHelper.light();
                      setState(() {
                        _selectedSemesterId = val;
                        // Jika bulan terpilih tidak termasuk dalam semester baru, reset bulan ke null
                        if (_selectedBulanId != null) {
                          final existsInSem = allBulanList.any(
                            (b) =>
                                b.id == _selectedBulanId &&
                                (val == null || b.semesterId == val),
                          );
                          if (!existsInSem) {
                            _selectedBulanId = null;
                          }
                        }
                      });
                      _loadData();
                    },
                  ),
                  const SizedBox(height: 12),

                  // 1.4 Form Select Bulan Hijriyah (Hanya menampilkan bulan dalam semester terpilih)
                  DropdownButtonFormField<int?>(
                    key: ValueKey(
                      'bulan_${_selectedSemesterId}_$_selectedBulanId',
                    ),
                    initialValue: _selectedBulanId,
                    isExpanded: true,
                    decoration: InputDecoration(
                      labelText: 'Pilih Bulan Hijriyah',
                      helperText: _selectedSemesterId != null
                          ? 'Pilihan bulan otomatis dibatasi sesuai semester terpilih'
                          : 'Menampilkan semua bulan pada tahun ajaran aktif',
                      helperStyle: TextStyle(
                        fontSize: 10,
                        color: isDark
                            ? const Color(0xFF94A3B8)
                            : const Color(0xFF64748B),
                      ),
                      prefixIcon: const Icon(
                        Icons.calendar_month_rounded,
                        size: 18,
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 10,
                      ),
                    ),
                    items: [
                      DropdownMenuItem<int?>(
                        value: null,
                        child: Text(
                          _selectedSemesterId != null
                              ? 'Semua Bulan (${semesterList.firstWhere((s) => s.id == _selectedSemesterId, orElse: () => semesterList.first).namaSemester})'
                              : 'Semua Bulan (1 Tahun Penuh)',
                          style: const TextStyle(fontSize: 12.5),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      ...filteredBulanList.map((b) {
                        return DropdownMenuItem<int?>(
                          value: b.id,
                          child: Text(
                            '${b.namaBulan} ${b.tahunHijriyah}',
                            style: const TextStyle(fontSize: 12.5),
                            overflow: TextOverflow.ellipsis,
                          ),
                        );
                      }),
                    ],
                    onChanged: (val) {
                      HapticHelper.light();
                      setState(() => _selectedBulanId = val);
                      _loadData();
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // 2. RINGKASAN STATISTIK KEHADIRAN MENGAJAR RUANGAN
            if (provider.isLoadingPresensiUstadz)
              const ShimmerLoadingList(count: 1)
            else
              GlassCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    // Header Baris Persentase & Info Ruangan / Ustadz Terpilih
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                !isWaliMode
                                    ? 'Kehadiran Mengajar Saya'
                                    : (_selectedUstadzId != null
                                          ? (daftarUstadz
                                                .firstWhere(
                                                  (u) =>
                                                      u.id == _selectedUstadzId,
                                                  orElse: () =>
                                                      daftarUstadz.first,
                                                )
                                                .nama)
                                          : 'Kehadiran Mengajar di Kelas'),
                                style: const TextStyle(
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.bold,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${data?.totalSesi ?? 0} Sesi Pertemuan Mengajar Terlaksana',
                                style: TextStyle(
                                  fontSize: 10.5,
                                  color: isDark
                                      ? const Color(0xFF94A3B8)
                                      : const Color(0xFF64748B),
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
                            color: primary.withValues(
                              alpha: isDark ? 0.22 : 0.12,
                            ),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(
                              color: primary.withValues(alpha: 0.35),
                            ),
                          ),
                          child: Text(
                            '${data?.persentaseKehadiran ?? 0}%',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w900,
                              color: primary,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Progress Bar
                    ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: LinearProgressIndicator(
                        value: ((data?.persentaseKehadiran ?? 0) / 100).clamp(
                          0.0,
                          1.0,
                        ),
                        minHeight: 8,
                        backgroundColor: isDark
                            ? const Color(0xFF263326)
                            : const Color(0xFFE2E8F0),
                        valueColor: AlwaysStoppedAnimation<Color>(primary),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Grid 5 Kotak Status (Hadir, Tugas, Izin, Sakit, Alpha)
                    Row(
                      children: [
                        _buildStatBox(
                          label: 'Hadir',
                          count: data?.totalHadir ?? 0,
                          color: AppColors.hadirTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 6),
                        _buildStatBox(
                          label: 'Tugas',
                          count: data?.totalTugas ?? 0,
                          color: AppColors.violetAccent,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 6),
                        _buildStatBox(
                          label: 'Izin',
                          count: data?.totalIzin ?? 0,
                          color: AppColors.izinTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 6),
                        _buildStatBox(
                          label: 'Sakit',
                          count: data?.totalSakit ?? 0,
                          color: AppColors.sakitTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 6),
                        _buildStatBox(
                          label: 'Alpha',
                          count: data?.totalAlpha ?? 0,
                          color: AppColors.alphaTextLight,
                          isDark: isDark,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 14),

            // 3. SEGMENTED TAB BAR (Rekap Per Ustadz | Riwayat Sesi Mengajar)
            SegmentedTabBar(
              selectedIndex: _tabController.index,
              margin: EdgeInsets.zero,
              items: [
                SegmentedTabItem(
                  label: !isWaliMode ? 'Rekap Kehadiran' : 'Rekap Per Ustadz',
                  activeIcon: Icons.people_alt_rounded,
                  inactiveIcon: Icons.people_alt_outlined,
                ),
                const SegmentedTabItem(
                  label: 'Riwayat Mengajar',
                  activeIcon: Icons.history_rounded,
                  inactiveIcon: Icons.history_outlined,
                ),
              ],
              onTabChanged: (idx) {
                HapticHelper.selection();
                _tabController.animateTo(idx);
                setState(() {});
              },
            ),
            const SizedBox(height: 12),

            // Search Bar Filter
            TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: _tabController.index == 0
                    ? (!isWaliMode
                          ? 'Cari mata pelajaran atau catatan...'
                          : 'Cari nama ustadz atau mapel...')
                    : 'Cari mata pelajaran, ustadz, tanggal, atau catatan...',
                prefixIcon: const Icon(Icons.search_rounded, size: 20),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded, size: 18),
                        onPressed: () => _searchController.clear(),
                      )
                    : null,
                filled: true,
                fillColor: isDark ? const Color(0xFF162016) : Colors.white,
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide(
                    color: isDark
                        ? const Color(0xFF263326)
                        : const Color(0xFFE2E8F0),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),

            // 4. ISI KONTEN TAB
            if (_tabController.index == 0) ...[
              // === TAB 0: REKAP PER USTADZ PENGAJAR ===
              if (provider.isLoadingPresensiUstadz)
                const ShimmerLoadingList(count: 3)
              else if (filteredRekapUstadz.isEmpty)
                GlassCard(
                  padding: const EdgeInsets.all(28),
                  child: Center(
                    child: Text(
                      !isWaliMode
                          ? 'Belum ada data rekap mengajar pada periode ini.'
                          : 'Tidak ada data ustadz pengajar yang ditemukan.',
                    ),
                  ),
                )
              else
                ...filteredRekapUstadz.map((ustadz) {
                  return GlassCard(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(14),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Baris 1: Avatar, Nama, NIUP, Mapel
                        Row(
                          children: [
                            CircleAvatar(
                              radius: 20,
                              backgroundColor: primary.withValues(
                                alpha: isDark ? 0.2 : 0.12,
                              ),
                              child: Text(
                                ustadz.nama.isNotEmpty
                                    ? ustadz.nama[0].toUpperCase()
                                    : 'U',
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: primary,
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    ustadz.nama,
                                    style: const TextStyle(
                                      fontSize: 13.5,
                                      fontWeight: FontWeight.bold,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    ustadz.mapelList.isNotEmpty
                                        ? 'Mapel: ${ustadz.mapelList.join(", ")}'
                                        : 'NIUP: ${ustadz.niup}',
                                    style: TextStyle(
                                      fontSize: 10.5,
                                      color: isDark
                                          ? const Color(0xFF94A3B8)
                                          : const Color(0xFF64748B),
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ],
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: primary.withValues(
                                  alpha: isDark ? 0.2 : 0.12,
                                ),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '${ustadz.persentaseKehadiran}%',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w900,
                                  color: primary,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        // Baris 2: Breakdown Mini Badge
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              '${ustadz.totalSesi} Sesi Pertemuan',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Row(
                              children: [
                                _buildMiniBadge(
                                  'H: ${ustadz.totalHadir}',
                                  AppColors.hadirTextLight,
                                  isDark,
                                ),
                                const SizedBox(width: 4),
                                _buildMiniBadge(
                                  'T: ${ustadz.totalTugas}',
                                  AppColors.violetAccent,
                                  isDark,
                                ),
                                const SizedBox(width: 4),
                                _buildMiniBadge(
                                  'I: ${ustadz.totalIzin}',
                                  AppColors.izinTextLight,
                                  isDark,
                                ),
                                const SizedBox(width: 4),
                                _buildMiniBadge(
                                  'S: ${ustadz.totalSakit}',
                                  AppColors.sakitTextLight,
                                  isDark,
                                ),
                                const SizedBox(width: 4),
                                _buildMiniBadge(
                                  'A: ${ustadz.totalAlpha}',
                                  AppColors.alphaTextLight,
                                  isDark,
                                ),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  );
                }),
            ] else ...[
              // === TAB 1: RIWAYAT SESI MENGAJAR ===
              if (provider.isLoadingPresensiUstadz)
                const ShimmerLoadingList(count: 4)
              else if (filteredRiwayat.isEmpty)
                GlassCard(
                  padding: const EdgeInsets.all(28),
                  child: Center(
                    child: Text(
                      !isWaliMode
                          ? 'Belum ada catatan riwayat sesi mengajar pada periode ini.'
                          : 'Belum ada catatan riwayat sesi mengajar di kelas ini.',
                      style: const TextStyle(fontSize: 13),
                    ),
                  ),
                )
              else
                ...filteredRiwayat.map((r) {
                  Color badgeColor = AppColors.hadirTextLight;
                  if (r.status == 'Tugas') {
                    badgeColor = AppColors.violetAccent;
                  } else if (r.status == 'Izin') {
                    badgeColor = AppColors.izinTextLight;
                  } else if (r.status == 'Sakit') {
                    badgeColor = AppColors.sakitTextLight;
                  } else if (r.status == 'Alpha') {
                    badgeColor = AppColors.alphaTextLight;
                  }

                  return GlassCard(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(14),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Badge Status
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 9,
                            vertical: 5,
                          ),
                          decoration: BoxDecoration(
                            color: badgeColor.withValues(
                              alpha: isDark ? 0.2 : 0.12,
                            ),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: badgeColor.withValues(alpha: 0.35),
                            ),
                          ),
                          child: Text(
                            r.status,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: badgeColor,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),

                        // Info Detail Sesi
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${r.mapel} • ${r.namaUstadz}',
                                style: const TextStyle(
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${r.hariTanggal ?? r.tanggal} • ${r.namaRuangan}',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isDark
                                      ? const Color(0xFF94A3B8)
                                      : const Color(0xFF64748B),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Row(
                                children: [
                                  Icon(
                                    Icons.access_time_rounded,
                                    size: 13,
                                    color: isDark
                                        ? const Color(0xFF94A3B8)
                                        : const Color(0xFF64748B),
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    r.jamKeluar != null
                                        ? 'Masuk: ${r.jamMasuk} • Pulang: ${r.jamKeluar}'
                                        : 'Jam Masuk: ${r.jamMasuk}',
                                    style: TextStyle(
                                      fontSize: 10.5,
                                      color: isDark
                                          ? const Color(0xFF94A3B8)
                                          : const Color(0xFF64748B),
                                    ),
                                  ),
                                ],
                              ),
                              if (r.keterangan != '-' &&
                                  r.keterangan.isNotEmpty) ...[
                                const SizedBox(height: 4),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: isDark
                                        ? Colors.white.withValues(alpha: 0.05)
                                        : Colors.black.withValues(alpha: 0.04),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    'Catatan: ${r.keterangan}',
                                    style: TextStyle(
                                      fontSize: 10.5,
                                      fontStyle: FontStyle.italic,
                                      color: isDark
                                          ? const Color(0xFF94A3B8)
                                          : const Color(0xFF64748B),
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                }),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatBox({
    required String label,
    required int count,
    required Color color,
    required bool isDark,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 2),
        decoration: BoxDecoration(
          color: color.withValues(alpha: isDark ? 0.15 : 0.1),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Column(
          children: [
            Text(
              '$count',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w900,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 9.5,
                fontWeight: FontWeight.w600,
                color: isDark
                    ? const Color(0xFF94A3B8)
                    : const Color(0xFF64748B),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMiniBadge(String text, Color color, bool isDark) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: isDark ? 0.18 : 0.12),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 9.5,
          fontWeight: FontWeight.bold,
          color: color,
        ),
      ),
    );
  }
}
