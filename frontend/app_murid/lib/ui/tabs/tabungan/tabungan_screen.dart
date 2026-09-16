import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/tabungan_model.dart';
import '../../../providers/dashboard_provider.dart';
import '../../../providers/keuangan_provider.dart';
import '../../widgets/child_switcher_bar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/modern_header.dart';
import '../keuangan/detail_komplain_sheet.dart';
import '../keuangan/form_komplain_setoran_sheet.dart';

class TabunganScreen extends StatefulWidget {
  final bool isFullScreen;

  const TabunganScreen({super.key, this.isFullScreen = true});

  @override
  State<TabunganScreen> createState() => _TabunganScreenState();
}

class _TabunganScreenState extends State<TabunganScreen> {
  String? _selectedTabunganBulan;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final dashboard = context.read<DashboardProvider>();
      final selectedAnak = dashboard.selectedAnak;
      if (selectedAnak != null) {
        context.read<KeuanganProvider>().fetchTabungan(
          selectedAnak.id,
          force: true,
        );
      }
      if (dashboard.anakList.length >= 2) {
        context.read<KeuanganProvider>().fetchAllChildrenTabungan(
          dashboard.anakList.map((a) => a.id).toList(),
          force: true,
        );
      }
    });
  }

  Future<void> _onRefresh() async {
    HapticHelper.light();
    final dashboard = context.read<DashboardProvider>();
    final keuangan = context.read<KeuanganProvider>();
    final selectedAnak = dashboard.selectedAnak;

    if (selectedAnak != null) {
      if (dashboard.anakList.length >= 2) {
        await Future.wait([
          keuangan.fetchTabungan(
            selectedAnak.id,
            bulan: _selectedTabunganBulan,
            force: true,
          ),
          keuangan.fetchAllChildrenTabungan(
            dashboard.anakList.map((a) => a.id).toList(),
            force: true,
          ),
        ]);
      } else {
        await keuangan.fetchTabungan(
          selectedAnak.id,
          bulan: _selectedTabunganBulan,
          force: true,
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final dashboard = context.watch<DashboardProvider>();
    final selectedAnak = dashboard.selectedAnak;
    final keuangan = context.watch<KeuanganProvider>();
    final data = selectedAnak != null
        ? keuangan.getTabunganFor(selectedAnak.id)
        : null;

    if (selectedAnak == null) {
      return Scaffold(
        backgroundColor: isDark
            ? AppColors.surfaceDark
            : AppColors.surfaceLight,
        appBar: widget.isFullScreen
            ? const CustomAppBar(
                titleText: 'Tabungan Murid',
                subtitleText: 'Buku Tabungan & Mutasi Simpanan',
              )
            : null,
        body: const EmptyStateWidget(
          icon: Icons.child_care_rounded,
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
                      title: 'Tabungan Murid',
                      subtitle:
                          'Buku Tabungan, Mutasi Transaksi & Sanggahan Setoran',
                      icon: Icons.account_balance_wallet_rounded,
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
                child: keuangan.isLoadingTabungan && data == null
                    ? Center(
                        child: CircularProgressIndicator(
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
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
                          // Tampilan Buku Tabungan Anak Terpilih
                          if (data == null ||
                              !data.hasTabungan ||
                              data.rekening == null) ...[
                            GlassCard(
                              padding: const EdgeInsets.all(24),
                              borderRadius: 22,
                              child: Center(
                                child: Column(
                                  children: [
                                    Icon(
                                      Icons.account_balance_wallet_outlined,
                                      size: 44,
                                      color: isDark
                                          ? Colors.white38
                                          : Colors.black38,
                                    ),
                                    const SizedBox(height: 12),
                                    Text(
                                      'Buku Tabungan ${selectedAnak.namaLengkap} Belum Terdaftar',
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(
                                        fontSize: 14,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      'Murid belum memiliki rekening buku tabungan terdaftar di madrasah. Pendaftaran buku tabungan dilakukan langsung oleh bagian keuangan/admin madrasah.',
                                      textAlign: TextAlign.center,
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
                            ),
                          ] else ...[
                            // Account Switcher (Jika santri memiliki lebih dari 1 rekening tabungan)
                            _buildAccountSwitcher(
                              keuangan,
                              data,
                              selectedAnak.id,
                              isDark,
                            ),

                            // Passbook Card Header
                            _buildPassbookHeaderCard(
                              data.rekening!,
                              selectedAnak.namaLengkap,
                              isDark,
                            ),
                            const SizedBox(height: 12),

                            // 3 Summary Columns (Total Setor, Tarik, Potongan)
                            GlassCard(
                              padding: const EdgeInsets.all(14),
                              borderRadius: 20,
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceAround,
                                children: [
                                  _buildSummaryColumn(
                                    'Total Setoran',
                                    CurrencyFormatter.format(
                                      data.rekening!.totalSetor,
                                    ),
                                    const Color(0xFF10B981),
                                    isDark,
                                  ),
                                  Container(
                                    height: 30,
                                    width: 1,
                                    color: isDark
                                        ? Colors.white12
                                        : Colors.black12,
                                  ),
                                  _buildSummaryColumn(
                                    'Total Penarikan',
                                    CurrencyFormatter.format(
                                      data.rekening!.totalTarik,
                                    ),
                                    AppColors.roseDanger,
                                    isDark,
                                  ),
                                  Container(
                                    height: 30,
                                    width: 1,
                                    color: isDark
                                        ? Colors.white12
                                        : Colors.black12,
                                  ),
                                  _buildSummaryColumn(
                                    'Alokasi Potongan (${data.rekening!.persentasePotongan}%)',
                                    CurrencyFormatter.format(
                                      data.rekening!.totalPotongan,
                                    ),
                                    isDark ? Colors.white70 : Colors.black87,
                                    isDark,
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Rekap Bulanan Filter
                            if (data.rekapBulanan.isNotEmpty) ...[
                              Text(
                                'REKAP MUTASI PER BULAN',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 0.8,
                                  color: isDark
                                      ? Colors.white60
                                      : Colors.black54,
                                ),
                              ),
                              const SizedBox(height: 8),
                              SingleChildScrollView(
                                scrollDirection: Axis.horizontal,
                                child: Row(
                                  children: [
                                    _buildMonthFilterPill(
                                      'Semua',
                                      null,
                                      keuangan,
                                      selectedAnak.id,
                                      isDark,
                                    ),
                                    ...data.rekapBulanan.map(
                                      (m) => _buildMonthFilterPill(
                                        m.label,
                                        m.key,
                                        keuangan,
                                        selectedAnak.id,
                                        isDark,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 16),
                            ],

                            // Riwayat Transaksi List
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'RIWAYAT TRANSAKSI (${data.riwayat.length})',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: 0.8,
                                    color: isDark
                                        ? Colors.white60
                                        : Colors.black54,
                                  ),
                                ),
                                Text(
                                  'Kronologis',
                                  style: TextStyle(
                                    fontSize: 10,
                                    color: isDark
                                        ? Colors.white54
                                        : Colors.black45,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),

                            if (data.riwayat.isEmpty)
                              const GlassCard(
                                padding: EdgeInsets.all(24),
                                child: Center(
                                  child: Text(
                                    'Belum ada transaksi tabungan pada periode ini.',
                                    style: TextStyle(fontSize: 12),
                                  ),
                                ),
                              )
                            else
                              ...data.riwayat.map(
                                (trx) => _buildTransaksiTabunganCard(
                                  trx,
                                  selectedAnak.id,
                                  selectedAnak.namaLengkap,
                                  isDark,
                                ),
                              ),
                          ],
                        ],
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPassbookHeaderCard(
    TabunganRekeningAnak rekening,
    String? namaAnak,
    bool isDark,
  ) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: isDark
              ? const [Color(0xFF162E1A), Color(0xFF0C1D0E)]
              : [AppColors.primaryLight, const Color(0xFF166534)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.35 : 0.1),
            blurRadius: 18,
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
                child: Text(
                  namaAnak != null
                      ? 'TABUNGAN ${namaAnak.toUpperCase()}'
                      : 'BUKU TABUNGAN MURID',
                  style: const TextStyle(
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
            'SALDO TABUNGAN SAAT INI',
            style: TextStyle(
              color: Colors.white70,
              fontSize: 10,
              fontWeight: FontWeight.bold,
              letterSpacing: 0.8,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            CurrencyFormatter.format(rekening.saldo),
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
                    'No. Barcode Rekening',
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
                    'Saldo Bersih Dapat Ditarik',
                    style: TextStyle(color: Colors.white60, fontSize: 10),
                  ),
                  Text(
                    CurrencyFormatter.format(rekening.saldoDapatDitarik),
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

  Widget _buildMonthFilterPill(
    String label,
    String? key,
    KeuanganProvider keuangan,
    int anakId,
    bool isDark,
  ) {
    final isSelected = _selectedTabunganBulan == key;

    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: InkWell(
        onTap: () {
          HapticHelper.selection();
          setState(() {
            _selectedTabunganBulan = key;
          });
          keuangan.fetchTabungan(anakId, bulan: key, force: true);
        },
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
          decoration: BoxDecoration(
            color: isSelected
                ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                : (isDark ? const Color(0xFF1E281E) : const Color(0xFFF1F5F9)),
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

  Widget _buildTransaksiTabunganCard(
    TransaksiTabunganAnak trx,
    int anakId,
    String namaAnak,
    bool isDark,
  ) {
    final isSetor = trx.isSetor;
    final komplain = trx.komplain;

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: GlassCard(
        padding: const EdgeInsets.all(14),
        borderRadius: 18,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: isSetor
                        ? const Color(0xFF10B981).withValues(alpha: 0.15)
                        : AppColors.roseDanger.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    isSetor
                        ? Icons.arrow_downward_rounded
                        : Icons.arrow_upward_rounded,
                    size: 20,
                    color: isSetor
                        ? const Color(0xFF10B981)
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
                            '${isSetor ? "+" : "-"} ${CurrencyFormatter.format(trx.nominal)}',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w900,
                              color: isSetor
                                  ? const Color(0xFF10B981)
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
                      if (trx.keterangan.isNotEmpty &&
                          trx.keterangan != '-') ...[
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
                            'Saldo: ${CurrencyFormatter.format(trx.saldoAkhir)}',
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

            // Sanggahan / Komplain Section untuk Setor Tunai
            if (isSetor) ...[
              const SizedBox(height: 8),
              const Divider(height: 1),
              const SizedBox(height: 6),

              if (komplain != null) ...[
                // Banner Komplain
                InkWell(
                  onTap: () {
                    HapticHelper.light();
                    showModalBottomSheet(
                      context: context,
                      isScrollControlled: true,
                      backgroundColor: Colors.transparent,
                      builder: (_) => DetailKomplainSheet(
                        anakId: anakId,
                        namaAnak: namaAnak,
                        transaksi: trx,
                        komplain: komplain,
                      ),
                    );
                  },
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: komplain.isPending
                          ? AppColors.amberAccent.withValues(alpha: 0.12)
                          : (komplain.isDisetujui
                                ? const Color(
                                    0xFF10B981,
                                  ).withValues(alpha: 0.12)
                                : AppColors.roseDanger.withValues(alpha: 0.12)),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Icon(
                              komplain.isPending
                                  ? Icons.hourglass_top_rounded
                                  : (komplain.isDisetujui
                                        ? Icons.check_circle_rounded
                                        : Icons.cancel_rounded),
                              size: 13,
                              color: komplain.isPending
                                  ? const Color(0xFFD97706)
                                  : (komplain.isDisetujui
                                        ? const Color(0xFF10B981)
                                        : AppColors.roseDanger),
                            ),
                            const SizedBox(width: 6),
                            Text(
                              komplain.isPending
                                  ? 'Sanggahan: Klaim ${CurrencyFormatter.format(komplain.nominalKlaim)} (Menunggu)'
                                  : (komplain.isDisetujui
                                        ? 'Sanggahan Disetujui (${CurrencyFormatter.format(komplain.nominalKlaim)})'
                                        : 'Sanggahan Ditolak Pengelola'),
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: komplain.isPending
                                    ? const Color(0xFFD97706)
                                    : (komplain.isDisetujui
                                          ? const Color(0xFF10B981)
                                          : AppColors.roseDanger),
                              ),
                            ),
                          ],
                        ),
                        const Icon(
                          Icons.chevron_right_rounded,
                          size: 14,
                          color: Colors.grey,
                        ),
                      ],
                    ),
                  ),
                ),
              ] else ...[
                // Tombol Ajukan Sanggahan
                Align(
                  alignment: Alignment.centerRight,
                  child: InkWell(
                    onTap: () {
                      HapticHelper.light();
                      showModalBottomSheet(
                        context: context,
                        isScrollControlled: true,
                        backgroundColor: Colors.transparent,
                        builder: (_) => FormKomplainSetoranSheet(
                          anakId: anakId,
                          namaAnak: namaAnak,
                          transaksi: trx,
                        ),
                      );
                    },
                    borderRadius: BorderRadius.circular(8),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 6,
                        vertical: 3,
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.shield_outlined,
                            size: 12,
                            color: isDark
                                ? AppColors.amberAccent
                                : const Color(0xFFD97706),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            'Sanggah / Komplain Nominal',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: isDark
                                  ? AppColors.amberAccent
                                  : const Color(0xFFD97706),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ],
          ],
        ),
      ),
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

  // =========================================================================
  // ACCOUNT SWITCHER (Jika Anak memiliki lebih dari 1 rekening tabungan)
  // =========================================================================
  Widget _buildAccountSwitcher(
    KeuanganProvider provider,
    TabunganAnakData data,
    int anakId,
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
                'Pilih Buku Tabungan (${data.daftarRekening.length} Rekening Aktif)',
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
                        provider.fetchTabungan(
                          anakId,
                          tabunganId: rek.id,
                          bulan: _selectedTabunganBulan,
                          force: true,
                        );
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
                                '${rek.periode} • ${CurrencyFormatter.format(rek.saldo)}',
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
