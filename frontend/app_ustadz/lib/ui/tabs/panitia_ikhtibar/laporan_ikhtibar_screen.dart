import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/panitia_ikhtibar_model.dart';
import '../../../providers/panitia_ikhtibar_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/kwitansi_pembayaran_screen.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';

class LaporanIkhtibarScreen extends StatefulWidget {
  const LaporanIkhtibarScreen({super.key});

  @override
  State<LaporanIkhtibarScreen> createState() => _LaporanIkhtibarScreenState();
}

class _LaporanIkhtibarScreenState extends State<LaporanIkhtibarScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        setState(() {});
      }
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _loadData() {
    final provider = context.read<PanitiaIkhtibarProvider>();
    provider.fetchLaporanRingkasan();
    provider.fetchLaporanRiwayat();
  }

  void _shiftDate(int days) {
    HapticHelper.selection();
    final provider = context.read<PanitiaIkhtibarProvider>();
    final current = provider.filterLaporanTanggal;
    provider.setFilterLaporanTanggal(current.add(Duration(days: days)));
  }

  Future<void> _pickDate(BuildContext context) async {
    final provider = context.read<PanitiaIkhtibarProvider>();
    final picked = await showDatePicker(
      context: context,
      initialDate: provider.filterLaporanTanggal,
      firstDate: DateTime(2020),
      lastDate: DateTime(2035),
    );
    if (picked != null) {
      HapticHelper.light();
      provider.setFilterLaporanTanggal(picked);
    }
  }

  String _formatRupiah(num number) {
    final formatter = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );
    return formatter.format(number);
  }

  void _bukaHalamanKwitansiRiwayat(RiwayatPembayaranIkhtibarItem r) {
    HapticHelper.light();
    final provider = context.read<PanitiaIkhtibarProvider>();
    final ikhtibarKe = provider.kasirRingkasan?.ikhtibarKe;
    final judulKegiatan = (ikhtibarKe != null && ikhtibarKe.isNotEmpty)
        ? 'Ikhtibar $ikhtibarKe'
        : 'Ikhtibar';
    final tglBayar = r.tanggal != null ? DateTime.tryParse(r.tanggal!) : null;

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => KwitansiPembayaranScreen(
          judulKegiatan: judulKegiatan,
          subJudul: 'Riwayat Setoran Panitia Ikhtibar',
          noTransaksi: r.noTransaksi,
          status: r.tipePembayaran,
          nominalBayar: r.nominal,
          namaPembayar: r.namaPenyetor,
          kampung: r.kampung,
          metodePembayaran: r.metode,
          nomorTabungan: r.nomorTabungan,
          namaNasabah: r.namaNasabah,
          ruanganNasabah: r.ruanganNasabah,
          tanggalBayar: tglBayar,
          kasir: r.diterimaOleh,
          kwitansiUrl: r.kwitansiUrl ?? r.kwitansiTagihanUrl,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final provider = context.watch<PanitiaIkhtibarProvider>();
    final ringkasan = provider.laporanRingkasan;

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Laporan Pembayaran Ikhtibar',
        showBackButton: true,
      ),
      body: RefreshIndicator(
        onRefresh: () async => _loadData(),
        child: NestedScrollView(
          headerSliverBuilder: (context, innerBoxIsScrolled) {
            return [
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                  child: Column(
                    children: [
                      // Ringkasan Keuangan Card
                      if (ringkasan != null) ...[
                        GlassCard(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(8),
                                        decoration: BoxDecoration(
                                          color:
                                              (isDark
                                                      ? AppColors.primaryDark
                                                      : AppColors.primaryLight)
                                                  .withValues(alpha: 0.15),
                                          shape: BoxShape.circle,
                                        ),
                                        child: Icon(
                                          Icons.assessment_rounded,
                                          color: isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight,
                                          size: 20,
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      const Text(
                                        'Rekapitulasi Iuran',
                                        style: TextStyle(
                                          fontSize: 14,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ],
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 10,
                                      vertical: 4,
                                    ),
                                    decoration: BoxDecoration(
                                      color:
                                          (isDark
                                                  ? AppColors.primaryDark
                                                  : AppColors.primaryLight)
                                              .withValues(alpha: 0.12),
                                      borderRadius: BorderRadius.circular(20),
                                    ),
                                    child: Text(
                                      '${ringkasan.persentase}% Lunas',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 14),

                              ClipRRect(
                                borderRadius: BorderRadius.circular(6),
                                child: LinearProgressIndicator(
                                  value: (ringkasan.persentase / 100).clamp(
                                    0.0,
                                    1.0,
                                  ),
                                  minHeight: 8,
                                  backgroundColor: isDark
                                      ? Colors.white12
                                      : Colors.black12,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                              const SizedBox(height: 14),

                              Row(
                                children: [
                                  Expanded(
                                    child: _buildMetricItem(
                                      'Target Tagihan',
                                      _formatRupiah(ringkasan.targetTotal),
                                      isDark,
                                    ),
                                  ),
                                  Expanded(
                                    child: _buildMetricItem(
                                      'Kas Masuk',
                                      _formatRupiah(ringkasan.masukTotal),
                                      isDark,
                                      color: isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight,
                                    ),
                                  ),
                                  Expanded(
                                    child: _buildMetricItem(
                                      'Sisa Tagihan',
                                      _formatRupiah(ringkasan.sisaTotal),
                                      isDark,
                                      color: AppColors.roseDanger,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 14),
                      ],

                      // Tab Navigation Bar (SegmentedTabBar konsisten M3)
                      SegmentedTabBar(
                        margin: const EdgeInsets.only(top: 4, bottom: 2),
                        selectedIndex: _tabController.index,
                        onTabChanged: (idx) {
                          _tabController.animateTo(idx);
                          setState(() {});
                        },
                        items: [
                          SegmentedTabItem(
                            activeIcon: Icons.map_rounded,
                            inactiveIcon: Icons.map_outlined,
                            label: 'Dusun',
                            activeColor: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          SegmentedTabItem(
                            activeIcon: Icons.badge_rounded,
                            inactiveIcon: Icons.badge_outlined,
                            label: 'Pengurus',
                            activeColor: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          SegmentedTabItem(
                            activeIcon: Icons.receipt_long_rounded,
                            inactiveIcon: Icons.receipt_long_outlined,
                            label: 'Riwayat',
                            activeColor: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ];
          },
          body: TabBarView(
            controller: _tabController,
            children: [
              // TAB 1: Rekap Per Kampung
              _buildTabLegerKampung(provider, isDark),

              // TAB 2: Kasir BPH & Tabungan
              _buildTabKasirBph(provider, isDark),

              // TAB 3: Riwayat Transaksi
              _buildTabRiwayat(provider, isDark),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMetricItem(
    String label,
    String value,
    bool isDark, {
    Color? color,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 10,
            color: isDark ? Colors.white60 : Colors.black54,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // TAB 1: LEGER KAMPUNG
  // =========================================================================
  Widget _buildTabLegerKampung(PanitiaIkhtibarProvider provider, bool isDark) {
    if (provider.isLoadingLaporan && provider.laporanRingkasan == null) {
      return const Padding(
        padding: EdgeInsets.all(16),
        child: ShimmerLoadingList(count: 4),
      );
    }

    final rekapList = provider.laporanRingkasan?.rekapKampung ?? [];
    if (rekapList.isEmpty) {
      return const Center(child: Text('Belum ada data rekap per kampung.'));
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: rekapList.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (ctx, idx) {
        final k = rekapList[idx];
        return GlassCard(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: isDark ? Colors.white12 : Colors.black12,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          k.kode,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 11,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        k.namaKampung,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  Text(
                    '${k.lunasCount} / ${k.totalKk} KK Lunas',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: k.persentase >= 100
                          ? (isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight)
                          : (isDark ? Colors.white70 : Colors.black87),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),

              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: LinearProgressIndicator(
                  value: (k.persentase / 100).clamp(0.0, 1.0),
                  minHeight: 6,
                  backgroundColor: isDark ? Colors.white12 : Colors.black12,
                  color: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                ),
              ),
              const SizedBox(height: 10),

              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Target: ${_formatRupiah(k.target)}',
                    style: const TextStyle(fontSize: 11),
                  ),
                  Text(
                    'Masuk: ${_formatRupiah(k.masuk)}',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                  ),
                  Text(
                    'Sisa: ${_formatRupiah(k.sisa)}',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: k.sisa > 0
                          ? AppColors.roseDanger
                          : (isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  // =========================================================================
  // TAB 2: KASIR BPH & POTONGAN TABUNGAN
  // =========================================================================
  Widget _buildTabKasirBph(PanitiaIkhtibarProvider provider, bool isDark) {
    if (provider.isLoadingLaporan && provider.laporanRingkasan == null) {
      return const Padding(
        padding: EdgeInsets.all(16),
        child: ShimmerLoadingList(count: 4),
      );
    }

    final ringkasan = provider.laporanRingkasan;
    final tabungan = ringkasan?.rekapTabungan;
    final bphList = ringkasan?.rekapBph ?? [];

    if (bphList.isEmpty && (tabungan == null || tabungan.totalNominal == 0)) {
      return const Center(child: Text('Belum ada data penerimaan kasir BPH.'));
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // 1. Rekap Potongan Tabungan
        if (tabungan != null) ...[
          GlassCard(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: Colors.amber.withValues(alpha: 0.15),
                            shape: BoxShape.circle,
                          ),
                          child: Icon(
                            Icons.account_balance_rounded,
                            size: 18,
                            color: Colors.amber.shade700,
                          ),
                        ),
                        const SizedBox(width: 10),
                        const Text(
                          'Rekap Potongan Tabungan',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.amber.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '${tabungan.totalTransaksi} Transaksi',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: Colors.amber.shade800,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                Row(
                  children: [
                    Expanded(
                      child: _buildMetricItem(
                        'Total Terpotong',
                        _formatRupiah(tabungan.totalNominal),
                        isDark,
                        color: Colors.amber.shade700,
                      ),
                    ),
                    Expanded(
                      child: _buildMetricItem(
                        'Disetujui Admin',
                        _formatRupiah(tabungan.verifiedNominal),
                        isDark,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                    Expanded(
                      child: _buildMetricItem(
                        'Pending Verifikasi',
                        _formatRupiah(tabungan.pendingNominal),
                        isDark,
                        color: AppColors.roseDanger,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  'Catatan: Potongan tabungan dihitung masuk ke kas riil kepanitiaan setelah diverifikasi oleh admin madrasah di backend.',
                  style: TextStyle(
                    fontSize: 10,
                    fontStyle: FontStyle.italic,
                    color: isDark ? Colors.white54 : Colors.black45,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
        ],

        // 2. Daftar Penerimaan Kasir per Petugas BPH
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Penerimaan Kasir per Petugas BPH',
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
            ),
            Text(
              '${bphList.length} Petugas',
              style: TextStyle(
                fontSize: 12,
                color: isDark ? Colors.white60 : Colors.black54,
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),

        if (bphList.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 20),
            child: Center(
              child: Text('Belum ada transaksi pembayaran yang tercatat.'),
            ),
          )
        else
          ...bphList.map((b) {
            return Container(
              margin: const EdgeInsets.only(bottom: 10),
              child: GlassCard(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 18,
                          backgroundColor:
                              (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                  .withValues(alpha: 0.15),
                          child: Text(
                            b.nama.isNotEmpty ? b.nama[0].toUpperCase() : 'U',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                b.nama,
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${b.jabatan} • ${b.totalTransaksi} Transaksi',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isDark
                                      ? Colors.white60
                                      : Colors.black54,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              _formatRupiah(b.totalNominal),
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                            const Text(
                              'Total Diterima',
                              style: TextStyle(
                                fontSize: 10,
                                color: Colors.grey,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const Divider(height: 18),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Icon(
                              Icons.payments_outlined,
                              size: 14,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              'Tunai: ${_formatRupiah(b.nominalTunai)}',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                        Row(
                          children: [
                            Icon(
                              Icons.account_balance_outlined,
                              size: 14,
                              color: Colors.amber.shade700,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              'Tabungan: ${_formatRupiah(b.nominalTabungan)}',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w500,
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
          }),
      ],
    );
  }

  // =========================================================================
  // TAB 3: RIWAYAT TRANSAKSI
  // =========================================================================
  Widget _buildTabRiwayat(PanitiaIkhtibarProvider provider, bool isDark) {
    final now = DateTime.now();
    final isToday =
        provider.filterLaporanTanggal.year == now.year &&
        provider.filterLaporanTanggal.month == now.month &&
        provider.filterLaporanTanggal.day == now.day;

    return Column(
      children: [
        // 1. Filter Metode Horizontal
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 10, 16, 4),
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: ['Semua', 'Tunai', 'Potong Tabungan', 'Cicilan'].map((
                m,
              ) {
                final isSelected = provider.filterLaporanMetode == m;
                return Padding(
                  padding: const EdgeInsets.only(right: 6),
                  child: ChoiceChip(
                    selected: isSelected,
                    label: Text(m, style: const TextStyle(fontSize: 11)),
                    onSelected: (_) => provider.setFilterLaporanMetode(m),
                  ),
                );
              }).toList(),
            ),
          ),
        ),

        // 2. Filter Tanggal Bar Konsisten M3 (seperti presensi)
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
            decoration: BoxDecoration(
              color: isDark
                  ? Colors.white.withValues(alpha: 0.06)
                  : Colors.black.withValues(alpha: 0.04),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(
                color: isDark
                    ? Colors.white.withValues(alpha: 0.08)
                    : Colors.black.withValues(alpha: 0.06),
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                IconButton(
                  visualDensity: VisualDensity.compact,
                  icon: const Icon(Icons.chevron_left_rounded),
                  tooltip: 'Hari Sebelumnya',
                  onPressed: () => _shiftDate(-1),
                ),
                Expanded(
                  child: InkWell(
                    borderRadius: BorderRadius.circular(16),
                    onTap: () => _pickDate(context),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(vertical: 6),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.calendar_today_rounded,
                            size: 14,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 8),
                          Flexible(
                            child: Text(
                              isToday
                                  ? '${DateHelper.formatTanggalIndo(provider.filterLaporanTanggal)} (Hari Ini)'
                                  : DateHelper.formatTanggalIndo(
                                      provider.filterLaporanTanggal,
                                    ),
                              style: TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.bold,
                                color: isDark ? Colors.white : Colors.black87,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                if (!isToday)
                  IconButton(
                    visualDensity: VisualDensity.compact,
                    tooltip: 'Kembali ke Hari Ini',
                    icon: Icon(
                      Icons.today_rounded,
                      size: 18,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                    onPressed: () {
                      HapticHelper.light();
                      provider.setFilterLaporanTanggal(DateTime.now());
                    },
                  ),
                IconButton(
                  visualDensity: VisualDensity.compact,
                  icon: const Icon(Icons.chevron_right_rounded),
                  tooltip: 'Hari Berikutnya',
                  onPressed: () => _shiftDate(1),
                ),
              ],
            ),
          ),
        ),

        // List Riwayat
        Expanded(
          child: provider.isLoadingLaporan && provider.riwayatList.isEmpty
              ? const Padding(
                  padding: EdgeInsets.all(16),
                  child: ShimmerLoadingList(count: 4),
                )
              : provider.riwayatList.isEmpty
              ? const Center(child: Text('Belum ada transaksi pembayaran.'))
              : ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: provider.riwayatList.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 10),
                  itemBuilder: (ctx, idx) {
                    final r = provider.riwayatList[idx];
                    final isTabungan = r.metode == 'Potong Tabungan';
                    final badgeColor = r.isVerified
                        ? (isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight)
                        : Colors.amber;
                    String badgeText;
                    if (isTabungan) {
                      badgeText = r.isVerified
                          ? 'Potong Tabungan'
                          : 'Tabungan (Pending)';
                    } else {
                      badgeText = r.metode;
                    }

                    return GlassCard(
                      padding: const EdgeInsets.all(14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Text(
                                  r.namaPenyetor,
                                  style: const TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.bold,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              Text(
                                _formatRupiah(r.nominal),
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),

                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'Dusun: ${r.kampung} • ${r.tanggal ?? "-"}',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isDark
                                      ? Colors.white60
                                      : Colors.black54,
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: badgeColor.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  badgeText,
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: badgeColor,
                                  ),
                                ),
                              ),
                            ],
                          ),

                          if (isTabungan && r.nomorTabungan != null) ...[
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? Colors.white.withValues(alpha: 0.04)
                                    : Colors.black.withValues(alpha: 0.03),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Row(
                                children: [
                                  const Icon(
                                    Icons.account_balance_wallet_outlined,
                                    size: 13,
                                    color: Colors.amber,
                                  ),
                                  const SizedBox(width: 6),
                                  Expanded(
                                    child: Text(
                                      '${r.nomorTabungan} • ${r.namaNasabah ?? r.namaPenyetor} (${r.ruanganNasabah ?? "-"})',
                                      style: const TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.w500,
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                  if (!r.isVerified)
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 6,
                                        vertical: 2,
                                      ),
                                      decoration: BoxDecoration(
                                        color: Colors.amber.withValues(
                                          alpha: 0.15,
                                        ),
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: const Text(
                                        'Menunggu Admin',
                                        style: TextStyle(
                                          fontSize: 9,
                                          fontWeight: FontWeight.bold,
                                          color: Colors.amber,
                                        ),
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          ],

                          const SizedBox(height: 4),

                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'Diterima: ${r.diterimaOleh}',
                                style: TextStyle(
                                  fontSize: 10,
                                  color: isDark
                                      ? Colors.white38
                                      : Colors.black38,
                                ),
                              ),
                              if (r.kwitansiUrl != null &&
                                  r.kwitansiUrl!.isNotEmpty)
                                InkWell(
                                  onTap: () => _bukaHalamanKwitansiRiwayat(r),
                                  borderRadius: BorderRadius.circular(6),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 8,
                                      vertical: 3,
                                    ),
                                    decoration: BoxDecoration(
                                      color:
                                          (isDark
                                                  ? AppColors.primaryDark
                                                  : AppColors.primaryLight)
                                              .withValues(alpha: 0.12),
                                      borderRadius: BorderRadius.circular(6),
                                      border: Border.all(
                                        color:
                                            (isDark
                                                    ? AppColors.primaryDark
                                                    : AppColors.primaryLight)
                                                .withValues(alpha: 0.3),
                                      ),
                                    ),
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(
                                          Icons.receipt_long_rounded,
                                          size: 12,
                                          color: isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight,
                                        ),
                                        const SizedBox(width: 4),
                                        Text(
                                          'Kwitansi',
                                          style: TextStyle(
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                            color: isDark
                                                ? AppColors.primaryDark
                                                : AppColors.primaryLight,
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
                  },
                ),
        ),
      ],
    );
  }
}
