import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../data/models/panitia_imni_model.dart';
import '../../../providers/panitia_imni_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';

class NilaiImniScreen extends StatefulWidget {
  const NilaiImniScreen({super.key});

  @override
  State<NilaiImniScreen> createState() => _NilaiImniScreenState();
}

class _NilaiImniScreenState extends State<NilaiImniScreen> {
  int _selectedIndex = 0;
  final Map<int, TextEditingController> _controllers = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = context.read<PanitiaImniProvider>();
      provider.fetchNilaiData();
    });
  }

  @override
  void dispose() {
    for (var c in _controllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  void _syncControllers(List<NilaiImniMuridItem> murids) {
    for (var m in murids) {
      if (!_controllers.containsKey(m.muridId)) {
        _controllers[m.muridId] = TextEditingController(
          text: m.nilai != null
              ? (m.nilai! % 1 == 0
                    ? m.nilai!.toInt().toString()
                    : m.nilai!.toString())
              : '',
        );
      } else {
        final current = _controllers[m.muridId]!.text;
        final newVal = m.nilai != null
            ? (m.nilai! % 1 == 0
                  ? m.nilai!.toInt().toString()
                  : m.nilai!.toString())
            : '';
        if (current != newVal && !FocusScope.of(context).hasFocus) {
          _controllers[m.muridId]!.text = newVal;
        }
      }
    }
  }

  void _simpanNilai(String action) async {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.read<PanitiaImniProvider>();
    final isPublish = (action == 'publish');

    if (isPublish) {
      final confirm = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          title: const Text('Publikasikan Nilai IMNI?'),
          content: const Text(
            'Nilai yang dipublikasikan akan resmi tercatat pada hasil ujian IMNI dan dapat dilihat pada leger nilai. Lanjutkan?',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.pop(ctx, true),
              style: ElevatedButton.styleFrom(
                backgroundColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
              child: const Text(
                'Ya, Publikasikan',
                style: TextStyle(color: Colors.white),
              ),
            ),
          ],
        ),
      );
      if (confirm != true) return;
    }

    final ok = await provider.simpanNilai(action: action);
    if (mounted) {
      if (ok) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              isPublish
                  ? 'Nilai IMNI resmi dipublikasikan!'
                  : 'Draf nilai IMNI berhasil disimpan.',
            ),
            backgroundColor: isPublish
                ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                : AppColors.skyBlueAccent,
          ),
        );
      } else if (provider.errorMessage != null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(provider.errorMessage!),
            backgroundColor: AppColors.roseDanger,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<PanitiaImniProvider>();

    _syncControllers(provider.nilaiMuridList);

    return Scaffold(
      appBar: const CustomAppBar(
        titleText: 'Penilaian & Leger IMNI',
        subtitleText: 'Input Nilai Peserta & Leger Ranking IMNI',
      ),
      body: Column(
        children: [
          SegmentedTabBar(
            margin: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            selectedIndex: _selectedIndex,
            onTabChanged: (index) {
              setState(() {
                _selectedIndex = index;
              });
              if (index == 1) {
                context.read<PanitiaImniProvider>().fetchLegerData();
              }
            },
            items: [
              SegmentedTabItem(
                activeIcon: Icons.edit_note_rounded,
                inactiveIcon: Icons.edit_outlined,
                label: 'Input Nilai Mapel',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
              SegmentedTabItem(
                activeIcon: Icons.table_chart_rounded,
                inactiveIcon: Icons.table_chart_outlined,
                label: 'Leger & Ranking',
                activeColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
              ),
            ],
          ),
          Expanded(
            child: IndexedStack(
              index: _selectedIndex,
              children: [
                _buildInputNilaiTab(context, provider, isDark),
                _buildLegerTab(context, provider, isDark),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // TAB 1: FORM INPUT NILAI MAPEL
  // =========================================================================
  Widget _buildInputNilaiTab(
    BuildContext context,
    PanitiaImniProvider provider,
    bool isDark,
  ) {
    final jadwals = provider.nilaiJadwalList;
    final selectedJadwal = jadwals.isNotEmpty
        ? jadwals.firstWhere(
            (j) => j.id == provider.nilaiJadwalId,
            orElse: () => jadwals.first,
          )
        : null;

    final isPublished = selectedJadwal?.isPublished ?? false;

    return Stack(
      children: [
        RefreshIndicator(
          onRefresh: () async {
            await provider.fetchNilaiData();
          },
          child: CustomScrollView(
            slivers: [
              // Ruangan & Jadwal Selector
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
                  child: GlassCard(
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
                            const Expanded(
                              child: Text(
                                'Pilih Ruangan & Mata Pelajaran IMNI',
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

                        // Dropdown Ruangan Ujian IMNI
                        DropdownButtonFormField<int>(
                          key: ValueKey('ruangan_${provider.nilaiRuanganId}'),
                          initialValue: provider.nilaiDaftarRuangan.any(
                                (r) => r.id == provider.nilaiRuanganId,
                              )
                              ? provider.nilaiRuanganId
                              : (provider.nilaiDaftarRuangan.isNotEmpty
                                    ? provider.nilaiDaftarRuangan.first.id
                                    : null),
                          decoration: const InputDecoration(
                            labelText: 'Ruangan Ujian IMNI',
                            prefixIcon: Icon(Icons.meeting_room_rounded, size: 18),
                            contentPadding: EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 10,
                            ),
                          ),
                          items: provider.nilaiDaftarRuangan.map((r) {
                            return DropdownMenuItem<int>(
                              value: r.id,
                              child: Text(
                                '${r.namaRuangan} (${r.namaLevel} - ${r.kodeTingkat})',
                                style: const TextStyle(fontSize: 13),
                                overflow: TextOverflow.ellipsis,
                              ),
                            );
                          }).toList(),
                          onChanged: (val) {
                            if (val != null) {
                              provider.selectNilaiRuangan(val);
                            }
                          },
                        ),
                        const SizedBox(height: 12),

                        // Dropdown Mata Pelajaran Berdasarkan Jadwal Ujian
                        if (jadwals.isEmpty)
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? const Color(0xFF161F16)
                                  : Colors.white,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: isDark ? Colors.white12 : Colors.black12,
                              ),
                            ),
                            child: const Center(
                              child: Text(
                                'Tidak ada mapel ujian di ruangan ini.',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey,
                                ),
                              ),
                            ),
                          )
                        else
                          DropdownButtonFormField<int>(
                            key: ValueKey(
                              'jadwal_${provider.nilaiRuanganId}_${provider.nilaiJadwalId}',
                            ),
                            initialValue: jadwals.any(
                                  (j) => j.id == provider.nilaiJadwalId,
                                )
                                ? provider.nilaiJadwalId
                                : (jadwals.isNotEmpty ? jadwals.first.id : null),
                            isExpanded: true,
                            decoration: const InputDecoration(
                              labelText: 'Mata Pelajaran (Jadwal Ujian)',
                              prefixIcon: Icon(Icons.quiz_rounded, size: 18),
                              contentPadding: EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 10,
                              ),
                            ),
                            items: jadwals.map((j) {
                              final dateStr =
                                  j.hariTanggalSingkat ?? j.hariTanggal ?? '-';
                              final timeStr = (j.waktuMulai != null &&
                                      j.waktuSelesai != null)
                                  ? '${j.waktuMulai} - ${j.waktuSelesai}'
                                  : '';
                              final jadwalInfo = timeStr.isNotEmpty
                                  ? '$dateStr • $timeStr'
                                  : dateStr;
                              return DropdownMenuItem<int>(
                                value: j.id,
                                child: Text(
                                  '${j.namaMapel} ($jadwalInfo)',
                                  style: const TextStyle(fontSize: 13),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                provider.selectNilaiJadwal(val);
                              }
                            },
                          ),
                      ],
                    ),
                  ),
                ),
              ),

              // Status Info Banner & Detail Jadwal Terpilih
              if (selectedJadwal != null)
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 4,
                    ),
                    child: GlassCard(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Row(
                                  children: [
                                    Icon(
                                      Icons.event_available_rounded,
                                      size: 16,
                                      color: isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight,
                                    ),
                                    const SizedBox(width: 6),
                                    Expanded(
                                      child: Text(
                                        '${selectedJadwal.hariTanggal ?? selectedJadwal.hariTanggalSingkat ?? '-'}${selectedJadwal.waktuMulai != null ? " • ${selectedJadwal.waktuMulai} - ${selectedJadwal.waktuSelesai} WIB" : ""}',
                                        style: const TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.bold,
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 3,
                                ),
                                decoration: BoxDecoration(
                                  color: (isPublished
                                          ? (isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight)
                                          : AppColors.amberAccent)
                                      .withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(
                                    color: (isPublished
                                            ? (isDark
                                                ? AppColors.primaryDark
                                                : AppColors.primaryLight)
                                            : AppColors.amberAccent)
                                        .withValues(alpha: 0.3),
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      isPublished
                                          ? Icons.verified_rounded
                                          : Icons.pending_actions_rounded,
                                      size: 12,
                                      color: isPublished
                                          ? (isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight)
                                          : AppColors.amberAccent,
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      isPublished
                                          ? 'Terpublikasi'
                                          : 'Draf',
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 11,
                                        color: isPublished
                                            ? (isDark
                                                ? AppColors.primaryDark
                                                : AppColors.primaryLight)
                                            : AppColors.amberAccent,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Text(
                                  'Mapel: ${selectedJadwal.namaMapel}',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: isDark
                                        ? const Color(0xFF8D9387)
                                        : const Color(0xFF73796E),
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              Text(
                                '${provider.nilaiMuridList.where((m) => m.nilai != null).length}/${provider.nilaiMuridList.length} Murid Dinilai',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),

              // Murid List
              if (provider.isLoadingNilai)
                const SliverToBoxAdapter(
                  child: Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16),
                    child: ShimmerLoadingList(count: 6, height: 60),
                  ),
                )
              else if (provider.nilaiMuridList.isEmpty)
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.all(32),
                    child: Center(
                      child: Text(
                        'Tidak ada data murid di ruangan ini.',
                        style: TextStyle(
                          color: isDark ? Colors.white38 : Colors.black38,
                        ),
                      ),
                    ),
                  ),
                )
              else
                SliverPadding(
                  padding: EdgeInsets.fromLTRB(
                    16,
                    8,
                    16,
                    provider.nilaiMuridList.isEmpty
                        ? 16
                        : 110 + MediaQuery.of(context).padding.bottom,
                  ),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate((context, index) {
                      final m = provider.nilaiMuridList[index];
                      return _buildMuridNilaiRow(context, m, isDark);
                    }, childCount: provider.nilaiMuridList.length),
                  ),
                ),
            ],
          ),
        ),

        // Bottom Action Bar (Simpan Draf & Publikasikan) - floating GlassCard like in form_nilai_screen.dart
        if (provider.nilaiMuridList.isNotEmpty)
          Positioned(
            left: 16,
            right: 16,
            bottom: 16 + MediaQuery.of(context).padding.bottom,
            child: GlassCard(
              padding: const EdgeInsets.all(12),
              child: Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: provider.isSavingNilai
                          ? null
                          : () => _simpanNilai('draft'),
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
                      onPressed: provider.isSavingNilai
                          ? null
                          : () => _simpanNilai('publish'),
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        backgroundColor: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: provider.isSavingNilai
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
    );
  }

  Widget _buildMuridNilaiRow(
    BuildContext context,
    NilaiImniMuridItem m,
    bool isDark,
  ) {
    final provider = context.read<PanitiaImniProvider>();
    final ctrl = _controllers[m.muridId];

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF161F16) : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: m.isLocked
              ? AppColors.roseDanger.withValues(alpha: 0.3)
              : (isDark
                    ? Colors.white10
                    : Colors.black.withValues(alpha: 0.06)),
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 28,
            height: 28,
            decoration: BoxDecoration(
              color: isDark
                  ? Colors.white10
                  : Colors.black.withValues(alpha: 0.05),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Text(
                m.nomorMeja != null ? '${m.nomorMeja}' : '-',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                ),
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
                    Flexible(
                      child: Text(
                        m.nama,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    if (m.isLocked) ...[
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.roseDanger.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          'Terkunci',
                          style: TextStyle(
                            fontSize: 9,
                            color: AppColors.roseDanger,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
                Text(
                  'No. ${m.nomorPeserta} • ${m.nism}',
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
          const SizedBox(width: 12),
          SizedBox(
            width: 70,
            height: 40,
            child: TextField(
              controller: ctrl,
              enabled: !m.isLocked,
              keyboardType: const TextInputType.numberWithOptions(
                decimal: true,
              ),
              textAlign: TextAlign.center,
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
              onChanged: (val) {
                final score = double.tryParse(val.trim());
                provider.updateNilaiMurid(m.muridId, score);
              },
              decoration: InputDecoration(
                hintText: m.isLocked ? '-' : '0',
                hintStyle: const TextStyle(color: Colors.grey),
                contentPadding: const EdgeInsets.symmetric(vertical: 8),
                filled: true,
                fillColor: m.isLocked
                    ? (isDark ? Colors.white10 : Colors.black12)
                    : (isDark
                          ? const Color(0xFF1E261E)
                          : const Color(0xFFF3F7F2)),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide(
                    color: isDark ? Colors.white24 : Colors.black26,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // TAB 2: LEGER NILAI & RANKING IMNI
  // =========================================================================
  Widget _buildLegerTab(
    BuildContext context,
    PanitiaImniProvider provider,
    bool isDark,
  ) {
    final legerData = provider.legerData;
    final stat = legerData?.statistik;
    final legerRows = legerData?.leger ?? [];

    return RefreshIndicator(
      onRefresh: () async {
        await provider.fetchLegerData();
      },
      child: CustomScrollView(
        slivers: [
          // Filter Ruangan
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF161F16) : Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isDark ? Colors.white12 : Colors.black12,
                  ),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<int>(
                    isExpanded: true,
                    value: provider.legerDaftarRuangan.any((r) => r.id == provider.legerRuanganId)
                        ? provider.legerRuanganId
                        : (provider.legerDaftarRuangan.isNotEmpty ? provider.legerDaftarRuangan.first.id : null),
                    hint: const Text('Pilih Ruangan IMNI'),
                    items: provider.legerDaftarRuangan.map((r) {
                      return DropdownMenuItem<int>(
                        value: r.id,
                        child: Text(
                          '${r.namaRuangan} (${r.namaLevel} - ${r.kodeTingkat})',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      );
                    }).toList(),
                    onChanged: (val) {
                      if (val != null) {
                        provider.selectLegerRuangan(val);
                      }
                    },
                  ),
                ),
              ),
            ),
          ),

          // Statistik Cards
          if (stat != null)
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 4,
                ),
                child: GlassCard(
                  padding: const EdgeInsets.all(14),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      _buildLegerMiniStat(
                        'Peserta',
                        '${stat.totalMurid}',
                        AppColors.skyBlueAccent,
                        isDark,
                      ),
                      _buildLegerMiniStat(
                        'Rata-rata',
                        '${stat.rataRataKelas}',
                        isDark ? AppColors.primaryDark : AppColors.primaryLight,
                        isDark,
                      ),
                      _buildLegerMiniStat(
                        'Tertinggi',
                        '${stat.nilaiTertinggi}',
                        AppColors.amberAccent,
                        isDark,
                      ),
                      _buildLegerMiniStat(
                        'Terendah',
                        '${stat.nilaiTerendah}',
                        AppColors.roseDanger,
                        isDark,
                      ),
                    ],
                  ),
                ),
              ),
            ),

          // Leger List Cards
          if (provider.isLoadingLeger)
            const SliverToBoxAdapter(
              child: Padding(
                padding: EdgeInsets.symmetric(horizontal: 16),
                child: ShimmerLoadingList(count: 6, height: 80),
              ),
            )
          else if (legerRows.isEmpty)
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Center(
                  child: Text(
                    'Belum ada data nilai leger IMNI.',
                    style: TextStyle(
                      color: isDark ? Colors.white38 : Colors.black38,
                    ),
                  ),
                ),
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate((context, index) {
                  final row = legerRows[index];
                  return _buildLegerCard(context, row, isDark);
                }, childCount: legerRows.length),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildLegerMiniStat(
    String label,
    String value,
    Color color,
    bool isDark,
  ) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: TextStyle(
            fontSize: 10,
            color: isDark ? const Color(0xFF8D9387) : const Color(0xFF73796E),
          ),
        ),
      ],
    );
  }

  Widget _buildLegerCard(
    BuildContext context,
    LegerImniRowItem row,
    bool isDark,
  ) {
    Color rankColor;
    if (row.ranking == 1) {
      rankColor = const Color(0xFFFFD700); // Gold
    } else if (row.ranking == 2) {
      rankColor = const Color(0xFFC0C0C0); // Silver
    } else if (row.ranking == 3) {
      rankColor = const Color(0xFFCD7F32); // Bronze
    } else {
      rankColor = Colors.grey;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF161F16) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: row.ranking <= 3
              ? rankColor.withValues(alpha: 0.5)
              : (isDark
                    ? Colors.white10
                    : Colors.black.withValues(alpha: 0.06)),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: rankColor.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                  border: Border.all(color: rankColor, width: 1.5),
                ),
                child: Center(
                  child: Text(
                    '#${row.ranking}',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: rankColor,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      row.nama,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    Text(
                      'No. ${row.nomorPeserta} • ${row.nism}',
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
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    'Rata: ${row.rataRata}',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 14,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 6,
                      vertical: 2,
                    ),
                    decoration: BoxDecoration(
                      color: AppColors.skyBlueAccent.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      'Predikat: ${row.predikat}',
                      style: TextStyle(
                        fontSize: 9,
                        fontWeight: FontWeight.bold,
                        color: AppColors.skyBlueAccent,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 8),

          // Scores breakdown horizontal chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: row.nilaiMapel.entries.map((entry) {
                return Container(
                  margin: const EdgeInsets.only(right: 6),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: isDark
                        ? const Color(0xFF1E261E)
                        : const Color(0xFFF3F7F2),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      Text(
                        '${entry.key}: ',
                        style: const TextStyle(
                          fontSize: 10,
                          color: Colors.grey,
                        ),
                      ),
                      Text(
                        entry.value != null
                            ? (entry.value! % 1 == 0
                                  ? entry.value!.toInt().toString()
                                  : entry.value!.toString())
                            : '-',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }
}
