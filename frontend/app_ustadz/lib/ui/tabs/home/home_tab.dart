import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/dashboard_model.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/dashboard_provider.dart';
import '../../widgets/app_avatar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/schedule_card.dart';
import '../../widgets/shimmer_loading.dart';
import '../../widgets/signal_indicator_widget.dart';
import '../akademik/jadwal_pelajaran_screen.dart';
import '../akademik/mata_pelajaran_screen.dart';
import '../kas/kas_ruangan_screen.dart';
import '../murid/direktori_murid_screen.dart';
import '../pelanggaran/referensi_pelanggaran_screen.dart';
import '../presensi/form_presensi_screen.dart';
import '../presensi/badal_presensi_screen.dart';
import '../tagihan/tagihan_screen.dart';
import '../tabungan/tabungan_screen.dart';
import '../akun/hubungi_admin_screen.dart';
import '../akun/pengingat_bel_screen.dart';
import '../laporan/laporan_pengampu_screen.dart';
import '../laporan/laporan_ruangan_screen.dart';
import 'kalendar_screen.dart';
import 'pengumuman_screen.dart';
import 'detail_pengumuman_screen.dart';
import '../catatan/catatan_ustadz_screen.dart';
import '../../../providers/presensi_provider.dart';
import '../presensi/checkin_ustadz_sheet.dart';
import '../panitia_imni/pembayaran_imni_screen.dart';
import '../panitia_imni/pengeluaran_imni_screen.dart';
import '../panitia_imni/presensi_imni_screen.dart';
import '../panitia_imni/nilai_imni_screen.dart';

class HomeTab extends StatefulWidget {
  final VoidCallback? onNavigateToPresensiGuru;
  final VoidCallback? onNavigateToPresensiMurid;

