import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/panitia_imni_model.dart';
import '../../../providers/panitia_imni_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class PengeluaranImniScreen extends StatefulWidget {
  const PengeluaranImniScreen({super.key});

  @override
  State<PengeluaranImniScreen> createState() => _PengeluaranImniScreenState();
}

class _PengeluaranImniScreenState extends State<PengeluaranImniScreen> {
  final TextEditingController _searchCtrl = TextEditingController();
  String _selectedKategori = 'Semua';

  final List<String> _kategoriOptions = [
    'Pra IMNI',
    'Saat IMNI',
    'Pasca IMNI (Wisuda)',
  ];

  Color _getKategoriColor(String kat) {
    if (kat.contains('Pra')) {
      return Colors.indigo;
    } else if (kat.contains('Saat')) {
      return Colors.teal;
    } else if (kat.contains('Pasca') || kat.contains('Wisuda')) {
      return Colors.purple;
    }
    return Colors.blue;
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  void _loadData() {
    final provider = context.read<PanitiaImniProvider>();
    provider.fetchPengeluaranRingkasan();
    provider.fetchPengeluaranList(
      kategori: _selectedKategori == 'Semua' ? null : _selectedKategori,
      search: _searchCtrl.text.trim(),
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
  // MODAL CATAT / EDIT PENGELUARAN
  // =========================================================================
  void _openFormPengeluaranSheet({PengeluaranImniItem? item}) {
    HapticHelper.light();
    final isEdit = (item != null);
    String kategori = item?.kategori ?? _kategoriOptions.first;
    final judulCtrl = TextEditingController(text: item?.judulPengeluaran ?? '');
    final nominalCtrl = TextEditingController(
      text: item != null ? item.nominal.toInt().toString() : '',
    );
    final penerimaCtrl = TextEditingController(text: item?.penerimaDana ?? '');
    final keteranganCtrl = TextEditingController(text: item?.keterangan ?? '');
    String metodePembayaran = item?.metodePembayaran ?? 'Tunai';
    DateTime selectedDate = item?.tanggalPengeluaran != null
        ? DateTime.tryParse(item!.tanggalPengeluaran!) ?? DateTime.now()
        : DateTime.now();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          final provider = context.watch<PanitiaImniProvider>();

          return Container(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(context).viewInsets.bottom + 20,
              left: 20,
              right: 20,
              top: 20,
            ),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.2),
                  blurRadius: 20,
                  offset: const Offset(0, -5),
                ),
              ],
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: isDark ? Colors.white24 : Colors.black12,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: (isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight)
                              .withValues(alpha: 0.12),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          isEdit ? Icons.edit_note_rounded : Icons.add_shopping_cart_rounded,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                          size: 24,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          isEdit ? 'Edit Pengeluaran IMNI' : 'Catat Pengeluaran Baru',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Divider(height: 1),
                  const SizedBox(height: 16),

                  // Kategori Dropdown
                  const Text(
                    'Kategori Pengeluaran *',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                    decoration: BoxDecoration(
                      border: Border.all(
                        color: isDark ? Colors.white24 : Colors.black26,
                      ),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        isExpanded: true,
                        value: kategori,
                        items: _kategoriOptions.map((k) {
                          return DropdownMenuItem(
                            value: k,
                            child: Text(k, style: const TextStyle(fontSize: 13)),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) {
                            setModalState(() {
                              kategori = val;
                            });
                          }
                        },
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Judul Pengeluaran
                  const Text(
                    'Judul / Uraian Pengeluaran *',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: judulCtrl,
                    decoration: InputDecoration(
                      hintText: 'Contoh: Penggandaan 150 Eks Naskah Soal',
                      prefixIcon: const Icon(Icons.description_outlined, size: 20),
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

                  // Nominal & Tanggal Row
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Nominal (Rp) *',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: nominalCtrl,
                              keyboardType: TextInputType.number,
                              decoration: InputDecoration(
                                hintText: '0',
                                prefixIcon: const Icon(Icons.attach_money_rounded, size: 20),
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
                              'Tanggal *',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 6),
                            InkWell(
                              onTap: () async {
                                final picked = await showDatePicker(
                                  context: context,
                                  initialDate: selectedDate,
                                  firstDate: DateTime(2020),
                                  lastDate: DateTime(2030),
                                );
                                if (picked != null) {
                                  setModalState(() {
                                    selectedDate = picked;
                                  });
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
                                    color: isDark ? Colors.white24 : Colors.black26,
                                  ),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.calendar_today_rounded, size: 16),
                                    const SizedBox(width: 8),
                                    Text(
                                      '${selectedDate.day.toString().padLeft(2, '0')}/${selectedDate.month.toString().padLeft(2, '0')}/${selectedDate.year}',
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
                  const SizedBox(height: 14),

                  // Penerima Dana
                  const Text(
                    'Penerima Dana / Vendor',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: penerimaCtrl,
                    decoration: InputDecoration(
                      hintText: 'Misal: Toko Berkah / Ustadz Ahmad',
                      prefixIcon: const Icon(Icons.storefront_outlined, size: 20),
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

                  // Metode Bayar
                  const Text(
                    'Metode Pembayaran',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: ['Tunai', 'Transfer', 'Lainnya'].map((m) {
                      final isSelected = (metodePembayaran == m);
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ChoiceChip(
                          label: Text(m),
                          selected: isSelected,
                          onSelected: (val) {
                            if (val) {
                              setModalState(() {
                                metodePembayaran = m;
                              });
                            }
                          },
                          selectedColor: (isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight)
                              .withValues(alpha: 0.2),
                          labelStyle: TextStyle(
                            color: isSelected
                                ? (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                : null,
                            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 14),

                  // Keterangan
                  const Text(
                    'Keterangan Tambahan',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: keteranganCtrl,
                    decoration: InputDecoration(
                      hintText: 'Catatan detail pengeluaran...',
                      prefixIcon: const Icon(Icons.notes_rounded, size: 20),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Tombol Simpan
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: provider.isSubmittingPengeluaran
                          ? null
                          : () async {
                              final judul = judulCtrl.text.trim();
                              final nom = double.tryParse(
                                nominalCtrl.text.replaceAll(RegExp(r'[^0-9]'), ''),
                              );
                              if (judul.isEmpty || nom == null || nom <= 0) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text('Lengkapi judul dan nominal pengeluaran!'),
                                  ),
                                );
                                return;
                              }

                              final dateStr =
                                  '${selectedDate.year}-${selectedDate.month.toString().padLeft(2, '0')}-${selectedDate.day.toString().padLeft(2, '0')}';

                              bool ok;
                              if (isEdit) {
                                ok = await provider.updatePengeluaran(
                                  id: item.id,
                                  kategori: kategori,
                                  judulPengeluaran: judul,
                                  nominal: nom,
                                  tanggalPengeluaran: dateStr,
                                  penerimaDana: penerimaCtrl.text.trim().isEmpty
                                      ? null
                                      : penerimaCtrl.text.trim(),
                                  metodePembayaran: metodePembayaran,
                                  keterangan: keteranganCtrl.text.trim().isEmpty
                                      ? null
                                      : keteranganCtrl.text.trim(),
                                );
                              } else {
                                ok = await provider.simpanPengeluaran(
                                  kategori: kategori,
                                  judulPengeluaran: judul,
                                  nominal: nom,
                                  tanggalPengeluaran: dateStr,
                                  penerimaDana: penerimaCtrl.text.trim().isEmpty
                                      ? null
                                      : penerimaCtrl.text.trim(),
                                  metodePembayaran: metodePembayaran,
                                  keterangan: keteranganCtrl.text.trim().isEmpty
                                      ? null
                                      : keteranganCtrl.text.trim(),
                                );
                              }

                              if (context.mounted) {
                                Navigator.pop(context);
                                if (ok) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(
                                        isEdit
                                            ? 'Pengeluaran berhasil diperbarui!'
                                            : 'Pengeluaran IMNI berhasil dicatat!',
                                      ),
                                      backgroundColor: Colors.green,
                                    ),
                                  );
                                }
                              }
                            },
                      icon: provider.isSubmittingPengeluaran
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Icon(Icons.check_circle_rounded),
                      label: Text(
                        provider.isSubmittingPengeluaran
                            ? 'Menyimpan...'
                            : (isEdit ? 'Perbarui Pengeluaran' : 'Simpan Pengeluaran'),
                        style: const TextStyle(fontWeight: FontWeight.bold),
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

  void _confirmHapusPengeluaran(PengeluaranImniItem item) {
    HapticHelper.warning();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus Pengeluaran?'),
        content: Text(
          'Apakah Anda yakin ingin menghapus pengeluaran "${item.judulPengeluaran}" senilai ${_formatRupiah(item.nominal)}?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final provider = context.read<PanitiaImniProvider>();
              final ok = await provider.hapusPengeluaran(item.id);
              if (mounted && ok) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Pengeluaran berhasil dihapus'),
                    backgroundColor: Colors.green,
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.redAccent),
            child: const Text('Hapus', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<PanitiaImniProvider>();
    final ringkasan = provider.pengeluaranRingkasan;

    return Scaffold(
      appBar: const CustomAppBar(
        titleText: 'Pengeluaran IMNI',
        subtitleText: 'Realisasi Biaya & Anggaran IMNI',
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openFormPengeluaranSheet(),
        backgroundColor: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded),
        label: const Text('Catat Pengeluaran', style: TextStyle(fontWeight: FontWeight.bold)),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          _loadData();
        },
        child: CustomScrollView(
          slivers: [
            // 1. STATISTIK SALDO KAS IMNI
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
                child: Column(
                  children: [
                    GlassCard(
                      padding: const EdgeInsets.all(18),
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
                                    'Sisa Saldo Kas IMNI',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E),
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    _formatRupiah(ringkasan?.sisaSaldo ?? 0),
                                    style: TextStyle(
                                      fontSize: 22,
                                      fontWeight: FontWeight.bold,
                                      color: (ringkasan?.sisaSaldo ?? 0) >= 0
                                          ? Colors.green
                                          : Colors.redAccent,
                                    ),
                                  ),
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color: ((ringkasan?.sisaSaldo ?? 0) >= 0
                                          ? Colors.green
                                          : Colors.redAccent)
                                      .withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  (ringkasan?.sisaSaldo ?? 0) >= 0 ? 'Surplus' : 'Defisit',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                    color: (ringkasan?.sisaSaldo ?? 0) >= 0
                                        ? Colors.green
                                        : Colors.redAccent,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 14),
                          const Divider(height: 1),
                          const SizedBox(height: 14),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Total Pemasukan Kas',
                                    style: TextStyle(
                                      fontSize: 11,
                                      color: isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E),
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    _formatRupiah(ringkasan?.totalPemasukan ?? 0),
                                    style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.green,
                                    ),
                                  ),
                                ],
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(
                                    'Total Pengeluaran Kas',
                                    style: TextStyle(
                                      fontSize: 11,
                                      color: isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E),
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    _formatRupiah(ringkasan?.totalPengeluaran ?? 0),
                                    style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.redAccent,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),

                    // Breakdown Kategori Horizontal Cards
                    if (ringkasan != null && ringkasan.breakdownKategori.isNotEmpty) ...[
                      const SizedBox(height: 12),
                      SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: ringkasan.breakdownKategori.map((b) {
                            final katColor = _getKategoriColor(b.kategori);
                            return Container(
                              margin: const EdgeInsets.only(right: 8),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: isDark ? const Color(0xFF161F16) : Colors.white,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                  color: katColor.withValues(alpha: 0.3),
                                ),
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 4,
                                    height: 24,
                                    decoration: BoxDecoration(
                                      color: katColor,
                                      borderRadius: BorderRadius.circular(2),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        b.kategori,
                                        style: TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.w600,
                                          color: isDark ? Colors.white70 : Colors.black87,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        _formatRupiah(b.total),
                                        style: const TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            );
                          }).toList(),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),

            // 2. SEARCH & FILTER KATEGORI CHIPS
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Column(
                  children: [
                    TextField(
                      controller: _searchCtrl,
                      onChanged: (val) {
                        provider.setSearchPengeluaran(val.trim());
                      },
                      decoration: InputDecoration(
                        hintText: 'Cari Judul, Kode, Penerima Dana...',
                        hintStyle: const TextStyle(fontSize: 13),
                        prefixIcon: const Icon(Icons.search_rounded, size: 20),
                        suffixIcon: _searchCtrl.text.isNotEmpty
                            ? IconButton(
                                icon: const Icon(Icons.clear, size: 18),
                                onPressed: () {
                                  _searchCtrl.clear();
                                  provider.setSearchPengeluaran('');
                                },
                              )
                            : null,
                        filled: true,
                        fillColor: isDark ? const Color(0xFF161F16) : Colors.white,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: BorderSide(
                            color: isDark ? Colors.white12 : Colors.black12,
                          ),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: BorderSide(
                            color: isDark ? Colors.white12 : Colors.black12,
                          ),
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 12,
                        ),
                      ),
                    ),
                    const SizedBox(height: 10),
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          ChoiceChip(
                            label: const Text('Semua Kategori'),
                            selected: _selectedKategori == 'Semua',
                            onSelected: (val) {
                              if (val) {
                                setState(() => _selectedKategori = 'Semua');
                                provider.setFilterKategori(null);
                              }
                            },
                            selectedColor: (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                .withValues(alpha: 0.2),
                          ),
                          const SizedBox(width: 6),
                          ..._kategoriOptions.map((k) {
                            final isSelected = (_selectedKategori == k);
                            return Padding(
                              padding: const EdgeInsets.only(right: 6),
                              child: ChoiceChip(
                                label: Text(k),
                                selected: isSelected,
                                onSelected: (val) {
                                  if (val) {
                                    setState(() => _selectedKategori = k);
                                    provider.setFilterKategori(k);
                                  }
                                },
                                selectedColor: (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                    .withValues(alpha: 0.2),
                              ),
                            );
                          }),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),
                  ],
                ),
              ),
            ),

            // 3. DAFTAR PENGELUARAN LIST
            if (provider.isLoadingPengeluaran)
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16),
                  child: ShimmerLoadingList(count: 5, height: 90),
                ),
              )
            else if (provider.pengeluaranList.isEmpty)
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(32),
                  child: Center(
                    child: Column(
                      children: [
                        Icon(
                          Icons.receipt_long_outlined,
                          size: 48,
                          color: isDark ? Colors.white24 : Colors.black26,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Belum ada data pengeluaran IMNI.',
                          style: TextStyle(
                            color: isDark ? const Color(0xFF8D9387) : const Color(0xFF73796E),
                            fontSize: 13,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 4, 16, 80),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final item = provider.pengeluaranList[index];
                      return _buildPengeluaranCard(context, item, isDark);
                    },
                    childCount: provider.pengeluaranList.length,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildPengeluaranCard(
    BuildContext context,
    PengeluaranImniItem item,
    bool isDark,
  ) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF161F16) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? Colors.white10 : Colors.black.withValues(alpha: 0.06),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: _getKategoriColor(item.kategori).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    item.kategori,
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: _getKategoriColor(item.kategori),
                    ),
                  ),
                ),
                Text(
                  item.tanggalFormat ?? item.tanggalPengeluaran ?? '-',
                  style: TextStyle(
                    fontSize: 11,
                    color: isDark ? const Color(0xFF8D9387) : const Color(0xFF73796E),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.judulPengeluaran,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Penerima: ${item.penerimaDana ?? "-"} • ${item.metodePembayaran}',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark ? const Color(0xFF8D9387) : const Color(0xFF73796E),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  _formatRupiah(item.nominal),
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: Colors.redAccent,
                  ),
                ),
              ],
            ),
            if (item.keterangan != null && item.keterangan!.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(
                'Catatan: ${item.keterangan}',
                style: TextStyle(
                  fontSize: 11,
                  fontStyle: FontStyle.italic,
                  color: isDark ? const Color(0xFF8D9387) : const Color(0xFF73796E),
                ),
              ),
            ],
            const SizedBox(height: 8),
            const Divider(height: 1),
            const SizedBox(height: 6),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Dicatat oleh: ${item.pencatatNama}',
                  style: TextStyle(
                    fontSize: 10,
                    color: isDark ? Colors.white38 : Colors.black38,
                  ),
                ),
                Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.edit_outlined, size: 18),
                      onPressed: () => _openFormPengeluaranSheet(item: item),
                      visualDensity: VisualDensity.compact,
                      tooltip: 'Edit',
                    ),
                    IconButton(
                      icon: const Icon(Icons.delete_outline, size: 18, color: Colors.redAccent),
                      onPressed: () => _confirmHapusPengeluaran(item),
                      visualDensity: VisualDensity.compact,
                      tooltip: 'Hapus',
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
