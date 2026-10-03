import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/bell_provider.dart';
import '../../../providers/theme_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';

class PengaturanIzinScreen extends StatefulWidget {
  const PengaturanIzinScreen({super.key});

  @override
  State<PengaturanIzinScreen> createState() => _PengaturanIzinScreenState();
}

class _PengaturanIzinScreenState extends State<PengaturanIzinScreen>
    with WidgetsBindingObserver {
  bool _isRequesting = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<BellProvider>().refreshPermissionStatus();
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      context.read<BellProvider>().refreshPermissionStatus();
    }
  }

  Future<void> _handleAutoRequestAll() async {
    final bell = context.read<BellProvider>();
    final themeProvider = context.read<ThemeProvider>();
    final isDark = themeProvider.isDarkMode;
    final primary = isDark
        ? themeProvider.activePreset.primaryDark
        : themeProvider.activePreset.primaryLight;

    setState(() => _isRequesting = true);
    HapticHelper.medium();

    try {
      final status = await bell.requestAllPermissionsSeamlessly();
      if (!mounted) return;

      final messenger = ScaffoldMessenger.of(context);
      messenger.clearSnackBars();
      if (status.allGranted) {
        messenger.showSnackBar(
          SnackBar(
            content: const Row(
              children: [
                Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Alhamdulillah! Semua izin sistem berhasil diaktifkan.',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                      fontSize: 12.5,
                    ),
                  ),
                ),
              ],
            ),
            backgroundColor: primary,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        );
      } else {
        messenger.showSnackBar(
          SnackBar(
            content: const Row(
              children: [
                Icon(Icons.info_outline_rounded, color: Colors.white, size: 20),
                SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Periksa izin yang bertanda kuning di bawah untuk mengaktifkannya.',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                      fontSize: 12,
                    ),
                  ),
                ),
              ],
            ),
            backgroundColor: AppColors.amberAccent,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isRequesting = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final themeProvider = context.watch<ThemeProvider>();
    final isDark = themeProvider.isDarkMode;
    final primary = isDark
        ? themeProvider.activePreset.primaryDark
        : themeProvider.activePreset.primaryLight;
    final primaryContainer = isDark
        ? themeProvider.activePreset.primaryContainerDark
        : themeProvider.activePreset.primaryContainerLight;
    final onPrimaryContainer = isDark
        ? themeProvider.activePreset.onPrimaryContainerDark
        : themeProvider.activePreset.onPrimaryContainerLight;

    final bell = context.watch<BellProvider>();
    final status = bell.permissionStatus;

    final notifGranted = status?.notifications ?? true;
    final batteryGranted = status?.batteryIgnored ?? true;
    final exactAlarmGranted = status?.exactAlarm ?? true;
    final allGranted = notifGranted && batteryGranted && exactAlarmGranted;

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Pengaturan Izin Aplikasi'),
      body: ListView(
        padding: EdgeInsets.fromLTRB(
          16,
          12,
          16,
          40 + MediaQuery.of(context).padding.bottom,
        ),
        children: [
          // ===================================================================
          // 1. HERO CARD: AKTIVASI 1-KLIK OTOMATIS BERDASARKAN WARNA TEMA
          // ===================================================================
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: isDark
                    ? [
                        primary.withValues(alpha: 0.9),
                        primaryContainer.withValues(alpha: 0.85),
                      ]
                    : [
                        primary,
                        primary.withValues(alpha: 0.88),
                      ],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(22),
              boxShadow: [
                BoxShadow(
                  color: (isDark ? Colors.black : primary).withValues(alpha: 0.28),
                  blurRadius: 16,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        allGranted
                            ? Icons.verified_rounded
                            : Icons.bolt_rounded,
                        color: Colors.white,
                        size: 28,
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            allGranted
                                ? 'Izin Sistem Siap & Lengkap'
                                : 'Aktivasi Izin Otomatis (1 Klik)',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            allGranted
                                ? 'Bel masuk dan notifikasi dijamin berbunyi tepat waktu di semua merek HP (Samsung, Xiaomi, Oppo, Vivo, iPhone).'
                                : 'Tekan tombol di bawah untuk memberikan seluruh izin yang dibutuhkan aplikasi tanpa perlu setel manual.',
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.92),
                              fontSize: 11.5,
                              height: 1.35,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Status Chips
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    _buildStatusBadge(
                      icon: Icons.notifications_active_rounded,
                      label: 'Notifikasi',
                      isGranted: notifGranted,
                    ),
                    _buildStatusBadge(
                      icon: Icons.battery_charging_full_rounded,
                      label: 'Baterai Latar',
                      isGranted: batteryGranted,
                    ),
                    _buildStatusBadge(
                      icon: Icons.alarm_on_rounded,
                      label: 'Alarm Tepat Waktu',
                      isGranted: exactAlarmGranted,
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Tombol Eksekusi 1-Klik
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: primary,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                    onPressed: _isRequesting
                        ? null
                        : () => _handleAutoRequestAll(),
                    icon: _isRequesting
                        ? SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(
                                primary,
                              ),
                            ),
                          )
                        : Icon(
                            allGranted
                                ? Icons.refresh_rounded
                                : Icons.touch_app_rounded,
                            size: 19,
                            color: primary,
                          ),
                    label: Text(
                      _isRequesting
                          ? 'Memproses Izin...'
                          : (allGranted
                                ? 'Periksa Ulang Status Izin'
                                : '⚡ Aktifkan Semua Izin Sekarang'),
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                        color: primary,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Judul Rincian Izin
          const Text(
            'Rincian Izin Aplikasi',
            style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),

          // 1. Izin Notifikasi
          _buildPermissionCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.notifications_active_rounded,
            isGranted: notifGranted,
            title: '1. Izin Notifikasi (Push & Lock Screen)',
            subtitle: 'Untuk memunculkan bel & pengumuman di layar HP',
            description:
                'Menampilkan kartu notifikasi bel jam ke-1 dan ke-2 di layar kunci (*lockscreen*) serta pemberitahuan informasi akademik.',
            actionButton: _buildPermissionActionButton(
              context: context,
              isDark: isDark,
              primary: primary,
              isGranted: notifGranted,
              activeLabel: 'Pengaturan Notifikasi (Aktif)',
              inactiveLabel: 'Buka Pengaturan Notifikasi HP',
              activeIcon: Icons.check_circle_outline_rounded,
              inactiveIcon: Icons.notifications_outlined,
              onPressed: () => bell.openNotificationSettings(),
            ),
          ),
          const SizedBox(height: 12),

          // 2. Pembebasan Baterai (1-Tap Native Dialog)
          _buildPermissionCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.battery_charging_full_rounded,
            isGranted: batteryGranted,
            title: '2. Pembebasan Hemat Daya Baterai',
            subtitle: 'Mencegah sistem Android mematikan alarm saat HP tidur',
            description:
                'Pada HP Samsung, Xiaomi, Oppo, Realme, dan Vivo, sistem baterai sering menidurkan aplikasi latar belakang sehingga bel terlambat berbunyi.',
            actionButton: _buildPermissionActionButton(
              context: context,
              isDark: isDark,
              primary: primary,
              isGranted: batteryGranted,
              activeLabel: 'Hemat Daya Bebas (Optimal)',
              inactiveLabel: 'Bebaskan Batasan Baterai (1-Klik)',
              activeIcon: Icons.check_circle_outline_rounded,
              inactiveIcon: Icons.bolt_rounded,
              inactiveColor: AppColors.amberAccent,
              onPressed: () => bell.requestBatteryExemption(),
            ),
          ),
          const SizedBox(height: 12),

          // 3. Izin Alarm & Pengingat (Exact Alarm)
          _buildPermissionCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.alarm_on_rounded,
            isGranted: exactAlarmGranted,
            title: '3. Izin Alarm & Pengingat (Exact Alarms)',
            subtitle:
                'Wajib untuk membunyikan Bel Masuk KBM secara tepat detik',
            description:
                'Memastikan nada bel sekolah pada Jam Ke-1 (13:45) dan Jam Ke-2 (15:30) langsung memicu audio sistem seketika.',
            actionButton: _buildPermissionActionButton(
              context: context,
              isDark: isDark,
              primary: primary,
              isGranted: exactAlarmGranted,
              activeLabel: 'Izin Alarm Tepat Waktu (Aktif)',
              inactiveLabel: 'Buka Izin Alarm & Pengingat',
              activeIcon: Icons.check_circle_outline_rounded,
              inactiveIcon: Icons.settings_suggest_rounded,
              onPressed: () => bell.openExactAlarmSettings(),
            ),
          ),
          const SizedBox(height: 12),

          // 4. Mulai Otomatis (Autostart)
          _buildPermissionCard(
            context: context,
            isDark: isDark,
            primary: primary,
            icon: Icons.power_settings_new_rounded,
            title: '4. Mulai Otomatis (Autostart)',
            subtitle: 'Khusus HP Xiaomi, Oppo, Realme & Vivo',
            description:
                'Menjaga agar jadwal bel tetap otomatis aktif kembali saat HP selesai di-restart/dimatikan.',
            actionButton: OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                foregroundColor: primary,
                backgroundColor: primary.withValues(alpha: isDark ? 0.12 : 0.06),
                side: BorderSide(
                  color: primary.withValues(alpha: 0.5),
                  width: 1.1,
                ),
                padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              onPressed: () {
                HapticHelper.light();
                bell.openAppSettings();
              },
              icon: const Icon(Icons.open_in_new_rounded, size: 16),
              label: const Text(
                'Buka Setelan Info Aplikasi',
                style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold),
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Tombol Buka Pengaturan Utama Aplikasi
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: isDark
                    ? primaryContainer.withValues(alpha: 0.35)
                    : primaryContainer.withValues(alpha: 0.65),
                foregroundColor: isDark ? primary : onPrimaryContainer,
                elevation: 0,
                side: BorderSide(
                  color: primary.withValues(alpha: 0.3),
                ),
                padding: const EdgeInsets.symmetric(vertical: 13),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
              onPressed: () {
                HapticHelper.light();
                bell.openAppSettings();
              },
              icon: Icon(
                Icons.settings_rounded,
                size: 18,
                color: isDark ? primary : onPrimaryContainer,
              ),
              label: Text(
                'Buka Setelan Lengkap Aplikasi (App Info)',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 12.5,
                  color: isDark ? primary : onPrimaryContainer,
                ),
              ),
            ),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildStatusBadge({
    required IconData icon,
    required String label,
    required bool isGranted,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.18),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: isGranted
              ? Colors.white.withValues(alpha: 0.35)
              : AppColors.amberAccent.withValues(alpha: 0.7),
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            isGranted
                ? Icons.check_circle_rounded
                : Icons.error_outline_rounded,
            size: 14,
            color: isGranted ? Colors.white : AppColors.amberAccent,
          ),
          const SizedBox(width: 5),
          Text(
            '$label: ${isGranted ? "Aktif" : "Perlu Izin"}',
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.bold,
              color: isGranted ? Colors.white : AppColors.amberAccent,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPermissionCard({
    required BuildContext context,
    required bool isDark,
    required Color primary,
    required IconData icon,
    required String title,
    required String subtitle,
    required String description,
    bool? isGranted,
    Widget? actionButton,
  }) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(9),
                decoration: BoxDecoration(
                  color: (isGranted == true
                          ? (isDark ? AppColors.hadirBgDark : AppColors.hadirBgLight)
                          : AppColors.amberAccent.withValues(alpha: 0.15)),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isGranted == true
                        ? (isDark ? AppColors.hadirTextDark : const Color(0xFF86EFAC))
                            .withValues(alpha: 0.4)
                        : AppColors.amberAccent.withValues(alpha: 0.35),
                  ),
                ),
                child: Icon(
                  isGranted == true ? Icons.check_circle_rounded : icon,
                  color: isGranted == true
                      ? (isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight)
                      : AppColors.amberAccent,
                  size: 22,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            title,
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        if (isGranted != null)
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 7,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: isGranted
                                  ? (isDark ? AppColors.hadirBgDark : AppColors.hadirBgLight)
                                  : AppColors.amberAccent.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(
                                color: isGranted
                                    ? (isDark ? AppColors.hadirTextDark : const Color(0xFF86EFAC))
                                        .withValues(alpha: 0.4)
                                    : AppColors.amberAccent.withValues(alpha: 0.4),
                                width: 0.8,
                              ),
                            ),
                            child: Text(
                              isGranted ? 'Aktif' : 'Perlu Izin',
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: isGranted
                                    ? (isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight)
                                    : AppColors.amberAccent,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: TextStyle(
                        fontSize: 11,
                        color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            description,
            style: TextStyle(
              fontSize: 11.5,
              height: 1.4,
              color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF334155),
            ),
          ),
          if (actionButton != null) ...[
            const SizedBox(height: 12),
            SizedBox(width: double.infinity, child: actionButton),
          ],
        ],
      ),
    );
  }

  Widget _buildPermissionActionButton({
    required BuildContext context,
    required bool isDark,
    required Color primary,
    required bool isGranted,
    required String activeLabel,
    required String inactiveLabel,
    required IconData activeIcon,
    required IconData inactiveIcon,
    required VoidCallback onPressed,
    Color? inactiveColor,
  }) {
    if (isGranted) {
      return OutlinedButton.icon(
        style: OutlinedButton.styleFrom(
          backgroundColor: (isDark ? AppColors.hadirBgDark : AppColors.hadirBgLight).withValues(alpha: 0.5),
          foregroundColor: isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight,
          side: BorderSide(
            color: (isDark ? AppColors.hadirTextDark : const Color(0xFF86EFAC)).withValues(alpha: 0.5),
            width: 1.0,
          ),
          padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        onPressed: () {
          HapticHelper.light();
          onPressed();
        },
        icon: Icon(activeIcon, size: 16),
        label: Text(
          activeLabel,
          style: const TextStyle(
            fontSize: 11.5,
            fontWeight: FontWeight.bold,
          ),
        ),
      );
    }

    final btnColor = inactiveColor ?? primary;
    return FilledButton.icon(
      style: FilledButton.styleFrom(
        backgroundColor: btnColor,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 12),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
      onPressed: () {
        HapticHelper.light();
        onPressed();
      },
      icon: Icon(inactiveIcon, size: 16),
      label: Text(
        inactiveLabel,
        style: const TextStyle(
          fontSize: 11.5,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}
