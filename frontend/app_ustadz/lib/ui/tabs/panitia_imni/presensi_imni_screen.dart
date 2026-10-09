import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/panitia_imni_model.dart';
import '../../../providers/panitia_imni_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';
import '../../widgets/status_presensi_chip.dart';

class PresensiImniScreen extends StatefulWidget {
  const PresensiImniScreen({super.key});

  @override
  State<PresensiImniScreen> createState() => _PresensiImniScreenState();
}

class _PresensiImniScreenState extends State<PresensiImniScreen> {
  final TextEditingController _beritaAcaraController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final p = context.read<PanitiaImniProvider>();
      p.fetchPresensiData().then((_) {
        if (mounted && p.presensiPengawas?.catatanBeritaAcara != null) {
          _beritaAcaraController.text = p.presensiPengawas!.catatanBeritaAcara!;
        }
      });
    });
  }

  @override
  void dispose() {
    _beritaAcaraController.dispose();
    super.dispose();
  }

  void _showCatatanDialog(
    int muridId,
    String namaMurid,
    String? existingCatatan,
  ) {
    HapticHelper.light();
    final noteCtrl = TextEditingController(text: existingCatatan);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(
          'Catatan Presensi: $namaMurid',
          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
        ),
        content: TextField(
          controller: noteCtrl,
          decoration: const InputDecoration(
            hintText: 'Contoh: Izin terlambat 15 menit karena hujan...',
            border: OutlineInputBorder(),
          ),
          maxLines: 3,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              context.read<PanitiaImniProvider>().setPresensiCatatan(
                muridId,
                noteCtrl.text.trim().isEmpty ? null : noteCtrl.text.trim(),
              );
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<PanitiaImniProvider>();
    final isTpq = (provider.presensiKategori == 'tpq');
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final onPrimaryColor = isDark
        ? AppColors.onPrimaryDark
        : AppColors.onPrimaryLight;

    return Scaffold(
      appBar: const CustomAppBar(
        titleText: 'Presensi IMNI',
        subtitleText: 'Presensi Peserta IMNI & Pengawas Ujian',
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await provider.fetchPresensiData();
          if (mounted &&
              provider.presensiPengawas?.catatanBeritaAcara != null) {
            _beritaAcaraController.text =
                provider.presensiPengawas!.catatanBeritaAcara!;
          }
        },
        child: ListView(
          padding: EdgeInsets.fromLTRB(
            16,
            12,
            16,
            120 + MediaQuery.of(context).padding.bottom,
          ),
          children: [
            // ===================================================================
            // 1. TAB SWITCHER (3 TPQ vs 6 IBT & 3 TSA)
            // ===================================================================
            SegmentedTabBar(
              margin: const EdgeInsets.only(bottom: 14),
              selectedIndex: isTpq ? 0 : 1,
              onTabChanged: (idx) {
                provider.setPresensiKategori(idx == 0 ? 'tpq' : 'imni');
                if (provider.presensiPengawas?.catatanBeritaAcara != null) {
                  _beritaAcaraController.text =
                      provider.presensiPengawas!.catatanBeritaAcara!;
                } else {
                  _beritaAcaraController.clear();
                }
              },
              items: [
                SegmentedTabItem(
                  activeIcon: Icons.menu_book_rounded,
                  inactiveIcon: Icons.menu_book_outlined,
                  label: '3 TPQ',
                  activeColor: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                ),
                SegmentedTabItem(
                  activeIcon: Icons.school_rounded,
                  inactiveIcon: Icons.school_outlined,
                  label: '6 IBT & 3 TSA',
                  activeColor: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                ),
              ],
            ),

            // ===================================================================
            // VIEW 1: TAB 3 TPQ (SEPERTI PRESENSI UJIAN)
            // ===================================================================
            if (isTpq) ...[
              // 1.1 FILTER RUANGAN & MATA PELAJARAN TPQ
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
                          color: AppColors.primaryLight,
                        ),
                        const SizedBox(width: 8),
                        const Expanded(
                          child: Text(
                            'Pilih Ruangan & Mata Pelajaran TPQ',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Dropdown Ruangan Kelas 3 TPQ
                    DropdownButtonFormField<int>(
                      key: ValueKey('ruangan_${provider.presensiRuanganId}'),
                      initialValue:
                          provider.presensiDaftarRuangan.any(
                            (r) => r.id == provider.presensiRuanganId,
                          )
                          ? provider.presensiRuanganId
                          : (provider.presensiDaftarRuangan.isNotEmpty
                                ? provider.presensiDaftarRuangan.first.id
                                : null),
                      decoration: const InputDecoration(
                        labelText: 'Ruangan Kelas 3 TPQ',
                        prefixIcon: Icon(Icons.meeting_room_rounded, size: 18),
                        contentPadding: EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 10,
                        ),
                      ),
                      items: provider.presensiDaftarRuangan.map((r) {
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
                          provider.selectPresensiRuangan(val);
                          if (provider.presensiPengawas?.catatanBeritaAcara !=
                              null) {
                            _beritaAcaraController.text =
                                provider.presensiPengawas!.catatanBeritaAcara!;
                          }
                        }
                      },
                    ),

                    // Dropdown Mata Pelajaran TPQ
                    if (provider.presensiJadwalList.isNotEmpty) ...[
                      const SizedBox(height: 12),
                      DropdownButtonFormField<int>(
                        key: ValueKey(
                          'jadwal_${provider.presensiRuanganId}_${provider.presensiJadwalId}',
                        ),
                        initialValue:
                            provider.presensiJadwalList.any(
                              (j) => j.id == provider.presensiJadwalId,
                            )
                            ? provider.presensiJadwalId
                            : (provider.presensiJadwalList.isNotEmpty
                                  ? provider.presensiJadwalList.first.id
                                  : null),
                        isExpanded: true,
                        decoration: const InputDecoration(
                          labelText: 'Mata Pelajaran Ujian TPQ',
                          prefixIcon: Icon(Icons.quiz_rounded, size: 18),
                          contentPadding: EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 10,
                          ),
                        ),
                        items: provider.presensiJadwalList.map((j) {
                          final dateStr =
                              j.hariTanggalSingkat ?? j.hariTanggal ?? '-';
                          final timeStr =
                              '${j.waktuMulai ?? "-"} - ${j.waktuSelesai ?? "-"}';
                          return DropdownMenuItem<int>(
                            value: j.id,
                            child: Text(
                              '${j.namaMapel} ($dateStr • $timeStr)',
                              style: const TextStyle(fontSize: 13),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) {
                            HapticHelper.light();
                            provider.selectPresensiJadwal(val);
                            if (provider.presensiPengawas?.catatanBeritaAcara !=
                                null) {
                              _beritaAcaraController.text = provider
                                  .presensiPengawas!
                                  .catatanBeritaAcara!;
                            }
                          }
                        },
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 14),

              if (provider.isLoadingPresensi && provider.presensiData == null)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 20),
                  child: ShimmerLoadingList(count: 5, height: 90),
                )
              else if (provider.presensiDaftarRuangan.isEmpty)
                _buildBelumAdaRuanganEmptyState(
                  context,
                  isDark,
                  primaryColor,
                  onPrimaryColor,
                  provider,
                  isTpq: true,
                )
              else if (provider.presensiJadwalList.isEmpty)
                _buildBelumAdaJadwalEmptyState(
                  context,
                  isDark,
                  provider,
                  isTpq: true,
                )
              else ...[
                // 1.2 CARD KETERANGAN MATA PELAJARAN YANG SEDANG DIPRESENSI
                if (provider.currentPresensiJadwal != null) ...[
                  GlassCard(
                    padding: const EdgeInsets.all(14),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color:
                                (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                    .withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(
                            Icons.quiz_rounded,
                            size: 22,
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
                                provider.currentPresensiJadwal!.namaMapel,
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 3),
                              Row(
                                children: [
                                  const Icon(
                                    Icons.event_note_rounded,
                                    size: 12,
                                    color: Colors.grey,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    '${provider.currentPresensiJadwal!.hariTanggalSingkat ?? provider.currentPresensiJadwal!.hariTanggal ?? "-"} • ${provider.currentPresensiJadwal!.waktuMulai ?? "-"} - ${provider.currentPresensiJadwal!.waktuSelesai ?? "-"}',
                                    style: TextStyle(
                                      fontSize: 11,
                                      color: isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 2),
                              Row(
                                children: [
                                  const Icon(
                                    Icons.badge_outlined,
                                    size: 12,
                                    color: Colors.grey,
                                  ),
                                  const SizedBox(width: 4),
                                  Expanded(
                                    child: Text(
                                      'Pengawas (Guru Mapel): ${provider.currentPresensiJadwal!.pengawasNama ?? provider.presensiPengawas?.ustadzNama ?? "Belum Ditentukan"}',
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
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                ],

                // 1.3 CARD PENGAWAS RUANGAN & BERITA ACARA (INLINE)
                if (provider.presensiPengawas != null) ...[
                  GlassCard(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(7),
                              decoration: BoxDecoration(
                                color: AppColors.violetAccent.withValues(
                                  alpha: 0.15,
                                ),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(
                                Icons.person_pin_rounded,
                                size: 16,
                                color: AppColors.violetAccent,
                              ),
                            ),
                            const SizedBox(width: 8),
                            const Expanded(
                              child: Text(
                                'Pengawas Ruangan & Berita Acara',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        // Info Pengawas Utama Terjadwal
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                          decoration: BoxDecoration(
                            color: (isDark ? Colors.white : Colors.black)
                                .withValues(alpha: 0.04),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.badge_outlined, size: 15),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  'Pengawas Terjadwal: ${provider.presensiPengawas!.ustadzNama ?? "Belum Ditentukan"}',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 10),

                        // Status Kehadiran Pengawas (H, I, S, B)
                        Row(
                          children: [
                            Expanded(
                              child: StatusPresensiChip(
                                status: 'H',
                                label: 'Hadir',
                                isSelected:
                                    provider.presensiPengawas!.status ==
                                    'Hadir',
                                onTap: () {
                                  HapticHelper.light();
                                  provider.updatePengawasStatus('Hadir');
                                },
                              ),
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: StatusPresensiChip(
                                status: 'I',
                                label: 'Izin',
                                isSelected:
                                    provider.presensiPengawas!.status == 'Izin',
                                onTap: () {
                                  HapticHelper.light();
                                  provider.updatePengawasStatus('Izin');
                                },
                              ),
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: StatusPresensiChip(
                                status: 'S',
                                label: 'Sakit',
                                isSelected:
                                    provider.presensiPengawas!.status ==
                                    'Sakit',
                                onTap: () {
                                  HapticHelper.light();
                                  provider.updatePengawasStatus('Sakit');
                                },
                              ),
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: StatusPresensiChip(
                                status: 'B',
                                label: 'Badal',
                                isSelected:
                                    provider.presensiPengawas!.status ==
                                        'Badal' ||
                                    provider.presensiPengawas!.status ==
                                        'Digantikan',
                                onTap: () {
                                  HapticHelper.light();
                                  provider.updatePengawasStatus('Badal');
                                },
                              ),
                            ),
                          ],
                        ),

                        // Dropdown Ustadz Badal jika status Badal
                        if (provider.presensiPengawas!.status == 'Badal' ||
                            provider.presensiPengawas!.status ==
                                'Digantikan') ...[
                          const SizedBox(height: 10),
                          DropdownButtonFormField<int>(
                            initialValue:
                                provider.presensiPengawas!.ustadzPenggantiId,
                            decoration: const InputDecoration(
                              labelText: 'Pilih Ustadz Pengganti (Badal)',
                              prefixIcon: Icon(
                                Icons.swap_horiz_rounded,
                                size: 18,
                              ),
                              contentPadding: EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
                            ),
                            items: provider.presensiDaftarBadal.map((u) {
                              return DropdownMenuItem<int>(
                                value: u['id'] as int,
                                child: Text(
                                  '${u['nama']} (${u['kode'] ?? "-"})',
                                  style: const TextStyle(fontSize: 12),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                final match = provider.presensiDaftarBadal
                                    .firstWhere(
                                      (b) => b['id'] == val,
                                      orElse: () => {},
                                    );
                                provider.updatePengawasPengganti(
                                  val,
                                  match['nama'] as String?,
                                );
                              }
                            },
                          ),
                        ],

                        const SizedBox(height: 10),
                        // Input Berita Acara Ujian
                        TextField(
                          controller: _beritaAcaraController,
                          decoration: const InputDecoration(
                            hintText:
                                'Catatan Berita Acara (misal: Ujian tertib, tidak ada kendala)',
                            labelText: 'Berita Acara Singkat',
                            prefixIcon: Icon(Icons.notes_rounded, size: 16),
                            contentPadding: EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 8,
                            ),
                          ),
                          style: const TextStyle(fontSize: 12),
                          onChanged: (val) => provider.updateBeritaAcara(
                            val.trim().isEmpty ? null : val.trim(),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                ],

                // 1.4 LIVE SUMMARY BAR & AKSI CEPAT
                if (provider.presensiMuridList.isNotEmpty) ...[
                  _buildLiveSummaryBar(context, isDark, provider),
                  const SizedBox(height: 12),
                ],

                // 1.5 DAFTAR SANTRI 3 TPQ
                if (provider.presensiMuridList.isEmpty)
                  _buildBelumAdaMuridEmptyState(context, isDark, isTpq: true)
                else ...[
                  ...provider.presensiMuridList.map((m) {
                    return _buildMuridCard(
                      context,
                      m,
                      provider,
                      isDark,
                      isTpq: true,
                    );
                  }),
                  const SizedBox(height: 16),
                  ElevatedButton.icon(
                    onPressed: provider.isSavingPresensi
                        ? null
                        : () async {
                            HapticHelper.medium();
                            final ok = await provider.simpanPresensi();
                            if (context.mounted) {
                              if (ok) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: const Text(
                                      'Presensi Kelas 3 TPQ berhasil disimpan!',
                                    ),
                                    backgroundColor: AppColors.primaryLight,
                                  ),
                                );
                              } else {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(
                                      provider.errorMessage ??
                                          'Gagal menyimpan presensi.',
                                    ),
                                    backgroundColor: AppColors.roseDanger,
                                  ),
                                );
                              }
                            }
                          },
                    icon: provider.isSavingPresensi
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Icon(Icons.save_rounded, size: 18),
                    label: Text(
                      provider.isSavingPresensi
                          ? 'Menyimpan Presensi...'
                          : 'Simpan Presensi (${provider.countPresensiSudahDiisi}/${provider.totalPresensiMurid})',
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                      foregroundColor: isDark
                          ? AppColors.onPrimaryDark
                          : AppColors.onPrimaryLight,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                  ),
                ],
              ],
            ],

            // ===================================================================
            // VIEW 2: TAB 6 IBT & 3 TSA (SISTEM RUANGAN GABUNGAN IMNI)
            // ===================================================================
            if (!isTpq) ...[
              // 2.1 FILTER TANGGAL & RUANGAN IMNI
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
                          color: AppColors.primaryLight,
                        ),
                        const SizedBox(width: 8),
                        const Expanded(
                          child: Text(
                            'Pilih Tanggal & Ruangan Ujian IMNI',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Selector Tanggal / Hari Ujian
                    if (provider.presensiDaftarHariUjian.isNotEmpty) ...[
                      DropdownButtonFormField<String>(
                        key: ValueKey(
                          'tgl_${provider.presensiSelectedTanggal}',
                        ),
                        initialValue:
                            provider.presensiDaftarHariUjian.any(
                              (h) =>
                                  h.tanggal == provider.presensiSelectedTanggal,
                            )
                            ? provider.presensiSelectedTanggal
                            : (provider.presensiDaftarHariUjian.isNotEmpty
                                  ? provider
                                        .presensiDaftarHariUjian
                                        .first
                                        .tanggal
                                  : null),
                        decoration: const InputDecoration(
                          labelText: 'Tanggal / Hari Ujian IMNI',
                          prefixIcon: Icon(Icons.event_rounded, size: 18),
                          contentPadding: EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 10,
                          ),
                        ),
                        items: provider.presensiDaftarHariUjian.map((h) {
                          return DropdownMenuItem<String>(
                            value: h.tanggal,
                            child: Text(
                              'Hari ${h.hariKe} • ${h.namaHari} (${h.tanggalFormat})',
                              style: const TextStyle(fontSize: 13),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) {
                            HapticHelper.light();
                            provider.selectPresensiTanggal(val);
                            if (provider.presensiPengawas?.catatanBeritaAcara !=
                                null) {
                              _beritaAcaraController.text = provider
                                  .presensiPengawas!
                                  .catatanBeritaAcara!;
                            }
                          }
                        },
                      ),
                      const SizedBox(height: 12),
                    ],

                    // Selector Ruangan IMNI (R1, R2, ...)
                    if (provider.presensiDaftarRuanganImni.isNotEmpty) ...[
                      DropdownButtonFormField<int>(
                        key: ValueKey(
                          'ruangan_imni_${provider.presensiSelectedTanggal}_${provider.presensiSelectedRuanganImniId}',
                        ),
                        initialValue:
                            provider.presensiDaftarRuanganImni.any(
                              (r) =>
                                  r.id ==
                                  provider.presensiSelectedRuanganImniId,
                            )
                            ? provider.presensiSelectedRuanganImniId
                            : (provider.presensiDaftarRuanganImni.isNotEmpty
                                  ? provider.presensiDaftarRuanganImni.first.id
                                  : null),
                        decoration: const InputDecoration(
                          labelText: 'Ruangan Ujian IMNI (Gabungan)',
                          prefixIcon: Icon(
                            Icons.meeting_room_rounded,
                            size: 18,
                          ),
                          contentPadding: EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 10,
                          ),
                        ),
                        items: provider.presensiDaftarRuanganImni.map((r) {
                          return DropdownMenuItem<int>(
                            value: r.id,
                            child: Text(
                              '[${r.namaRuanganImni}] ${r.ruanganFisikNama}',
                              style: const TextStyle(fontSize: 13),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) {
                            HapticHelper.light();
                            provider.selectPresensiRuanganImni(val);
                            if (provider.presensiPengawas?.catatanBeritaAcara !=
                                null) {
                              _beritaAcaraController.text = provider
                                  .presensiPengawas!
                                  .catatanBeritaAcara!;
                            }
                          }
                        },
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 14),

              if (provider.isLoadingPresensi && provider.presensiData == null)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 20),
                  child: ShimmerLoadingList(count: 5, height: 90),
                )
              else if (provider.presensiDaftarRuanganImni.isEmpty)
                _buildBelumAdaRuanganEmptyState(
                  context,
                  isDark,
                  primaryColor,
                  onPrimaryColor,
                  provider,
                  isTpq: false,
                )
              else ...[
                // 2.2 INFO MAPEL HARI INI & PENANGGUNG JAWAB RUANGAN
                if (provider.presensiSelectedRuanganImni != null) ...[
                  GlassCard(
                    padding: const EdgeInsets.all(14),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: Colors.indigo.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(
                                Icons.meeting_room_rounded,
                                size: 20,
                                color: Colors.indigo,
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Ruangan: ${provider.presensiSelectedRuanganImni!.namaRuanganImni} (${provider.presensiSelectedRuanganImni!.ruanganFisikNama})',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    'PJ Ruangan: ${provider.presensiSelectedRuanganImni!.penanggungJawab}',
                                    style: TextStyle(
                                      fontSize: 11,
                                      color: isDark
                                          ? Colors.white70
                                          : Colors.black87,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        if (provider.presensiSelectedHariInfo != null) ...[
                          const SizedBox(height: 10),
                          const Divider(height: 1),
                          const SizedBox(height: 10),
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Expanded(
                                child: Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: Colors.indigo.withValues(
                                      alpha: 0.08,
                                    ),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        'Mapel 6 IBT',
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                          color: Colors.indigo,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        provider
                                                .presensiSelectedHariInfo!
                                                .jadwalIbt
                                                .isNotEmpty
                                            ? provider
                                                  .presensiSelectedHariInfo!
                                                  .jadwalIbt
                                                  .map((j) => j['nama_mapel'])
                                                  .join(', ')
                                            : '-',
                                        style: const TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.w600,
                                        ),
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: Colors.teal.withValues(alpha: 0.08),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        'Mapel 3 TSA',
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                          color: Colors.teal,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        provider
                                                .presensiSelectedHariInfo!
                                                .jadwalTsa
                                                .isNotEmpty
                                            ? provider
                                                  .presensiSelectedHariInfo!
                                                  .jadwalTsa
                                                  .map((j) => j['nama_mapel'])
                                                  .join(', ')
                                            : '-',
                                        style: const TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.w600,
                                        ),
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                ],

                // 2.3 CARD PENGAWAS RUANGAN & BERITA ACARA (INLINE)
                if (provider.presensiPengawas != null) ...[
                  GlassCard(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(7),
                              decoration: BoxDecoration(
                                color: AppColors.violetAccent.withValues(
                                  alpha: 0.15,
                                ),
                                shape: BoxShape.rectangle,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(
                                Icons.person_pin_rounded,
                                size: 16,
                                color: AppColors.violetAccent,
                              ),
                            ),
                            const SizedBox(width: 8),
                            const Expanded(
                              child: Text(
                                'Pengawas Ruangan & Berita Acara',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        // Info Pengawas Utama Terjadwal
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                          decoration: BoxDecoration(
                            color: (isDark ? Colors.white : Colors.black)
                                .withValues(alpha: 0.04),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.badge_outlined, size: 15),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  'Pengawas Terjadwal: ${provider.presensiPengawas!.ustadzNama ?? "Belum Ditentukan"}',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color:
                                        (provider.presensiPengawas?.isPlotted ==
                                            true)
                                        ? null
                                        : Colors.amber.shade800,
                                  ),
                                ),
                              ),
                              if (provider.presensiPengawas?.isPlotted !=
                                  true) ...[
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 6,
                                    vertical: 2,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.amber.withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    'Belum diplot',
                                    style: TextStyle(
                                      fontSize: 9,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.amber.shade800,
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                        const SizedBox(height: 10),

                        // Status Kehadiran Pengawas (H, I, S, B)
                        Row(
                          children: [
                            Expanded(
                              child: StatusPresensiChip(
                                status: 'H',
                                label: 'Hadir',
                                isSelected:
                                    provider.presensiPengawas!.status ==
                                    'Hadir',
                                onTap: () {
                                  HapticHelper.light();
                                  provider.updatePengawasStatus('Hadir');
                                },
                              ),
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: StatusPresensiChip(
                                status: 'I',
                                label: 'Izin',
                                isSelected:
                                    provider.presensiPengawas!.status == 'Izin',
                                onTap: () {
                                  HapticHelper.light();
                                  provider.updatePengawasStatus('Izin');
                                },
                              ),
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: StatusPresensiChip(
                                status: 'S',
                                label: 'Sakit',
                                isSelected:
                                    provider.presensiPengawas!.status ==
                                    'Sakit',
                                onTap: () {
                                  HapticHelper.light();
                                  provider.updatePengawasStatus('Sakit');
                                },
                              ),
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: StatusPresensiChip(
                                status: 'B',
                                label: 'Badal',
                                isSelected:
                                    provider.presensiPengawas!.status ==
                                        'Badal' ||
                                    provider.presensiPengawas!.status ==
                                        'Digantikan',
                                onTap: () {
                                  HapticHelper.light();
                                  provider.updatePengawasStatus('Badal');
                                },
                              ),
                            ),
                          ],
                        ),

                        // Dropdown Ustadz Badal jika status Badal
                        if (provider.presensiPengawas!.status == 'Badal' ||
                            provider.presensiPengawas!.status ==
                                'Digantikan') ...[
                          const SizedBox(height: 10),
                          DropdownButtonFormField<int>(
                            initialValue:
                                provider.presensiPengawas!.ustadzPenggantiId,
                            decoration: const InputDecoration(
                              labelText: 'Pilih Ustadz Pengganti (Badal)',
                              prefixIcon: Icon(
                                Icons.swap_horiz_rounded,
                                size: 18,
                              ),
                              contentPadding: EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
                            ),
                            items: provider.presensiDaftarBadal.map((u) {
                              return DropdownMenuItem<int>(
                                value: u['id'] as int,
                                child: Text(
                                  '${u['nama']} (${u['kode'] ?? "-"})',
                                  style: const TextStyle(fontSize: 12),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                final match = provider.presensiDaftarBadal
                                    .firstWhere(
                                      (b) => b['id'] == val,
                                      orElse: () => {},
                                    );
                                provider.updatePengawasPengganti(
                                  val,
                                  match['nama'] as String?,
                                );
                              }
                            },
                          ),
                        ],

                        const SizedBox(height: 10),
                        // Input Berita Acara Ujian
                        TextField(
                          controller: _beritaAcaraController,
                          decoration: const InputDecoration(
                            hintText:
                                'Catatan Berita Acara (misal: Ujian tertib, tidak ada kendala)',
                            labelText: 'Berita Acara Singkat',
                            prefixIcon: Icon(Icons.notes_rounded, size: 16),
                            contentPadding: EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 8,
                            ),
                          ),
                          style: const TextStyle(fontSize: 12),
                          onChanged: (val) => provider.updateBeritaAcara(
                            val.trim().isEmpty ? null : val.trim(),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                ],

                // 2.4 LIVE SUMMARY BAR & AKSI CEPAT
                if (provider.presensiMuridList.isNotEmpty) ...[
                  _buildLiveSummaryBar(context, isDark, provider),
                  const SizedBox(height: 12),
                ],

                // 2.5 DAFTAR SANTRI 6 IBT & 3 TSA
                if (provider.presensiMuridList.isEmpty)
                  _buildBelumAdaMuridEmptyState(context, isDark, isTpq: false)
                else ...[
                  ...provider.presensiMuridList.map((m) {
                    return _buildMuridCard(
                      context,
                      m,
                      provider,
                      isDark,
                      isTpq: false,
                    );
                  }),
                  const SizedBox(height: 16),
                  ElevatedButton.icon(
                    onPressed: provider.isSavingPresensi
                        ? null
                        : () async {
                            HapticHelper.medium();
                            final ok = await provider.simpanPresensi();
                            if (context.mounted) {
                              if (ok) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: const Text(
                                      'Presensi Ruangan IMNI berhasil disimpan!',
                                    ),
                                    backgroundColor: AppColors.primaryLight,
                                  ),
                                );
                              } else {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(
                                      provider.errorMessage ??
                                          'Gagal menyimpan presensi.',
                                    ),
                                    backgroundColor: AppColors.roseDanger,
                                  ),
                                );
                              }
                            }
                          },
                    icon: provider.isSavingPresensi
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Icon(Icons.save_rounded, size: 18),
                    label: Text(
                      provider.isSavingPresensi
                          ? 'Menyimpan Presensi...'
                      : 'Simpan Presensi Ruangan (${provider.countPresensiSudahDiisi}/${provider.totalPresensiMurid})',
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                      foregroundColor: isDark
                          ? AppColors.onPrimaryDark
                          : AppColors.onPrimaryLight,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                  ),
                ],
              ],
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildLiveSummaryBar(
    BuildContext context,
    bool isDark,
    PanitiaImniProvider provider,
  ) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: isDark ? AppColors.surfaceContainerLowDark : AppColors.surfaceContainerLowLight,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isDark ? AppColors.outlineDark : AppColors.outlineLight,
        ),
      ),
      child: Row(
        children: [
          Expanded(
            child: Wrap(
              spacing: 6,
              runSpacing: 4,
              children: [
                _buildBadge(
                  'Total: ${provider.totalPresensiMurid}',
                  Colors.grey,
                ),
                if (provider.countPresensiBelum > 0)
                  _buildBadge(
                    'Belum: ${provider.countPresensiBelum}',
                    AppColors.amberAccent,
                  ),
                _buildBadge(
                  'Hadir: ${provider.countPresensiHadir}',
                  AppColors.primaryLight,
                ),
                if (provider.countPresensiIzin > 0)
                  _buildBadge(
                    'Izin: ${provider.countPresensiIzin}',
                    AppColors.skyBlueAccent,
                  ),
                if (provider.countPresensiSakit > 0)
                  _buildBadge(
                    'Sakit: ${provider.countPresensiSakit}',
                    AppColors.amberAccent,
                  ),
                if (provider.countPresensiAlpha > 0)
                  _buildBadge(
                    'Alpha: ${provider.countPresensiAlpha}',
                    AppColors.roseDanger,
                  ),
                if (provider.countPresensiDispensasi > 0)
                  _buildBadge(
                    'Dispen: ${provider.countPresensiDispensasi}',
                    AppColors.violetAccent,
                  ),
              ],
            ),
          ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert_rounded, size: 20),
            tooltip: 'Aksi Cepat',
            onSelected: (val) {
              if (val == 'hadir') {
                provider.setAllPresensiStatus('Hadir');
              } else if (val == 'kosong') {
                provider.setSemuaPresensiKosong();
              }
            },
            itemBuilder: (ctx) => [
              PopupMenuItem(
                value: 'hadir',
                child: Row(
                  children: [
                    Icon(
                      Icons.done_all_rounded,
                      size: 18,
                      color: AppColors.primaryLight,
                    ),
                    const SizedBox(width: 8),
                    const Text(
                      'Hadirkan Semua',
                      style: TextStyle(fontSize: 12),
                    ),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'kosong',
                child: Row(
                  children: [
                    Icon(
                      Icons.refresh_rounded,
                      size: 18,
                      color: AppColors.amberAccent,
                    ),
                    SizedBox(width: 8),
                    Text('Kosongkan Semua', style: TextStyle(fontSize: 12)),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildMuridCard(
    BuildContext context,
    PresensiImniMuridItem m,
    PanitiaImniProvider provider,
    bool isDark, {
    required bool isTpq,
  }) {
    final isBelumDiisi = (m.status == null || m.status!.isEmpty);
    final statusColor = _getStatusColor(m.status);

    Color getTingkatColor(String? tk) {
      if (tk == null) return Colors.blueGrey;
      if (tk.contains('6') || tk.contains('IBT')) return Colors.orange;
      if (tk.contains('TSA') || tk.contains('3 TSA')) return Colors.blue;
      if (tk.contains('TPQ')) return Colors.green;
      return Colors.blueGrey;
    }

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Santri (Avatar, Nama, NISM, Badge Status & Lock status)
          Row(
            children: [
              CircleAvatar(
                radius: 18,
                backgroundColor: isBelumDiisi
                    ? (isDark
                          ? Colors.white10
                          : Colors.black.withValues(alpha: 0.06))
                    : statusColor.withValues(alpha: 0.15),
                child: !isTpq && m.nomorMeja != null
                    ? Text(
                        '#${m.nomorMeja}',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: isBelumDiisi
                              ? (isDark
                                    ? const Color(0xFF8D9387)
                                    : const Color(0xFF73796E))
                              : statusColor,
                        ),
                      )
                    : Text(
                        m.nama.isNotEmpty ? m.nama[0].toUpperCase() : 'M',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: isBelumDiisi
                              ? (isDark
                                    ? const Color(0xFF8D9387)
                                    : const Color(0xFF73796E))
                              : statusColor,
                        ),
                      ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            m.nama,
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (!isTpq &&
                            m.ruanganAsal != null &&
                            m.ruanganAsal != '-' &&
                            m.ruanganAsal!.isNotEmpty) ...[
                          const SizedBox(width: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: getTingkatColor(
                                m.ruanganAsal,
                              ).withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              m.ruanganAsal!,
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: getTingkatColor(m.ruanganAsal),
                              ),
                            ),
                          ),
                        ],

                        const SizedBox(width: 6),
                        _buildStatusBadge(m.status, isDark),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'NISM: ${m.nism} • ${m.jenisKelamin == 'L' ? 'Murid Putra' : 'Murid Putri'}',
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
              IconButton(
                icon: Icon(
                  m.catatan != null && m.catatan!.isNotEmpty
                      ? Icons.comment_rounded
                      : Icons.mode_comment_outlined,
                  size: 16,
                  color: m.catatan != null && m.catatan!.isNotEmpty
                      ? AppColors.skyBlueAccent
                      : (isDark
                            ? const Color(0xFF8D9387)
                            : const Color(0xFF73796E)),
                ),
                tooltip: 'Catatan Presensi',
                onPressed: () =>
                    _showCatatanDialog(m.muridId, m.nama, m.catatan),
                visualDensity: VisualDensity.compact,
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Garis Pembatas Halus
          Divider(
            height: 1,
            thickness: 0.8,
            color: isDark
                ? AppColors.outlineDark.withValues(alpha: 0.4)
                : AppColors.outlineLight.withValues(alpha: 0.7),
          ),

          const SizedBox(height: 10),

          // 5 Tombol Presensi (H, I, S, A, D) Fleksibel & Menyesuaikan Layar
          Row(
            children: [
              Expanded(
                child: StatusPresensiChip(
                  status: 'H',
                  label: 'Hadir',
                  isSelected: m.status == 'Hadir',
                  onTap: () {
                    HapticHelper.light();
                    provider.setPresensiStatus(m.muridId, 'Hadir');
                  },
                ),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: StatusPresensiChip(
                  status: 'I',
                  label: 'Izin',
                  isSelected: m.status == 'Izin',
                  onTap: () {
                    HapticHelper.light();
                    provider.setPresensiStatus(m.muridId, 'Izin');
                  },
                ),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: StatusPresensiChip(
                  status: 'S',
                  label: 'Sakit',
                  isSelected: m.status == 'Sakit',
                  onTap: () {
                    HapticHelper.light();
                    provider.setPresensiStatus(m.muridId, 'Sakit');
                  },
                ),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: StatusPresensiChip(
                  status: 'A',
                  label: 'Alpha',
                  isSelected: m.status == 'Alpha',
                  onTap: () {
                    HapticHelper.light();
                    provider.setPresensiStatus(m.muridId, 'Alpha');
                  },
                ),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: StatusPresensiChip(
                  status: 'D',
                  label: 'Dispen',
                  isSelected: m.status == 'Dispensasi',
                  onTap: () {
                    HapticHelper.light();
                    provider.setPresensiStatus(m.muridId, 'Dispensasi');
                  },
                ),
              ),
            ],
          ),

          if (m.catatan != null && m.catatan!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              'Memo: ${m.catatan!}',
              style: const TextStyle(
                fontSize: 10,
                fontStyle: FontStyle.italic,
                color: AppColors.skyBlueAccent,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildBelumAdaRuanganEmptyState(
    BuildContext context,
    bool isDark,
    Color primaryColor,
    Color onPrimaryColor,
    PanitiaImniProvider provider, {
    required bool isTpq,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 8),
      child: GlassCard(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: primaryColor.withValues(alpha: isDark ? 0.2 : 0.1),
                borderRadius: BorderRadius.circular(24),
              ),
              child: Icon(
                Icons.meeting_room_outlined,
                size: 48,
                color: primaryColor,
              ),
            ),
            const SizedBox(height: 18),
            Text(
              isTpq
                  ? 'Belum Ada Ruangan Kelas 3 TPQ'
                  : 'Belum Ada Master Ruangan IMNI',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              isTpq
                  ? 'Tidak ditemukan data ruangan kelas 3 TPQ pada tahun ajaran aktif.'
                  : 'Panitia IMNI belum membuat data master Ruangan IMNI.',
              style: TextStyle(
                fontSize: 13,
                height: 1.4,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: () => provider.fetchPresensiData(),
              icon: const Icon(Icons.refresh_rounded, size: 18),
              label: const Text('Muat Ulang Data'),
              style: ElevatedButton.styleFrom(
                backgroundColor: primaryColor,
                foregroundColor: onPrimaryColor,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 12,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBelumAdaJadwalEmptyState(
    BuildContext context,
    bool isDark,
    PanitiaImniProvider provider, {
    required bool isTpq,
  }) {
    final ruanganName = isTpq
        ? (provider.currentPresensiRuangan?.namaRuangan ?? 'Ruangan ini')
        : 'Ruangan ini';

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
                borderRadius: BorderRadius.circular(24),
              ),
              child: Icon(
                Icons.pending_actions_rounded,
                size: 48,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Jadwal Ujian Belum Dibuat',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Jadwal ujian untuk $ruanganName belum dibuat oleh Administrator.',
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

  Widget _buildBelumAdaMuridEmptyState(
    BuildContext context,
    bool isDark, {
    required bool isTpq,
  }) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(8, 12, 8, 24),
      child: GlassCard(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                    .withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(24),
              ),
              child: Icon(
                isTpq ? Icons.school_outlined : Icons.meeting_room_outlined,
                size: 40,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              isTpq
                  ? 'Belum Ada Murid di Ruangan TPQ Ini'
                  : 'Belum Ada Murid yang Diplot ke Ruangan Ini',
              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              isTpq
                  ? 'Tidak ada data Murid aktif terdaftar di ruangan kelas 3 TPQ ini.'
                  : 'Panitia IMNI belum melakukan plotting Murid untuk ruangan ini pada tanggal ujian terpilih dari sistem backend.',
              style: TextStyle(
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
                fontSize: 12,
                height: 1.4,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String? status) {
    switch (status) {
      case 'Hadir':
        return AppColors.primaryLight;
      case 'Sakit':
        return AppColors.amberAccent;
      case 'Izin':
        return AppColors.skyBlueAccent;
      case 'Alpha':
        return AppColors.roseDanger;
      case 'Badal':
      case 'Dispensasi':
        return AppColors.violetAccent;
      default:
        return Colors.grey;
    }
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
      case 'Badal':
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

  Widget _buildBadge(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.bold,
          color: color,
        ),
      ),
    );
  }
}
