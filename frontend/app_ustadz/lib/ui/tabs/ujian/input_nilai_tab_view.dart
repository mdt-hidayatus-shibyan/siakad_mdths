import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/nilai_provider.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';
import '../penilaian/form_nilai_screen.dart';
import '../penilaian/leger_screen.dart';

class InputNilaiTabView extends StatefulWidget {
  const InputNilaiTabView({super.key});

  @override
  State<InputNilaiTabView> createState() => _InputNilaiTabViewState();
}

class _InputNilaiTabViewState extends State<InputNilaiTabView> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NilaiProvider>().fetchMapelJadwal();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<NilaiProvider>();

    if (provider.isLoading && provider.mapelJadwalData == null) {
      return const Padding(
        padding: EdgeInsets.all(16),
        child: ShimmerLoadingList(count: 5, height: 80),
      );
    }

    if (provider.errorMessage != null && provider.mapelJadwalData == null) {
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
                provider.errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 14),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: () => provider.fetchMapelJadwal(),
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => provider.fetchMapelJadwal(
        ruanganId: provider.selectedRuanganId,
        ujianId: provider.selectedUjianId,
      ),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 140),
        children: [
          // ===================================================================
          // 1. FILTER RUANGAN KELAS (UTAMA) & AGENDA UJIAN
          // ===================================================================
          GlassCard(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.filter_list_rounded,
                      size: 18,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                    const SizedBox(width: 8),
                    const Text(
                      'Pilih Ruangan Kelas & Agenda Ujian',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // 1.1 Dropdown Ruangan Kelas
                DropdownButtonFormField<int>(
                  key: ValueKey('ruangan_${provider.selectedRuanganId}'),
                  initialValue: provider.selectedRuanganId,
                  decoration: const InputDecoration(
                    labelText: 'Ruangan Kelas',
                    prefixIcon: Icon(Icons.meeting_room_rounded, size: 18),
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                  ),
                  items: provider.daftarRuangan.map((r) {
                    return DropdownMenuItem<int>(
                      value: r.id,
                      child: Text(
                        '${r.namaRuangan} (${r.namaLevel})',
                        style: const TextStyle(fontSize: 13),
                        overflow: TextOverflow.ellipsis,
                      ),
                    );
                  }).toList(),
                  onChanged: (val) {
                    if (val != null) {
                      HapticHelper.light();
                      provider.selectRuangan(val);
                    }
                  },
                ),
                const SizedBox(height: 12),

                // 1.2 Dropdown Agenda Ujian (Otomatis Filter sesuai Level Kelas)
                DropdownButtonFormField<int>(
                  key: ValueKey(
                    'ujian_${provider.selectedRuanganId}_${provider.selectedUjianId}',
                  ),
                  initialValue: provider.selectedUjianId,
                  decoration: const InputDecoration(
                    labelText: 'Agenda Ujian',
                    prefixIcon: Icon(Icons.auto_stories_rounded, size: 18),
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                  ),
                  items: provider.daftarUjian.map((u) {
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
                      provider.selectUjian(val);
                    }
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          // ===================================================================
          // 2. KONDISI EMPTY STATE
          // ===================================================================
          if (provider.daftarUjian.isEmpty)
            _buildBelumAdaUjianEmptyState(context, isDark, provider)
          else if (provider.jadwalList.isEmpty)
            _buildBelumAdaJadwalEmptyState(context, isDark, provider)
          else ...[
            // ===================================================================
            // 3. MENU KHUSUS WALI RUANGAN: LEGER NILAI & RANKING KELAS
            // ===================================================================
            if (provider.isWaliRuangan &&
                provider.selectedRuanganId != null &&
                provider.selectedUjianId != null) ...[
              GlassCard(
                padding: const EdgeInsets.all(14),
                customBorderColor: AppColors.amberAccent.withValues(alpha: 0.5),
                onTap: () {
                  HapticHelper.light();
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => LegerRuanganScreen(
                        ruanganId: provider.selectedRuanganId!,
                        ujianId: provider.selectedUjianId!,
                        ruanganName: provider.selectedRuanganNama,
                        ujianName: provider.currentUjian?.namaUjian,
                      ),
                    ),
                  );
                },
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppColors.amberAccent.withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.leaderboard_rounded,
                        size: 22,
                        color: AppColors.amberAccent,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Row(
                            children: [
                              Text(
                                'Leger Nilai & Ranking 1 Kelas',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              SizedBox(width: 6),
                              Icon(
                                Icons.workspace_premium_rounded,
                                size: 14,
                                color: AppColors.amberAccent,
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Rekap nilai seluruh mapel & kalkulasi peringkat ${provider.selectedRuanganNama}',
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
                    const Icon(
                      Icons.chevron_right_rounded,
                      size: 20,
                      color: Colors.grey,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
            ],

            // ===================================================================
            // 4. DAFTAR MATA PELAJARAN SESUAI JADWAL UJIAN
            // ===================================================================
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Mata Pelajaran Ujian',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                Text(
                  '${provider.jadwalList.length} Mapel Terjadwal',
                  style: TextStyle(
                    fontSize: 11,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),

            ...provider.jadwalList.map((j) {
              Color statusColor = Colors.grey;
              if (j.statusInput == 'Selesai') {
                statusColor = isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight;
              } else if (j.statusInput.contains('Draf')) {
                statusColor = AppColors.amberAccent;
              }

              return GlassCard(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.all(14),
                onTap: () {
                  HapticHelper.light();
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => FormNilaiScreen(
                        ruanganId: provider.selectedRuanganId!,
                        ujianId: provider.selectedUjianId!,
                        jadwalUjianId: j.id,
                        mapelName: j.namaMapel,
                        ruanganName: provider.selectedRuanganNama,
                        ujianName: provider.currentUjian?.namaUjian,
                      ),
                    ),
                  ).then((_) {
                    // Refresh data after returning from grading screen
                    provider.fetchMapelJadwal(
                      ruanganId: provider.selectedRuanganId,
                      ujianId: provider.selectedUjianId,
                    );
                  });
                },
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color:
                            (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                .withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(14),
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
                            j.namaMapel,
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Row(
                            children: [
                              Icon(
                                Icons.access_time_rounded,
                                size: 12,
                                color: isDark
                                    ? const Color(0xFF8D9387)
                                    : const Color(0xFF73796E),
                              ),
                              const SizedBox(width: 4),
                              Text(
                                '${j.tanggalUjian} • ${j.waktuMulai} - ${j.waktuSelesai}',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 3),
                          Row(
                            children: [
                              Icon(
                                Icons.badge_outlined,
                                size: 12,
                                color: isDark
                                    ? const Color(0xFF8D9387)
                                    : const Color(0xFF73796E),
                              ),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  'Pengawas: ${j.pengawasNama}',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: isDark
                                        ? const Color(0xFF8D9387)
                                        : const Color(0xFF73796E),
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),

                    // Progress Status Badge & Chevron
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: statusColor.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: statusColor.withValues(alpha: 0.4),
                            ),
                          ),
                          child: Text(
                            '${j.statusInput} (${j.jumlahDinilai}/${j.totalMurid})',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: statusColor,
                            ),
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Icon(
                          Icons.arrow_forward_ios_rounded,
                          size: 14,
                          color: Colors.grey,
                        ),
                      ],
                    ),
                  ],
                ),
              );
            }),
          ],
        ],
      ),
    );
  }

  Widget _buildBelumAdaJadwalEmptyState(
    BuildContext context,
    bool isDark,
    NilaiProvider provider,
  ) {
    final ujianName = provider.currentUjian?.namaUjian ?? 'Ujian';
    final tipeUjian = provider.currentUjian?.tipeUjian ?? 'IMDA';
    final ruanganName = provider.currentRuangan?.namaRuangan ?? 'Ruangan ini';
    final levelName = provider.currentRuangan?.namaLevel ?? '';

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 8),
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.amberAccent.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.event_busy_rounded,
                size: 48,
                color: AppColors.amberAccent,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Jadwal $tipeUjian Belum Dibuat',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Jadwal ujian $ujianName ($tipeUjian) untuk $ruanganName ($levelName) belum dibuat oleh Administrator.',
              style: TextStyle(
                fontSize: 13,
                height: 1.4,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: isDark
                    ? const Color(0xFF101710)
                    : const Color(0xFFF3F4F1),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: isDark
                      ? AppColors.outlineDark
                      : AppColors.outlineLight,
                ),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.info_outline_rounded,
                    size: 16,
                    color: AppColors.skyBlueAccent,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Silakan hubungi staf/admin MDT untuk membuat jadwal mata pelajaran ujian di web admin.',
                      style: TextStyle(
                        fontSize: 11,
                        color: isDark
                            ? const Color(0xFF8D9387)
                            : const Color(0xFF555555),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBelumAdaUjianEmptyState(
    BuildContext context,
    bool isDark,
    NilaiProvider provider,
  ) {
    final ruanganName = provider.currentRuangan?.namaRuangan ?? 'Ruangan ini';

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 8),
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                    .withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.pending_actions_rounded,
                size: 48,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Agenda Ujian Belum Dibuat',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Agenda ujian untuk $ruanganName belum dibuat oleh Administrator.',
              style: TextStyle(
                fontSize: 13,
                height: 1.4,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
