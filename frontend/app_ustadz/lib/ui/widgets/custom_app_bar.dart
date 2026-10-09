import 'package:flutter/material.dart';
import '../../core/theme/app_motion.dart';
import 'signal_indicator_widget.dart';

/// Reusable Material 3 Expressive Tonal Icon Button for AppBars and Actions
class CircularIconButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback? onPressed;
  final double size;
  final double iconSize;
  final String? tooltip;
  final Color? iconColor;
  final Color? backgroundColor;
  final double borderRadius;

  const CircularIconButton({
    super.key,
    required this.icon,
    this.onPressed,
    this.size = 40,
    this.iconSize = 22,
    this.tooltip,
    this.iconColor,
    this.backgroundColor,
    this.borderRadius = 14,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final colorScheme = theme.colorScheme;

    final effectiveBg = backgroundColor ??
        (isDark
            ? colorScheme.surfaceContainerHigh
            : colorScheme.surfaceContainerHighest.withValues(alpha: 0.65));

    final effectiveIconColor = iconColor ??
        (isDark ? colorScheme.primary : colorScheme.primary);

    final buttonRadius = BorderRadius.circular(borderRadius);

    Widget button = Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onPressed,
        borderRadius: buttonRadius,
        splashColor: effectiveIconColor.withValues(alpha: 0.12),
        highlightColor: effectiveIconColor.withValues(alpha: 0.06),
        child: Container(
          width: size,
          height: size,
          decoration: BoxDecoration(
            borderRadius: buttonRadius,
            color: effectiveBg,
          ),
          child: Center(
            child: Icon(
              icon,
              size: iconSize,
              color: effectiveIconColor,
            ),
          ),
        ),
      ),
    );

    if (onPressed != null) {
      button = M3ScaleOnPress(
        onTap: onPressed,
        pressedScale: 0.92,
        borderRadius: buttonRadius,
        child: button,
      );
    }

    if (tooltip != null) {
      button = Tooltip(message: tooltip!, child: button);
    }

    return button;
  }
}

/// Floating modern M3 Expressive AppBar with tonal back and action buttons
class CustomAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String? titleText;
  final String? subtitleText;
  final Widget? title;
  final Widget? leading;
  final List<Widget>? actions;
  final bool showBackButton;
  final VoidCallback? onBackPressed;
  final bool centerTitle;
  final PreferredSizeWidget? bottom;
  final Color? backgroundColor;
  final bool showSignalIndicator;

  const CustomAppBar({
    super.key,
    this.titleText,
    this.subtitleText,
    this.title,
    this.leading,
    this.actions,
    this.showBackButton = true,
    this.onBackPressed,
    this.centerTitle = true,
    this.bottom,
    this.backgroundColor,
    this.showSignalIndicator = true,
  });

  @override
  Size get preferredSize =>
      Size.fromHeight((bottom?.preferredSize.height ?? 0) + kToolbarHeight);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final canPop = Navigator.canPop(context);

    Widget? leadingWidget = leading;
    if (leadingWidget == null && showBackButton && canPop) {
      leadingWidget = Padding(
        padding: const EdgeInsets.only(left: 16),
        child: Center(
          child: CircularIconButton(
            icon: Icons.arrow_back_rounded,
            iconSize: 22,
            onPressed: onBackPressed ?? () => Navigator.maybePop(context),
            tooltip: 'Kembali',
          ),
        ),
      );
    } else if (leadingWidget != null) {
      leadingWidget = Padding(
        padding: const EdgeInsets.only(left: 16),
        child: Center(child: leadingWidget),
      );
    }

    Widget? titleWidget = title;
    if (titleWidget == null && titleText != null) {
      titleWidget = Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: centerTitle
            ? CrossAxisAlignment.center
            : CrossAxisAlignment.start,
        children: [
          Text(
            titleText!,
            style: TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w700,
              letterSpacing: -0.2,
              color: isDark ? const Color(0xFFE2E3DD) : const Color(0xFF191C19),
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          if (subtitleText != null && subtitleText!.isNotEmpty) ...[
            const SizedBox(height: 1.5),
            Text(
              subtitleText!,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w500,
                color: isDark
                    ? const Color(0xFF8E918F)
                    : const Color(0xFF79747E),
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ],
      );
    }

    List<Widget>? effectiveActions;
    if (showSignalIndicator || actions != null) {
      effectiveActions = [
        if (actions != null)
          ...actions!.map(
            (action) => Padding(
              padding: const EdgeInsets.only(right: 8),
              child: Center(child: action),
            ),
          ),
        if (showSignalIndicator)
          const Padding(
            padding: EdgeInsets.only(right: 14),
            child: Center(child: SignalIndicatorWidget()),
          ),
      ];
    }

    return AppBar(
      backgroundColor: backgroundColor ?? Colors.transparent,
      elevation: 0,
      scrolledUnderElevation: 2.0,
      centerTitle: centerTitle,
      leadingWidth: 58,
      leading: leadingWidget,
      title: titleWidget,
      actions: effectiveActions,
      bottom: bottom,
    );
  }
}
