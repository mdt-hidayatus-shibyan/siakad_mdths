import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/haptic_helper.dart';
import '../../providers/signal_provider.dart';

/// Minimalist High-Precision Signal Bar Status Indicator Widget for AppBars
class SignalIndicatorWidget extends StatelessWidget {
  final bool showText;
  final double size;

  const SignalIndicatorWidget({
    super.key,
    this.showText = false,
    this.size = 34,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Consumer<SignalProvider>(
      builder: (context, signal, _) {
        final color = signal.color;

        Widget content;
        if (showText) {
          content = Container(
            height: size,
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: color.withValues(alpha: isDark ? 0.18 : 0.10),
              borderRadius: BorderRadius.circular(18),
              border: Border.all(
                color: color.withValues(alpha: isDark ? 0.35 : 0.25),
                width: 1.0,
              ),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                PrecisionSignalBars(
                  quality: signal.quality,
                  color: color,
                  isDark: isDark,
                  height: 13,
                ),
                const SizedBox(width: 6),
                Text(
                  signal.latencyMs != null
                      ? '${signal.latencyMs}ms'
                      : signal.label,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white : const Color(0xFF1E293B),
                  ),
                ),
              ],
            ),
          );
        } else {
          content = Container(
            width: size,
            height: size,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: color.withValues(alpha: isDark ? 0.18 : 0.12),
              border: Border.all(
                color: color.withValues(alpha: isDark ? 0.35 : 0.22),
                width: 1.0,
              ),
            ),
            child: Center(
              child: PrecisionSignalBars(
                quality: signal.quality,
                color: color,
                isDark: isDark,
                height: 13.5,
              ),
            ),
          );
        }

        final tooltipMessage = signal.latencyMs != null
            ? '${signal.label} (${signal.latencyMs} ms) - Ketuk untuk rincian'
            : '${signal.label} - Ketuk untuk rincian';

        return Tooltip(
          message: tooltipMessage,
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: () {
                HapticHelper.light();
                SignalDetailSheet.show(context);
              },
              customBorder: showText
                  ? RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20),
                    )
                  : const CircleBorder(),
              child: content,
            ),
          ),
        );
      },
    );
  }
}

/// Precision 4-Bar Cellular Signal Indicator
class PrecisionSignalBars extends StatelessWidget {
  final SignalQuality quality;
  final Color color;
  final bool isDark;
  final double height;
  final double barWidth;
  final double spacing;

  const PrecisionSignalBars({
    super.key,
    required this.quality,
    required this.color,
    required this.isDark,
    this.height = 14,
    this.barWidth = 2.8,
    this.spacing = 1.6,
  });

  @override
  Widget build(BuildContext context) {
    int activeBars;
    switch (quality) {
      case SignalQuality.strong:
        activeBars = 4;
        break;
      case SignalQuality.moderate:
        activeBars = 3;
        break;
      case SignalQuality.weak:
        activeBars = 2;
        break;
      case SignalQuality.offline:
        activeBars = 0;
        break;
      case SignalQuality.checking:
        activeBars = 1;
        break;
    }

    final inactiveColor = isDark
        ? Colors.white.withValues(alpha: 0.16)
        : Colors.black.withValues(alpha: 0.12);

    final barHeights = [0.32, 0.54, 0.76, 1.0];

    if (quality == SignalQuality.offline) {
      return SizedBox(
        height: height,
        child: Stack(
          alignment: Alignment.center,
          children: [
            Row(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: List.generate(4, (i) {
                return Container(
                  margin: EdgeInsets.only(right: i < 3 ? spacing : 0),
                  width: barWidth,
                  height: height * barHeights[i],
                  decoration: BoxDecoration(
                    color: const Color(0xFFEF4444).withValues(alpha: 0.25),
                    borderRadius: BorderRadius.circular(barWidth / 2),
                  ),
                );
              }),
            ),
            const Icon(
              Icons.close_rounded,
              size: 13,
              color: Color(0xFFEF4444),
            ),
          ],
        ),
      );
    }

    return SizedBox(
      height: height,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: List.generate(4, (i) {
          final isActive = i < activeBars;
          return Container(
            margin: EdgeInsets.only(right: i < 3 ? spacing : 0),
            width: barWidth,
            height: height * barHeights[i],
            decoration: BoxDecoration(
              color: isActive ? color : inactiveColor,
              borderRadius: BorderRadius.circular(barWidth / 2),
            ),
          );
        }),
      ),
    );
  }
}

