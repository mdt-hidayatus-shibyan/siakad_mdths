import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_motion.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/akademik_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class MataPelajaranScreen extends StatefulWidget {
  const MataPelajaranScreen({super.key});

  @override
  State<MataPelajaranScreen> createState() => _MataPelajaranScreenState();
}

class _MataPelajaranScreenState extends State<MataPelajaranScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AkademikProvider>().fetchMataPelajaran();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final akademik = context.watch<AkademikProvider>();
    final levels = akademik.mapelData?.levels ?? [];
    final mapels = akademik.mapelData?.mataPelajaran ?? [];

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Katalog Mata Pelajaran'),
      body: RefreshIndicator(
        onRefresh: () => akademik.fetchMataPelajaran(),
        color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        child: ListView(
          padding: EdgeInsets.fromLTRB(
            16,
            12,
            16,
            40 + MediaQuery.of(context).padding.bottom,
          ),
          children: [
            // 1. Search Bar M3E
            Container(
              decoration: BoxDecoration(
                color: isDark
                    ? AppColors.surfaceContainerHighDark
                    : AppColors.surfaceContainerHighLight,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
                  width: 0.8,
                ),
              ),
              child: TextField(
                controller: _searchController,
                onChanged: (val) {
                  akademik.setSearchMapel(val);
                },
                decoration: InputDecoration(
                  hintText: 'Cari nama mapel, kode, atau kitab...',
                  hintStyle: TextStyle(
                    fontSize: 13,
                    color: isDark
                        ? const Color(0xFF64748B)
                        : const Color(0xFF94A3B8),
                  ),
                  prefixIcon: Icon(
                    Icons.search_rounded,
                    size: 20,
                    color: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                  suffixIcon: _searchController.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear_rounded, size: 18),
                          onPressed: () {
                            _searchController.clear();
                            akademik.setSearchMapel('');
                          },
                        )
                      : null,
                  filled: false,
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 13,
                  ),
                  border: InputBorder.none,
                ),
              ),
            ),
            const SizedBox(height: 14),

            // 2. Filter Level / Kelas Horisontal Chips
            if (levels.isNotEmpty)
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: levels.asMap().entries.map((entry) {
                    final index = entry.key;
                    final lvl = entry.value;
                    return Padding(
                      padding: EdgeInsets.only(left: index == 0 ? 0 : 8),
                      child: _buildLevelChip(
                        lvl.id,
                        lvl.namaLevel,
                        isDark,
                        akademik,
                      ),
                    );
                  }).toList(),
                ),
              ),
            const SizedBox(height: 16),

            // 3. Daftar Mata Pelajaran M3E
            if (akademik.isLoadingMapel)
              const ShimmerLoadingList(count: 6)
            else if (mapels.isEmpty)
              const GlassCard(
                padding: EdgeInsets.all(28),
                child: Center(
                  child: Text(
                    'Tidak ada mata pelajaran yang sesuai filter.',
                    style: TextStyle(fontSize: 13),
                  ),
                ),
              )
            else
              ...mapels.map((m) {
                return M3ScaleOnPress(
                  onTap: () {},
                  pressedScale: 0.98,
                  borderRadius: BorderRadius.circular(20),
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: isDark
                          ? AppColors.surfaceContainerDark
                          : AppColors.surfaceContainerLight,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: isDark
                            ? AppColors.outlineDark
                            : AppColors.outlineLight,
                        width: 0.8,
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Header Mapel: Level & Kelompok
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 9,
                                vertical: 3.5,
                              ),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? AppColors.primaryContainerDark
                                    : AppColors.primaryContainerLight,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                m.levelNama,
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 9,
                                vertical: 3.5,
                              ),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? const Color(0xFF241538)
                                    : const Color(0xFFF3E8FF),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '${m.kelompok} • ${m.kodeMapel}',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.violetAccent
                                      : const Color(0xFF6D28D9),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // Nama Mapel with Book Icon
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            Container(
                              width: 38,
                              height: 38,
                              decoration: BoxDecoration(
                                color: isDark
                                    ? AppColors.skyBlueAccent.withValues(
                                        alpha: 0.15,
                                      )
                                    : const Color(0xFFE0F2FE),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Icon(
                                Icons.menu_book_rounded,
                                size: 18,
                                color: isDark
                                    ? AppColors.skyBlueAccent
                                    : const Color(0xFF0284C7),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                m.namaMapel,
                                style: const TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: -0.2,
                                ),
                              ),
                            ),
                          ],
                        ),

                        // Referensi Kitab / Pengarang jika ada
                        if (m.referensi != null && m.referensi!.isNotEmpty) ...[
                          const SizedBox(height: 12),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 8,
                            ),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? AppColors.surfaceContainerHighDark
                                  : AppColors.surfaceContainerHighLight,
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                color: isDark
                                    ? AppColors.outlineDark
                                    : AppColors.outlineLight,
                                width: 0.6,
                              ),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Icon(
                                  Icons.auto_stories_rounded,
                                  size: 14,
                                  color: isDark
                                      ? AppColors.amberAccent
                                      : const Color(0xFFD97706),
                                ),
                                const SizedBox(width: 7),
                                Expanded(
                                  child: Text(
                                    'Kitab: ${m.referensi}${m.pengarang != null && m.pengarang!.isNotEmpty ? ' (${m.pengarang})' : ''}${m.penerbit != null && m.penerbit!.isNotEmpty ? ' • Penerbit: ${m.penerbit}' : ''}',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w500,
                                      color: isDark
                                          ? const Color(0xFF94A3B8)
                                          : const Color(0xFF64748B),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                );
              }),
          ],
        ),
      ),
    );
  }

  Widget _buildLevelChip(
    int levelId,
    String label,
    bool isDark,
    AkademikProvider akademik,
  ) {
    final isSelected = akademik.selectedLevelId == levelId;

    return M3ScaleOnPress(
      onTap: () {
        HapticHelper.light();
        akademik.setSelectedLevelId(levelId);
      },
      pressedScale: 0.94,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected
              ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
              : (isDark
                    ? AppColors.surfaceContainerHighDark
                    : AppColors.surfaceContainerHighLight),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected
                ? Colors.transparent
                : (isDark ? AppColors.outlineDark : AppColors.outlineLight),
            width: 0.8,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
            color: isSelected
                ? (isDark ? Colors.black : Colors.white)
                : (isDark ? const Color(0xFFCBD5E1) : const Color(0xFF475569)),
          ),
        ),
      ),
    );
  }
}
