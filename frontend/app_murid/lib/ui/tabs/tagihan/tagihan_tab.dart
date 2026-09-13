import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/dashboard_provider.dart';
import '../../../providers/keuangan_provider.dart';
import '../../widgets/child_switcher_bar.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/modern_header.dart';
import '../../widgets/segmented_tab_bar.dart';

class TagihanTab extends StatefulWidget {
  final int initialTabIndex;
  final bool isFullScreen;

  const TagihanTab({
    super.key,
    this.initialTabIndex = 0,
    this.isFullScreen = false,
  });

  @override
  State<TagihanTab> createState() => _TagihanTabState();
}

class _TagihanTabState extends State<TagihanTab>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: 3,
      vsync: this,
      initialIndex: widget.initialTabIndex.clamp(0, 2),
    );
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        setState(() {});
      }
    });

    WidgetsBinding.instance.addPostFrameCallback((_) {
      final dashboard = context.read<DashboardProvider>();
      final selectedAnak = dashboard.selectedAnak;
      if (selectedAnak != null) {
        context.read<KeuanganProvider>().fetchAllKeuangan(selectedAnak.id);
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
    final dashboard = context.watch<DashboardProvider>();
    final selectedAnak = dashboard.selectedAnak;
    final keuangan = context.watch<KeuanganProvider>();

    if (selectedAnak == null) {
      return Scaffold(
        backgroundColor: isDark
            ? AppColors.surfaceDark
            : AppColors.surfaceLight,
        appBar: widget.isFullScreen
            ? AppBar(
                title: const Text('Tagihan Murid'),
                backgroundColor: Colors.transparent,
                elevation: 0,
              )
            : null,
        body: const EmptyStateWidget(
          icon: Icons.family_restroom_rounded,
          title: 'Pilih Murid Terlebih Dahulu',
          subtitle: 'Silakan pilih profil anak pada menu Beranda.',
        ),
      );
    }

    return Scaffold(
      backgroundColor: isDark ? AppColors.surfaceDark : AppColors.surfaceLight,
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
              child: Row(
                children: [
                  if (widget.isFullScreen || Navigator.canPop(context)) ...[
                    IconButton(
                      onPressed: () => Navigator.of(context).pop(),
                      icon: const Icon(Icons.arrow_back_ios_new_rounded),
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(),
                    ),
                    const SizedBox(width: 12),
                  ],
                  Expanded(
                    child: ModernHeader(
                      title: 'Tagihan & Pembayaran',
                      subtitle: 'Monitoring SPP, Tagihan Murid & Tagihan KK',
                    ),
                  ),
                ],
              ),
            ),

            // Multi-Child Switcher Bar
            const ChildSwitcherBar(),

            // 3 Segmented Tabs (SPP, Tagihan Murid, Tagihan KK)
            SegmentedTabBar(
              controller: _tabController,
              tabs: const [
                SegmentedTabBarItem(
                  label: 'SPP Syahriyah',
                  icon: Icons.credit_card_rounded,
                ),
                SegmentedTabBarItem(
                  label: 'Tagihan Murid',
                  icon: Icons.receipt_long_rounded,
                ),
                SegmentedTabBarItem(
                  label: 'Tagihan KK',
                  icon: Icons.family_restroom_rounded,
                ),
              ],
            ),

            Expanded(
              child: RefreshIndicator(
                onRefresh: () async {
                  HapticHelper.light();
                  await keuangan.fetchAllKeuangan(selectedAnak.id, force: true);
                },
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
                child: TabBarView(
                  controller: _tabController,
                  physics: const BouncingScrollPhysics(),
                  children: [
                    _buildSppView(keuangan, isDark),
                    _buildNonSppView(keuangan, isDark),
                    _buildTagihanWaliView(keuangan, dashboard, isDark),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // =========================================================================
  // TAB 1: KARTU SPP 11 BULAN
  // =========================================================================
  Widget _buildSppView(KeuanganProvider keuangan, bool isDark) {
    if (keuangan.isLoading) {
      return Center(
        child: CircularProgressIndicator(
          color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        ),
      );
    }

    final sppList = keuangan.sppList;
    final rekap = keuangan.rekapTagihan;

    if (sppList.isEmpty) {
      return const EmptyStateWidget(
        icon: Icons.credit_card_off_rounded,
        title: 'Belum Ada Tagihan SPP',
        subtitle: 'Data tagihan SPP bulanan murid belum diatur oleh admin.',
      );
    }

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(
        parent: BouncingScrollPhysics(),
      ),
      padding: EdgeInsets.fromLTRB(16, 8, 16, widget.isFullScreen ? 24 : 100),
      children: [
        if (rekap != null) ...[
          GlassCard(
            padding: const EdgeInsets.all(16),
            borderRadius: 20,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildSummaryColumn(
                  'Total Target SPP',
                  CurrencyFormatter.format(rekap.totalSpp),
                  isDark ? Colors.white70 : Colors.black87,
                  isDark,
                ),
                Container(
                  height: 32,
                  width: 1,
                  color: isDark ? Colors.white12 : Colors.black12,
                ),
                _buildSummaryColumn(
                  'Total Terbayar',
                  CurrencyFormatter.format(rekap.totalSppLunas),
                  const Color(0xFF10B981),
                  isDark,
                ),
                Container(
                  height: 32,
                  width: 1,
                  color: isDark ? Colors.white12 : Colors.black12,
                ),
                _buildSummaryColumn(
                  'Tunggakan',
                  CurrencyFormatter.format(rekap.totalSppTunggakan),
                  rekap.totalSppTunggakan > 0
                      ? AppColors.roseDanger
                      : const Color(0xFF10B981),
                  isDark,
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
        ],

        Text(
          'KARTU SPP SYAHRIYAH (11 BULAN HIJRIYAH)',
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w900,
            letterSpacing: 0.8,
            color: isDark ? Colors.white60 : Colors.black54,
          ),
        ),
        const SizedBox(height: 8),

        ...sppList.map((spp) {
          final isLunas = spp.isLunas;
          final isBebas = spp.isBebas;

          Color badgeBg = Colors.grey.withValues(alpha: 0.15);
          Color badgeColor = Colors.grey;

          if (isLunas) {
            badgeBg = const Color(0xFF10B981).withValues(alpha: 0.15);
            badgeColor = const Color(0xFF10B981);
          } else if (isBebas) {
            badgeBg = AppColors.skyBlueAccent.withValues(alpha: 0.15);
            badgeColor = AppColors.skyBlueAccent;
          } else {
            badgeBg = AppColors.amberAccent.withValues(alpha: 0.15);
            badgeColor = const Color(0xFFD97706);
          }

          return Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: GlassCard(
              padding: const EdgeInsets.all(16),
              borderRadius: 20,
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: isLunas
                          ? const Color(0xFF10B981).withValues(alpha: 0.12)
                          : (isDark
                                ? Colors.white.withValues(alpha: 0.05)
                                : Colors.black.withValues(alpha: 0.03)),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Icon(
                      isLunas
                          ? Icons.check_circle_rounded
                          : Icons.calendar_month_rounded,
                      color: isLunas
                          ? const Color(0xFF10B981)
                          : (isDark ? Colors.white60 : Colors.black54),
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Bulan ${spp.bulan}',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w800,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          CurrencyFormatter.format(spp.nominal),
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                        if (spp.tanggalBayar != null) ...[
                          const SizedBox(height: 2),
                          Text(
                            'Dibayar: ${spp.tanggalBayar} • Kwitansi: ${spp.noTransaksi ?? "-"}',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w500,
                              color: isDark ? Colors.white54 : Colors.black54,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 5,
                    ),
                    decoration: BoxDecoration(
                      color: badgeBg,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      spp.statusBayar,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w900,
                        color: badgeColor,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }

  // =========================================================================
  // TAB 2: TAGIHAN NON-SPP
  // =========================================================================
  Widget _buildNonSppView(KeuanganProvider keuangan, bool isDark) {
    if (keuangan.isLoading) {
      return Center(
        child: CircularProgressIndicator(
          color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        ),
      );
    }

    final nonSppList = keuangan.nonSppList;
    final rekap = keuangan.rekapTagihan;

    if (nonSppList.isEmpty) {
      return const EmptyStateWidget(
        icon: Icons.receipt_long_rounded,
        title: 'Tidak Ada Tagihan Non-SPP',
        subtitle: 'Tidak ada tagihan berkala atau insidental untuk murid ini.',
      );
    }

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(
        parent: BouncingScrollPhysics(),
      ),
      padding: EdgeInsets.fromLTRB(16, 8, 16, widget.isFullScreen ? 24 : 100),
      children: [
        if (rekap != null) ...[
          GlassCard(
            padding: const EdgeInsets.all(16),
            borderRadius: 20,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildSummaryColumn(
                  'Total Target Non-SPP',
                  CurrencyFormatter.format(rekap.totalNonSpp),
                  isDark ? Colors.white70 : Colors.black87,
                  isDark,
                ),
                Container(
                  height: 32,
                  width: 1,
                  color: isDark ? Colors.white12 : Colors.black12,
                ),
                _buildSummaryColumn(
                  'Total Terbayar',
                  CurrencyFormatter.format(rekap.totalNonSppLunas),
                  const Color(0xFF10B981),
                  isDark,
                ),
                Container(
                  height: 32,
                  width: 1,
                  color: isDark ? Colors.white12 : Colors.black12,
                ),
                _buildSummaryColumn(
                  'Tunggakan',
                  CurrencyFormatter.format(rekap.totalNonSppTunggakan),
                  rekap.totalNonSppTunggakan > 0
                      ? AppColors.roseDanger
                      : const Color(0xFF10B981),
                  isDark,
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
        ],

        Text(
          'TAGIHAN NON-SPP (UJIAN, KITAB, SERAGAM, DLL)',
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w900,
            letterSpacing: 0.8,
            color: isDark ? Colors.white60 : Colors.black54,
          ),
        ),
        const SizedBox(height: 8),

        ...nonSppList.map((tagihan) {
          final isLunas = tagihan.isLunas;

          return Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: GlassCard(
              padding: const EdgeInsets.all(16),
              borderRadius: 20,
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: isLunas
                          ? const Color(0xFF10B981).withValues(alpha: 0.12)
                          : (isDark
                                ? Colors.white.withValues(alpha: 0.05)
                                : Colors.black.withValues(alpha: 0.03)),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Icon(
                      isLunas
                          ? Icons.check_circle_rounded
                          : Icons.receipt_rounded,
                      color: isLunas
                          ? const Color(0xFF10B981)
                          : (isDark ? Colors.white60 : Colors.black54),
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          tagihan.namaTagihan,
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w800,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          CurrencyFormatter.format(tagihan.nominal),
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                        if (tagihan.tanggalBayar != null) ...[
                          const SizedBox(height: 2),
                          Text(
                            'Dibayar: ${tagihan.tanggalBayar} • No. Kwitansi: ${tagihan.noTransaksi ?? "-"}',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w500,
                              color: isDark ? Colors.white54 : Colors.black54,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 5,
                    ),
                    decoration: BoxDecoration(
                      color: isLunas
                          ? const Color(0xFF10B981).withValues(alpha: 0.15)
                          : AppColors.amberAccent.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      tagihan.statusBayar,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w900,
                        color: isLunas
                            ? const Color(0xFF10B981)
                            : const Color(0xFFD97706),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }

  // =========================================================================
  // TAB 3: TAGIHAN WALI MURID (KK / KELUARGA)
  // =========================================================================
  Widget _buildTagihanWaliView(
    KeuanganProvider keuangan,
    DashboardProvider dashboard,
    bool isDark,
  ) {
    if (keuangan.isLoadingTagihanWali && keuangan.rekapTagihanWali == null) {
      return Center(
        child: CircularProgressIndicator(
          color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        ),
      );
    }

    final waliList = keuangan.tagihanWaliList;
    final rekap = keuangan.rekapTagihanWali;
    final wali = dashboard.dashboardData?.wali;

    if (waliList.isEmpty) {
      return const EmptyStateWidget(
        icon: Icons.family_restroom_rounded,
        title: 'Belum Ada Tagihan KK',
        subtitle:
            'Tidak ada tagihan keluarga (seperti infaq gedung, haflah, dll.) yang dibebankan kepada wali murid saat ini.',
      );
    }

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(
        parent: BouncingScrollPhysics(),
      ),
      padding: EdgeInsets.fromLTRB(16, 8, 16, widget.isFullScreen ? 24 : 100),
      children: [
        if (rekap != null) ...[
          GlassCard(
            padding: const EdgeInsets.all(16),
            borderRadius: 20,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildSummaryColumn(
                  'Total Tagihan KK',
                  CurrencyFormatter.format(rekap.totalTagihan),
                  isDark ? Colors.white70 : Colors.black87,
                  isDark,
                ),
                Container(
                  height: 32,
                  width: 1,
                  color: isDark ? Colors.white12 : Colors.black12,
                ),
                _buildSummaryColumn(
                  'Total Terbayar',
                  CurrencyFormatter.format(rekap.totalLunas),
                  const Color(0xFF10B981),
                  isDark,
                ),
                Container(
                  height: 32,
                  width: 1,
                  color: isDark ? Colors.white12 : Colors.black12,
                ),
                _buildSummaryColumn(
                  'Tunggakan',
                  CurrencyFormatter.format(rekap.totalTunggakan),
                  rekap.totalTunggakan > 0
                      ? AppColors.roseDanger
                      : const Color(0xFF10B981),
                  isDark,
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
        ],

        if (wali != null) ...[
          GlassCard(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            borderRadius: 16,
            child: Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color:
                        (isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight)
                            .withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    Icons.badge_rounded,
                    color: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'KK: ${wali.namaKepalaKeluarga}',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                          color: isDark ? Colors.white : Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'No. KK: ${wali.noKk} • Reg: ${wali.noRegistrasi}${wali.kampung != null && wali.kampung!.isNotEmpty ? " • Ds. ${wali.kampung}" : ""}',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                          color: isDark ? Colors.white60 : Colors.black54,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
        ],

        Text(
          'TAGIHAN PER KELUARGA / KK (INFAQ, HAFLAH, DLL)',
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w900,
            letterSpacing: 0.8,
            color: isDark ? Colors.white60 : Colors.black54,
          ),
        ),
        const SizedBox(height: 8),

        ...waliList.map((tagihan) {
          final isLunas = tagihan.isLunas;

          Color badgeBg = Colors.grey.withValues(alpha: 0.15);
          Color badgeColor = Colors.grey;

          if (isLunas) {
            badgeBg = const Color(0xFF10B981).withValues(alpha: 0.15);
            badgeColor = const Color(0xFF10B981);
          } else {
            badgeBg = AppColors.amberAccent.withValues(alpha: 0.15);
            badgeColor = const Color(0xFFD97706);
          }

          return Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: GlassCard(
              padding: const EdgeInsets.all(16),
              borderRadius: 20,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: isLunas
                              ? const Color(0xFF10B981).withValues(alpha: 0.12)
                              : (isDark
                                    ? Colors.white.withValues(alpha: 0.05)
                                    : Colors.black.withValues(alpha: 0.03)),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Icon(
                          isLunas
                              ? Icons.check_circle_rounded
                              : Icons.account_balance_wallet_rounded,
                          color: isLunas
                              ? const Color(0xFF10B981)
                              : (isDark ? Colors.white60 : Colors.black54),
                          size: 22,
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              tagihan.namaTagihan,
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w800,
                                color: isDark ? Colors.white : Colors.black87,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              CurrencyFormatter.format(tagihan.nominal),
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
                          vertical: 5,
                        ),
                        decoration: BoxDecoration(
                          color: badgeBg,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          tagihan.statusBayar,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w900,
                            color: badgeColor,
                          ),
                        ),
                      ),
                    ],
                  ),
                  if (tagihan.tahunPelajaran != null ||
                      tagihan.tanggalBayar != null ||
                      tagihan.keterangan != null) ...[
                    const SizedBox(height: 10),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: isDark
                            ? Colors.white.withValues(alpha: 0.03)
                            : Colors.black.withValues(alpha: 0.02),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (tagihan.tahunPelajaran != null)
                            Text(
                              'Tahun Ajaran: ${tagihan.tahunPelajaran}',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: isDark ? Colors.white70 : Colors.black87,
                              ),
                            ),
                          if (tagihan.tanggalBayar != null) ...[
                            const SizedBox(height: 2),
                            Text(
                              'Dibayar: ${tagihan.tanggalBayar} • Kwitansi: ${tagihan.noTransaksi ?? "-"} • Metode: ${tagihan.metodePembayaran ?? "Tunai"}',
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w500,
                                color: isDark ? Colors.white60 : Colors.black54,
                              ),
                            ),
                          ],
                          if (tagihan.keterangan != null &&
                              tagihan.keterangan!.trim().isNotEmpty) ...[
                            const SizedBox(height: 2),
                            Text(
                              'Catatan: ${tagihan.keterangan}',
                              style: TextStyle(
                                fontSize: 10,
                                fontStyle: FontStyle.italic,
                                color: isDark ? Colors.white54 : Colors.black54,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),
          );
        }),
      ],
    );
  }

  Widget _buildSummaryColumn(
    String title,
    String value,
    Color color,
    bool isDark,
  ) {
    return Column(
      children: [
        Text(
          title,
          style: TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w700,
            color: isDark ? Colors.white54 : Colors.black54,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w900,
            color: color,
          ),
        ),
      ],
    );
  }
}
