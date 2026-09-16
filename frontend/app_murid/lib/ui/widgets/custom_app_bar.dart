import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';

/// Reusable Circular Icon Button for AppBars and Actions
class CircularIconButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback? onPressed;
  final double size;
  final double iconSize;
  final String? tooltip;
  final Color? iconColor;
  final Color? backgroundColor;

  const CircularIconButton({
    super.key,
    required this.icon,
    this.onPressed,
    this.size = 40,
    this.iconSize = 22,
    this.tooltip,
    this.iconColor,
    this.backgroundColor,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    Widget button = Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onPressed,
        customBorder: const CircleBorder(),
        child: Container(
          width: size,
          height: size,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color:
                backgroundColor ??
                (isDark ? const Color(0xFF1A211A) : Colors.white),
            border: Border.all(
              color: isDark ? AppColors.outlineDark : const Color(0xFFE2E8F0),
              width: 1,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: isDark ? 0.35 : 0.06),
                blurRadius: 10,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Center(
            child: Icon(
              icon,
              size: iconSize,
              color:
                  iconColor ??
                  (isDark ? Colors.white : const Color(0xFF1E293B)),
            ),
          ),
        ),
      ),
    );

    if (tooltip != null) {
      button = Tooltip(message: tooltip!, child: button);
    }

    return button;
  }
}

/// Floating modern AppBar with circular back and action buttons & centered title
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
  });

  @override
  Size get preferredSize =>
      Size.fromHeight((bottom?.preferredSize.height ?? 0) + kToolbarHeight);

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final canPop = Navigator.canPop(context);

    Widget? leadingWidget = leading;
    if (leadingWidget == null && showBackButton && canPop) {
      leadingWidget = Padding(
        padding: const EdgeInsets.only(left: 16),
        child: Center(
          child: CircularIconButton(
            icon: Icons.chevron_left_rounded,
            iconSize: 26,
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
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              letterSpacing: -0.3,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          if (subtitleText != null && subtitleText!.isNotEmpty) ...[
            const SizedBox(height: 2),
            Text(
              subtitleText!,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w500,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ],
      );
    }

    return AppBar(
      backgroundColor: backgroundColor ?? Colors.transparent,
      elevation: 0,
      scrolledUnderElevation: 0,
      centerTitle: centerTitle,
      leadingWidth: 58,
      leading: leadingWidget,
      title: titleWidget,
      actions: actions != null
          ? [
              ...actions!.map(
                (action) => Padding(
                  padding: const EdgeInsets.only(right: 12),
                  child: Center(child: action),
                ),
              ),
              const SizedBox(width: 4),
            ]
          : null,
      bottom: bottom,
    );
  }
}