  const HomeTab({
    super.key,
    this.onNavigateToPresensiGuru,
    this.onNavigateToPresensiMurid,
  });

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  bool _isRefreshing = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<DashboardProvider>().fetchDashboard();
    });
  }

  Future<void> _refreshData() async {
    if (_isRefreshing) return;
    setState(() {
      _isRefreshing = true;
    });
    HapticHelper.light();

    try {
      await Future.wait([
        context.read<DashboardProvider>().fetchDashboard(),
        context.read<AuthProvider>().fetchProfile(),
      ]);
      if (!mounted) return;
      HapticHelper.medium();
      ScaffoldMessenger.of(context).clearSnackBars();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Row(
            children: [
              Icon(Icons.check_circle_rounded, color: Colors.white, size: 18),
              SizedBox(width: 10),
              Text(
                'Data aplikasi berhasil diperbarui',
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
                ),
              ),
            ],
          ),
          backgroundColor: Theme.of(context).brightness == Brightness.dark
              ? AppColors.primaryDark
              : AppColors.primaryLight,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          duration: const Duration(seconds: 2),
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 80),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).clearSnackBars();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal memperbarui data: $e'),
          backgroundColor: Colors.redAccent,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 80),
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isRefreshing = false;
        });
      }
    }
  }

  Future<void> _handlePresensiGuru(JadwalHariIniItem j) async {
    HapticHelper.light();
    final presensiP = context.read<PresensiProvider>();
    if (presensiP.sesiUstadzList.isEmpty) {
      await presensiP.fetchSesiUstadz();
    }
    if (!mounted) return;
    final matching = presensiP.sesiUstadzList
        .where((s) => s.jadwalId == j.id)
        .firstOrNull;
    if (matching != null) {
      await CheckinUstadzSheet.show(context, matching);
      if (!mounted) return;
      context.read<DashboardProvider>().fetchDashboard();
    } else {
      widget.onNavigateToPresensiGuru?.call();
    }
  }

  Future<void> _handleAbsenMurid(JadwalHariIniItem j) async {
    HapticHelper.light();
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => FormPresensiScreen(
          jadwalId: j.id,
          mapel: j.mapel,
          ruangan: j.kelas,
          jam: j.jam,
        ),
      ),
    );
    if (!mounted) return;
    context.read<DashboardProvider>().fetchDashboard();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final user = context.watch<AuthProvider>().user;
    final dashboard = context.watch<DashboardProvider>();

    return Scaffold(
      body: SafeArea(
        bottom: false,
        child: RefreshIndicator(
          onRefresh: _refreshData,
          color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.fromLTRB(
              18,
              12,
              18,
              120 + MediaQuery.of(context).padding.bottom,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. Header Bar: Profile, Salam, & Action Buttons
                Row(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    AppAvatar(
                      name: user?.name ?? 'Ustadz',
                      imageUrl: user?.photo,
                      radius: 22,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            user?.name ?? '-',
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              letterSpacing: -0.2,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 3),
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 6,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color:
                                      (isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight)
                                          .withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  (user?.isWaliRuangan == true &&
                                          (user?.ruanganWali?.isNotEmpty ??
                                              false))
                                      ? 'Wali ${user!.ruanganWali}'
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
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  user?.tahunPelajaran ?? '1447/1448 H',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w500,
                                    color: isDark
                                        ? const Color(0xFF94A3B8)
                                        : const Color(0xFF64748B),
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 6),
                    const SignalIndicatorWidget(size: 34),
                    const SizedBox(width: 6),
                    Tooltip(
                      message: 'Perbarui & Sinkronisasi Data',
                      child: Material(
                        color: Colors.transparent,
                        child: InkWell(
                          onTap: (_isRefreshing || dashboard.isLoading)
                              ? null
                              : _refreshData,
                          borderRadius: BorderRadius.circular(17),
                          child: Container(
                            width: 34,
                            height: 34,
                            decoration: BoxDecoration(
                              color:
                                  (isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight)
                                      .withValues(alpha: 0.12),
                              shape: BoxShape.circle,
                            ),
                            child: Center(
                              child: _isRefreshing || dashboard.isLoading
                                  ? SizedBox(
                                      width: 16,
                                      height: 16,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        color: isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight,
                                      ),
                                    )
                                  : Icon(
                                      Icons.refresh_rounded,
                                      size: 18,
                                      color: isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight,
                                    ),
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Tooltip(
                      message: 'Hubungi Admin & Pusat Bantuan',
                      child: Material(
                        color: Colors.transparent,
                        child: InkWell(
                          onTap: () {
                            HapticHelper.light();
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const HubungiAdminScreen(),
                              ),
                            );
                          },
                          borderRadius: BorderRadius.circular(17),
                          child: Container(
                            width: 34,
                            height: 34,
                            decoration: BoxDecoration(
                              color:
                                  (isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight)
                                      .withValues(alpha: 0.12),
                              shape: BoxShape.circle,
                            ),
                            child: Center(
                              child: Icon(
                                Icons.support_agent_rounded,
                                size: 18,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.campaign_rounded,
                          size: 19,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                        const SizedBox(width: 6),
                        const Text(
                          'Pengumuman Terkini',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    TextButton(
                      onPressed: () {
                        HapticHelper.light();
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const PengumumanScreen(),
                          ),
                        );
                      },
                      child: const Text(
                        'Lihat Semua',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                if (dashboard.isLoading)
                  const ShimmerLoadingList(count: 2, height: 85)
                else if (dashboard.dashboardData?.pengumumanList.isEmpty ??
                    true)
                  GlassCard(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 18,
                    ),
                    child: Center(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.info_outline_rounded,
                            size: 18,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            'Belum ada pengumuman baru saat ini.',
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
                  )
                else
                  ...dashboard.dashboardData!.pengumumanList.map(
                    (p) => _buildPengumumanCard(context, p, isDark),
                  ),
                const SizedBox(height: 14),

                // 2. Jadwal Mengajar Hari Ini
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.event_available_rounded,
                          size: 19,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                        const SizedBox(width: 6),
                        const Text(
                          'Jadwal Mengajar Hari Ini',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color:
                            (dashboard.dashboardData?.isLiburHariIni ?? false)
                            ? (isDark
                                  ? const Color(0xFF382305)
                                  : const Color(0xFFFEF3C7))
                            : (isDark
                                  ? AppColors.primaryContainerDark
                                  : AppColors.primaryContainerLight),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        (dashboard.dashboardData?.isLiburHariIni ?? false)
                            ? '🏖️ Libur KBM'
                            : '${dashboard.dashboardData?.jadwalHariIniList.length ?? 0} Sesi KBM',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color:
                              (dashboard.dashboardData?.isLiburHariIni ?? false)
                              ? AppColors.amberAccent
                              : (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),

                if (dashboard.isLoading)
                  const ShimmerLoadingList(count: 2)
                else if (dashboard.dashboardData?.isLiburHariIni ?? false)
                  GlassCard(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 20,
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: isDark
                                ? const Color(0xFF382305)
                                : const Color(0xFFFEF3C7),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.beach_access_rounded,
                            size: 24,
                            color: AppColors.amberAccent,
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Hari Ini Libur KBM',
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? Colors.white
                                      : const Color(0xFF92400E),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                dashboard
                                        .dashboardData
                                        ?.keteranganLiburHariIni ??
                                    'Kegiatan Belajar Mengajar Diliburkan',
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
                  )
                else if (dashboard.dashboardData?.jadwalHariIniList.isEmpty ??
                    true)
                  const GlassCard(
                    padding: EdgeInsets.all(18),
                    child: Center(
                      child: Text(
                        'Tidak ada jadwal mengajar pada hari ini.',
                        style: TextStyle(fontSize: 13),
                      ),
                    ),
                  )
                else
                  ...dashboard.dashboardData!.jadwalHariIniList.map(
                    (j) => ScheduleCard(
                      item: j,
                      onPresensiGuruTap: () => _handlePresensiGuru(j),
                      onAbsenTap: () => _handleAbsenMurid(j),
                    ),
                  ),
                const SizedBox(height: 22),

                // 3. Menu Cepat (Ustadz Umum & Wali Ruangan)
                Row(
                  children: [
                    Icon(
                      Icons.widgets_rounded,
                      size: 19,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                    const SizedBox(width: 6),
                    const Text(
                      'Menu Cepat',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                GlassCard(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 14,
                  ),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          _buildQuickAction(
                            icon: Icons.calendar_month_rounded,
                            label: 'Kalender',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const KalendarScreen(),
                                ),
                              );
                            },
                          ),
                          _buildQuickAction(
                            icon: Icons.gavel_rounded,
                            label: 'Ref. Pelanggaran',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) =>
                                      const ReferensiPelanggaranScreen(),
                                ),
                              );
                            },
                          ),
                          _buildQuickAction(
                            icon: Icons.menu_book_rounded,
                            label: 'Mapel',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const MataPelajaranScreen(),
                                ),
                              );
                            },
                          ),
                          _buildQuickAction(
                            icon: Icons.schedule_rounded,
                            label: 'Jadwal',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const JadwalPelajaranScreen(),
                                ),
                              );
                            },
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      Row(
                        children: [
                          _buildQuickAction(
                            icon: Icons.edit_note_rounded,
                            label: 'Catatan',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const CatatanUstadzScreen(),
                                ),
                              );
                            },
                          ),
                          _buildQuickAction(
                            icon: Icons.campaign_rounded,
                            label: 'Pengumuman',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const PengumumanScreen(),
                                ),
                              );
                            },
                          ),
                          _buildQuickAction(
                            icon: Icons.assessment_rounded,
                            label: 'Laporan',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const LaporanPengampuScreen(),
                                ),
                              );
                            },
                          ),
                          _buildQuickAction(
                            icon: Icons.account_balance_wallet_rounded,
                            label: 'Tabungan',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const TabunganScreen(),
                                ),
                              );
                            },
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      Row(
                        children: [
                          _buildQuickAction(
                            icon: Icons.swap_horiz_rounded,
                            label: 'Ustadz Pengganti',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const BadalPresensiScreen(),
                                ),
                              );
                            },
                          ),
                          _buildQuickAction(
                            icon: Icons.notifications_active_rounded,
                            label: 'Pengingat Bel',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const PengingatBelScreen(),
                                ),
                              );
                            },
                          ),
                          _buildQuickAction(
                            icon: Icons.headset_mic_rounded,
                            label: 'Bantuan',
                            isDark: isDark,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const HubungiAdminScreen(),
                                ),
                              );
                            },
                          ),
                          const Expanded(child: SizedBox.shrink()),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 18),

                // Menu Tambahan Khusus Wali Ruangan
                if (user?.isWaliRuangan ?? false) ...[
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Icon(
                            Icons.admin_panel_settings_rounded,
                            size: 19,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            'Wali Ruangan (${user?.ruanganWali ?? "-"})',
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? AppColors.primaryContainerDark
                              : AppColors.primaryContainerLight,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          'Akses Khusus',
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
                  const SizedBox(height: 10),
                  GlassCard(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 14,
                    ),
                    child: Row(
                      children: [
                        _buildQuickAction(
                          icon: Icons.money_rounded,
                          label: 'Kas Ruangan',
                          isDark: isDark,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const KasRuanganScreen(),
                              ),
                            );
                          },
                        ),
                        _buildQuickAction(
                          icon: Icons.receipt_long_rounded,
                          label: 'Tagihan',
                          isDark: isDark,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => TagihanScreen(
                                  initialRuanganId: user?.ruanganWaliId,
                                ),
                              ),
                            );
                          },
                        ),
                        _buildQuickAction(
                          icon: Icons.people_alt_rounded,
                          label: 'Anggota Murid',
                          isDark: isDark,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => DirektoriMuridScreen(
                                  ruanganId: user?.ruanganWaliId,
                                ),
                              ),
                            );
                          },
                        ),
                        _buildQuickAction(
                          icon: Icons.admin_panel_settings_rounded,
                          label: 'Laporan ${user?.ruanganWali ?? "Ruangan"}',
                          isDark: isDark,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const LaporanRuanganScreen(),
                              ),
                            );
                          },
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                ],

                // Menu Tambahan Khusus Kepanitiaan IMNI
                if (user?.isPanitiaImni ?? false) ...[
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          const Icon(
                            Icons.stars_rounded,
                            size: 19,
                            color: AppColors.amberAccent,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            'Kepanitiaan IMNI (${user?.jabatanPanitiaImni ?? "Panitia"})',
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF382305)
                              : const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Text(
                          'Panitia IMNI',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: AppColors.amberAccent,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  GlassCard(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 14,
                    ),
                    child: Row(
                      children: [
                        _buildQuickAction(
                          icon: Icons.payments_rounded,
                          label: 'Tagihan IMNI',
                          isDark: isDark,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const PembayaranImniScreen(),
                              ),
                            );
                          },
                        ),
                        _buildQuickAction(
                          icon: Icons.shopping_cart_checkout_rounded,
                          label: 'Pengeluaran',
                          isDark: isDark,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const PengeluaranImniScreen(),
                              ),
                            );
                          },
                        ),
                        _buildQuickAction(
                          icon: Icons.fact_check_rounded,
                          label: 'Presensi IMNI',
                          isDark: isDark,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const PresensiImniScreen(),
                              ),
                            );
                          },
                        ),
                        _buildQuickAction(
                          icon: Icons.grade_rounded,
                          label: 'Nilai & Leger',
                          isDark: isDark,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const NilaiImniScreen(),
                              ),
                            );
                          },
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                ],

                const SizedBox(height: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildQuickAction({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
    required bool isDark,
  }) {
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;
    final containerBg = isDark
        ? AppColors.primaryContainerDark.withValues(alpha: 0.55)
        : AppColors.primaryContainerLight.withValues(alpha: 0.85);
    final borderColor = isDark
        ? AppColors.primaryDark.withValues(alpha: 0.22)
        : AppColors.primaryLight.withValues(alpha: 0.25);
    final textColor = isDark
        ? const Color(0xFFE2E8F0)
        : const Color(0xFF1E293B);

    return Expanded(
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () {
            HapticHelper.light();
            onTap();
          },
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 4, horizontal: 2),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 50,
                  height: 50,
                  decoration: BoxDecoration(
                    color: containerBg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: borderColor, width: 1.2),
                    boxShadow: [
                      BoxShadow(
                        color: primary.withValues(alpha: isDark ? 0.08 : 0.04),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Center(child: Icon(icon, size: 23, color: primary)),
                ),
                const SizedBox(height: 7),
                Text(
                  label,
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: textColor,
                    letterSpacing: -0.2,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPengumumanCard(
    BuildContext context,
    PengumumanItem p,
    bool isDark,
  ) {
    final isPenting = p.tipe.toLowerCase() == 'penting';
    final isKegiatan = p.tipe.toLowerCase() == 'kegiatan';
    final isLibur = p.tipe.toLowerCase() == 'libur';

    final Color badgeBg;
    final Color badgeText;
    final IconData badgeIcon;

    if (isPenting) {
      badgeBg = isDark ? const Color(0xFF3B1212) : const Color(0xFFFEE2E2);
      badgeText = AppColors.roseDanger;
      badgeIcon = Icons.error_outline_rounded;
    } else if (isKegiatan) {
      badgeBg = isDark ? const Color(0xFF0F2313) : const Color(0xFFD1FAE5);
      badgeText = isDark ? AppColors.primaryDark : const Color(0xFF059669);
      badgeIcon = Icons.event_available_rounded;
    } else if (isLibur) {
      badgeBg = isDark ? const Color(0xFF382305) : const Color(0xFFFEF3C7);
      badgeText = AppColors.amberAccent;
      badgeIcon = Icons.beach_access_rounded;
    } else {
      badgeBg = isDark ? const Color(0xFF0C243B) : const Color(0xFFE0F2FE);
      badgeText = isDark ? AppColors.skyBlueAccent : const Color(0xFF0284C7);
      badgeIcon = Icons.info_outline_rounded;
    }

    final hasPdf = p.lampiranPdfUrl != null && p.lampiranPdfUrl!.isNotEmpty;

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      onTap: () {
        HapticHelper.light();
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => DetailPengumumanScreen(pengumuman: p),
          ),
        );
      },
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 3,
                    ),
                    decoration: BoxDecoration(
                      color: badgeBg,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(badgeIcon, size: 12, color: badgeText),
                        const SizedBox(width: 4),
                        Text(
                          p.tipe,
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: badgeText,
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (hasPdf) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 7,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF3B1212)
                            : const Color(0xFFFEE2E2),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: AppColors.roseDanger.withValues(alpha: 0.3),
                        ),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.picture_as_pdf_rounded,
                            size: 11,
                            color: AppColors.roseDanger,
                          ),
                          SizedBox(width: 3),
                          Text(
                            'PDF',
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w900,
                              color: AppColors.roseDanger,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
              Text(
                p.tanggalMulai,
                style: TextStyle(
                  fontSize: 11,
                  color: isDark
                      ? const Color(0xFF8D9387)
                      : const Color(0xFF73796E),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            p.judul,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(
            p.konten,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: 12,
              height: 1.4,
              color: isDark ? const Color(0xFFCCCCCC) : const Color(0xFF4B5563),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              Text(
                'Baca Selengkapnya',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                ),
              ),
              const SizedBox(width: 2),
              Icon(
                Icons.chevron_right_rounded,
                size: 16,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
            ],
          ),
        ],
      ),
    );
  }
}
