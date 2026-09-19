import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../providers/presensi_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';
import '../../widgets/status_presensi_chip.dart';

class FormPresensiScreen extends StatefulWidget {
  final int jadwalId;
  final String mapel;
  final String ruangan;
  final String jam;

  const FormPresensiScreen({
    super.key,
    required this.jadwalId,
    required this.mapel,
    required this.ruangan,
    required this.jam,
  });

  @override
  State<FormPresensiScreen> createState() => _FormPresensiScreenState();
}

class _FormPresensiScreenState extends State<FormPresensiScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PresensiProvider>().fetchMurid(widget.jadwalId);
    });
  }

  Future<void> _handleSimpan() async {
    final presensi = context.read<PresensiProvider>();
    final success = await presensi.simpanPresensi(widget.jadwalId);

    if (!mounted) return;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Absensi kelas ${widget.ruangan} berhasil disimpan!'),
          backgroundColor: AppColors.hadirTextLight,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(presensi.errorMessage ?? 'Gagal menyimpan absensi.'),
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
    final presensi = context.watch<PresensiProvider>();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: widget.mapel,
        subtitleText: '${widget.ruangan} • ${widget.jam}',
        actions: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: isDark ? const Color(0xFF1A211A) : Colors.white,
              border: Border.all(
                color: isDark ? AppColors.outlineDark : const Color(0xFFE2E8F0),
                width: 1,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: isDark ? 0.35 : 0.06),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: PopupMenuButton<String>(
              padding: EdgeInsets.zero,
              icon: Icon(
                Icons.more_vert_rounded,
                size: 20,
                color: isDark ? Colors.white : const Color(0xFF1E293B),
              ),
              tooltip: 'Aksi Cepat',
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              onSelected: (val) {
                if (val == 'hadir') {
                  presensi.setSemuaHadir();
                } else if (val == 'kosong') {
                  presensi.setSemuaKosong();
                }
              },
              itemBuilder: (context) => [
                const PopupMenuItem(
                  value: 'hadir',
                  child: Row(
                    children: [
                      Icon(
                        Icons.done_all_rounded,
                        size: 18,
                        color: AppColors.hadirTextLight,
                      ),
                      SizedBox(width: 8),
                      Text(
                        'Hadirkan Semua',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
                const PopupMenuItem(
                  value: 'kosong',
                  child: Row(
                    children: [
                      Icon(
                        Icons.clear_all_rounded,
                        size: 18,
                        color: AppColors.amberAccent,
                      ),
                      SizedBox(width: 8),
                      Text(
                        'Kosongkan Semua',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      body: Stack(
        children: [
          // Murid List
          if (presensi.isLoading)
            const Padding(
              padding: EdgeInsets.all(16),
              child: ShimmerLoadingList(count: 6, height: 72),
            )
          else if (presensi.muridList.isEmpty)
            const Center(child: Text('Tidak ada data murid di kelas ini.'))
          else
            ListView.builder(
              padding: const EdgeInsets.fromLTRB(
                16,
                12,
                16,
                125,
              ), // Bottom padding for sticky bar
              itemCount: presensi.muridList.length,
              itemBuilder: (context, index) {
                final murid = presensi.muridList[index];
                final isFilled =
                    murid.status != null && murid.status!.isNotEmpty;

                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Baris Atas: Nomor, Nama Murid (Full Width), NISM, & Badge Status
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          // Avatar Nomor dengan Status Ring
                          Container(
                            width: 36,
                            height: 36,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: isFilled
                                  ? (isDark
                                        ? const Color(0xFF142414)
                                        : const Color(0xFFE8F5E9))
                                  : (isDark
                                        ? const Color(0xFF181C18)
                                        : const Color(0xFFF1F5F9)),
                              border: Border.all(
                                color: isFilled
                                    ? (isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight)
                                    : (isDark
                                          ? AppColors.outlineDark
                                          : AppColors.outlineLight),
                                width: isFilled ? 1.5 : 1.0,
                              ),
                            ),
                            alignment: Alignment.center,
                            child: Text(
                              '${index + 1}',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: isFilled
                                    ? (isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight)
                                    : (isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E)),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),

                          // Nama Murid & NISM (Spacious & Jelas)
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  murid.nama,
                                  style: const TextStyle(
                                    fontSize: 14,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: -0.2,
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 3),
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
                          const SizedBox(width: 8),

                          // Badge Status Kehadiran Terpilih / Belum
                          _buildStatusBadge(murid.status, isDark),
                        ],
                      ),

                      const SizedBox(height: 12),

                      // Garis Pembatas Halus
                      Divider(
                        height: 1,
                        thickness: 0.8,
                        color: isDark
                            ? AppColors.outlineDark.withValues(alpha: 0.4)
                            : AppColors.outlineLight.withValues(alpha: 0.7),
                      ),

                      const SizedBox(height: 10),

                      // Baris 5 Tombol Presensi (H, S, I, A, D) Fleksibel & Mudah Ditekan
                      Row(
                        children: [
                          Expanded(
                            child: StatusPresensiChip(
                              status: 'H',
                              label: 'Hadir',
                              isSelected: murid.status == 'Hadir',
                              onTap: () => presensi.updateMuridStatus(
                                murid.muridId,
                                'Hadir',
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: StatusPresensiChip(
                              status: 'S',
                              label: 'Sakit',
                              isSelected: murid.status == 'Sakit',
                              onTap: () => presensi.updateMuridStatus(
                                murid.muridId,
                                'Sakit',
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: StatusPresensiChip(
                              status: 'I',
                              label: 'Izin',
                              isSelected: murid.status == 'Izin',
                              onTap: () => presensi.updateMuridStatus(
                                murid.muridId,
                                'Izin',
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: StatusPresensiChip(
                              status: 'A',
                              label: 'Alpha',
                              isSelected: murid.status == 'Alpha',
                              onTap: () => presensi.updateMuridStatus(
                                murid.muridId,
                                'Alpha',
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: StatusPresensiChip(
                              status: 'D',
                              label: 'Dispen',
                              isSelected: murid.status == 'Dispensasi',
                              onTap: () => presensi.updateMuridStatus(
                                murid.muridId,
                                'Dispensasi',
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),

          // Sticky Bottom Action Bar with Live Summary Counter
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: Container(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
              decoration: BoxDecoration(
                color: isDark
                    ? const Color(0xF2000000)
                    : const Color(0xF2FAF9F6),
                border: Border(
                  top: BorderSide(
                    color: isDark
                        ? AppColors.outlineDark
                        : AppColors.outlineLight,
                  ),
                ),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Live Counters Summary Row
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        if (presensi.countBelumDiisi > 0) ...[
                          _buildSummaryPill(
                            'Belum',
                            presensi.countBelumDiisi,
                            AppColors.amberAccent,
                            isDark,
                          ),
                          const SizedBox(width: 10),
                        ],
                        _buildSummaryPill(
                          'Hadir',
                          presensi.countHadir,
                          AppColors.hadirTextLight,
                          isDark,
                        ),
                        const SizedBox(width: 10),
                        _buildSummaryPill(
                          'Sakit',
                          presensi.countSakit,
                          AppColors.sakitTextLight,
                          isDark,
                        ),
                        const SizedBox(width: 10),
                        _buildSummaryPill(
                          'Izin',
                          presensi.countIzin,
                          AppColors.izinTextLight,
                          isDark,
                        ),
                        const SizedBox(width: 10),
                        _buildSummaryPill(
                          'Alpha',
                          presensi.countAlpha,
                          AppColors.alphaTextLight,
                          isDark,
                        ),
                        const SizedBox(width: 10),
                        _buildSummaryPill(
                          'Disp',
                          presensi.countDispensasi,
                          AppColors.dispensasiTextLight,
                          isDark,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 10),

                  // Submit Button
                  ElevatedButton(
                    onPressed: (presensi.isSaving || presensi.isLoading)
                        ? null
                        : _handleSimpan,
                    child: presensi.isSaving
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : Text(
                            presensi.countBelumDiisi == 0
                                ? 'Simpan Semua Presensi (${presensi.totalMurid} Murid)'
                                : 'Simpan Presensi (${presensi.countSudahDiisi}/${presensi.totalMurid} Diisi)',
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
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

  Widget _buildStatusBadge(String? status, bool isDark) {
    final isFilled = status != null && status.isNotEmpty;
    if (!isFilled) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: AppColors.amberAccent.withValues(alpha: 0.15),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: AppColors.amberAccent.withValues(alpha: 0.4),
            width: 0.8,
          ),
        ),
        child: const Text(
          'Belum',
          style: TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.bold,
            color: AppColors.amberAccent,
          ),
        ),
      );
    }

    Color bg;
    Color text;
    Color border;

    switch (status) {
      case 'Hadir':
        bg = isDark ? AppColors.hadirBgDark : AppColors.hadirBgLight;
        text = isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight;
        border = isDark ? AppColors.hadirTextDark : const Color(0xFF86EFAC);
        break;
      case 'Sakit':
        bg = isDark ? AppColors.sakitBgDark : AppColors.sakitBgLight;
        text = isDark ? AppColors.sakitTextDark : AppColors.sakitTextLight;
        border = isDark ? AppColors.sakitTextDark : const Color(0xFFFDE68A);
        break;
      case 'Izin':
        bg = isDark ? AppColors.izinBgDark : AppColors.izinBgLight;
        text = isDark ? AppColors.izinTextDark : AppColors.izinTextLight;
        border = isDark ? AppColors.izinTextDark : const Color(0xFF93C5FD);
        break;
      case 'Alpha':
        bg = isDark ? AppColors.alphaBgDark : AppColors.alphaBgLight;
        text = isDark ? AppColors.alphaTextDark : AppColors.alphaTextLight;
        border = isDark ? AppColors.alphaTextDark : const Color(0xFFFCA5A5);
        break;
      case 'Dispensasi':
      default:
        bg = isDark ? AppColors.dispensasiBgDark : AppColors.dispensasiBgLight;
        text = isDark
            ? AppColors.dispensasiTextDark
            : AppColors.dispensasiTextLight;
        border = isDark
            ? AppColors.dispensasiTextDark
            : const Color(0xFFD8B4FE);
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: border.withValues(alpha: 0.5), width: 0.8),
      ),
      child: Text(
        status,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.bold,
          color: text,
        ),
      ),
    );
  }

  Widget _buildSummaryPill(String label, int count, Color color, bool isDark) {
    return Row(
      children: [
        Container(
          width: 8,
          height: 8,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 4),
        Text(
          '$label: $count',
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.bold,
            color: isDark ? const Color(0xFFE2E3DD) : const Color(0xFF191C19),
          ),
        ),
      ],
    );
  }
}