/// BottomSheet showing full signal telemetry, guidance, & re-test action
class SignalDetailSheet extends StatelessWidget {
  const SignalDetailSheet({super.key});

  static Future<void> show(BuildContext context) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const SignalDetailSheet(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Consumer<SignalProvider>(
      builder: (context, signal, _) {
        final color = signal.color;
        final isSafe = signal.isSafeToInput;

        String formattedTime = '-';
        if (signal.lastChecked != null) {
          final dt = signal.lastChecked!;
          final h = dt.hour.toString().padLeft(2, '0');
          final m = dt.minute.toString().padLeft(2, '0');
          final s = dt.second.toString().padLeft(2, '0');
          formattedTime = '$h:$m:$s WIB';
        }

        return Container(
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF161D16) : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
            border: Border.all(
              color: isDark ? AppColors.outlineDark : const Color(0xFFE2E8F0),
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.25),
                blurRadius: 20,
                offset: const Offset(0, -4),
              ),
            ],
          ),
          padding: EdgeInsets.fromLTRB(
            20,
            14,
            20,
            20 + MediaQuery.of(context).padding.bottom,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Handle Bar
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: isDark ? Colors.white24 : Colors.black12,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 18),

              // Sheet Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: color.withValues(alpha: isDark ? 0.20 : 0.12),
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: color.withValues(alpha: isDark ? 0.4 : 0.25),
                            width: 1.2,
                          ),
                        ),
                        child: Center(
                          child: PrecisionSignalBars(
                            quality: signal.quality,
                            color: color,
                            isDark: isDark,
                            height: 16,
                            barWidth: 3.2,
                            spacing: 1.8,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Status Koneksi & Sinyal',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            signal.label,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: color,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, size: 20),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Latency Meter Card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: isDark ? 0.08 : 0.05),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: color.withValues(alpha: 0.25)),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Kecepatan Respon (Ping):',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: color.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            signal.latencyMs != null
                                ? '${signal.latencyMs} ms'
                                : 'Terputus',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: color,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    // Visual Status Banner for Input Safety
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 10,
                      ),
                      decoration: BoxDecoration(
                        color: isSafe
                            ? const Color(0xFF10B981).withValues(alpha: 0.12)
                            : const Color(0xFFEF4444).withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isSafe
                              ? const Color(0xFF10B981).withValues(alpha: 0.3)
                              : const Color(0xFFEF4444).withValues(alpha: 0.3),
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            isSafe
                                ? Icons.check_circle_rounded
                                : Icons.warning_amber_rounded,
                            size: 20,
                            color: isSafe
                                ? const Color(0xFF10B981)
                                : const Color(0xFFEF4444),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              isSafe
                                  ? 'AMAN: Siap untuk input presensi & data'
                                  : 'PERHATIAN: Hindari input data saat ini',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isSafe
                                    ? const Color(0xFF10B981)
                                    : const Color(0xFFEF4444),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),

              // Detailed Advice Text & Last Checked
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: isDark
                      ? const Color(0xFF1F291F)
                      : const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isDark
                        ? AppColors.outlineDark
                        : const Color(0xFFE2E8F0),
                  ),
                ),
                child: Column(
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(
                          Icons.info_outline_rounded,
                          size: 18,
                          color: isDark ? Colors.white70 : Colors.black54,
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            signal.advice,
                            style: TextStyle(
                              fontSize: 12,
                              height: 1.4,
                              color: isDark ? Colors.white70 : Colors.black87,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        Icon(
                          Icons.access_time_rounded,
                          size: 13,
                          color: isDark ? Colors.white38 : Colors.black38,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          'Pemeriksaan terakhir: $formattedTime',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark ? Colors.white38 : Colors.black38,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Action Buttons
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.pop(context),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                        side: BorderSide(
                          color: isDark
                              ? AppColors.outlineDark
                              : const Color(0xFFCBD5E1),
                        ),
                      ),
                      child: const Text('Tutup'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: signal.isChecking
                          ? null
                          : () {
                              HapticHelper.medium();
                              signal.checkSignal();
                            },
                      icon: signal.isChecking
                          ? const SizedBox(
                              width: 16,
                              height: 16,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Icon(Icons.refresh_rounded, size: 18),
                      label: Text(
                        signal.isChecking ? 'Menguji...' : 'Uji Ulang Sinyal',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        elevation: 0,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}
