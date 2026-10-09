import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/laporan_model.dart';
import '../../../providers/laporan_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';
import '../../widgets/app_avatar.dart';

class LaporanUjianScreen extends StatefulWidget {
  final int? initialRuanganId;
  final int? initialUjianId;

  const LaporanUjianScreen({
    super.key,
    this.initialRuanganId,
    this.initialUjianId,
  });

  @override
  State<LaporanUjianScreen> createState() => _LaporanUjianScreenState();
}

class _LaporanUjianScreenState extends State<LaporanUjianScreen> {
  int? _selectedRuanganId;
  int? _selectedUjianId;
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _selectedRuanganId = widget.initialRuanganId;
    _selectedUjianId = widget.initialUjianId;

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
    _searchController.dispose();
    super.dispose();
  }

  void _loadData() {
    context.read<LaporanProvider>().fetchLaporanUjian(
      ruanganId: _selectedRuanganId,
      ujianId: _selectedUjianId,
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;
    final bottomInset = MediaQuery.of(context).padding.bottom;
    final provider = context.watch<LaporanProvider>();
    final data = provider.laporanUjianData;
    final isLoading = provider.isLoadingLaporanUjian;
    final error = provider.errorLaporanUjian;

    // Sync selected ruangan & ujian id
    if (_selectedRuanganId == null && data?.ruanganId != null) {
      _selectedRuanganId = data!.ruanganId;
    }
    if (data != null && data.daftarUjian.isNotEmpty) {
      final exists = data.daftarUjian.any((u) => u.id == _selectedUjianId);
      if (!exists) {
        _selectedUjianId = data.ujian?.id ?? data.daftarUjian.first.id;
      }
    }

    final allSantri = data?.rekapMurid ?? [];

    // Filter santri hanya berdasarkan search query
    final filteredSantri = allSantri.where((s) {
      if (_searchQuery.isNotEmpty) {
        return s.nama.toLowerCase().contains(_searchQuery) ||
            s.nism.toLowerCase().contains(_searchQuery) ||
            s.wali.toLowerCase().contains(_searchQuery);
      }
      return true;
    }).toList();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Laporan Ujian',
        subtitleText: data != null
            ? '${data.namaRuangan} (${data.levelNama}) • ${data.ujian?.namaUjian ?? "Ujian"}'
            : 'Rekapitulasi Nilai & Leger Kelas',
      ),
      body: RefreshIndicator(
        onRefresh: () async => _loadData(),
        color: primary,
        child: ListView(
          padding: EdgeInsets.fromLTRB(16, 12, 16, 40 + bottomInset),
          children: [
            // 1. FILTER AGENDA UJIAN & RUANGAN
            if (data != null && data.daftarUjian.isNotEmpty) ...[
              _buildUjianFilterCard(context, isDark, primary, data),
              const SizedBox(height: 14),
            ],

            // 2. SEARCH BAR SANTRI
            _buildSearchBar(context, isDark, primary),
            const SizedBox(height: 14),

            // 3. STATISTIK KELAS & SUMMARY NILAI
            if (data != null) ...[
              _buildStatistikCard(context, isDark, primary, data),
              const SizedBox(height: 14),
            ],

            // 4. TOP 3 PODIUM JUARA KELAS (Jika tidak sedang search)
            if (allSantri.length >= 3 && _searchQuery.isEmpty) ...[
              _buildTop3Podium(
                context,
                isDark,
                primary,
                allSantri.take(3).toList(),
              ),
              const SizedBox(height: 16),
            ],

            // 5. DAFTAR SANTRI & LEGER RANKING
            if (isLoading && data == null)
              const ShimmerLoadingList(count: 5, height: 110)
            else if (error != null && data == null)
              _buildErrorState(context, isDark, primary, error)
            else if (allSantri.isEmpty)
              _buildEmptyState(context, isDark, primary)
            else if (filteredSantri.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(32),
                  child: Text(
                    'Tidak ditemukan santri dengan kata kunci "$_searchQuery"',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 13,
                      color: isDark
                          ? const Color(0xFF94A3B8)
                          : const Color(0xFF64748B),
                    ),
                  ),
                ),
              )
            else ...[
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Peringkat Murid (${filteredSantri.length} Murid)',
                    style: const TextStyle(
                      fontSize: 13.5,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  if (data != null)
                    Text(
                      '${data.totalMapel} Mapel Diujikan',
                      style: TextStyle(
                        fontSize: 11,
                        color: isDark
                            ? const Color(0xFF94A3B8)
                            : const Color(0xFF64748B),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 10),

              ...filteredSantri.map(
                (santri) => _buildSantriCard(
                  context,
                  isDark,
                  primary,
                  santri,
                  data?.mapelHeader ?? [],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  // =========================================================================
  // 1. FILTER AGENDA UJIAN & RUANGAN
  // =========================================================================
  Widget _buildUjianFilterCard(
    BuildContext context,
    bool isDark,
    Color primary,
    LaporanUjianData data,
  ) {
    final ruanganList = data.ruanganList;
    final hasMultipleRuangan = ruanganList.length > 1;

    return GlassCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Icon(Icons.tune_rounded, size: 18, color: primary),
                  const SizedBox(width: 8),
                  const Text(
                    'Filter Laporan Ujian',
                    style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              if (data.isKelasAkhir)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: primary.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    'Kelas Akhir',
                    style: TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.bold,
                      color: primary,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),

          // Pilihan Ruangan jika ustadz mengampu / memiliki akses ke lebih dari 1 ruangan
          if (hasMultipleRuangan) ...[
            DropdownButtonFormField<int>(
              key: ValueKey('ruangan_$_selectedRuanganId'),
              initialValue: ruanganList.any((r) => r.id == _selectedRuanganId)
                  ? _selectedRuanganId
                  : ruanganList.first.id,
              isExpanded: true,
              decoration: InputDecoration(
                labelText: 'Ruangan / Kelas',
                prefixIcon: const Icon(Icons.meeting_room_rounded, size: 18),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 11,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
              items: ruanganList.map((r) {
                return DropdownMenuItem<int>(
                  value: r.id,
                  child: Text(
                    '${r.namaRuangan} (${r.levelNama})',
                    style: const TextStyle(fontSize: 13),
                    overflow: TextOverflow.ellipsis,
                  ),
                );
              }).toList(),
              onChanged: (val) {
                if (val != null && val != _selectedRuanganId) {
                  HapticHelper.light();
                  setState(() {
                    _selectedRuanganId = val;
                    _selectedUjianId = null; // Reset agar otomatis memilih ujian valid untuk ruangan baru
                  });
                  _loadData();
                }
              },
            ),
            const SizedBox(height: 10),
          ],

          // Pilihan Agenda Ujian
          DropdownButtonFormField<int>(
            key: ValueKey('ujian_${_selectedUjianId}_${data.ruanganId}'),
            initialValue: data.daftarUjian.any((uj) => uj.id == _selectedUjianId)
                ? _selectedUjianId
                : (data.daftarUjian.isNotEmpty ? data.daftarUjian.first.id : null),
            isExpanded: true,
            decoration: InputDecoration(
              labelText: 'Agenda Ujian',
              prefixIcon: const Icon(Icons.auto_stories_rounded, size: 18),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 14,
                vertical: 11,
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            items: data.daftarUjian.map((uj) {
              return DropdownMenuItem<int>(
                value: uj.id,
                child: Text(
                  '${uj.namaUjian} (${uj.semester})',
                  style: const TextStyle(fontSize: 13),
                  overflow: TextOverflow.ellipsis,
                ),
              );
            }).toList(),
            onChanged: (val) {
              if (val != null && val != _selectedUjianId) {
                HapticHelper.light();
                setState(() {
                  _selectedUjianId = val;
                });
                _loadData();
              }
            },
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // 2. SEARCH BAR SANTRI
  // =========================================================================
  Widget _buildSearchBar(BuildContext context, bool isDark, Color primary) {
    return TextField(
      controller: _searchController,
      decoration: InputDecoration(
        hintText: 'Cari nama santri, NISM, atau wali...',
        hintStyle: TextStyle(
          fontSize: 12.5,
          color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
        ),
        prefixIcon: Icon(
          Icons.search_rounded,
          size: 20,
          color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
        ),
        suffixIcon: _searchQuery.isNotEmpty
            ? IconButton(
                icon: const Icon(Icons.clear_rounded, size: 18),
                onPressed: () {
                  _searchController.clear();
                },
              )
            : null,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 14,
          vertical: 10,
        ),
        filled: true,
        fillColor: isDark ? const Color(0xFF1E293B) : const Color(0xFFF8FAFC),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(
            color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
          ),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(
            color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
          ),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: primary, width: 1.5),
        ),
      ),
    );
  }

  // =========================================================================
  // 3. STATISTIK KELAS & SUMMARY NILAI
  // =========================================================================
  Widget _buildStatistikCard(
    BuildContext context,
    bool isDark,
    Color primary,
    LaporanUjianData data,
  ) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Icon(Icons.analytics_rounded, size: 18, color: primary),
                  const SizedBox(width: 8),
                  const Text(
                    'Ringkasan Nilai & Leger Kelas',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color:
                      (data.persentaseTuntas >= 75
                              ? (isDark
                                    ? AppColors.hadirBgDark
                                    : AppColors.hadirBgLight)
                              : (isDark
                                    ? AppColors.alphaBgDark
                                    : AppColors.alphaBgLight))
                          .withValues(alpha: 0.7),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  '${data.persentaseTuntas.toStringAsFixed(1)}% Tuntas',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: data.persentaseTuntas >= 75
                        ? (isDark
                              ? AppColors.hadirTextDark
                              : AppColors.hadirTextLight)
                        : (isDark
                              ? AppColors.alphaTextDark
                              : AppColors.alphaTextLight),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // 4 Grid Statistik
          Row(
            children: [
              _buildStatItem(
                label: 'Total Murid',
                value: '${data.totalMurid}',
                icon: Icons.people_alt_outlined,
                color: AppColors.skyBlueAccent,
                isDark: isDark,
              ),
              _buildStatItem(
                label: 'Rata-Rata',
                value: data.rataRataKelas.toStringAsFixed(1),
                icon: Icons.auto_graph_rounded,
                color: primary,
                isDark: isDark,
              ),
              _buildStatItem(
                label: 'Tertinggi',
                value: data.nilaiTertinggi.toStringAsFixed(1),
                icon: Icons.arrow_upward_rounded,
                color: isDark
                    ? AppColors.hadirTextDark
                    : AppColors.hadirTextLight,
                isDark: isDark,
              ),
              _buildStatItem(
                label: 'Terendah',
                value: data.nilaiTerendah.toStringAsFixed(1),
                icon: Icons.arrow_downward_rounded,
                color: isDark
                    ? AppColors.alphaTextDark
                    : AppColors.alphaTextLight,
                isDark: isDark,
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Progress bar ketuntasan ujian
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: data.totalMurid > 0
                  ? data.jumlahTuntas / data.totalMurid
                  : 0.0,
              minHeight: 6,
              backgroundColor: isDark
                  ? const Color(0xFF1E293B)
                  : const Color(0xFFE2E8F0),
              valueColor: AlwaysStoppedAnimation<Color>(
                isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight,
              ),
            ),
          ),
          const SizedBox(height: 6),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Ketuntasan: ${data.jumlahTuntas} Tuntas • ${data.jumlahBelumTuntas} Belum Tuntas',
                style: TextStyle(
                  fontSize: 10.5,
                  color: isDark
                      ? const Color(0xFF94A3B8)
                      : const Color(0xFF64748B),
                ),
              ),
              Text(
                'KKM: \u2265 55.0',
                style: TextStyle(
                  fontSize: 10.5,
                  fontWeight: FontWeight.bold,
                  color: isDark
                      ? const Color(0xFF94A3B8)
                      : const Color(0xFF64748B),
                ),
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
        margin: const EdgeInsets.symmetric(horizontal: 2.5),
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
        decoration: BoxDecoration(
          color: color.withValues(alpha: isDark ? 0.15 : 0.08),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.25)),
        ),
        child: Column(
          children: [
            Icon(icon, size: 15, color: color),
            const SizedBox(height: 4),
            Text(
              value,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.bold,
                color: isDark ? Colors.white : Colors.black87,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 9.5,
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
    );
  }

  // =========================================================================
  // 4. TOP 3 PODIUM JUARA KELAS
  // =========================================================================
  Widget _buildTop3Podium(
    BuildContext context,
    bool isDark,
    Color primary,
    List<MuridRekapUjianItem> top3,
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
                'Top 3 Peringkat Bintang Pelajar',
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
    required MuridRekapUjianItem item,
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
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
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
            'Rata: ${item.rataRata.toStringAsFixed(1)}',
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w600,
              color: color,
            ),
          ),
          const SizedBox(height: 6),
          Container(
            height: height,
            margin: const EdgeInsets.symmetric(horizontal: 5),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  color.withValues(alpha: 0.35),
                  color.withValues(alpha: 0.1),
                ],
              ),
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(10),
              ),
              border: Border.all(color: color.withValues(alpha: 0.45)),
            ),
            child: Center(
              child: Text(
                '#$rank',
                style: TextStyle(
                  fontSize: 17,
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

  // =========================================================================
  // 5. KARTU REKAP NILAI MURID
  // =========================================================================
  Widget _buildSantriCard(
    BuildContext context,
    bool isDark,
    Color primary,
    MuridRekapUjianItem santri,
    List<MapelHeaderItem> mapelHeader,
  ) {
    final isTop3 = santri.ranking <= 3;
    final isTuntas = santri.statusTuntas == 'Tuntas';

    final rankBadgeColor = santri.ranking == 1
        ? const Color(0xFFF59E0B)
        : santri.ranking == 2
        ? const Color(0xFF94A3B8)
        : santri.ranking == 3
        ? const Color(0xFFD97706)
        : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B));

    final tuntasBg = isTuntas
        ? (isDark ? AppColors.hadirBgDark : AppColors.hadirBgLight)
        : (isDark ? AppColors.alphaBgDark : AppColors.alphaBgLight);
    final tuntasText = isTuntas
        ? (isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight)
        : (isDark ? AppColors.alphaTextDark : AppColors.alphaTextLight);

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      padding: EdgeInsets.zero,
      child: InkWell(
        onTap: () {
          HapticHelper.light();
          _showDetailModal(context, isDark, primary, santri);
        },
        borderRadius: BorderRadius.circular(20),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Row 1: Avatar + Rank Badge, Identitas Murid, Nilai Rata-rata & Predikat
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Avatar dengan Ranking Badge di Sudut Kanan Bawah
                  Stack(
                    clipBehavior: Clip.none,
                    children: [
                      AppAvatar(
                        radius: 23,
                        name: santri.nama,
                        imageUrl: santri.foto,
                        shape: BoxShape.circle,
                        cacheDimension: 110,
                      ),
                      Positioned(
                        right: -4,
                        bottom: -4,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 5,
                            vertical: 1.5,
                          ),
                          decoration: BoxDecoration(
                            color: isTop3
                                ? rankBadgeColor
                                : (isDark
                                      ? const Color(0xFF1E293B)
                                      : const Color(0xFFE2E8F0)),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: isDark
                                  ? const Color(0xFF0F172A)
                                  : Colors.white,
                              width: 1.5,
                            ),
                          ),
                          child: Text(
                            '#${santri.ranking}',
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.bold,
                              color: isTop3
                                  ? Colors.white
                                  : (isDark
                                        ? const Color(0xFF94A3B8)
                                        : const Color(0xFF475569)),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(width: 14),

                  // Identitas Murid
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
                          'NISM: ${santri.nism} • ${santri.jenisKelamin == "L" ? "Murid Putra" : "Murid Putri"}',
                          style: TextStyle(
                            fontSize: 10.5,
                            color: isDark
                                ? const Color(0xFF94A3B8)
                                : const Color(0xFF64748B),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Wali: ${santri.wali}',
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

                  // Rata-rata & Predikat & Ketuntasan Pill
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            santri.rataRata.toStringAsFixed(1),
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: primary,
                            ),
                          ),
                          const SizedBox(width: 5),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: primary.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              santri.predikat,
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: primary,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 7,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: tuntasBg,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          santri.statusTuntas,
                          style: TextStyle(
                            fontSize: 9.5,
                            fontWeight: FontWeight.bold,
                            color: tuntasText,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 10),
              const Divider(height: 1),
              const SizedBox(height: 10),

              // Row 2: Rincian Nilai Mata Pelajaran (Wrap Tags)
              if (santri.mapelNilai.isNotEmpty) ...[
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: santri.mapelNilai.map((mpl) {
                    final isFilled = mpl.nilai != null;
                    final isPass = (mpl.nilai ?? 0) >= 60;

                    return Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 3.5,
                      ),
                      decoration: BoxDecoration(
                        color: isFilled
                            ? (isPass
                                  ? primary.withValues(alpha: 0.08)
                                  : (isDark
                                        ? AppColors.alphaBgDark
                                        : AppColors.alphaBgLight))
                            : (isDark
                                  ? const Color(0xFF1E293B)
                                  : const Color(0xFFF1F5F9)),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(
                          color: isFilled
                              ? (isPass
                                    ? primary.withValues(alpha: 0.25)
                                    : (isDark
                                              ? AppColors.alphaTextDark
                                              : AppColors.alphaTextLight)
                                          .withValues(alpha: 0.3))
                              : (isDark
                                    ? AppColors.outlineDark
                                    : AppColors.outlineLight),
                        ),
                      ),
                      child: Text(
                        '${mpl.namaMapel}: ${isFilled ? mpl.nilai!.toStringAsFixed(mpl.nilai! % 1 == 0 ? 0 : 1) : "-"}',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: isFilled
                              ? FontWeight.w600
                              : FontWeight.normal,
                          color: isFilled
                              ? (isPass
                                    ? (isDark ? Colors.white : Colors.black87)
                                    : (isDark
                                          ? AppColors.alphaTextDark
                                          : AppColors.alphaTextLight))
                              : (isDark
                                    ? const Color(0xFF94A3B8)
                                    : const Color(0xFF64748B)),
                        ),
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 8),
              ],

              // Row 3: Total Nilai & Progres Terisi & Hint Detail
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Terisi: ${santri.jumlahMapelDiikuti}/${santri.totalMapel} Mapel',
                    style: TextStyle(
                      fontSize: 10.5,
                      color: isDark
                          ? const Color(0xFF94A3B8)
                          : const Color(0xFF64748B),
                    ),
                  ),
                  Row(
                    children: [
                      Text(
                        'Total: ${santri.totalNilai.toStringAsFixed(santri.totalNilai % 1 == 0 ? 0 : 1)}',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: isDark
                              ? const Color(0xFFCBD5E1)
                              : const Color(0xFF475569),
                        ),
                      ),
                      const SizedBox(width: 4),
                      Icon(
                        Icons.chevron_right_rounded,
                        size: 16,
                        color: isDark
                            ? const Color(0xFF64748B)
                            : const Color(0xFF94A3B8),
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  // =========================================================================
  // 6. MODAL BOTTOM SHEET DETAIL NILAI MURID (DENGAN SAFE AREA NAVIGATION BAR)
  // =========================================================================
  void _showDetailModal(
    BuildContext context,
    bool isDark,
    Color primary,
    MuridRekapUjianItem santri,
  ) {
    final bottomInset = MediaQuery.of(context).padding.bottom;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalCtx) {
        return Container(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(modalCtx).size.height * 0.85,
          ),
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF0F172A) : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            border: Border.all(
              color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
            ),
          ),
          child: SafeArea(
            top: false,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Handle Drag
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    margin: const EdgeInsets.only(top: 12, bottom: 8),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF334155)
                          : const Color(0xFFCBD5E1),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),

                // Header
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 8, 20, 14),
                  child: Row(
                    children: [
                      AppAvatar(
                        radius: 20,
                        name: santri.nama,
                        imageUrl: santri.foto,
                        shape: BoxShape.circle,
                        cacheDimension: 100,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              santri.nama,
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              'NISM: ${santri.nism} • Peringkat #${santri.ranking}',
                              style: TextStyle(
                                fontSize: 11,
                                color: isDark
                                    ? const Color(0xFF94A3B8)
                                    : const Color(0xFF64748B),
                              ),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded, size: 20),
                        onPressed: () => Navigator.pop(modalCtx),
                      ),
                    ],
                  ),
                ),
                const Divider(height: 1),

                // Content List
                Expanded(
                  child: ListView(
                    padding: EdgeInsets.fromLTRB(20, 16, 20, 20 + bottomInset),
                    children: [
                      // Summary Banner
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: primary.withValues(
                            alpha: isDark ? 0.18 : 0.08,
                          ),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: primary.withValues(alpha: 0.25),
                          ),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _buildModalSummaryItem(
                              'Total Nilai',
                              santri.totalNilai.toStringAsFixed(1),
                              isDark,
                            ),
                            _buildModalSummaryItem(
                              'Rata-Rata',
                              santri.rataRata.toStringAsFixed(1),
                              isDark,
                              valueColor: primary,
                            ),
                            _buildModalSummaryItem(
                              'Predikat',
                              santri.predikat,
                              isDark,
                            ),
                            _buildModalSummaryItem(
                              'Status',
                              santri.statusTuntas,
                              isDark,
                              valueColor: santri.statusTuntas == 'Tuntas'
                                  ? (isDark
                                        ? AppColors.hadirTextDark
                                        : AppColors.hadirTextLight)
                                  : (isDark
                                        ? AppColors.alphaTextDark
                                        : AppColors.alphaTextLight),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 18),

                      // Rincian Mata Pelajaran
                      const Text(
                        'Rincian Nilai Mata Pelajaran',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 10),

                      if (santri.mapelNilai.isEmpty)
                        Padding(
                          padding: const EdgeInsets.all(20),
                          child: Center(
                            child: Text(
                              'Belum ada nilai mata pelajaran.',
                              style: TextStyle(
                                color: isDark
                                    ? const Color(0xFF94A3B8)
                                    : const Color(0xFF64748B),
                              ),
                            ),
                          ),
                        )
                      else
                        ...santri.mapelNilai.map((mpl) {
                          final isFilled = mpl.nilai != null;
                          final isPass = (mpl.nilai ?? 0) >= 60;

                          return Container(
                            margin: const EdgeInsets.only(bottom: 8),
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 10,
                            ),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? const Color(0xFF1E293B)
                                  : const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                color: isDark
                                    ? AppColors.outlineDark
                                    : AppColors.outlineLight,
                              ),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  mpl.namaMapel,
                                  style: const TextStyle(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                Row(
                                  children: [
                                    Text(
                                      isFilled
                                          ? mpl.nilai!.toStringAsFixed(1)
                                          : 'Belum Dinilai',
                                      style: TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.bold,
                                        color: isFilled
                                            ? (isPass
                                                  ? (isDark
                                                        ? Colors.white
                                                        : Colors.black87)
                                                  : (isDark
                                                        ? AppColors
                                                              .alphaTextDark
                                                        : AppColors
                                                              .alphaTextLight))
                                            : (isDark
                                                  ? const Color(0xFF94A3B8)
                                                  : const Color(0xFF64748B)),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 6,
                                        vertical: 2,
                                      ),
                                      decoration: BoxDecoration(
                                        color: isFilled
                                            ? (isPass
                                                  ? (isDark
                                                        ? AppColors.hadirBgDark
                                                        : AppColors
                                                              .hadirBgLight)
                                                  : (isDark
                                                        ? AppColors.alphaBgDark
                                                        : AppColors
                                                              .alphaBgLight))
                                            : (isDark
                                                  ? const Color(0xFF334155)
                                                  : const Color(0xFFE2E8F0)),
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: Text(
                                        isFilled
                                            ? (isPass ? 'Tuntas' : 'Remidi')
                                            : '-',
                                        style: TextStyle(
                                          fontSize: 9,
                                          fontWeight: FontWeight.bold,
                                          color: isFilled
                                              ? (isPass
                                                    ? (isDark
                                                          ? AppColors
                                                                .hadirTextDark
                                                          : AppColors
                                                                .hadirTextLight)
                                                    : (isDark
                                                          ? AppColors
                                                                .alphaTextDark
                                                          : AppColors
                                                                .alphaTextLight))
                                              : (isDark
                                                    ? const Color(0xFF94A3B8)
                                                    : const Color(0xFF64748B)),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          );
                        }),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildModalSummaryItem(
    String label,
    String value,
    bool isDark, {
    Color? valueColor,
  }) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: valueColor ?? (isDark ? Colors.white : Colors.black87),
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: TextStyle(
            fontSize: 10,
            color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // 7. EMPTY & ERROR STATES
  // =========================================================================
  Widget _buildEmptyState(BuildContext context, bool isDark, Color primary) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 16),
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            Icon(
              Icons.assessment_outlined,
              size: 48,
              color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
            ),
            const SizedBox(height: 14),
            const Text(
              'Belum Ada Nilai Ujian',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(
              'Data leger dan rekapitulasi nilai ujian akan otomatis terhitung saat nilai mata pelajaran diinput.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 12,
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

  Widget _buildErrorState(
    BuildContext context,
    bool isDark,
    Color primary,
    String error,
  ) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 16),
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            Icon(
              Icons.error_outline_rounded,
              size: 48,
              color: isDark
                  ? AppColors.alphaTextDark
                  : AppColors.alphaTextLight,
            ),
            const SizedBox(height: 14),
            Text(
              error,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _loadData,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }
}
