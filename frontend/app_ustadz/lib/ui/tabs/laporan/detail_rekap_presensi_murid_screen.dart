import 'package:flutter/material.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/laporan_model.dart';
import '../../../data/repositories/laporan_repository.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class DetailRekapPresensiMuridScreen extends StatefulWidget {
  final int? ruanganId;
  final int? jadwalId;
  final int? semesterId;
  final int? bulanHijriyahId;
  final String title;
  final String? namaMapel;
  final String? namaUstadz;
  final String? namaRuangan;
  final String? jadwalInfo;

  const DetailRekapPresensiMuridScreen({
    super.key,
    this.ruanganId,
    this.jadwalId,
    this.semesterId,
    this.bulanHijriyahId,
    required this.title,
    this.namaMapel,
    this.namaUstadz,
    this.namaRuangan,
    this.jadwalInfo,
  });

  @override
  State<DetailRekapPresensiMuridScreen> createState() =>
      _DetailRekapPresensiMuridScreenState();
}

class _DetailRekapPresensiMuridScreenState
    extends State<DetailRekapPresensiMuridScreen> {
  final LaporanRepository _repo = LaporanRepository();
  LaporanPresensiMuridData? _data;
  bool _isLoading = true;
  String? _error;
  String _searchSantri = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadData();
    _searchController.addListener(() {
      setState(() {
        _searchSantri = _searchController.text.trim().toLowerCase();
      });
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    HapticHelper.light();
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await _repo.getLaporanPresensiMurid(
        ruanganId: widget.ruanganId,
        jadwalId: widget.jadwalId,
        semesterId: widget.semesterId,
        bulanHijriyahId: widget.bulanHijriyahId,
      );
      if (mounted) {
        setState(() {
          _data = res;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString().replaceAll('Exception: ', '');
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;

    final allSantri = _data?.rekapMurid ?? [];
    final filteredSantri = allSantri.where((s) {
      if (_searchSantri.isEmpty) return true;
      return s.nama.toLowerCase().contains(_searchSantri) ||
          s.nism.toLowerCase().contains(_searchSantri);
    }).toList();

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Rekap Presensi Murid',
        subtitleText: widget.title,
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        color: primary,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
          children: [
            // 1. KARTU RINGKASAN REKAP
            if (_isLoading)
              const ShimmerLoadingList(count: 1)
            else if (_error != null)
              GlassCard(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    Icon(
                      Icons.error_outline_rounded,
                      color: AppColors.roseDanger,
                      size: 36,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _error!,
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontSize: 12),
                    ),
                    const SizedBox(height: 12),
                    ElevatedButton(
                      onPressed: _loadData,
                      child: const Text('Coba Lagi'),
                    ),
                  ],
                ),
              )
            else
              GlassCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    // Header Card
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _data?.selectedJadwal != null
                                    ? _data!.selectedJadwal!.namaMapel
                                    : (_data?.namaRuangan.isNotEmpty == true
                                          ? 'Rekap Presensi ${_data!.namaRuangan}'
                                          : 'Rekap Keseluruhan Kelas'),
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 3),
                              Text(
                                _data?.selectedJadwal != null
                                    ? 'Pengampu: ${_data?.selectedJadwal?.namaUstadz} • ${_data?.totalHariEfektif ?? 0} Sesi'
                                    : 'Wali: ${_data?.waliRuanganNama ?? "-"} • ${_data?.totalHariEfektif ?? 0} Hari Efektif',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isDark
                                      ? const Color(0xFF94A3B8)
                                      : const Color(0xFF64748B),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 5,
                          ),
                          decoration: BoxDecoration(
                            color: primary.withValues(
                              alpha: isDark ? 0.22 : 0.12,
                            ),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(
                              color: primary.withValues(alpha: 0.35),
                            ),
                          ),
                          child: Text(
                            '${_data?.persentaseKehadiranKelas ?? 0}%',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w900,
                              color: primary,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Progress Bar
                    ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: LinearProgressIndicator(
                        value: ((_data?.persentaseKehadiranKelas ?? 0) / 100)
                            .clamp(0.0, 1.0),
                        minHeight: 8,
                        backgroundColor: isDark
                            ? const Color(0xFF263326)
                            : const Color(0xFFE2E8F0),
                        valueColor: AlwaysStoppedAnimation<Color>(primary),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Grid 5 Status
                    Row(
                      children: [
                        _buildStatBox(
                          label: 'Hadir',
                          count: _data?.totalHadir ?? 0,
                          color: AppColors.hadirTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 5),
                        _buildStatBox(
                          label: 'Sakit',
                          count: _data?.totalSakit ?? 0,
                          color: AppColors.sakitTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 5),
                        _buildStatBox(
                          label: 'Izin',
                          count: _data?.totalIzin ?? 0,
                          color: AppColors.izinTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 5),
                        _buildStatBox(
                          label: 'Alpha',
                          count: _data?.totalAlpha ?? 0,
                          color: AppColors.alphaTextLight,
                          isDark: isDark,
                        ),
                        const SizedBox(width: 5),
                        _buildStatBox(
                          label: 'Dispen',
                          count: _data?.totalDispensasi ?? 0,
                          color: AppColors.violetAccent,
                          isDark: isDark,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 14),

            // 2. SEARCH BAR SANTRI
            TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari nama Murid atau NISM...',
                prefixIcon: const Icon(Icons.search_rounded, size: 20),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded, size: 18),
                        onPressed: () => _searchController.clear(),
                      )
                    : null,
                filled: true,
                fillColor: isDark ? const Color(0xFF162016) : Colors.white,
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide(
                    color: isDark
                        ? const Color(0xFF263326)
                        : const Color(0xFFE2E8F0),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),

            // 3. DAFTAR REKAPITULASI PRESENSI PER MURID
            if (_isLoading)
              const ShimmerLoadingList(count: 4)
            else if (filteredSantri.isEmpty)
              const GlassCard(
                padding: EdgeInsets.all(28),
                child: Center(
                  child: Text('Tidak ada data Murid yang ditemukan.'),
                ),
              )
            else ...[
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Daftar Murid (${filteredSantri.length} Santri)',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  if (_data?.totalPoinKelas != null &&
                      _data!.totalPoinKelas > 0)
                    Text(
                      'Total Poin: ${_data!.totalPoinKelas.toStringAsFixed(1)}',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: AppColors.roseDanger,
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 8),

              ...filteredSantri.asMap().entries.map((entry) {
                final idx = entry.key + 1;
                final santri = entry.value;

                Color poinColor = isDark
                    ? const Color(0xFF94A3B8)
                    : const Color(0xFF64748B);
                if (santri.akumulasiPoin >= 5) {
                  poinColor = AppColors.roseDanger;
                } else if (santri.akumulasiPoin > 0) {
                  poinColor = Colors.orange;
                }

                Color persenColor = AppColors.hadirTextLight;
                if (santri.persentaseKehadiran < 70) {
                  persenColor = AppColors.roseDanger;
                } else if (santri.persentaseKehadiran < 85) {
                  persenColor = Colors.orange;
                }

                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Baris 1: Nomor, Nama, NISM & Akumulasi Poin
                      Row(
                        children: [
                          Container(
                            width: 26,
                            height: 26,
                            decoration: BoxDecoration(
                              color: primary.withValues(
                                alpha: isDark ? 0.2 : 0.12,
                              ),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Center(
                              child: Text(
                                '$idx',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: primary,
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
                                  santri.nama,
                                  style: const TextStyle(
                                    fontSize: 13.5,
                                    fontWeight: FontWeight.bold,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'NISM: ${santri.nism} • Wali: ${santri.wali}',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    color: isDark
                                        ? const Color(0xFF94A3B8)
                                        : const Color(0xFF64748B),
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 7,
                              vertical: 2.5,
                            ),
                            decoration: BoxDecoration(
                              color: poinColor.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(
                                color: poinColor.withValues(alpha: 0.35),
                              ),
                            ),
                            child: Text(
                              '${santri.akumulasiPoin.toStringAsFixed(1)} Poin',
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: poinColor,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),

                      // Baris 2: Breakdown Mini Badge H/S/I/A/D & Persentase Hadir
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              _buildMiniBadge(
                                'H: ${santri.hadirCount}',
                                AppColors.hadirTextLight,
                                isDark,
                              ),
                              const SizedBox(width: 4),
                              _buildMiniBadge(
                                'S: ${santri.sakitCount}',
                                AppColors.sakitTextLight,
                                isDark,
                              ),
                              const SizedBox(width: 4),
                              _buildMiniBadge(
                                'I: ${santri.izinCount}',
                                AppColors.izinTextLight,
                                isDark,
                              ),
                              const SizedBox(width: 4),
                              _buildMiniBadge(
                                'A: ${santri.alphaCount}',
                                AppColors.alphaTextLight,
                                isDark,
                              ),
                              const SizedBox(width: 4),
                              _buildMiniBadge(
                                'D: ${santri.dispensasiCount}',
                                AppColors.violetAccent,
                                isDark,
                              ),
                            ],
                          ),
                          Text(
                            '${santri.persentaseKehadiran}% Hadir',
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w900,
                              color: persenColor,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                );
              }),

              const SizedBox(height: 8),
              // Keterangan Rumus Poin Kehadiran
              GlassCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.info_outline_rounded, size: 15, color: primary),
                    const SizedBox(width: 6),
                    Flexible(
                      child: Text(
                        'Rumus Poin: 1 Alpha = ${_data?.poinAlphaRate ?? 1} Poin  |  1 Izin = ${_data?.poinIzinRate ?? 0.16} Poin',
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.bold,
                          color: isDark
                              ? const Color(0xFF94A3B8)
                              : const Color(0xFF64748B),
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ],
                ),
              ),
            ],

            // 4. LOG RIWAYAT PERTEMUAN (JIKA ADA & JADWAL SPESIFIK)
            if ((_data?.riwayatPertemuan ?? []).isNotEmpty) ...[
              const SizedBox(height: 18),
              Row(
                children: [
                  Icon(Icons.history_rounded, size: 18, color: primary),
                  const SizedBox(width: 6),
                  Text(
                    'Log Pertemuan (${_data!.riwayatPertemuan.length} Sesi)',
                    style: const TextStyle(
                      fontSize: 13.5,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),

              ...(_data?.riwayatPertemuan ?? []).map((riwayat) {
                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            riwayat.hariTanggal ?? riwayat.tanggal,
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Row(
                            children: [
                              _buildMiniBadge(
                                'H: ${riwayat.totalHadir}',
                                AppColors.hadirTextLight,
                                isDark,
                              ),
                              const SizedBox(width: 4),
                              _buildMiniBadge(
                                'S: ${riwayat.totalSakit}',
                                AppColors.sakitTextLight,
                                isDark,
                              ),
                              const SizedBox(width: 4),
                              _buildMiniBadge(
                                'I: ${riwayat.totalIzin}',
                                AppColors.izinTextLight,
                                isDark,
                              ),
                              const SizedBox(width: 4),
                              _buildMiniBadge(
                                'A: ${riwayat.totalAlpha}',
                                AppColors.alphaTextLight,
                                isDark,
                              ),
                              const SizedBox(width: 4),
                              _buildMiniBadge(
                                'D: ${riwayat.totalDispensasi}',
                                AppColors.violetAccent,
                                isDark,
                              ),
                            ],
                          ),
                        ],
                      ),
                      if (riwayat.muridAbsen.isNotEmpty) ...[
                        const SizedBox(height: 8),
                        const Divider(height: 1),
                        const SizedBox(height: 8),
                        Text(
                          'Murid Tidak Hadir (${riwayat.muridAbsen.length}):',
                          style: TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w600,
                            color: isDark
                                ? const Color(0xFF94A3B8)
                                : const Color(0xFF64748B),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Wrap(
                          spacing: 6,
                          runSpacing: 4,
                          children: riwayat.muridAbsen.map((m) {
                            Color c = AppColors.alphaTextLight;
                            if (m.status == 'Izin') {
                              c = AppColors.izinTextLight;
                            } else if (m.status == 'Sakit') {
                              c = AppColors.sakitTextLight;
                            } else if (m.status == 'Dispensasi') {
                              c = AppColors.violetAccent;
                            }
                            return Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: c.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(
                                  color: c.withValues(alpha: 0.3),
                                ),
                              ),
                              child: Text(
                                '${m.nama} (${m.status})',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: c,
                                ),
                              ),
                            );
                          }).toList(),
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

  Widget _buildStatBox({
    required String label,
    required int count,
    required Color color,
    required bool isDark,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 2),
        decoration: BoxDecoration(
          color: color.withValues(alpha: isDark ? 0.15 : 0.1),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Column(
          children: [
            Text(
              '$count',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w900,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 9.5,
                fontWeight: FontWeight.w600,
                color: isDark
                    ? const Color(0xFF94A3B8)
                    : const Color(0xFF64748B),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMiniBadge(String text, Color color, bool isDark) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: isDark ? 0.18 : 0.12),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 9.5,
          fontWeight: FontWeight.bold,
          color: color,
        ),
      ),
    );
  }
}
