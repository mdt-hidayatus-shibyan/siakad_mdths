import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/catatan_ustadz_model.dart';
import '../../../providers/catatan_ustadz_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';
import 'detail_catatan_ustadz_screen.dart';
import 'form_catatan_ustadz_screen.dart';

class CatatanUstadzScreen extends StatefulWidget {
  const CatatanUstadzScreen({super.key});

  @override
  State<CatatanUstadzScreen> createState() => _CatatanUstadzScreenState();
}

class _CatatanUstadzScreenState extends State<CatatanUstadzScreen> {
  final TextEditingController _searchCtrl = TextEditingController();

  final List<String> _kategoriFilterList = [
    'Semua',
    'Keluhan Murid',
    'KBM & Perkembangan Akademik',
    'Fasilitas Madrasah',
    'Evaluasi & Saran',
    'Lainnya',
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<CatatanUstadzProvider>().fetchCatatanList();
    });
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Color _getUrgencyColor(String urgensi, bool isDark) {
    switch (urgensi) {
      case 'Penting / Mendesak':
        return Colors.red;
      case 'Tinggi':
        return Colors.orange;
      case 'Sedang':
        return Colors.amber.shade700;
      case 'Rendah':
      default:
        return const Color(0xFF059669);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<CatatanUstadzProvider>();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Catatan & Keluhan',
        subtitleText: 'Laporan murid & madrasah oleh Ustadz',
        actions: [
          CircularIconButton(
            icon: Icons.refresh_rounded,
            iconSize: 20,
            iconColor: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            tooltip: 'Segarkan',
            onPressed: provider.isLoading
                ? null
                : () => provider.fetchCatatanList(),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          HapticHelper.medium();
          final catatanProvider = context.read<CatatanUstadzProvider>();
          final result = await Navigator.push<bool>(
            context,
            MaterialPageRoute(builder: (_) => const FormCatatanUstadzScreen()),
          );
          if (result == true) {
            catatanProvider.fetchCatatanList();
          }
        },
        icon: const Icon(Icons.edit_note_rounded, size: 22),
        label: const Text(
          'Catatan Baru',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: isDark
            ? AppColors.primaryDark
            : AppColors.primaryLight,
        foregroundColor: isDark ? Colors.black : Colors.white,
      ),
      body: RefreshIndicator(
        onRefresh: () => provider.fetchCatatanList(silent: true),
        color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        child: Column(
          children: [
            // Search Bar & Filter Header
            Container(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
              child: Column(
                children: [
                  // Search Field
                  TextField(
                    controller: _searchCtrl,
                    decoration: InputDecoration(
                      hintText: 'Cari judul, murid, isi catatan...',
                      prefixIcon: const Icon(Icons.search_rounded, size: 20),
                      suffixIcon: _searchCtrl.text.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear_rounded, size: 18),
                              onPressed: () {
                                _searchCtrl.clear();
                                provider.setSearch('');
                              },
                            )
                          : null,
                      filled: true,
                      fillColor: isDark
                          ? const Color(0xFF1B231B)
                          : Colors.white,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide(
                          color: isDark
                              ? AppColors.outlineDark
                              : const Color(0xFFE2E8F0),
                        ),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide(
                          color: isDark
                              ? AppColors.outlineDark
                              : const Color(0xFFE2E8F0),
                        ),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 12,
                      ),
                    ),
                    onChanged: (val) {
                      provider.setSearch(val.trim());
                    },
                  ),
                  const SizedBox(height: 10),

                  // Kategori Horizontal Filter Chips
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    physics: const BouncingScrollPhysics(),
                    child: Row(
                      children: _kategoriFilterList.map((kat) {
                        final isSelected = provider.selectedKategori == kat;
                        return Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: FilterChip(
                            selected: isSelected,
                            label: Text(kat),
                            labelStyle: TextStyle(
                              fontSize: 11,
                              fontWeight: isSelected
                                  ? FontWeight.bold
                                  : FontWeight.w600,
                              color: isSelected
                                  ? (isDark ? Colors.black : Colors.white)
                                  : (isDark ? Colors.white70 : Colors.black87),
                            ),
                            selectedColor: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                            backgroundColor: isDark
                                ? const Color(0xFF1A221A)
                                : const Color(0xFFF1F5F9),
                            checkmarkColor: isDark
                                ? Colors.black
                                : Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                              side: BorderSide(
                                color: isSelected
                                    ? Colors.transparent
                                    : (isDark
                                          ? AppColors.outlineDark
                                          : const Color(0xFFE2E8F0)),
                              ),
                            ),
                            onSelected: (_) {
                              HapticHelper.light();
                              provider.setKategori(kat);
                            },
                          ),
                        );
                      }).toList(),
                    ),
                  ),
                ],
              ),
            ),

            // Main List Content
            Expanded(
              child: provider.isLoading
                  ? const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 16),
                      child: ShimmerLoadingList(count: 4, height: 110),
                    )
                  : provider.catatanList.isEmpty
                  ? const EmptyStateView(
                      icon: Icons.speaker_notes_off_rounded,
                      title: 'Belum Ada Catatan',
                      description:
                          'Gunakan tombol "Catatan Baru" di bawah untuk mencatat keluhan murid atau aspirasi madrasah.',
                    )
                  : ListView.separated(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 80),
                      itemCount: provider.catatanList.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 12),
                      itemBuilder: (ctx, idx) {
                        final item = provider.catatanList[idx];
                        return _buildCatatanCard(item, isDark);
                      },
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCatatanCard(CatatanUstadzItem item, bool isDark) {
    final urgencyColor = _getUrgencyColor(item.tingkatUrgensi, isDark);

    return InkWell(
      onTap: () async {
        HapticHelper.light();
        final catatanProvider = context.read<CatatanUstadzProvider>();
        final result = await Navigator.push<bool>(
          context,
          MaterialPageRoute(
            builder: (_) => DetailCatatanUstadzScreen(catatan: item),
          ),
        );
        if (result == true) {
          catatanProvider.fetchCatatanList();
        }
      },
      borderRadius: BorderRadius.circular(20),
      child: GlassCard(
        padding: const EdgeInsets.all(16),
        borderRadius: 20,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Row 1: Badges (Kategori & Urgensi)
            Row(
              children: [
                // Kategori Chip
                Container(
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
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color:
                          (isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight)
                              .withValues(alpha: 0.25),
                    ),
                  ),
                  child: Text(
                    item.kategori,
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                  ),
                ),
                const SizedBox(width: 6),

                // Urgensi Badge
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: urgencyColor.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: urgencyColor.withValues(alpha: 0.25),
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.shield_outlined,
                        size: 11,
                        color: urgencyColor,
                      ),
                      const SizedBox(width: 3),
                      Text(
                        item.tingkatUrgensi,
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: urgencyColor,
                        ),
                      ),
                    ],
                  ),
                ),
                const Spacer(),

                // Status Dibaca Admin
                if (item.isSudahDibacaAdmin)
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 6,
                      vertical: 2,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFF059669).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.done_all_rounded,
                          size: 13,
                          color: Color(0xFF059669),
                        ),
                        SizedBox(width: 3),
                        Text(
                          'Dibaca',
                          style: TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF059669),
                          ),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 10),

            // Row 2: Judul Catatan
            Text(
              item.judul,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.bold,
                letterSpacing: -0.2,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 6),

            // Row 3: Target Murid (jika ada)
            if (item.targetTipe == 'murid' && item.muridNama != null) ...[
              Row(
                children: [
                  Icon(
                    Icons.person_rounded,
                    size: 14,
                    color: isDark
                        ? Colors.amber.shade400
                        : Colors.amber.shade800,
                  ),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      'Murid: ${item.muridNama} (NISM: ${item.muridNism ?? "-"})',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: isDark
                            ? Colors.amber.shade400
                            : Colors.amber.shade800,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
            ] else if (item.targetTipe == 'madrasah') ...[
              Row(
                children: [
                  Icon(
                    Icons.location_city_rounded,
                    size: 14,
                    color: isDark ? Colors.blue.shade400 : Colors.blue.shade700,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    item.ruanganNama != null && item.ruanganNama!.isNotEmpty
                        ? 'Sasaran: Madrasah • ${item.ruanganNama}'
                        : 'Sasaran: Madrasah & Fasilitas',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: isDark
                          ? Colors.blue.shade400
                          : Colors.blue.shade700,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
            ],

            // Row 4: Cuplikan Isi Catatan
            Text(
              item.isiCatatan,
              style: TextStyle(
                fontSize: 12,
                color: isDark ? Colors.white70 : Colors.black87,
                height: 1.35,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 10),

            // Row 5: Footer (Tanggal & Lampiran Icon)
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.schedule_rounded,
                      size: 12,
                      color: isDark ? Colors.white38 : Colors.black45,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      item.createdAt.length >= 10
                          ? item.createdAt.substring(0, 10)
                          : item.createdAt,
                      style: TextStyle(
                        fontSize: 10,
                        color: isDark ? Colors.white38 : Colors.black45,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                if (item.lampiranFoto != null && item.lampiranFoto!.isNotEmpty)
                  Row(
                    children: [
                      Icon(
                        Icons.image_outlined,
                        size: 13,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                      const SizedBox(width: 3),
                      Text(
                        'Foto',
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
              ],
            ),
          ],
        ),
      ),
    );
  }
}
