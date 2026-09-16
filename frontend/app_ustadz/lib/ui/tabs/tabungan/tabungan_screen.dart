import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/tabungan_model.dart';
import '../../../providers/tabungan_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';
import 'detail_tabungan_screen.dart';

class TabunganScreen extends StatefulWidget {
  final int? initialRuanganId;

  const TabunganScreen({super.key, this.initialRuanganId});

  @override
  State<TabunganScreen> createState() => _TabunganScreenState();
}

class _TabunganScreenState extends State<TabunganScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final p = context.read<TabunganProvider>();
      p.fetchTabunganUstadz();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<TabunganProvider>();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Tabungan Pribadi',
        actions: [
          CircularIconButton(
            icon: Icons.refresh_rounded,
            tooltip: 'Segarkan',
            iconColor: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            onPressed: () {
              HapticHelper.light();
              provider.fetchTabunganUstadz();
            },
          ),
        ],
      ),
      body: _buildTabunganPribadiView(provider, isDark),
    );
  }

  // =========================================================================
  // TAB 1: TABUNGAN PRIBADI USTADZ
  // =========================================================================
  Widget _buildTabunganPribadiView(TabunganProvider provider, bool isDark) {
    if (provider.isLoading && provider.tabunganUstadz == null) {
      return const Padding(
        padding: EdgeInsets.all(16.0),
        child: ShimmerLoadingList(count: 3, height: 110),
      );
    }

    final data = provider.tabunganUstadz;
    if (data == null || !data.hasTabungan || data.rekening == null) {
      return RefreshIndicator(
        onRefresh: () => provider.fetchTabunganUstadz(),
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(24),
          children: [
            const SizedBox(height: 40),
            Center(
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1F291F)
                          : const Color(0xFFF1F5F9),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      Icons.account_balance_wallet_outlined,
                      size: 48,
                      color: isDark ? Colors.white38 : Colors.black38,
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Belum Memiliki Rekening Tabungan',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    data?.message ??
                        'Anda belum memiliki buku tabungan ustadz aktif di madrasah. Silakan hubungi bendahara/admin untuk membuka rekening.',
                    textAlign: TextAlign.center,
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
      );
    }

    final rekening = data.rekening!;

    return RefreshIndicator(
      onRefresh: () =>
          provider.fetchTabunganUstadz(tabunganId: provider.selectedTabunganId),
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 10, 16, 80),
        children: [
          // Account Switcher (jika ustadz memiliki lebih dari 1 rekening)
          _buildAccountSwitcher(provider, data, isDark),

          // Passbook Header Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: isDark
                    ? const [Color(0xFF241538), Color(0xFF140B20)]
                    : const [Color(0xFF6D28D9), Color(0xFF4C1D95)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(24),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: isDark ? 0.35 : 0.1),
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
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Text(
                        'TABUNGAN ASATIDZ',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                    Text(
                      rekening.status,
                      style: const TextStyle(
                        color: Color(0xFF86EFAC),
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                const Text(
                  'SALDO TABUNGAN ANDA',
                  style: TextStyle(
                    color: Colors.white70,
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    letterSpacing: 0.8,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Rp ${DateHelper.formatRupiah(rekening.saldo)}',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 26,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 12),
                const Divider(color: Colors.white24, height: 1),
                const SizedBox(height: 10),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'No. Rekening / Barcode',
                          style: TextStyle(color: Colors.white60, fontSize: 10),
                        ),
                        Text(
                          rekening.nomorRekening,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            fontFamily: 'monospace',
                          ),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        const Text(
                          'Dapat Ditarik',
                          style: TextStyle(color: Colors.white60, fontSize: 10),
                        ),
                        Text(
                          'Rp ${DateHelper.formatRupiah(rekening.saldoDapatDitarik)}',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // 2-Box Summary (Setor & Tarik)
          Row(
            children: [
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isDark
                        ? const Color(0xFF0F2313)
                        : const Color(0xFFE8F5E9),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        Icons.arrow_downward_rounded,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Total Setoran',
                              style: TextStyle(
                                fontSize: 10,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                            Text(
                              'Rp ${DateHelper.formatRupiah(rekening.totalSetor)}',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w900,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isDark
                        ? const Color(0xFF380C14)
                        : const Color(0xFFFFE4E6),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.arrow_upward_rounded,
                        color: AppColors.roseDanger,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Total Penarikan',
                              style: TextStyle(
                                fontSize: 10,
                                color: AppColors.roseDanger,
                              ),
                            ),
                            Text(
                              'Rp ${DateHelper.formatRupiah(rekening.totalTarik)}',
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w900,
                                color: AppColors.roseDanger,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),

          // Transaksi Terakhir
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Transaksi Terakhir (${data.transaksiTerbaru.length})',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                ),
              ),
              InkWell(
                onTap: () {
                  HapticHelper.light();
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => DetailTabunganScreen(
                        tabunganId: rekening.id,
                        initialNama: rekening.namaNasabah,
                      ),
                    ),
                  );
                },
                child: Text(
                  'Lihat Buku Lengkap',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.primaryLight,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),

          if (data.transaksiTerbaru.isEmpty)
            GlassCard(
              padding: const EdgeInsets.all(20),
              child: const Center(
                child: Text(
                  'Belum ada transaksi pada rekening Anda.',
                  style: TextStyle(fontSize: 12),
                ),
              ),
            )
          else
            ...data.transaksiTerbaru.map((trx) {
              final isSetor = trx.isSetor;
              return GlassCard(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.all(12),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: isSetor
                            ? (isDark
                                  ? const Color(0xFF0F2313)
                                  : const Color(0xFFE8F5E9))
                            : (isDark
                                  ? const Color(0xFF380C14)
                                  : const Color(0xFFFFE4E6)),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        isSetor
                            ? Icons.arrow_downward_rounded
                            : Icons.arrow_upward_rounded,
                        size: 18,
                        color: isSetor
                            ? (isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight)
                            : AppColors.roseDanger,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            trx.kategori,
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            trx.tanggal,
                            style: TextStyle(
                              fontSize: 10,
                              color: isDark ? Colors.white54 : Colors.black45,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          '${isSetor ? "+" : "-"}Rp ${DateHelper.formatRupiah(trx.nominal)}',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w900,
                            color: isSetor
                                ? (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                : AppColors.roseDanger,
                          ),
                        ),
                        Text(
                          'Saldo: Rp ${DateHelper.formatRupiah(trx.saldoAkhir)}',
                          style: TextStyle(
                            fontSize: 10,
                            color: isDark ? Colors.white54 : Colors.black45,
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
    );
  }

  // =========================================================================
  // ACCOUNT SWITCHER (Jika Ustadz memiliki lebih dari 1 rekening)
  // =========================================================================
  Widget _buildAccountSwitcher(
    TabunganProvider provider,
    TabunganUstadzResponse data,
    bool isDark,
  ) {
    if (data.daftarRekening.length <= 1) {
      return const SizedBox.shrink();
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.account_balance_rounded,
                size: 14,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
              const SizedBox(width: 6),
              Text(
                'Pilih Rekening (${data.daftarRekening.length} Rekening Aktif)',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white70 : Colors.black87,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: data.daftarRekening.map((rek) {
                final isSelected = rek.id == data.rekening?.id;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(14),
                    onTap: () {
                      if (!isSelected) {
                        HapticHelper.light();
                        provider.fetchTabunganUstadz(tabunganId: rek.id);
                      }
                    },
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 10,
                      ),
                      decoration: BoxDecoration(
                        color: isSelected
                            ? (isDark
                                  ? AppColors.primaryDark.withValues(
                                      alpha: 0.25,
                                    )
                                  : AppColors.primaryLight.withValues(
                                      alpha: 0.12,
                                    ))
                            : (isDark
                                  ? const Color(0xFF1E261E)
                                  : const Color(0xFFF1F5F9)),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isSelected
                              ? (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                              : (isDark ? Colors.white12 : Colors.black12),
                          width: isSelected ? 1.5 : 1.0,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            isSelected
                                ? Icons.check_circle_rounded
                                : Icons.radio_button_unchecked_rounded,
                            size: 16,
                            color: isSelected
                                ? (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                : (isDark ? Colors.white38 : Colors.black38),
                          ),
                          const SizedBox(width: 8),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                rek.namaRekening.isNotEmpty &&
                                        rek.namaRekening != 'Tabungan'
                                    ? rek.namaRekening
                                    : 'Rekening ${rek.nomorRekening}',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: isSelected
                                      ? FontWeight.bold
                                      : FontWeight.w600,
                                  color: isSelected
                                      ? (isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight)
                                      : (isDark
                                            ? Colors.white70
                                            : Colors.black87),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${rek.periode} • Rp ${DateHelper.formatRupiah(rek.saldo)}',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: isSelected
                                      ? FontWeight.bold
                                      : FontWeight.normal,
                                  color: isDark
                                      ? Colors.white54
                                      : Colors.black54,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }
}
