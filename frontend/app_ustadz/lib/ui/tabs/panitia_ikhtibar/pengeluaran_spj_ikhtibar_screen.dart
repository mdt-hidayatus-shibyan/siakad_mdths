import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/panitia_ikhtibar_model.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/panitia_ikhtibar_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';

class PengeluaranSpjIkhtibarScreen extends StatefulWidget {
  const PengeluaranSpjIkhtibarScreen({super.key});

  @override
  State<PengeluaranSpjIkhtibarScreen> createState() =>
      _PengeluaranSpjIkhtibarScreenState();
}

class _PengeluaranSpjIkhtibarScreenState
    extends State<PengeluaranSpjIkhtibarScreen>
    with SingleTickerProviderStateMixin {
  TabController? _tabController;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  void _loadData() {
    final provider = context.read<PanitiaIkhtibarProvider>();
    provider.fetchPaguSpjRingkasan();
    provider.fetchSpjList();
  }

  @override
  void dispose() {
    _tabController?.dispose();
    super.dispose();
  }

  String _formatRupiah(num number) {
    final formatter = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );
    return formatter.format(number);
  }

  // =========================================================================
  // MODAL BOTTOM SHEET: INPUT SPJ BARU
  // =========================================================================
  void _openTambahSpjSheet() {
    HapticHelper.light();
    final authUser = context.read<AuthProvider>().user;
    final isBph = authUser?.isBphIkhtibar ?? false;
    final provider = context.read<PanitiaIkhtibarProvider>();
    final ringkasan = provider.paguSpjRingkasan;

    final judulCtrl = TextEditingController();
    final nominalCtrl = TextEditingController();
    final tokoCtrl = TextEditingController();
    final rincianCtrl = TextEditingController();
    DateTime selectedDate = DateTime.now();
    String metodeBayar = 'Tunai';
    int? selectedDivisiId = isBph
        ? (ringkasan?.daftarDivisi.isNotEmpty == true
              ? ringkasan!.daftarDivisi.first.id
              : null)
        : authUser?.divisiIkhtibarId;

    File? selectedFoto;
    final ImagePicker picker = ImagePicker();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          final bottomInset = MediaQuery.of(context).viewInsets.bottom;
          final systemBottom = MediaQuery.of(context).padding.bottom;
          final bottomPadding = bottomInset > 0
              ? (bottomInset + 20)
              : (systemBottom + 24);

          return Container(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(context).size.height * 0.9,
            ),
            padding: EdgeInsets.fromLTRB(20, 16, 20, bottomPadding),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E2129) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(24),
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.15),
                  blurRadius: 20,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Handle Bar
                  Center(
                    child: Container(
                      width: 44,
                      height: 4,
                      decoration: BoxDecoration(
                        color: isDark ? Colors.white24 : Colors.black12,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Header
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Input SPJ Pengeluaran Baru',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            isBph
                                ? 'Kepanitiaan Ikhtibar (Akses Penuh BPH)'
                                : 'Divisi: ${authUser?.namaDivisiIkhtibar ?? "-"}',
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark ? Colors.white60 : Colors.black54,
                            ),
                          ),
                        ],
                      ),
                      IconButton(
                        onPressed: () => Navigator.pop(ctx),
                        icon: const Icon(Icons.close_rounded),
                        visualDensity: VisualDensity.compact,
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Pilihan Divisi (Hanya jika BPH)
                  if (isBph &&
                      (ringkasan?.daftarDivisi.isNotEmpty ?? false)) ...[
                    const Text(
                      'Pilih Pos / Divisi Anggaran *',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<int>(
                      initialValue: selectedDivisiId,
                      isExpanded: true,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 10,
                        ),
                      ),
                      items: ringkasan!.daftarDivisi.map((d) {
                        return DropdownMenuItem<int>(
                          value: d.id,
                          child: Text(
                            '${d.namaDivisi} (Pagu: ${_formatRupiah(d.nominalPagu)})',
                            style: const TextStyle(fontSize: 13),
                          ),
                        );
                      }).toList(),
                      onChanged: (val) {
                        setModalState(() => selectedDivisiId = val);
                      },
                    ),
                    const SizedBox(height: 12),
                  ],

                  // Judul Pengeluaran
                  const Text(
                    'Judul Pengeluaran / Belanja *',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: judulCtrl,
                    decoration: InputDecoration(
                      hintText:
                          'Contoh: Hadiah Juara Lomba Adzan, Sewa Terop...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Nominal & Tanggal Nota
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Nominal (Rp) *',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: nominalCtrl,
                              keyboardType: TextInputType.number,
                              decoration: InputDecoration(
                                prefixText: 'Rp ',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 14,
                                  vertical: 12,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Tanggal Nota *',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 6),
                            InkWell(
                              onTap: () async {
                                final picked = await showDatePicker(
                                  context: context,
                                  initialDate: selectedDate,
                                  firstDate: DateTime(2025),
                                  lastDate: DateTime(2030),
                                );
                                if (picked != null) {
                                  setModalState(() => selectedDate = picked);
                                }
                              },
                              borderRadius: BorderRadius.circular(12),
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 13,
                                ),
                                decoration: BoxDecoration(
                                  border: Border.all(
                                    color: isDark
                                        ? Colors.white24
                                        : Colors.black26,
                                  ),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(
                                      Icons.calendar_today_rounded,
                                      size: 16,
                                    ),
                                    const SizedBox(width: 8),
                                    Text(
                                      DateFormat(
                                        'dd/MM/yyyy',
                                      ).format(selectedDate),
                                      style: const TextStyle(fontSize: 12),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Nama Toko & Metode Bayar
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Nama Toko / Penerima',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: tokoCtrl,
                              decoration: InputDecoration(
                                hintText: 'Nama toko/penjual...',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 14,
                                  vertical: 12,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Metode Bayar',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 6),
                            DropdownButtonFormField<String>(
                              initialValue: metodeBayar,
                              decoration: InputDecoration(
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 14,
                                  vertical: 10,
                                ),
                              ),
                              items: const [
                                DropdownMenuItem(
                                  value: 'Tunai',
                                  child: Text('Tunai'),
                                ),
                                DropdownMenuItem(
                                  value: 'Transfer',
                                  child: Text('Transfer'),
                                ),
                              ],
                              onChanged: (val) {
                                if (val != null) {
                                  setModalState(() => metodeBayar = val);
                                }
                              },
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Rincian Item Belanja
                  const Text(
                    'Rincian Item Belanja (Opsional)',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: rincianCtrl,
                    maxLines: 2,
                    decoration: InputDecoration(
                      hintText:
                          'Contoh: 3 buah trofi @Rp 50.000, 1 rim kertas piagam...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Upload Foto Bukti Nota
                  const Text(
                    'Foto Bukti Nota / Kwitansi',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  InkWell(
                    onTap: () async {
                      showModalBottomSheet(
                        context: context,
                        builder: (bctx) => SafeArea(
                          child: Wrap(
                            children: [
                              ListTile(
                                leading: const Icon(Icons.camera_alt_rounded),
                                title: const Text('Ambil Foto Kamera'),
                                onTap: () async {
                                  Navigator.pop(bctx);
                                  final XFile? photo = await picker.pickImage(
                                    source: ImageSource.camera,
                                    imageQuality: 80,
                                  );
                                  if (photo != null) {
                                    setModalState(() {
                                      selectedFoto = File(photo.path);
                                    });
                                  }
                                },
                              ),
                              ListTile(
                                leading: const Icon(
                                  Icons.photo_library_rounded,
                                ),
                                title: const Text('Pilih dari Galeri'),
                                onTap: () async {
                                  Navigator.pop(bctx);
                                  final XFile? image = await picker.pickImage(
                                    source: ImageSource.gallery,
                                    imageQuality: 80,
                                  );
                                  if (image != null) {
                                    setModalState(() {
                                      selectedFoto = File(image.path);
                                    });
                                  }
                                },
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      width: double.infinity,
                      height: 90,
                      decoration: BoxDecoration(
                        color: isDark
                            ? Colors.white10
                            : Colors.black.withValues(alpha: 0.04),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isDark ? Colors.white24 : Colors.black26,
                          style: BorderStyle.solid,
                        ),
                      ),
                      child: selectedFoto != null
                          ? Stack(
                              alignment: Alignment.center,
                              children: [
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: Image.file(
                                    selectedFoto!,
                                    width: double.infinity,
                                    height: 90,
                                    fit: BoxFit.cover,
                                  ),
                                ),
                                Positioned(
                                  right: 6,
                                  top: 6,
                                  child: CircleAvatar(
                                    radius: 14,
                                    backgroundColor: Colors.black54,
                                    child: IconButton(
                                      icon: const Icon(
                                        Icons.close,
                                        size: 14,
                                        color: Colors.white,
                                      ),
                                      onPressed: () => setModalState(
                                        () => selectedFoto = null,
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            )
                          : Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.add_a_photo_rounded,
                                  size: 26,
                                  color: isDark
                                      ? Colors.white60
                                      : Colors.black54,
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Ketuk untuk mengunggah foto nota fisik',
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
                  const SizedBox(height: 20),

                  // Tombol Simpan SPJ
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        final judul = judulCtrl.text.trim();
                        final rawNominal = nominalCtrl.text.replaceAll(
                          RegExp(r'[^0-9]'),
                          '',
                        );
                        final nominal = double.tryParse(rawNominal);

                        if (judul.isEmpty) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Judul pengeluaran wajib diisi'),
                            ),
                          );
                          return;
                        }
                        if (nominal == null || nominal <= 0) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Nominal pengeluaran tidak valid'),
                            ),
                          );
                          return;
                        }

                        HapticHelper.medium();
                        Navigator.pop(ctx);

                        final success = await provider.simpanSpj(
                          divisiId: selectedDivisiId,
                          judulPengeluaran: judul,
                          nominal: nominal,
                          tanggalNota: DateFormat(
                            'yyyy-MM-dd',
                          ).format(selectedDate),
                          namaTokoPenerima: tokoCtrl.text.trim(),
                          metodePembayaran: metodeBayar,
                          rincianItem: rincianCtrl.text.trim(),
                          buktiNotaFile: selectedFoto,
                        );

                        if (!ctx.mounted) return;
                        Navigator.of(ctx).pop();

                        if (!mounted) return;
                        final messenger = ScaffoldMessenger.of(context);
                        if (success) {
                          messenger.showSnackBar(
                            SnackBar(
                              content: const Text(
                                'SPJ pengeluaran berhasil disimpan!',
                              ),
                              backgroundColor: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        } else {
                          messenger.showSnackBar(
                            SnackBar(
                              content: Text(
                                provider.paguSpjError ?? 'Gagal menyimpan SPJ',
                              ),
                              backgroundColor: Colors.redAccent,
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        }
                      },
                      icon: const Icon(Icons.check_circle_outline_rounded),
                      label: const Text(
                        'Simpan Laporan SPJ',
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
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

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final authUser = context.watch<AuthProvider>().user;
    final isBph = authUser?.isBphIkhtibar ?? false;
    final provider = context.watch<PanitiaIkhtibarProvider>();
    final ringkasan = provider.paguSpjRingkasan;

    if (_tabController == null && isBph) {
      _tabController = TabController(length: 2, vsync: this);
      _tabController!.addListener(() {
        if (!_tabController!.indexIsChanging) {
          setState(() {});
        }
      });
    }

    return Scaffold(
      appBar: CustomAppBar(
        titleText: isBph
            ? 'Pagu & SPJ Ikhtibar'
            : 'SPJ Divisi (${authUser?.namaDivisiIkhtibar ?? "-"})',
        showBackButton: true,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openTambahSpjSheet,
        backgroundColor: isDark
            ? AppColors.primaryDark
            : AppColors.primaryLight,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded),
        label: const Text('Input SPJ'),
      ),
      body: RefreshIndicator(
        onRefresh: () async => _loadData(),
        child: isBph
            ? _buildBphLayout(provider, ringkasan, isDark)
            : _buildDivisiLayout(provider, ringkasan, isDark),
      ),
    );
  }

  // =========================================================================
  // VIEW KHUSUS BPH
  // =========================================================================
  Widget _buildBphLayout(
    PanitiaIkhtibarProvider provider,
    PaguSpjRingkasanModel? ringkasan,
    bool isDark,
  ) {
    return NestedScrollView(
      headerSliverBuilder: (context, innerBoxIsScrolled) {
        return [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
              child: Column(
                children: [
                  // Ringkasan Pagu Eksekutif
                  if (ringkasan != null) ...[
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
                                    padding: const EdgeInsets.all(8),
                                    decoration: BoxDecoration(
                                      color: AppColors.amberAccent.withValues(
                                        alpha: 0.15,
                                      ),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(
                                      Icons.account_balance_wallet_rounded,
                                      color: AppColors.amberAccent,
                                      size: 20,
                                    ),
                                  ),
                                  const SizedBox(width: 10),
                                  const Text(
                                    'Pagu & Serapan Anggaran Ikhtibar',
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
                                  color: AppColors.amberAccent.withValues(
                                    alpha: 0.12,
                                  ),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: const Text(
                                  'BPH View',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.amberAccent,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 14),

                          Row(
                            children: [
                              Expanded(
                                child: _buildMetricCol(
                                  'Total Pagu 16 Pos',
                                  _formatRupiah(ringkasan.totalPagu ?? 0),
                                  isDark,
                                ),
                              ),
                              Expanded(
                                child: _buildMetricCol(
                                  'Dana Tersalurkan',
                                  _formatRupiah(
                                    ringkasan.totalTersalurkan ?? 0,
                                  ),
                                  isDark,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Row(
                            children: [
                              Expanded(
                                child: _buildMetricCol(
                                  'Realisasi SPJ',
                                  _formatRupiah(ringkasan.totalSpj ?? 0),
                                  isDark,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                              Expanded(
                                child: _buildMetricCol(
                                  'Sisa Saldo Kas Utama',
                                  _formatRupiah(ringkasan.sisaKasUtama ?? 0),
                                  isDark,
                                  color: AppColors.amberAccent,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                  ],

                  // Tab Selector (SegmentedTabBar konsisten M3)
                  if (_tabController != null)
                    SegmentedTabBar(
                      margin: const EdgeInsets.only(top: 8, bottom: 2),
                      selectedIndex: _tabController!.index,
                      onTabChanged: (idx) {
                        _tabController!.animateTo(idx);
                        setState(() {});
                      },
                      items: [
                        SegmentedTabItem(
                          activeIcon: Icons.account_balance_wallet_rounded,
                          inactiveIcon: Icons.account_balance_wallet_outlined,
                          label: 'Daftar Divisi',
                          activeColor: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                        SegmentedTabItem(
                          activeIcon: Icons.receipt_long_rounded,
                          inactiveIcon: Icons.receipt_long_outlined,
                          label: 'Daftar SPJ Belanja',
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
      body: _tabController == null
          ? const SizedBox()
          : TabBarView(
              controller: _tabController,
              children: [
                _buildListDivisiPagu(ringkasan?.daftarDivisi ?? [], isDark),
                _buildListSpj(provider, isDark),
              ],
            ),
    );
  }

  // =========================================================================
  // VIEW KHUSUS ANGGOTA DIVISI
  // =========================================================================
  Widget _buildDivisiLayout(
    PanitiaIkhtibarProvider provider,
    PaguSpjRingkasanModel? ringkasan,
    bool isDark,
  ) {
    final div = ringkasan?.divisi;

    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 80),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Hero Card Divisi
          if (div != null) ...[
            GlassCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            div.namaDivisi,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'PJ: ${div.penanggungJawab}',
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark ? Colors.white60 : Colors.black54,
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
                                  .withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          div.kode,
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

                  Row(
                    children: [
                      Expanded(
                        child: _buildMetricCol(
                          'Plafon Pagu',
                          _formatRupiah(div.nominalPagu),
                          isDark,
                        ),
                      ),
                      Expanded(
                        child: _buildMetricCol(
                          'Dana Diterima',
                          _formatRupiah(div.danaTersalurkan),
                          isDark,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(
                        child: _buildMetricCol(
                          'Belanja SPJ',
                          _formatRupiah(div.realisasiSpj),
                          isDark,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                      ),
                      Expanded(
                        child: _buildMetricCol(
                          'Sisa Kas di Tangan',
                          _formatRupiah(div.sisaKasDivisi),
                          isDark,
                          color: div.sisaKasDivisi > 0
                              ? AppColors.amberAccent
                              : Colors.grey,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
          ],

          const Text(
            'Riwayat Nota & Belanja SPJ Divisi',
            style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 10),

          // Daftar SPJ Divisi
          if (provider.isLoadingPaguSpj && provider.spjList.isEmpty)
            const ShimmerLoadingList(count: 3)
          else if (provider.spjList.isEmpty)
            Center(
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 40),
                child: Column(
                  children: [
                    Icon(
                      Icons.receipt_long_rounded,
                      size: 44,
                      color: isDark ? Colors.white24 : Colors.black26,
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Belum ada nota SPJ yang diinput untuk divisi ini',
                    ),
                  ],
                ),
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: provider.spjList.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (ctx, idx) =>
                  _buildSpjCard(provider.spjList[idx], isDark),
            ),
        ],
      ),
    );
  }

  Widget _buildListDivisiPagu(List<DivisiPaguItem> divisis, bool isDark) {
    if (divisis.isEmpty) {
      return const Center(child: Text('Belum ada daftar divisi'));
    }
    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 80),
      itemCount: divisis.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (ctx, idx) {
        final d = divisis[idx];
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
                      Text(
                        d.namaDivisi,
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  Text(
                    _formatRupiah(d.nominalPagu),
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              Text(
                'Koordinator: ${d.penanggungJawab}',
                style: TextStyle(
                  fontSize: 11,
                  color: isDark ? Colors.white60 : Colors.black54,
                ),
              ),
              const SizedBox(height: 8),

              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Distribusi: ${_formatRupiah(d.danaTersalurkan)}',
                    style: TextStyle(
                      fontSize: 11,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                  ),
                  Text(
                    'Pengeluaran: ${_formatRupiah(d.realisasiSpj)}',
                    style: TextStyle(
                      fontSize: 11,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                  ),
                  Text(
                    'Sisa Dana: ${_formatRupiah(d.sisaKasDivisi)}',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
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

  Widget _buildListSpj(PanitiaIkhtibarProvider provider, bool isDark) {
    if (provider.isLoadingPaguSpj && provider.spjList.isEmpty) {
      return const Padding(
        padding: EdgeInsets.all(16),
        child: ShimmerLoadingList(count: 4),
      );
    }
    if (provider.spjList.isEmpty) {
      return const Center(child: Text('Belum ada data SPJ'));
    }
    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 80),
      itemCount: provider.spjList.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (ctx, idx) => _buildSpjCard(provider.spjList[idx], isDark),
    );
  }

  Widget _buildSpjCard(SpjIkhtibarItem s, bool isDark) {
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
                  s.judulPengeluaran,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Text(
                _formatRupiah(s.nominal),
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '${s.namaDivisi} • ${s.tanggalNota ?? "-"}',
                style: TextStyle(
                  fontSize: 11,
                  color: isDark ? Colors.white60 : Colors.black54,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: isDark
                      ? Colors.white.withValues(alpha: 0.08)
                      : Colors.black.withValues(alpha: 0.05),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.person_outline,
                      size: 11,
                      color: isDark ? Colors.white70 : Colors.black54,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'Diinput: ${s.diinputOleh}',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                        color: isDark ? Colors.white70 : Colors.black87,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          if (s.rincianItem != null && s.rincianItem!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              'Rincian: ${s.rincianItem}',
              style: TextStyle(
                fontSize: 11,
                fontStyle: FontStyle.italic,
                color: isDark ? Colors.white70 : Colors.black87,
              ),
            ),
          ],
          const SizedBox(height: 4),
          Text(
            'Kode: ${s.kodeSpj} • Toko: ${s.namaTokoPenerima}',
            style: TextStyle(
              fontSize: 10,
              color: isDark ? Colors.white38 : Colors.black38,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMetricCol(
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
}
