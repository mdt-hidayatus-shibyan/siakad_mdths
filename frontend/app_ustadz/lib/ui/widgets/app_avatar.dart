import 'package:flutter/material.dart';
import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';

class AppAvatar extends StatelessWidget {
  final String? imageUrl;
  final String name;
  final double radius;
  final Color? backgroundColor;
  final Color? textColor;
  final Border? border;
  final int? cacheDimension;
  final BoxShape shape;
  final BorderRadius? borderRadius;
  final BoxFit fit;
  final Alignment alignment;
  final VoidCallback? onTap;

  const AppAvatar({
    super.key,
    this.imageUrl,
    required this.name,
    this.radius = 20,
    this.backgroundColor,
    this.textColor,
    this.border,
    this.cacheDimension,
    this.shape = BoxShape.circle,
    this.borderRadius,
    this.fit = BoxFit.cover,
    this.alignment = Alignment.topCenter,
    this.onTap,
  });

  String get _initial {
    final trimmed = name.trim();
    if (trimmed.isEmpty) return '?';
    return trimmed[0].toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final resolvedUrl = ApiClient.resolveImageUrl(imageUrl);
    final size = radius * 2;
    final int memCacheSize =
        cacheDimension ?? (size * 2.5).toInt().clamp(60, 400);

    final defaultBg = isDark
        ? const Color(0xFF0F2313)
        : AppColors.primaryContainerLight;
    final defaultTextColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;

    Widget fallbackChild = Center(
      child: Text(
        _initial,
        style: TextStyle(
          fontSize: radius * 0.85,
          fontWeight: FontWeight.bold,
          color: textColor ?? defaultTextColor,
        ),
      ),
    );

    Widget avatarContent;

    if (resolvedUrl != null && resolvedUrl.isNotEmpty) {
      avatarContent = Image.network(
        resolvedUrl,
        width: size,
        height: size,
        fit: fit,
        alignment: alignment,
        cacheWidth: memCacheSize,
        cacheHeight: memCacheSize,
        errorBuilder: (context, error, stackTrace) => fallbackChild,
        loadingBuilder: (context, child, loadingProgress) {
          if (loadingProgress == null) return child;
          return Center(
            child: SizedBox(
              width: radius * 0.8,
              height: radius * 0.8,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
                value: loadingProgress.expectedTotalBytes != null
                    ? loadingProgress.cumulativeBytesLoaded /
                          loadingProgress.expectedTotalBytes!
                    : null,
              ),
            ),
          );
        },
      );
    } else {
      avatarContent = fallbackChild;
    }

    final avatarBox = Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: backgroundColor ?? defaultBg,
        shape: shape,
        borderRadius: shape == BoxShape.circle
            ? null
            : (borderRadius ?? BorderRadius.circular(radius * 0.5)),
        border: border,
      ),
      clipBehavior: Clip.antiAlias,
      alignment: Alignment.center,
      child: avatarContent,
    );

    if (onTap != null) {
      return GestureDetector(onTap: onTap, child: avatarBox);
    }

    return avatarBox;
  }
}
