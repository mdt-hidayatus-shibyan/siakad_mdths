import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/murid_model.dart';
import '../../../providers/murid_provider.dart';
import '../../widgets/app_avatar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class DirektoriMuridScreen extends StatefulWidget {
  final int? ruanganId;
  const DirektoriMuridScreen({super.key, this.ruanganId});

  @override
  State<DirektoriMuridScreen> createState() => _DirektoriMuridScreenState();
}

class _DirektoriMuridScreenState extends State<DirektoriMuridScreen> {
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<MuridProvider>().fetchMuridRuangan(
        ruanganId: widget.ruanganId,
      );
    });
  }

  void _showFullPhotoDialog(
    BuildContext context,
    String? imageUrl,
    String name,
  ) {
    HapticHelper.medium();
    final resolvedUrl = ApiClient.resolveImageUrl(imageUrl);
    if (resolvedUrl == null || resolvedUrl.isEmpty) return;

    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.black.withValues(alpha: 0.92),
        insetPadding: const EdgeInsets.all(16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header Dialog
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 8, 8),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, color: Colors.white),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
            ),
            const Divider(color: Colors.white24, height: 1),

            // Interactive Full Image
            Padding(
              padding: const EdgeInsets.all(12),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(14),
                child: InteractiveViewer(
                  panEnabled: true,
                  minScale: 0.8,
                  maxScale: 4.0,
                  child: Image.network(
                    resolvedUrl,
                    fit: BoxFit.contain,
                    errorBuilder: (context, error, stackTrace) => Container(
                      height: 220,
                      color: Colors.white10,
                      child: const Center(
                        child: Text(
                          'Gagal memuat foto',
                          style: TextStyle(color: Colors.white70, fontSize: 13),
                        ),
                      ),
                    ),
                    loadingBuilder: (context, child, loadingProgress) {
                      if (loadingProgress == null) return child;
                      return Container(
                        height: 220,
                        color: Colors.white10,
                        child: const Center(
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ),
            ),
            const Padding(
              padding: EdgeInsets.only(bottom: 12),
              child: Text(
                'Cubit layar untuk memperbesar (Zoom)',
                style: TextStyle(color: Colors.white60, fontSize: 11),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showDetailSantri(MuridModel murid) {
    HapticHelper.light();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        final isDark = Theme.of(context).brightness == Brightness.dark;

        return Container(
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF101710) : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
          ),
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
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
                  Stack(
                    children: [
                      AppAvatar(
                        radius: 34,
                        name: murid.namaLengkap,
                        imageUrl: murid.foto,
                        shape: BoxShape.rectangle,
                        borderRadius: BorderRadius.circular(16),
                        fit: BoxFit.cover,
                        alignment: Alignment.topCenter,
                        cacheDimension: 250,
                        onTap: murid.foto != null
                            ? () => _showFullPhotoDialog(
                                context,
                                murid.foto,
                                murid.namaLengkap,
                              )
                            : null,
                      ),
                      if (murid.foto != null)
                        Positioned(
                          right: 0,
                          bottom: 0,
                          child: GestureDetector(
                            onTap: () => _showFullPhotoDialog(
                              context,
                              murid.foto,
                              murid.namaLengkap,
                            ),
                            child: Container(
                              padding: const EdgeInsets.all(4),
                              decoration: BoxDecoration(
                                color:
                                    (isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight)
                                        .withValues(alpha: 0.9),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(
                                Icons.fullscreen_rounded,
                                size: 14,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          murid.namaLengkap,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        if (murid.namaPanggilan != null)
                          Text(
                            'Panggilan: ${murid.namaPanggilan}',
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark
                                  ? const Color(0xFF8D9387)
                                  : const Color(0xFF73796E),
                            ),
                          ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? const Color(0xFF101710)
                                    : const Color(0xFFE8F5E9),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                murid.jenisKelamin == 'L'
                                    ? 'Murid Putra'
                                    : 'Murid Putri',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                            ),
                            if (murid.isYatim) ...[
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 6,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? const Color(0xFF382305)
                                      : const Color(0xFFFEF3C7),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text(
                                  '⭐ Yatim / Piatu',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.amberAccent,
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              const Divider(height: 1),
              const SizedBox(height: 14),

              // Detail Fields
              _buildDetailRow('NISM', murid.nism, isDark),
              if (murid.nisn != null)
                _buildDetailRow('NISN', murid.nisn!, isDark),
              if (murid.nik != null) _buildDetailRow('NIK', murid.nik!, isDark),
              if (murid.tempatLahir != null || murid.tanggalLahir != null)
                _buildDetailRow(
                  'TTL',
                  '${murid.tempatLahir ?? "-"}, ${murid.tanggalLahir ?? "-"}',
                  isDark,
                ),
              _buildDetailRow(
                'Nama Ayah',
                '${murid.namaAyah ?? "-"} (${murid.statusAyah ?? "Hidup"})',
                isDark,
              ),
              _buildDetailRow(
                'Nama Ibu',
                '${murid.namaIbu ?? "-"} (${murid.statusIbu ?? "Hidup"})',
                isDark,
              ),
              _buildDetailRow('Status Murid', murid.status, isDark),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDetailRow(String label, String value, bool isDark) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 110,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 12,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
            ),
          ),
          const Text(': '),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<MuridProvider>();

    final filteredMurids = provider.muridList.where((m) {
      return m.namaLengkap.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          m.nism.contains(_searchQuery);
    }).toList();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Direktori Murid',
        subtitleText: provider.namaRuangan.isNotEmpty
            ? provider.namaRuangan
            : null,
      ),
      body: RefreshIndicator(
        onRefresh: () => context.read<MuridProvider>().fetchMuridRuangan(
          ruanganId: widget.ruanganId,
        ),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Search Bar
            TextField(
              decoration: InputDecoration(
                hintText: 'Cari murid berdasarkan nama / NISM...',
                prefixIcon: const Icon(Icons.search_rounded, size: 20),
                contentPadding: const EdgeInsets.symmetric(
                  vertical: 10,
                  horizontal: 14,
                ),
              ),
              onChanged: (val) {
                setState(() => _searchQuery = val);
              },
            ),
            const SizedBox(height: 16),

            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Daftar Murid Kelas',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                ),
                Text(
                  '${filteredMurids.length} Murid',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            if (provider.isLoading)
              const ShimmerLoadingList(count: 5, height: 74)
            else if (filteredMurids.isEmpty)
              const GlassCard(
                padding: EdgeInsets.all(24),
                child: Center(child: Text('Tidak ada murid ditemukan.')),
              )
            else
              ...filteredMurids.map((murid) {
                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  onTap: () => _showDetailSantri(murid),
                  child: Row(
                    children: [
                      AppAvatar(
                        radius: 23,
                        name: murid.namaLengkap,
                        imageUrl: murid.foto,
                        shape: BoxShape.rectangle,
                        borderRadius: BorderRadius.circular(13),
                        fit: BoxFit.cover,
                        alignment: Alignment.topCenter,
                        cacheDimension: 120,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              murid.namaLengkap,
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'NISM: ${murid.nism} • ${murid.jenisKelamin == "L" ? "Putra" : "Putri"}',
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
                      if (murid.isYatim)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? const Color(0xFF382305)
                                : const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'Yatim',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: AppColors.amberAccent,
                            ),
                          ),
                        ),
                      const SizedBox(width: 6),
                      const Icon(Icons.chevron_right_rounded, size: 20),
                    ],
                  ),
                );
              }),
          ],
        ),
      ),
    );
  }
}
