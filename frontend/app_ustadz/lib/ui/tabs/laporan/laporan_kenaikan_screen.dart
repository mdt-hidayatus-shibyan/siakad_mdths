import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/laporan_model.dart';
import '../../../providers/laporan_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class LaporanKenaikanScreen extends StatefulWidget {
  final int? initialRuanganId;

  const LaporanKenaikanScreen({super.key, this.initialRuanganId});

  @override
  State<LaporanKenaikanScreen> createState() => _LaporanKenaikanScreenState();
}

class _LaporanKenaikanScreenState extends State<LaporanKenaikanScreen> {
  String _selectedStatusFilter = 'Semua';
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();

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
    context.read<LaporanProvider>().fetchKenaikanKelas(
      ruanganId: widget.initialRuanganId,
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;
    final provider = context.watch<LaporanProvider>();
    final data = provider.kenaikanKelasData;
    final isLoading = provider.isLoadingKenaikanKelas;
    final error = provider.errorKenaikanKelas;

    final allSantri = data?.dataKenaikan ?? [];
    final filteredSantri = allSantri.where((s) {
      // Filter status
      if (_selectedStatusFilter != 'Semua') {
        if (_selectedStatusFilter == 'Naik Kelas' &&
            s.keputusanFinal != 'Naik Kelas') {
          return false;
        }
        if (_selectedStatusFilter == 'Lulus' && s.keputusanFinal != 'Lulus') {
          return false;
        }
        if (_selectedStatusFilter == 'Tinggal Kelas' &&
            s.keputusanFinal != 'Tinggal Kelas') {
          return false;
        }
      }

      // Filter search
      if (_searchQuery.isNotEmpty) {
        return s.nama.toLowerCase().contains(_searchQuery) ||
            s.nism.toLowerCase().contains(_searchQuery) ||
            s.wali.toLowerCase().contains(_searchQuery);
      }
      return true;
    }).toList();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Kenaikan & Kelulusan',
        subtitleText: data != null
            ? '${data.namaRuangan} (${data.levelNama}) • ${data.tahunPelajaran}'
            : 'Rekapitulasi Kenaikan Kelas',
      ),
      body: RefreshIndicator(
        onRefresh: () async => _loadData(),
        color: primary,
        child: ListView(
          padding: EdgeInsets.fromLTRB(
            16,
            12,
            16,
            40 + MediaQuery.of(context).padding.bottom,
          ),
          children: [
            // 1. SEARCH BAR
            _buildSearchBar(isDark, primary),
            const SizedBox(height: 14),

            if (isLoading && data == null) ...[
              _buildLoadingSkeleton(),
            ] else if (error != null && data == null) ...[
              _buildErrorCard(error, isDark, primary),
            ] else if (data != null) ...[
              // 2. BANNER ALGORITMA & RASIO BOBOT
              _buildAlgorithmBanner(isDark, primary, data),
              const SizedBox(height: 14),

              // 3. RINGKASAN STATISTIK
              _buildSummaryStats(isDark, primary, data),
              const SizedBox(height: 14),

              // 4. STATUS TABS (Semua, Naik, Lulus, Tinggal)
              _buildStatusTabs(isDark, primary, data),
              const SizedBox(height: 12),

              // 5. HEADER LIST & JUMLAH
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Daftar Murid (${filteredSantri.length} Murid)',
                    style: TextStyle(
                      fontSize: 13.5,
                      fontWeight: FontWeight.bold,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                  if (data.isKelasAkhir)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: AppColors.amberAccent.withValues(
                          alpha: isDark ? 0.2 : 0.12,
                        ),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(
                          color: AppColors.amberAccent.withValues(
                            alpha: isDark ? 0.4 : 0.3,
                          ),
                        ),
                      ),
                      child: const Row(
                        children: [
                          Icon(
                            Icons.school_rounded,
                            size: 13,
                            color: AppColors.amberAccent,
                          ),
                          SizedBox(width: 4),
                          Text(
                            'Tingkat Akhir',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: AppColors.amberAccent,
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 10),

              // 6. LIST MURID
              if (filteredSantri.isEmpty)
                _buildEmptySantri(isDark, primary)
              else
                ...filteredSantri.asMap().entries.map((entry) {
                  final index = entry.key;
                  final murid = entry.value;
                  return _buildMuridCard(
                    context: context,
                    isDark: isDark,
                    primary: primary,
                    index: index + 1,
                    murid: murid,
                    kkm: data.kkm,
                    isKelasAkhir: data.isKelasAkhir,
                  );
                }),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildSearchBar(bool isDark, Color primary) {
    return Container(
      decoration: BoxDecoration(
        color: isDark ? AppColors.surfaceContainerDark : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.2 : 0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: TextField(
        controller: _searchController,
        style: TextStyle(
          fontSize: 13,
          color: isDark ? Colors.white : Colors.black87,
        ),
        decoration: InputDecoration(
          hintText: 'Cari nama murid, NISM, atau nama wali...',
          hintStyle: TextStyle(
            fontSize: 12,
            color: isDark ? Colors.white38 : Colors.black38,
          ),
          prefixIcon: Icon(Icons.search_rounded, size: 20, color: primary),
          suffixIcon: _searchQuery.isNotEmpty
              ? IconButton(
                  icon: Icon(
                    Icons.clear_rounded,
                    size: 18,
                    color: isDark ? Colors.white60 : Colors.black54,
                  ),
                  onPressed: () {
                    _searchController.clear();
                  },
                )
              : null,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(vertical: 12),
        ),
      ),
    );
  }

  Widget _buildAlgorithmBanner(
    bool isDark,
    Color primary,
    LaporanKenaikanKelasData data,
  ) {
    final bobot = data.bobotKonfigurasi;
    final hadirText = isDark
        ? AppColors.hadirTextDark
        : AppColors.hadirTextLight;
    final hadirBg = isDark ? AppColors.hadirBgDark : AppColors.hadirBgLight;
    final alphaText = isDark
        ? AppColors.alphaTextDark
        : AppColors.alphaTextLight;
    final alphaBg = isDark ? AppColors.alphaBgDark : AppColors.alphaBgLight;

    return GlassCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: primary.withValues(alpha: isDark ? 0.25 : 0.12),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: primary.withValues(alpha: isDark ? 0.4 : 0.25),
                  ),
                ),
                child: Icon(
                  Icons.auto_awesome_rounded,
                  size: 18,
                  color: primary,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Tabel Pertimbangan Algoritma Kenaikan',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white : Colors.black87,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
            decoration: BoxDecoration(
              color: primary.withValues(alpha: isDark ? 0.15 : 0.08),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(
                color: primary.withValues(alpha: isDark ? 0.3 : 0.2),
              ),
            ),
            child: Row(
              children: [
                Icon(Icons.pie_chart_rounded, size: 14, color: primary),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'Rasio Bobot: Ujian (${bobot.bobotUjian}%) + Presensi (${bobot.bobotPresensi}%) + Disiplin (${bobot.bobotPelanggaran}%)',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: primary,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),

          // Legend KKM
          Row(
            children: [
              Expanded(
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 5.5,
                  ),
                  decoration: BoxDecoration(
                    color: hadirBg,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: hadirText.withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.arrow_upward_rounded,
                        size: 13,
                        color: hadirText,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'KKM > 55 (${data.isKelasAkhir ? "Lulus" : "Naik"})',
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.bold,
                          color: hadirText,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 5.5,
                  ),
                  decoration: BoxDecoration(
                    color: alphaBg,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: alphaText.withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.arrow_downward_rounded,
                        size: 13,
                        color: alphaText,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        '≤ 55 (Tinggal)',
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.bold,
                          color: alphaText,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryStats(
    bool isDark,
    Color primary,
    LaporanKenaikanKelasData data,
  ) {
    final hadirText = isDark
        ? AppColors.hadirTextDark
        : AppColors.hadirTextLight;
    final alphaText = isDark
        ? AppColors.alphaTextDark
        : AppColors.alphaTextLight;

    return Row(
      children: [
        // Total Murid
        Expanded(
          child: _buildStatItem(
            isDark: isDark,
            title: 'Total Murid',
            value: data.totalMurid.toString(),
            color: AppColors.skyBlueAccent,
            icon: Icons.groups_rounded,
          ),
        ),
        const SizedBox(width: 8),

        // Naik Kelas / Lulus
        if (data.isKelasAkhir) ...[
          Expanded(
            child: _buildStatItem(
              isDark: isDark,
              title: 'Lulus',
              value: data.totalLulus.toString(),
              color: hadirText,
              icon: Icons.school_rounded,
            ),
          ),
        ] else ...[
          Expanded(
            child: _buildStatItem(
              isDark: isDark,
              title: 'Naik Kelas',
              value: data.totalNaikKelas.toString(),
              color: hadirText,
              icon: Icons.trending_up_rounded,
            ),
          ),
        ],
        const SizedBox(width: 8),

        // Tinggal Kelas
        Expanded(
          child: _buildStatItem(
            isDark: isDark,
            title: 'Tinggal',
            value: data.totalTinggalKelas.toString(),
            color: alphaText,
            icon: Icons.trending_down_rounded,
          ),
        ),
      ],
    );
  }

  Widget _buildStatItem({
    required bool isDark,
    required String title,
    required String value,
    required Color color,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: isDark ? 0.15 : 0.08),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: color.withValues(alpha: isDark ? 0.35 : 0.22),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                title,
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                  color: isDark
                      ? const Color(0xFF94A3B8)
                      : const Color(0xFF64748B),
                ),
              ),
              Icon(icon, size: 14, color: color),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusTabs(
    bool isDark,
    Color primary,
    LaporanKenaikanKelasData data,
  ) {
    final tabs = [
      {'key': 'Semua', 'label': 'Semua (${data.totalMurid})'},
      if (!data.isKelasAkhir)
        {'key': 'Naik Kelas', 'label': 'Naik (${data.totalNaikKelas})'},
      if (data.isKelasAkhir)
        {'key': 'Lulus', 'label': 'Lulus (${data.totalLulus})'},
      {'key': 'Tinggal Kelas', 'label': 'Tinggal (${data.totalTinggalKelas})'},
    ];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: tabs.map((tab) {
          final isSelected = _selectedStatusFilter == tab['key'];
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: FilterChip(
              selected: isSelected,
              label: Text(tab['label'] as String),
              labelStyle: TextStyle(
                fontSize: 11.5,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                color: isSelected
                    ? Colors.white
                    : (isDark ? Colors.white70 : Colors.black87),
              ),
              selectedColor: primary,
              backgroundColor: isDark
                  ? AppColors.surfaceContainerDark
                  : AppColors.surfaceLight,
              checkmarkColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
                side: BorderSide(
                  color: isSelected
                      ? Colors.transparent
                      : (isDark
                            ? AppColors.outlineDark
                            : AppColors.outlineLight),
                ),
              ),
              onSelected: (_) {
                HapticHelper.selection();
                setState(() {
                  _selectedStatusFilter = tab['key'] as String;
                });
              },
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildMuridCard({
    required BuildContext context,
    required bool isDark,
    required Color primary,
    required int index,
    required MuridKenaikanItem murid,
    required double kkm,
    required bool isKelasAkhir,
  }) {
    final isNaik =
        murid.keputusanFinal == 'Naik Kelas' || murid.keputusanFinal == 'Lulus';
    final statusColor = isNaik
        ? (isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight)
        : (isDark ? AppColors.alphaTextDark : AppColors.alphaTextLight);
    final statusBg = isNaik
        ? (isDark ? AppColors.hadirBgDark : AppColors.hadirBgLight)
        : (isDark ? AppColors.alphaBgDark : AppColors.alphaBgLight);
    final isTop3 = index <= 3;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      child: GlassCard(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Row Atas: Avatar dengan Badge Peringkat terintegrasi, Nama Murid & Status
            Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Avatar + Rank Badge Overlay
                Stack(
                  clipBehavior: Clip.none,
                  children: [
                    CircleAvatar(
                      radius: 20,
                      backgroundColor: isDark
                          ? primary.withValues(alpha: 0.25)
                          : primary.withValues(alpha: 0.12),
                      backgroundImage: murid.foto != null
                          ? NetworkImage(murid.foto!)
                          : null,
                      child: murid.foto == null
                          ? Text(
                              murid.nama.isNotEmpty
                                  ? murid.nama[0].toUpperCase()
                                  : 'M',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: primary,
                                fontSize: 14,
                              ),
                            )
                          : null,
                    ),
                    Positioned(
                      bottom: -2,
                      right: -3,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 4.5,
                          vertical: 1.5,
                        ),
                        decoration: BoxDecoration(
                          color: isTop3
                              ? AppColors.amberAccent
                              : (isDark
                                    ? AppColors.surfaceContainerHighDark
                                    : const Color(0xFF64748B)),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: isDark
                                ? AppColors.surfaceContainerDark
                                : Colors.white,
                            width: 1.5,
                          ),
                        ),
                        child: Text(
                          '$index',
                          style: const TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                            height: 1.1,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(width: 12),

                // Info Nama & NISM / Wali
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        murid.nama,
                        style: TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.bold,
                          letterSpacing: -0.2,
                          color: isDark ? Colors.white : Colors.black87,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2.5),
                      Text(
                        '${murid.nism.isNotEmpty ? murid.nism : "Tanpa NISM"} • Wali: ${murid.wali}',
                        style: TextStyle(
                          fontSize: 11,
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
                const SizedBox(width: 8),

                // Badge Status Kenaikan
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 9,
                    vertical: 4.5,
                  ),
                  decoration: BoxDecoration(
                    color: statusBg,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: statusColor.withValues(alpha: 0.35),
                      width: 1,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isNaik
                            ? Icons.arrow_upward_rounded
                            : Icons.arrow_downward_rounded,
                        size: 11.5,
                        color: statusColor,
                      ),
                      const SizedBox(width: 3.5),
                      Text(
                        murid.keputusanFinal,
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.bold,
                          color: statusColor,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Divider(
              height: 1,
              color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
            ),
            const SizedBox(height: 10),

            // Nilai Sem 1, Sem 2 & Akumulasi
            Row(
              children: [
                // Tot. Sem 1
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      color: isDark
                          ? AppColors.surfaceContainerLowDark
                          : AppColors.surfaceLight,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isDark
                            ? AppColors.outlineDark
                            : AppColors.outlineLight,
                      ),
                    ),
                    child: Column(
                      children: [
                        Text(
                          'Sem 1 (IMDA 1)',
                          style: TextStyle(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w600,
                            color: isDark
                                ? const Color(0xFF94A3B8)
                                : const Color(0xFF64748B),
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 3),
                        Text(
                          murid.skorSem1 > 0
                              ? murid.skorSem1.toStringAsFixed(1)
                              : '-',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 8),

                // Tot. Sem 2
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      color: isDark
                          ? AppColors.surfaceContainerLowDark
                          : AppColors.surfaceLight,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isDark
                            ? AppColors.outlineDark
                            : AppColors.outlineLight,
                      ),
                    ),
                    child: Column(
                      children: [
                        Text(
                          isKelasAkhir ? 'Sem 2 (IMNI)' : 'Sem 2 (IMDA 2)',
                          style: TextStyle(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w600,
                            color: isDark
                                ? const Color(0xFF94A3B8)
                                : const Color(0xFF64748B),
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 3),
                        Text(
                          murid.skorSem2 > 0
                              ? murid.skorSem2.toStringAsFixed(1)
                              : '-',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 8),

                // Final Akumulasi
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      color: statusBg,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: statusColor.withValues(alpha: 0.35),
                      ),
                    ),
                    child: Column(
                      children: [
                        Text(
                          'Akumulasi Final',
                          style: TextStyle(
                            fontSize: 9.5,
                            fontWeight: FontWeight.bold,
                            color: statusColor,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 3),
                        Text(
                          murid.nilaiAkumulasi.toStringAsFixed(2),
                          style: TextStyle(
                            fontSize: 13.5,
                            fontWeight: FontWeight.bold,
                            color: statusColor,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),

            // Baris Bawah: Level Tujuan, Status Kunci & Detail Button
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Level Tujuan / SK
                Expanded(
                  child: Row(
                    children: [
                      Icon(
                        Icons.arrow_forward_rounded,
                        size: 13,
                        color: isDark
                            ? const Color(0xFF94A3B8)
                            : const Color(0xFF64748B),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'Tujuan: ',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark
                              ? const Color(0xFF94A3B8)
                              : const Color(0xFF64748B),
                        ),
                      ),
                      Flexible(
                        child: Text(
                          murid.levelTujuanNama,
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.bold,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (murid.sudahDikunci) ...[
                        const SizedBox(width: 6),
                        Icon(
                          Icons.verified_rounded,
                          size: 14,
                          color: isDark
                              ? AppColors.hadirTextDark
                              : AppColors.hadirTextLight,
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(width: 8),

                // Tombol Detail Rincian
                InkWell(
                  onTap: () {
                    HapticHelper.light();
                    _showCalculationDetailModal(
                      context,
                      isDark,
                      primary,
                      murid,
                    );
                  },
                  borderRadius: BorderRadius.circular(8),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 9,
                      vertical: 4.5,
                    ),
                    decoration: BoxDecoration(
                      color: primary.withValues(alpha: isDark ? 0.2 : 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'Detail Rumus',
                          style: TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.bold,
                            color: primary,
                          ),
                        ),
                        const SizedBox(width: 2),
                        Icon(
                          Icons.chevron_right_rounded,
                          size: 14,
                          color: primary,
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showCalculationDetailModal(
    BuildContext context,
    bool isDark,
    Color primary,
    MuridKenaikanItem murid,
  ) {
    final detail = murid.detailPerhitungan;
    final isNaik =
        murid.keputusanFinal == 'Naik Kelas' || murid.keputusanFinal == 'Lulus';
    final statusColor = isNaik
        ? (isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight)
        : (isDark ? AppColors.alphaTextDark : AppColors.alphaTextLight);
    final statusBg = isNaik
        ? (isDark ? AppColors.hadirBgDark : AppColors.hadirBgLight)
        : (isDark ? AppColors.alphaBgDark : AppColors.alphaBgLight);

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) {
        return Container(
          decoration: BoxDecoration(
            color: isDark ? AppColors.surfaceContainerDark : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: EdgeInsets.fromLTRB(
            20,
            14,
            20,
            30 + MediaQuery.of(context).padding.bottom,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: isDark ? Colors.white24 : Colors.black12,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Header Modal
              Row(
                children: [
                  CircleAvatar(
                    radius: 20,
                    backgroundColor: primary.withValues(
                      alpha: isDark ? 0.25 : 0.15,
                    ),
                    child: Text(
                      murid.nama.isNotEmpty ? murid.nama[0].toUpperCase() : 'M',
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
                          murid.nama,
                          style: TextStyle(
                            fontSize: 14.5,
                            fontWeight: FontWeight.bold,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                        Text(
                          'NISM: ${murid.nism.isNotEmpty ? murid.nism : "-"} • Status: ${murid.keputusanFinal}',
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
                ],
              ),
              const SizedBox(height: 16),
              Divider(
                height: 1,
                color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
              ),
              const SizedBox(height: 14),

              Text(
                'Rincian Komponen Penilaian Algoritma:',
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : Colors.black87,
                ),
              ),
              const SizedBox(height: 10),

              // Rata-rata Ujian
              _buildDetailItemRow(
                icon: Icons.assignment_rounded,
                color: AppColors.skyBlueAccent,
                title: '1. Nilai Ujian (Bobot ${detail?.bobotUjian ?? 60}%)',
                sem1Text: 'Sem 1: ${detail?.rataUjianSem1 ?? 0}',
                sem2Text: 'Sem 2: ${detail?.rataUjianSem2 ?? 0}',
                isDark: isDark,
              ),
              const SizedBox(height: 8),

              // Presensi & Kehadiran (Alpha & Izin)
              _buildDetailItemRow(
                icon: Icons.how_to_reg_rounded,
                color: isDark
                    ? AppColors.hadirTextDark
                    : AppColors.hadirTextLight,
                title:
                    '2. Presensi / Hadir (Bobot ${detail?.bobotPresensi ?? 24}%)',
                sem1Text:
                    'Sem 1: A=${detail?.jumlahAlphaSem1 ?? 0}, I=${detail?.jumlahIzinSem1 ?? 0} (Poin ${detail?.poinPresensiSem1.toStringAsFixed(2) ?? "0"} → Nilai ${detail?.nilaiPresensiSem1.toStringAsFixed(1) ?? "100"})',
                sem2Text:
                    'Sem 2: A=${detail?.jumlahAlphaSem2 ?? 0}, I=${detail?.jumlahIzinSem2 ?? 0} (Poin ${detail?.poinPresensiSem2.toStringAsFixed(2) ?? "0"} → Nilai ${detail?.nilaiPresensiSem2.toStringAsFixed(1) ?? "100"})',
                isDark: isDark,
              ),
              const SizedBox(height: 8),

              // Poin Pelanggaran Kedisiplinan
              _buildDetailItemRow(
                icon: Icons.rule_rounded,
                color: isDark
                    ? AppColors.alphaTextDark
                    : AppColors.alphaTextLight,
                title:
                    '3. Kedisiplinan (Bobot ${detail?.bobotPelanggaran ?? 16}%)',
                sem1Text:
                    'Sem 1: Poin ${detail?.poinPelanggaranSem1.toStringAsFixed(1) ?? "0"} → Nilai ${detail?.nilaiPelanggaranSem1.toStringAsFixed(1) ?? "100"}',
                sem2Text:
                    'Sem 2: Poin ${detail?.poinPelanggaranSem2.toStringAsFixed(1) ?? "0"} → Nilai ${detail?.nilaiPelanggaranSem2.toStringAsFixed(1) ?? "100"}',
                isDark: isDark,
              ),
              const SizedBox(height: 8),

              // Total Skor Semester
              _buildDetailItemRow(
                icon: Icons.calculate_rounded,
                color: AppColors.amberAccent,
                title: 'Total Skor Per Semester',
                sem1Text: 'Skor Sem 1: ${murid.skorSem1.toStringAsFixed(2)}',
                sem2Text: 'Skor Sem 2: ${murid.skorSem2.toStringAsFixed(2)}',
                isDark: isDark,
              ),
              const SizedBox(height: 14),

              // Kotak Akumulasi
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: statusBg,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: statusColor.withValues(alpha: 0.35),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Nilai Akumulasi Akhir:',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark ? Colors.white70 : Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '(${murid.skorSem1.toStringAsFixed(1)} + ${murid.skorSem2.toStringAsFixed(1)}) ÷ 2',
                          style: TextStyle(
                            fontSize: 10,
                            color: isDark
                                ? const Color(0xFF94A3B8)
                                : const Color(0xFF64748B),
                            fontFamily: 'monospace',
                          ),
                        ),
                      ],
                    ),
                    Text(
                      '${murid.nilaiAkumulasi.toStringAsFixed(2)} (${murid.keputusanFinal})',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: statusColor,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Tombol Tutup
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isDark
                        ? AppColors.surfaceContainerHighDark
                        : AppColors.outlineLight,
                    foregroundColor: isDark ? Colors.white : Colors.black87,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  onPressed: () => Navigator.pop(ctx),
                  child: const Text(
                    'Tutup',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDetailItemRow({
    required IconData icon,
    required Color color,
    required String title,
    required String sem1Text,
    required String sem2Text,
    required bool isDark,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: isDark
            ? AppColors.surfaceContainerLowDark
            : AppColors.surfaceLight,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 14, color: color),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white : Colors.black87,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                sem1Text,
                style: TextStyle(
                  fontSize: 10.5,
                  color: isDark
                      ? const Color(0xFF94A3B8)
                      : const Color(0xFF64748B),
                ),
              ),
              Text(
                sem2Text,
                style: TextStyle(
                  fontSize: 10.5,
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

  Widget _buildEmptySantri(bool isDark, Color primary) {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: isDark ? AppColors.surfaceContainerDark : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
        ),
      ),
      child: Column(
        children: [
          Icon(
            Icons.search_off_rounded,
            size: 48,
            color: isDark ? Colors.white30 : Colors.black26,
          ),
          const SizedBox(height: 12),
          Text(
            'Tidak ada murid yang sesuai filter',
            style: TextStyle(
              fontWeight: FontWeight.bold,
              fontSize: 13.5,
              color: isDark ? Colors.white : Colors.black87,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Coba ubah kata kunci pencarian atau tab status kenaikan.',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 11.5,
              color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLoadingSkeleton() {
    return const ShimmerLoadingList(count: 4, height: 110);
  }

  Widget _buildErrorCard(String error, bool isDark, Color primary) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.roseDanger.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.roseDanger.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          const Icon(
            Icons.error_outline_rounded,
            size: 40,
            color: AppColors.roseDanger,
          ),
          const SizedBox(height: 10),
          Text(
            error,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 12,
              color: isDark ? Colors.white : Colors.black87,
            ),
          ),
          const SizedBox(height: 14),
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(
              backgroundColor: primary,
              foregroundColor: Colors.white,
            ),
            onPressed: () => _loadData(),
            icon: const Icon(Icons.refresh_rounded, size: 16),
            label: const Text('Coba Lagi'),
          ),
        ],
      ),
    );
  }
}
