import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../data/models/nilai_ujian_model.dart';
import '../../../providers/nilai_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class LegerRuanganScreen extends StatefulWidget {
  final int ruanganId;
  final int? ujianId;
  final String ruanganName;
  final String? ujianName;

  const LegerRuanganScreen({
    super.key,
    required this.ruanganId,
    this.ujianId,
    required this.ruanganName,
    this.ujianName,
  });

  @override
  State<LegerRuanganScreen> createState() => _LegerRuanganScreenState();
}

class _LegerRuanganScreenState extends State<LegerRuanganScreen> {
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadData() {
    final p = context.read<NilaiProvider>();
    final uId =
        widget.ujianId ??
        p.selectedUjianId ??
        p.daftarUjian.firstOrNull?.id ??
        0;
    return p.fetchLeger(ruanganId: widget.ruanganId, ujianId: uId);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final nilai = context.watch<NilaiProvider>();
    final legerData = nilai.legerData;
    final allList = nilai.legerList;

    final filteredList = allList.where((item) {
      if (_searchQuery.isEmpty) return true;
      final q = _searchQuery.toLowerCase();
      return item.nama.toLowerCase().contains(q) || item.nism.contains(q);
    }).toList();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Leger Nilai ${widget.ruanganName}',
        subtitleText:
            '${widget.ujianName ?? nilai.selectedUjian?.namaUjian ?? "Ujian"} • Ranking Otomatis',
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
          children: [
            // ===================================================================
            // 1. STATISTIK KELAS & SUMMARY
            // ===================================================================
            if (legerData?.statistik != null) ...[
              _buildStatistikCard(context, isDark, legerData!.statistik!),
              const SizedBox(height: 14),
            ],

            // ===================================================================
            // 2. PODIUM TOP 3 JUARA KELAS
            // ===================================================================
            if (allList.length >= 3 && _searchQuery.isEmpty) ...[
              _buildTop3Podium(context, isDark, allList.take(3).toList()),
              const SizedBox(height: 16),
            ],

            // ===================================================================
            // 3. SEARCH BAR SANTRI
            // ===================================================================
            if (allList.isNotEmpty) ...[
              TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Cari santri berdasarkan nama atau NISM...',
                  hintStyle: const TextStyle(fontSize: 12),
                  prefixIcon: const Icon(Icons.search_rounded, size: 20),
                  suffixIcon: _searchQuery.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear_rounded, size: 18),
                          onPressed: () {
                            setState(() {
                              _searchController.clear();
                              _searchQuery = '';
                            });
                          },
                        )
                      : null,
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 10,
                  ),
                  filled: true,
                  fillColor: (isDark ? Colors.white : Colors.black).withValues(
                    alpha: 0.04,
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(
                      color: isDark
                          ? AppColors.outlineDark
                          : AppColors.outlineLight,
                    ),
                  ),
                ),
                onChanged: (val) {
                  setState(() {
                    _searchQuery = val.trim();
                  });
                },
              ),
              const SizedBox(height: 14),
            ],

            // ===================================================================
            // 4. DAFTAR LEGER SANTRI (RANKING LIST)
            // ===================================================================
            if (nilai.isLoading && allList.isEmpty)
              const ShimmerLoadingList(count: 5, height: 110)
            else if (allList.isEmpty)
              _buildEmptyState(context, isDark)
            else if (filteredList.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(32),
                  child: Text(
                    'Tidak ditemukan santri dengan kata kunci "$_searchQuery"',
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: Colors.grey),
                  ),
                ),
              )
            else ...[
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Peringkat Murid (${filteredList.length} Murid)',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  if (legerData?.kolomMapel != null)
                    Text(
                      '${legerData!.kolomMapel.length} Mata Pelajaran',
                      style: TextStyle(
                        fontSize: 11,
                        color: isDark
                            ? const Color(0xFF8D9387)
                            : const Color(0xFF73796E),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 10),

              ...filteredList.map(
                (row) => _buildLegerCard(context, isDark, row),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatistikCard(
    BuildContext context,
    bool isDark,
    LegerStatistik stat,
  ) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.analytics_rounded,
                size: 18,
                color: AppColors.primaryLight,
              ),
              const SizedBox(width: 8),
              const Text(
                'Ringkasan Nilai Kelas',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              _buildStatItem(
                label: 'Murid',
                value: '${stat.totalSantri}',
                icon: Icons.people_alt_outlined,
                color: AppColors.skyBlueAccent,
                isDark: isDark,
              ),
              _buildStatItem(
                label: 'Rata-Rata',
                value: stat.rataRataKelas.toStringAsFixed(1),
                icon: Icons.auto_graph_rounded,
                color: AppColors.primaryLight,
                isDark: isDark,
              ),
              _buildStatItem(
                label: 'Tertinggi',
                value: stat.nilaiTertinggi.toStringAsFixed(1),
                icon: Icons.arrow_upward_rounded,
                color: AppColors.hadirTextLight,
                isDark: isDark,
              ),
              _buildStatItem(
                label: 'Terendah',
                value: stat.nilaiTerendah.toStringAsFixed(1),
                icon: Icons.arrow_downward_rounded,
                color: AppColors.roseDanger,
                isDark: isDark,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatItem({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
    required bool isDark,
  }) {
    return Expanded(
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 3),
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.25)),
        ),
        child: Column(
          children: [
            Icon(icon, size: 16, color: color),
            const SizedBox(height: 4),
            Text(
              value,
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.bold,
                color: isDark ? Colors.white : Colors.black87,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: const TextStyle(fontSize: 10, color: Colors.grey),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTop3Podium(
    BuildContext context,
    bool isDark,
    List<LegerRowItem> top3,
  ) {
    if (top3.length < 3) return const SizedBox.shrink();

    final r1 = top3[0];
    final r2 = top3[1];
    final r3 = top3[2];

    return GlassCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          const Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.emoji_events_rounded,
                color: AppColors.amberAccent,
                size: 18,
              ),
              SizedBox(width: 6),
              Text(
                'Top 3 Peringkat Kelas',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              // Rank 2 (Silver)
              _buildPodiumColumn(
                rank: 2,
                item: r2,
                color: const Color(0xFF94A3B8),
                badgeText: '🥈 2',
                height: 85,
                isDark: isDark,
              ),
              // Rank 1 (Gold)
              _buildPodiumColumn(
                rank: 1,
                item: r1,
                color: const Color(0xFFF59E0B),
                badgeText: '👑 1',
                height: 110,
                isDark: isDark,
              ),
              // Rank 3 (Bronze)
              _buildPodiumColumn(
                rank: 3,
                item: r3,
                color: const Color(0xFFD97706),
                badgeText: '🥉 3',
                height: 70,
                isDark: isDark,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPodiumColumn({
    required int rank,
    required LegerRowItem item,
    required Color color,
    required String badgeText,
    required double height,
    required bool isDark,
  }) {
    return Expanded(
      child: Column(
        children: [
          Text(
            badgeText,
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(
            item.nama,
            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.center,
          ),
          Text(
            'Nilai: ${item.total.toStringAsFixed(1)}',
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w600,
              color: color,
            ),
          ),
          const SizedBox(height: 6),
          Container(
            height: height,
            margin: const EdgeInsets.symmetric(horizontal: 6),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  color.withValues(alpha: 0.4),
                  color.withValues(alpha: 0.1),
                ],
              ),
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(10),
              ),
              border: Border.all(color: color.withValues(alpha: 0.5)),
            ),
            child: Center(
              child: Text(
                '#$rank',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: color,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLegerCard(BuildContext context, bool isDark, LegerRowItem row) {
    Color? rankBadgeBg;
    Color? rankBadgeText;
    String rankIcon = '#${row.ranking}';

    if (row.ranking == 1) {
      rankBadgeBg = const Color(0xFFFEF3C7);
      rankBadgeText = const Color(0xFFB45309);
      rankIcon = '🥇 1';
    } else if (row.ranking == 2) {
      rankBadgeBg = const Color(0xFFF3F4F6);
      rankBadgeText = const Color(0xFF4B5563);
      rankIcon = '🥈 2';
    } else if (row.ranking == 3) {
      rankBadgeBg = const Color(0xFFFFEDD5);
      rankBadgeText = const Color(0xFFC2410C);
      rankIcon = '🥉 3';
    }

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Row Header: Ranking, Nama, NISM & Rata-rata/Predikat
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color:
                          rankBadgeBg ??
                          (isDark
                              ? const Color(0xFF101710)
                              : const Color(0xFFF3F4F1)),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: rankBadgeText != null
                            ? rankBadgeText.withValues(alpha: 0.3)
                            : (isDark
                                  ? AppColors.outlineDark
                                  : AppColors.outlineLight),
                      ),
                    ),
                    child: Text(
                      rankIcon,
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                        color:
                            rankBadgeText ??
                            (isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        row.nama,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Text(
                        'NISM: ${row.nism} • ${row.jenisKelamin == 'L' ? 'Murid Putra' : 'Murid Putri'}',
                        style: TextStyle(
                          fontSize: 10,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF73796E),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Row(
                    children: [
                      Text(
                        row.rataRata.toStringAsFixed(1),
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: AppColors.primaryLight,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.primaryLight.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          row.predikat,
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: AppColors.primaryLight,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const Text(
                    'Rata-Rata',
                    style: TextStyle(fontSize: 10, color: Colors.grey),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 10),

          // Rincian Nilai per Mata Pelajaran
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: row.nilaiMapel.entries.map((entry) {
              final scoreVal = entry.value;
              final isFilled = scoreVal != null;

              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: isFilled
                      ? AppColors.primaryLight.withValues(alpha: 0.08)
                      : (isDark ? Colors.white : Colors.black).withValues(
                          alpha: 0.03,
                        ),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: isFilled
                        ? AppColors.primaryLight.withValues(alpha: 0.3)
                        : (isDark
                              ? AppColors.outlineDark
                              : AppColors.outlineLight),
                  ),
                ),
                child: Text(
                  '${entry.key}: ${isFilled ? scoreVal : "-"}',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: isFilled ? FontWeight.w600 : FontWeight.normal,
                    color: isFilled
                        ? (isDark ? Colors.white : Colors.black87)
                        : Colors.grey,
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 8),

          // Total Nilai & Progres Terisi
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Terisi: ${row.jumlahTerisi}/${row.totalMapel} Mapel',
                style: const TextStyle(fontSize: 10, color: Colors.grey),
              ),
              Text(
                'Total Nilai: ${row.total.toStringAsFixed(1)}',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context, bool isDark) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 16),
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            const Icon(
              Icons.pending_actions_rounded,
              size: 48,
              color: AppColors.amberAccent,
            ),
            const SizedBox(height: 14),
            const Text(
              'Belum Ada Nilai Diinput',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(
              'Data leger dan ranking kelas akan otomatis terkalkulasi setelah guru/pengawas menginput nilai mata pelajaran ujian.',
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
    );
  }
}
