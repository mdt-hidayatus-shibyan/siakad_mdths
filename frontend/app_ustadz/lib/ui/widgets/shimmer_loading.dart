import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

/// Material 3 Expressive Tonal Wave Shimmer Skeleton
class ShimmerLoadingList extends StatelessWidget {
  final int count;
  final double height;
  final double borderRadius;

  const ShimmerLoadingList({
    super.key,
    this.count = 4,
    this.height = 90,
    this.borderRadius = 20,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final colorScheme = theme.colorScheme;

    final baseColor = isDark
        ? colorScheme.surfaceContainer
        : colorScheme.surfaceContainer;
    final highlightColor = isDark
        ? colorScheme.surfaceContainerHighest
        : colorScheme.surfaceContainerLowest;

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: count,
      itemBuilder: (context, index) {
        return Shimmer.fromColors(
          baseColor: baseColor,
          highlightColor: highlightColor,
          period: const Duration(milliseconds: 1400),
          child: Container(
            height: height,
            margin: const EdgeInsets.only(bottom: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(borderRadius),
            ),
          ),
        );
      },
    );
  }
}
