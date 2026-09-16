import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/akademik_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class ReferensiPelanggaranScreen extends StatefulWidget {
  const ReferensiPelanggaranScreen({super.key});

  @override
  State<ReferensiPelanggaranScreen> createState() =>
      _ReferensiPelanggaranScreenState();
}

class _ReferensiPelanggaranScreenState
    extends State<ReferensiPelanggaranScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AkademikProvider>().fetchReferensiPelanggaran();
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
    final summary = akademik.referensiData?.summary;
    final list = akademik.referensiData?.list ?? [];

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Referensi Pelanggaran'),
      body: RefreshIndicator(
        onRefresh: () => akademik.fetchReferensiPelanggaran(),
        color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
          children: [
            // 1. Search Bar
            TextField(
              controller: _searchController,
              onChanged: (val) {
                akademik.setSearchReferensi(val);
              },
              decoration: InputDecoration(
                hintText: 'Cari nama pelanggaran...',
                prefixIcon: const Icon(Icons.search_rounded, size: 20),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          akademik.setSearchReferensi('');
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

            // 2. Filter Kategori Chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildFilterChip(
                    'Semua',
                    summary?.total ?? 0,
                    isDark,
                    akademik,
                  ),
                  const SizedBox(width: 8),
                  _buildFilterChip(
                    'Ringan',
                    summary?.ringan ?? 0,
                    isDark,
                    akademik,
                  ),
                  const SizedBox(width: 8),
                  _buildFilterChip(
                    'Sedang',
                    summary?.sedang ?? 0,
                    isDark,
                    akademik,
                  ),
                  const SizedBox(width: 8),
                  _buildFilterChip(
                    'Berat',
                    summary?.berat ?? 0,
                    isDark,
                    akademik,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // 3. Daftar Pelanggaran
            if (akademik.isLoadingReferensi)
              const ShimmerLoadingList(count: 6)
            else if (list.isEmpty)
              const GlassCard(
                padding: EdgeInsets.all(28),
                child: Center(
                  child: Text(
                    'Tidak ada referensi pelanggaran ditemukan.',
                    style: TextStyle(fontSize: 13),
                  ),
                ),
              )
            else
              ...list.map((p) {
                Color badgeBg;
                Color badgeText;

                switch (p.kategori) {
                  case 'Ringan':
                    badgeBg = isDark
                        ? const Color(0xFF0F2313)
                        : AppColors.primaryContainerLight;
                    badgeText = isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight;
                    break;
                  case 'Sedang':
                    badgeBg = isDark
                        ? const Color(0xFF382305)
                        : const Color(0xFFFEF3C7);
                    badgeText = isDark
                        ? AppColors.amberAccent
                        : const Color(0xFFD97706);
                    break;
                  case 'Berat':
                    badgeBg = isDark
                        ? const Color(0xFF380C14)
                        : const Color(0xFFFFE4E6);
                    badgeText = isDark
                        ? const Color(0xFFF87171)
                        : AppColors.roseDanger;
                    break;
                  default:
                    badgeBg = isDark
                        ? const Color(0xFF161E16)
                        : const Color(0xFFE5E7EB);
                    badgeText = isDark ? Colors.white : Colors.black87;
                }

                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ID Box
                      Container(
                        width: 36,
                        height: 36,
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF162016)
                              : const Color(0xFFF1F5F0),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          '#${p.id}',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),

                      // Nama Pelanggaran & Poin
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              p.namaPelanggaran,
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                height: 1.3,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 3,
                                  ),
                                  decoration: BoxDecoration(
                                    color: badgeBg,
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    'Kategori: ${p.kategori}',
                                    style: TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold,
                                      color: badgeText,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
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
                                    '${p.poinFormatted} Poin',
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
                          ],
                        ),
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

  Widget _buildFilterChip(
    String label,
    int count,
    bool isDark,
    AkademikProvider akademik,
  ) {
    final isSelected = akademik.selectedKategori == label;

    return InkWell(
      onTap: () {
        HapticHelper.light();
        akademik.setSelectedKategori(label);
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
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                color: isSelected
                    ? (isDark ? Colors.black : Colors.white)
                    : (isDark
                          ? const Color(0xFF8D9387)
                          : const Color(0xFF555D50)),
              ),
            ),
            if (count > 0) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                decoration: BoxDecoration(
                  color: isSelected
                      ? (isDark ? Colors.black26 : Colors.white24)
                      : (isDark
                            ? const Color(0xFF1D281D)
                            : const Color(0xFFE2E8F0)),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '$count',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: isSelected
                        ? (isDark ? Colors.black : Colors.white)
                        : (isDark ? Colors.white70 : Colors.black87),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
