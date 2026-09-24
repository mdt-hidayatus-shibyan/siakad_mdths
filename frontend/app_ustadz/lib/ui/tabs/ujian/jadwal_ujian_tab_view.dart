import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/akademik_provider.dart';
import '../../widgets/app_avatar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class JadwalUjianTabView extends StatefulWidget {
  const JadwalUjianTabView({super.key});

  @override
  State<JadwalUjianTabView> createState() => _JadwalUjianTabViewState();
}

class _JadwalUjianTabViewState extends State<JadwalUjianTabView> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AkademikProvider>().fetchJadwalUjian();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final onPrimaryColor = isDark
        ? AppColors.onPrimaryDark
        : AppColors.onPrimaryLight;
    final akademik = context.watch<AkademikProvider>();
    final ujianData = akademik.jadwalUjianData;
    final onlyMyJadwal = akademik.onlyMyJadwalUjian;
    final isWali = ujianData?.isWaliRuangan ?? false;
    final isAllTasks = (ujianData?.selectedRuanganId == 0);

    if (akademik.isLoadingJadwalUjian && ujianData == null) {
      return const Padding(
        padding: EdgeInsets.all(16),
        child: ShimmerLoadingList(count: 6, height: 80),
      );
    }

    if (akademik.jadwalUjianError != null && ujianData == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.error_outline_rounded,
                size: 48,
                color: AppColors.roseDanger,
              ),
              const SizedBox(height: 12),
              Text(
                akademik.jadwalUjianError!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 14),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: () => akademik.fetchJadwalUjian(),
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('Coba Lagi'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryColor,
                  foregroundColor: onPrimaryColor,
                ),
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => akademik.fetchJadwalUjian(
        ruanganId: akademik.selectedRuanganUjianId,
        ujianId: akademik.selectedAgendaUjianId,
      ),
      color: primaryColor,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
        children: [
          // ===================================================================
          // 1. FILTER RUANGAN KELAS & AGENDA UJIAN
          // ===================================================================
          GlassCard(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header Filter & Icon
                Row(
                  children: [
                    Icon(
                      Icons.filter_list_rounded,
                      size: 18,
                      color: primaryColor,
                    ),
                    const SizedBox(width: 8),
                    const Text(
                      'Pilih Ruangan & Agenda Ujian',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Mode Switcher: [ Per Ruangan Kelas ] | [ ⭐ Semua Tugas Saya ]
                Container(
                  margin: const EdgeInsets.only(bottom: 14),
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: isDark
                        ? AppColors.surfaceContainerDark
                        : AppColors.surfaceLight,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: isDark
                          ? AppColors.outlineDark
                          : AppColors.outlineLight,
                      width: 0.8,
                    ),
                  ),
                  child: Row(
                    children: [
                      // Mode 1: Per Ruangan Kelas
                      Expanded(
                        child: InkWell(
                          onTap: () {
                            if (isAllTasks) {
                              HapticHelper.light();
                              // Cari ruangan binaan atau ruangan non-0 pertama
                              final firstRoom = ujianData?.daftarRuangan
                                  .firstWhere(
                                    (r) => r.id > 0,
                                    orElse: () => ujianData.daftarRuangan.first,
                                  );
                              if (firstRoom != null && firstRoom.id > 0) {
                                akademik.selectRuanganUjian(firstRoom.id);
                              }
                            }
                          },
                          borderRadius: BorderRadius.circular(9),
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            decoration: BoxDecoration(
                              color: !isAllTasks
                                  ? (isDark
                                        ? primaryColor.withValues(alpha: 0.25)
                                        : AppColors.primaryContainerLight)
                                  : Colors.transparent,
                              borderRadius: BorderRadius.circular(9),
                              border: !isAllTasks
                                  ? Border.all(
                                      color: primaryColor.withValues(
                                        alpha: isDark ? 0.4 : 0.25,
                                      ),
                                      width: 0.8,
                                    )
                                  : null,
                              boxShadow: !isAllTasks
                                  ? [
                                      BoxShadow(
                                        color: Colors.black.withValues(
                                          alpha: isDark ? 0.2 : 0.05,
                                        ),
                                        blurRadius: 4,
                                        offset: const Offset(0, 1),
                                      ),
                                    ]
                                  : null,
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.meeting_room_rounded,
                                  size: 15,
                                  color: !isAllTasks
                                      ? primaryColor
                                      : (isDark
                                            ? const Color(0xFF8D9387)
                                            : const Color(0xFF73796E)),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  'Per Ruangan',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: !isAllTasks
                                        ? FontWeight.bold
                                        : FontWeight.w500,
                                    color: !isAllTasks
                                        ? primaryColor
                                        : (isDark
                                              ? const Color(0xFF8D9387)
                                              : const Color(0xFF73796E)),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 4),

                      // Mode 2: Semua Tugas Saya
                      Expanded(
                        child: InkWell(
                          onTap: () {
                            if (!isAllTasks) {
                              HapticHelper.light();
                              akademik.selectRuanganUjian(0);
                            }
                          },
                          borderRadius: BorderRadius.circular(9),
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            decoration: BoxDecoration(
                              color: isAllTasks
                                  ? (isDark
                                        ? primaryColor.withValues(alpha: 0.25)
                                        : AppColors.primaryContainerLight)
                                  : Colors.transparent,
                              borderRadius: BorderRadius.circular(9),
                              border: isAllTasks
                                  ? Border.all(
                                      color: primaryColor.withValues(
                                        alpha: isDark ? 0.4 : 0.25,
                                      ),
                                      width: 0.8,
                                    )
                                  : null,
                              boxShadow: isAllTasks
                                  ? [
                                      BoxShadow(
                                        color: Colors.black.withValues(
                                          alpha: isDark ? 0.2 : 0.05,
                                        ),
                                        blurRadius: 4,
                                        offset: const Offset(0, 1),
                                      ),
                                    ]
                                  : null,
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.stars_rounded,
                                  size: 15,
                                  color: isAllTasks
                                      ? primaryColor
                                      : (isDark
                                            ? const Color(0xFF8D9387)
                                            : const Color(0xFF73796E)),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  'Semua Tugas Saya',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: isAllTasks
                                        ? FontWeight.bold
                                        : FontWeight.w500,
                                    color: isAllTasks
                                        ? primaryColor
                                        : (isDark
                                              ? const Color(0xFF8D9387)
                                              : const Color(0xFF73796E)),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // Dropdown Ruangan Kelas
                DropdownButtonFormField<int>(
                  key: ValueKey('ruangan_${ujianData?.selectedRuanganId}'),
                  initialValue: ujianData?.selectedRuanganId,
                  decoration: const InputDecoration(
                    labelText: 'Ruangan',
                    prefixIcon: Icon(Icons.meeting_room_rounded, size: 18),
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                  ),
                  items: (ujianData?.daftarRuangan ?? []).map((r) {
                    final isOptionAll = (r.id == 0);
                    return DropdownMenuItem<int>(
                      value: r.id,
                      child: Text(
                        isOptionAll
                            ? r.namaRuangan
                            : '${r.namaRuangan} - (${r.namaLevel})',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: isOptionAll
                              ? FontWeight.bold
                              : FontWeight.normal,
                          color: isOptionAll ? primaryColor : null,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    );
                  }).toList(),
                  onChanged: (val) {
                    if (val != null) {
                      HapticHelper.light();
                      akademik.selectRuanganUjian(val);
                    }
                  },
                ),
                const SizedBox(height: 12),

                // Dropdown Agenda Ujian
                DropdownButtonFormField<int>(
                  key: ValueKey(
                    'ujian_${ujianData?.selectedRuanganId}_${ujianData?.selectedUjianId}',
                  ),
                  initialValue: ujianData?.selectedUjianId,
                  decoration: const InputDecoration(
                    labelText: 'Agenda Ujian',
                    prefixIcon: Icon(Icons.auto_stories_rounded, size: 18),
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                  ),
                  items: (ujianData?.daftarUjian ?? []).map((u) {
                    return DropdownMenuItem<int>(
                      value: u.id,
                      child: Text(
                        '${u.namaUjian} (${u.semester})',
                        style: const TextStyle(fontSize: 13),
                        overflow: TextOverflow.ellipsis,
                      ),
                    );
                  }).toList(),
                  onChanged: (val) {
                    if (val != null) {
                      HapticHelper.light();
                      akademik.selectAgendaUjian(val);
                    }
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // ===================================================================
          // 2. KETERANGAN STATUS RUANGAN & QUICK SWITCH
          // ===================================================================
          if (ujianData != null) ...[
            if (isAllTasks) ...[
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: primaryColor.withValues(alpha: isDark ? 0.15 : 0.08),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: primaryColor.withValues(alpha: 0.3),
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.assignment_ind_rounded,
                      size: 20,
                      color: primaryColor,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Kumpulan Semua Tugas Mengawas Anda',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: primaryColor,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Menampilkan total ${ujianData.totalJadwal} jadwal tugas menguji/mengawas di seluruh ruangan',
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
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ] else if (ujianData.selectedRuanganNama.isNotEmpty) ...[
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: isWali
                      ? primaryColor.withValues(alpha: isDark ? 0.15 : 0.08)
                      : AppColors.skyBlueAccent.withValues(
                          alpha: isDark ? 0.15 : 0.08,
                        ),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: isWali
                        ? primaryColor.withValues(alpha: 0.3)
                        : AppColors.skyBlueAccent.withValues(alpha: 0.3),
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      isWali
                          ? Icons.verified_user_rounded
                          : Icons.meeting_room_outlined,
                      size: 18,
                      color: isWali ? primaryColor : AppColors.skyBlueAccent,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isWali
                                ? 'Ruangan Binaan: ${ujianData.selectedRuanganNama} (${ujianData.namaLevel})'
                                : 'Ruangan: ${ujianData.selectedRuanganNama} (${ujianData.namaLevel})',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: isWali
                                  ? (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                  : (isDark
                                        ? const Color(0xFF38BDF8)
                                        : const Color(0xFF0284C7)),
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            isWali
                                ? 'Wali: ${ujianData.waliRuanganNama} • Total: ${ujianData.totalJadwal} Mata Uji'
                                      '${ujianData.totalJadwalSaya > 0 ? ' • ${ujianData.totalJadwalSaya} Tugas Mengawas Anda' : ''}'
                                : 'Wali: ${ujianData.waliRuanganNama} • Menampilkan ${ujianData.totalJadwal} Tugas Mengawas Anda',
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
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ],
          ],

          // 3. Quick Toggle: Tugas Mengawas Saya (Hanya perlu pada Ruangan Binaan)
          if (!isAllTasks && isWali) ...[
            InkWell(
              onTap: () {
                HapticHelper.light();
                akademik.setOnlyMyJadwalUjian(!onlyMyJadwal);
              },
              borderRadius: BorderRadius.circular(12),
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: onlyMyJadwal
                      ? (isDark
                            ? AppColors.primaryDark.withValues(alpha: 0.2)
                            : AppColors.primaryContainerLight)
                      : (isDark
                            ? AppColors.surfaceContainerDark
                            : AppColors.surfaceLight),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: onlyMyJadwal
                        ? (isDark
                              ? AppColors.primaryDark.withValues(alpha: 0.4)
                              : AppColors.primaryLight.withValues(alpha: 0.3))
                        : (isDark
                              ? AppColors.outlineDark
                              : AppColors.outlineLight),
                    width: 0.8,
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      onlyMyJadwal
                          ? Icons.check_circle_rounded
                          : Icons.person_pin_circle_outlined,
                      size: 18,
                      color: onlyMyJadwal
                          ? primaryColor
                          : (isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E)),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'Tampilkan Hanya Tugas Mengawas Saya (${ujianData?.totalJadwalSaya ?? 0})',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: onlyMyJadwal
                              ? FontWeight.bold
                              : FontWeight.w500,
                          color: onlyMyJadwal
                              ? (isDark
                                    ? Colors.white
                                    : const Color(0xFF1D291E))
                              : (isDark
                                    ? const Color(0xFFC4C8BA)
                                    : const Color(0xFF43493E)),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
          ] else ...[
            const SizedBox(height: 4),
          ],

          // 4. Daftar Jadwal Ujian (Grouped by Tanggal)
          if (akademik.isLoadingJadwalUjian)
            const ShimmerLoadingList(count: 3)
          else if (ujianData == null || ujianData.jadwalPerTanggal.isEmpty)
            GlassCard(
              padding: const EdgeInsets.all(32),
              child: Column(
                children: [
                  Icon(
                    Icons.assignment_late_outlined,
                    size: 44,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    isAllTasks
                        ? 'Tidak ada jadwal tugas mengawas untuk Anda pada agenda ini.'
                        : (onlyMyJadwal
                              ? 'Tidak ada jadwal mengawas untuk Anda pada ruangan ini.'
                              : 'Belum ada jadwal ujian untuk ruangan dan agenda ini.'),
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 13),
                  ),
                ],
              ),
            )
          else
            ...ujianData.jadwalPerTanggal.map((tglItem) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header Tanggal
                    Container(
                      margin: const EdgeInsets.only(
                        bottom: 10,
                        left: 2,
                        right: 2,
                      ),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                      decoration: BoxDecoration(
                        color: isDark
                            ? AppColors.surfaceContainerDark
                            : AppColors.primaryContainerLight.withValues(
                                alpha: 0.35,
                              ),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: isDark
                              ? AppColors.outlineDark
                              : primaryColor.withValues(alpha: 0.15),
                          width: 0.8,
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Icon(
                                Icons.calendar_today_rounded,
                                size: 14,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                tglItem.hariTanggal,
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? Colors.white
                                      : const Color(0xFF2C3E2D),
                                ),
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? AppColors.primaryDark.withValues(
                                      alpha: 0.15,
                                    )
                                  : AppColors.primaryContainerLight,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              '${tglItem.totalSesi} Ujian',
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
                      ),
                    ),

                    // Daftar Kartu Jadwal Ujian pada tanggal tsb
                    ...tglItem.sesi.map((s) {
                      final badgeColor = _getTipeUjianColor(
                        s.tipeUjian,
                        isDark,
                      );

                      return GlassCard(
                        margin: const EdgeInsets.only(bottom: 10),
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Baris 1: Jam & Badge Tingkat / Tipe Ujian
                            Row(
                              children: [
                                // Jam Pelaksanaan
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 3,
                                  ),
                                  decoration: BoxDecoration(
                                    color: isDark
                                        ? AppColors.surfaceContainerDark
                                        : AppColors.surfaceLight,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(
                                      color: isDark
                                          ? AppColors.outlineDark
                                          : AppColors.outlineLight,
                                      width: 0.8,
                                    ),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(
                                        Icons.access_time_rounded,
                                        size: 12,
                                        color: isDark
                                            ? const Color(0xFF8D9387)
                                            : const Color(0xFF555D50),
                                      ),
                                      const SizedBox(width: 5),
                                      Text(
                                        s.jam,
                                        style: TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.bold,
                                          color: isDark
                                              ? const Color(0xFF8D9387)
                                              : const Color(0xFF555D50),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                const Spacer(),

                                // Badge Tipe Ujian
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 3,
                                  ),
                                  decoration: BoxDecoration(
                                    color: badgeColor.withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(
                                      color: badgeColor.withValues(alpha: 0.3),
                                      width: 0.8,
                                    ),
                                  ),
                                  child: Text(
                                    s.tipeUjian,
                                    style: TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold,
                                      color: badgeColor,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 6),

                                // Badge Tingkat/Level
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 3,
                                  ),
                                  decoration: BoxDecoration(
                                    color: isDark
                                        ? AppColors.primaryDark.withValues(
                                            alpha: 0.15,
                                          )
                                        : AppColors.primaryContainerLight,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(
                                      color: isDark
                                          ? AppColors.primaryDark.withValues(
                                              alpha: 0.25,
                                            )
                                          : AppColors.primaryLight.withValues(
                                              alpha: 0.15,
                                            ),
                                      width: 0.8,
                                    ),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      if (isAllTasks) ...[
                                        Icon(
                                          Icons.meeting_room_outlined,
                                          size: 11,
                                          color: isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight,
                                        ),
                                        const SizedBox(width: 3),
                                      ],
                                      Text(
                                        isAllTasks
                                            ? 'Kelas ${s.namaLevel}'
                                            : s.namaLevel,
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                          color: isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 10),

                            // Nama Mata Pelajaran
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    s.namaMapel,
                                    style: const TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                if (s.isCustomMapel)
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 6,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Colors.purple.withValues(
                                        alpha: 0.15,
                                      ),
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: const Text(
                                      'Muatan Khusus',
                                      style: TextStyle(
                                        fontSize: 9,
                                        fontWeight: FontWeight.bold,
                                        color: Colors.purple,
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                            const SizedBox(height: 3),

                            // Nama Agenda Ujian & Semester
                            Text(
                              '${s.namaUjian} (${s.semester})',
                              style: TextStyle(
                                fontSize: 11,
                                color: isDark
                                    ? const Color(0xFF8D9387)
                                    : const Color(0xFF73796E),
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 12),

                            // Info Pengawas Ujian
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 8,
                              ),
                              decoration: BoxDecoration(
                                color: s.isMySchedule
                                    ? (isDark
                                          ? AppColors.primaryDark.withValues(
                                              alpha: 0.12,
                                            )
                                          : AppColors.primaryContainerLight
                                                .withValues(alpha: 0.5))
                                    : (isDark
                                          ? AppColors.surfaceContainerDark
                                          : AppColors.surfaceLight),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: s.isMySchedule
                                      ? (isDark
                                            ? AppColors.primaryDark.withValues(
                                                alpha: 0.35,
                                              )
                                            : AppColors.primaryLight.withValues(
                                                alpha: 0.25,
                                              ))
                                      : (isDark
                                            ? AppColors.outlineDark
                                            : AppColors.outlineLight),
                                  width: 0.8,
                                ),
                              ),
                              child: Row(
                                children: [
                                  AppAvatar(
                                    name: s.namaPengawas,
                                    imageUrl: s.pengawasFoto,
                                    radius: 14,
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Flexible(
                                              child: Text(
                                                s.namaPengawas,
                                                style: TextStyle(
                                                  fontSize: 12,
                                                  fontWeight: s.isMySchedule
                                                      ? FontWeight.bold
                                                      : FontWeight.w600,
                                                ),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                            if (s.isMySchedule) ...[
                                              const SizedBox(width: 6),
                                              Container(
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                      horizontal: 6,
                                                      vertical: 1.5,
                                                    ),
                                                decoration: BoxDecoration(
                                                  color: isDark
                                                      ? AppColors.primaryDark
                                                      : AppColors.primaryLight,
                                                  borderRadius:
                                                      BorderRadius.circular(4),
                                                ),
                                                child: Text(
                                                  'Tugas Anda',
                                                  style: TextStyle(
                                                    fontSize: 9,
                                                    fontWeight: FontWeight.bold,
                                                    color: onPrimaryColor,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ],
                                        ),
                                        const SizedBox(height: 1),
                                        Text(
                                          s.kodePengawas != null &&
                                                  s.kodePengawas != '-'
                                              ? 'Pengawas • Kode: ${s.kodePengawas}'
                                              : (s.isCustomMapel
                                                    ? 'Pengawas (Wali Ruangan)'
                                                    : 'Pengawas (Guru Mapel)'),
                                          style: TextStyle(
                                            fontSize: 10,
                                            color: isDark
                                                ? const Color(0xFF8D9387)
                                                : const Color(0xFF73796E),
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
                      );
                    }),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }

  // =========================================================================
  // HELPER COLORS
  // =========================================================================
  Color _getTipeUjianColor(String tipe, bool isDark) {
    switch (tipe.toUpperCase()) {
      case 'IMDA 1':
        return isDark
            ? const Color(0xFF38BDF8)
            : const Color(0xFF0284C7); // Sky
      case 'IMDA 2':
        return isDark
            ? const Color(0xFFFBBF24)
            : const Color(0xFFD97706); // Amber
      case 'IMNI':
        return isDark
            ? const Color(0xFFA78BFA)
            : const Color(0xFF7C3AED); // Purple
      default:
        return isDark ? AppColors.primaryDark : AppColors.primaryLight;
    }
  }
}
