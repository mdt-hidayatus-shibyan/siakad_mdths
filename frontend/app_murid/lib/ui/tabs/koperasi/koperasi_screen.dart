import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/koperasi_model.dart';
import '../../../providers/dashboard_provider.dart';
import '../../../providers/keuangan_provider.dart';
import '../../widgets/child_switcher_bar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/modern_header.dart';

class KoperasiScreen extends StatefulWidget {
  final bool isFullScreen;

  const KoperasiScreen({super.key, this.isFullScreen = true});

  @override
  State<KoperasiScreen> createState() => _KoperasiScreenState();
}

class _KoperasiScreenState extends State<KoperasiScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final dashboard = context.read<DashboardProvider>();
      final selectedAnak = dashboard.selectedAnak;
      if (selectedAnak != null) {
        context.read<KeuanganProvider>().fetchKoperasi(
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
      await context.read<KeuanganProvider>().fetchKoperasi(
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
                titleText: 'Koperasi Madrasah',
                subtitleText: 'Riwayat Transaksi Koperasi Murid',
              )
            : null,
        body: const EmptyStateWidget(
          icon: Icons.child_care_rounded,
          title: 'Pilih Murid Terlebih Dahulu',
          subtitle: 'Silakan pilih profil anak pada menu Beranda.',
        ),
      );
    }

    final data = keuangan.koperasiData;
    final isLoading = keuangan.isLoadingKoperasi && data == null;

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
                  Expanded(
                    child: ModernHeader(
                      title: 'Koperasi Madrasah',
                      subtitle:
                          'Riwayat Pembelian Kitab, Seragam & Jajanan Murid',
                      icon: Icons.storefront_rounded,
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
                    : data == null || data.riwayat.isEmpty
                    ? const SingleChildScrollView(
                        physics: AlwaysScrollableScrollPhysics(
                          parent: BouncingScrollPhysics(),
                        ),
                        child: Padding(
                          padding: EdgeInsets.only(top: 80),
                          child: EmptyStateWidget(
                            icon: Icons.storefront_outlined,
                            title: 'Belum Ada Transaksi Koperasi',
                            subtitle:
                                'Belum ada riwayat pembelian seragam, kitab, alat tulis, atau jajanan murid di Koperasi MDT Hidayatus Shibyan.',
                          ),
                        ),
                      )
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
                          // Summary Banner Belanja Koperasi
                          _buildSummaryBanner(data.summary, isDark),
                          const SizedBox(height: 16),

                          Text(
                            'RIWAYAT NOTA PEMBELIAN (${data.riwayat.length})',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.8,
                              color: isDark ? Colors.white60 : Colors.black54,
                            ),
                          ),
                          const SizedBox(height: 8),

                          ...data.riwayat.map(
                            (nota) => _buildNotaKoperasiCard(nota, isDark),
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

  Widget _buildSummaryBanner(KoperasiSummary summary, bool isDark) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: isDark
              ? const [Color(0xFF231838), Color(0xFF120C20)]
              : const [Color(0xFF7C3AED), Color(0xFF5B21B6)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.35 : 0.08),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'TOTAL BELANJA DI KOPERASI MADRASAH',
                style: TextStyle(
                  color: Colors.white70,
                  fontSize: 10,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.8,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '${summary.totalTransaksi} Nota',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            CurrencyFormatter.format(summary.totalBelanja),
            style: const TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 10),
          const Divider(color: Colors.white24, height: 1),
          const SizedBox(height: 8),
          Text(
            'Total ${summary.totalItem} item produk/paket telah dibeli murid',
            style: const TextStyle(color: Colors.white70, fontSize: 11),
          ),
        ],
      ),
    );
  }

  Widget _buildNotaKoperasiCard(TransaksiKoperasiItem nota, bool isDark) {
    final isPotongTabungan = nota.isPotongTabungan;

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GlassCard(
        padding: const EdgeInsets.all(16),
        borderRadius: 20,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header Nota: No Nota, Status, Metode
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF2A1C40)
                            : const Color(0xFFF3E8FF),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.receipt_long_rounded,
                        size: 18,
                        color: Color(0xFF7C3AED),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          nota.nomorNota,
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w900,
                            fontFamily: 'monospace',
                          ),
                        ),
                        Text(
                          nota.tanggalFormatted,
                          style: TextStyle(
                            fontSize: 10,
                            color: isDark ? Colors.white54 : Colors.black45,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: isPotongTabungan
                        ? const Color(0xFF10B981).withValues(alpha: 0.15)
                        : (isDark
                              ? const Color(0xFF1E281E)
                              : const Color(0xFFF1F5F9)),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (isPotongTabungan) ...[
                        const Icon(
                          Icons.account_balance_wallet_rounded,
                          size: 11,
                          color: Color(0xFF10B981),
                        ),
                        const SizedBox(width: 3),
                      ],
                      Text(
                        nota.metodePembayaran,
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: isPotongTabungan
                              ? const Color(0xFF10B981)
                              : (isDark ? Colors.white70 : Colors.black87),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            const Divider(height: 1),
            const SizedBox(height: 10),

            // Item Details
            ...nota.items.map((item) {
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 3),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item.namaItem,
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          Text(
                            '${item.jumlah} ${item.satuan} x ${CurrencyFormatter.format(item.hargaSatuan)}',
                            style: TextStyle(
                              fontSize: 10,
                              color: isDark ? Colors.white54 : Colors.black45,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Text(
                      CurrencyFormatter.format(item.subtotal),
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
              );
            }),

            const SizedBox(height: 10),
            const Divider(height: 1),
            const SizedBox(height: 8),

            // Total Akhir & Kasir Footer
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Kasir: ${nota.kasir}',
                  style: TextStyle(
                    fontSize: 10,
                    color: isDark ? Colors.white38 : Colors.black38,
                  ),
                ),
                Row(
                  children: [
                    const Text(
                      'Total: ',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    Text(
                      CurrencyFormatter.format(nota.totalAkhir),
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF7C3AED),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
