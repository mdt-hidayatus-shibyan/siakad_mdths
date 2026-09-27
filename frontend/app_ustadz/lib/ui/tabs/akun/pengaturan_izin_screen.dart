import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/bell_provider.dart';
import '../../../providers/theme_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';

class PengaturanIzinScreen extends StatelessWidget {
  const PengaturanIzinScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final themeProvider = context.watch<ThemeProvider>();
    final isDark = themeProvider.isDarkMode;
    final primary = isDark
        ? themeProvider.activePreset.primaryDark
        : themeProvider.activePreset.primaryLight;
    final bell = context.read<BellProvider>();

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Pengaturan Izin Aplikasi'),
      body: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        children: [
          // Banner Penjelasan
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: isDark
                    ? [primary.withValues(alpha: 0.7), primary]
                    : [primary, primary.withValues(alpha: 0.85)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: (isDark ? Colors.black : primary).withValues(
                    alpha: 0.25,
                  ),
                  blurRadius: 14,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.security_rounded,
                    color: Colors.white,
                    size: 26,
                  ),
                ),
                const SizedBox(width: 14),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Izin & Perizinan Sistem',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      SizedBox(height: 3),
                      Text(
                        'Daftar izin yang dibutuhkan aplikasi MDTHS Ustadz agar seluruh fitur (presensi, alarm bel, notifikasi) berjalan normal dan tepat waktu.',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 11.5,
                          height: 1.35,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // 1. Izin Alarm & Pengingat (Exact Alarm)
          _buildPermissionCard(
            context: context,
            isDark: isDark,
            icon: Icons.alarm_on_rounded,
            badgeColor: primary,
            title: '1. Izin Alarm & Pengingat (Exact Alarms)',
            subtitle: 'Wajib untuk membunyikan Bel Masuk KBM tepat waktu',
            description:
                'Izin ini digunakan untuk membunyikan nada bel sekolah pada Jam Ke-1 (13:45) dan Jam Ke-2 (15:30) secara presisi meskipun layar HP sedang terkunci/mati.\n\n• Status: Diizinkan otomatis saat instalasi aplikasi (Android 13+).\n• Opsi: Anda dapat mematikan atau menyalakan izin ini melalui tombol di bawah.',
            actionButton: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              onPressed: () {
                HapticHelper.light();
                bell.openExactAlarmSettings();
              },
              icon: const Icon(Icons.settings_suggest_rounded, size: 16),
              label: const Text(
                'Buka Izin Alarm di HP (Nyalakan / Matikan)',
                style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold),
              ),
            ),
          ),
          const SizedBox(height: 12),

          // 2. Izin Notifikasi
          _buildPermissionCard(
            context: context,
            isDark: isDark,
            icon: Icons.notifications_active_rounded,
            badgeColor: primary,
            title: '2. Izin Notifikasi (Push & Local Notifications)',
            subtitle: 'Untuk memunculkan pesan bel & pengumuman di layar HP',
            description:
                'Menampilkan pesan pengingat masuk kelas, presensi ustadz/murid di layar kunci (*lockscreen*), dan pemberitahuan penting madrasah.',
            actionButton: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              onPressed: () {
                HapticHelper.light();
                bell.openNotificationSettings();
              },
              icon: const Icon(Icons.notifications_outlined, size: 16),
              label: const Text(
                'Buka Pengaturan Notifikasi HP',
                style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold),
              ),
            ),
          ),
          const SizedBox(height: 12),

          // 3. Penghemat Baterai (Tanpa Pembatasan)
          _buildPermissionCard(
            context: context,
            isDark: isDark,
            icon: Icons.battery_saver_rounded,
            badgeColor: const Color(0xFFD97706),
            title: '3. Penghemat Baterai (Tanpa Pembatasan)',
            subtitle: 'Mencegah sistem Android mematikan alarm latar belakang',
            description:
                'Pada HP Xiaomi, Oppo, Realme, Vivo, dan Samsung, sistem Android sering mematikan alarm saat HP tidur jika penghemat baterai aktif.\n\n👉 Solusi: Buka Info Aplikasi > Penggunaan Baterai > Pilih "Tanpa Pembatasan" (No Restrictions / Unrestricted).',
            actionButton: OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFFD97706),
                side: const BorderSide(color: Color(0xFFD97706), width: 1.2),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              onPressed: () {
                HapticHelper.light();
                bell.openBatterySettings();
              },
              icon: const Icon(Icons.battery_charging_full_rounded, size: 16),
              label: const Text(
                'Atur Penghemat Baterai',
                style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold),
              ),
            ),
          ),
          const SizedBox(height: 12),

          // 4. Mulai Otomatis (Autostart)
          _buildPermissionCard(
            context: context,
            isDark: isDark,
            icon: Icons.power_settings_new_rounded,
            badgeColor: const Color(0xFF7C3AED),
            title: '4. Mulai Otomatis (Autostart / Auto-run)',
            subtitle: 'Khusus HP Xiaomi (HyperOS/MIUI), Oppo, & Vivo',
            description:
                'Mengaktifkan fitur Mulai Otomatis memastikan jadwal bel KBM langsung aktif kembali secara otomatis saat HP baru saja dihidupkan ulang (*restart*).',
            actionButton: OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFF7C3AED),
                side: const BorderSide(color: Color(0xFF7C3AED), width: 1.2),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              onPressed: () {
                HapticHelper.light();
                bell.openAppSettings();
              },
              icon: const Icon(Icons.open_in_new_rounded, size: 16),
              label: const Text(
                'Buka Info Aplikasi untuk Autostart',
                style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold),
              ),
            ),
          ),
          const SizedBox(height: 12),

          // 5. Kamera & Galeri Foto
          _buildPermissionCard(
            context: context,
            isDark: isDark,
            icon: Icons.photo_camera_rounded,
            badgeColor: const Color(0xFFEC4899),
            title: '5. Kamera & Galeri Foto (Camera & Storage)',
            subtitle: 'Untuk foto profil, lampiran surat sakit, & dokumentasi',
            description:
                'Izin ini diminta secara otomatis saat Anda mengambil foto atau memilih gambar dari galeri untuk foto profil, lampiran catatan, atau surat izin murid.',
            actionButton: OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFFEC4899),
                side: const BorderSide(color: Color(0xFFEC4899), width: 1.2),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              onPressed: () {
                HapticHelper.light();
                bell.openAppSettings();
              },
              icon: const Icon(Icons.settings_applications_rounded, size: 16),
              label: const Text(
                'Buka Info Izin Aplikasi',
                style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold),
              ),
            ),
          ),
          const SizedBox(height: 12),

          // 6. Akses Internet & Jaringan
          _buildPermissionCard(
            context: context,
            isDark: isDark,
            icon: Icons.wifi_rounded,
            badgeColor: primary,
            title: '6. Akses Jaringan Internet (Network)',
            subtitle: 'Untuk sinkronisasi online data akademik & presensi',
            description:
                'Digunakan untuk mengambil data jadwal KBM, mengirim presensi murid dan ustadz, mencatat pembayaran kas, dan menyinkronkan tabungan ke server MDTHS.',
          ),
          const SizedBox(height: 16),

          // Tombol Buka Pengaturan Utama Aplikasi
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: isDark
                    ? const Color(0xFF1E293B)
                    : const Color(0xFFE2E8F0),
                foregroundColor: isDark ? Colors.white : Colors.black87,
                elevation: 0,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
              onPressed: () {
                HapticHelper.light();
                bell.openAppSettings();
              },
              icon: const Icon(Icons.settings_rounded, size: 18),
              label: const Text(
                'Buka Setelan Lengkap Aplikasi (App Info)',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
            ),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildPermissionCard({
    required BuildContext context,
    required bool isDark,
    required IconData icon,
    required Color badgeColor,
    required String title,
    required String subtitle,
    required String description,
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
                  color: badgeColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: badgeColor, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: TextStyle(
                        fontSize: 11,
                        color: isDark ? Colors.white60 : Colors.black54,
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
}
