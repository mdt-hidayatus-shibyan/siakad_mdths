import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/laporan_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';
import 'detail_rekap_presensi_murid_screen.dart';

class LaporanPresensiMuridScreen extends StatefulWidget {
  final int? initialRuanganId;

  const LaporanPresensiMuridScreen({super.key, this.initialRuanganId});

  @override
  State<LaporanPresensiMuridScreen> createState() =>
      _LaporanPresensiMuridScreenState();
}

class _LaporanPresensiMuridScreenState
    extends State<LaporanPresensiMuridScreen> {
  int? _selectedRuanganId;
  int? _selectedSemesterId;
  int? _selectedBulanId;
  bool _isSemesterInitialized = false;
  String _searchJadwal = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _selectedRuanganId = widget.initialRuanganId;

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });

    _searchController.addListener(() {
      setState(() {
        _searchJadwal = _searchController.text.trim().toLowerCase();
      });
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _loadData() {
    context.read<LaporanProvider>().fetchPresensiMurid(
      ruanganId: _selectedRuanganId,
      semesterId: _selectedSemesterId,
      bulanHijriyahId: _selectedBulanId,
      jadwalId:
          null, // Default null untuk memuat rekap semua jadwal & rekap kelas
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;
    final provider = context.watch<LaporanProvider>();
    final data = provider.presensiMuridData;

    final ruanganList = data?.ruanganList ?? [];
    final semesterList = data?.semesterList ?? [];
    final allBulanList = data?.bulanHijriyahList ?? [];
    final jadwalList = data?.jadwalPelajaranList ?? [];

    if (!_isSemesterInitialized && data != null) {
      _selectedSemesterId = data.selectedSemesterId;
      _isSemesterInitialized = true;
    }

    if (_selectedRuanganId == null && ruanganList.isNotEmpty) {
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

    // Filter jadwal pelajaran berdasarkan kolom pencarian
    final filteredJadwal = jadwalList.where((j) {
      if (_searchJadwal.isEmpty) return true;
      return j.namaMapel.toLowerCase().contains(_searchJadwal) ||
          j.kodeMapel.toLowerCase().contains(_searchJadwal) ||
          j.namaUstadz.toLowerCase().contains(_searchJadwal) ||
          j.hari.toLowerCase().contains(_searchJadwal);
    }).toList();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Laporan Presensi Murid',
        subtitleText: data?.isWaliRuangan == false
            ? 'Mata Pelajaran yang Diampu'
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
            // 1. FORM SELECT FILTER (Ruangan, Semester, Bulan Hijriyah)
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
                        data?.isWaliRuangan == false
                            ? 'Filter Laporan Presensi Mapel'
                            : 'Filter Laporan Presensi Murid',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // 1.1 Form Select Ruangan Kelas (jika ada lebih dari 1 ruangan binaan)
                  if (ruanganList.length > 1) ...[
                    DropdownButtonFormField<int>(
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
                      items: ruanganList.map((r) {
                        return DropdownMenuItem<int>(
                          value: r.id,
                          child: Text(
                            '${r.namaRuangan} (${r.levelNama})',
                            style: const TextStyle(fontSize: 12.5),
                            overflow: TextOverflow.ellipsis,
                          ),
                        );
                      }).toList(),
                      onChanged: (val) {
                        if (val != null && val != _selectedRuanganId) {
                          HapticHelper.light();
                          setState(() {
                            _selectedRuanganId = val;
                          });
                          _loadData();
                        }
                      },
                    ),
                    const SizedBox(height: 12),
                  ],

                  // 1.2 Form Select Semester
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

                  // 1.3 Form Select Bulan Hijriyah (Hanya menampilkan bulan dalam semester terpilih)
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

            // 2. KARTU REKAP KESELURUHAN KELAS (Dapat Diklik untuk Membuka Rekap Murid Seluruh Mapel)
            if (provider.isLoadingPresensiMurid)
              const ShimmerLoadingList(count: 1)
            else
              GlassCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    // Header Baris Info & Persentase
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                data?.isWaliRuangan == false
                                    ? 'REKAP MAPEL YANG DIAMPU'
                                    : 'REKAP KESELURUHAN KELAS',
                                style: const TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                data?.isWaliRuangan == false
                                    ? 'Pengajar: ${data?.waliRuanganNama ?? "-"} • ${data?.totalHariEfektif ?? 0} Sesi Mengajar'
                                    : 'Wali: ${data?.waliRuanganNama ?? "-"} • ${data?.totalHariEfektif ?? 0} Hari Efektif',
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
                            '${data?.persentaseKehadiranKelas ?? 0}%',
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
                        value: ((data?.persentaseKehadiranKelas ?? 0) / 100)
                            .clamp(0.0, 1.0),
                        minHeight: 8,
                        backgroundColor: isDark
                            ? const Color(0xFF263326)
                            : const Color(0xFFE2E8F0),
                        valueColor: AlwaysStoppedAnimation<Color>(primary),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Grid 5 Kotak Status (Hadir, Sakit, Izin, Alpha, Dispen)
                    Row(
                      children: [
                        _buildStatBox(
                          label: 'Hadir',
                          count: data?.totalHadir ?? 0,
                          color: AppColors.hadirTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 5),
                        _buildStatBox(
                          label: 'Sakit',
                          count: data?.totalSakit ?? 0,
                          color: AppColors.sakitTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 5),
                        _buildStatBox(
                          label: 'Izin',
                          count: data?.totalIzin ?? 0,
                          color: AppColors.izinTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 5),
                        _buildStatBox(
                          label: 'Alpha',
                          count: data?.totalAlpha ?? 0,
                          color: AppColors.alphaTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 5),
                        _buildStatBox(
                          label: 'Dispen',
                          count: data?.totalDispensasi ?? 0,
                          color: AppColors.violetAccent,
                          isDark: isDark,
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Tombol Aksi Buka Rekap Seluruh Murid Kelas
                    InkWell(
                      onTap: () {
                        HapticHelper.medium();
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => DetailRekapPresensiMuridScreen(
                              ruanganId: _selectedRuanganId,
                              semesterId: _selectedSemesterId,
                              bulanHijriyahId: _selectedBulanId,
                              jadwalId: null,
                              title: data?.isWaliRuangan == false
                                  ? 'Rekap Santri Diampu'
                                  : 'Rekap Keseluruhan Kelas',
                              namaRuangan: data?.namaRuangan,
                            ),
                          ),
                        );
                      },
                      borderRadius: BorderRadius.circular(10),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          vertical: 10,
                          horizontal: 14,
                        ),
                        decoration: BoxDecoration(
                          color: primary.withValues(alpha: isDark ? 0.18 : 0.1),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: primary.withValues(alpha: 0.35),
                          ),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.people_alt_rounded,
                              size: 16,
                              color: primary,
                            ),
                            const SizedBox(width: 8),
                            Text(
                              data?.isWaliRuangan == false
                                  ? 'Lihat Rekap Seluruh Santri yang Diampu'
                                  : 'Lihat Rekap Seluruh Murid (Rekap Kelas)',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: primary,
                              ),
                            ),
                            const SizedBox(width: 4),
                            Icon(
                              Icons.arrow_forward_rounded,
                              size: 14,
                              color: primary,
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 18),

            // 3. DAFTAR REKAPITULASI PER JADWAL PELAJARAN
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.calendar_view_week_rounded,
                      size: 18,
                      color: primary,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      data?.isWaliRuangan == false
                          ? 'Jadwal yang Anda Ampu'
                          : 'Rekap Per Jadwal Pelajaran',
                      style: const TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                Text(
                  '${filteredJadwal.length} Jadwal',
                  style: TextStyle(
                    fontSize: 11,
                    color: isDark
                        ? const Color(0xFF94A3B8)
                        : const Color(0xFF64748B),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),

            // Search Bar Mapel / Ustadz
            TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari mata pelajaran atau pengajar...',
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
            const SizedBox(height: 10),

            if (provider.isLoadingPresensiMurid)
              const ShimmerLoadingList(count: 3)
            else if (filteredJadwal.isEmpty)
              const GlassCard(
                padding: EdgeInsets.all(28),
                child: Center(
                  child: Text('Tidak ada jadwal pelajaran yang sesuai.'),
                ),
              )
            else
              ...filteredJadwal.asMap().entries.map((entry) {
                final idx = entry.key + 1;
                final jadwal = entry.value;

                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  child: InkWell(
                    onTap: () {
                      HapticHelper.light();
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => DetailRekapPresensiMuridScreen(
                            ruanganId: _selectedRuanganId,
                            semesterId: _selectedSemesterId,
                            bulanHijriyahId: _selectedBulanId,
                            jadwalId: jadwal.id,
                            title: '${jadwal.namaMapel} (${jadwal.hari})',
                            namaMapel: jadwal.namaMapel,
                            namaUstadz: jadwal.namaUstadz,
                            namaRuangan: data?.namaRuangan,
                            jadwalInfo: '${jadwal.hari} • ${jadwal.jamText}',
                          ),
                        ),
                      );
                    },
                    borderRadius: BorderRadius.circular(12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Baris 1: No, Mapel, Pengampu, % Hadir
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              width: 28,
                              height: 28,
                              decoration: BoxDecoration(
                                color: primary.withValues(
                                  alpha: isDark ? 0.2 : 0.12,
                                ),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Center(
                                child: Text(
                                  '$idx',
                                  style: TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.bold,
                                    color: primary,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    jadwal.namaMapel,
                                    style: const TextStyle(
                                      fontSize: 13.5,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${jadwal.hari} • ${jadwal.jamText} • ${jadwal.namaUstadz}',
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
                                horizontal: 8,
                                vertical: 3.5,
                              ),
                              decoration: BoxDecoration(
                                color: primary.withValues(
                                  alpha: isDark ? 0.2 : 0.12,
                                ),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '${jadwal.persentaseKehadiran}%',
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

                        // Baris 2: Mini Badges (H/S/I/A/D) & Aksi Buka Rekap Murid
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                _buildMiniBadge(
                                  'H: ${jadwal.totalHadir}',
                                  AppColors.hadirTextLight,
                                  isDark,
                                ),
                                const SizedBox(width: 4),
                                _buildMiniBadge(
                                  'S: ${jadwal.totalSakit}',
                                  AppColors.sakitTextLight,
                                  isDark,
                                ),
                                const SizedBox(width: 4),
                                _buildMiniBadge(
                                  'I: ${jadwal.totalIzin}',
                                  AppColors.izinTextLight,
                                  isDark,
                                ),
                                const SizedBox(width: 4),
                                _buildMiniBadge(
                                  'A: ${jadwal.totalAlpha}',
                                  AppColors.alphaTextLight,
                                  isDark,
                                ),
                                const SizedBox(width: 4),
                                _buildMiniBadge(
                                  'D: ${jadwal.totalDispensasi}',
                                  AppColors.violetAccent,
                                  isDark,
                                ),
                              ],
                            ),
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  'Rekap Murid',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                    color: primary,
                                  ),
                                ),
                                const SizedBox(width: 3),
                                Icon(
                                  Icons.arrow_forward_ios_rounded,
                                  size: 11,
                                  color: primary,
                                ),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              }),
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
