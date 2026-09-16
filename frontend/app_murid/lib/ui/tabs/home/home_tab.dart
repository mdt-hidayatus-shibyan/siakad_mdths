import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/akademik_provider.dart';
import '../../../providers/dashboard_provider.dart';
import '../../../providers/keuangan_provider.dart';
import '../../../providers/presensi_provider.dart';
import '../../widgets/child_switcher_bar.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/glass_card.dart';
import '../akun/biodata_anak_screen.dart';
import '../akun/buku_kasus_screen.dart';
import '../keuangan/kas_ruangan_screen.dart';
import '../koperasi/koperasi_screen.dart';
import '../tabungan/tabungan_screen.dart';
import 'semua_jadwal_screen.dart';

class HomeTab extends StatelessWidget {
  final Function(int tabIndex)? onNavigateTab;

  const HomeTab({super.key, this.onNavigateTab});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final dashboard = context.watch<DashboardProvider>();
    final data = dashboard.dashboardData;
    final selectedAnak = dashboard.selectedAnak;

    if (dashboard.isLoading && data == null) {
      return Center(
        child: CircularProgressIndicator(
          color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        ),
      );
    }

    if (data == null) {
      return Scaffold(
        body: EmptyStateWidget(
          icon: Icons.error_outline_rounded,
          title: 'Data Belum Tersedia',
          subtitle: dashboard.errorMessage ?? 'Gagal memuat data dashboard.',
          action: ElevatedButton.icon(
            onPressed: () => dashboard.fetchDashboard(),
            icon: const Icon(Icons.refresh_rounded),
            label: const Text('Muat Ulang'),
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: isDark ? AppColors.surfaceDark : AppColors.surfaceLight,
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () async {
            HapticHelper.light();
            await dashboard.fetchDashboard();
            if (!context.mounted) return;
            if (dashboard.selectedAnak != null) {
              final id = dashboard.selectedAnak!.id;
              final keuangan = context.read<KeuanganProvider>();
              if (dashboard.anakList.length >= 2) {
                await Future.wait([
                  keuangan.fetchAllKeuangan(id, force: true),
                  keuangan.fetchAllChildrenTabungan(
                    dashboard.anakList.map((a) => a.id).toList(),
                    force: true,
                  ),
                ]);
              } else {
                await keuangan.fetchAllKeuangan(id, force: true);
              }
              if (!context.mounted) return;
              context.read<PresensiProvider>().fetchPresensi(id, force: true);
              context.read<AkademikProvider>().fetchAkademik(id, force: true);
            }
          },
          color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(
              parent: BouncingScrollPhysics(),
            ),
            padding: const EdgeInsets.only(bottom: 100),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top Greeting & Header
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            "Assalamu'alaikum,",
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: isDark ? Colors.white60 : Colors.black54,
                            ),
                          ),
                          Text(
                            data.wali.namaKepalaKeluarga,
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w900,
                              letterSpacing: -0.3,
                              color: isDark ? Colors.white : Colors.black87,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? AppColors.primaryDark.withValues(alpha: 0.15)
                              : AppColors.primaryLight.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: isDark
                                ? AppColors.primaryDark.withValues(alpha: 0.3)
                                : AppColors.primaryLight.withValues(alpha: 0.2),
                          ),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              Icons.calendar_month_rounded,
                              size: 13,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              data.tahunHijriyah,
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w800,
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
                ),

                // Multi-Child Switcher
                const ChildSwitcherBar(),

                // Jadwal Hari Ini / Jadwal Ujian
                _buildJadwalHariIniSection(context, dashboard, isDark),

                // Pengumuman Madrasah (Wali Murid)
                _buildPengumumanSection(context, dashboard, isDark),

                if (selectedAnak != null) ...[
                  // Kartu Profil Murid Terpilih
                  Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 6,
                    ),
                    child: GlassCard(
                      padding: const EdgeInsets.all(18),
                      borderRadius: 24,
                      child: Column(
                        children: [
                          Row(
                            children: [
                              // Avatar / Foto Murid
                              Builder(
                                builder: (_) {
                                  final fotoUrl = ApiClient.resolveImageUrl(
                                    selectedAnak.foto,
                                  );
                                  return Container(
                                    width: 54,
                                    height: 54,
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      color: isDark
                                          ? AppColors.primaryDark.withValues(
                                              alpha: 0.2,
                                            )
                                          : AppColors.primaryLight.withValues(
                                              alpha: 0.1,
                                            ),
                                      border: Border.all(
                                        color: isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight,
                                        width: 2,
                                      ),
                                      image: fotoUrl != null
                                          ? DecorationImage(
                                              image: NetworkImage(fotoUrl),
                                              fit: BoxFit.cover,
                                            )
                                          : null,
                                    ),
                                    child: fotoUrl == null
                                        ? Center(
                                            child: Text(
                                              selectedAnak
                                                      .namaLengkap
                                                      .isNotEmpty
                                                  ? selectedAnak.namaLengkap
                                                        .substring(0, 1)
                                                        .toUpperCase()
                                                  : 'M',
                                              style: TextStyle(
                                                fontSize: 22,
                                                fontWeight: FontWeight.w900,
                                                color: isDark
                                                    ? AppColors.primaryDark
                                                    : AppColors.primaryLight,
                                              ),
                                            ),
                                          )
                                        : null,
                                  );
                                },
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      selectedAnak.namaLengkap,
                                      style: TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.w900,
                                        letterSpacing: -0.3,
                                        color: isDark
                                            ? Colors.white
                                            : Colors.black87,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      'Ruangan: ${selectedAnak.ruangan ?? "-"}',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w700,
                                        color: isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight,
                                      ),
                                    ),
                                    Text(
                                      'NISM: ${selectedAnak.nism}',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w500,
                                        color: isDark
                                            ? Colors.white54
                                            : Colors.black54,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              // Tombol Detail Profil
                              IconButton(
                                onPressed: () {
                                  HapticHelper.light();
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) =>
                                          BiodataAnakScreen(anak: selectedAnak),
                                    ),
                                  );
                                },
                                icon: const Icon(
                                  Icons.arrow_forward_ios_rounded,
                                  size: 16,
                                ),
                                tooltip: 'Lihat Biodata',
                              ),
                            ],
                          ),
                          const SizedBox(height: 14),
                          const Divider(height: 1),
                          const SizedBox(height: 12),

