import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
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
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
          children: [
            // 1. Search Bar
            TextField(
              controller: _searchController,
              onChanged: (val) {
                akademik.setSearchMapel(val);
              },
              decoration: InputDecoration(
                hintText: 'Cari nama mapel, kode, atau kitab...',
                prefixIcon: const Icon(Icons.search_rounded, size: 20),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          akademik.setSearchMapel('');
                        },
                      )
                    : null,
                filled: true,
                fillColor: isDark
                    ? const Color(0xFF101710)
                    : const Color(0xFFF1F5F0),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 12,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide.none,
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

            // 3. Daftar Mata Pelajaran
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
                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header Mapel: Level & Kelompok
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? const Color(0xFF0F2313)
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
                              horizontal: 8,
                              vertical: 3,
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
                      const SizedBox(height: 10),

                      // Nama Mapel
                      Text(
                        m.namaMapel,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                        ),
                      ),

                      // Referensi Kitab / Pengarang jika ada
                      if (m.referensi != null && m.referensi!.isNotEmpty) ...[
                        const SizedBox(height: 8),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(
                              Icons.menu_book_rounded,
                              size: 15,
                              color: isDark
                                  ? AppColors.amberAccent
                                  : const Color(0xFFD97706),
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                'Kitab: ${m.referensi}${m.pengarang != null && m.pengarang!.isNotEmpty ? ' (${m.pengarang})' : ''}${m.penerbit != null && m.penerbit!.isNotEmpty ? ' • Penerbit: ${m.penerbit}' : ''}',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isDark
                                      ? const Color(0xFFB0B7A8)
                                      : const Color(0xFF555D50),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
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

    return InkWell(
      onTap: () {
        HapticHelper.light();
        akademik.setSelectedLevelId(levelId);
      },
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected
              ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
              : (isDark ? const Color(0xFF101710) : const Color(0xFFF1F5F0)),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
            color: isSelected
                ? (isDark ? Colors.black : Colors.white)
                : (isDark ? const Color(0xFF8D9387) : const Color(0xFF555D50)),
          ),
        ),
      ),
    );
  }
}
