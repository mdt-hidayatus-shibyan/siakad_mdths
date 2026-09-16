import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/pelanggaran_provider.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class RiwayatTabView extends StatefulWidget {
  const RiwayatTabView({super.key});

  @override
  State<RiwayatTabView> createState() => _RiwayatTabViewState();
}

class _RiwayatTabViewState extends State<RiwayatTabView> {
  final _searchController = TextEditingController();
  String _selectedKategori = 'Semua';
  int? _selectedRuanganId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _loadData() {
    final provider = context.read<PelanggaranProvider>();
    final ruanganList = provider.ruanganList;
    if (_selectedRuanganId == null && ruanganList.isNotEmpty) {
      _selectedRuanganId = ruanganList.first.id;
    }
    final kategoriParam = _selectedKategori == 'Semua'
        ? null
        : _selectedKategori;
    provider.fetchRiwayat(
      search: _searchController.text.trim(),
      kategori: kategoriParam,
      ruanganId: _selectedRuanganId,
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<PelanggaranProvider>();
    final riwayatList = provider.riwayatList;
    final ruanganList = provider.ruanganList;

    if (_selectedRuanganId == null && ruanganList.isNotEmpty) {
      _selectedRuanganId = ruanganList.first.id;
    }

    return RefreshIndicator(
      onRefresh: () async => _loadData(),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 120),
        children: [
          // 1. SEARCH BAR
          TextField(
            controller: _searchController,
            decoration: InputDecoration(
              hintText: 'Cari nama santri, NISM, pelanggaran...',
              prefixIcon: const Icon(Icons.search_rounded, size: 20),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded, size: 18),
                      onPressed: () {
                        _searchController.clear();
                        _loadData();
                      },
                    )
                  : null,
              filled: true,
              fillColor: isDark ? const Color(0xFF162016) : Colors.white,
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 14,
                vertical: 10,
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide(
                  color: isDark
                      ? const Color(0xFF263326)
                      : const Color(0xFFE2E8F0),
                ),
              ),
            ),
            onSubmitted: (_) {
              HapticHelper.light();
              _loadData();
            },
          ),
          const SizedBox(height: 10),

          // 2. FILTER KATEGORI CHIPS
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                ...['Semua', 'Ringan', 'Sedang', 'Berat'].map((k) {
                  final isSelected = _selectedKategori == k;
                  return Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: ChoiceChip(
                      label: Text(k, style: const TextStyle(fontSize: 11)),
                      selected: isSelected,
                      onSelected: (selected) {
                        if (selected) {
                          setState(() => _selectedKategori = k);
                          _loadData();
                        }
                      },
                    ),
                  );
                }),
                const SizedBox(width: 6),
                if (ruanganList.isNotEmpty)
                  PopupMenuButton<int>(
                    tooltip: 'Filter Ruangan',
                    initialValue: _selectedRuanganId,
                    onSelected: (val) {
                      setState(() => _selectedRuanganId = val);
                      _loadData();
                    },
                    itemBuilder: (ctx) => [
                      ...ruanganList.map(
                        (r) => PopupMenuItem<int>(
                          value: r.id,
                          child: Text(
                            r.namaRuangan,
                            style: const TextStyle(fontSize: 12),
                          ),
                        ),
                      ),
                    ],
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color:
                            (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                .withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color:
                              (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                  .withValues(alpha: 0.4),
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.meeting_room_rounded,
                            size: 14,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            ruanganList
                                .firstWhere(
                                  (r) => r.id == _selectedRuanganId,
                                  orElse: () => ruanganList.first,
                                )
                                .namaRuangan,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                          ),
                          const SizedBox(width: 2),
                          Icon(
                            Icons.arrow_drop_down_rounded,
                            size: 16,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          // 3. DAFTAR RIWAYAT
          if (provider.isLoading)
            const ShimmerLoadingList(count: 3)
          else if (riwayatList.isEmpty)
            const GlassCard(
              padding: EdgeInsets.symmetric(vertical: 36, horizontal: 20),
              child: Center(
                child: Column(
                  children: [
                    Icon(
                      Icons.history_edu_rounded,
                      size: 44,
                      color: Color(0xFF8D9387),
                    ),
                    SizedBox(height: 10),
                    Text(
                      'Belum ada riwayat pelanggaran.',
                      style: TextStyle(fontSize: 13),
                    ),
                  ],
                ),
              ),
            )
          else
            ...riwayatList.map((item) {
              Color katColor = AppColors.amberAccent;
              if (item.kategori == 'Sedang') katColor = Colors.orange;
              if (item.kategori == 'Berat') katColor = const Color(0xFFF43F5E);

              return GlassCard(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header: Nama Santri & Badge Poin
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
                    const SizedBox(height: 4),

                    // Subtitle: NISM & Ruangan & Tanggal
                    Row(
                      children: [
                        if (item.ruanganNama != null) ...[
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
                              item.ruanganNama!,
                              style: const TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                        ],
                        Text(
                          'NISM: ${item.nism} • ${item.tanggal.isNotEmpty ? DateHelper.formatTanggalIndo(DateTime.tryParse(item.tanggal) ?? DateTime.now()) : item.tanggal}',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark
                                ? const Color(0xFF94A3B8)
                                : const Color(0xFF64748B),
                          ),
                        ),
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
                          if (item.referensiId != null) ...[
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
                          ],
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

                    // Keterangan
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
                    // Dicatat oleh & Hapus
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
                                title: const Text('Hapus Catatan Riwayat?'),
                                content: Text(
                                  'Hapus catatan kasus untuk ${item.muridNama}?',
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
