import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/nilai_ujian_model.dart';
import '../../../providers/nilai_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class FormNilaiScreen extends StatefulWidget {
  final int ruanganId;
  final int? ujianId;
  final int? jadwalUjianId;
  final String mapelName;
  final String ruanganName;
  final String? ujianName;

  const FormNilaiScreen({
    super.key,
    required this.ruanganId,
    this.ujianId,
    this.jadwalUjianId,
    required this.mapelName,
    required this.ruanganName,
    this.ujianName,
  });

  @override
  State<FormNilaiScreen> createState() => _FormNilaiScreenState();
}

class _FormNilaiScreenState extends State<FormNilaiScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final p = context.read<NilaiProvider>();
      final uId =
          widget.ujianId ??
          p.selectedUjianId ??
          p.daftarUjian.firstOrNull?.id ??
          0;
      p.fetchInputData(
        ruanganId: widget.ruanganId,
        ujianId: uId,
        jadwalUjianId: widget.jadwalUjianId,
      );
    });
  }

  Future<void> _handleSave(String action) async {
    HapticHelper.medium();
    final provider = context.read<NilaiProvider>();
    final uId =
        widget.ujianId ??
        provider.selectedUjianId ??
        provider.daftarUjian.firstOrNull?.id ??
        0;
    final success = await provider.simpanNilai(
      ruanganId: widget.ruanganId,
      ujianId: uId,
      jadwalUjianId: widget.jadwalUjianId,
      action: action,
    );

    if (!mounted) return;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            action == 'publish'
                ? 'Nilai resmi berhasil dipublikasikan ke rapor!'
                : 'Draf nilai berhasil disimpan.',
          ),
          backgroundColor: AppColors.hadirTextLight,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            provider.errorMessage ?? 'Gagal menyimpan nilai santri.',
          ),
          backgroundColor: AppColors.roseDanger,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
      );
    }
  }

  void _showDispensasiDialog(MuridNilaiItem murid) {
    HapticHelper.light();
    final reasonCtrl = TextEditingController(
      text: 'Dispensasi Ujian dari Ustadz / Wali Ruangan',
    );
    final provider = context.read<NilaiProvider>();
    final uId =
        widget.ujianId ??
        provider.selectedUjianId ??
        provider.daftarUjian.firstOrNull?.id ??
        0;

    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final onPrimaryColor = isDark
        ? AppColors.onPrimaryDark
        : AppColors.onPrimaryLight;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: primaryColor.withValues(alpha: 0.15),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.verified_user_rounded,
                color: primaryColor,
                size: 22,
              ),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Text(
                'Beri Dispensasi Ujian',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Beri dispensasi ujian untuk santri:',
              style: TextStyle(
                fontSize: 12,
                color: Theme.of(context).brightness == Brightness.dark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
            ),
            const SizedBox(height: 4),
            Text(
              '${murid.nama} (${murid.nism})',
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
            ),
            if (murid.lockReason != null) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: AppColors.roseDanger.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: AppColors.roseDanger.withValues(alpha: 0.3),
                  ),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.info_outline_rounded,
                      size: 14,
                      color: AppColors.roseDanger,
                    ),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        murid.lockReason!,
                        style: const TextStyle(
                          fontSize: 11,
                          color: AppColors.roseDanger,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 14),
            const Text(
              'Alasan / Kebijakan Dispensasi:',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            TextField(
              controller: reasonCtrl,
              decoration: InputDecoration(
                hintText: 'Masukkan alasan pemberian izin ujian...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 10,
                ),
              ),
              maxLines: 2,
              style: const TextStyle(fontSize: 13),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final success = await provider.beriDispensasi(
                ujianId: uId,
                muridId: murid.muridId,
                ruanganId: widget.ruanganId,
                jadwalUjianId: widget.jadwalUjianId,
                alasanIzin: reasonCtrl.text.trim().isEmpty
                    ? 'Dispensasi Ujian dari Ustadz / Wali Ruangan'
                    : reasonCtrl.text.trim(),
              );

              if (!mounted) return;
              if (success) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      'Dispensasi berhasil diberikan untuk ${murid.nama}. Akses input nilai telah dibuka.',
                    ),
                    backgroundColor: primaryColor,
                    behavior: SnackBarBehavior.floating,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                );
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      provider.errorMessage ?? 'Gagal memberikan dispensasi.',
                    ),
                    backgroundColor: AppColors.roseDanger,
                    behavior: SnackBarBehavior.floating,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: primaryColor,
              foregroundColor: onPrimaryColor,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Text('Beri Dispensasi'),
          ),
        ],
      ),
    );
  }

  void _showBatalDispensasiDialog(MuridNilaiItem murid) {
    HapticHelper.light();
    final provider = context.read<NilaiProvider>();
    final uId =
        widget.ujianId ??
        provider.selectedUjianId ??
        provider.daftarUjian.firstOrNull?.id ??
        0;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Text('Batalkan Dispensasi?'),
        content: Text(
          'Apakah Anda yakin ingin membatalkan dispensasi ujian untuk santri ${murid.nama}?',
          style: const TextStyle(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Tutup'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final success = await provider.batalkanDispensasi(
                ujianId: uId,
                muridId: murid.muridId,
                ruanganId: widget.ruanganId,
                jadwalUjianId: widget.jadwalUjianId,
              );

              if (!mounted) return;
              if (success) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      'Dispensasi untuk ${murid.nama} telah dibatalkan.',
                    ),
                    backgroundColor: AppColors.amberAccent,
                    behavior: SnackBarBehavior.floating,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.roseDanger,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Text('Batalkan Dispensasi'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final nilai = context.watch<NilaiProvider>();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: widget.mapelName,
        subtitleText:
            '${widget.ruanganName} • ${widget.ujianName ?? nilai.selectedUjian?.namaUjian ?? "Ujian"}',
      ),
      body: Stack(
        children: [
          if (nilai.isLoading && nilai.muridNilaiList.isEmpty)
            const Padding(
              padding: EdgeInsets.all(16),
              child: ShimmerLoadingList(count: 6, height: 74),
            )
          else if (nilai.muridNilaiList.isEmpty)
            const Center(
              child: Text('Tidak ada murid ditemukan untuk ruangan ini.'),
            )
          else
            ListView.builder(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 110),
              itemCount: nilai.muridNilaiList.length,
              itemBuilder: (context, index) {
                final murid = nilai.muridNilaiList[index];

                return _MuridNilaiCard(
                  key: ValueKey('murid_${murid.muridId}'),
                  murid: murid,
                  index: index,
                  isDark: isDark,
                  isWaliRuangan: nilai.isWaliRuangan,
                  onDispensasi: () => _showDispensasiDialog(murid),
                  onBatalDispensasi: () => _showBatalDispensasiDialog(murid),
                  onScoreChanged: (score) {
                    nilai.updateScore(murid.muridId, score);
                  },
                );
              },
            ),

          // Bottom Action Bar (Simpan Draf & Publikasikan)
          if (nilai.muridNilaiList.isNotEmpty)
            Positioned(
              left: 16,
              right: 16,
              bottom: 16,
              child: GlassCard(
                padding: const EdgeInsets.all(12),
                child: Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: nilai.isSaving
                            ? null
                            : () => _handleSave('draft'),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: const Text('Simpan Draf'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: nilai.isSaving
                            ? null
                            : () => _handleSave('publish'),
                        style: ElevatedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          backgroundColor: AppColors.primaryLight,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: nilai.isSaving
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: Colors.white,
                                ),
                              )
                            : const Text(
                                'Publikasikan',
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                ),
                              ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _MuridNilaiCard extends StatefulWidget {
  final MuridNilaiItem murid;
  final int index;
  final bool isDark;
  final bool isWaliRuangan;
  final VoidCallback onDispensasi;
  final VoidCallback onBatalDispensasi;
  final ValueChanged<double?> onScoreChanged;

  const _MuridNilaiCard({
    super.key,
    required this.murid,
    required this.index,
    required this.isDark,
    required this.isWaliRuangan,
    required this.onDispensasi,
    required this.onBatalDispensasi,
    required this.onScoreChanged,
  });

  @override
  State<_MuridNilaiCard> createState() => _MuridNilaiCardState();
}

class _MuridNilaiCardState extends State<_MuridNilaiCard> {
  late final TextEditingController _controller;
  late final FocusNode _focusNode;

  static String _formatNilai(double? val) {
    if (val == null) return '';
    if (val == val.roundToDouble()) {
      return val.toInt().toString();
    }
    return val.toString();
  }

  @override
  void initState() {
    super.initState();
    _controller = TextEditingController(text: _formatNilai(widget.murid.nilai));
    _focusNode = FocusNode();
  }

  @override
  void didUpdateWidget(covariant _MuridNilaiCard oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.murid.muridId != widget.murid.muridId) {
      _controller.text = _formatNilai(widget.murid.nilai);
    } else if (!_focusNode.hasFocus) {
      final currentParsed = double.tryParse(
        _controller.text.trim().replaceAll(',', '.'),
      );
      if (currentParsed != widget.murid.nilai) {
        _controller.text = _formatNilai(widget.murid.nilai);
      }
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final murid = widget.murid;
    final isDark = widget.isDark;

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        children: [
          // Avatar nomor urut
          CircleAvatar(
            radius: 18,
            backgroundColor: isDark
                ? const Color(0xFF101710)
                : const Color(0xFFE8F5E9),
            child: Text(
              '${widget.index + 1}',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
            ),
          ),
          const SizedBox(width: 12),

          // Nama & Status
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  murid.nama,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  'NISM: ${murid.nism} • ${murid.jenisKelamin == 'L' ? 'Murid Putra' : 'Murid Putri'}',
                  style: TextStyle(
                    fontSize: 10,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                ),
                const SizedBox(height: 3),
                if (murid.isLocked) ...[
                  Text(
                    '🔒 ${murid.lockReason ?? "Terkunci Administrasi"}',
                    style: const TextStyle(
                      fontSize: 11,
                      color: AppColors.roseDanger,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  // Tombol Beri Dispensasi (Hanya Wali Ruangan)
                  if (widget.isWaliRuangan)
                    InkWell(
                      onTap: widget.onDispensasi,
                      borderRadius: BorderRadius.circular(8),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color:
                              (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                  .withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color:
                                (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                    .withValues(alpha: 0.5),
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.verified_user_rounded,
                              size: 13,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              'Beri Dispensasi',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                          ],
                        ),
                      ),
                    )
                  else
                    Text(
                      'Hubungi Wali Ruangan untuk izin',
                      style: TextStyle(
                        fontSize: 10,
                        fontStyle: FontStyle.italic,
                        color: isDark
                            ? const Color(0xFF8D9387)
                            : const Color(0xFF73796E),
                      ),
                    ),
                ] else if (murid.lockReason?.contains('Dispensasi') ??
                    false) ...[
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? AppColors.dispensasiBgDark
                              : AppColors.dispensasiBgLight,
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color:
                                (isDark
                                        ? AppColors.dispensasiTextDark
                                        : AppColors.dispensasiTextLight)
                                    .withValues(alpha: 0.3),
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.verified_rounded,
                              size: 11,
                              color: isDark
                                  ? AppColors.dispensasiTextDark
                                  : AppColors.dispensasiTextLight,
                            ),
                            const SizedBox(width: 3),
                            Text(
                              'Dispensasi',
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: isDark
                                    ? AppColors.dispensasiTextDark
                                    : AppColors.dispensasiTextLight,
                              ),
                            ),
                          ],
                        ),
                      ),
                      if (widget.isWaliRuangan) ...[
                        const SizedBox(width: 6),
                        InkWell(
                          onTap: widget.onBatalDispensasi,
                          child: const Icon(
                            Icons.cancel_outlined,
                            size: 14,
                            color: Colors.grey,
                          ),
                        ),
                      ],
                    ],
                  ),
                ] else
                  Text(
                    murid.isPublished
                        ? '✓ Terbit di Rapor'
                        : (murid.nilai != null
                              ? 'Draf Belum Terbit'
                              : 'Belum Diisi'),
                    style: TextStyle(
                      fontSize: 11,
                      color: murid.isPublished
                          ? AppColors.hadirTextLight
                          : (murid.nilai != null
                                ? AppColors.amberAccent
                                : Colors.grey),
                    ),
                  ),
              ],
            ),
          ),

          // Input Field Nilai
          SizedBox(
            width: 80,
            child: TextField(
              controller: _controller,
              focusNode: _focusNode,
              keyboardType: const TextInputType.numberWithOptions(
                decimal: true,
              ),
              enabled: !murid.isLocked,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              decoration: InputDecoration(
                hintText: murid.isLocked ? 'Kunci' : '0-100',
                hintStyle: TextStyle(
                  fontSize: 11,
                  color: murid.isLocked ? AppColors.roseDanger : Colors.grey,
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 8,
                  vertical: 10,
                ),
                filled: true,
                fillColor: (isDark ? Colors.white : Colors.black).withValues(
                  alpha: murid.isLocked ? 0.02 : 0.05,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: isDark
                        ? AppColors.outlineDark
                        : AppColors.outlineLight,
                  ),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: murid.nilai != null
                        ? AppColors.primaryLight
                        : (isDark
                              ? AppColors.outlineDark
                              : AppColors.outlineLight),
                  ),
                ),
                disabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color:
                        (isDark
                                ? AppColors.outlineDark
                                : AppColors.outlineLight)
                            .withValues(alpha: 0.5),
                  ),
                ),
              ),
              onChanged: (val) {
                final clean = val.trim().replaceAll(',', '.');
                if (clean.isEmpty) {
                  widget.onScoreChanged(null);
                  setState(() {});
                  return;
                }
                final score = double.tryParse(clean);
                if (score != null) {
                  widget.onScoreChanged(score);
                  setState(() {});
                }
              },
            ),
          ),
        ],
      ),
    );
  }
}
