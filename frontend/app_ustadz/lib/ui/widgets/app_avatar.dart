import 'package:cached_network_image/cached_network_image.dart';
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
    this.shape = BoxShape.rectangle,
    this.borderRadius,
    this.fit = BoxFit.cover,
    this.alignment = Alignment.center,
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
      avatarContent = CachedNetworkImage(
        imageUrl: resolvedUrl,
        width: size,
        height: size,
        fit: fit,
        alignment: alignment,
        memCacheWidth: memCacheSize,
        memCacheHeight: memCacheSize,
        filterQuality: FilterQuality.medium,
        placeholder: (context, url) => Center(
          child: SizedBox(
            width: radius * 0.8,
            height: radius * 0.8,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            ),
          ),
        ),
        errorWidget: (context, url, error) => fallbackChild,
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
            : (borderRadius ?? BorderRadius.circular(radius * 0.55)),
        border: border,
      ),
      clipBehavior: Clip.antiAlias,
      alignment: Alignment.center,
      child: shape == BoxShape.circle
          ? ClipOval(
              child: SizedBox(width: size, height: size, child: avatarContent),
            )
          : ClipRRect(
              borderRadius: borderRadius ?? BorderRadius.circular(radius * 0.55),
              child: SizedBox(width: size, height: size, child: avatarContent),
            ),
    );

    if (onTap != null) {
      return GestureDetector(onTap: onTap, child: avatarBox);
    }

    return avatarBox;
  }
}