                          // Status Kehadiran Hari Ini
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                children: [
                                  Icon(
                                    Icons.access_time_filled_rounded,
                                    size: 15,
                                    color: isDark
                                        ? Colors.white60
                                        : Colors.black54,
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    'Status Hari Ini:',
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                      color: isDark
                                          ? Colors.white70
                                          : Colors.black87,
                                    ),
                                  ),
                                ],
                              ),
                              _buildStatusBadge(
                                selectedAnak.statusHariIni,
                                isDark,
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),

                  Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 8,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
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
                              'Akses Cepat',
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
                          child: Row(
                            children: [
                              _buildQuickAction(
                                icon: Icons.credit_card_rounded,
                                label: 'Kartu SPP',
                                isDark: isDark,
                                onTap: () => onNavigateTab?.call(1),
                              ),
                              _buildQuickAction(
                                icon: Icons.account_balance_wallet_rounded,
                                label: 'Tabungan',
                                isDark: isDark,
                                onTap: () {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => const TabunganScreen(),
                                    ),
                                  );
                                },
                              ),
                              _buildQuickAction(
                                icon: Icons.meeting_room_rounded,
                                label: 'Kas Ruangan',
                                isDark: isDark,
                                onTap: () {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => const KasRuanganScreen(),
                                    ),
                                  );
                                },
                              ),
                              _buildQuickAction(
                                icon: Icons.storefront_rounded,
                                label: 'Koperasi',
                                isDark: isDark,
                                onTap: () {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => const KoperasiScreen(),
                                    ),
                                  );
                                },
                              ),
                              Consumer<AkademikProvider>(
                                builder: (ctx, akademik, _) {
                                  final rekap = akademik.rekapPelanggaran;
                                  String? badgeText;
                                  Color? badgeColor;
                                  if (rekap != null) {
                                    if (rekap.totalPoin > 0) {
                                      badgeText =
                                          '+${rekap.totalPoinFormatted}';
                                      badgeColor = AppColors.roseDanger;
                                    } else {
                                      badgeText = '0';
                                      badgeColor = isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight;
                                    }
                                  }

                                  return _buildQuickAction(
                                    icon: Icons.warning_amber_rounded,
                                    label: 'Buku Kasus',
                                    badgeText: badgeText,
                                    badgeColor: badgeColor,
                                    isDark: isDark,
                                    onTap: () {
                                      Navigator.of(context).push(
                                        MaterialPageRoute(
                                          builder: (_) =>
                                              const BukuKasusScreen(),
                                        ),
                                      );
                                    },
                                  );
                                },
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatusBadge(String? status, bool isDark) {
    Color bg = Colors.grey.withValues(alpha: 0.15);
    Color text = Colors.grey;

    if (status == 'Hadir') {
      bg = const Color(0xFF10B981).withValues(alpha: 0.15);
      text = const Color(0xFF10B981);
    } else if (status == 'Izin') {
      bg = AppColors.skyBlueAccent.withValues(alpha: 0.15);
      text = AppColors.skyBlueAccent;
    } else if (status == 'Sakit') {
      bg = AppColors.amberAccent.withValues(alpha: 0.15);
      text = AppColors.amberAccent;
    } else if (status == 'Alpha') {
      bg = AppColors.roseDanger.withValues(alpha: 0.15);
      text = AppColors.roseDanger;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        status ?? 'Belum Ada Sesi',
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w800,
          color: text,
        ),
      ),
    );
  }

  Widget _buildQuickAction({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
    required bool isDark,
    Color? color,
    String? badgeText,
    Color? badgeColor,
  }) {
    final primary =
        color ?? (isDark ? AppColors.primaryDark : AppColors.primaryLight);
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
                Stack(
                  clipBehavior: Clip.none,
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
                            color: primary.withValues(
                              alpha: isDark ? 0.08 : 0.04,
                            ),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Center(
                        child: Icon(icon, size: 23, color: primary),
                      ),
                    ),
                    if (badgeText != null)
                      Positioned(
                        top: -4,
                        right: -4,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 5,
                            vertical: 1.5,
                          ),
                          decoration: BoxDecoration(
                            color:
                                badgeColor ??
                                (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(
                              color: isDark
                                  ? const Color(0xFF1A211A)
                                  : Colors.white,
                              width: 1.5,
                            ),
                          ),
                          child: Text(
                            badgeText,
                            style: const TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                  ],
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

  // =========================================================================
  // JADWAL PELAJARAN HARI INI & JADWAL UJIAN
  // =========================================================================
  Widget _buildJadwalHariIniSection(
    BuildContext context,
    DashboardProvider dashboard,
    bool isDark,
  ) {
    if (dashboard.selectedAnak == null) return const SizedBox.shrink();

    final jadwalAnak = dashboard.jadwalAnak;
    final isLoading = dashboard.isLoadingJadwal;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Icon(
                    jadwalAnak?.isUjian == true
                        ? Icons.edit_calendar_rounded
                        : Icons.calendar_today_rounded,
                    size: 14,
                    color: jadwalAnak?.isUjian == true
                        ? AppColors.amberAccent
                        : (isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight),
                  ),
                  const SizedBox(width: 6),
                  Text(
                    jadwalAnak?.isUjian == true
                        ? 'JADWAL UJIAN HARI INI'
                        : 'JADWAL PELAJARAN HARI INI',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 0.8,
                      color: isDark ? Colors.white60 : Colors.black54,
                    ),
                  ),
                ],
              ),
              InkWell(
                onTap: () {
                  HapticHelper.light();
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const SemuaJadwalScreen(),
                    ),
                  );
                },
                child: Text(
                  'Lihat Semua >',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),

          if (isLoading && jadwalAnak == null)
            GlassCard(
              padding: const EdgeInsets.all(20),
              borderRadius: 20,
              child: Center(
                child: SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                ),
              ),
            )
          else if (jadwalAnak?.isLibur == true)
            // Tampilan Hari Libur
            GlassCard(
              padding: const EdgeInsets.all(18),
              borderRadius: 20,
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: AppColors.amberAccent.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Icon(
                      Icons.beach_access_rounded,
                      color: AppColors.amberAccent,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Hari Ini Tidak Ada KBM',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w800,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          jadwalAnak?.keteranganLibur ??
                              'Libur Rutin Mingguan / Kalender Madrasah',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w500,
                            color: isDark ? Colors.white60 : Colors.black54,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            )
          else if (jadwalAnak?.isUjian == true)
            // Tampilan Mode Ujian
            GlassCard(
              padding: const EdgeInsets.all(18),
              borderRadius: 20,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: AppColors.roseDanger.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          Icons.timer_outlined,
                          size: 13,
                          color: AppColors.roseDanger,
                        ),
                        const SizedBox(width: 5),
                        Text(
                          jadwalAnak?.namaUjian ?? 'Sesi Ujian Madrasah',
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w900,
                            color: AppColors.roseDanger,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                  if (jadwalAnak?.jadwalHariIni.isEmpty == true)
                    Text(
                      'Tidak ada jadwal ujian untuk ruangan ini hari ini.',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: isDark ? Colors.white54 : Colors.black54,
                      ),
                    )
                  else
                    ...jadwalAnak!.jadwalHariIni.map((j) {
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: isDark
                                ? Colors.white.withValues(alpha: 0.05)
                                : Colors.black.withValues(alpha: 0.03),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(
                              color: AppColors.roseDanger.withValues(
                                alpha: 0.2,
                              ),
                            ),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 36,
                                height: 36,
                                decoration: BoxDecoration(
                                  color: AppColors.roseDanger.withValues(
                                    alpha: 0.15,
                                  ),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(
                                  Icons.menu_book_rounded,
                                  size: 18,
                                  color: AppColors.roseDanger,
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      j.mapel,
                                      style: TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.w800,
                                        color: isDark
                                            ? Colors.white
                                            : Colors.black87,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      'Pengawas: ${j.ustadz}',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: isDark
                                            ? Colors.white54
                                            : Colors.black54,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? Colors.white10
                                      : Colors.black.withValues(alpha: 0.05),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  j.waktu,
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    color: isDark
                                        ? Colors.white70
                                        : Colors.black87,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    }),
                ],
              ),
            )
          else
            // Tampilan Jadwal KBM Reguler Hari Ini
            GlassCard(
              padding: const EdgeInsets.all(16),
              borderRadius: 20,
              child: jadwalAnak?.jadwalHariIni.isEmpty == true
                  ? Row(
                      children: [
                        Icon(
                          Icons.info_outline_rounded,
                          size: 20,
                          color: isDark ? Colors.white38 : Colors.black38,
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Tidak ada jam pelajaran untuk hari ${jadwalAnak?.hariIni ?? "ini"}.',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                              color: isDark ? Colors.white54 : Colors.black54,
                            ),
                          ),
                        ),
                      ],
                    )
                  : Column(
                      children: [
                        ...?jadwalAnak?.jadwalHariIni.asMap().entries.map((
                          entry,
                        ) {
                          final idx = entry.key;
                          final j = entry.value;
                          final isLast =
                              idx == jadwalAnak.jadwalHariIni.length - 1;

                          return Column(
                            children: [
                              Row(
                                children: [
                                  Container(
                                    width: 36,
                                    height: 36,
                                    decoration: BoxDecoration(
                                      color:
                                          (isDark
                                                  ? AppColors.primaryDark
                                                  : AppColors.primaryLight)
                                              .withValues(alpha: 0.12),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Icon(
                                      Icons.menu_book_rounded,
                                      size: 18,
                                      color: isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight,
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          j.mapel,
                                          style: TextStyle(
                                            fontSize: 13,
                                            fontWeight: FontWeight.w800,
                                            color: isDark
                                                ? Colors.white
                                                : Colors.black87,
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          '${j.jamKe ?? "Pelajaran"} • ${j.ustadz}',
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w500,
                                            color: isDark
                                                ? Colors.white54
                                                : Colors.black54,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 8,
                                      vertical: 4,
                                    ),
                                    decoration: BoxDecoration(
                                      color:
                                          (isDark
                                                  ? AppColors.primaryDark
                                                  : AppColors.primaryLight)
                                              .withValues(alpha: 0.1),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      j.waktu,
                                      style: TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.w800,
                                        color: isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              if (!isLast)
                                const Divider(height: 16, indent: 48),
                            ],
                          );
                        }),
                      ],
                    ),
            ),
          const SizedBox(height: 10),
          // Tombol Lihat Semua Jadwal
          InkWell(
            onTap: () {
              HapticHelper.light();
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const SemuaJadwalScreen()),
              );
            },
            borderRadius: BorderRadius.circular(16),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
              decoration: BoxDecoration(
                color: (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                    .withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color:
                      (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                          .withValues(alpha: 0.25),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.calendar_month_rounded,
                    size: 16,
                    color: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Lihat Semua Jadwal Pelajaran (Mingguan)',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                  ),
                  const SizedBox(width: 6),
                  Icon(
                    Icons.arrow_forward_ios_rounded,
                    size: 12,
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
    );
  }

  // =========================================================================
  // PENGUMUMAN MADRASAH (UNTUK WALI MURID)
  // =========================================================================
  Widget _buildPengumumanSection(
    BuildContext context,
    DashboardProvider dashboard,
    bool isDark,
  ) {
    final list = dashboard.pengumumanList;
    if (list.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.campaign_rounded,
                size: 16,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
              const SizedBox(width: 6),
              Text(
                'PENGUMUMAN MADRASAH',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 0.8,
                  color: isDark ? Colors.white60 : Colors.black54,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          ...list.map((p) {
            final tipe = p['tipe']?.toString() ?? 'Informasi';
            Color badgeColor = AppColors.skyBlueAccent;
            if (tipe == 'Penting') badgeColor = AppColors.roseDanger;
            if (tipe == 'Kegiatan') badgeColor = const Color(0xFF10B981);
            if (tipe == 'Libur') badgeColor = AppColors.amberAccent;

            final hasPdf =
                p['lampiran_pdf_url'] != null &&
                p['lampiran_pdf_url'].toString().isNotEmpty;

            return Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: InkWell(
                onTap: () {
                  HapticHelper.light();
                  _showPengumumanDetailSheet(context, p, isDark);
                },
                borderRadius: BorderRadius.circular(20),
                child: GlassCard(
                  padding: const EdgeInsets.all(16),
                  borderRadius: 20,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: badgeColor.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Text(
                              tipe,
                              style: TextStyle(
                                fontSize: 9,
                                fontWeight: FontWeight.w900,
                                color: badgeColor,
                              ),
                            ),
                          ),
                          Row(
                            children: [
                              Icon(
                                Icons.event_note_rounded,
                                size: 12,
                                color: isDark ? Colors.white38 : Colors.black38,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                p['tanggal_mulai']?.toString() ?? '',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w600,
                                  color: isDark
                                      ? Colors.white38
                                      : Colors.black38,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        p['judul']?.toString() ?? 'Pengumuman Resmi',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: isDark ? Colors.white : Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        p['konten']?.toString() ?? '-',
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                          color: isDark ? Colors.white60 : Colors.black54,
                        ),
                      ),
                      if (hasPdf) ...[
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: AppColors.roseDanger.withValues(
                                  alpha: 0.1,
                                ),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.picture_as_pdf_rounded,
                                    size: 13,
                                    color: AppColors.roseDanger,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    p['nama_file_pdf']?.toString() ??
                                        'Lampiran PDF',
                                    style: const TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w700,
                                      color: AppColors.roseDanger,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            );
          }),
        ],
      ),
    );
  }

  void _showPengumumanDetailSheet(
    BuildContext context,
    Map<String, dynamic> p,
    bool isDark,
  ) {
    final tipe = p['tipe']?.toString() ?? 'Informasi';
    Color badgeColor = AppColors.skyBlueAccent;
    if (tipe == 'Penting') badgeColor = AppColors.roseDanger;
    if (tipe == 'Kegiatan') badgeColor = const Color(0xFF10B981);
    if (tipe == 'Libur') badgeColor = AppColors.amberAccent;

    final pdfUrl = p['lampiran_pdf_url']?.toString();
    final hasPdf = pdfUrl != null && pdfUrl.isNotEmpty;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(context).size.height * 0.85,
          ),
          decoration: BoxDecoration(
            color: isDark ? AppColors.surfaceContainerDark : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(32)),
          ),
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 44,
                  height: 4,
                  decoration: BoxDecoration(
                    color: isDark ? Colors.white24 : Colors.black12,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: badgeColor.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      tipe,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w900,
                        color: badgeColor,
                      ),
                    ),
                  ),
                  Text(
                    'Terbit: ${p["tanggal_mulai"] ?? "-"}',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: isDark ? Colors.white54 : Colors.black54,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                p['judul']?.toString() ?? 'Pengumuman Resmi',
                style: TextStyle(
                  fontSize: 17,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.3,
                  color: isDark ? Colors.white : Colors.black87,
                ),
              ),
              const SizedBox(height: 12),
              const Divider(height: 1),
              const SizedBox(height: 12),
              Expanded(
                child: SingleChildScrollView(
                  physics: const BouncingScrollPhysics(),
                  child: Text(
                    p['konten']?.toString() ?? '',
                    style: TextStyle(
                      fontSize: 13,
                      height: 1.6,
                      fontWeight: FontWeight.w500,
                      color: isDark ? Colors.white70 : Colors.black87,
                    ),
                  ),
                ),
              ),
              if (hasPdf) ...[
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton.icon(
                    onPressed: () => _openUrl(pdfUrl, context),
                    icon: const Icon(Icons.picture_as_pdf_rounded, size: 18),
                    label: const Text('Buka / Unduh Lampiran Dokumen PDF'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.roseDanger,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                  ),
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  Future<void> _openUrl(String? urlString, BuildContext context) async {
    if (urlString == null || urlString.isEmpty) return;
    HapticHelper.light();
    final uri = Uri.parse(urlString);
    try {
      if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Tidak dapat membuka tautan PDF.'),
              backgroundColor: AppColors.roseDanger,
            ),
          );
        }
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal membuka file: $e'),
            backgroundColor: AppColors.roseDanger,
          ),
        );
      }
    }
  }
}
