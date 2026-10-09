import 'package:flutter/material.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/theme/app_motion.dart';
import '../../../../core/utils/haptic_helper.dart';

/// Pilihan ukuran tile menu ala Control Center (Android / iOS 18)
enum QuickMenuSize {
  compact, // 1x1: Kotak ringkas (lebar 1 unit, muat 4 per baris)
  normal,  // 2x1: Kartu horizontal standar (lebar 2 unit, muat 2 per baris)
  large,   // 3x1: Kartu lebar 3 unit (lebar 3 unit, bisa berdampingan dengan 1x1)
}

extension QuickMenuSizeExt on QuickMenuSize {
  String get code {
    switch (this) {
      case QuickMenuSize.compact:
        return 'compact';
      case QuickMenuSize.normal:
        return 'normal';
      case QuickMenuSize.large:
        return 'large';
    }
  }

  String get label {
    switch (this) {
      case QuickMenuSize.compact:
        return '1x1';
      case QuickMenuSize.normal:
        return '2x1';
      case QuickMenuSize.large:
        return '3x1';
    }
  }

  QuickMenuSize next() {
    switch (this) {
      case QuickMenuSize.compact:
        return QuickMenuSize.normal;
      case QuickMenuSize.normal:
        return QuickMenuSize.large;
      case QuickMenuSize.large:
        return QuickMenuSize.compact;
    }
  }

  /// Geser sentuhan ke kanan -> perbesar
  QuickMenuSize expand() {
    switch (this) {
      case QuickMenuSize.compact:
        return QuickMenuSize.normal;
      case QuickMenuSize.normal:
        return QuickMenuSize.large;
      case QuickMenuSize.large:
        return QuickMenuSize.large;
    }
  }

  /// Geser sentuhan ke kiri -> perkecil
  QuickMenuSize shrink() {
    switch (this) {
      case QuickMenuSize.large:
        return QuickMenuSize.normal;
      case QuickMenuSize.normal:
        return QuickMenuSize.compact;
      case QuickMenuSize.compact:
        return QuickMenuSize.compact;
    }
  }

  static QuickMenuSize fromCode(String? code) {
    switch (code) {
      case 'compact':
      case '1x1':
        return QuickMenuSize.compact;
      case 'large':
      case '3x1':
      case '4x1':
        return QuickMenuSize.large;
      case 'normal':
      case '2x1':
      default:
        return QuickMenuSize.normal;
    }
  }
}

/// Model data untuk satu item menu cepat
class QuickMenuItemData {
  final String id;
  final IconData icon;
  final String title;
  final String? shortTitle;
  final String subtitle;
  final Color accentColor;
  final VoidCallback onTap;

  const QuickMenuItemData({
    required this.id,
    required this.icon,
    required this.title,
    this.shortTitle,
    required this.subtitle,
    required this.accentColor,
    required this.onTap,
  });

  String get compactLabel => shortTitle ?? title;
}

/// Grid menu cepat kustom yang dapat diatur ukurannya per item & diatur posisinya (drag and drop)
class CustomizableMenuGrid extends StatelessWidget {
  final List<QuickMenuItemData> items;
  final Map<String, QuickMenuSize> sizes;
  final bool isEditing;
  final List<String>? pinnedIds;
  final void Function(String id, QuickMenuSize newSize) onResize;
  final VoidCallback onLongPressTile;
  final void Function(String fromId, String toId)? onReorder;
  final void Function(String id)? onTogglePin;

