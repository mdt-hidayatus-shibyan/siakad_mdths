import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_motion.dart';
import '../../../providers/presensi_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';
import '../../widgets/status_presensi_chip.dart';

class FormPresensiScreen extends StatefulWidget {
  final int? jadwalId;
  final String mapel;
  final String ruangan;
  final String jam;
  final DateTime? tanggal;
  final bool isBadal;
  final String? guruUtama;
  final String? statusUstadz;
  final String? alasanBadal;
  final bool isEvent;
  final int? kalendarId;
  final int? ruanganId;
  final String? sesi;

  const FormPresensiScreen({
    super.key,
    this.jadwalId,
    required this.mapel,
    required this.ruangan,
    required this.jam,
    this.tanggal,
    this.isBadal = false,
    this.guruUtama,
    this.statusUstadz,
    this.alasanBadal,
    this.isEvent = false,
    this.kalendarId,
    this.ruanganId,
    this.sesi,
  });

  @override
  State<FormPresensiScreen> createState() => _FormPresensiScreenState();
}

class _FormPresensiScreenState extends State<FormPresensiScreen> {
  late String _statusUstadz;
  late String _alasanBadal;

  @override
  void initState() {
    super.initState();
    _statusUstadz = widget.statusUstadz ?? 'Izin';
    _alasanBadal = widget.alasanBadal ?? '';
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (widget.isEvent &&
          widget.kalendarId != null &&
          widget.ruanganId != null &&
          widget.sesi != null) {
        context.read<PresensiProvider>().fetchMuridKegiatan(
          widget.kalendarId!,
          widget.ruanganId!,
          widget.sesi!,
          widget.tanggal,
        );
      } else {
        context.read<PresensiProvider>().fetchMurid(
          widget.jadwalId ?? 0,
          widget.tanggal,
        );
      }
    });
  }

  void _editAlasanBadalModal(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    String tempStatus = _statusUstadz;
    final textController = TextEditingController(text: _alasanBadal);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setSheetState) {
          return Container(
            padding: EdgeInsets.only(
              left: 20,
              right: 20,
              top: 20,
              bottom:
                  MediaQuery.of(context).viewInsets.bottom +
                  MediaQuery.of(context).padding.bottom +
                  20,
            ),
            decoration: BoxDecoration(
              color: isDark ? AppColors.surfaceContainerDark : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(24),
              ),
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: isDark ? Colors.white24 : Colors.black12,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: isDark
                              ? AppColors.primaryDark.withValues(alpha: 0.2)
                              : AppColors.primaryLight.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Icon(
                          Icons.edit_note_rounded,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                          size: 20,
                        ),
                      ),
                      const SizedBox(width: 10),
                      const Text(
                        'Ubah Alasan Penggantian Guru',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    'Status Kehadiran Guru Utama:',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: ['Izin', 'Sakit', 'Alpha', 'Kosong'].map((st) {
                      final isSel = tempStatus == st;
                      return ChoiceChip(
                        label: Text(st),
                        selected: isSel,
                        onSelected: (val) {
                          if (val) {
                            setSheetState(() => tempStatus = st);
                          }
                        },
                        selectedColor: isDark
                            ? AppColors.primaryContainerDark
                            : AppColors.primaryContainerLight,
                        labelStyle: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: isSel
                              ? (isDark
                                    ? AppColors.onPrimaryContainerDark
                                    : AppColors.onPrimaryContainerLight)
                              : (isDark ? Colors.white70 : Colors.black87),
                        ),
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    'Pilihan Alasan Cepat:',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 6,
                    runSpacing: 6,
                    children:
                        [
                          'Sakit / Kurang Sehat',
                          'Izin Acara Keluarga',
                          'Tugas Luar / Lembaga',
                          'Terlambat / Berhalangan',
                          'Keperluan Mendesak',
                        ].map((alasan) {
                          return ActionChip(
                            label: Text(alasan),
                            onPressed: () {
                              setSheetState(() {
                                textController.text = alasan;
                              });
                            },
                            backgroundColor: isDark
                                ? AppColors.surfaceContainerHighDark
                                : const Color(0xFFF1F5F9),
                            labelStyle: TextStyle(
                              fontSize: 11,
                              color: isDark
                                  ? Colors.white70
                                  : const Color(0xFF334155),
                            ),
                          );
                        }).toList(),
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    'Catatan / Keterangan Alasan:',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: textController,
                    maxLines: 2,
                    decoration: InputDecoration(
                      hintText: 'Tuliskan alasan penggantian...',
                      hintStyle: TextStyle(
                        fontSize: 12,
                        color: isDark ? Colors.white38 : Colors.black38,
                      ),
                      filled: true,
                      fillColor: isDark
                          ? AppColors.surfaceContainerLowDark
                          : const Color(0xFFF8FAFC),
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
                          color: isDark
                              ? AppColors.outlineDark
                              : AppColors.outlineLight,
                        ),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide(
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                          width: 1.5,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  FilledButton(
                    onPressed: () {
                      setState(() {
                        _statusUstadz = tempStatus;
                        _alasanBadal = textController.text.trim();
                      });
                      Navigator.pop(ctx);
                    },
                    style: FilledButton.styleFrom(
                      backgroundColor: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                      foregroundColor: isDark
                          ? AppColors.onPrimaryDark
                          : AppColors.onPrimaryLight,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text('Terapkan Alasan'),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Future<void> _handleSimpan() async {
    final presensi = context.read<PresensiProvider>();
    final bool success;
    if (widget.isEvent &&
        widget.kalendarId != null &&
        widget.ruanganId != null &&
        widget.sesi != null) {
      success = await presensi.simpanPresensiKegiatan(
        kalendarId: widget.kalendarId!,
        ruanganId: widget.ruanganId!,
        sesi: widget.sesi!,
        customDate: widget.tanggal,
      );
    } else {
      success = await presensi.simpanPresensi(
        widget.jadwalId ?? 0,
        widget.tanggal,
        widget.isBadal,
        widget.isBadal ? _statusUstadz : null,
        widget.isBadal ? (_alasanBadal.isNotEmpty ? _alasanBadal : null) : null,
      );
    }

    if (!mounted) return;
    if (success) {
      final msg = widget.isEvent
          ? 'Presensi ${widget.mapel} (${widget.ruangan}) sesi ${widget.sesi} berhasil disimpan!'
          : (widget.isBadal
                ? 'Presensi kelas ${widget.ruangan} berhasil disimpan sebagai Guru Pengganti (Badal)!'
                : 'Presensi kelas ${widget.ruangan} berhasil disimpan!');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: AppColors.hadirTextLight,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
      );
      Navigator.pop(context, true);
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
              borderRadius: BorderRadius.circular(14),
              color: isDark ? AppColors.surfaceContainerHighDark : Colors.white,
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
              padding: EdgeInsets.fromLTRB(
                16,
                12,
                16,
                125 + MediaQuery.of(context).padding.bottom,
              ), // Bottom padding for sticky bar
              itemCount: presensi.muridList.length + (widget.isBadal ? 1 : 0),
              itemBuilder: (context, index) {
                if (widget.isBadal && index == 0) {
                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: isDark
                          ? AppColors.primaryContainerDark.withValues(
                              alpha: 0.25,
                            )
                          : AppColors.primaryContainerLight.withValues(
                              alpha: 0.6,
                            ),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: isDark
                            ? AppColors.primaryDark.withValues(alpha: 0.4)
                            : AppColors.primaryLight.withValues(alpha: 0.35),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? AppColors.primaryDark.withValues(
                                        alpha: 0.2,
                                      )
                                    : AppColors.primaryLight.withValues(
                                        alpha: 0.15,
                                      ),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Icon(
                                Icons.swap_horiz_rounded,
                                size: 16,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'Mode Guru Pengganti (Badal)',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.onPrimaryContainerDark
                                      : AppColors.onPrimaryContainerLight,
                                ),
                              ),
                            ),
                            InkWell(
                              onTap: () => _editAlasanBadalModal(context),
                              borderRadius: BorderRadius.circular(8),
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? AppColors.surfaceContainerHighDark
                                      : Colors.white,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                    color: isDark
                                        ? AppColors.primaryDark.withValues(
                                            alpha: 0.4,
                                          )
                                        : AppColors.primaryLight.withValues(
                                            alpha: 0.4,
                                          ),
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      Icons.edit_rounded,
                                      size: 12,
                                      color: isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight,
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      'Ubah Alasan',
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
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 6,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? AppColors.surfaceContainerLowDark
                                : Colors.white.withValues(alpha: 0.85),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: isDark
                                  ? AppColors.outlineDark.withValues(alpha: 0.3)
                                  : AppColors.outlineLight.withValues(
                                      alpha: 0.7,
                                    ),
                            ),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              if (widget.guruUtama != null) ...[
                                Text(
                                  'Menggantikan: ${widget.guruUtama}',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                    color: isDark
                                        ? Colors.white70
                                        : const Color(0xFF334155),
                                  ),
                                ),
                                const SizedBox(height: 2),
                              ],
                              Row(
                                children: [
                                  Text(
                                    'Status: $_statusUstadz',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight,
                                    ),
                                  ),
                                  if (_alasanBadal.isNotEmpty) ...[
                                    Expanded(
                                      child: Text(
                                        ' • $_alasanBadal',
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: isDark
                                              ? Colors.white70
                                              : const Color(0xFF475569),
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
                  );
                }

                final muridIndex = widget.isBadal ? index - 1 : index;
                final murid = presensi.muridList[muridIndex];
                final isFilled =
                    murid.status != null && murid.status!.isNotEmpty;

                return M3StaggeredFadeSlide(
                  index: muridIndex,
                  child: GlassCard(
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
                              borderRadius: BorderRadius.circular(12),
                              color: isFilled
                                  ? (isDark
                                        ? AppColors.primaryContainerDark
                                        : AppColors.primaryContainerLight)
                                  : (isDark
                                        ? AppColors.surfaceContainerHighDark
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
                              '${muridIndex + 1}',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: isFilled
                                    ? (isDark
                                          ? AppColors.onPrimaryContainerDark
                                          : AppColors.onPrimaryContainerLight)
                                    : (isDark
                                          ? Colors.white60
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
              padding: EdgeInsets.fromLTRB(
                16,
                12,
                16,
                20 + MediaQuery.of(context).padding.bottom,
              ),
              decoration: BoxDecoration(
                color: isDark
                    ? const Color(0xF2000000)
                    : const Color(0xF2FAF9F6),
                border: Border(
                  top: BorderSide(
                    color: isDark
                        ? AppColors.outlineVariantDark
                        : AppColors.outlineVariantLight,
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
                          const SizedBox(width: 8),
                        ],
                        _buildSummaryPill(
                          'Hadir',
                          presensi.countHadir,
                          AppColors.hadirTextLight,
                          isDark,
                        ),
                        const SizedBox(width: 8),
                        _buildSummaryPill(
                          'Sakit',
                          presensi.countSakit,
                          AppColors.sakitTextLight,
                          isDark,
                        ),
                        const SizedBox(width: 8),
                        _buildSummaryPill(
                          'Izin',
                          presensi.countIzin,
                          AppColors.izinTextLight,
                          isDark,
                        ),
                        const SizedBox(width: 8),
                        _buildSummaryPill(
                          'Alpha',
                          presensi.countAlpha,
                          AppColors.alphaTextLight,
                          isDark,
                        ),
                        const SizedBox(width: 8),
                        _buildSummaryPill(
                          'Disp',
                          presensi.countDispensasi,
                          AppColors.dispensasiTextLight,
                          isDark,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Submit Button
                  M3ScaleOnPress(
                    onTap: (presensi.isSaving || presensi.isLoading)
                        ? null
                        : _handleSimpan,
                    pressedScale: 0.96,
                    borderRadius: BorderRadius.circular(16),
                    child: SizedBox(
                      width: double.infinity,
                      child: FilledButton(
                        onPressed: (presensi.isSaving || presensi.isLoading)
                            ? null
                            : _handleSimpan,
                        style: FilledButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                          backgroundColor: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                          foregroundColor: isDark
                              ? AppColors.onPrimaryDark
                              : AppColors.onPrimaryLight,
                        ),
                        child: presensi.isSaving
                            ? SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: isDark
                                      ? AppColors.onPrimaryDark
                                      : Colors.white,
                                ),
                              )
                            : Text(
                                presensi.countBelumDiisi == 0
                                    ? 'Simpan Semua Presensi (${presensi.totalMurid} Murid)'
                                    : 'Simpan Presensi (${presensi.countSudahDiisi}/${presensi.totalMurid} Diisi)',
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: -0.2,
                                ),
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
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: isDark
            ? color.withValues(alpha: 0.16)
            : color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: isDark
              ? color.withValues(alpha: 0.35)
              : color.withValues(alpha: 0.25),
          width: 0.8,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 7,
            height: 7,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 5),
          Text(
            '$label $count',
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.bold,
              color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF1E293B),
            ),
          ),
        ],
      ),
    );
  }
}
