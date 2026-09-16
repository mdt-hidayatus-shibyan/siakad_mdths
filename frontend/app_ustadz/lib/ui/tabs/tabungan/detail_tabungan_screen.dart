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
import 'form_setor_tabungan_sheet.dart';

class DetailTabunganScreen extends StatefulWidget {
  final int tabunganId;
  final String? initialNama;

  const DetailTabunganScreen({
    super.key,
    required this.tabunganId,
    this.initialNama,
  });

  @override
  State<DetailTabunganScreen> createState() => _DetailTabunganScreenState();
}

class _DetailTabunganScreenState extends State<DetailTabunganScreen> {
  String? _selectedBulan;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<TabunganProvider>().fetchDetailTabungan(
        widget.tabunganId,
        bulan: _selectedBulan,
      );
    });
  }

  void _openSetorSheet(TabunganRekeningModel rekening) {
    HapticHelper.medium();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => FormSetorTabunganSheet(
        tabunganId: rekening.id,
        namaNasabah: rekening.namaNasabah,
        nomorRekening: rekening.nomorRekening,
        saldoSaatIni: rekening.saldo,
      ),
    ).then((val) {
      if (mounted && val == true) {
        context.read<TabunganProvider>().fetchDetailTabungan(
          widget.tabunganId,
          bulan: _selectedBulan,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<TabunganProvider>();
    final data = provider.detailTabungan;
    final rekening = data?.rekening;

    return Scaffold(
      appBar: CustomAppBar(
        titleText:
            rekening?.namaNasabah ?? widget.initialNama ?? 'Detail Tabungan',
        actions: [
          CircularIconButton(
            icon: Icons.refresh_rounded,
            tooltip: 'Segarkan Data',
            iconColor: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            onPressed: () {
              HapticHelper.light();
              provider.fetchDetailTabungan(
                widget.tabunganId,
                bulan: _selectedBulan,
              );
            },
          ),
        ],
      ),
      floatingActionButton: rekening != null
          ? FloatingActionButton.extended(
              onPressed: () => _openSetorSheet(rekening),
              backgroundColor: isDark
                  ? AppColors.primaryDark
                  : AppColors.primaryLight,
              foregroundColor: isDark
                  ? AppColors.onPrimaryDark
                  : AppColors.onPrimaryLight,
              icon: const Icon(Icons.add_card_rounded),
              label: const Text(
                'Setor Tunai',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
            )
          : null,
      body: provider.isLoading && data == null
          ? const Padding(
              padding: EdgeInsets.all(16.0),
              child: ShimmerLoadingList(count: 4, height: 100),
            )
          : data == null
          ? Center(
              child: Text(
                provider.errorMessage ?? 'Gagal memuat detail tabungan',
              ),
            )
          : RefreshIndicator(
              onRefresh: () => provider.fetchDetailTabungan(
                widget.tabunganId,
                bulan: _selectedBulan,
              ),
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 90),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Card Passbook Header
                    _buildPassbookHeader(rekening!, isDark),
                    const SizedBox(height: 16),

                    // Summary Statistics 3-Box
                    Row(
                      children: [
                        Expanded(
                          child: _buildSummaryBox(
                            'Total Setor',
                            ' ${DateHelper.formatRupiah(rekening.totalSetor)}',
                            isDark
                                ? const Color(0xFF0F2313)
                                : const Color(0xFFE8F5E9),
                            isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                            Icons.arrow_downward_rounded,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _buildSummaryBox(
                            'Total Tarik',
                            ' ${DateHelper.formatRupiah(rekening.totalTarik)}',
                            isDark
                                ? const Color(0xFF380C14)
                                : const Color(0xFFFFE4E6),
                            AppColors.roseDanger,
                            Icons.arrow_upward_rounded,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _buildSummaryBox(
                            'Dapat Ditarik',
                            ' ${DateHelper.formatRupiah(rekening.saldoDapatDitarik)}',
                            isDark
                                ? const Color(0xFF0C243B)
                                : const Color(0xFFE0F2FE),
                            isDark
                                ? AppColors.skyBlueAccent
                                : const Color(0xFF0284C7),
                            Icons.lock_open_rounded,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),

                    // Filter Rekap Bulanan Pills
                    if (data.rekapBulanan.isNotEmpty) ...[
                      const Text(
                        'Rekap Mutasi Per Bulan',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 8),
                      SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: [
                            _buildMonthFilterChip('Semua Bulan', null, isDark),
                            ...data.rekapBulanan.map(
                              (m) =>
                                  _buildMonthFilterChip(m.label, m.key, isDark),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 18),
                    ],

                    // Riwayat Transaksi List Header
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Riwayat Transaksi (${data.riwayat.length})',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Text(
                          'Kronologis',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark ? Colors.white54 : Colors.black45,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),

                    if (data.riwayat.isEmpty)
                      GlassCard(
                        padding: const EdgeInsets.all(24),
                        child: Center(
                          child: Column(
                            children: [
                              Icon(
                                Icons.receipt_long_outlined,
                                size: 40,
                                color: isDark ? Colors.white24 : Colors.black26,
                              ),
                              const SizedBox(height: 8),
                              const Text(
                                'Belum ada transaksi pada periode ini.',
                                style: TextStyle(fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                      )
                    else
                      ...data.riwayat.map(
                        (trx) => _buildTransactionCard(trx, isDark),
                      ),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildPassbookHeader(TabunganRekeningModel rekening, bool isDark) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: isDark
              ? const [Color(0xFF1B381E), Color(0xFF0F2313)]
              : [AppColors.primaryLight, const Color(0xFF1B6A35)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.4 : 0.12),
            blurRadius: 18,
            offset: const Offset(0, 6),
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
                child: Text(
                  rekening.jenisNasabah,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: rekening.status.toLowerCase() == 'aktif'
                      ? const Color(0xFF22C55E).withValues(alpha: 0.3)
                      : Colors.orange.withValues(alpha: 0.3),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  rekening.status,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Text(
            'SALDO TABUNGAN SAAT INI',
            style: TextStyle(
              color: Colors.white70,
              fontSize: 11,
              fontWeight: FontWeight.w600,
              letterSpacing: 0.8,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            ' ${DateHelper.formatRupiah(rekening.saldo)}',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 26,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 14),
          const Divider(color: Colors.white24, height: 1),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'No. Barcode / Rekening',
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
                    'Potongan Madrasah',
                    style: TextStyle(color: Colors.white60, fontSize: 10),
                  ),
                  Text(
                    '${rekening.persentasePotongan}% ( ${DateHelper.formatRupiah(rekening.totalPotongan)})',
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
    );
  }

  Widget _buildSummaryBox(
    String title,
    String value,
    Color bg,
    Color color,
    IconData icon,
  ) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: [
          Icon(icon, size: 18, color: color),
          const SizedBox(height: 4),
          Text(
            title,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w900,
              color: color,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildMonthFilterChip(String label, String? key, bool isDark) {
    final isSelected = _selectedBulan == key;

    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: InkWell(
        onTap: () {
          HapticHelper.selection();
          setState(() {
            _selectedBulan = key;
          });
          context.read<TabunganProvider>().fetchDetailTabungan(
            widget.tabunganId,
            bulan: key,
          );
        },
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
          decoration: BoxDecoration(
            color: isSelected
                ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                : (isDark ? const Color(0xFF1F291F) : const Color(0xFFF1F5F9)),
            borderRadius: BorderRadius.circular(14),
          ),
          child: Text(
            label,
            style: TextStyle(
              fontSize: 11,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
              color: isSelected
                  ? Colors.white
                  : (isDark ? Colors.white70 : Colors.black87),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildTransactionCard(TransaksiTabunganItem trx, bool isDark) {
    final isSetor = trx.isSetor;

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: isSetor
                  ? (isDark ? const Color(0xFF0F2313) : const Color(0xFFE8F5E9))
                  : (isDark
                        ? const Color(0xFF380C14)
                        : const Color(0xFFFFE4E6)),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              isSetor
                  ? Icons.arrow_downward_rounded
                  : Icons.arrow_upward_rounded,
              size: 20,
              color: isSetor
                  ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                  : AppColors.roseDanger,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      trx.kategori,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      '${isSetor ? "+" : "-"} ${DateHelper.formatRupiah(trx.nominal)}',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w900,
                        color: isSetor
                            ? (isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight)
                            : AppColors.roseDanger,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  '${trx.tanggal} • ${trx.kodeTransaksi}',
                  style: TextStyle(
                    fontSize: 10,
                    color: isDark ? Colors.white54 : Colors.black45,
                  ),
                ),
                if (trx.keterangan.isNotEmpty && trx.keterangan != '-') ...[
                  const SizedBox(height: 4),
                  Text(
                    trx.keterangan,
                    style: TextStyle(
                      fontSize: 11,
                      color: isDark ? Colors.white70 : Colors.black87,
                    ),
                  ),
                ],
                const SizedBox(height: 4),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Saldo:  ${DateHelper.formatRupiah(trx.saldoAkhir)}',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: isDark ? Colors.white60 : Colors.black54,
                      ),
                    ),
                    Text(
                      'Petugas: ${trx.petugas}',
                      style: TextStyle(
                        fontSize: 9,
                        color: isDark ? Colors.white38 : Colors.black38,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
