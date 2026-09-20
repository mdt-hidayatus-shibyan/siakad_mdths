import 'dart:ui';
import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';

class GlassCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry margin;
  final double borderRadius;
  final VoidCallback? onTap;
  final Color? customBorderColor;
  final Color? customBgColor;

  const GlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
    this.margin = EdgeInsets.zero,
    this.borderRadius = 24,
    this.onTap,
    this.customBorderColor,
    this.customBgColor,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final borderColor =
        customBorderColor ??
        (isDark ? AppColors.outlineDark : AppColors.outlineLight);
    final bgColor =
        customBgColor ??
        (isDark ? AppColors.cardGlassDark : AppColors.cardGlassLight);

    Widget content = ClipRRect(
      borderRadius: BorderRadius.circular(borderRadius),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 16, sigmaY: 16),
        child: Container(
          padding: padding,
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(borderRadius),
            border: Border.all(color: borderColor, width: 1),
          ),
          child: child,
        ),
      ),
    );

    if (onTap != null) {
      content = InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(borderRadius),
        child: content,
      );
    }

    if (margin != EdgeInsets.zero) {
      content = Padding(padding: margin, child: content);
    }

    return content;
  }
}
