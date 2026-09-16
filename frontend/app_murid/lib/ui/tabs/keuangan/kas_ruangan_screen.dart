import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/kas_ruangan_model.dart';
import '../../../providers/dashboard_provider.dart';
import '../../../providers/keuangan_provider.dart';
import '../../widgets/child_switcher_bar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/modern_header.dart';

class KasRuanganScreen extends StatefulWidget {
  final bool isFullScreen;

  const KasRuanganScreen({super.key, this.isFullScreen = true});

  @override
  State<KasRuanganScreen> createState() => _KasRuanganScreenState();
}

class _KasRuanganScreenState extends State<KasRuanganScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final dashboard = context.read<DashboardProvider>();
      final selectedAnak = dashboard.selectedAnak;
      if (selectedAnak != null) {
        context.read<KeuanganProvider>().fetchKasRuangan(
          selectedAnak.id,
          force: true,
        );
      }
    });
  }

  Future<void> _onRefresh() async {
    HapticHelper.light();
    final dashboard = context.read<DashboardProvider>();
    final selectedAnak = dashboard.selectedAnak;
    if (selectedAnak != null) {
      await context.read<KeuanganProvider>().fetchKasRuangan(
        selectedAnak.id,
        force: true,
      );
    }
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
            ? const CustomAppBar(
                titleText: 'Kas Ruangan',
                subtitleText: 'Rincian Iuran & Kas Santri',
              )
            : null,
        body: const EmptyStateWidget(
          icon: Icons.child_care_rounded,
          title: 'Pilih Murid Terlebih Dahulu',
          subtitle: 'Silakan pilih profil anak pada menu Beranda.',
        ),
      );
    }

    final data = keuangan.kasRuanganData;
    final isLoading = keuangan.isLoadingKasRuangan && data == null;

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
                    CircularIconButton(
                      icon: Icons.chevron_left_rounded,
                      iconSize: 26,
                      onPressed: () => Navigator.of(context).pop(),
                      tooltip: 'Kembali',
                    ),
                    const SizedBox(width: 12),
                  ],
                  const Expanded(
                    child: ModernHeader(
                      title: 'Kas Ruangan',
                      subtitle: 'Rincian Target, Terkumpul & Kurang Kas Santri',
                      icon: Icons.meeting_room_rounded,
                    ),
                  ),
                ],
              ),
            ),

            // Multi-Child Switcher Bar
            const ChildSwitcherBar(),

            Expanded(
              child: RefreshIndicator(
                onRefresh: _onRefresh,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
                child: isLoading
                    ? Center(
                        child: CircularProgressIndicator(
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                      )
                    : data == null
                    ? SingleChildScrollView(
                        physics: const AlwaysScrollableScrollPhysics(
                          parent: BouncingScrollPhysics(),
                        ),
                        child: Padding(
                          padding: const EdgeInsets.only(top: 80),
                          child: EmptyStateWidget(
                            icon: Icons.error_outline_rounded,
                            title: 'Gagal Memuat Data',
                            subtitle:
                                keuangan.errorMessage ??
                                'Terjadi kesalahan saat memuat data kas ruangan.',
                            action: ElevatedButton.icon(
                              onPressed: _onRefresh,
                              icon: const Icon(Icons.refresh_rounded),
                              label: const Text('Coba Lagi'),
                            ),
                          ),
                        ),
                      )
                    : !data.hasKas
                    ? _buildNoKasView(data, isDark)
                    : ListView(
                        physics: const AlwaysScrollableScrollPhysics(
                          parent: BouncingScrollPhysics(),
                        ),
                        padding: EdgeInsets.fromLTRB(
                          16,
                          8,
                          16,
                          widget.isFullScreen ? 24 : 100,
                        ),
                        children: [
                          // Kartu Rincian Kas Santri (Target, Terkumpul, Kurang)
                          if (data.murid != null && data.ruangan != null)
                            _buildKasSummaryCard(
                              data.murid!,
                              data.ruangan!,
                              isDark,
                            ),
                          const SizedBox(height: 18),

                          // Section Riwayat Pembayaran
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'RIWAYAT PEMBAYARAN KAS (${data.riwayatPembayaran.length})',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 0.8,
                                  color: isDark
                                      ? Colors.white60
                                      : Colors.black54,
                                ),
                              ),
                              if (data.murid != null)
                                Text(
                                  'Terkumpul: ${CurrencyFormatter.format(data.murid!.totalKasTerkumpul)}',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w800,
                                    color: Color(0xFF10B981),
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 10),

                          if (data.riwayatPembayaran.isEmpty)
                            GlassCard(
                              padding: const EdgeInsets.symmetric(
                                vertical: 36,
                                horizontal: 16,
                              ),
                              child: Column(
                                children: [
                                  Icon(
                                    Icons.receipt_long_outlined,
                                    size: 44,
                                    color: isDark
                                        ? Colors.white38
                                        : Colors.black38,
                                  ),
                                  const SizedBox(height: 10),
                                  Text(
                                    'Belum Ada Catatan Pembayaran',
                                    style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w700,
                                      color: isDark
                                          ? Colors.white70
                                          : Colors.black87,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Belum ada transaksi pembayaran kas yang tercatat untuk santri ini.',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isDark
                                          ? Colors.white54
                                          : Colors.black54,
                                    ),
                                  ),
                                ],
                              ),
                            )
                          else
                            ...data.riwayatPembayaran.map(
                              (item) => _buildRiwayatItem(item, isDark),
                            ),
                        ],
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNoKasView(KasRuanganAnakData data, bool isDark) {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(
        parent: BouncingScrollPhysics(),
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          const SizedBox(height: 40),
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: isDark
                  ? AppColors.primaryDark.withValues(alpha: 0.15)
                  : AppColors.primaryLight.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(
              Icons.meeting_room_outlined,
              size: 44,
              color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            ),
          ),
          const SizedBox(height: 20),
          Text(
            'Tidak Ada Kas Ruangan',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w800,
              color: isDark ? Colors.white : Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            data.pesan ??
                'Ruangan kelas santri saat ini belum memiliki catatan kas ruangan.',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 13,
              color: isDark ? Colors.white60 : Colors.black54,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 24),
          if (data.ruangan != null)
            GlassCard(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: isDark
                          ? AppColors.primaryDark.withValues(alpha: 0.2)
                          : AppColors.primaryLight.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(
                      Icons.school_rounded,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          data.ruangan!.namaRuangan,
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Wali Ruangan: ${data.ruangan!.waliRuangan}',
                          style: TextStyle(
                            fontSize: 12,
                            color: isDark ? Colors.white60 : Colors.black54,
                          ),
                        ),
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

  Widget _buildKasSummaryCard(
    KasMuridModel murid,
    KasRuanganInfoModel ruangan,
    bool isDark,
  ) {
    Color statusColor;
    Color statusBgColor;

    if (murid.isLunas) {
      statusColor = const Color(0xFF10B981);
      statusBgColor = const Color(0xFF10B981).withValues(alpha: 0.15);
    } else if (murid.isBebasKas) {
      statusColor = AppColors.skyBlueAccent;
      statusBgColor = AppColors.skyBlueAccent.withValues(alpha: 0.15);
    } else if (murid.status == 'Sebagian') {
      statusColor = const Color(0xFFF59E0B);
      statusBgColor = const Color(0xFFF59E0B).withValues(alpha: 0.15);
    } else {
      statusColor = const Color(0xFFEF4444);
      statusBgColor = const Color(0xFFEF4444).withValues(alpha: 0.15);
    }

    return GlassCard(
      padding: const EdgeInsets.all(18),
      borderRadius: 20,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF6366F1).withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.meeting_room_rounded,
                      size: 18,
                      color: Color(0xFF6366F1),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        ruangan.namaRuangan,
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          color: isDark ? Colors.white : Colors.black87,
                        ),
                      ),
                      Text(
                        'Wali: ${ruangan.waliRuangan}',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                          color: isDark ? Colors.white60 : Colors.black54,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: statusBgColor,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: statusColor.withValues(alpha: 0.3)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      murid.isLunas
                          ? Icons.check_circle_rounded
                          : Icons.pending_rounded,
                      size: 13,
                      color: statusColor,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      murid.status,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: statusColor,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          const Divider(height: 1),
          const SizedBox(height: 16),

          // 3 Kolom: Target Kas, Terkumpul, Kurang
          Row(
            children: [
              Expanded(
                child: _buildMetricItem(
                  label: 'Target Kas',
                  value: CurrencyFormatter.format(murid.targetKas),
                  color: isDark ? Colors.white : Colors.black87,
                  isDark: isDark,
                ),
              ),
              Container(
                width: 1,
                height: 36,
                color: isDark ? Colors.white12 : Colors.black12,
              ),
              Expanded(
                child: _buildMetricItem(
                  label: 'Terkumpul',
                  value: CurrencyFormatter.format(murid.totalKasTerkumpul),
                  color: const Color(0xFF10B981),
                  isDark: isDark,
                ),
              ),
              Container(
                width: 1,
                height: 36,
                color: isDark ? Colors.white12 : Colors.black12,
              ),
              Expanded(
                child: _buildMetricItem(
                  label: 'Kurang',
                  value: CurrencyFormatter.format(murid.kurangKas),
                  color: murid.kurangKas > 0
                      ? const Color(0xFFEF4444)
                      : const Color(0xFF10B981),
                  isDark: isDark,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildMetricItem({
    required String label,
    required String value,
    required Color color,
    required bool isDark,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w600,
              color: isDark ? Colors.white54 : Colors.black54,
            ),
          ),
          const SizedBox(height: 3),
          FittedBox(
            fit: BoxFit.scaleDown,
            child: Text(
              value,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w900,
                color: color,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRiwayatItem(RiwayatPembayaranKasModel item, bool isDark) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: GlassCard(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        borderRadius: 16,
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFF10B981).withValues(alpha: 0.15),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.check_rounded,
                size: 18,
                color: Color(0xFF10B981),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.tanggalBayarFormatted,
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
                        item.isDisetor
                            ? Icons.verified_rounded
                            : Icons.schedule_rounded,
                        size: 12,
                        color: item.isDisetor
                            ? const Color(0xFF10B981)
                            : const Color(0xFFF59E0B),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        item.statusSetor,
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: item.isDisetor
                              ? const Color(0xFF10B981)
                              : const Color(0xFFF59E0B),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            Text(
              CurrencyFormatter.format(item.jumlahBayar),
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w900,
                color: Color(0xFF10B981),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
