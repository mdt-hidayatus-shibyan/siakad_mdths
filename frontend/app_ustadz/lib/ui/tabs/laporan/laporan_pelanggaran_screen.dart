import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/laporan_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';

class LaporanPelanggaranScreen extends StatefulWidget {
  final int? initialRuanganId;

  const LaporanPelanggaranScreen({super.key, this.initialRuanganId});

  @override
  State<LaporanPelanggaranScreen> createState() =>
      _LaporanPelanggaranScreenState();
}

class _LaporanPelanggaranScreenState extends State<LaporanPelanggaranScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  int? _selectedRuanganId;
  String _selectedKategori = 'Semua';
  String _searchSantri = '';
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
        _searchSantri = _searchController.text.trim().toLowerCase();
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
    context.read<LaporanProvider>().fetchPelanggaranMurid(
      ruanganId: _selectedRuanganId,
      kategori: _selectedKategori == 'Semua' ? null : _selectedKategori,
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;
    final provider = context.watch<LaporanProvider>();
    final data = provider.pelanggaranData;

    final ruanganList = data?.ruanganList ?? [];
    if (_selectedRuanganId == null && ruanganList.isNotEmpty) {
      _selectedRuanganId = ruanganList.first.id;
    }

    final allSantri = data?.rekapMurid ?? [];
    final filteredSantri = allSantri.where((s) {
      if (_searchSantri.isEmpty) return true;
      return s.nama.toLowerCase().contains(_searchSantri) ||
          s.nism.toLowerCase().contains(_searchSantri);
    }).toList();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Laporan Buku Kasus',
        subtitleText: data?.namaRuangan.isNotEmpty == true
            ? '${data?.namaRuangan} (${data?.levelNama})'
            : 'Buku Kasus Kelas',
      ),
      body: RefreshIndicator(
        onRefresh: () async => _loadData(),
        color: primary,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
          children: [
            // 1. FILTER BAR (KATEGORI & RUANGAN)
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  ...['Semua', 'Ringan', 'Sedang', 'Berat'].map((k) {
                    final isSelected = _selectedKategori == k;
                    return Padding(
                      padding: const EdgeInsets.only(right: 6),
                      child: ChoiceChip(
                        label: Text(k, style: const TextStyle(fontSize: 11)),
                        selected: isSelected,
                        onSelected: (selected) {
                          if (selected && _selectedKategori != k) {
                            setState(() => _selectedKategori = k);
                            _loadData();
                          }
                        },
                      ),
                    );
                  }),
                  if (ruanganList.length > 1) ...[
                    const SizedBox(width: 4),
                    PopupMenuButton<int>(
                      initialValue: _selectedRuanganId,
                      tooltip: 'Pilih Ruangan',
                      onSelected: (val) {
                        setState(() => _selectedRuanganId = val);
                        _loadData();
                      },
                      itemBuilder: (ctx) => ruanganList
                          .map(
                            (r) => PopupMenuItem<int>(
                              value: r.id,
                              child: Text(
                                r.namaRuangan,
                                style: const TextStyle(fontSize: 12),
                              ),
                            ),
                          )
                          .toList(),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: primary.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: primary.withValues(alpha: 0.35),
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.meeting_room_rounded,
                              size: 14,
                              color: primary,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              ruanganList
                                  .firstWhere(
                                    (r) => r.id == _selectedRuanganId,
                                    orElse: () => ruanganList.first,
                                  )
                                  .namaRuangan,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: primary,
                              ),
                            ),
                            const Icon(Icons.arrow_drop_down_rounded, size: 16),
                          ],
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 12),

            // 2. RINGKASAN STATISTIK PELANGGARAN KELAS
            if (provider.isLoadingPelanggaran)
              const ShimmerLoadingList(count: 1)
            else
              GlassCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Akumulasi Buku Kasus Kelas',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              '${data?.totalMurid ?? 0} Santri Kelas Binaan',
                              style: TextStyle(
                                fontSize: 10.5,
                                color: isDark
                                    ? const Color(0xFF94A3B8)
                                    : const Color(0xFF64748B),
                              ),
                            ),
                          ],
                        ),
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 9,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: AppColors.roseDanger.withValues(
                                  alpha: 0.15,
                                ),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                  color: AppColors.roseDanger.withValues(
                                    alpha: 0.35,
                                  ),
                                ),
                              ),
                              child: Text(
                                '+${(data?.totalPoin ?? 0).toStringAsFixed((data?.totalPoin ?? 0).truncateToDouble() == (data?.totalPoin ?? 0) ? 0 : 1)} Poin',
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w900,
                                  color: AppColors.roseDanger,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // 4 Kotak Statistik Kasus (Total Kasus, Ringan, Sedang, Berat)
                    Row(
                      children: [
                        _buildStatBox(
                          label: 'Total Kasus',
                          count: data?.totalKasus ?? 0,
                          color: primary,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 8),
                        _buildStatBox(
                          label: 'Ringan',
                          count: data?.kasusRingan ?? 0,
                          color: AppColors.amberAccent,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 8),
                        _buildStatBox(
                          label: 'Sedang',
                          count: data?.kasusSedang ?? 0,
                          color: Colors.orange,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 8),
                        _buildStatBox(
                          label: 'Berat',
                          count: data?.kasusBerat ?? 0,
                          color: AppColors.roseDanger,
                          isDark: isDark,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 14),

            // 3. SEGMENTED TAB BAR (Rekap Kedisiplinan | Log Kasus)
            SegmentedTabBar(
              selectedIndex: _tabController.index,
              margin: EdgeInsets.zero,
              items: const [
                SegmentedTabItem(
                  label: 'Leaderboard Santri',
                  activeIcon: Icons.leaderboard_rounded,
                  inactiveIcon: Icons.leaderboard_outlined,
                ),
                SegmentedTabItem(
                  label: 'Riwayat Log Kasus',
                  activeIcon: Icons.history_edu_rounded,
                  inactiveIcon: Icons.history_edu_outlined,
                ),
              ],
              onTabChanged: (idx) {
                HapticHelper.selection();
                _tabController.animateTo(idx);
                setState(() {});
              },
            ),
            const SizedBox(height: 12),

            // 4. ISI TAB KONTEN
            if (_tabController.index == 0) ...[
              // === TAB 0: REKAP KEDISIPLINAN PER SANTRI ===
              TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Cari nama santri atau NISM...',
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

              if (provider.isLoadingPelanggaran)
                const ShimmerLoadingList(count: 3)
              else if (filteredSantri.isEmpty)
                const GlassCard(
                  padding: EdgeInsets.all(28),
                  child: Center(
                    child: Text('Tidak ada catatan pelanggaran santri.'),
                  ),
                )
              else
                ...filteredSantri.map((santri) {
                  Color statusColor = AppColors.hadirTextLight;
                  if (santri.statusKedisiplinan == 'Perhatian') {
                    statusColor = AppColors.amberAccent;
                  } else if (santri.statusKedisiplinan == 'Peringatan') {
                    statusColor = Colors.orange;
                  } else if (santri.statusKedisiplinan == 'Kritis') {
                    statusColor = AppColors.roseDanger;
                  }

                  return GlassCard(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(14),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 20,
                          backgroundColor:
                              (santri.totalPoin > 0
                                      ? AppColors.roseDanger
                                      : primary)
                                  .withValues(alpha: isDark ? 0.2 : 0.12),
                          child: Text(
                            santri.nama.isNotEmpty
                                ? santri.nama[0].toUpperCase()
                                : 'M',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: santri.totalPoin > 0
                                  ? AppColors.roseDanger
                                  : primary,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                santri.nama,
                                style: const TextStyle(
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.bold,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'NISM: ${santri.nism} • ${santri.totalKasus} Kasus',
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
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              santri.totalPoin > 0
                                  ? '+${santri.totalPoin.toStringAsFixed(santri.totalPoin.truncateToDouble() == santri.totalPoin ? 0 : 1)} Poin'
                                  : '0 Poin',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w900,
                                color: santri.totalPoin > 0
                                    ? AppColors.roseDanger
                                    : AppColors.hadirTextLight,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: statusColor.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                santri.statusKedisiplinan,
                                style: TextStyle(
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.bold,
                                  color: statusColor,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  );
                }),
            ] else ...[
              // === TAB 1: LOG RIWAYAT KASUS ===
              if (provider.isLoadingPelanggaran)
                const ShimmerLoadingList(count: 3)
              else if ((data?.riwayatLog ?? []).isEmpty)
                const GlassCard(
                  padding: EdgeInsets.all(28),
                  child: Center(
                    child: Text('Belum ada log catatan pelanggaran.'),
                  ),
                )
              else
                ...(data?.riwayatLog ?? []).map((log) {
                  Color katColor = AppColors.amberAccent;
                  if (log.kategori == 'Sedang') katColor = Colors.orange;
                  if (log.kategori == 'Berat') katColor = AppColors.roseDanger;

                  return GlassCard(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(14),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Text(
                                log.namaMurid,
                                style: const TextStyle(
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 7,
                                vertical: 2.5,
                              ),
                              decoration: BoxDecoration(
                                color: katColor.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(
                                  color: katColor.withValues(alpha: 0.35),
                                ),
                              ),
                              child: Text(
                                '+${log.poin} Poin (${log.kategori})',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: katColor,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          log.namaPelanggaran,
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        if (log.keterangan.isNotEmpty &&
                            log.keterangan != '-') ...[
                          const SizedBox(height: 4),
                          Text(
                            'Ket: ${log.keterangan}',
                            style: TextStyle(
                              fontSize: 11,
                              fontStyle: FontStyle.italic,
                              color: isDark
                                  ? const Color(0xFF94A3B8)
                                  : const Color(0xFF64748B),
                            ),
                          ),
                        ],
                        const SizedBox(height: 6),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              log.hariTanggal ?? log.tanggal,
                              style: TextStyle(
                                fontSize: 10.5,
                                color: isDark
                                    ? const Color(0xFF64748B)
                                    : const Color(0xFF94A3B8),
                              ),
                            ),
                            Text(
                              'Pencatat: ${log.pencatat}',
                              style: TextStyle(
                                fontSize: 10,
                                color: isDark
                                    ? const Color(0xFF64748B)
                                    : const Color(0xFF94A3B8),
                              ),
                            ),
                          ],
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
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
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
                fontSize: 16,
                fontWeight: FontWeight.w900,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
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
}
