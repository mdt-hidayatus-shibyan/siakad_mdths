import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/kas_ruangan_model.dart';
import '../../../providers/kas_provider.dart';
import '../../widgets/app_avatar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';

class KasRuanganScreen extends StatefulWidget {
  const KasRuanganScreen({super.key});

  @override
  State<KasRuanganScreen> createState() => _KasRuanganScreenState();
}

class _KasRuanganScreenState extends State<KasRuanganScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  String _filterStatus = 'Semua';
  String _searchQuery = '';
  int? _selectedRuanganId;
  int _riwayatTabType = 0;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
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
    final kas = context.read<KasProvider>();
    kas.fetchRingkasan(ruanganId: _selectedRuanganId);
    kas.fetchRiwayatSetoran(ruanganId: _selectedRuanganId);
    kas.fetchPengaturan();
    kas.fetchPenerimaList();
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
  // 1. MODAL PENGATURAN TARGET KAS
  // =========================================================================
  void _openPengaturanSheet() {
    HapticHelper.light();
    final kas = context.read<KasProvider>();
    final pengaturan = kas.pengaturan;
    final nominalLakiCtrl = TextEditingController(
      text: (pengaturan?.nominalLaki ?? 0).toString(),
    );
    final nominalPerempuanCtrl = TextEditingController(
      text: (pengaturan?.nominalPerempuan ?? 0).toString(),
    );
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).viewInsets.bottom + 24,
            ),
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
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: AppColors.primaryLight.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(
                        Icons.tune_rounded,
                        color: AppColors.primaryLight,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Target Iuran Kas Ruangan',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            kas.ringkasan?.namaRuangan ?? 'Kelas Binaan',
                            style: TextStyle(
                              fontSize: 11,
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
                Text(
                  'Tentukan target iuran kas per semester/tahun untuk murid putra dan putri di kelas ini.',
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                ),
                const SizedBox(height: 16),

                TextField(
                  controller: nominalLakiCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(
                    labelText: 'Target Kas Murid Putra (Rp)',
                    prefixIcon: Icon(Icons.male_rounded, color: Colors.blue),
                  ),
                ),
                const SizedBox(height: 12),

                TextField(
                  controller: nominalPerempuanCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(
                    labelText: 'Target Kas Murid Putri (Rp)',
                    prefixIcon: Icon(Icons.female_rounded, color: Colors.pink),
                  ),
                ),
                const SizedBox(height: 20),

                ElevatedButton(
                  onPressed: isSaving
                      ? null
                      : () async {
                          final laki =
                              int.tryParse(nominalLakiCtrl.text.trim()) ?? 0;
                          final perempuan =
                              int.tryParse(nominalPerempuanCtrl.text.trim()) ??
                              0;

                          setModalState(() => isSaving = true);
                          HapticHelper.medium();

                          final success = await context
                              .read<KasProvider>()
                              .updatePengaturan(
                                ruanganId: kas.ringkasan?.ruanganId,
                                nominalLaki: laki,
                                nominalPerempuan: perempuan,
                              );

                          if (ctx.mounted) {
                            Navigator.pop(ctx);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  success
                                      ? 'Pengaturan nominal kas ruangan berhasil diperbarui!'
                                      : 'Gagal memperbarui target kas.',
                                ),
                                backgroundColor: success
                                    ? AppColors.primaryLight
                                    : AppColors.roseDanger,
                              ),
                            );
                          }
                        },
                  child: isSaving
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text('Simpan Pengaturan Target'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // 2. MODAL INPUT BAYAR KAS SANTRI
  // =========================================================================
  void _openBayarSheet(MuridKasItem murid) {
    HapticHelper.light();
    final nominalController = TextEditingController(text: '10000');
    final tanggalController = TextEditingController(
      text: DateHelper.toYmd(DateTime.now()),
    );
    bool isSaving = false;
    final sisa = murid.targetKas > murid.totalDibayar
        ? murid.targetKas - murid.totalDibayar
        : 0;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).viewInsets.bottom + 20,
            ),
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
                Text(
                  'Catat Iuran Kas Murid',
                  style: const TextStyle(
                    fontSize: 17,
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
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: isDark
                        ? const Color(0xFF1C241D)
                        : const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Total Masuk: ${_formatRupiah(murid.totalDibayar)}',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF64748B),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        '•  Sisa: ${_formatRupiah(sisa)}',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: sisa > 0
                              ? AppColors.amberAccent
                              : AppColors.hadirTextLight,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Pilihan Cepat Nominal
                const Text(
                  'Pilihan Cepat Nominal:',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 6),
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  children: [
                    ...[5000, 10000, 20000, 50000].map((nom) {
                      return ActionChip(
                        label: Text(_formatRupiah(nom)),
                        onPressed: () {
                          setModalState(() {
                            nominalController.text = nom.toString();
                          });
                        },
                      );
                    }),
                    if (sisa > 0)
                      ActionChip(
                        backgroundColor: AppColors.primaryLight.withValues(
                          alpha: 0.15,
                        ),
                        label: Text(
                          'Lunasi Sisa (${_formatRupiah(sisa)})',
                          style: TextStyle(
                            color: AppColors.primaryLight,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        onPressed: () {
                          setModalState(() {
                            nominalController.text = sisa.toString();
                          });
                        },
                      ),
                  ],
                ),
                const SizedBox(height: 12),

                TextField(
                  controller: nominalController,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(
                    labelText: 'Nominal Pembayaran (Rp)',
                    prefixIcon: Icon(Icons.attach_money_rounded),
                  ),
                ),
                const SizedBox(height: 12),

                TextField(
                  controller: tanggalController,
                  decoration: InputDecoration(
                    labelText: 'Tanggal Pembayaran (YYYY-MM-DD)',
                    prefixIcon: const Icon(Icons.calendar_today_rounded),
                    suffixIcon: IconButton(
                      icon: const Icon(Icons.edit_calendar_rounded),
                      onPressed: () async {
                        final picked = await showDatePicker(
                          context: context,
                          initialDate: DateTime.now(),
                          firstDate: DateTime.now().subtract(
                            const Duration(days: 90),
                          ),
                          lastDate: DateTime.now(),
                        );
                        if (picked != null) {
                          setModalState(() {
                            tanggalController.text = DateHelper.toYmd(picked);
                          });
                        }
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 20),

                ElevatedButton(
                  onPressed: isSaving
                      ? null
                      : () async {
                          final jumlah = num.tryParse(
                            nominalController.text.trim(),
                          );
                          if (jumlah == null || jumlah <= 0) return;

                          setModalState(() => isSaving = true);
                          HapticHelper.medium();

                          final success = await context
                              .read<KasProvider>()
                              .bayarKas(
                                muridId: murid.muridId,
                                jumlahBayar: jumlah,
                                tanggalBayar: tanggalController.text.trim(),
                              );

                          if (ctx.mounted) {
                            Navigator.pop(ctx);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  success
                                      ? 'Pembayaran kas ${murid.nama} sebesar ${_formatRupiah(jumlah)} berhasil dicatat!'
                                      : 'Gagal mencatat kas santri.',
                                ),
                                backgroundColor: success
                                    ? AppColors.primaryLight
                                    : AppColors.roseDanger,
                              ),
                            );
                          }
                        },
                  child: isSaving
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text('Simpan Pembayaran Kas'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // 2.1 MODAL EDIT BAYAR KAS
  // =========================================================================
  void _openEditBayarSheet(MuridKasItem murid, RiwayatBayarKasItem riwayat) {
    HapticHelper.light();
    final nominalController = TextEditingController(
      text: riwayat.jumlahBayar.toInt().toString(),
    );
    final tanggalController = TextEditingController(text: riwayat.tanggalBayar);
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).viewInsets.bottom + 20,
            ),
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
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: AppColors.skyBlueAccent.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(
                        Icons.edit_note_rounded,
                        color: AppColors.skyBlueAccent,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Edit Pembayaran Kas',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            '${murid.nama} (NISM: ${murid.nism})',
                            style: TextStyle(
                              fontSize: 11,
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

                // Pilihan Cepat Nominal
                const Text(
                  'Pilihan Cepat Nominal:',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 6),
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  children: [5000, 10000, 20000, 50000].map((nom) {
                    return ActionChip(
                      label: Text(_formatRupiah(nom)),
                      onPressed: () {
                        setModalState(() {
                          nominalController.text = nom.toString();
                        });
                      },
                    );
                  }).toList(),
                ),
                const SizedBox(height: 12),

                TextField(
                  controller: nominalController,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(
                    labelText: 'Nominal Pembayaran (Rp)',
                    prefixIcon: Icon(Icons.attach_money_rounded),
                  ),
                ),
                const SizedBox(height: 12),

                TextField(
                  controller: tanggalController,
                  decoration: InputDecoration(
                    labelText: 'Tanggal Pembayaran (YYYY-MM-DD)',
                    prefixIcon: const Icon(Icons.calendar_today_rounded),
                    suffixIcon: IconButton(
                      icon: const Icon(Icons.edit_calendar_rounded),
                      onPressed: () async {
                        final initial =
                            DateTime.tryParse(tanggalController.text) ??
                            DateTime.now();
                        final picked = await showDatePicker(
                          context: context,
                          initialDate: initial,
                          firstDate: DateTime.now().subtract(
                            const Duration(days: 120),
                          ),
                          lastDate: DateTime.now(),
                        );
                        if (picked != null) {
                          setModalState(() {
                            tanggalController.text = DateHelper.toYmd(picked);
                          });
                        }
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 20),

                ElevatedButton(
                  onPressed: isSaving
                      ? null
                      : () async {
                          final jumlah = num.tryParse(
                            nominalController.text.trim(),
                          );
                          if (jumlah == null || jumlah <= 0) return;

                          setModalState(() => isSaving = true);
                          HapticHelper.medium();

                          final success = await context
                              .read<KasProvider>()
                              .updateBayarKas(
                                id: riwayat.id,
                                muridId: murid.muridId,
                                jumlahBayar: jumlah,
                                tanggalBayar: tanggalController.text.trim(),
                              );

                          if (ctx.mounted) {
                            Navigator.pop(ctx);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  success
                                      ? 'Pembayaran kas ${murid.nama} berhasil diperbarui menjadi ${_formatRupiah(jumlah)}!'
                                      : 'Gagal memperbarui pembayaran kas.',
                                ),
                                backgroundColor: success
                                    ? AppColors.primaryLight
                                    : AppColors.roseDanger,
                              ),
                            );
                          }
                        },
                  child: isSaving
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text('Simpan Perubahan'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // 3. MODAL RIWAYAT CICILAN KAS SANTRI
  // =========================================================================
  void _openRiwayatSheet(MuridKasItem murid) {
    HapticHelper.light();
    context.read<KasProvider>().fetchRiwayatMurid(murid.muridId);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Consumer<KasProvider>(
        builder: (context, provider, _) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(context).size.height * 0.75,
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
                Text(
                  'Riwayat Iuran Kas',
                  style: const TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  '${murid.nama} (Total Masuk: ${_formatRupiah(murid.totalDibayar)} / Target: ${_formatRupiah(murid.targetKas)})',
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                ),
                const SizedBox(height: 16),
                Expanded(
                  child: provider.isLoading
                      ? const ShimmerLoadingList(count: 3)
                      : provider.riwayatSantri.isEmpty
                      ? const Center(
                          child: Text('Belum ada riwayat pembayaran kas.'),
                        )
                      : ListView.builder(
                          itemCount: provider.riwayatSantri.length,
                          itemBuilder: (context, index) {
                            final r = provider.riwayatSantri[index];
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
                                          _formatRupiah(r.jumlahBayar),
                                          style: const TextStyle(
                                            fontSize: 14,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          r.hariTanggal ?? r.tanggalBayar,
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: isDark
                                                ? const Color(0xFF8D9387)
                                                : const Color(0xFF73796E),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 4,
                                        ),
                                        decoration: BoxDecoration(
                                          color: r.isDisetor
                                              ? (isDark
                                                    ? const Color(0xFF0F2313)
                                                    : const Color(0xFFE8F5E9))
                                              : (isDark
                                                    ? const Color(0xFF382305)
                                                    : const Color(0xFFFEF3C7)),
                                          borderRadius: BorderRadius.circular(
                                            8,
                                          ),
                                        ),
                                        child: Text(
                                          r.isDisetor
                                              ? '✓ Disetor'
                                              : '● Di Wali',
                                          style: TextStyle(
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                            color: r.isDisetor
                                                ? AppColors.hadirTextLight
                                                : AppColors.amberAccent,
                                          ),
                                        ),
                                      ),
                                      if (!r.isDisetor) ...[
                                        const SizedBox(width: 4),
                                        IconButton(
                                          icon: const Icon(
                                            Icons.edit_outlined,
                                            size: 18,
                                            color: AppColors.skyBlueAccent,
                                          ),
                                          tooltip: 'Edit Pembayaran',
                                          onPressed: () {
                                            _openEditBayarSheet(murid, r);
                                          },
                                        ),
                                        IconButton(
                                          icon: const Icon(
                                            Icons.delete_outline_rounded,
                                            size: 18,
                                            color: AppColors.roseDanger,
                                          ),
                                          tooltip: 'Batalkan Pembayaran',
                                          onPressed: () async {
                                            final confirm = await showDialog<bool>(
                                              context: context,
                                              builder: (c) => AlertDialog(
                                                title: const Text(
                                                  'Batalkan Pembayaran Kas?',
                                                ),
                                                content: Text(
                                                  'Yakin ingin membatalkan pembayaran ${_formatRupiah(r.jumlahBayar)} tanggal ${r.hariTanggal ?? r.tanggalBayar}?',
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
                                                              AppColors
                                                                  .roseDanger,
                                                        ),
                                                    onPressed: () =>
                                                        Navigator.pop(c, true),
                                                    child: const Text('Hapus'),
                                                  ),
                                                ],
                                              ),
                                            );
                                            if (confirm == true &&
                                                context.mounted) {
                                              await context
                                                  .read<KasProvider>()
                                                  .hapusBayarKas(
                                                    id: r.id,
                                                    muridId: murid.muridId,
                                                  );
                                            }
                                          },
                                        ),
                                      ],
                                    ],
                                  ),
                                ],
                              ),
                            );
                          },
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
  // 4. MODAL SETOR KAS KE TABUNGAN MADRASAH
  // =========================================================================
  void _openSetorSheet() {
    HapticHelper.light();
    final kas = context.read<KasProvider>();
    final ringkasan = kas.ringkasan;
    if (ringkasan == null) return;

    final sisaDiWali = ringkasan.sisaDiTanganWali;
    if (sisaDiWali <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Tidak ada uang kas fisik di tangan Wali untuk diajukan setor.',
          ),
          backgroundColor: AppColors.amberAccent,
        ),
      );
      return;
    }

    final nominalController = TextEditingController(
      text: sisaDiWali.toInt().toString(),
    );
    final tanggalController = TextEditingController(
      text: DateHelper.toYmd(DateTime.now()),
    );
    final keteranganController = TextEditingController();
    int? selectedPenerimaId = kas.penerimaList.isNotEmpty
        ? kas.penerimaList.first.id
        : null;
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).viewInsets.bottom + 24,
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
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppColors.skyBlueAccent.withValues(
                            alpha: 0.12,
                          ),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(
                          Icons.account_balance_rounded,
                          color: AppColors.skyBlueAccent,
                          size: 20,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Setor ke Tabungan Madrasah',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              'Ruangan ${ringkasan.namaRuangan} (${ringkasan.levelNama})',
                              style: TextStyle(
                                fontSize: 11,
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

                  // Info Tabungan Tujuan & Sisa Fisik
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1E281F)
                          : const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: AppColors.primaryLight.withValues(alpha: 0.3),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(
                              Icons.info_outline_rounded,
                              color: AppColors.primaryLight,
                              size: 18,
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'Uang Fisik Kas di Tangan Wali:',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.primaryLight,
                                ),
                              ),
                            ),
                            Text(
                              _formatRupiah(sisaDiWali),
                              style: TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.w900,
                                color: AppColors.primaryLight,
                              ),
                            ),
                          ],
                        ),
                        if (ringkasan.tabungan != null) ...[
                          const SizedBox(height: 6),
                          const Divider(height: 1),
                          const SizedBox(height: 6),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'Tujuan: Rek. ${ringkasan.tabungan!.nomorRekening}',
                                style: TextStyle(
                                  fontSize: 10.5,
                                  color: isDark
                                      ? Colors.white70
                                      : Colors.black54,
                                ),
                              ),
                              Text(
                                'Saldo: ${_formatRupiah(ringkasan.tabungan!.saldo)}',
                                style: TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Catatan Alur Verifikasi
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF2C2411)
                          : const Color(0xFFFFFBEB),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: AppColors.amberAccent.withValues(alpha: 0.3),
                      ),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(
                          Icons.hourglass_empty_rounded,
                          size: 16,
                          color: AppColors.amberAccent,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Pengajuan setoran akan berstatus "Menunggu Verifikasi" hingga fisik uang diterima & disetujui Admin/Petugas Tabungan di Web Backend.',
                            style: TextStyle(
                              fontSize: 10.5,
                              color: isDark
                                  ? const Color(0xFFFDE68A)
                                  : const Color(0xFF92400E),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Pilihan Cepat
                  const Text(
                    'Pilihan Cepat Nominal Setor:',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  Wrap(
                    spacing: 8,
                    runSpacing: 6,
                    children: [
                      ...[
                        50000,
                        100000,
                        200000,
                      ].where((n) => n <= sisaDiWali).map((nom) {
                        return ActionChip(
                          label: Text(_formatRupiah(nom)),
                          onPressed: () {
                            setModalState(() {
                              nominalController.text = nom.toString();
                            });
                          },
                        );
                      }),
                      ActionChip(
                        backgroundColor: AppColors.primaryLight.withValues(
                          alpha: 0.15,
                        ),
                        label: Text(
                          'Setor Semua (${_formatRupiah(sisaDiWali)})',
                          style: TextStyle(
                            color: AppColors.primaryLight,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        onPressed: () {
                          setModalState(() {
                            nominalController.text = sisaDiWali
                                .toInt()
                                .toString();
                          });
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  TextField(
                    controller: nominalController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Jumlah yang Disetorkan (Rp)',
                      prefixIcon: Icon(Icons.payments_outlined),
                    ),
                  ),
                  const SizedBox(height: 12),

                  TextField(
                    controller: tanggalController,
                    decoration: InputDecoration(
                      labelText: 'Tanggal Setoran (YYYY-MM-DD)',
                      prefixIcon: const Icon(Icons.calendar_today_rounded),
                      suffixIcon: IconButton(
                        icon: const Icon(Icons.edit_calendar_rounded),
                        onPressed: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: DateTime.now(),
                            firstDate: DateTime.now().subtract(
                              const Duration(days: 90),
                            ),
                            lastDate: DateTime.now(),
                          );
                          if (picked != null) {
                            setModalState(() {
                              tanggalController.text = DateHelper.toYmd(picked);
                            });
                          }
                        },
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  if (kas.penerimaList.isNotEmpty)
                    DropdownButtonFormField<int>(
                      initialValue: selectedPenerimaId,
                      decoration: const InputDecoration(
                        labelText: 'Petugas / Penerima Verifikasi',
                        prefixIcon: Icon(Icons.person_outline_rounded),
                      ),
                      items: kas.penerimaList.map((p) {
                        return DropdownMenuItem<int>(
                          value: p.id,
                          child: Text('${p.name} (${p.role})'),
                        );
                      }).toList(),
                      onChanged: (val) {
                        setModalState(() => selectedPenerimaId = val);
                      },
                    ),
                  const SizedBox(height: 12),

                  TextField(
                    controller: keteranganController,
                    decoration: const InputDecoration(
                      labelText: 'Keterangan / Catatan Setoran (Opsional)',
                      hintText: 'Misal: Setoran Kas Bulan September',
                      prefixIcon: Icon(Icons.notes_rounded),
                    ),
                  ),
                  const SizedBox(height: 20),

                  ElevatedButton(
                    onPressed: isSaving
                        ? null
                        : () async {
                            final jumlah = num.tryParse(
                              nominalController.text.trim(),
                            );
                            if (jumlah == null || jumlah <= 0) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text('Nominal setoran tidak valid.'),
                                  backgroundColor: AppColors.roseDanger,
                                ),
                              );
                              return;
                            }
                            if (jumlah > sisaDiWali) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    'Nominal setoran melebihi sisa uang di tangan wali (${_formatRupiah(sisaDiWali)}).',
                                  ),
                                  backgroundColor: AppColors.roseDanger,
                                ),
                              );
                              return;
                            }

                            setModalState(() => isSaving = true);
                            HapticHelper.medium();

                            final success = await context
                                .read<KasProvider>()
                                .simpanSetoran(
                                  ruanganId: ringkasan.ruanganId,
                                  jumlahSetor: jumlah,
                                  tanggalSetor: tanggalController.text.trim(),
                                  penerimaId: selectedPenerimaId,
                                  keterangan: keteranganController.text.trim(),
                                );

                            if (ctx.mounted) {
                              Navigator.pop(ctx);
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    success
                                        ? 'Alhamdulillah, pengajuan setoran kas ${_formatRupiah(jumlah)} berhasil diajukan! Menunggu verifikasi petugas.'
                                        : 'Gagal mengajukan setoran kas.',
                                  ),
                                  backgroundColor: success
                                      ? AppColors.primaryLight
                                      : AppColors.roseDanger,
                                ),
                              );
                            }
                          },
                    child: isSaving
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Text('Ajukan Setoran ke Tabungan'),
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
  // 5. MODAL EDIT SETORAN KE TABUNGAN
  // =========================================================================
  void _openEditSetoranSheet(SetoranKasItem setoran) {
    HapticHelper.light();
    final nominalController = TextEditingController(
      text: setoran.jumlahSetor.toInt().toString(),
    );
    final tanggalController = TextEditingController(text: setoran.tanggalSetor);
    final keteranganController = TextEditingController(
      text: setoran.keterangan != '-' ? setoran.keterangan : '',
    );
    int? selectedPenerimaId = setoran.penerimaId;
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          final kas = context.watch<KasProvider>();

          return Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).viewInsets.bottom + 24,
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
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppColors.skyBlueAccent.withValues(
                            alpha: 0.12,
                          ),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(
                          Icons.edit_note_rounded,
                          color: AppColors.skyBlueAccent,
                          size: 20,
                        ),
                      ),
                      const SizedBox(width: 10),
                      const Expanded(
                        child: Text(
                          'Edit Pengajuan Setoran Kas',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  TextField(
                    controller: nominalController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Jumlah yang Disetorkan (Rp)',
                      prefixIcon: Icon(Icons.payments_outlined),
                    ),
                  ),
                  const SizedBox(height: 12),

                  TextField(
                    controller: tanggalController,
                    decoration: InputDecoration(
                      labelText: 'Tanggal Setoran (YYYY-MM-DD)',
                      prefixIcon: const Icon(Icons.calendar_today_rounded),
                      suffixIcon: IconButton(
                        icon: const Icon(Icons.edit_calendar_rounded),
                        onPressed: () async {
                          final initial =
                              DateTime.tryParse(tanggalController.text) ??
                              DateTime.now();
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: initial,
                            firstDate: DateTime.now().subtract(
                              const Duration(days: 120),
                            ),
                            lastDate: DateTime.now(),
                          );
                          if (picked != null) {
                            setModalState(() {
                              tanggalController.text = DateHelper.toYmd(picked);
                            });
                          }
                        },
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  if (kas.penerimaList.isNotEmpty)
                    DropdownButtonFormField<int>(
                      initialValue:
                          selectedPenerimaId ?? kas.penerimaList.first.id,
                      decoration: const InputDecoration(
                        labelText: 'Petugas / Penerima Verifikasi',
                        prefixIcon: Icon(Icons.person_outline_rounded),
                      ),
                      items: kas.penerimaList.map((p) {
                        return DropdownMenuItem<int>(
                          value: p.id,
                          child: Text('${p.name} (${p.role})'),
                        );
                      }).toList(),
                      onChanged: (val) {
                        setModalState(() => selectedPenerimaId = val);
                      },
                    ),
                  const SizedBox(height: 12),

                  TextField(
                    controller: keteranganController,
                    decoration: const InputDecoration(
                      labelText: 'Keterangan / Catatan Setoran (Opsional)',
                      prefixIcon: Icon(Icons.notes_rounded),
                    ),
                  ),
                  const SizedBox(height: 20),

                  ElevatedButton(
                    onPressed: isSaving
                        ? null
                        : () async {
                            final jumlah = num.tryParse(
                              nominalController.text.trim(),
                            );
                            if (jumlah == null || jumlah <= 0) return;

                            setModalState(() => isSaving = true);
                            HapticHelper.medium();

                            final success = await context
                                .read<KasProvider>()
                                .updateSetoran(
                                  id: setoran.id,
                                  ruanganId: setoran.ruanganId,
                                  jumlahSetor: jumlah,
                                  tanggalSetor: tanggalController.text.trim(),
                                  penerimaId: selectedPenerimaId,
                                  keterangan: keteranganController.text.trim(),
                                );

                            if (ctx.mounted) {
                              Navigator.pop(ctx);
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    success
                                        ? 'Pengajuan setoran kas berhasil diperbarui menjadi ${_formatRupiah(jumlah)}!'
                                        : 'Gagal memperbarui setoran kas.',
                                  ),
                                  backgroundColor: success
                                      ? AppColors.primaryLight
                                      : AppColors.roseDanger,
                                ),
                              );
                            }
                          },
                    child: isSaving
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Text('Simpan Perubahan Pengajuan'),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final kas = context.watch<KasProvider>();
    final roomList = kas.ringkasan?.ruanganList ?? [];

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Kas Ruangan',
        subtitleText: kas.ringkasan?.namaRuangan ?? 'Kelas Binaan',
        actions: [
          CircularIconButton(
            icon: Icons.tune_rounded,
            tooltip: 'Pengaturan Target Kas',
            iconColor: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            onPressed: _openPengaturanSheet,
          ),
        ],
      ),
      body: Column(
        children: [
          // 0. Pemilih Ruangan (Jika Wali memiliki >1 Ruangan)
          if (roomList.length > 1) ...[
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
              child: Container(
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
                      'Pilih Kelas: ',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<int>(
                          value: _selectedRuanganId ?? kas.ringkasan?.ruanganId,
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
                              context.read<KasProvider>().fetchRingkasan(
                                ruanganId: newId,
                              );
                              context.read<KasProvider>().fetchRiwayatSetoran(
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
            ),
          ],

          // 1. Navigation Segmented Tab Bar (Bayar vs Setor)
          SegmentedTabBar(
            selectedIndex: _tabController.index,
            onTabChanged: (idx) {
              _tabController.animateTo(idx);
              setState(() {});
            },
            items: [
              SegmentedTabItem(
                activeIcon: Icons.payments_rounded,
                inactiveIcon: Icons.payments_outlined,
                label: 'Bayar Kas',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
              SegmentedTabItem(
                activeIcon: Icons.account_balance_rounded,
                inactiveIcon: Icons.account_balance_outlined,
                label: 'Setor ke Tabungan',
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
                _buildBayarTabView(isDark, kas),
                _buildSetorTabView(isDark, kas),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // TAB VIEW 1: BAYAR KAS SANTRI
  // =========================================================================
  Widget _buildBayarTabView(bool isDark, KasProvider kas) {
    final filteredList = kas.muridList.where((m) {
      final matchQuery =
          m.nama.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          m.nism.contains(_searchQuery);
      if (_filterStatus == 'Lunas') {
        return matchQuery && m.status == 'Lunas';
      } else if (_filterStatus == 'Belum Lunas') {
        return matchQuery && m.status != 'Lunas';
      }
      return matchQuery;
    }).toList();

    return RefreshIndicator(
      onRefresh: () async => _loadData(),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 6, 16, 40),
        children: [
          // Ringkasan Finansial Card
          if (kas.ringkasan != null)
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
                              Icons.account_balance_wallet_rounded,
                              color: AppColors.primaryLight,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 10),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Kas ${kas.ringkasan!.namaRuangan}',
                                style: const TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              Text(
                                kas.ringkasan!.levelNama,
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
                          '${kas.ringkasan!.totalMurid} Murid',
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

                  // Tabungan Kas Ruangan Account Chip
                  if (kas.ringkasan?.tabungan != null) ...[
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF0F2313)
                            : const Color(0xFFE8F5E9),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: isDark
                              ? const Color(0xFF1E3A20)
                              : const Color(0xFFC8E6C9),
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            Icons.account_balance_rounded,
                            size: 16,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Rek. Tabungan: ${kas.ringkasan!.tabungan!.nomorRekening} (Saldo: ${_formatRupiah(kas.ringkasan!.tabungan!.saldo)})',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.hadirTextLight.withValues(
                                alpha: 0.15,
                              ),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              kas.ringkasan!.tabungan!.status,
                              style: const TextStyle(
                                fontSize: 9.5,
                                fontWeight: FontWeight.bold,
                                color: AppColors.hadirTextLight,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],

                  const SizedBox(height: 16),
                  Row(
                    children: [
                      _buildSummaryItem(
                        'Terkumpul',
                        _formatRupiah(kas.ringkasan!.totalTerkumpul),
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
                        'Di Tabungan',
                        _formatRupiah(kas.ringkasan!.totalSudahDisetor),
                        AppColors.skyBlueAccent,
                        isDark,
                        onTap: () {
                          _tabController.animateTo(1);
                        },
                      ),
                      Container(
                        height: 36,
                        width: 1,
                        color: isDark
                            ? AppColors.outlineDark
                            : AppColors.outlineLight,
                      ),
                      _buildSummaryItem(
                        'Menunggu Verif',
                        _formatRupiah(kas.ringkasan!.totalMenungguVerifikasi),
                        AppColors.amberAccent,
                        isDark,
                        onTap: () {
                          _tabController.animateTo(1);
                        },
                      ),
                      Container(
                        height: 36,
                        width: 1,
                        color: isDark
                            ? AppColors.outlineDark
                            : AppColors.outlineLight,
                      ),
                      _buildSummaryItem(
                        'Di Tangan Wali',
                        _formatRupiah(kas.ringkasan!.sisaDiTanganWali),
                        kas.ringkasan!.sisaDiTanganWali > 0
                            ? AppColors.roseDanger
                            : Colors.grey,
                        isDark,
                        onTap: _openSetorSheet,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          const SizedBox(height: 16),

          // Search & Filter Bar
          Row(
            children: [
              Expanded(
                child: TextField(
                  decoration: InputDecoration(
                    hintText: 'Cari murid...',
                    prefixIcon: const Icon(Icons.search_rounded, size: 20),
                    contentPadding: const EdgeInsets.symmetric(
                      vertical: 8,
                      horizontal: 12,
                    ),
                    isDense: true,
                  ),
                  onChanged: (val) {
                    setState(() => _searchQuery = val);
                  },
                ),
              ),
              const SizedBox(width: 8),
              DropdownButton<String>(
                value: _filterStatus,
                underline: const SizedBox(),
                items: const [
                  DropdownMenuItem(value: 'Semua', child: Text('Semua')),
                  DropdownMenuItem(value: 'Lunas', child: Text('Lunas')),
                  DropdownMenuItem(
                    value: 'Belum Lunas',
                    child: Text('Belum Lunas'),
                  ),
                ],
                onChanged: (val) {
                  if (val != null) setState(() => _filterStatus = val);
                },
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Murid List
          if (kas.isLoading)
            const ShimmerLoadingList(count: 4, height: 86)
          else if (filteredList.isEmpty)
            const GlassCard(
              padding: EdgeInsets.all(24),
              child: Center(child: Text('Tidak ada murid ditemukan.')),
            )
          else
            ...filteredList.map((m) {
              final isLunas = m.status == 'Lunas';
              final progress = m.targetKas > 0
                  ? (m.totalDibayar / m.targetKas).clamp(0.0, 1.0)
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
                                      'NISM: ${m.nism} • ${isPutra ? "Putra" : "Putri"}',
                                      style: TextStyle(
                                        fontSize: 11,
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
                                : (isDark
                                      ? const Color(0xFF382305)
                                      : const Color(0xFFFEF3C7)),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            m.status,
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
                    const SizedBox(height: 10),

                    // Progress Bar
                    ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: LinearProgressIndicator(
                        value: progress,
                        minHeight: 6,
                        backgroundColor: isDark
                            ? const Color(0xFF202720)
                            : const Color(0xFFE5E7EB),
                        valueColor: AlwaysStoppedAnimation(
                          isLunas
                              ? AppColors.hadirTextLight
                              : AppColors.amberAccent,
                        ),
                      ),
                    ),
                    const SizedBox(height: 6),

                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Masuk: ${_formatRupiah(m.totalDibayar)} / ${_formatRupiah(m.targetKas)}',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            if (!isLunas && m.sisaTunggakan > 0)
                              Text(
                                'Sisa: ${_formatRupiah(m.sisaTunggakan)}',
                                style: const TextStyle(
                                  fontSize: 10.5,
                                  color: AppColors.amberAccent,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                          ],
                        ),
                        Row(
                          children: [
                            TextButton(
                              onPressed: () => _openRiwayatSheet(m),
                              style: TextButton.styleFrom(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 2,
                                ),
                                minimumSize: Size.zero,
                                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                              ),
                              child: const Text(
                                'Riwayat',
                                style: TextStyle(fontSize: 11),
                              ),
                            ),
                            const SizedBox(width: 6),
                            ElevatedButton(
                              onPressed: () => _openBayarSheet(m),
                              style: ElevatedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                minimumSize: Size.zero,
                                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                              ),
                              child: const Text(
                                '+ Bayar',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
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
  // TAB VIEW 2: SETORAN KE TABUNGAN MADRASAH
  // =========================================================================
  Widget _buildSetorTabView(bool isDark, KasProvider kas) {
    final riwayat = kas.riwayatSetoran;
    final ringkasan = kas.ringkasan;
    final sisaDiWali = ringkasan?.sisaDiTanganWali ?? 0;
    final tabungan = ringkasan?.tabungan;

    return RefreshIndicator(
      onRefresh: () async => _loadData(),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 6, 16, 40),
        children: [
          // Setoran Summary Card
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
                            color: AppColors.skyBlueAccent.withValues(
                              alpha: 0.12,
                            ),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(
                            Icons.account_balance_rounded,
                            color: AppColors.skyBlueAccent,
                            size: 20,
                          ),
                        ),
                        const SizedBox(width: 10),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Tabungan Kas Ruangan',
                              style: TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              ringkasan?.namaRuangan ?? 'Kelas Binaan',
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
                    if (tabungan != null)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF0F2313)
                              : const Color(0xFFE8F5E9),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          tabungan.status,
                          style: TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.bold,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                      ),
                  ],
                ),

                // Info Rekening Tabungan Kas Ruangan
                if (tabungan != null) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: isDark
                            ? const [Color(0xFF1B381E), Color(0xFF0F2313)]
                            : [AppColors.primaryLight, const Color(0xFF1B6A35)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'SALDO TABUNGAN KAS',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: 9.5,
                                fontWeight: FontWeight.bold,
                                letterSpacing: 0.5,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              _formatRupiah(tabungan.saldo),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 18,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ],
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            const Text(
                              'No. Rekening',
                              style: TextStyle(
                                color: Colors.white60,
                                fontSize: 9.5,
                              ),
                            ),
                            Text(
                              tabungan.nomorRekening,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                fontFamily: 'monospace',
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  if (tabungan.totalTarik > 0) ...[
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: AppColors.skyBlueAccent.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: AppColors.skyBlueAccent.withValues(
                            alpha: 0.25,
                          ),
                        ),
                      ),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.info_outline_rounded,
                            size: 14,
                            color: AppColors.skyBlueAccent,
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              'Penarikan kas dari tabungan (${_formatRupiah(tabungan.totalTarik)}) telah dikembalikan ke saldo fisik di tangan Wali.',
                              style: const TextStyle(
                                fontSize: 10.5,
                                color: AppColors.skyBlueAccent,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],

                const SizedBox(height: 16),
                Row(
                  children: [
                    _buildSummaryItem(
                      'Uang di Tangan Wali',
                      _formatRupiah(sisaDiWali),
                      sisaDiWali > 0
                          ? AppColors.roseDanger
                          : AppColors.primaryLight,
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
                      'Menunggu Verif',
                      _formatRupiah(riwayat?.totalMenungguVerifikasi ?? 0),
                      AppColors.amberAccent,
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
                      'Masuk Tabungan',
                      _formatRupiah(riwayat?.totalDisetor ?? 0),
                      AppColors.primaryLight,
                      isDark,
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Tombol Setor Baru
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: sisaDiWali > 0 ? _openSetorSheet : null,
                    icon: const Icon(Icons.payments_rounded, size: 18),
                    label: Text(
                      sisaDiWali > 0
                          ? 'Ajukan Setor ke Tabungan (${_formatRupiah(sisaDiWali)})'
                          : 'Semua Uang Kas Sudah Masuk / Diajukan',
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.skyBlueAccent,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // Segmented Switcher untuk Riwayat Setoran vs Penarikan
          Container(
            padding: const EdgeInsets.all(4),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () {
                      HapticHelper.selection();
                      setState(() => _riwayatTabType = 0);
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      decoration: BoxDecoration(
                        color: _riwayatTabType == 0
                            ? (isDark ? const Color(0xFF334155) : Colors.white)
                            : Colors.transparent,
                        borderRadius: BorderRadius.circular(9),
                        boxShadow: _riwayatTabType == 0
                            ? [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.05),
                                  blurRadius: 4,
                                  offset: const Offset(0, 2),
                                ),
                              ]
                            : null,
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.arrow_downward_rounded,
                            size: 15,
                            color: _riwayatTabType == 0
                                ? AppColors.skyBlueAccent
                                : (isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E)),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            'Setoran Kas (${riwayat?.list.length ?? 0})',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: _riwayatTabType == 0
                                  ? FontWeight.bold
                                  : FontWeight.w500,
                              color: _riwayatTabType == 0
                                  ? (isDark
                                        ? Colors.white
                                        : const Color(0xFF1E293B))
                                  : (isDark
                                        ? const Color(0xFF8D9387)
                                        : const Color(0xFF73796E)),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 4),
                Expanded(
                  child: GestureDetector(
                    onTap: () {
                      HapticHelper.selection();
                      setState(() => _riwayatTabType = 1);
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      decoration: BoxDecoration(
                        color: _riwayatTabType == 1
                            ? (isDark ? const Color(0xFF334155) : Colors.white)
                            : Colors.transparent,
                        borderRadius: BorderRadius.circular(9),
                        boxShadow: _riwayatTabType == 1
                            ? [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.05),
                                  blurRadius: 4,
                                  offset: const Offset(0, 2),
                                ),
                              ]
                            : null,
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.arrow_upward_rounded,
                            size: 15,
                            color: _riwayatTabType == 1
                                ? AppColors.amberAccent
                                : (isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E)),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            'Penarikan Kas (${riwayat?.penarikanList.length ?? 0})',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: _riwayatTabType == 1
                                  ? FontWeight.bold
                                  : FontWeight.w500,
                              color: _riwayatTabType == 1
                                  ? (isDark
                                        ? Colors.white
                                        : const Color(0xFF1E293B))
                                  : (isDark
                                        ? const Color(0xFF8D9387)
                                        : const Color(0xFF73796E)),
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

          // Tampilan Konten sesuai Tab: Setoran (0) vs Penarikan (1)
          if (_riwayatTabType == 0) ...[
            // Daftar Riwayat Setoran
            if (kas.isLoading)
              const ShimmerLoadingList(count: 3, height: 90)
            else if (riwayat == null || riwayat.list.isEmpty)
              const GlassCard(
                padding: EdgeInsets.all(24),
                child: Center(
                  child: Text('Belum ada riwayat setoran ke tabungan.'),
                ),
              )
            else
              ...riwayat.list.map((s) {
                final isDiterima = s.isDiterima;
                final isMenunggu = s.isMenungguVerifikasi;
                final isDitolak = s.isDitolak;

                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            _formatRupiah(s.jumlahSetor),
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w900,
                              color: isDiterima
                                  ? AppColors.primaryLight
                                  : (isMenunggu
                                        ? AppColors.amberAccent
                                        : AppColors.roseDanger),
                            ),
                          ),
                          // Status Badge
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: isDiterima
                                  ? (isDark
                                        ? const Color(0xFF0F2313)
                                        : const Color(0xFFE8F5E9))
                                  : (isMenunggu
                                        ? (isDark
                                              ? const Color(0xFF382305)
                                              : const Color(0xFFFEF3C7))
                                        : (isDark
                                              ? const Color(0xFF380C14)
                                              : const Color(0xFFFFE4E6))),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              isDiterima
                                  ? '✓ Masuk Tabungan'
                                  : (isMenunggu
                                        ? '⏳ Menunggu Verifikasi'
                                        : '✕ Ditolak'),
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: isDiterima
                                    ? AppColors.hadirTextLight
                                    : (isMenunggu
                                          ? AppColors.amberAccent
                                          : AppColors.roseDanger),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Tanggal Pengajuan: ${s.hariTanggal ?? s.tanggalSetor}',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF73796E),
                        ),
                      ),

                      if (isDiterima)
                        Text(
                          'Diverifikasi: ${s.diverifikasiOlehNama ?? s.penerimaNama}${s.diverifikasiPada != null ? " • ${s.diverifikasiPada}" : ""}',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: isDark
                                ? const Color(0xFF86EFAC)
                                : const Color(0xFF1B6A35),
                          ),
                        )
                      else if (isMenunggu)
                        Text(
                          'Petugas Dituju: ${s.penerimaNama}',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: isDark
                                ? const Color(0xFFC3C8BC)
                                : const Color(0xFF43483E),
                          ),
                        )
                      else if (isDitolak)
                        Text(
                          'Ditolak oleh: ${s.diverifikasiOlehNama ?? s.penerimaNama}',
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: AppColors.roseDanger,
                          ),
                        ),

                      if (s.catatanVerifikasi != null &&
                          s.catatanVerifikasi!.isNotEmpty)
                        Container(
                          margin: const EdgeInsets.only(top: 6),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? const Color(0xFF380C14)
                                : const Color(0xFFFFE4E6),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            'Catatan Verifikator: ${s.catatanVerifikasi}',
                            style: const TextStyle(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w600,
                              color: AppColors.roseDanger,
                            ),
                          ),
                        ),

                      if (s.keterangan.isNotEmpty && s.keterangan != '-')
                        Padding(
                          padding: const EdgeInsets.only(top: 4),
                          child: Text(
                            'Catatan Wali: ${s.keterangan}',
                            style: TextStyle(
                              fontSize: 10.5,
                              fontStyle: FontStyle.italic,
                              color: isDark
                                  ? const Color(0xFF8D9387)
                                  : const Color(0xFF73796E),
                            ),
                          ),
                        ),

                      // Action buttons (only when Menunggu Verifikasi)
                      if (s.canEdit || s.canDelete) ...[
                        const SizedBox(height: 8),
                        const Divider(height: 1),
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            if (s.canEdit)
                              TextButton.icon(
                                icon: const Icon(
                                  Icons.edit_outlined,
                                  size: 15,
                                  color: AppColors.skyBlueAccent,
                                ),
                                label: const Text(
                                  'Edit',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: AppColors.skyBlueAccent,
                                  ),
                                ),
                                onPressed: () {
                                  _openEditSetoranSheet(s);
                                },
                              ),
                            if (s.canDelete) ...[
                              const SizedBox(width: 8),
                              TextButton.icon(
                                icon: const Icon(
                                  Icons.cancel_outlined,
                                  size: 15,
                                  color: AppColors.roseDanger,
                                ),
                                label: const Text(
                                  'Batalkan',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: AppColors.roseDanger,
                                  ),
                                ),
                                onPressed: () async {
                                  final kasProvider = context
                                      .read<KasProvider>();
                                  final confirm = await showDialog<bool>(
                                    context: context,
                                    builder: (c) => AlertDialog(
                                      title: const Text(
                                        'Batalkan Pengajuan Setoran?',
                                      ),
                                      content: Text(
                                        'Yakin ingin membatalkan pengajuan setoran ${_formatRupiah(s.jumlahSetor)} tanggal ${s.hariTanggal ?? s.tanggalSetor}?\n\nStatus uang akan dikembalikan ke sisa di tangan Wali Kelas.',
                                      ),
                                      actions: [
                                        TextButton(
                                          onPressed: () =>
                                              Navigator.pop(c, false),
                                          child: const Text('Tutup'),
                                        ),
                                        ElevatedButton(
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor:
                                                AppColors.roseDanger,
                                          ),
                                          onPressed: () =>
                                              Navigator.pop(c, true),
                                          child: const Text('Batalkan Setoran'),
                                        ),
                                      ],
                                    ),
                                  );
                                  if (confirm == true) {
                                    await kasProvider.hapusSetoran(
                                      id: s.id,
                                      ruanganId: s.ruanganId,
                                    );
                                  }
                                },
                              ),
                            ],
                          ],
                        ),
                      ],
                    ],
                  ),
                );
              }),
          ] else ...[
            // Daftar Riwayat Penarikan
            if (kas.isLoading)
              const ShimmerLoadingList(count: 3, height: 90)
            else if (riwayat == null || riwayat.penarikanList.isEmpty)
              const GlassCard(
                padding: EdgeInsets.all(24),
                child: Center(
                  child: Text(
                    'Belum ada riwayat penarikan tunai dari tabungan kas ini.',
                    textAlign: TextAlign.center,
                  ),
                ),
              )
            else
              ...riwayat.penarikanList.map((p) {
                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 10),
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
                                padding: const EdgeInsets.all(6),
                                decoration: BoxDecoration(
                                  color: AppColors.amberAccent.withValues(
                                    alpha: 0.12,
                                  ),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Icon(
                                  Icons.arrow_outward_rounded,
                                  size: 16,
                                  color: AppColors.amberAccent,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Text(
                                '- ${_formatRupiah(p.nominal)}',
                                style: const TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w900,
                                  color: AppColors.amberAccent,
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
                              color: isDark
                                  ? const Color(0xFF382305)
                                  : const Color(0xFFFEF3C7),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              p.kategori,
                              style: const TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: AppColors.amberAccent,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Text(
                            p.kodeTransaksi,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              fontFamily: 'monospace',
                              color: isDark
                                  ? const Color(0xFFC3C8BC)
                                  : const Color(0xFF43483E),
                            ),
                          ),
                          const Text(
                            ' • ',
                            style: TextStyle(fontSize: 11, color: Colors.grey),
                          ),
                          Expanded(
                            child: Text(
                              p.hariTanggal ?? p.tanggal,
                              style: TextStyle(
                                fontSize: 11,
                                color: isDark
                                    ? const Color(0xFF8D9387)
                                    : const Color(0xFF73796E),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Petugas Pencair: ${p.petugasNama}',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF73796E),
                        ),
                      ),
                      if (p.keterangan.isNotEmpty && p.keterangan != '-')
                        Padding(
                          padding: const EdgeInsets.only(top: 4),
                          child: Text(
                            'Keperluan: ${p.keterangan}',
                            style: TextStyle(
                              fontSize: 10.5,
                              fontStyle: FontStyle.italic,
                              color: isDark
                                  ? const Color(0xFF8D9387)
                                  : const Color(0xFF73796E),
                            ),
                          ),
                        ),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF0F2313)
                              : const Color(0xFFE8F5E9),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.check_circle_outline_rounded,
                              size: 13,
                              color: AppColors.hadirTextLight,
                            ),
                            SizedBox(width: 4),
                            Text(
                              'Uang fisik kembali ke tangan Wali Ruangan',
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: AppColors.hadirTextLight,
                              ),
                            ),
                          ],
                        ),
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

  Widget _buildSummaryItem(
    String label,
    String value,
    Color color,
    bool isDark, {
    VoidCallback? onTap,
  }) {
    final content = Column(
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
    );

    return Expanded(
      child: onTap != null
          ? InkWell(
              onTap: onTap,
              borderRadius: BorderRadius.circular(8),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: content,
              ),
            )
          : content,
    );
  }
}