  const CustomizableMenuGrid({
    super.key,
    required this.items,
    required this.sizes,
    required this.isEditing,
    required this.onResize,
    required this.onLongPressTile,
    this.pinnedIds,
    this.onReorder,
    this.onTogglePin,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    const double spacing = 10.0;

    return LayoutBuilder(
      builder: (context, constraints) {
        final maxWidth = constraints.maxWidth;
        final unitWidth = (maxWidth - 3 * spacing) / 4;
        final normalWidth = (maxWidth - spacing) / 2;
        // 3x1: 3 unit lebar (lebar ~75% baris, muat 1 kartu 3x1 + 1 kartu 1x1 berdampingan)
        // Kurangi 0.05px untuk toleransi rounding float agar Wrap tidak patah baris
        final largeWidth = (3 * unitWidth + 2 * spacing) - 0.05;

        return Wrap(
          spacing: spacing,
          runSpacing: spacing,
          children: items.map((item) {
            final size = sizes[item.id] ?? QuickMenuSize.normal;
            double tileWidth;
            switch (size) {
              case QuickMenuSize.compact:
                tileWidth = unitWidth;
                break;
              case QuickMenuSize.normal:
                tileWidth = normalWidth;
                break;
              case QuickMenuSize.large:
                tileWidth = largeWidth;
                break;
            }

            // Jika dalam mode edit dan mendukung reorder, bungkus dengan DragTarget & LongPressDraggable
            if (isEditing && onReorder != null) {
              return DragTarget<String>(
                onWillAcceptWithDetails: (details) => details.data != item.id,
                onAcceptWithDetails: (details) {
                  HapticHelper.medium();
                  onReorder!(details.data, item.id);
                },
                builder: (context, candidateData, rejectedData) {
                  final isHovered = candidateData.isNotEmpty;

                  return LongPressDraggable<String>(
                    data: item.id,
                    delay: const Duration(milliseconds: 180),
                    feedback: Material(
                      color: Colors.transparent,
                      child: SizedBox(
                        width: tileWidth,
                        child: Transform.scale(
                          scale: 1.05,
                          child: Opacity(
                            opacity: 0.92,
                            child: _buildTile(
                              context: context,
                              item: item,
                              size: size,
                              isDark: isDark,
                              isHovered: false,
                            ),
                          ),
                        ),
                      ),
                    ),
                    childWhenDragging: Opacity(
                      opacity: 0.25,
                      child: SizedBox(
                        width: tileWidth,
                        child: _buildTile(
                          context: context,
                          item: item,
                          size: size,
                          isDark: isDark,
                          isHovered: false,
                        ),
                      ),
                    ),
                    child: SizedBox(
                      width: tileWidth,
                      child: _buildTile(
                        context: context,
                        item: item,
                        size: size,
                        isDark: isDark,
                        isHovered: isHovered,
                      ),
                    ),
                  );
                },
              );
            }

            // Tampilan normal
            return SizedBox(
              width: tileWidth,
              child: _buildTile(
                context: context,
                item: item,
                size: size,
                isDark: isDark,
                isHovered: false,
              ),
            );
          }).toList(),
        );
      },
    );
  }

  Widget _buildTile({
    required BuildContext context,
    required QuickMenuItemData item,
    required QuickMenuSize size,
    required bool isDark,
    required bool isHovered,
  }) {
    final theme = Theme.of(context);
    final colorScheme = theme.colorScheme;
    final isPinned = pinnedIds?.contains(item.id) ?? false;
    final iconColor = isDark
        ? (item.accentColor == AppColors.primaryLight
              ? AppColors.primaryDark
              : item.accentColor)
        : item.accentColor;

    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      // Mainkan geser sentuhan untuk ubah ukuran (swipe horizontal)
      onHorizontalDragEnd: isEditing
          ? (details) {
              final velocity = details.primaryVelocity ?? 0;
              if (velocity > 60) {
                final nextSize = size.expand();
                if (nextSize != size) {
                  HapticHelper.medium();
                  onResize(item.id, nextSize);
                }
              } else if (velocity < -60) {
                final nextSize = size.shrink();
                if (nextSize != size) {
                  HapticHelper.medium();
                  onResize(item.id, nextSize);
                }
              }
            }
          : null,
      child: M3ScaleOnPress(
        onTap: () {
          if (isEditing) {
            // Pada mode edit, sentuhan ringan dapat digunakan untuk toggle ukuran
            HapticHelper.light();
            onResize(item.id, size.next());
          } else {
            HapticHelper.light();
            item.onTap();
          }
        },
        onLongPress: () {
          if (!isEditing) {
            HapticHelper.medium();
            onLongPressTile();
          }
        },
        pressedScale: 0.94,
        borderRadius: BorderRadius.circular(20),
        child: AnimatedContainer(
          duration: AppMotion.durationShort2,
          curve: AppMotion.emphasized,
          height: 68,
          padding: size == QuickMenuSize.compact
              ? const EdgeInsets.symmetric(horizontal: 4, vertical: 6)
              : const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: isHovered
                ? item.accentColor.withValues(alpha: isDark ? 0.25 : 0.15)
                : colorScheme.surfaceContainerHigh,
            borderRadius: BorderRadius.circular(20),
            border: isHovered
                ? Border.all(color: item.accentColor, width: 2.2)
                : (isEditing
                    ? Border.all(
                        color: item.accentColor.withValues(alpha: 0.85),
                        width: 1.8,
                      )
                    : null),
            boxShadow: isHovered
                ? [
                    BoxShadow(
                      color: item.accentColor.withValues(alpha: 0.35),
                      blurRadius: 10,
                      offset: const Offset(0, 2),
                    ),
                  ]
                : (isEditing
                    ? [
                        BoxShadow(
                          color: item.accentColor.withValues(alpha: 0.15),
                          blurRadius: 6,
                          offset: const Offset(0, 2),
                        ),
                      ]
                    : null),
          ),
          child: Stack(
            clipBehavior: Clip.none,
            children: [
              // Konten sesuai ukuran - semua rapi dan seragam di dalam kartu 68dp
              if (size == QuickMenuSize.compact)
                Positioned.fill(
                  child: _buildCompactContent(item, iconColor, isDark),
                )
              else if (size == QuickMenuSize.large)
                _buildLargeContent(item, iconColor, isDark)
              else
                _buildNormalContent(item, iconColor, isDark),

              // Handle pill resize di tepi kanan vertikal saat mode edit
              if (isEditing)
                Positioned(
                  top: 0,
                  bottom: 0,
                  right: -3.5,
                  child: _buildResizeHandle(item, size),
                ),

              // Tombol Pin di pojok kiri atas saat mode edit
              if (isEditing)
                Positioned(
                  top: -4,
                  left: -4,
                  child: _buildPinButton(item, isPinned, isDark),
                )
              else if (isPinned)
                Positioned(
                  top: 0,
                  right: 0,
                  child: Icon(
                    Icons.push_pin_rounded,
                    size: 11,
                    color: item.accentColor.withValues(alpha: 0.75),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  // 1. Tampilan 1x1: Kotak Proporsional dengan Icon Squircle & Teks Singkat di Dalam Kartu
  Widget _buildCompactContent(
    QuickMenuItemData item,
    Color iconColor,
    bool isDark,
  ) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.center,
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: isDark
                  ? item.accentColor.withValues(alpha: 0.22)
                  : item.accentColor.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(11),
            ),
            child: Center(
              child: Icon(item.icon, size: 20, color: iconColor),
            ),
          ),
          const SizedBox(height: 4),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: Text(
              item.compactLabel,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 10.5,
                fontWeight: FontWeight.w700,
                letterSpacing: -0.2,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // 2. Tampilan 2x1: Ukuran Normal dengan Icon Squircle 42x42, Title & Subtitle Lengkap
  Widget _buildNormalContent(
    QuickMenuItemData item,
    Color iconColor,
    bool isDark,
  ) {
    return Row(
      children: [
        Container(
          width: 42,
          height: 42,
          decoration: BoxDecoration(
            color: isDark
                ? item.accentColor.withValues(alpha: 0.20)
                : item.accentColor.withValues(alpha: 0.14),
            borderRadius: BorderRadius.circular(13),
          ),
          child: Center(child: Icon(item.icon, size: 22, color: iconColor)),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                item.title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  letterSpacing: -0.2,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                item.subtitle,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 10.5,
                  fontWeight: FontWeight.w500,
                  color: isDark
                      ? const Color(0xFF8D9387)
                      : const Color(0xFF73796E),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  // 3. Tampilan 3x1: Kartu Hero 3 Unit dengan Icon Squircle, Title & Subtitle + Chevron
  Widget _buildLargeContent(
    QuickMenuItemData item,
    Color iconColor,
    bool isDark,
  ) {
    return Row(
      children: [
        Container(
          width: 42,
          height: 42,
          decoration: BoxDecoration(
            color: isDark
                ? item.accentColor.withValues(alpha: 0.20)
                : item.accentColor.withValues(alpha: 0.14),
            borderRadius: BorderRadius.circular(13),
          ),
          child: Center(child: Icon(item.icon, size: 22, color: iconColor)),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                item.title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  letterSpacing: -0.2,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                item.subtitle,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 10.5,
                  fontWeight: FontWeight.w500,
                  color: isDark
                      ? const Color(0xFF8D9387)
                      : const Color(0xFF73796E),
                ),
              ),
            ],
          ),
        ),
        if (!isEditing) ...[
          const SizedBox(width: 4),
          Container(
            width: 26,
            height: 26,
            decoration: BoxDecoration(
              color: item.accentColor.withValues(alpha: isDark ? 0.20 : 0.10),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Icon(
                Icons.chevron_right_rounded,
                size: 16,
                color: iconColor,
              ),
            ),
          ),
        ],
      ],
    );
  }

  // Tombol Pin untuk toggle pin menu saat mode edit
  Widget _buildPinButton(QuickMenuItemData item, bool isPinned, bool isDark) {
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: () {
        HapticHelper.medium();
        onTogglePin?.call(item.id);
      },
      child: Container(
        width: 24,
        height: 24,
        decoration: BoxDecoration(
          color: isPinned
              ? item.accentColor
              : (isDark ? const Color(0xFF2E322B) : const Color(0xFFE2E4DC)),
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.20),
              blurRadius: 4,
              offset: const Offset(0, 1),
            ),
          ],
        ),
        child: Center(
          child: Icon(
            isPinned ? Icons.push_pin_rounded : Icons.push_pin_outlined,
            size: 13,
            color: isPinned
                ? Colors.white
                : (isDark ? Colors.white70 : Colors.black87),
          ),
        ),
      ),
    );
  }

  // Handle pill resize di tepi kanan vertikal
  Widget _buildResizeHandle(QuickMenuItemData item, QuickMenuSize size) {
    return Center(
      child: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onHorizontalDragEnd: (details) {
          final velocity = details.primaryVelocity ?? 0;
          if (velocity > 30) {
            final nextSize = size.expand();
            if (nextSize != size) {
              HapticHelper.medium();
              onResize(item.id, nextSize);
            }
          } else if (velocity < -30) {
            final nextSize = size.shrink();
            if (nextSize != size) {
              HapticHelper.medium();
              onResize(item.id, nextSize);
            }
          }
        },
        onTap: () {
          HapticHelper.medium();
          onResize(item.id, size.next());
        },
        child: Container(
          width: 20, // touch area lapang
          height: 38,
          color: Colors.transparent,
          alignment: Alignment.center,
          child: Container(
            width: 6,
            height: 22,
            decoration: BoxDecoration(
              color: item.accentColor,
              borderRadius: BorderRadius.circular(10),
              boxShadow: [
                BoxShadow(
                  color: item.accentColor.withValues(alpha: 0.5),
                  blurRadius: 4,
                  offset: const Offset(0, 1),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
