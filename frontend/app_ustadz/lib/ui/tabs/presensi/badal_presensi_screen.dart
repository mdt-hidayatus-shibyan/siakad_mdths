import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/badal_model.dart';
import '../../../providers/badal_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';
import 'form_presensi_screen.dart';

class BadalPresensiScreen extends StatefulWidget {
  const BadalPresensiScreen({super.key});

  @override
  State<BadalPresensiScreen> createState() => _BadalPresensiScreenState();
}

class _BadalPresensiScreenState extends State<BadalPresensiScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final p = context.read<BadalProvider>();
      p.fetchRuanganList();
    });
  }

  Future<void> _pickDate(BuildContext context) async {
    final p = context.read<BadalProvider>();
    final picked = await showDatePicker(
      context: context,
      initialDate: p.selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 90)),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );
    if (picked != null && picked != p.selectedDate) {
      HapticHelper.light();
      p.setSelectedDate(picked);
    }
  }

  void _showInfoDialog(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        backgroundColor: isDark ? AppColors.surfaceContainerDark : Colors.white,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: isDark
                    ? AppColors.primaryDark.withValues(alpha: 0.2)
                    : AppColors.primaryLight.withValues(alpha: 0.15),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.swap_horiz_rounded,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
                size: 24,
              ),
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Text(
                'Guru Pengganti (Badal)',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: const Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Fitur ini memungkinkan setiap Ustadz/Pengajar menjadi Guru Pengganti (Badal) untuk kelas yang pengajarnya berhalangan hadir.',
              style: TextStyle(fontSize: 13, height: 1.4),
            ),
            SizedBox(height: 12),
            Text(
              'Langkah Penggunaan:\n'
              '1. Pilih Ruangan / Kelas yang ingin digantikan.\n'
              '2. Tentukan Tanggal KBM yang dituju.\n'
              '3. Tinjau jadwal pelajaran pada ruangan tersebut.\n'
              '4. Tekan "Gantikan & Isi Presensi" untuk mencatat presensi murid secara otomatis atas nama Anda.',
              style: TextStyle(fontSize: 12, height: 1.5),
            ),
          ],
        ),
        actions: [
          FilledButton(
            onPressed: () => Navigator.pop(ctx),
            style: FilledButton.styleFrom(
              backgroundColor: isDark
                  ? AppColors.primaryDark
                  : AppColors.primaryLight,
              foregroundColor: isDark
                  ? AppColors.onPrimaryDark
                  : AppColors.onPrimaryLight,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Text('Mengerti'),
          ),
        ],
      ),
    );
  }

  void _showRuanganPickerBottomSheet(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final badalProvider = context.read<BadalProvider>();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setSheetState) {
            String searchQuery = '';
            List<BadalRuanganItem> filteredList =
                List.from(badalProvider.ruanganList)..sort((a, b) {
                  final comp = a.urutanLevel.compareTo(b.urutanLevel);
                  if (comp != 0) return comp;
                  return a.namaRuangan.compareTo(b.namaRuangan);
                });

            void filter(String query) {
              setSheetState(() {
                searchQuery = query.toLowerCase().trim();
                filteredList =
                    badalProvider.ruanganList.where((r) {
                      final matchName = r.namaRuangan.toLowerCase().contains(
                        searchQuery,
                      );
                      final matchLevel = r.level.toLowerCase().contains(
                        searchQuery,
                      );
                      final matchWali = r.waliKelas.toLowerCase().contains(
                        searchQuery,
                      );
                      return matchName || matchLevel || matchWali;
                    }).toList()..sort((a, b) {
                      final comp = a.urutanLevel.compareTo(b.urutanLevel);
                      if (comp != 0) return comp;
                      return a.namaRuangan.compareTo(b.namaRuangan);
                    });
              });
            }

            return Container(
              height: MediaQuery.of(context).size.height * 0.75,
              decoration: BoxDecoration(
                color: isDark ? AppColors.surfaceContainerDark : Colors.white,
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(24),
                ),
              ),
              child: Column(
                children: [
                  const SizedBox(height: 12),
                  // Drag Handle
                  Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: isDark ? Colors.white24 : Colors.black12,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    child: Row(
                      children: [
                        Icon(
                          Icons.meeting_room_rounded,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                        const SizedBox(width: 10),
                        const Text(
                          'Pilih Ruangan / Kelas',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                  // Search Box
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: TextField(
                      autofocus: false,
                      onChanged: filter,
                      decoration: InputDecoration(
                        hintText: 'Cari ruangan, kelas, atau wali...',
                        hintStyle: TextStyle(
                          fontSize: 13,
                          color: isDark ? Colors.white38 : Colors.black38,
                        ),
                        prefixIcon: const Icon(Icons.search_rounded, size: 20),
                        filled: true,
                        fillColor: isDark
                            ? AppColors.surfaceContainerHighDark
                            : const Color(0xFFF1F5F9),
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
                  ),
                  const SizedBox(height: 10),
                  const Divider(height: 1),
                  // Ruangan List
                  Expanded(
                    child: filteredList.isEmpty
                        ? const Center(
                            child: Text(
                              'Ruangan tidak ditemukan',
                              style: TextStyle(fontSize: 13),
                            ),
                          )
                        : ListView.separated(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 12,
                            ),
                            itemCount: filteredList.length,
                            separatorBuilder: (_, __) =>
                                const SizedBox(height: 8),
                            itemBuilder: (ctx, idx) {
                              final item = filteredList[idx];
                              final isSelected =
                                  badalProvider.selectedRuangan?.id == item.id;

                              return InkWell(
                                onTap: () {
                                  HapticHelper.selection();
                                  badalProvider.setSelectedRuangan(item);
                                  Navigator.pop(ctx);
                                },
                                borderRadius: BorderRadius.circular(14),
                                child: Container(
                                  padding: const EdgeInsets.all(14),
                                  decoration: BoxDecoration(
                                    color: isSelected
                                        ? (isDark
                                              ? AppColors.primaryContainerDark
                                              : AppColors.primaryContainerLight)
                                        : (isDark
                                              ? AppColors
                                                    .surfaceContainerLowDark
                                              : const Color(0xFFF8FAFC)),
                                    borderRadius: BorderRadius.circular(14),
                                    border: Border.all(
                                      color: isSelected
                                          ? (isDark
                                                ? AppColors.primaryDark
                                                : AppColors.primaryLight)
                                          : (isDark
                                                ? AppColors.outlineDark
                                                : AppColors.outlineLight),
                                      width: isSelected ? 1.5 : 1.0,
                                    ),
                                  ),
                                  child: Row(
                                    children: [
                                      Container(
                                        width: 40,
                                        height: 40,
                                        decoration: BoxDecoration(
                                          shape: BoxShape.circle,
                                          color: isDark
                                              ? AppColors
                                                    .surfaceContainerHighDark
                                              : Colors.white,
                                        ),
                                        child: Icon(
                                          Icons.door_sliding_rounded,
                                          size: 20,
                                          color: isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight,
                                        ),
                                      ),
                                      const SizedBox(width: 14),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Row(
                                              children: [
                                                Text(
                                                  item.namaRuangan,
                                                  style: const TextStyle(
                                                    fontSize: 14,
                                                    fontWeight: FontWeight.bold,
                                                  ),
                                                ),
                                                const SizedBox(width: 8),
                                                Container(
                                                  padding:
                                                      const EdgeInsets.symmetric(
                                                        horizontal: 6,
                                                        vertical: 2,
                                                      ),
                                                  decoration: BoxDecoration(
                                                    color: isDark
                                                        ? Colors.white12
                                                        : Colors.black12,
                                                    borderRadius:
                                                        BorderRadius.circular(
                                                          6,
                                                        ),
                                                  ),
                                                  child: Text(
                                                    item.level,
                                                    style: const TextStyle(
                                                      fontSize: 10,
                                                      fontWeight:
                                                          FontWeight.w600,
                                                    ),
                                                  ),
                                                ),
                                              ],
                                            ),
                                            const SizedBox(height: 3),
                                            Text(
                                              'Wali: ${item.waliKelas} • ${item.totalMurid} Murid',
                                              style: TextStyle(
                                                fontSize: 12,
                                                color: isDark
                                                    ? Colors.white60
                                                    : const Color(0xFF64748B),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      if (isSelected)
                                        Icon(
                                          Icons.check_circle_rounded,
                                          color: isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight,
                                        ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  void _showAlasanBadalModal(BuildContext context, BadalJadwalItem item) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final badal = context.read<BadalProvider>();
    final ruanganName = badal.selectedRuangan?.namaRuangan ?? 'Ruangan';

    String selectedStatus =
        (item.ustadzStatus == 'Sakit' || item.ustadzStatus == 'Alpha')
        ? item.ustadzStatus
        : 'Izin';
    final textController = TextEditingController(
      text: item.ustadzKeterangan ?? '',
    );

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
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
                                : AppColors.primaryLight.withValues(
                                    alpha: 0.15,
                                  ),
                            shape: BoxShape.circle,
                          ),
                          child: Icon(
                            Icons.swap_horiz_rounded,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                            size: 20,
                          ),
                        ),
                        const SizedBox(width: 10),
                        const Expanded(
                          child: Text(
                            'Alasan Penggantian Guru',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Info Guru Asli & Jadwal
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: isDark
                            ? AppColors.surfaceContainerLowDark
                            : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isDark
                              ? AppColors.outlineDark
                              : const Color(0xFFE2E8F0),
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text(
                                item.mapel,
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(width: 6),
                              Text(
                                '• ${item.jam}',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: isDark
                                      ? Colors.white60
                                      : const Color(0xFF64748B),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Guru Pengampu: ${item.guruPengampu}',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                              color: isDark
                                  ? Colors.white70
                                  : const Color(0xFF334155),
                            ),
                          ),
                          Text(
                            'Ruangan / Kelas: $ruanganName',
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark
                                  ? Colors.white60
                                  : const Color(0xFF64748B),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Pilihan Status Guru Utama
                    const Text(
                      'Status Kehadiran Guru Utama:',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: ['Izin', 'Sakit', 'Alpha', 'Kosong'].map((st) {
                        final isSel = selectedStatus == st;
                        return ChoiceChip(
                          label: Text(st),
                          selected: isSel,
                          onSelected: (val) {
                            if (val) {
                              setSheetState(() => selectedStatus = st);
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

                    // Template Alasan Cepat
                    const Text(
                      'Pilih Alasan Cepat:',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
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

                    // Input Catatan / Keterangan
                    const Text(
                      'Catatan / Alasan Penggantian:',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: textController,
                      maxLines: 2,
                      decoration: InputDecoration(
                        hintText: 'Misal: Izin ada keperluan keluarga...',
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
                                : const Color(0xFFE2E8F0),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 18),

                    // Tombol Lanjut ke Presensi
                    FilledButton.icon(
                      onPressed: () async {
                        HapticHelper.medium();
                        final alasanText = textController.text.trim();
                        Navigator.pop(ctx);

                        final updated = await Navigator.push<bool>(
                          context,
                          MaterialPageRoute(
                            builder: (_) => FormPresensiScreen(
                              jadwalId: item.jadwalId,
                              mapel: item.mapel,
                              ruangan: ruanganName,
                              jam: item.jam,
                              tanggal: badal.selectedDate,
                              isBadal: true,
                              guruUtama: item.guruPengampu,
                              statusUstadz: selectedStatus,
                              alasanBadal: alasanText,
                            ),
                          ),
                        );
                        if (updated == true && mounted) {
                          badal.fetchJadwalRuangan();
                        }
                      },
                      icon: const Icon(Icons.arrow_forward_rounded, size: 18),
                      label: const Text('Lanjut Isi Presensi Murid'),
                      style: FilledButton.styleFrom(
                        backgroundColor: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                        foregroundColor: isDark
                            ? AppColors.onPrimaryDark
                            : AppColors.onPrimaryLight,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Color _getUstadzStatusColor(String status, bool isDark) {
    switch (status) {
      case 'Hadir':
        return AppColors.hadirTextLight;
      case 'Izin':
        return AppColors.izinTextLight;
      case 'Sakit':
        return AppColors.sakitTextLight;
      case 'Alpha':
        return AppColors.alphaTextLight;
      case 'Bebas KBM':
        return AppColors.amberAccent;
      case 'Kosong':
        return Colors.blueGrey;
      default:
        return isDark ? Colors.white38 : Colors.black38;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final badal = context.watch<BadalProvider>();
    final isToday =
        DateHelper.toYmd(badal.selectedDate) ==
        DateHelper.toYmd(DateTime.now());

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Presensi Guru Pengganti',
        subtitleText: 'Badal Mengajar & Presensi Murid',
        actions: [
          CircularIconButton(
            icon: Icons.info_outline_rounded,
            iconSize: 20,
            tooltip: 'Info Guru Pengganti',
            onPressed: () => _showInfoDialog(context),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: badal.refresh,
        child: ListView(
          padding: EdgeInsets.fromLTRB(
            16,
            12,
            16,
            MediaQuery.of(context).padding.bottom + 24,
          ),
          children: [
            // 1. Pemilih Ruangan / Kelas
            GlassCard(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Icon(
                            Icons.meeting_room_rounded,
                            size: 18,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 6),
                          const Text(
                            'Ruangan / Kelas Yang Digantikan',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      InkWell(
                        onTap: () => _showRuanganPickerBottomSheet(context),
                        borderRadius: BorderRadius.circular(8),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 4,
                          ),
                          child: Row(
                            children: [
                              Text(
                                'Ganti',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                              const SizedBox(width: 2),
                              Icon(
                                Icons.arrow_drop_down_rounded,
                                size: 18,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  InkWell(
                    onTap: () => _showRuanganPickerBottomSheet(context),
                    borderRadius: BorderRadius.circular(14),
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: isDark
                            ? AppColors.surfaceContainerLowDark
                            : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isDark
                              ? AppColors.outlineDark
                              : const Color(0xFFE2E8F0),
                        ),
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? AppColors.primaryContainerDark
                                  : AppColors.primaryContainerLight,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Icon(
                              Icons.school_rounded,
                              size: 20,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Text(
                                      badal.selectedRuangan?.namaRuangan ??
                                          'Pilih Ruangan...',
                                      style: const TextStyle(
                                        fontSize: 14,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    if (badal.selectedRuangan != null) ...[
                                      const SizedBox(width: 8),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 6,
                                          vertical: 2,
                                        ),
                                        decoration: BoxDecoration(
                                          color: isDark
                                              ? Colors.white12
                                              : Colors.black12,
                                          borderRadius: BorderRadius.circular(
                                            6,
                                          ),
                                        ),
                                        child: Text(
                                          badal.selectedRuangan!.level,
                                          style: const TextStyle(
                                            fontSize: 10,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  badal.selectedRuangan != null
                                      ? 'Wali Ruangan: ${badal.selectedRuangan!.waliKelas} • ${badal.selectedRuangan!.totalMurid} Murid'
                                      : 'Ketuk untuk memilih ruangan yang ingin digantikan',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: isDark
                                        ? Colors.white60
                                        : const Color(0xFF64748B),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Icon(
                            Icons.chevron_right_rounded,
                            color: isDark ? Colors.white38 : Colors.black38,
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),

            // 2. Baris Pemilih Tanggal
            GlassCard(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  IconButton(
                    visualDensity: VisualDensity.compact,
                    icon: const Icon(Icons.chevron_left_rounded),
                    onPressed: () => badal.shiftDate(-1),
                  ),
                  InkWell(
                    onTap: () => _pickDate(context),
                    borderRadius: BorderRadius.circular(12),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 6,
                      ),
                      child: Row(
                        children: [
                          Icon(
                            Icons.calendar_today_rounded,
                            size: 16,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            DateHelper.formatTanggalIndo(badal.selectedDate),
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          if (isToday) ...[
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? AppColors.primaryContainerDark
                                    : AppColors.primaryContainerLight,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                'Hari Ini',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  IconButton(
                    visualDensity: VisualDensity.compact,
                    icon: const Icon(Icons.chevron_right_rounded),
                    onPressed: () => badal.shiftDate(1),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // 3. Status Khusus / State Loading / List Jadwal
            if (badal.isLoadingJadwal || badal.isLoadingRuangan) ...[
              const ShimmerLoadingList(count: 3, height: 130),
            ] else if (badal.selectedRuangan == null) ...[
              GlassCard(
                padding: const EdgeInsets.all(24),
                child: Column(
                  children: [
                    Icon(
                      Icons.touch_app_rounded,
                      size: 48,
                      color: isDark ? Colors.white38 : Colors.black38,
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'Pilih Ruangan Terlebih Dahulu',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Silakan pilih ruangan di atas untuk melihat jadwal pelajaran yang akan digantikan.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? Colors.white60 : Colors.black54,
                      ),
                    ),
                    const SizedBox(height: 16),
                    FilledButton.icon(
                      onPressed: () => _showRuanganPickerBottomSheet(context),
                      icon: const Icon(Icons.meeting_room_rounded, size: 18),
                      label: const Text('Pilih Ruangan'),
                      style: FilledButton.styleFrom(
                        backgroundColor: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                  ],
                ),
              ),
            ] else if (badal.isLibur) ...[
              GlassCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 28,
                ),
                child: Column(
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF382305)
                            : const Color(0xFFFEF3C7),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.beach_access_rounded,
                        size: 32,
                        color: AppColors.amberAccent,
                      ),
                    ),
                    const SizedBox(height: 14),
                    const Text(
                      'Hari Libur Madrasah',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      badal.keteranganLibur ??
                          'Kegiatan Belajar Mengajar Diliburkan',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? Colors.white60 : Colors.black54,
                      ),
                    ),
                  ],
                ),
              ),
            ] else if (badal.isUjian) ...[
              GlassCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 28,
                ),
                child: Column(
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF2E1065)
                            : const Color(0xFFF3E8FF),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.assignment_turned_in_rounded,
                        size: 32,
                        color: AppColors.violetAccent,
                      ),
                    ),
                    const SizedBox(height: 14),
                    const Text(
                      'Masa Ujian Madrasah',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      badal.namaUjian ??
                          'Jadwal KBM Reguler ditiadakan selama masa ujian.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? Colors.white60 : Colors.black54,
                      ),
                    ),
                  ],
                ),
              ),
            ] else if (badal.jadwalList.isEmpty) ...[
              GlassCard(
                padding: const EdgeInsets.all(24),
                child: Column(
                  children: [
                    Icon(
                      Icons.event_busy_rounded,
                      size: 48,
                      color: isDark ? Colors.white38 : Colors.black38,
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'Tidak Ada Jadwal pada Hari ${badal.jadwalData?.hari ?? '-'}',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Tidak ditemukan sesi KBM di kelas ${badal.selectedRuangan?.namaRuangan} pada tanggal ini.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? Colors.white60 : Colors.black54,
                      ),
                    ),
                  ],
                ),
              ),
            ] else ...[
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.view_timeline_rounded,
                        size: 18,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        'Jadwal Pelajaran (${badal.jadwalList.length} Sesi)',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 10),

              // Daftar Sesi Jadwal
              ...badal.jadwalList.map((item) {
                final statusColor = _getUstadzStatusColor(
                  item.ustadzStatus,
                  isDark,
                );

                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header Card: Mapel & Jam
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? AppColors.primaryContainerDark
                                  : AppColors.primaryContainerLight,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Icon(
                              Icons.menu_book_rounded,
                              size: 20,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item.mapel,
                                  style: const TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Row(
                                  children: [
                                    Icon(
                                      Icons.access_time_rounded,
                                      size: 13,
                                      color: isDark
                                          ? Colors.white60
                                          : const Color(0xFF64748B),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      item.jam,
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w500,
                                        color: isDark
                                            ? Colors.white70
                                            : const Color(0xFF475569),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      const Divider(height: 1),
                      const SizedBox(height: 12),

                      // Info Pengampu Asli & Status Presensi Ustadz
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Guru Pengampu:',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: isDark
                                        ? Colors.white54
                                        : const Color(0xFF64748B),
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  item.guruPengampu,
                                  style: const TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          // Badge Presensi Guru
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: statusColor.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: statusColor.withValues(alpha: 0.4),
                              ),
                            ),
                            child: Text(
                              item.ustadzStatus,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: statusColor,
                              ),
                            ),
                          ),
                        ],
                      ),

                      // Jika ada info guru pengganti tercatat
                      if (item.ustadzPenggantiNama != null &&
                          item.ustadzPenggantiNama!.isNotEmpty) ...[
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 5,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? AppColors.surfaceContainerHighDark
                                : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.swap_horiz_rounded,
                                size: 14,
                                color: AppColors.skyBlueAccent,
                              ),
                              const SizedBox(width: 6),
                              Flexible(
                                child: Text(
                                  item.isSayaPengganti
                                      ? 'Anda tercatat sebagai Guru Pengganti'
                                      : 'Digantikan: ${item.ustadzPenggantiNama}',
                                  style: const TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],

                      // Jika sesi Bebas KBM
                      if (item.isBebasKbm) ...[
                        const SizedBox(height: 6),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 6,
                          ),
                          decoration: BoxDecoration(
                            color: AppColors.amberAccent.withValues(
                              alpha: 0.12,
                            ),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: AppColors.amberAccent.withValues(
                                alpha: 0.25,
                              ),
                            ),
                          ),
                          child: Row(
                            children: [
                              const Icon(
                                Icons.pause_circle_filled_rounded,
                                size: 14,
                                color: AppColors.amberAccent,
                              ),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  item.keteranganBebasKbm != null &&
                                          item.keteranganBebasKbm!.isNotEmpty
                                      ? 'Sesi Bebas KBM: ${item.keteranganBebasKbm}'
                                      : 'Sesi Bebas KBM (Presensi tidak diberlakukan)',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w600,
                                    color: isDark
                                        ? AppColors.amberAccent
                                        : const Color(0xFFB45309),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],

                      // Jika ada alasan / keterangan presensi guru tercatat
                      if (item.ustadzKeterangan != null &&
                          item.ustadzKeterangan!.isNotEmpty &&
                          !item.isBebasKbm) ...[
                        const SizedBox(height: 6),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 6,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? AppColors.surfaceContainerLowDark
                                : const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: isDark
                                  ? AppColors.outlineDark
                                  : const Color(0xFFE2E8F0),
                            ),
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Icon(
                                Icons.description_outlined,
                                size: 14,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  'Alasan: ${item.ustadzKeterangan}',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w500,
                                    color: isDark
                                        ? Colors.white70
                                        : const Color(0xFF334155),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],

                      const SizedBox(height: 12),

                      if (!item.isBebasKbm) ...[
                      // Status Presensi Murid & Tombol Aksi
                      Row(
                        children: [
                          Expanded(
                            child: Row(
                              children: [
                                Icon(
                                  item.sudahAbsenMurid
                                      ? Icons.check_circle_rounded
                                      : Icons.radio_button_unchecked_rounded,
                                  size: 15,
                                  color: item.sudahAbsenMurid
                                      ? AppColors.hadirTextLight
                                      : AppColors.amberAccent,
                                ),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    item.sudahAbsenMurid
                                        ? 'Murid Sudah Diabsen (${item.totalTerisi}/${item.totalMurid})'
                                        : 'Presensi Murid Belum Diisi',
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                      color: item.sudahAbsenMurid
                                          ? (isDark
                                                ? AppColors.hadirTextDark
                                                : AppColors.hadirTextLight)
                                          : (isDark
                                                ? AppColors.amberAccent
                                                : const Color(0xFFB45309)),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),

                      // Tombol Gantikan & Buka Presensi Murid
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton.icon(
                          onPressed: () {
                            HapticHelper.medium();
                            _showAlasanBadalModal(context, item);
                          },
                          icon: Icon(
                            item.sudahAbsenMurid
                                ? Icons.edit_note_rounded
                                : Icons.how_to_reg_rounded,
                            size: 18,
                          ),
                          label: Text(
                            item.sudahAbsenMurid
                                ? 'Ubah Presensi Murid (Badal)'
                                : 'Gantikan & Isi Presensi Murid',
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          style: FilledButton.styleFrom(
                            backgroundColor: item.sudahAbsenMurid
                                ? (isDark
                                      ? AppColors.surfaceContainerHighDark
                                      : const Color(0xFFE2E8F0))
                                : (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight),
                            foregroundColor: item.sudahAbsenMurid
                                ? (isDark
                                      ? AppColors.primaryDark
                                      : const Color(0xFF1E293B))
                                : (isDark
                                      ? AppColors.onPrimaryDark
                                      : AppColors.onPrimaryLight),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                        ),
                      ),
                    ],
                      ],
                  ),
                );
              }),
            ],
          ],
        ),
      ),
    );
  }
}
