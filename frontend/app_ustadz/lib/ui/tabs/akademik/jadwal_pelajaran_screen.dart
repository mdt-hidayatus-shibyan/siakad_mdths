import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/akademik_model.dart';
import '../../../providers/akademik_provider.dart';
import '../../widgets/app_avatar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';

class JadwalPelajaranScreen extends StatefulWidget {
  const JadwalPelajaranScreen({super.key});

  @override
  State<JadwalPelajaranScreen> createState() => _JadwalPelajaranScreenState();
}

class _JadwalPelajaranScreenState extends State<JadwalPelajaranScreen> {
  final List<String> _daftarHari = [
    'Sabtu',
    'Ahad',
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AkademikProvider>().fetchJadwalPelajaran();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final akademik = context.watch<AkademikProvider>();
    final jadwalData = akademik.jadwalData;
    final hariAktif = akademik.selectedHari;
    final isWali = jadwalData?.isWaliRuangan ?? false;
    final isWaliMode = isWali && akademik.jadwalModeIndex == 1;

    // Ambil list jadwal aktif berdasarkan mode (Jadwal Mengajar Saya vs Jadwal Kelas Binaan)
    final activeList = akademik.activeJadwalPerHari;
    final hariItem = activeList.firstWhere(
      (h) => h.hari == hariAktif,
      orElse: () => HariJadwalItem(hari: hariAktif, totalSesi: 0, sesi: []),
    );

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Jadwal Pelajaran'),
      body: RefreshIndicator(
        onRefresh: () => akademik.fetchJadwalPelajaran(),
        color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
          children: [
            // 1. Tab Switcher jika Ustadz adalah Wali Ruangan
            if (isWali)
              SegmentedTabBar(
                margin: const EdgeInsets.only(bottom: 16),
                selectedIndex: akademik.jadwalModeIndex,
                onTabChanged: (index) {
                  akademik.setJadwalModeIndex(index);
                },
                items: [
                  const SegmentedTabItem(
                    activeIcon: Icons.person_rounded,
                    inactiveIcon: Icons.person_outline_rounded,
                    label: 'Jadwal Saya',
                  ),
                  SegmentedTabItem(
                    activeIcon: Icons.meeting_room_rounded,
                    inactiveIcon: Icons.meeting_room_outlined,
                    label: 'Ruangan ${jadwalData?.ruanganWaliNama ?? 'Binaan'}',
                  ),
                ],
              ),

            // 2. Ringkasan Beban Mengajar / Jadwal Kelas
            GlassCard(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 24,
                    backgroundColor: isDark
                        ? AppColors.primaryDark.withValues(alpha: 0.15)
                        : AppColors.primaryContainerLight,
                    child: Icon(
                      isWaliMode
                          ? Icons.meeting_room_rounded
                          : Icons.schedule_rounded,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Flexible(
                              child: Text(
                                isWaliMode
                                    ? 'Ruangan ${jadwalData?.ruanganWaliNama ?? '-'}'
                                    : (jadwalData?.ustadzNama ?? 'Pengajar'),
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 7,
                                vertical: 2.5,
                              ),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? AppColors.primaryDark.withValues(
                                        alpha: 0.15,
                                      )
                                    : AppColors.primaryContainerLight,
                                borderRadius: BorderRadius.circular(6),
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
                              child: Text(
                                isWaliMode
                                    ? (jadwalData?.levelWaliNama ?? 'Wali')
                                    : 'Pengampu',
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
                        const SizedBox(height: 3),
                        Text(
                          isWaliMode
                              ? 'Total: ${jadwalData?.totalJadwalRuanganMingguan ?? 0} Sesi Pelajaran / Minggu'
                              : 'Total: ${jadwalData?.totalJadwalMingguan ?? 0} Sesi Mengajar / Minggu',
                          style: TextStyle(
                            fontSize: 12,
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
            const SizedBox(height: 16),

            // 3. Day Selector Horizontal Chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _daftarHari.map((hari) {
                  final isSelected = hari == hariAktif;
                  final totalSesiHari = activeList
                      .firstWhere(
                        (h) => h.hari == hari,
                        orElse: () =>
                            HariJadwalItem(hari: hari, totalSesi: 0, sesi: []),
                      )
                      .totalSesi;

                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: InkWell(
                      onTap: () {
                        HapticHelper.light();
                        akademik.setSelectedHari(hari);
                      },
                      borderRadius: BorderRadius.circular(20),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 8,
                        ),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                              : (isDark
                                    ? const Color(0xFF101710)
                                    : const Color(0xFFF1F5F0)),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              hari,
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: isSelected
                                    ? FontWeight.bold
                                    : FontWeight.normal,
                                color: isSelected
                                    ? (isDark ? Colors.black : Colors.white)
                                    : (isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF555D50)),
                              ),
                            ),
                            if (totalSesiHari > 0) ...[
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 6,
                                  vertical: 1,
                                ),
                                decoration: BoxDecoration(
                                  color: isSelected
                                      ? (isDark
                                            ? Colors.black26
                                            : Colors.white24)
                                      : (isDark
                                            ? const Color(0xFF1D281D)
                                            : const Color(0xFFE2E8F0)),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  '$totalSesiHari',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: isSelected
                                        ? (isDark ? Colors.black : Colors.white)
                                        : (isDark
                                              ? Colors.white70
                                              : Colors.black87),
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 16),

            // 4. Sesi List on Selected Day
            if (akademik.isLoadingJadwal)
              const ShimmerLoadingList(count: 3)
            else if (hariItem.sesi.isEmpty)
              GlassCard(
                padding: const EdgeInsets.all(32),
                child: Column(
                  children: [
                    Icon(
                      Icons.event_busy_rounded,
                      size: 40,
                      color: isDark
                          ? const Color(0xFF8D9387)
                          : const Color(0xFF73796E),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      isWaliMode
                          ? 'Tidak ada jadwal Ruangan ${jadwalData?.ruanganWaliNama ?? ''} pada hari $hariAktif.'
                          : 'Tidak ada jadwal mengajar pada hari $hariAktif.',
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontSize: 13),
                    ),
                  ],
                ),
              )
            else
              ...hariItem.sesi.map((s) {
                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Timing & Info Tag
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? const Color(0xFF101710)
                                  : const Color(0xFFF1F5F0),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: isDark
                                    ? const Color(0xFF263326)
                                    : const Color(0xFFE2E8F0),
                                width: 0.8,
                              ),
                            ),
                            child: Text(
                              s.jam,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isDark
                                    ? const Color(0xFF8D9387)
                                    : const Color(0xFF555D50),
                              ),
                            ),
                          ),
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
                            child: Text(
                              'Ruangan: ${s.ruangan}',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),

                      // Nama Pelajaran
                      Text(
                        s.mapel,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),

                      // Gedung & Kamar
                      Row(
                        children: [
                          Icon(
                            Icons.apartment_rounded,
                            size: 14,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E),
                          ),
                          const SizedBox(width: 5),
                          Expanded(
                            child: Text(
                              'Gedung : ${s.lokasiGedungKamar}',
                              style: TextStyle(
                                fontSize: 12,
                                color: isDark
                                    ? const Color(0xFF8D9387)
                                    : const Color(0xFF73796E),
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),

                      // Info Ustadz Pengampu (Khusus mode Wali Ruangan / Jadwal Kelas)
                      if (isWaliMode) ...[
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 8,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? const Color(0xFF131B13)
                                : const Color(0xFFF6F8F5),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: isDark
                                  ? const Color(0xFF263326)
                                  : const Color(0xFFE2E8F0),
                              width: 0.8,
                            ),
                          ),
                          child: Row(
                            children: [
                              AppAvatar(
                                name: s.ustadz ?? 'Ustadz',
                                imageUrl: s.ustadzFoto,
                                radius: 14,
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      s.ustadz ?? 'Pengajar',
                                      style: const TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                    if (s.kodeUstadz != null &&
                                        s.kodeUstadz != '-') ...[
                                      const SizedBox(height: 1),
                                      Text(
                                        'Kode: ${s.kodeUstadz}',
                                        style: TextStyle(
                                          fontSize: 10,
                                          color: isDark
                                              ? const Color(0xFF8D9387)
                                              : const Color(0xFF73796E),
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
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
