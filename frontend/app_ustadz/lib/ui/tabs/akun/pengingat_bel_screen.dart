import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/bell_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';

class PengingatBelScreen extends StatelessWidget {
  const PengingatBelScreen({super.key});

  static const List<Map<String, dynamic>> _daysConfig = [
    {'day': 1, 'name': 'Senin', 'short': 'Sen'},
    {'day': 2, 'name': 'Selasa', 'short': 'Sel'},
    {'day': 3, 'name': 'Rabu', 'short': 'Rab'},
    {'day': 4, 'name': 'Kamis', 'short': 'Kam'},
    {'day': 5, 'name': 'Jumat', 'short': 'Jum'},
    {'day': 6, 'name': 'Sabtu', 'short': 'Sab'},
    {'day': 7, 'name': 'Ahad', 'short': 'Ahd'},
  ];

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Pengingat Bel Masuk'),
      body: Consumer<BellProvider>(
        builder: (context, bell, _) {
          final nextInfo = bell.nextBellInfo;

          return ListView(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            children: [
              // ===============================================================
              // 1. HERO BANNER & MASTER SWITCH
              // ===============================================================
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: bell.isEnabled
                        ? [
                            const Color(0xFF047857),
                            const Color(0xFF059669),
                            const Color(0xFF10B981),
                          ]
                        : [
                            isDark
                                ? const Color(0xFF1E293B)
                                : const Color(0xFF94A3B8),
                            isDark
                                ? const Color(0xFF334155)
                                : const Color(0xFF64748B),
                          ],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color:
                          (bell.isEnabled
                                  ? const Color(0xFF059669)
                                  : Colors.black)
                              .withValues(alpha: isDark ? 0.35 : 0.25),
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
                            bell.isEnabled
                                ? Icons.notifications_active_rounded
                                : Icons.notifications_off_rounded,
                            color: Colors.white,
                            size: 26,
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Bel Masuk Otomatis',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 3),
                              Text(
                                bell.isEnabled
                                    ? 'Notifikasi suara aktif berbunyi pada jam masuk KBM'
                                    : 'Notifikasi suara bel saat ini dinonaktifkan',
                                style: TextStyle(
                                  color: Colors.white.withValues(alpha: 0.88),
                                  fontSize: 11.5,
                                  height: 1.3,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Switch(
                          value: bell.isEnabled,
                          onChanged: (val) {
                            HapticHelper.medium();
                            bell.setEnabled(val);
                          },
                          activeThumbColor: Colors.white,
                          activeTrackColor: const Color(0xFF34D399),
                          inactiveThumbColor: Colors.white70,
                          inactiveTrackColor: Colors.white24,
                        ),
                      ],
                    ),
                    if (bell.isEnabled) ...[
                      const SizedBox(height: 14),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 8,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.18),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.2),
                            width: 1,
                          ),
                        ),
                        child: Row(
                          children: [
                            const Icon(
                              Icons.access_time_filled_rounded,
                              size: 16,
                              color: Colors.white,
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                nextInfo['text'] ??
                                    'Menunggu jadwal berikutnya',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // ===============================================================
              // 2. JADWAL BEL MASUK KBM (JAM 1 & JAM 2)
              // ===============================================================
              const Text(
                'Jadwal Bel Masuk KBM',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),

              // KARTU JAM PERTAMA (13:45)
              _buildScheduleCard(
                context: context,
                isDark: isDark,
                jamNumber: 1,
                title: 'Jam Pertama (Jam Ke-1)',
                subtitle: 'Bel awal masuk sesi Kegiatan Belajar Mengajar',
                timeFormatted: bell.jam1Formatted,
                timeOfDay: bell.jam1Time,
                badgeColor: const Color(0xFF0284C7),
                icon: Icons.looks_one_rounded,
                onTimeChanged: (newTime) => bell.setJam1Time(newTime),
                onTestPlay: () => bell.testPlay(),
                isPlaying: bell.isPlaying,
              ),
              const SizedBox(height: 10),

              // KARTU JAM KEDUA (15:30)
              _buildScheduleCard(
                context: context,
                isDark: isDark,
                jamNumber: 2,
                title: 'Jam Kedua (Jam Ke-2)',
                subtitle: 'Bel masuk sesi kedua setelah istirahat / jeda',
                timeFormatted: bell.jam2Formatted,
                timeOfDay: bell.jam2Time,
                badgeColor: const Color(0xFFD97706),
                icon: Icons.looks_two_rounded,
                onTimeChanged: (newTime) => bell.setJam2Time(newTime),
                onTestPlay: () => bell.testPlay(),
                isPlaying: bell.isPlaying,
              ),
              const SizedBox(height: 20),

              // ===============================================================
              // 3. KONDISI JADWAL MENGAJAR (FILTER OTOMATIS)
              // ===============================================================
              const Text(
                'Opsi Sesuai Jadwal Mengajar',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              GlassCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Hanya Bunyikan Saat Ada Jadwal Mengajar',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 3),
                              Text(
                                'Bel hanya akan berbunyi di hari & jam saat Anda memiliki jadwal mengajar di kelas.',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: isDark
                                      ? Colors.white60
                                      : Colors.black54,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Switch(
                          value: bell.onlyOnTeachingDays,
                          activeThumbColor: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                          onChanged: (val) {
                            HapticHelper.selection();
                            bell.setOnlyOnTeachingDays(val);
                          },
                        ),
                      ],
                    ),
                    if (bell.onlyOnTeachingDays) ...[
                      const SizedBox(height: 12),
                      const Divider(height: 1),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Icon(
                            Icons.event_available_rounded,
                            size: 16,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 6),
                          const Text(
                            'Jadwal Mengajar Anda Terdeteksi:',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      if (bell.teachingSchedule.isEmpty)
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: isDark
                                ? const Color(0xFF1E293B)
                                : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            'Belum ada jadwal mengajar terdeteksi. Jadwal akan otomatis tersinkronisasi dari server.',
                            style: TextStyle(
                              fontSize: 11,
                              color: isDark ? Colors.white60 : Colors.black54,
                            ),
                          ),
                        )
                      else
                        Wrap(
                          spacing: 6,
                          runSpacing: 6,
                          children: bell.teachingSchedule.entries.map((entry) {
                            final dayName = _mapWeekdayToDayName(entry.key);
                            final sessionsText = entry.value
                                .map((s) => 'Jam $s')
                                .join(' & ');
                            return Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 6,
                              ),
                              decoration: BoxDecoration(
                                color:
                                    (isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight)
                                        .withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                  color:
                                      (isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight)
                                          .withValues(alpha: 0.3),
                                ),
                              ),
                              child: Text(
                                '$dayName: $sessionsText',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // ===============================================================
              // 4. HARI AKTIF MADRASAH
              // ===============================================================
              const Text(
                'Hari Aktif Notifikasi Bel',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 4),
              Text(
                'Pilih hari saat KBM madrasah berlangsung (Standar: Sabtu s.d. Kamis)',
                style: TextStyle(
                  fontSize: 11.5,
                  color: isDark ? Colors.white60 : Colors.black54,
                ),
              ),
              const SizedBox(height: 10),
              GlassCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
                child: Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: _daysConfig.map((item) {
                    final dayInt = item['day'] as int;
                    final isSelected = bell.activeDays.contains(dayInt);

                    return FilterChip(
                      selected: isSelected,
                      label: Text(item['name'] as String),
                      labelStyle: TextStyle(
                        fontSize: 12,
                        fontWeight: isSelected
                            ? FontWeight.bold
                            : FontWeight.normal,
                        color: isSelected
                            ? Colors.white
                            : (isDark ? Colors.white70 : Colors.black87),
                      ),
                      selectedColor: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                      backgroundColor: isDark
                          ? const Color(0xFF1E293B)
                          : const Color(0xFFF1F5F9),
                      checkmarkColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                        side: BorderSide(
                          color: isSelected
                              ? Colors.transparent
                              : (isDark ? Colors.white12 : Colors.black12),
                        ),
                      ),
                      onSelected: (selected) {
                        HapticHelper.selection();
                        bell.toggleActiveDay(dayInt);
                      },
                    );
                  }).toList(),
                ),
              ),
              const SizedBox(height: 20),

              // ===============================================================
              // 4. PENGATURAN NADA SUARA & VOLUME
              // ===============================================================
              const Text(
                'Pengaturan Suara & Volume',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              GlassCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Info Suara Bel
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color:
                                (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                    .withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(
                            Icons.notifications_active_rounded,
                            size: 20,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'School Bell (school-bell.wav)',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'Nada bel masuk berbunyi diulang 3x',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isDark
                                      ? Colors.white60
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
                            color: const Color(
                              0xFF15803D,
                            ).withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Text(
                            'Ulang 3x',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF15803D),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    const Divider(height: 1),
                    const SizedBox(height: 16),

                    // Slider Volume
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Icon(
                              bell.volume == 0
                                  ? Icons.volume_off_rounded
                                  : (bell.volume < 0.5
                                        ? Icons.volume_down_rounded
                                        : Icons.volume_up_rounded),
                              size: 18,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                            const SizedBox(width: 8),
                            const Text(
                              'Volume Suara',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                        Text(
                          '${(bell.volume * 100).toInt()}%',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                      ],
                    ),
                    Slider(
                      value: bell.volume,
                      min: 0.1,
                      max: 1.0,
                      divisions: 9,
                      activeColor: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                      onChanged: (val) {
                        bell.setVolume(val);
                      },
                    ),

                    const SizedBox(height: 8),

                    // Tombol Uji Coba Suara
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: bell.isPlaying
                              ? AppColors.roseDanger
                              : (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        onPressed: () {
                          HapticHelper.medium();
                          if (bell.isPlaying) {
                            bell.stopSound();
                          } else {
                            bell.testPlay();
                          }
                        },
                        icon: Icon(
                          bell.isPlaying
                              ? Icons.stop_rounded
                              : Icons.play_arrow_rounded,
                          size: 20,
                        ),
                        label: Text(
                          bell.isPlaying
                              ? 'Hentikan Suara Bel'
                              : 'Uji Coba Suara Bel Sekarang',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // ===============================================================
              // 5. TOMBOL RESET PENGATURAN
              // ===============================================================
              Center(
                child: TextButton.icon(
                  style: TextButton.styleFrom(
                    foregroundColor: isDark ? Colors.white60 : Colors.black54,
                  ),
                  onPressed: () async {
                    HapticHelper.warning();
                    final confirm = await showDialog<bool>(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('Reset Pengaturan Bel?'),
                        content: const Text(
                          'Jadwal bel akan dikembalikan ke jam standar:\n• Jam 1: 13:45 WIB\n• Jam 2: 15:30 WIB\n• Hari: Sabtu s.d. Kamis',
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx, false),
                            child: const Text('Batal'),
                          ),
                          ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.roseDanger,
                              foregroundColor: Colors.white,
                            ),
                            onPressed: () => Navigator.pop(ctx, true),
                            child: const Text('Reset'),
                          ),
                        ],
                      ),
                    );

                    if (confirm == true && context.mounted) {
                      await bell.resetToDefault();
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text(
                              'Pengaturan bel berhasil di-reset ke jadwal standar (13:45 & 15:30)',
                            ),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      }
                    }
                  },
                  icon: const Icon(Icons.restore_rounded, size: 16),
                  label: const Text(
                    'Kembalikan ke Jadwal Standar (13:45 & 15:30)',
                    style: TextStyle(fontSize: 12),
                  ),
                ),
              ),
              const SizedBox(height: 20),
            ],
          );
        },
      ),
    );
  }

  Widget _buildScheduleCard({
    required BuildContext context,
    required bool isDark,
    required int jamNumber,
    required String title,
    required String subtitle,
    required String timeFormatted,
    required TimeOfDay timeOfDay,
    required Color badgeColor,
    required IconData icon,
    required Function(TimeOfDay) onTimeChanged,
    required VoidCallback onTestPlay,
    required bool isPlaying,
  }) {
    return GlassCard(
      padding: const EdgeInsets.all(14),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: badgeColor.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(icon, color: badgeColor, size: 28),
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
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: badgeColor.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    timeFormatted,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: badgeColor,
                      fontFamily: 'monospace',
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Column(
            children: [
              IconButton(
                tooltip: 'Ubah Waktu',
                style: IconButton.styleFrom(
                  backgroundColor: isDark
                      ? const Color(0xFF1E293B)
                      : const Color(0xFFF1F5F9),
                  padding: const EdgeInsets.all(8),
                ),
                icon: const Icon(Icons.edit_calendar_rounded, size: 18),
                onPressed: () async {
                  HapticHelper.light();
                  final picked = await showTimePicker(
                    context: context,
                    initialTime: timeOfDay,
                  );
                  if (picked != null) {
                    HapticHelper.confirmSuccess();
                    onTimeChanged(picked);
                  }
                },
              ),
              const SizedBox(height: 4),
              IconButton(
                tooltip: 'Uji Suara',
                style: IconButton.styleFrom(
                  backgroundColor: badgeColor.withValues(alpha: 0.15),
                  padding: const EdgeInsets.all(8),
                ),
                icon: Icon(
                  Icons.volume_up_rounded,
                  size: 18,
                  color: badgeColor,
                ),
                onPressed: () {
                  HapticHelper.selection();
                  onTestPlay();
                },
              ),
            ],
          ),
        ],
      ),
    );
  }

  String _mapWeekdayToDayName(int weekday) {
    switch (weekday) {
      case 1:
        return 'Senin';
      case 2:
        return 'Selasa';
      case 3:
        return 'Rabu';
      case 4:
        return 'Kamis';
      case 5:
        return 'Jumat';
      case 6:
        return 'Sabtu';
      case 7:
        return 'Ahad';
      default:
        return 'Hari';
    }
  }
}
