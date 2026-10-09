import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/tagihan_model.dart';
import '../../../providers/tagihan_provider.dart';
import '../../widgets/app_avatar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';

class TagihanScreen extends StatefulWidget {
  final int? initialRuanganId;
  final int initialTabIndex;

  const TagihanScreen({
    super.key,
    this.initialRuanganId,
    this.initialTabIndex = 0,
  });

  @override
  State<TagihanScreen> createState() => _TagihanScreenState();
}

class _TagihanScreenState extends State<TagihanScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  int? _selectedRuanganId;

  // State Tab 1: SPP Bulanan
  int? _selectedBulanId;
  String _sppFilterStatus = 'Semua';
  String _sppSearchQuery = '';

  // State Tab 2: Tagihan Non-SPP
  int? _selectedMasterTagihanId;
  String _nonSppFilterStatus = 'Semua';
  String _nonSppSearchQuery = '';
  final Set<int> _selectedTagihanIds = {};

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: 2,
      vsync: this,
      initialIndex: widget.initialTabIndex,
    );
    _tabController.addListener(() {
      if (mounted) setState(() {});
    });

    _selectedRuanganId = widget.initialRuanganId;
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
    final provider = context.read<TagihanProvider>();
    provider.fetchSppRingkasan(ruanganId: _selectedRuanganId);
    provider.fetchNonSppRingkasan(
      ruanganId: _selectedRuanganId,
      pengaturanTagihanId: _selectedMasterTagihanId,
    );
  }

  String _formatRupiah(num number) {
    final str = number.toInt().toString();
    final buffer = StringBuffer();
    int count = 0;
    for (int i = str.length - 1; i >= 0; i--) {
      buffer.write(str[i]);
      count++;
      if (count % 3 == 0 && i != 0) {
        buffer.write('.');
      }
    }
    return 'Rp ${buffer.toString().split('').reversed.join('')}';
  }

  // =========================================================================
  // MODAL KARTU SPP MURID (READ ONLY)
  // =========================================================================
  void _openKartuSppSheet(MuridSppItem murid) {
    HapticHelper.light();
    context.read<TagihanProvider>().fetchKartuSppMurid(
      murid.muridId,
      ruanganId: _selectedRuanganId,
    );

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Consumer<TagihanProvider>(
        builder: (context, provider, _) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          final kartu = provider.kartuMurid;
          final systemBottom = MediaQuery.of(context).padding.bottom;

          return Container(
            decoration: BoxDecoration(
              color: isDark ? AppColors.surfaceContainerLowDark : AppColors.surfaceContainerLowLight,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(20, 12, 20, systemBottom + 24),
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(context).size.height * 0.85,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 36,
                    height: 4,
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF43483E)
                          : const Color(0xFFC3C8BC),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),

                // Header Kartu SPP
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: AppColors.primaryLight.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(
                        Icons.receipt_long_rounded,
                        color: AppColors.primaryLight,
                        size: 22,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Kartu Syahriyah / SPP',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            '${murid.nama} (NISM: ${murid.nism})',
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
                  ],
                ),
                const SizedBox(height: 14),

                // Ringkasan Progress & Tunggakan
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isDark
                        ? const Color(0xFF1B241C)
                        : const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Lunas: ${murid.bulanLunasCount} / ${murid.totalBulan} Bulan',
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Terbayar: ${_formatRupiah(murid.totalDibayar)}',
                              style: TextStyle(
                                fontSize: 11,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        height: 32,
                        width: 1,
                        color: isDark
                            ? const Color(0xFF334155)
                            : const Color(0xFFCBD5E1),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              murid.sisaTunggakan > 0
                                  ? 'Tunggakan'
                                  : 'Status SPP',
                              style: TextStyle(
                                fontSize: 11,
                                color: isDark
                                    ? const Color(0xFF8D9387)
                                    : const Color(0xFF64748B),
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              murid.sisaTunggakan > 0
                                  ? _formatRupiah(murid.sisaTunggakan)
                                  : (murid.bulanBebasCount > 0
                                        ? 'Bebas SPP ✨'
                                        : 'Lunas Semua ✨'),
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: murid.sisaTunggakan > 0
                                    ? AppColors.roseDanger
                                    : AppColors.hadirTextLight,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),

                const Text(
                  'Rincian 11 Bulan Hijriyah:',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),

                // List 11 Bulan Hijriyah
                Expanded(
                  child: provider.isLoadingKartu
                      ? const ShimmerLoadingList(count: 4, height: 60)
                      : kartu == null
                      ? const Padding(
                          padding: EdgeInsets.symmetric(vertical: 24),
                          child: EmptyStateView(
                            icon: Icons.credit_card_off_rounded,
                            title: 'Tagihan SPP Belum Diterbitkan',
                            description:
                                'Data kartu SPP untuk murid ini belum diterbitkan atau belum diatur oleh Bendahara.',
                          ),
                        )
                      : kartu.bulanItems.isEmpty
                      ? const Padding(
                          padding: EdgeInsets.symmetric(vertical: 24),
                          child: EmptyStateView(
                            icon: Icons.credit_card_off_rounded,
                            title: 'Bulan SPP Belum Diterbitkan',
                            description:
                                'Belum ada rincian bulan Syahriyah/SPP yang diterbitkan untuk murid ini.',
                          ),
                        )
                      : ListView.builder(
                          itemCount: kartu.bulanItems.length,
                          itemBuilder: (context, index) {
                            final b = kartu.bulanItems[index];
                            final isLunas = b.statusBayar == 'Lunas';
                            final isDonatur =
                                b.statusBayar == 'Ditanggung Donatur' ||
                                b.statusBayar == 'Bebas SPP' ||
                                b.statusBayar == 'Gratis';

                            return GlassCard(
                              margin: const EdgeInsets.only(bottom: 8),
                              padding: const EdgeInsets.all(12),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          '${index + 1}. ${b.namaBulan} ${b.tahunHijriyah}',
                                          style: const TextStyle(
                                            fontSize: 13,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          _formatRupiah(b.nominal),
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: isDark
                                                ? const Color(0xFF8D9387)
                                                : const Color(0xFF73796E),
                                          ),
                                        ),
                                        if (isLunas && b.noKwitansi != null)
                                          Padding(
                                            padding: const EdgeInsets.only(
                                              top: 2,
                                            ),
                                            child: Text(
                                              'Kwitansi: ${b.noKwitansi} (${b.hariTanggalBayar ?? b.tanggalBayar})',
                                              style: TextStyle(
                                                fontSize: 10,
                                                color: isDark
                                                    ? AppColors.primaryDark
                                                    : AppColors.primaryLight,
                                              ),
                                            ),
                                          ),
                                      ],
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 8,
                                      vertical: 4,
                                    ),
                                    decoration: BoxDecoration(
                                      color: isLunas
                                          ? (isDark
                                                ? AppColors.hadirBgDark
                                                : AppColors.hadirBgLight)
                                          : isDonatur
                                          ? (isDark
                                                ? const Color(0xFF241538)
                                                : const Color(0xFFF3E8FF))
                                          : (isDark
                                                ? const Color(0xFF380C14)
                                                : const Color(0xFFFFE4E6)),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      b.statusBayar,
                                      style: TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                        color: isLunas
                                            ? AppColors.hadirTextLight
                                            : isDonatur
                                            ? AppColors.violetAccent
                                            : AppColors.roseDanger,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                ),
                const SizedBox(height: 10),

                // Read Only Notice
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: isDark
                        ? const Color(0xFF1E281F)
                        : const Color(0xFFF0FDF4),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        Icons.visibility_outlined,
                        size: 16,
                        color: AppColors.primaryLight,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Mode Pemantauan: Pembayaran dan kwitansi SPP resmi diterbitkan oleh Bendahara Madrasah.',
                          style: TextStyle(
                            fontSize: 10.5,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF43483E),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // MODAL PEMBAYARAN NON-SPP (SINGLE / MULTI)
  // =========================================================================
  void _openBayarNonSppSheet({
    List<MuridNonSppItem>? muridList,
    MuridNonSppItem? singleMurid,
  }) {
    HapticHelper.light();
    final items = singleMurid != null
        ? [singleMurid]
        : (muridList ??
              context
                  .read<TagihanProvider>()
                  .nonSppMuridList
                  .where((m) => _selectedTagihanIds.contains(m.tagihanId))
                  .toList());

    if (items.isEmpty) return;

    final totalNominal = items.fold<num>(0, (sum, m) => sum + m.nominal);
    DateTime selectedDate = DateTime.now();
    String tipePembayar = 'Wali Murid';
    String metodeBayar = 'Tunai';
    final catatanCtrl = TextEditingController();
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          final bottomInset = MediaQuery.of(context).viewInsets.bottom;
          final systemBottom = MediaQuery.of(context).padding.bottom;
          final bottomPadding = bottomInset > 0 ? (bottomInset + 20) : (systemBottom + 24);

          return Container(
            decoration: BoxDecoration(
              color: isDark ? AppColors.surfaceContainerLowDark : AppColors.surfaceContainerLowLight,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              bottomPadding,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 36,
                      height: 4,
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF43483E)
                            : const Color(0xFFC3C8BC),
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Header
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppColors.primaryLight.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(
                          Icons.payments_rounded,
                          color: AppColors.primaryLight,
                          size: 22,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Bayar Tagihan Non-SPP',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              items.length == 1
                                  ? '${items.first.nama} (NISM: ${items.first.nism})'
                                  : 'Pembayaran Massal: ${items.length} Murid Terpilih',
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
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Total Tagihan Box
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1B241C)
                          : const Color(0xFFE8F5E9),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: isDark
                            ? AppColors.primaryDark.withValues(alpha: 0.3)
                            : const Color(0xFFA7F3D0),
                      ),
                    ),
                    child: Column(
                      children: [
                        Text(
                          'TOTAL PEMBAYARAN',
                          style: TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 0.5,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          _formatRupiah(totalNominal),
                          style: TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.w900,
                            color: isDark
                                ? Colors.white
                                : AppColors.primaryLight,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Tanggal Bayar Picker
                  InkWell(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: selectedDate,
                        firstDate: DateTime(2023),
                        lastDate: DateTime.now().add(const Duration(days: 30)),
                      );
                      if (picked != null) {
                        setModalState(() => selectedDate = picked);
                      }
                    },
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF1B241C)
                            : const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isDark
                              ? const Color(0xFF334155)
                              : const Color(0xFFCBD5E1),
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              const Icon(
                                Icons.calendar_today_rounded,
                                size: 18,
                              ),
                              const SizedBox(width: 8),
                              const Text(
                                'Tanggal: ',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              Text(
                                DateHelper.formatIndonesian(selectedDate),
                                style: const TextStyle(fontSize: 12),
                              ),
                            ],
                          ),
                          const Icon(
                            Icons.arrow_drop_down_circle_outlined,
                            size: 18,
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Metode Pembayaran
                  Row(
                    children: [
                      const Text(
                        'Metode: ',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(width: 18),
                      ChoiceChip(
                        label: Text(
                          'Tunai',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: metodeBayar == 'Tunai'
                                ? FontWeight.bold
                                : FontWeight.normal,
                            color: metodeBayar == 'Tunai'
                                ? Colors.white
                                : (isDark
                                      ? const Color(0xFFC3C8BC)
                                      : const Color(0xFF43483E)),
                          ),
                        ),
                        selected: metodeBayar == 'Tunai',
                        selectedColor: AppColors.primaryLight,
                        backgroundColor: isDark
                            ? const Color(0xFF1B241C)
                            : const Color(0xFFF1F5F9),
                        checkmarkColor: Colors.white,
                        onSelected: (val) {
                          if (val) setModalState(() => metodeBayar = 'Tunai');
                        },
                      ),
                      const SizedBox(width: 8),
                      ChoiceChip(
                        label: Text(
                          'Transfer',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: metodeBayar == 'Transfer'
                                ? FontWeight.bold
                                : FontWeight.normal,
                            color: metodeBayar == 'Transfer'
                                ? Colors.white
                                : (isDark
                                      ? const Color(0xFFC3C8BC)
                                      : const Color(0xFF43483E)),
                          ),
                        ),
                        selected: metodeBayar == 'Transfer',
                        selectedColor: AppColors.primaryLight,
                        backgroundColor: isDark
                            ? const Color(0xFF1B241C)
                            : const Color(0xFFF1F5F9),
                        checkmarkColor: Colors.white,
                        onSelected: (val) {
                          if (val) {
                            setModalState(() => metodeBayar = 'Transfer');
                          }
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Catatan Input
                  TextField(
                    controller: catatanCtrl,
                    decoration: const InputDecoration(
                      labelText: 'Catatan Transaksi (Opsional)',
                      hintText: 'Contoh: Lunas via wali murid',
                      prefixIcon: Icon(Icons.edit_note_rounded, size: 20),
                      isDense: true,
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Submit Button
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: isSubmitting
                          ? null
                          : () async {
                              setModalState(() => isSubmitting = true);
                              HapticHelper.medium();

                              final tagihanIdsToPay = items
                                  .map((m) => m.tagihanId)
                                  .whereType<int>()
                                  .toList();

                              final success = await context
                                  .read<TagihanProvider>()
                                  .bayarNonSpp(
                                    tagihanIds: tagihanIdsToPay,
                                    tipePembayar: tipePembayar,
                                    metodePembayaran: metodeBayar,
                                    tanggalBayar:
                                        '${selectedDate.year}-${selectedDate.month.toString().padLeft(2, '0')}-${selectedDate.day.toString().padLeft(2, '0')}',
                                    catatan: catatanCtrl.text.isNotEmpty
                                        ? catatanCtrl.text
                                        : null,
                                  );

                              if (context.mounted) {
                                Navigator.pop(ctx);
                                setState(() {
                                  _selectedTagihanIds.clear();
                                });
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(
                                      success
                                          ? 'Pembayaran berhasil dicatat!'
                                          : 'Gagal memproses pembayaran.',
                                    ),
                                    backgroundColor: success
                                        ? AppColors.primaryLight
                                        : AppColors.roseDanger,
                                  ),
                                );
                              }
                            },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                      child: isSubmitting
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : Text(
                              'Konfirmasi Pembayaran (${_formatRupiah(totalNominal)})',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // TAB VIEW 1: SYAHRIYAH / SPP BULANAN (READ ONLY)
  // =========================================================================
  Widget _buildSppTabView(bool isDark, TagihanProvider tagihan) {
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final ringkasan = tagihan.ringkasan;
    final roomList =
        ringkasan?.ruanganList ?? tagihan.nonSppRingkasan?.ruanganList ?? [];
    final bulanList = ringkasan?.bulanList ?? [];

    if (tagihan.isLoading && ringkasan == null) {
      return ListView(
        padding: const EdgeInsets.all(16),
        children: const [ShimmerLoadingList(count: 3, height: 110)],
      );
    }

    if (tagihan.errorMessage != null && ringkasan == null) {
      return RefreshIndicator(
        onRefresh: () async => _loadData(),
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const SizedBox(height: 60),
            EmptyStateView(
              icon: Icons.error_outline_rounded,
              title: 'Gagal Memuat Data',
              description: tagihan.errorMessage!,
              actionLabel: 'Coba Lagi',
              onActionTap: _loadData,
            ),
          ],
        ),
      );
    }

    // Kondisi tagihan SPP belum diterbitkan oleh Admin / Bendahara
    final isSppBelumDiterbitkan =
        ringkasan == null ||
        ringkasan.totalTargetSpp == 0 ||
        bulanList.isEmpty ||
        tagihan.muridList.isEmpty;

    return RefreshIndicator(
      onRefresh: () async => _loadData(),
      child: ListView(
        padding: EdgeInsets.fromLTRB(
          16,
          12,
          16,
          120 + MediaQuery.of(context).padding.bottom,
        ),
        children: [
          // ===================================================================
          // 1. FILTER RUANGAN KELAS & BULAN SPP
          // ===================================================================
          GlassCard(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.filter_list_rounded,
                      size: 18,
                      color: primaryColor,
                    ),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        'Pilih Ruangan Kelas & Bulan SPP',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // 1.1 Dropdown Ruangan Kelas
                if (roomList.isNotEmpty) ...[
                  DropdownButtonFormField<int>(
                    key: ValueKey(
                      'spp_ruangan_${_selectedRuanganId ?? ringkasan?.ruanganId}',
                    ),
                    initialValue: _selectedRuanganId ?? ringkasan?.ruanganId,
                    decoration: const InputDecoration(
                      labelText: 'Ruangan Kelas',
                      prefixIcon: Icon(Icons.meeting_room_rounded, size: 18),
                      contentPadding: EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 10,
                      ),
                    ),
                    items: roomList.map((r) {
                      return DropdownMenuItem<int>(
                        value: r.id,
                        child: Text(
                          '${r.namaRuangan} (${r.levelNama})',
                          style: const TextStyle(fontSize: 13),
                          overflow: TextOverflow.ellipsis,
                        ),
                      );
                    }).toList(),
                    onChanged: (newId) {
                      if (newId != null) {
                        HapticHelper.light();
                        setState(() {
                          _selectedRuanganId = newId;
                          _selectedBulanId = null;
                        });
                        context.read<TagihanProvider>().fetchSppRingkasan(
                          ruanganId: newId,
                        );
                        context.read<TagihanProvider>().fetchNonSppRingkasan(
                          ruanganId: newId,
                          pengaturanTagihanId: null,
                        );
                      }
                    },
                  ),
                  const SizedBox(height: 12),
                ],

                // 1.2 Dropdown Bulan SPP
                DropdownButtonFormField<int?>(
                  key: ValueKey(
                    'spp_bulan_${_selectedRuanganId}_$_selectedBulanId',
                  ),
                  initialValue: _selectedBulanId,
                  decoration: const InputDecoration(
                    labelText: 'Bulan Hijriyah',
                    prefixIcon: Icon(Icons.calendar_month_rounded, size: 18),
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                  ),
                  items: [
                    const DropdownMenuItem<int?>(
                      value: null,
                      child: Text(
                        'Semua Bulan',
                        style: TextStyle(fontSize: 13),
                      ),
                    ),
                    ...bulanList.map((b) {
                      return DropdownMenuItem<int?>(
                        value: b.id,
                        child: Text(
                          '${b.namaBulan} (${b.tahunHijriyah})',
                          style: const TextStyle(fontSize: 13),
                          overflow: TextOverflow.ellipsis,
                        ),
                      );
                    }),
                  ],
                  onChanged: (val) {
                    HapticHelper.light();
                    setState(() => _selectedBulanId = val);
                    context.read<TagihanProvider>().fetchSppMuridList(
                      ruanganId: _selectedRuanganId,
                      bulanHijriyahId: val,
                      status: _sppFilterStatus,
                      search: _sppSearchQuery,
                    );
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          // ===================================================================
          // 2. KONDISI BELUM DITERBITKAN / DATA KONTEN
          // ===================================================================
          if (isSppBelumDiterbitkan && !tagihan.isLoading)
            Padding(
              padding: const EdgeInsets.only(top: 20),
              child: EmptyStateView(
                icon: Icons.pending_actions_rounded,
                title: 'Tagihan SPP Belum Diterbitkan',
                description:
                    'Tagihan Syahriyah (SPP) untuk ${ringkasan?.namaRuangan ?? "kelas ini"} belum diterbitkan oleh Bendahara Madrasah. Silakan hubungi Administrator jika tagihan seharusnya sudah diterbitkan.',
                actionLabel: 'Muat Ulang',
                onActionTap: _loadData,
              ),
            )
          else ...[
            // 2.1 Ringkasan Finansial SPP
            if (ringkasan != null)
              GlassCard(
                padding: const EdgeInsets.all(18),
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
                                color: AppColors.primaryLight.withValues(
                                  alpha: 0.12,
                                ),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Icon(
                                Icons.verified_rounded,
                                color: AppColors.primaryLight,
                                size: 20,
                              ),
                            ),
                            const SizedBox(width: 10),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Syahriyah ${ringkasan.namaRuangan}',
                                  style: const TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                Text(
                                  '${_formatRupiah(ringkasan.nominalSppBulanan)} / murid / bulan',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: isDark
                                        ? const Color(0xFF8D9387)
                                        : const Color(0xFF73796E),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? AppColors.primaryContainerDark
                                : AppColors.primaryContainerLight,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            '${ringkasan.totalSantri} Murid',
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
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        _buildSummaryItem(
                          'Total Target',
                          _formatRupiah(ringkasan.totalTargetSpp),
                          isDark
                              ? const Color(0xFFC3C8BC)
                              : const Color(0xFF43483E),
                          isDark,
                        ),
                        Container(
                          height: 36,
                          width: 1,
                          color: isDark
                              ? AppColors.outlineDark
                              : AppColors.outlineLight,
                        ),
                        _buildSummaryItem(
                          'Sudah Lunas',
                          _formatRupiah(ringkasan.totalLunasNominal),
                          AppColors.primaryLight,
                          isDark,
                        ),
                        Container(
                          height: 36,
                          width: 1,
                          color: isDark
                              ? AppColors.outlineDark
                              : AppColors.outlineLight,
                        ),
                        _buildSummaryItem(
                          'Tunggakan',
                          _formatRupiah(ringkasan.totalTunggakanNominal),
                          AppColors.amberAccent,
                          isDark,
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Badges count status
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        _buildMiniBadge(
                          'Lunas Semua: ${ringkasan.totalSantriLunasSemua}',
                          AppColors.hadirTextLight,
                          isDark
                              ? AppColors.hadirBgDark
                              : AppColors.hadirBgLight,
                        ),
                        _buildMiniBadge(
                          'Belum Lunas: ${ringkasan.totalSantriBelumLunas}',
                          AppColors.amberAccent,
                          isDark
                              ? const Color(0xFF382305)
                              : const Color(0xFFFEF3C7),
                        ),
                        if (ringkasan.totalSantriBebasDonatur > 0)
                          _buildMiniBadge(
                            'Bebas: ${ringkasan.totalSantriBebasDonatur}',
                            AppColors.amberAccent,
                            isDark
                                ? const Color(0xFF382305)
                                : const Color(0xFFFEF3C7),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 14),

            // 2.2 Filter & Search Bar
            TextField(
              decoration: const InputDecoration(
                hintText: 'Cari nama atau NISM murid...',
                prefixIcon: Icon(Icons.search_rounded, size: 20),
                contentPadding: EdgeInsets.symmetric(
                  vertical: 8,
                  horizontal: 12,
                ),
                isDense: true,
              ),
              onChanged: (val) {
                setState(() => _sppSearchQuery = val);
                context.read<TagihanProvider>().fetchSppMuridList(
                  ruanganId: _selectedRuanganId,
                  bulanHijriyahId: _selectedBulanId,
                  status: _sppFilterStatus,
                  search: val,
                );
              },
            ),
            const SizedBox(height: 10),

            // 2.3 Filter Status Chips Bar
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  ...['Semua', 'Lunas', 'Belum Lunas', 'Ditanggung Donatur'].map(
                    (status) {
                      final isSelected = _sppFilterStatus == status;
                      return Padding(
                        padding: const EdgeInsets.only(right: 6),
                        child: FilterChip(
                          label: Text(
                            status,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: isSelected
                                  ? FontWeight.bold
                                  : FontWeight.normal,
                              color: isSelected
                                  ? Colors.white
                                  : (isDark
                                        ? const Color(0xFFC3C8BC)
                                        : const Color(0xFF43483E)),
                            ),
                          ),
                          selected: isSelected,
                          backgroundColor: isDark
                              ? const Color(0xFF1B241C)
                              : const Color(0xFFF1F5F9),
                          selectedColor: primaryColor,
                          checkmarkColor: Colors.white,
                          onSelected: (val) {
                            setState(() => _sppFilterStatus = status);
                            context.read<TagihanProvider>().fetchSppMuridList(
                              ruanganId: _selectedRuanganId,
                              bulanHijriyahId: _selectedBulanId,
                              status: status,
                              search: _sppSearchQuery,
                            );
                          },
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),

          // 3. Murid SPP List
          if (tagihan.isLoading)
            const ShimmerLoadingList(count: 4, height: 100)
          else if (tagihan.muridList.isEmpty)
            _sppSearchQuery.isNotEmpty ||
                    _sppFilterStatus != 'Semua' ||
                    _selectedBulanId != null
                ? const Padding(
                    padding: EdgeInsets.symmetric(vertical: 24),
                    child: EmptyStateView(
                      icon: Icons.search_off_rounded,
                      title: 'Tidak Ada Data Ditemukan',
                      description:
                          'Tidak ada data murid yang sesuai dengan filter atau kata kunci pencarian.',
                    ),
                  )
                : Padding(
                    padding: const EdgeInsets.symmetric(vertical: 24),
                    child: EmptyStateView(
                      icon: Icons.credit_card_off_rounded,
                      title: 'Tagihan SPP Belum Diterbitkan',
                      description:
                          'Belum ada rincian tagihan SPP santri yang diterbitkan di kelas ini.',
                      actionLabel: 'Muat Ulang',
                      onActionTap: _loadData,
                    ),
                  )
          else
            ...tagihan.muridList.map((m) {
              final isLunas = m.statusKeseluruhan == 'Lunas';
              final isDonatur =
                  m.statusKeseluruhan == 'Ditanggung Donatur' ||
                  m.statusKeseluruhan == 'Bebas SPP';
              final progress = m.totalBulan > 0
                  ? (m.bulanLunasCount / m.totalBulan).clamp(0.0, 1.0)
                  : 1.0;
              final isPutra = m.jenisKelamin == 'L';

              return GlassCard(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Row(
                            children: [
                              AppAvatar(
                                radius: 18,
                                name: m.nama,
                                imageUrl: m.foto,
                                fit: BoxFit.cover,
                                alignment: Alignment.topCenter,
                                backgroundColor: isPutra
                                    ? Colors.blue.withValues(alpha: 0.15)
                                    : Colors.pink.withValues(alpha: 0.15),
                                textColor: isPutra ? Colors.blue : Colors.pink,
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      m.nama,
                                      style: const TextStyle(
                                        fontSize: 13.5,
                                        fontWeight: FontWeight.bold,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                    Text(
                                      'NISM: ${m.nism} • Wali: ${m.wali}',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: isDark
                                            ? const Color(0xFF8D9387)
                                            : const Color(0xFF73796E),
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: isLunas
                                ? (isDark
                                      ? AppColors.hadirBgDark
                                      : AppColors.hadirBgLight)
                                : isDonatur
                                ? (isDark
                                      ? const Color(0xFF241538)
                                      : const Color(0xFFF3E8FF))
                                : (isDark
                                      ? const Color(0xFF382305)
                                      : const Color(0xFFFEF3C7)),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            isDonatur
                                ? 'Donatur'
                                : (isLunas ? 'Lunas' : 'Belum Lunas'),
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: isLunas
                                  ? AppColors.hadirTextLight
                                  : isDonatur
                                  ? AppColors.violetAccent
                                  : AppColors.amberAccent,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),

                    // Progress Bar
                    ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: LinearProgressIndicator(
                        value: isDonatur ? 1.0 : progress,
                        minHeight: 6,
                        backgroundColor: isDark
                            ? const Color(0xFF202720)
                            : const Color(0xFFE5E7EB),
                        valueColor: AlwaysStoppedAnimation(
                          isLunas
                              ? AppColors.hadirTextLight
                              : isDonatur
                              ? AppColors.violetAccent
                              : AppColors.amberAccent,
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),

                    // Bulan Dots Matrix (11 Hijriyah Dots)
                    Row(
                      children: [
                        Expanded(
                          child: Wrap(
                            spacing: 4,
                            runSpacing: 4,
                            children: m.bulanItems.map((b) {
                              final blnLunas = b.statusBayar == 'Lunas';
                              final blnDonatur =
                                  b.statusBayar == 'Ditanggung Donatur' ||
                                  b.statusBayar == 'Bebas SPP';

                              Color dotColor = isDark
                                  ? const Color(0xFF475569)
                                  : const Color(0xFFCBD5E1);
                              if (blnLunas) {
                                dotColor = AppColors.hadirTextLight;
                              } else if (blnDonatur) {
                                dotColor = AppColors.violetAccent;
                              }

                              return Tooltip(
                                message: '${b.namaBulan}: ${b.statusBayar}',
                                child: Container(
                                  width: 14,
                                  height: 14,
                                  decoration: BoxDecoration(
                                    color: dotColor,
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                ),
                              );
                            }).toList(),
                          ),
                        ),
                        const SizedBox(width: 8),
                        ElevatedButton.icon(
                          onPressed: () => _openKartuSppSheet(m),
                          icon: const Icon(
                            Icons.receipt_long_rounded,
                            size: 15,
                          ),
                          label: const Text(
                            'Kartu SPP',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 4,
                            ),
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),

                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Lunas: ${m.bulanLunasCount} / ${m.totalBulan} Bulan (${_formatRupiah(m.totalDibayar)})',
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        if (!isLunas && !isDonatur && m.sisaTunggakan > 0)
                          Text(
                            'Sisa: ${_formatRupiah(m.sisaTunggakan)}',
                            style: const TextStyle(
                              fontSize: 11,
                              color: AppColors.roseDanger,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                      ],
                    ),
                  ],
                ),
              );
            }),
          ],
        ],
      ),
    );
  }

  // =========================================================================
  // TAB VIEW 2: TAGIHAN NON-SPP (PEMBAYARAN & PEMANTAUAN)
  // =========================================================================
  Widget _buildNonSppTabView(bool isDark, TagihanProvider tagihan) {
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final onPrimaryColor = isDark
        ? AppColors.onPrimaryDark
        : AppColors.onPrimaryLight;
    final ringkasan = tagihan.nonSppRingkasan;
    final masterList = (ringkasan?.masterTagihanList ?? [])
        .where((m) =>
            m.tipe.toUpperCase() != 'IMNI' &&
            !m.namaTagihan.toUpperCase().contains('IMNI') &&
            !m.kodeTagihan.toUpperCase().contains('IMNI'))
        .toList();
    final roomList =
        ringkasan?.ruanganList ?? tagihan.ringkasan?.ruanganList ?? [];
    final muridList = tagihan.nonSppMuridList;

    if (tagihan.isLoadingNonSpp && ringkasan == null) {
      return ListView(
        padding: const EdgeInsets.all(16),
        children: const [ShimmerLoadingList(count: 3, height: 110)],
      );
    }

    if (tagihan.errorMessage != null && ringkasan == null) {
      return RefreshIndicator(
        onRefresh: () async => _loadData(),
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const SizedBox(height: 60),
            EmptyStateView(
              icon: Icons.error_outline_rounded,
              title: 'Gagal Memuat Data Tagihan',
              description: tagihan.errorMessage!,
              actionLabel: 'Coba Lagi',
              onActionTap: _loadData,
            ),
          ],
        ),
      );
    }

    // Kondisi tagihan Non-SPP belum diterbitkan
    final isNonSppBelumDiterbitkan =
        masterList.isEmpty ||
        ringkasan == null ||
        ringkasan.pengaturanTagihanId == null;

    final unpaidCount = muridList
        .where((m) => m.statusBayar == 'Belum Lunas')
        .length;
    final allUnpaidSelected =
        unpaidCount > 0 &&
        muridList
            .where((m) => m.statusBayar == 'Belum Lunas')
            .every((m) => _selectedTagihanIds.contains(m.tagihanId));

    return Stack(
      children: [
        RefreshIndicator(
          onRefresh: () async => _loadData(),
          child: ListView(
            padding: EdgeInsets.fromLTRB(
              16,
              12,
              16,
              120 + MediaQuery.of(context).padding.bottom,
            ),
            children: [
              // ===================================================================
              // 1. FILTER RUANGAN KELAS & JENIS TAGIHAN NON-SPP
              // ===================================================================
              GlassCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.filter_list_rounded,
                          size: 18,
                          color: primaryColor,
                        ),
                        const SizedBox(width: 8),
                        const Expanded(
                          child: Text(
                            'Pilih Ruangan Kelas & Jenis Tagihan',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // 1.1 Dropdown Ruangan Kelas
                    if (roomList.isNotEmpty) ...[
                      DropdownButtonFormField<int>(
                        key: ValueKey(
                          'non_spp_ruangan_${_selectedRuanganId ?? ringkasan?.ruanganId}',
                        ),
                        initialValue: _selectedRuanganId ?? ringkasan?.ruanganId,
                        decoration: const InputDecoration(
                          labelText: 'Ruangan Kelas',
                          prefixIcon: Icon(Icons.meeting_room_rounded, size: 18),
                          contentPadding: EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 10,
                          ),
                        ),
                        items: roomList.map((r) {
                          return DropdownMenuItem<int>(
                            value: r.id,
                            child: Text(
                              '${r.namaRuangan} (${r.levelNama})',
                              style: const TextStyle(fontSize: 13),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }).toList(),
                        onChanged: (newId) {
                          if (newId != null) {
                            HapticHelper.light();
                            setState(() {
                              _selectedRuanganId = newId;
                              _selectedMasterTagihanId = null;
                              _selectedTagihanIds.clear();
                            });
                            context.read<TagihanProvider>().fetchSppRingkasan(
                              ruanganId: newId,
                            );
                            context
                                .read<TagihanProvider>()
                                .fetchNonSppRingkasan(
                                  ruanganId: newId,
                                  pengaturanTagihanId: null,
                                );
                          }
                        },
                      ),
                      const SizedBox(height: 12),
                    ],

                    // 1.2 Dropdown Jenis Tagihan Non-SPP
                    DropdownButtonFormField<int>(
                      key: ValueKey(
                        'non_spp_${_selectedRuanganId}_${_selectedMasterTagihanId ?? ringkasan?.pengaturanTagihanId}',
                      ),
                      initialValue: masterList.any((m) =>
                              m.id ==
                              (_selectedMasterTagihanId ??
                                  ringkasan?.pengaturanTagihanId))
                          ? (_selectedMasterTagihanId ??
                              ringkasan?.pengaturanTagihanId)
                          : (masterList.isNotEmpty ? masterList.first.id : null),
                      decoration: const InputDecoration(
                        labelText: 'Jenis Tagihan Non-SPP',
                        prefixIcon: Icon(Icons.receipt_long_rounded, size: 18),
                        contentPadding: EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 10,
                        ),
                      ),
                      items: masterList.isEmpty
                          ? [
                              const DropdownMenuItem<int>(
                                value: null,
                                child: Text(
                                  'Belum ada tagihan diterbitkan',
                                  style: TextStyle(fontSize: 13),
                                ),
                              ),
                            ]
                          : masterList.map((m) {
                              return DropdownMenuItem<int>(
                                value: m.id,
                                child: Text(
                                  m.namaTagihan,
                                  style: const TextStyle(fontSize: 13),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              );
                            }).toList(),
                      onChanged: masterList.isEmpty
                          ? null
                          : (val) {
                              if (val != null) {
                                HapticHelper.light();
                                setState(() {
                                  _selectedMasterTagihanId = val;
                                  _selectedTagihanIds.clear();
                                });
                                context
                                    .read<TagihanProvider>()
                                    .fetchNonSppRingkasan(
                                      ruanganId: _selectedRuanganId ??
                                          ringkasan?.ruanganId,
                                      pengaturanTagihanId: val,
                                    );
                              }
                            },
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),

              // ===================================================================
              // 2. KONDISI BELUM DITERBITKAN / DATA KONTEN
              // ===================================================================
              if (isNonSppBelumDiterbitkan && !tagihan.isLoadingNonSpp)
                Padding(
                  padding: const EdgeInsets.only(top: 20),
                  child: EmptyStateView(
                    icon: Icons.receipt_long_outlined,
                    title: 'Tagihan Non-SPP Belum Diterbitkan',
                    description:
                        'Belum ada jenis tagihan non-SPP (seperti ujian, seragam, kitab, dll.) yang diterbitkan untuk ${ringkasan?.namaRuangan ?? "kelas ini"}.',
                    actionLabel: 'Muat Ulang',
                    onActionTap: _loadData,
                  ),
                )
              else ...[
                // 2.1 Ringkasan Finansial Tagihan Non-SPP
                if (ringkasan != null)
                  GlassCard(
                    padding: const EdgeInsets.all(18),
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
                                    color: AppColors.primaryLight.withValues(
                                      alpha: 0.12,
                                    ),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Icon(
                                    Icons.receipt_long_rounded,
                                    color: AppColors.primaryLight,
                                    size: 20,
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      ringkasan.namaTagihan,
                                      style: const TextStyle(
                                        fontSize: 15,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    Text(
                                      'Tipe: ${ringkasan.tipeTagihan.toUpperCase()} • ${_formatRupiah(ringkasan.nominal)} / murid',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: isDark
                                            ? const Color(0xFF8D9387)
                                            : const Color(0xFF73796E),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? AppColors.primaryContainerDark
                                    : AppColors.primaryContainerLight,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '${ringkasan.totalSantri} Murid',
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
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            _buildSummaryItem(
                              'Total Target',
                              _formatRupiah(ringkasan.totalTargetNominal),
                              isDark
                                  ? const Color(0xFFC3C8BC)
                                  : const Color(0xFF43483E),
                              isDark,
                            ),
                            Container(
                              height: 36,
                              width: 1,
                              color: isDark
                                  ? AppColors.outlineDark
                                  : AppColors.outlineLight,
                            ),
                            _buildSummaryItem(
                              'Sudah Lunas',
                              _formatRupiah(ringkasan.totalLunasNominal),
                              AppColors.primaryLight,
                              isDark,
                            ),
                            Container(
                              height: 36,
                              width: 1,
                              color: isDark
                                  ? AppColors.outlineDark
                                  : AppColors.outlineLight,
                            ),
                            _buildSummaryItem(
                              'Tunggakan',
                              _formatRupiah(ringkasan.totalTunggakanNominal),
                              AppColors.amberAccent,
                              isDark,
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // Status Count Badges
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _buildMiniBadge(
                              'Lunas: ${ringkasan.totalSantriLunas}',
                              AppColors.hadirTextLight,
                              isDark
                                  ? AppColors.hadirBgDark
                                  : AppColors.hadirBgLight,
                            ),
                            _buildMiniBadge(
                              'Belum Lunas: ${ringkasan.totalSantriBelumLunas}',
                              AppColors.amberAccent,
                              isDark
                                  ? const Color(0xFF382305)
                                  : const Color(0xFFFEF3C7),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                const SizedBox(height: 14),

                // 2.2 Search Bar
                TextField(
                  decoration: const InputDecoration(
                    hintText: 'Cari nama atau NISM murid...',
                    prefixIcon: Icon(Icons.search_rounded, size: 20),
                    contentPadding: EdgeInsets.symmetric(
                      vertical: 8,
                      horizontal: 12,
                    ),
                    isDense: true,
                  ),
                  onChanged: (val) {
                    setState(() => _nonSppSearchQuery = val);
                    context.read<TagihanProvider>().fetchNonSppMuridList(
                      ruanganId: _selectedRuanganId,
                      pengaturanTagihanId: _selectedMasterTagihanId,
                      status: _nonSppFilterStatus,
                      search: val,
                    );
                  },
                ),
                const SizedBox(height: 10),

                // 2.3 Status Filter Chips Bar (Semua, Lunas, Belum Lunas + Pilih Semua)
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      // Status Filter Chips
                      ...['Semua', 'Lunas', 'Belum Lunas'].map((status) {
                        final isSelected = _nonSppFilterStatus == status;
                        return Padding(
                          padding: const EdgeInsets.only(right: 6),
                          child: FilterChip(
                            label: Text(
                              status,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: isSelected
                                    ? FontWeight.bold
                                    : FontWeight.normal,
                                color: isSelected
                                    ? Colors.white
                                    : (isDark
                                          ? const Color(0xFFC3C8BC)
                                          : const Color(0xFF43483E)),
                              ),
                            ),
                            selected: isSelected,
                            backgroundColor: isDark
                                ? const Color(0xFF1B241C)
                                : const Color(0xFFF1F5F9),
                            selectedColor: primaryColor,
                            checkmarkColor: Colors.white,
                            onSelected: (val) {
                              setState(() => _nonSppFilterStatus = status);
                              context
                                  .read<TagihanProvider>()
                                  .fetchNonSppMuridList(
                                    ruanganId: _selectedRuanganId,
                                    pengaturanTagihanId:
                                        _selectedMasterTagihanId ??
                                        ringkasan?.pengaturanTagihanId,
                                    status: status,
                                    search: _nonSppSearchQuery,
                                  );
                            },
                          ),
                        );
                      }),

                      // Pilih Semua / Batal Pilih Action
                      if (unpaidCount > 0)
                        Padding(
                          padding: const EdgeInsets.only(left: 2, right: 6),
                          child: InkWell(
                            onTap: () {
                              HapticHelper.light();
                              setState(() {
                                if (allUnpaidSelected) {
                                  _selectedTagihanIds.clear();
                                } else {
                                  for (var m in muridList) {
                                    if (m.statusBayar == 'Belum Lunas' &&
                                        m.tagihanId != null) {
                                      _selectedTagihanIds.add(m.tagihanId!);
                                    }
                                  }
                                }
                              });
                            },
                            borderRadius: BorderRadius.circular(20),
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 7,
                              ),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? AppColors.primaryContainerDark
                                    : AppColors.primaryContainerLight,
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: primaryColor.withValues(alpha: 0.3),
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    allUnpaidSelected
                                        ? Icons.check_box_rounded
                                        : Icons.check_box_outline_blank_rounded,
                                    size: 15,
                                    color: primaryColor,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    allUnpaidSelected
                                        ? 'Batal Pilih'
                                        : 'Pilih Semua ($unpaidCount)',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: primaryColor,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),

                // 2.4 Daftar Murid Non-SPP
                if (tagihan.isLoadingNonSpp)
                  const ShimmerLoadingList(count: 4, height: 90)
                else if (muridList.isEmpty)
                  _nonSppSearchQuery.isNotEmpty ||
                          _nonSppFilterStatus != 'Semua'
                      ? const Padding(
                          padding: EdgeInsets.symmetric(vertical: 24),
                          child: EmptyStateView(
                            icon: Icons.search_off_rounded,
                            title: 'Tidak Ada Data Ditemukan',
                            description:
                                'Tidak ada data murid yang sesuai dengan filter atau kata kunci pencarian.',
                          ),
                        )
                      : Padding(
                          padding: const EdgeInsets.symmetric(vertical: 24),
                          child: EmptyStateView(
                            icon: Icons.receipt_long_outlined,
                            title: 'Tagihan Belum Diterbitkan',
                            description:
                                'Tagihan ${ringkasan?.namaTagihan ?? "ini"} belum diterbitkan untuk murid di kelas ini.',
                            actionLabel: 'Muat Ulang',
                            onActionTap: _loadData,
                          ),
                        )
                else
                  ...muridList.map((m) {
                    final isLunas = m.statusBayar == 'Lunas';
                    final isPutra = m.jenisKelamin == 'L';
                    final isSelected =
                        m.tagihanId != null &&
                        _selectedTagihanIds.contains(m.tagihanId);

                    return GlassCard(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              // Checkbox Multi Select (Hanya untuk yang belum lunas)
                              if (!isLunas && m.tagihanId != null)
                                Padding(
                                  padding: const EdgeInsets.only(right: 6),
                                  child: SizedBox(
                                    width: 24,
                                    height: 24,
                                    child: Checkbox(
                                      value: isSelected,
                                      activeColor: primaryColor,
                                      materialTapTargetSize:
                                          MaterialTapTargetSize.shrinkWrap,
                                      visualDensity: VisualDensity.compact,
                                      onChanged: (val) {
                                        setState(() {
                                          if (val == true) {
                                            _selectedTagihanIds.add(
                                              m.tagihanId!,
                                            );
                                          } else {
                                            _selectedTagihanIds.remove(
                                              m.tagihanId!,
                                            );
                                          }
                                        });
                                      },
                                    ),
                                  ),
                                ),

                              // Avatar Murid
                              AppAvatar(
                                radius: 18,
                                name: m.nama,
                                imageUrl: m.foto,
                                fit: BoxFit.cover,
                                alignment: Alignment.topCenter,
                                backgroundColor: isPutra
                                    ? Colors.blue.withValues(alpha: 0.15)
                                    : Colors.pink.withValues(alpha: 0.15),
                                textColor: isPutra
                                    ? Colors.blue
                                    : Colors.pink,
                              ),
                              const SizedBox(width: 10),

                              // Nama & NISM
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      m.nama,
                                      style: const TextStyle(
                                        fontSize: 13.5,
                                        fontWeight: FontWeight.bold,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                    Text(
                                      'NISM: ${m.nism} • Wali: ${m.namaWali}',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: isDark
                                            ? const Color(0xFF8D9387)
                                            : const Color(0xFF73796E),
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],
                                ),
                              ),

                              // Nominal Tagihan & Status Badge
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(
                                    _formatRupiah(m.nominal),
                                    style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 8,
                                      vertical: 3,
                                    ),
                                    decoration: BoxDecoration(
                                      color: isLunas
                                          ? (isDark
                                                ? AppColors.hadirBgDark
                                                : AppColors.hadirBgLight)
                                          : (isDark
                                                ? const Color(0xFF382305)
                                                : const Color(0xFFFEF3C7)),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      m.statusBayar,
                                      style: TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                        color: isLunas
                                            ? AppColors.hadirTextLight
                                            : AppColors.amberAccent,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),

                          // Detail Kwitansi jika Lunas
                          if (isLunas && m.noKwitansi != null) ...[
                            const SizedBox(height: 10),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 6,
                              ),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? const Color(0xFF1E281F)
                                    : const Color(0xFFF0FDF4),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Text(
                                      'Kwitansi: ${m.noKwitansi} • ${m.hariTanggalBayar ?? m.tanggalBayar} (${m.metodePembayaran ?? "Tunai"})',
                                      style: TextStyle(
                                        fontSize: 10.5,
                                        color: primaryColor,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                  if (m.tagihanId != null)
                                    IconButton(
                                      icon: const Icon(
                                        Icons.undo_rounded,
                                        size: 16,
                                        color: AppColors.roseDanger,
                                      ),
                                      tooltip: 'Batalkan / Refund',
                                      padding: EdgeInsets.zero,
                                      constraints: const BoxConstraints(),
                                      onPressed: () async {
                                        final tagihanProvider = context
                                            .read<TagihanProvider>();
                                        final confirm = await showDialog<bool>(
                                          context: context,
                                          builder: (c) => AlertDialog(
                                            title: const Text(
                                              'Batalkan Transaksi?',
                                            ),
                                            content: Text(
                                              'Yakin ingin membatalkan pembayaran ${ringkasan?.namaTagihan} untuk ${m.nama}?\n\nStatus akan dikembalikan ke Belum Lunas.',
                                            ),
                                            actions: [
                                              TextButton(
                                                onPressed: () =>
                                                    Navigator.pop(c, false),
                                                child: const Text('Batal'),
                                              ),
                                              ElevatedButton(
                                                style:
                                                    ElevatedButton.styleFrom(
                                                  backgroundColor:
                                                      AppColors.roseDanger,
                                                  foregroundColor: Colors.white,
                                                ),
                                                onPressed: () =>
                                                    Navigator.pop(c, true),
                                                child:
                                                    const Text('Ya, Batalkan'),
                                              ),
                                            ],
                                          ),
                                        );
                                        if (confirm == true) {
                                          await tagihanProvider.batalBayarNonSpp(
                                            m.tagihanId!,
                                          );
                                        }
                                      },
                                    ),
                                ],
                              ),
                            ),
                          ],

                          // Tombol Bayar jika Belum Lunas
                          if (!isLunas && m.tagihanId != null) ...[
                            const SizedBox(height: 10),
                            Align(
                              alignment: Alignment.centerRight,
                              child: ElevatedButton.icon(
                                onPressed: () =>
                                    _openBayarNonSppSheet(singleMurid: m),
                                icon: const Icon(
                                  Icons.payments_rounded,
                                  size: 15,
                                ),
                                label: const Text(
                                  '+ Bayar Tagihan',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                style: ElevatedButton.styleFrom(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 12,
                                    vertical: 4,
                                  ),
                                  minimumSize: Size.zero,
                                  tapTargetSize:
                                      MaterialTapTargetSize.shrinkWrap,
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    );
                  }),
              ],
            ],
          ),
        ),

        // Floating Bottom Bar Bayar Massal
        if (_selectedTagihanIds.isNotEmpty)
          Positioned(
            left: 16,
            right: 16,
            bottom: 16 + MediaQuery.of(context).padding.bottom,
            child: Material(
              elevation: 8,
              borderRadius: BorderRadius.circular(16),
              color: primaryColor,
              child: InkWell(
                onTap: () => _openBayarNonSppSheet(),
                borderRadius: BorderRadius.circular(16),
                child: Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 18,
                    vertical: 14,
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Icon(
                            Icons.check_circle_rounded,
                            color: onPrimaryColor,
                            size: 22,
                          ),
                          const SizedBox(width: 10),
                          Text(
                            '${_selectedTagihanIds.length} Murid Dipilih',
                            style: TextStyle(
                              color: onPrimaryColor,
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      Row(
                        children: [
                          Text(
                            'Bayar Massal',
                            style: TextStyle(
                              color: onPrimaryColor,
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(width: 4),
                          Icon(
                            Icons.arrow_forward_rounded,
                            color: onPrimaryColor,
                            size: 18,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final tagihan = context.watch<TagihanProvider>();
    final ringkasan = tagihan.ringkasan;

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Pembayaran Tagihan',
        subtitleText: ringkasan?.namaRuangan ?? 'Kelas Binaan',
      ),
      body: Column(
        children: [
          // 1. Navigation Segmented Tab Bar (SPP vs Non-SPP)
          SegmentedTabBar(
            selectedIndex: _tabController.index,
            onTabChanged: (idx) {
              _tabController.animateTo(idx);
              setState(() {});
            },
            items: [
              SegmentedTabItem(
                activeIcon: Icons.calendar_month_rounded,
                inactiveIcon: Icons.calendar_month_outlined,
                label: 'Syahriyah (SPP)',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
              SegmentedTabItem(
                activeIcon: Icons.receipt_long_rounded,
                inactiveIcon: Icons.receipt_long_outlined,
                label: 'Tagihan Non-SPP',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
            ],
          ),

          // 2. Tab Bar Views
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildSppTabView(isDark, tagihan),
                _buildNonSppTabView(isDark, tagihan),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryItem(
    String label,
    String value,
    Color color,
    bool isDark,
  ) {
    return Expanded(
      child: Column(
        children: [
          Text(
            value,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w900,
              color: color,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 9,
              fontWeight: FontWeight.w500,
              color: isDark ? const Color(0xFF8D9387) : const Color(0xFF73796E),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMiniBadge(String text, Color textColor, Color bgColor) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.bold,
          color: textColor,
        ),
      ),
    );
  }
}
