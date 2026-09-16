import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
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

                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 12,
                  ),
                  child: Row(
                    children: [
                      // Avatar nomor urut
                      CircleAvatar(
                        radius: 18,
                        backgroundColor: isDark
                            ? const Color(0xFF101710)
                            : const Color(0xFFE8F5E9),
                        child: Text(
                          '${index + 1}',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
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
                            if (murid.isLocked)
                              Text(
                                '🔒 ${murid.lockReason ?? "Terkunci Administrasi"}',
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: AppColors.roseDanger,
                                  fontWeight: FontWeight.bold,
                                ),
                              )
                            else
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
                        child: TextFormField(
                          initialValue: murid.nilai?.toString() ?? '',
                          keyboardType: const TextInputType.numberWithOptions(
                            decimal: true,
                          ),
                          enabled: !murid.isLocked,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                          decoration: InputDecoration(
                            hintText: '0-100',
                            hintStyle: const TextStyle(
                              fontSize: 12,
                              color: Colors.grey,
                            ),
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 10,
                            ),
                            filled: true,
                            fillColor: (isDark ? Colors.white : Colors.black)
                                .withValues(alpha: 0.05),
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
                          ),
                          onChanged: (val) {
                            final score = double.tryParse(
                              val.replaceAll(',', '.'),
                            );
                            if (score != null) {
                              nilai.updateScore(murid.muridId, score);
                            }
                          },
                        ),
                      ),
                    ],
                  ),
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
