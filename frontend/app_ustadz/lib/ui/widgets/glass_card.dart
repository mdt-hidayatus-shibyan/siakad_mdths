import 'package:flutter/material.dart';
import '../../core/theme/app_motion.dart';

/// Material 3 Expressive Tonal Surface Card with Tactile Press Animation
/// Backward-compatible replacement for legacy GlassCard across all 50+ screens.
class GlassCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry margin;
  final double borderRadius;
  final VoidCallback? onTap;
  final VoidCallback? onLongPress;
  final Color? customBorderColor;
  final Color? customBgColor;
  final bool enablePressAnimation;

  const GlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
    this.margin = EdgeInsets.zero,
    this.borderRadius = 20,
    this.onTap,
    this.onLongPress,
    this.customBorderColor,
    this.customBgColor,
    this.enablePressAnimation = true,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final colorScheme = theme.colorScheme;

    // Material 3 Expressive Tonal Surface Container (Google Spec)
    final effectiveBg = customBgColor ??
        (isDark
            ? colorScheme.surfaceContainer
            : colorScheme.surfaceContainerLow);

    // Google M3 cards rely on tonal color difference, not harsh outline borders
    final effectiveBorder = customBorderColor ??
        (isDark
            ? colorScheme.outlineVariant.withValues(alpha: 0.18)
            : Colors.transparent);

    final cardRadius = BorderRadius.circular(borderRadius);

    Widget cardWidget = Container(
      padding: padding,
      decoration: BoxDecoration(
        color: effectiveBg,
        borderRadius: cardRadius,
        border: effectiveBorder == Colors.transparent
            ? null
            : Border.all(color: effectiveBorder, width: 0.8),
      ),
      child: child,
    );

    if (onTap != null || onLongPress != null) {
      cardWidget = Material(
        color: Colors.transparent,
        borderRadius: cardRadius,
        child: InkWell(
          onTap: onTap,
          onLongPress: onLongPress,
          borderRadius: cardRadius,
          splashColor: colorScheme.primary.withValues(alpha: 0.10),
          highlightColor: colorScheme.primary.withValues(alpha: 0.05),
          child: cardWidget,
        ),
      );

      if (enablePressAnimation) {
        cardWidget = M3ScaleOnPress(
          onTap: onTap,
          onLongPress: onLongPress,
          pressedScale: 0.97,
          borderRadius: cardRadius,
          child: cardWidget,
        );
      }
    }

    if (margin != EdgeInsets.zero) {
      cardWidget = Padding(padding: margin, child: cardWidget);
    }

    return cardWidget;
  }
}
