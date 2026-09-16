import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/akademik_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class JadwalPelajaranScreen extends StatefulWidget {
  const JadwalPelajaranScreen({super.key});

  @override
  State<JadwalPelajaranScreen> createState() => _JadwalPelajaranScreenState();
}

class _JadwalPelajaranScreenState extends State<JadwalPelajaranScreen> {
  final List<String> _daftarHari = [
    'Ahad',
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Sabtu',
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

    // Cari jadwal untuk hari yang dipilih
    final hariItem = jadwalData?.jadwalPerHari.firstWhere(
      (h) => h.hari == hariAktif,
      orElse: () => jadwalData.jadwalPerHari.first,
    );

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Jadwal Mengajar Saya'),
      body: RefreshIndicator(
        onRefresh: () => akademik.fetchJadwalPelajaran(),
        color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
          children: [
            // 1. Ringkasan Total Beban Mengajar Mingguan
            GlassCard(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 24,
                    backgroundColor: isDark
                        ? const Color(0xFF0F2313)
                        : AppColors.primaryContainerLight,
                    child: Icon(
                      Icons.schedule_rounded,
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
                        Text(
                          jadwalData?.ustadzNama ?? 'Pengajar',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Total: ${jadwalData?.totalJadwalMingguan ?? 0} Sesi Mengajar / Ahad',
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

            // 2. Day Selector Horizontal Chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _daftarHari.map((hari) {
                  final isSelected = hari == hariAktif;
                  final totalSesiHari =
                      jadwalData?.jadwalPerHari
                          .firstWhere(
                            (h) => h.hari == hari,
                            orElse: () => jadwalData.jadwalPerHari.first,
                          )
                          .totalSesi ??
                      0;

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

            // 3. Sesi List on Selected Day
            if (akademik.isLoadingJadwal)
              const ShimmerLoadingList(count: 3)
            else if ((hariItem?.sesi.isEmpty ?? true))
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
                      'Tidak ada jadwal mengajar pada hari $hariAktif.',
                      style: const TextStyle(fontSize: 13),
                    ),
                  ],
                ),
              )
            else
              ...hariItem!.sesi.map((s) {
                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Timing & Ruangan Tag
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
                                  ? const Color(0xFF0F2313)
                                  : AppColors.primaryContainerLight,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              s.jam,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
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
                                  ? const Color(0xFF241538)
                                  : const Color(0xFFF3E8FF),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              'Tingkat: ${s.level}',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isDark
                                    ? AppColors.violetAccent
                                    : const Color(0xFF6D28D9),
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
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),

                      Text(
                        'Ruangan: ${s.ruangan}',
                        style: TextStyle(
                          fontSize: 12,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF73796E),
                        ),
                      ),
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
