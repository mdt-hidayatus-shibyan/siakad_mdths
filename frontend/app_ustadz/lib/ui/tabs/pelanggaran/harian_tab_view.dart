import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/pelanggaran_provider.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';
import 'catat_pelanggaran_sheet.dart';

class HarianTabView extends StatefulWidget {
  const HarianTabView({super.key});

  @override
  State<HarianTabView> createState() => _HarianTabViewState();
}

class _HarianTabViewState extends State<HarianTabView> {
  DateTime _currentDate = DateTime.now();
  int? _selectedRuanganId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  void _loadData() {
    final provider = context.read<PelanggaranProvider>();
    final ruanganList = provider.ruanganList;
    if (_selectedRuanganId == null && ruanganList.isNotEmpty) {
      _selectedRuanganId = ruanganList.first.id;
    }
    provider.fetchHarian(
      tanggal: DateHelper.toYmd(_currentDate),
      ruanganId: _selectedRuanganId,
    );
  }

  void _shiftDate(int days) {
    HapticHelper.selection();
    setState(() {
      _currentDate = _currentDate.add(Duration(days: days));
    });
    _loadData();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _currentDate,
      firstDate: DateTime(2024),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );
    if (picked != null && picked != _currentDate) {
      setState(() => _currentDate = picked);
      _loadData();
    }
  }

  void _openCatatSheet() {
    HapticHelper.light();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) =>
          CatatPelanggaranSheet(initialRuanganId: _selectedRuanganId),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<PelanggaranProvider>();
    final harianData = provider.harianData;
    final ruanganList = provider.ruanganList;
    final list = harianData?.list ?? [];

    if (_selectedRuanganId == null && ruanganList.isNotEmpty) {
      _selectedRuanganId = ruanganList.first.id;
    }

    return RefreshIndicator(
      onRefresh: () async => _loadData(),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 120),
        children: [
          // 1. DATE PICKER SELECTOR BAR
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF162016) : Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: isDark
                    ? const Color(0xFF263326)
                    : const Color(0xFFE2E8F0),
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                IconButton(
                  visualDensity: VisualDensity.compact,
                  icon: const Icon(Icons.chevron_left_rounded),
                  onPressed: () => _shiftDate(-1),
                ),
                GestureDetector(
                  onTap: _pickDate,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.calendar_today_rounded,
                        size: 14,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        DateHelper.formatTanggalIndo(_currentDate),
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  visualDensity: VisualDensity.compact,
                  icon: const Icon(Icons.chevron_right_rounded),
                  onPressed: () => _shiftDate(1),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // 2. FILTER RUANGAN CHIPS
          if (ruanganList.isNotEmpty)
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: ruanganList.map((r) {
                  final isSelected = _selectedRuanganId == r.id;
                  return Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: ChoiceChip(
                      label: Text(
                        r.namaRuangan,
                        style: const TextStyle(fontSize: 11),
                      ),
                      selected: isSelected,
                      onSelected: (selected) {
                        if (selected && _selectedRuanganId != r.id) {
                          setState(() => _selectedRuanganId = r.id);
                          _loadData();
                        }
                      },
                    ),
                  );
                }).toList(),
              ),
            ),
          const SizedBox(height: 14),

          // 3. STATISTIK RINGKASAN HARI INI
          Row(
            children: [
              Expanded(
                child: GlassCard(
                  padding: const EdgeInsets.symmetric(
                    vertical: 12,
                    horizontal: 10,
                  ),
                  child: Column(
                    children: [
                      Text(
                        '${harianData?.totalKasus ?? 0}',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w900,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Total Kasus',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: isDark
                              ? const Color(0xFF94A3B8)
                              : const Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: GlassCard(
                  padding: const EdgeInsets.symmetric(
                    vertical: 12,
                    horizontal: 10,
                  ),
                  child: Column(
                    children: [
                      Text(
                        '+${harianData?.totalPoin ?? 0}',
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w900,
                          color: AppColors.roseDanger,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Poin Sanksi',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: isDark
                              ? const Color(0xFF94A3B8)
                              : const Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: GlassCard(
                  padding: const EdgeInsets.symmetric(
                    vertical: 12,
                    horizontal: 10,
                  ),
                  child: Column(
                    children: [
                      Text(
                        '${harianData?.totalSantri ?? 0}',
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w900,
                          color: Colors.orange,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Murid Melanggar',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: isDark
                              ? const Color(0xFF94A3B8)
                              : const Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),

          // 4. HEADER DAFTAR KASUS & TOMBOL TAMBAH
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Catatan Kasus (${list.length})',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                ),
              ),
              TextButton.icon(
                onPressed: _openCatatSheet,
                icon: const Icon(Icons.add_rounded, size: 16),
                label: const Text(
                  'Catat Kasus',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                ),
                style: TextButton.styleFrom(
                  visualDensity: VisualDensity.compact,
                  foregroundColor: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),

          // 5. LIST PELANGGARAN HARIAN
          if (provider.isLoading)
            const ShimmerLoadingList(count: 3)
          else if (list.isEmpty)
            GlassCard(
              padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 20),
              child: Column(
                children: [
                  Icon(
                    Icons.verified_user_rounded,
                    size: 48,
                    color: AppColors.primaryLight.withValues(alpha: 0.6),
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    'Tidak Ada Pelanggaran',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Alhamdulillah, tidak ada catatan pelanggaran santri pada tanggal ini.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 11,
                      color: isDark
                          ? const Color(0xFF94A3B8)
                          : const Color(0xFF64748B),
                    ),
                  ),
                ],
              ),
            )
          else
            ...list.map((item) {
              Color katColor = AppColors.amberAccent;
              if (item.kategori == 'Sedang') katColor = Colors.orange;
              if (item.kategori == 'Berat') katColor = const Color(0xFFF43F5E);

              return GlassCard(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header Baris 1: Nama Santri & Badge Poin
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            item.muridNama,
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: katColor.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: katColor.withValues(alpha: 0.4),
                            ),
                          ),
                          child: Text(
                            '+${item.poinFormatted} Poin',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w900,
                              color: katColor,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 3),

                    // Subtitle: Kelas & NISM & Waktu
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 1.5,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? const Color(0xFF1E293B)
                                : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(5),
                          ),
                          child: Text(
                            item.ruanganNama,
                            style: const TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          'NISM: ${item.nism}',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark
                                ? const Color(0xFF94A3B8)
                                : const Color(0xFF64748B),
                          ),
                        ),
                        if (item.waktu != null) ...[
                          const SizedBox(width: 8),
                          Text(
                            '• ${item.waktu}',
                            style: TextStyle(
                              fontSize: 11,
                              color: isDark
                                  ? const Color(0xFF94A3B8)
                                  : const Color(0xFF64748B),
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 8),

                    // Nama Pelanggaran
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF1B241B)
                            : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 5,
                              vertical: 1.5,
                            ),
                            decoration: BoxDecoration(
                              color: katColor.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              '#${item.referensiId}',
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: katColor,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              item.pelanggaran,
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Keterangan / Kronologi
                    if (item.keterangan != null &&
                        item.keterangan!.isNotEmpty) ...[
                      const SizedBox(height: 6),
                      Text(
                        'Ket: ${item.keterangan}',
                        style: TextStyle(
                          fontSize: 11,
                          fontStyle: FontStyle.italic,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF73796E),
                        ),
                      ),
                    ],

                    const SizedBox(height: 6),
                    // Dicatat oleh
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Dicatat oleh: ${item.diinputOleh}',
                          style: TextStyle(
                            fontSize: 9.5,
                            color: isDark
                                ? const Color(0xFF64748B)
                                : const Color(0xFF94A3B8),
                          ),
                        ),
                        InkWell(
                          onTap: () async {
                            final confirm = await showDialog<bool>(
                              context: context,
                              builder: (ctx) => AlertDialog(
                                title: const Text('Hapus Catatan Kasus?'),
                                content: Text(
                                  'Hapus catatan pelanggaran untuk ${item.muridNama}?',
                                ),
                                actions: [
                                  TextButton(
                                    onPressed: () => Navigator.pop(ctx, false),
                                    child: const Text('Batal'),
                                  ),
                                  TextButton(
                                    onPressed: () => Navigator.pop(ctx, true),
                                    child: const Text(
                                      'Hapus',
                                      style: TextStyle(color: Colors.red),
                                    ),
                                  ),
                                ],
                              ),
                            );

                            if (confirm == true && context.mounted) {
                              await context
                                  .read<PelanggaranProvider>()
                                  .hapusPelanggaran(item.id);
                            }
                          },
                          child: const Padding(
                            padding: EdgeInsets.symmetric(
                              horizontal: 4,
                              vertical: 2,
                            ),
                            child: Icon(
                              Icons.delete_outline_rounded,
                              size: 16,
                              color: Color(0xFFF43F5E),
                            ),
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
}
