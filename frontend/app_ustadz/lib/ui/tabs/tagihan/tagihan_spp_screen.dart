import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/tagihan_model.dart';
import '../../../providers/tagihan_provider.dart';
import '../../widgets/app_avatar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class TagihanSppScreen extends StatefulWidget {
  final int? initialRuanganId;

  const TagihanSppScreen({super.key, this.initialRuanganId});

  @override
  State<TagihanSppScreen> createState() => _TagihanSppScreenState();
}

class _TagihanSppScreenState extends State<TagihanSppScreen> {
  int? _selectedRuanganId;
  int? _selectedBulanId;
  String _selectedStatus = 'Semua';
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _selectedRuanganId = widget.initialRuanganId;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  void _loadData() {
    final provider = context.read<TagihanProvider>();
    provider.fetchSppRingkasan(ruanganId: _selectedRuanganId);
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
  // MODAL KARTU SPP SANTRI (READ ONLY)
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

          return Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
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
                                'Data kartu SPP untuk santri ini belum diterbitkan atau belum diatur oleh Bendahara.',
                          ),
                        )
                      : kartu.bulanItems.isEmpty
                      ? const Padding(
                          padding: EdgeInsets.symmetric(vertical: 24),
                          child: EmptyStateView(
                            icon: Icons.credit_card_off_rounded,
                            title: 'Bulan SPP Belum Diterbitkan',
                            description:
                                'Belum ada rincian bulan Syahriyah/SPP yang diterbitkan untuk santri ini.',
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

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final tagihan = context.watch<TagihanProvider>();
    final ringkasan = tagihan.ringkasan;
    final roomList = ringkasan?.ruanganList ?? [];
    final bulanList = ringkasan?.bulanList ?? [];

    if (tagihan.isLoading && ringkasan == null) {
      return Scaffold(
        appBar: const CustomAppBar(titleText: 'Monitoring SPP Murid'),
        body: ListView(
          padding: const EdgeInsets.all(16),
          children: const [ShimmerLoadingList(count: 3, height: 110)],
        ),
      );
    }

    if (tagihan.errorMessage != null && ringkasan == null) {
      return Scaffold(
        appBar: const CustomAppBar(titleText: 'Monitoring SPP Murid'),
        body: RefreshIndicator(
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
        ),
      );
    }

    // Kondisi tagihan SPP belum diterbitkan
    final isSppBelumDiterbitkan =
        ringkasan == null ||
        ringkasan.totalTargetSpp == 0 ||
        bulanList.isEmpty ||
        tagihan.muridList.isEmpty;

    if (isSppBelumDiterbitkan && !tagihan.isLoading) {
      return Scaffold(
        appBar: CustomAppBar(
          titleText: 'Monitoring SPP Murid',
          subtitleText: ringkasan?.namaRuangan ?? 'Ruangan Binaan',
        ),
        body: RefreshIndicator(
          onRefresh: () async => _loadData(),
          child: ListView(
            padding: const EdgeInsets.all(24),
            children: [
              const SizedBox(height: 60),
              EmptyStateView(
                icon: Icons.pending_actions_rounded,
                title: 'Tagihan SPP Belum Diterbitkan',
                description:
                    'Tagihan Syahriyah (SPP) untuk ${ringkasan?.namaRuangan ?? "Ruangan ini"} belum diterbitkan oleh Administrator Madrasah.',
                actionLabel: 'Muat Ulang',
                onActionTap: _loadData,
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Monitoring SPP Murid',
        subtitleText: ringkasan?.namaRuangan ?? 'Ruangan',
      ),
      body: RefreshIndicator(
        onRefresh: () async => _loadData(),
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 10, 16, 40),
          children: [
            // 0. Pemilih Ruangan (Jika Wali mengampu >1 Ruangan)
            if (roomList.length > 1) ...[
              Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: isDark
                      ? const Color(0xFF1E293B)
                      : const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.meeting_room_outlined, size: 18),
                    const SizedBox(width: 8),
                    const Text(
                      'Pilih Ruangan: ',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<int>(
                          value: _selectedRuanganId ?? ringkasan?.ruanganId,
                          isDense: true,
                          items: roomList.map((r) {
                            return DropdownMenuItem<int>(
                              value: r.id,
                              child: Text('${r.namaRuangan} (${r.levelNama})'),
                            );
                          }).toList(),
                          onChanged: (newId) {
                            if (newId != null) {
                              setState(() => _selectedRuanganId = newId);
                              context.read<TagihanProvider>().fetchSppRingkasan(
                                ruanganId: newId,
                              );
                            }
                          },
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            // 1. Ringkasan Finansial SPP
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
                            AppColors.violetAccent,
                            isDark
                                ? const Color(0xFF241538)
                                : const Color(0xFFF3E8FF),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 16),

            // 2. Filter & Search Bar
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
                setState(() => _searchQuery = val);
                context.read<TagihanProvider>().fetchSppMuridList(
                  ruanganId: _selectedRuanganId,
                  bulanHijriyahId: _selectedBulanId,
                  status: _selectedStatus,
                  search: val,
                );
              },
            ),
            const SizedBox(height: 10),

            // Filter Chips Bar (Status & Bulan)
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  // Dropdown Bulan
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1B241C)
                          : const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: isDark
                            ? const Color(0xFF334155)
                            : const Color(0xFFCBD5E1),
                      ),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<int?>(
                        value: _selectedBulanId,
                        isDense: true,
                        hint: const Text(
                          'Semua Bulan',
                          style: TextStyle(fontSize: 12),
                        ),
                        items: [
                          const DropdownMenuItem<int?>(
                            value: null,
                            child: Text(
                              'Semua Bulan',
                              style: TextStyle(fontSize: 12),
                            ),
                          ),
                          ...bulanList.map((b) {
                            return DropdownMenuItem<int?>(
                              value: b.id,
                              child: Text(
                                b.namaBulan,
                                style: const TextStyle(fontSize: 12),
                              ),
                            );
                          }),
                        ],
                        onChanged: (val) {
                          setState(() => _selectedBulanId = val);
                          context.read<TagihanProvider>().fetchSppMuridList(
                            ruanganId: _selectedRuanganId,
                            bulanHijriyahId: val,
                            status: _selectedStatus,
                            search: _searchQuery,
                          );
                        },
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // Filter Status Chips
                  ...[
                    'Semua',
                    'Lunas',
                    'Belum Lunas',
                    'Ditanggung Donatur',
                  ].map((status) {
                    final isSelected = _selectedStatus == status;
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
                        selectedColor: AppColors.primaryLight,
                        checkmarkColor: Colors.white,
                        onSelected: (val) {
                          setState(() => _selectedStatus = status);
                          context.read<TagihanProvider>().fetchSppMuridList(
                            ruanganId: _selectedRuanganId,
                            bulanHijriyahId: _selectedBulanId,
                            status: status,
                            search: _searchQuery,
                          );
                        },
                      ),
                    );
                  }),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // 3. Murid SPP List
            if (tagihan.isLoading)
              const ShimmerLoadingList(count: 4, height: 100)
            else if (tagihan.muridList.isEmpty)
              _searchQuery.isNotEmpty ||
                      _selectedStatus != 'Semua' ||
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
                            'Belum ada rincian tagihan SPP santri yang diterbitkan di Ruangan ini.',
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
                                  textColor: isPutra
                                      ? Colors.blue
                                      : Colors.pink,
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
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
                                      shape: BoxShape.circle,
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
        ),
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
