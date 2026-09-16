import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/pelanggaran_model.dart';
import '../../../providers/akademik_provider.dart';
import '../../../providers/dashboard_provider.dart';
import '../../widgets/child_switcher_bar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/glass_card.dart';
import 'hubungi_admin_screen.dart';

class BukuKasusScreen extends StatefulWidget {
  const BukuKasusScreen({super.key});

  @override
  State<BukuKasusScreen> createState() => _BukuKasusScreenState();
}

class _BukuKasusScreenState extends State<BukuKasusScreen> {
  String _selectedCategory = 'Semua';

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final dashboard = context.watch<DashboardProvider>();
    final selectedAnak = dashboard.selectedAnak;
    final akademik = context.watch<AkademikProvider>();
    final rekap = akademik.rekapPelanggaran;

    return Scaffold(
      backgroundColor: isDark ? AppColors.surfaceDark : AppColors.surfaceLight,
      appBar: CustomAppBar(
        titleText: 'Buku Kasus & Kedisiplinan',
        subtitleText: 'Riwayat Catatan Disiplin Murid',
        actions: [
          CircularIconButton(
            icon: Icons.info_outline_rounded,
            iconSize: 20,
            tooltip: 'Pedoman Poin Kedisiplinan',
            onPressed: () => _showPedomanPoinModal(context, isDark),
          ),
        ],
      ),
      body: selectedAnak == null
          ? const EmptyStateWidget(
              icon: Icons.child_care_rounded,
              title: 'Pilih Murid Terlebih Dahulu',
              subtitle:
                  'Silakan pilih profil murid untuk melihat catatan kedisiplinan.',
            )
          : RefreshIndicator(
              onRefresh: () async {
                HapticHelper.light();
                await akademik.fetchAkademik(selectedAnak.id, force: true);
              },
              color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 40),
                children: [
                  // Multi-Child Switcher Bar
                  const ChildSwitcherBar(margin: EdgeInsets.zero),
                  const SizedBox(height: 14),

                  // Hero Status & Accumulation Card
                  _buildHeroStatusCard(rekap, isDark),
                  const SizedBox(height: 16),

                  // Category Filter Chips
                  _buildCategoryFilterSection(rekap, isDark),
                  const SizedBox(height: 14),

                  // Violation List Section
                  _buildViolationList(rekap, isDark),
                ],
              ),
            ),
    );
  }

  // === 1. HERO ACCUMULATION & STATUS CARD ===
  Widget _buildHeroStatusCard(RekapPelanggaranAnakModel? rekap, bool isDark) {
    final totalPoin = rekap?.totalPoin ?? 0.0;
    final totalKasus = rekap?.totalKasus ?? 0;
    final statusColor = rekap?.statusColor ?? const Color(0xFF10B981);
    final statusText = rekap?.statusKedisiplinan ?? 'Sangat Baik & Bersih';

    return GlassCard(
      padding: const EdgeInsets.all(20),
      borderRadius: 28,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: isDark ? 0.2 : 0.12),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: statusColor.withValues(alpha: isDark ? 0.4 : 0.3),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      totalPoin <= 0
                          ? Icons.verified_user_rounded
                          : Icons.warning_amber_rounded,
                      size: 14,
                      color: statusColor,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      statusText,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                        color: statusColor,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 5,
                ),
                decoration: BoxDecoration(
                  color: isDark
                      ? Colors.white10
                      : Colors.black.withValues(alpha: 0.04),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Text(
                  '$totalKasus Kasus Tercatat',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: isDark ? Colors.white70 : Colors.black54,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),

          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Akumulasi Poin Pelanggaran',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: isDark ? Colors.white60 : Colors.black54,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.baseline,
                    textBaseline: TextBaseline.alphabetic,
                    children: [
                      Text(
                        rekap?.totalPoinFormatted ?? '0',
                        style: TextStyle(
                          fontSize: 34,
                          fontWeight: FontWeight.w900,
                          letterSpacing: -1,
                          color: statusColor,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        'Poin Sanksi',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: isDark ? Colors.white38 : Colors.black38,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              // Mini Circle Progress Indicator or Shield
              Container(
                width: 54,
                height: 54,
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: isDark ? 0.15 : 0.08),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: statusColor.withValues(alpha: isDark ? 0.35 : 0.2),
                    width: 2,
                  ),
                ),
                child: Center(
                  child: Icon(
                    totalPoin <= 0
                        ? Icons.workspace_premium_rounded
                        : Icons.balance_rounded,
                    color: statusColor,
                    size: 26,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 18),
          const Divider(height: 1),
          const SizedBox(height: 14),

          // Category Breakdown 3-Pills
          Row(
            children: [
              Expanded(
                child: _buildBreakdownMiniPill(
                  label: 'Ringan',
                  count: rekap?.rincian.ringan ?? 0,
                  color: AppColors.skyBlueAccent,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildBreakdownMiniPill(
                  label: 'Sedang',
                  count: rekap?.rincian.sedang ?? 0,
                  color: AppColors.amberAccent,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildBreakdownMiniPill(
                  label: 'Berat',
                  count: rekap?.rincian.berat ?? 0,
                  color: AppColors.roseDanger,
                  isDark: isDark,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildBreakdownMiniPill({
    required String label,
    required int count,
    required Color color,
    required bool isDark,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: isDark ? 0.12 : 0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: color.withValues(alpha: isDark ? 0.25 : 0.18),
        ),
      ),
      child: Column(
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w700,
              color: isDark ? Colors.white60 : Colors.black54,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            '$count',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w900,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  // === 3. CATEGORY FILTER CHIPS ===
  Widget _buildCategoryFilterSection(
    RekapPelanggaranAnakModel? rekap,
    bool isDark,
  ) {
    final categories = ['Semua', 'Ringan', 'Sedang', 'Berat'];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'RIWAYAT CATATAN KEDISIPLINAN',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.8,
                color: isDark ? Colors.white60 : Colors.black54,
              ),
            ),
            if (rekap != null && rekap.riwayat.isNotEmpty)
              Text(
                '${rekap.riwayat.length} Catatan',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: isDark ? Colors.white38 : Colors.black38,
                ),
              ),
          ],
        ),
        const SizedBox(height: 10),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          physics: const BouncingScrollPhysics(),
          child: Row(
            children: categories.map((cat) {
              final isSelected = _selectedCategory == cat;
              int count = 0;
              if (cat == 'Semua') {
                count = rekap?.totalKasus ?? 0;
              } else if (cat == 'Ringan') {
                count = rekap?.rincian.ringan ?? 0;
              } else if (cat == 'Sedang') {
                count = rekap?.rincian.sedang ?? 0;
              } else if (cat == 'Berat') {
                count = rekap?.rincian.berat ?? 0;
              }

              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: FilterChip(
                  label: Text('$cat ($count)'),
                  selected: isSelected,
                  onSelected: (selected) {
                    HapticHelper.light();
                    setState(() {
                      _selectedCategory = cat;
                    });
                  },
                  showCheckmark: false,
                  labelStyle: TextStyle(
                    fontSize: 12,
                    fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                    color: isSelected
                        ? (isDark ? Colors.black : Colors.white)
                        : (isDark ? Colors.white70 : Colors.black87),
                  ),
                  backgroundColor: isDark
                      ? Colors.white.withValues(alpha: 0.05)
                      : Colors.black.withValues(alpha: 0.03),
                  selectedColor: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                    side: BorderSide(
                      color: isSelected
                          ? Colors.transparent
                          : (isDark
                                ? AppColors.outlineDark
                                : AppColors.outlineLight),
                    ),
                  ),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 4,
                    vertical: 2,
                  ),
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }

  // === 4. VIOLATION LIST BUILDER ===
  Widget _buildViolationList(RekapPelanggaranAnakModel? rekap, bool isDark) {
    if (rekap == null || rekap.riwayat.isEmpty) {
      return _buildCleanZeroViolationsCard(isDark);
    }

    final filteredList = rekap.riwayat.where((item) {
      if (_selectedCategory == 'Semua') return true;
      return item.kategori.toLowerCase() == _selectedCategory.toLowerCase();
    }).toList();

    if (filteredList.isEmpty) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 24),
        child: EmptyStateWidget(
          icon: Icons.filter_alt_off_rounded,
          title: 'Tidak Ada Pelanggaran',
          subtitle:
              'Tidak ada catatan kategori "$_selectedCategory" untuk murid ini.',
        ),
      );
    }

    return Column(
      children: filteredList.map((item) {
        return Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: InkWell(
            onTap: () {
              HapticHelper.light();
              _showDetailPelanggaranModal(context, item, isDark);
            },
            borderRadius: BorderRadius.circular(22),
            child: GlassCard(
              padding: const EdgeInsets.all(16),
              borderRadius: 22,
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Leading Category Icon Badge
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: item.badgeColor.withValues(
                        alpha: isDark ? 0.2 : 0.12,
                      ),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Icon(
                      Icons.warning_amber_rounded,
                      color: item.badgeColor,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 14),

                  // Content Information
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Text(
                                item.kasus,
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w800,
                                  color: isDark ? Colors.white : Colors.black87,
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            // Poin Pill
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: item.badgeColor.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Text(
                                '+${item.poinFormatted} Poin',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                  color: item.badgeColor,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),

                        // Date & Room tag
                        Row(
                          children: [
                            Icon(
                              Icons.calendar_today_rounded,
                              size: 12,
                              color: isDark ? Colors.white38 : Colors.black38,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              '${item.hari.isNotEmpty ? "${item.hari}, " : ""}${item.tanggal}',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: isDark ? Colors.white54 : Colors.black54,
                              ),
                            ),
                            if (item.ruanganNama != null &&
                                item.ruanganNama!.isNotEmpty) ...[
                              Text(
                                ' • ',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isDark
                                      ? Colors.white38
                                      : Colors.black38,
                                ),
                              ),
                              Flexible(
                                child: Text(
                                  item.ruanganNama!,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                    color: isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight,
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),

                        if (item.keterangan.isNotEmpty &&
                            item.keterangan != '-') ...[
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? Colors.white.withValues(alpha: 0.04)
                                  : Colors.black.withValues(alpha: 0.02),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              item.keterangan,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w500,
                                color: isDark ? Colors.white70 : Colors.black87,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      }).toList(),
    );
  }

  // === 5. ZERO VIOLATIONS PRAISE CARD ===
  Widget _buildCleanZeroViolationsCard(bool isDark) {
    return GlassCard(
      padding: const EdgeInsets.all(28),
      borderRadius: 28,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: isDark
                    ? [const Color(0xFF064E3B), const Color(0xFF047857)]
                    : [const Color(0xFFDCFCE7), const Color(0xFFA7F3D0)],
              ),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Icon(
                Icons.verified_rounded,
                size: 40,
                color: isDark
                    ? const Color(0xFFA7F3D0)
                    : const Color(0xFF065F46),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'Alhamdulillah, Catatan Bersih!',
            textAlign: TextAlign.center,
            style: GoogleFonts.plusJakartaSans(
              fontSize: 16,
              fontWeight: FontWeight.w900,
              color: isDark ? Colors.white : Colors.black87,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Murid mematuhi seluruh tata tertib madrasah dengan sangat baik dan menunjukkan teladan budi pekerti yang terpuji.',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w500,
              height: 1.5,
              color: isDark ? Colors.white70 : Colors.black54,
            ),
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: const Color(
                0xFF10B981,
              ).withValues(alpha: isDark ? 0.2 : 0.1),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.star_rounded, size: 16, color: Color(0xFF10B981)),
                SizedBox(width: 6),
                Text(
                  'Pertahankan Prestasi Akhlak & Disiplin',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF10B981),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // === 6. DETAIL PELANGGARAN BOTTOM SHEET MODAL ===
  void _showDetailPelanggaranModal(
    BuildContext context,
    PelanggaranItemModel item,
    bool isDark,
  ) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppColors.surfaceContainerDark : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(32)),
      ),
      builder: (ctx) {
        return Padding(
          padding: EdgeInsets.fromLTRB(
            24,
            12,
            24,
            MediaQuery.of(ctx).padding.bottom + 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Pill drag handle
              Center(
                child: Container(
                  width: 44,
                  height: 4,
                  decoration: BoxDecoration(
                    color: isDark ? Colors.white24 : Colors.black12,
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Title and category badge
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: item.badgeColor.withValues(
                        alpha: isDark ? 0.2 : 0.12,
                      ),
                      borderRadius: BorderRadius.circular(18),
                    ),
                    child: Icon(
                      Icons.warning_amber_rounded,
                      color: item.badgeColor,
                      size: 26,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: item.badgeColor.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            'Kategori: ${item.kategori.toUpperCase()}',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w900,
                              color: item.badgeColor,
                            ),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          item.kasus,
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 16,
                            fontWeight: FontWeight.w900,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 20),
              const Divider(height: 1),
              const SizedBox(height: 16),

              // Data Grid Rincian
              _buildDetailRow(
                icon: Icons.calendar_today_rounded,
                label: 'Waktu Pelanggaran',
                value:
                    '${item.hari.isNotEmpty ? "${item.hari}, " : ""}${item.tanggal}',
                isDark: isDark,
              ),
              const SizedBox(height: 12),
              _buildDetailRow(
                icon: Icons.stars_rounded,
                label: 'Bobot Poin Sanksi',
                value: '+${item.poinFormatted} Poin',
                valueColor: item.badgeColor,
                isDark: isDark,
              ),
              if (item.ruanganNama != null && item.ruanganNama!.isNotEmpty) ...[
                const SizedBox(height: 12),
                _buildDetailRow(
                  icon: Icons.meeting_room_rounded,
                  label: 'Ruangan Terkait',
                  value: item.ruanganNama!,
                  isDark: isDark,
                ),
              ],
              const SizedBox(height: 12),
              _buildDetailRow(
                icon: Icons.person_pin_rounded,
                label: 'Dicatat Oleh',
                value: item.diinputOleh,
                isDark: isDark,
              ),

              if (item.keterangan.isNotEmpty && item.keterangan != '-') ...[
                const SizedBox(height: 16),
                Text(
                  'Catatan & Kronologi Ustadz:',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: isDark ? Colors.white60 : Colors.black54,
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: isDark
                        ? Colors.white.withValues(alpha: 0.05)
                        : Colors.black.withValues(alpha: 0.03),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isDark
                          ? AppColors.outlineDark
                          : AppColors.outlineLight,
                    ),
                  ),
                  child: Text(
                    item.keterangan,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                      height: 1.4,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                ),
              ],

              const SizedBox(height: 24),

              // Action Consult Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.of(ctx).pop();
                    HapticHelper.light();
                    Navigator.of(context).push(
                      MaterialPageRoute(
                        builder: (_) => const HubungiAdminScreen(),
                      ),
                    );
                  },
                  icon: const Icon(Icons.support_agent_rounded, size: 20),
                  label: const Text('Konsultasi dengan Madrasah'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                    foregroundColor: isDark ? Colors.black : Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDetailRow({
    required IconData icon,
    required String label,
    required String value,
    Color? valueColor,
    required bool isDark,
  }) {
    return Row(
      children: [
        Icon(icon, size: 16, color: isDark ? Colors.white54 : Colors.black45),
        const SizedBox(width: 10),
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: isDark ? Colors.white60 : Colors.black54,
          ),
        ),
        const Spacer(),
        Text(
          value,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w800,
            color: valueColor ?? (isDark ? Colors.white : Colors.black87),
          ),
        ),
      ],
    );
  }

  // === 7. PEDOMAN POIN MODAL ===
  void _showPedomanPoinModal(BuildContext context, bool isDark) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppColors.surfaceContainerDark : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(32)),
      ),
      builder: (ctx) {
        return Padding(
          padding: EdgeInsets.fromLTRB(
            24,
            12,
            24,
            MediaQuery.of(ctx).padding.bottom + 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 44,
                  height: 4,
                  decoration: BoxDecoration(
                    color: isDark ? Colors.white24 : Colors.black12,
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color:
                          (isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight)
                              .withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Icon(
                      Icons.menu_book_rounded,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Text(
                    'Pedoman Kedisiplinan Murid',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const Divider(height: 1),
              const SizedBox(height: 14),

              _buildPedomanItem(
                title: 'Pelanggaran Ringan (1 - 5 Poin)',
                desc:
                    'Terlambat masuk kelas, atribut pakaian tidak rapi, lupa membawa kitab/buku tugas.',
                color: AppColors.skyBlueAccent,
                isDark: isDark,
              ),
              const SizedBox(height: 10),
              _buildPedomanItem(
                title: 'Pelanggaran Sedang (6 - 15 Poin)',
                desc:
                    'Tidak masuk tanpa keterangan (Alpha), mengganggu ketenangan KBM, keluar madrasah tanpa izin.',
                color: AppColors.amberAccent,
                isDark: isDark,
              ),
              const SizedBox(height: 10),
              _buildPedomanItem(
                title: 'Pelanggaran Berat (> 15 Poin)',
                desc:
                    'Merusak fasilitas madrasah, perselisihan fisik, tindakan tidak terpuji yang memerlukan pembinaan khusus.',
                color: AppColors.roseDanger,
                isDark: isDark,
              ),
              const SizedBox(height: 20),

              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: () => Navigator.of(ctx).pop(),
                  style: OutlinedButton.styleFrom(
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  child: const Text('Tutup Informasi'),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildPedomanItem({
    required String title,
    required String desc,
    required Color color,
    required bool isDark,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: isDark ? 0.1 : 0.05),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: color.withValues(alpha: isDark ? 0.25 : 0.15),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 8,
                height: 8,
                decoration: BoxDecoration(color: color, shape: BoxShape.circle),
              ),
              const SizedBox(width: 8),
              Text(
                title,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: color,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            desc,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w500,
              height: 1.4,
              color: isDark ? Colors.white70 : Colors.black87,
            ),
          ),
        ],
      ),
    );
  }
}
