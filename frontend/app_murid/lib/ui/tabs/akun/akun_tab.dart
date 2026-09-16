import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/dashboard_provider.dart';
import '../../../providers/theme_provider.dart';
import '../../widgets/child_switcher_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/modern_header.dart';
import '../../auth/login_screen.dart';
import 'biodata_anak_screen.dart';
import 'hubungi_admin_screen.dart';

class AkunTab extends StatelessWidget {
  const AkunTab({super.key});

  void _showLogoutDialog(BuildContext context) {
    HapticHelper.medium();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? AppColors.surfaceContainerDark : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Text(
          'Konfirmasi Keluar',
          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
        ),
        content: const Text(
          'Apakah Anda yakin ingin keluar dari akun Wali Murid ini?',
          style: TextStyle(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.of(ctx).pop();
              await context.read<AuthProvider>().logout();
              if (context.mounted) {
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                  (route) => false,
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.roseDanger,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            child: const Text('Ya, Keluar'),
          ),
        ],
      ),
    );
  }

  void _showUbahPinDialog(BuildContext context) {
    HapticHelper.medium();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final pinLamaCtrl = TextEditingController();
    final pinBaruCtrl = TextEditingController();
    final pinBaruKonfCtrl = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? AppColors.surfaceContainerDark : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Row(
          children: [
            Icon(
              Icons.password_rounded,
              size: 22,
              color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            ),
            const SizedBox(width: 8),
            const Text(
              'Ubah PIN Keamanan',
              style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: pinLamaCtrl,
                keyboardType: TextInputType.number,
                obscureText: true,
                maxLength: 6,
                decoration: const InputDecoration(
                  labelText: 'PIN Lama (Default: 112233)',
                  counterText: '',
                  prefixIcon: Icon(Icons.lock_outline_rounded, size: 18),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: pinBaruCtrl,
                keyboardType: TextInputType.number,
                obscureText: true,
                maxLength: 6,
                decoration: const InputDecoration(
                  labelText: 'PIN Baru (6 Digit Angka)',
                  counterText: '',
                  prefixIcon: Icon(Icons.lock_reset_rounded, size: 18),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: pinBaruKonfCtrl,
                keyboardType: TextInputType.number,
                obscureText: true,
                maxLength: 6,
                decoration: const InputDecoration(
                  labelText: 'Konfirmasi PIN Baru',
                  counterText: '',
                  prefixIcon: Icon(Icons.check_rounded, size: 18),
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              final lama = pinLamaCtrl.text.trim();
              final baru = pinBaruCtrl.text.trim();
              final konf = pinBaruKonfCtrl.text.trim();

              if (baru.length != 6 || int.tryParse(baru) == null) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('PIN baru harus terdiri dari 6 digit angka.'),
                    backgroundColor: AppColors.roseDanger,
                  ),
                );
                return;
              }

              if (baru != konf) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Konfirmasi PIN baru tidak sesuai.'),
                    backgroundColor: AppColors.roseDanger,
                  ),
                );
                return;
              }

              HapticHelper.medium();
              final res = await context.read<AuthProvider>().updatePin(
                lama,
                baru,
              );

              if (ctx.mounted) {
                Navigator.of(ctx).pop();
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(res['message'] ?? 'Berhasil mengubah PIN.'),
                    backgroundColor: res['success'] == true
                        ? const Color(0xFF10B981)
                        : AppColors.roseDanger,
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: isDark
                  ? AppColors.primaryDark
                  : AppColors.primaryLight,
              foregroundColor: isDark ? Colors.black : Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            child: const Text('Simpan PIN'),
          ),
        ],
      ),
    );
  }

  void _showThemeDialog(BuildContext context, ThemeProvider themeProvider) {
    HapticHelper.medium();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? AppColors.surfaceContainerDark : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Row(
          children: [
            Icon(
              Icons.palette_outlined,
              size: 22,
              color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            ),
            const SizedBox(width: 8),
            const Text(
              'Pilih Tema Tampilan',
              style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildThemeOption(
              ctx,
              title: 'Mode Terang',
              subtitle: 'Tema standar bersih, cerah & nyaman (Default)',
              icon: Icons.light_mode_rounded,
              selected: themeProvider.themeMode == ThemeMode.light,
              onTap: () {
                themeProvider.setThemeMode(ThemeMode.light);
                Navigator.of(ctx).pop();
              },
              isDark: isDark,
            ),
            const SizedBox(height: 8),
            _buildThemeOption(
              ctx,
              title: 'Mode Gelap (Super AMOLED)',
              subtitle: 'Latar hitam murni hemat daya layar',
              icon: Icons.dark_mode_rounded,
              selected: themeProvider.themeMode == ThemeMode.dark,
              onTap: () {
                themeProvider.setThemeMode(ThemeMode.dark);
                Navigator.of(ctx).pop();
              },
              isDark: isDark,
            ),
            const SizedBox(height: 8),
            _buildThemeOption(
              ctx,
              title: 'Ikuti Pengaturan Sistem',
              subtitle: 'Menyesuaikan mode perangkat Anda',
              icon: Icons.brightness_auto_rounded,
              selected: themeProvider.themeMode == ThemeMode.system,
              onTap: () {
                themeProvider.setThemeMode(ThemeMode.system);
                Navigator.of(ctx).pop();
              },
              isDark: isDark,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Tutup'),
          ),
        ],
      ),
    );
  }

  void _showColorPresetDialog(BuildContext context) {
    HapticHelper.medium();
    final themeProvider = context.read<ThemeProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? AppColors.surfaceContainerDark : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Row(
          children: [
            Icon(
              Icons.color_lens_rounded,
              size: 22,
              color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            ),
            const SizedBox(width: 8),
            const Text(
              'Pilih Warna Aplikasi',
              style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
            ),
          ],
        ),
        content: SizedBox(
          width: double.maxFinite,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: AppColors.presets.map((preset) {
                final isSelected = themeProvider.colorPresetKey == preset.key;
                return Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: InkWell(
                    onTap: () {
                      HapticHelper.selection();
                      themeProvider.setColorPreset(preset.key);
                      Navigator.of(ctx).pop();
                    },
                    borderRadius: BorderRadius.circular(16),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 10,
                      ),
                      decoration: BoxDecoration(
                        color: isSelected
                            ? (isDark
                                  ? preset.primaryDark.withValues(alpha: 0.2)
                                  : preset.primaryLight.withValues(alpha: 0.1))
                            : Colors.transparent,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isSelected
                              ? (isDark
                                    ? preset.primaryDark
                                    : preset.primaryLight)
                              : (isDark ? Colors.white12 : Colors.black12),
                          width: isSelected ? 1.5 : 1,
                        ),
                      ),
                      child: Row(
                        children: [
                          Container(
                            width: 28,
                            height: 28,
                            decoration: BoxDecoration(
                              color: isDark
                                  ? preset.primaryDark
                                  : preset.primaryLight,
                              shape: BoxShape.circle,
                              border: Border.all(
                                color: Colors.white.withValues(alpha: 0.5),
                                width: 2,
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color:
                                      (isDark
                                              ? preset.primaryDark
                                              : preset.primaryLight)
                                          .withValues(alpha: 0.3),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: isSelected
                                ? const Icon(
                                    Icons.check_rounded,
                                    size: 16,
                                    color: Colors.white,
                                  )
                                : null,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  preset.name,
                                  style: TextStyle(
                                    fontSize: 13,
                                    fontWeight: isSelected
                                        ? FontWeight.w900
                                        : FontWeight.w700,
                                    color: isDark
                                        ? Colors.white
                                        : Colors.black87,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  preset.subtitle,
                                  style: TextStyle(
                                    fontSize: 10,
                                    color: isDark
                                        ? Colors.white54
                                        : Colors.black54,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          if (isSelected)
                            Icon(
                              Icons.check_circle_rounded,
                              size: 18,
                              color: isDark
                                  ? preset.primaryDark
                                  : preset.primaryLight,
                            ),
                        ],
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Tutup'),
          ),
        ],
      ),
    );
  }

  Widget _buildThemeOption(
    BuildContext context, {
    required String title,
    required String subtitle,
    required IconData icon,
    required bool selected,
    required VoidCallback onTap,
    required bool isDark,
  }) {
    return InkWell(
      onTap: () {
        HapticHelper.selection();
        onTap();
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: selected
              ? (isDark
                    ? AppColors.primaryDark.withValues(alpha: 0.2)
                    : AppColors.primaryLight.withValues(alpha: 0.1))
              : Colors.transparent,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: selected
                ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                : (isDark ? Colors.white12 : Colors.black12),
            width: selected ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            Icon(
              icon,
              size: 20,
              color: selected
                  ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                  : (isDark ? Colors.white60 : Colors.black54),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: selected ? FontWeight.w900 : FontWeight.w700,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: TextStyle(
                      fontSize: 10,
                      color: isDark ? Colors.white54 : Colors.black54,
                    ),
                  ),
                ],
              ),
            ),
            if (selected)
              Icon(
                Icons.check_circle_rounded,
                size: 18,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
          ],
        ),
      ),
    );
  }

  String _getThemeSubtitle(ThemeMode mode) {
    switch (mode) {
      case ThemeMode.light:
        return 'Mode Terang (Default)';
      case ThemeMode.dark:
        return 'Mode Gelap (Super AMOLED)';
      case ThemeMode.system:
        return 'Ikuti Pengaturan Sistem';
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final auth = context.watch<AuthProvider>();
    final themeProvider = context.watch<ThemeProvider>();
    final dashboard = context.watch<DashboardProvider>();
    final wali = dashboard.dashboardData?.wali ?? auth.currentWali;
    final selectedAnak = dashboard.selectedAnak;

    return Scaffold(
      backgroundColor: isDark ? AppColors.surfaceDark : AppColors.surfaceLight,
      body: SafeArea(
        child: SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          padding: const EdgeInsets.only(bottom: 100),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                child: ModernHeader(
                  title: 'Akun & Profil',
                  subtitle: 'Informasi Wali Murid & Pengaturan',
                  icon: Icons.person_rounded,
                ),
              ),

              // Multi-Child Switcher
              const ChildSwitcherBar(),

              // Kartu Profil Wali Murid
              Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 6,
                ),
                child: GlassCard(
                  padding: const EdgeInsets.all(20),
                  borderRadius: 24,
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Container(
                            width: 56,
                            height: 56,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: isDark
                                  ? AppColors.primaryDark.withValues(alpha: 0.2)
                                  : AppColors.primaryLight.withValues(
                                      alpha: 0.1,
                                    ),
                              border: Border.all(
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                                width: 2,
                              ),
                            ),
                            child: Icon(
                              Icons.family_restroom_rounded,
                              size: 28,
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
                                  wali?.namaKepalaKeluarga ??
                                      'Wali Murid MDTHS',
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
                                  'No. Registrasi: ${wali?.noRegistrasi ?? "-"}',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w700,
                                    color: isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight,
                                  ),
                                ),
                                Text(
                                  'No. KK: ${wali?.noKk ?? "-"} • ${wali?.kampung ?? "-"}',
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
                        ],
                      ),
                      if (wali?.alamat != null && wali!.alamat!.isNotEmpty) ...[
                        const SizedBox(height: 12),
                        const Divider(height: 1),
                        const SizedBox(height: 10),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(
                              Icons.location_on_rounded,
                              size: 14,
                              color: isDark ? Colors.white54 : Colors.black54,
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                wali.alamat!,
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w500,
                                  color: isDark
                                      ? Colors.white60
                                      : Colors.black54,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 10),

              // Menu Terkait Murid Aktif
              if (selectedAnak != null) ...[
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 6,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Biodata Murid (${selectedAnak.namaLengkap})',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 6),
                      GlassCard(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 4,
                        ),
                        child: Column(
                          children: [
                            _buildMenuItem(
                              icon: Icons.badge_rounded,
                              title: 'Biodata & Identitas Murid',
                              subtitle:
                                  'Detail data murid, orang tua, dan NISM',
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                              onTap: () {
                                Navigator.of(context).push(
                                  MaterialPageRoute(
                                    builder: (_) =>
                                        BiodataAnakScreen(anak: selectedAnak),
                                  ),
                                );
                              },
                              isDark: isDark,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],

              const SizedBox(height: 10),

              // Menu Aplikasi & Pengaturan
              Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 6,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Bantuan & Layanan',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    GlassCard(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 4,
                      ),
                      child: _buildMenuItem(
                        icon: Icons.support_agent_rounded,
                        title: 'Hubungi Admin & Pusat Bantuan',
                        subtitle: 'Layanan WhatsApp hotline & bantuan madrasah',
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                        onTap: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => const HubungiAdminScreen(),
                            ),
                          );
                        },
                        isDark: isDark,
                      ),
                    ),
                    const SizedBox(height: 14),

                    const Text(
                      'Tampilan & Sistem',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    GlassCard(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 4,
                      ),
                      child: Column(
                        children: [
                          _buildMenuItem(
                            icon: Icons.brightness_6_rounded,
                            title: 'Tema Tampilan',
                            subtitle: _getThemeSubtitle(
                              themeProvider.themeMode,
                            ),
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                            onTap: () =>
                                _showThemeDialog(context, themeProvider),
                            isDark: isDark,
                          ),
                          const Divider(height: 1),
                          _buildMenuItem(
                            icon: Icons.palette_rounded,
                            title: 'Warna Aksen Aplikasi',
                            subtitle:
                                '${themeProvider.activePreset.name} • ${themeProvider.activePreset.subtitle}',
                            color: isDark
                                ? themeProvider.activePreset.primaryDark
                                : themeProvider.activePreset.primaryLight,
                            onTap: () => _showColorPresetDialog(context),
                            isDark: isDark,
                            leading: Container(
                              width: 30,
                              height: 30,
                              decoration: BoxDecoration(
                                color: isDark
                                    ? themeProvider.activePreset.primaryDark
                                    : themeProvider.activePreset.primaryLight,
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: isDark
                                      ? Colors.white24
                                      : Colors.black12,
                                  width: 1.5,
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color:
                                        (isDark
                                                ? themeProvider
                                                      .activePreset
                                                      .primaryDark
                                                : themeProvider
                                                      .activePreset
                                                      .primaryLight)
                                            .withValues(alpha: 0.25),
                                    blurRadius: 4,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: const Icon(
                                Icons.palette_rounded,
                                size: 16,
                                color: Colors.white,
                              ),
                            ),
                            trailing: Container(
                              width: 14,
                              height: 14,
                              decoration: BoxDecoration(
                                color: isDark
                                    ? themeProvider.activePreset.primaryDark
                                    : themeProvider.activePreset.primaryLight,
                                shape: BoxShape.circle,
                              ),
                            ),
                          ),
                          const Divider(height: 1),
                          _buildMenuItem(
                            icon: Icons.lock_reset_rounded,
                            title: 'Ubah PIN Keamanan',
                            subtitle: 'Ganti 6-digit PIN login akun Anda',
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                            onTap: () => _showUbahPinDialog(context),
                            isDark: isDark,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Tombol Keluar Akun bergaya app_ustadz
                    ElevatedButton.icon(
                      onPressed: () => _showLogoutDialog(context),
                      icon: const Icon(Icons.logout_rounded, size: 18),
                      label: const Text('Keluar Akun'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: isDark
                            ? const Color(0xFF3B1212)
                            : const Color(0xFFFEE2E2),
                        foregroundColor: AppColors.roseDanger,
                        elevation: 0,
                        minimumSize: const Size(double.infinity, 50),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              Center(
                child: Column(
                  children: [
                    Text(
                      '${AppConstants.appName} v${AppConstants.appVersion}',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: isDark ? Colors.white38 : Colors.black38,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'MDT Hidayatus Shibyan Somorkoneng',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w500,
                        color: isDark ? Colors.white24 : Colors.black26,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    required VoidCallback onTap,
    required bool isDark,
    Widget? leading,
    Widget? trailing,
  }) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      onTap: () {
        HapticHelper.light();
        onTap();
      },
      leading:
          leading ??
          Container(
            padding: const EdgeInsets.all(7),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 18),
          ),
      title: Text(
        title,
        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
      ),
      subtitle: Text(
        subtitle,
        style: TextStyle(
          fontSize: 11,
          color: isDark ? const Color(0xFF8D9387) : const Color(0xFF73796E),
        ),
      ),
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (trailing != null) ...[trailing, const SizedBox(width: 6)],
          const Icon(Icons.chevron_right_rounded),
        ],
      ),
    );
  }
}
