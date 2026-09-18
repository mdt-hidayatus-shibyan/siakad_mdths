import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/catatan_ustadz_model.dart';
import '../../../providers/catatan_ustadz_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import 'form_catatan_ustadz_screen.dart';

class DetailCatatanUstadzScreen extends StatefulWidget {
  final CatatanUstadzItem catatan;

  const DetailCatatanUstadzScreen({super.key, required this.catatan});

  @override
  State<DetailCatatanUstadzScreen> createState() =>
      _DetailCatatanUstadzScreenState();
}

class _DetailCatatanUstadzScreenState extends State<DetailCatatanUstadzScreen> {
  late CatatanUstadzItem _item;

  @override
  void initState() {
    super.initState();
    _item = widget.catatan;
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

  void _confirmDelete() {
    HapticHelper.medium();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? const Color(0xFF1B231B) : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text(
          'Hapus Catatan Ini?',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
        ),
        content: Text(
          'Catatan "${_item.judul}" akan dihapus permanen dari sistem. Anda yakin?',
          style: const TextStyle(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              HapticHelper.medium();
              final success = await context
                  .read<CatatanUstadzProvider>()
                  .hapusCatatan(_item.id);
              if (success && mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Catatan berhasil dihapus.')),
                );
                Navigator.pop(context, true);
              }
            },
            child: const Text('Ya, Hapus'),
          ),
        ],
      ),
    );
  }

  void _showImageZoom(String imageUrl) {
    HapticHelper.light();
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(12),
        child: Stack(
          alignment: Alignment.topRight,
          children: [
            InteractiveViewer(
              minScale: 0.5,
              maxScale: 4.0,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Image.network(
                  imageUrl,
                  fit: BoxFit.contain,
                  errorBuilder: (_, __, ___) => const Center(
                    child: Text(
                      'Gagal memuat gambar',
                      style: TextStyle(color: Colors.white),
                    ),
                  ),
                ),
              ),
            ),
            IconButton(
              onPressed: () => Navigator.pop(ctx),
              icon: Container(
                padding: const EdgeInsets.all(6),
                decoration: const BoxDecoration(
                  color: Colors.black54,
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.close_rounded,
                  color: Colors.white,
                  size: 20,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final urgencyColor = _getUrgencyColor(_item.tingkatUrgensi, isDark);

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Detail Catatan',
        subtitleText: 'Laporan pendidik',
        actions: [
          CircularIconButton(
            icon: Icons.edit_rounded,
            iconSize: 18,
            iconColor: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            tooltip: 'Edit Catatan',
            onPressed: () async {
              HapticHelper.light();
              final catatanProvider = context.read<CatatanUstadzProvider>();
              final result = await Navigator.push<bool>(
                context,
                MaterialPageRoute(
                  builder: (_) => FormCatatanUstadzScreen(existingItem: _item),
                ),
              );
              if (result == true && mounted) {
                final found = catatanProvider.catatanList
                    .where((c) => c.id == _item.id)
                    .toList();
                if (found.isNotEmpty) {
                  setState(() {
                    _item = found.first;
                  });
                }
              }
            },
          ),
          CircularIconButton(
            icon: Icons.delete_outline_rounded,
            iconSize: 18,
            iconColor: isDark
                ? const Color(0xFFEF4444)
                : const Color(0xFFDC2626),
            tooltip: 'Hapus Catatan',
            onPressed: _confirmDelete,
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        physics: const BouncingScrollPhysics(),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header GlassCard (Kategori, Urgensi, Judul, Tanggal)
            GlassCard(
              padding: const EdgeInsets.all(18),
              borderRadius: 24,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Badges
                  Row(
                    children: [
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
                          _item.kategori,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 4,
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
                              size: 12,
                              color: urgencyColor,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              'Urgensi: ${_item.tingkatUrgensi}',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: urgencyColor,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Judul
                  Text(
                    _item.judul,
                    style: const TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.bold,
                      letterSpacing: -0.3,
                      height: 1.3,
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Metadata Info Row
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      color: isDark ? Colors.black26 : const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            const Icon(
                              Icons.schedule_rounded,
                              size: 14,
                              color: Colors.grey,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              _item.createdAt,
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                        if (_item.isSudahDibacaAdmin)
                          const Row(
                            children: [
                              Icon(
                                Icons.done_all_rounded,
                                size: 14,
                                color: Color(0xFF059669),
                              ),
                              SizedBox(width: 4),
                              Text(
                                'Dibaca Admin',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF059669),
                                ),
                              ),
                            ],
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Target Sasaran Box
            if (_item.targetTipe == 'murid' && _item.muridNama != null)
              GlassCard(
                padding: const EdgeInsets.all(14),
                borderRadius: 18,
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.amber.withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.person_rounded,
                        color: Colors.amber,
                        size: 22,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Murid yang Dilaporkan',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: Colors.grey,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            _item.muridNama!,
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            'NISM: ${_item.muridNism ?? "-"} • ${_item.ruanganNama ?? "-"}',
                            style: const TextStyle(
                              fontSize: 11,
                              color: Colors.grey,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              )
            else if (_item.targetTipe == 'madrasah')
              GlassCard(
                padding: const EdgeInsets.all(14),
                borderRadius: 18,
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.blue.withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.school_rounded,
                        color: Colors.blue,
                        size: 22,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Sasaran Laporan',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: Colors.grey,
                            ),
                          ),
                          const SizedBox(height: 2),
                          const Text(
                            'Madrasah, Sarana Prasarana & Fasilitas',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          if (_item.ruanganNama != null &&
                              _item.ruanganNama!.isNotEmpty)
                            Padding(
                              padding: const EdgeInsets.only(top: 2),
                              child: Text(
                                'Ruangan / Fasilitas: ${_item.ruanganNama}',
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: Colors.grey,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 14),

            // Isi Catatan Lengkap
            GlassCard(
              padding: const EdgeInsets.all(18),
              borderRadius: 24,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'URAIAN CATATAN / KELUHAN',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 0.8,
                      color: isDark ? Colors.white60 : Colors.black54,
                    ),
                  ),
                  const SizedBox(height: 12),
                  SelectableText(
                    _item.isiCatatan,
                    style: TextStyle(
                      fontSize: 13,
                      height: 1.5,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Lampiran Foto Bukti (Jika Ada)
            if (_item.lampiranFotoUrl != null) ...[
              GlassCard(
                padding: const EdgeInsets.all(16),
                borderRadius: 24,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'LAMPIRAN FOTO / BUKTI',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.8,
                            color: isDark ? Colors.white60 : Colors.black54,
                          ),
                        ),
                        Text(
                          'Ketuk untuk perbesar',
                          style: TextStyle(
                            fontSize: 10,
                            color: isDark ? Colors.white38 : Colors.black38,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    InkWell(
                      onTap: () => _showImageZoom(_item.lampiranFotoUrl!),
                      borderRadius: BorderRadius.circular(16),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: Image.network(
                          _item.lampiranFotoUrl!,
                          width: double.infinity,
                          height: 220,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            height: 100,
                            color: isDark ? Colors.white10 : Colors.black12,
                            child: const Center(
                              child: Text('Foto tidak dapat dimuat'),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
            ],
          ],
        ),
      ),
    );
  }
}
