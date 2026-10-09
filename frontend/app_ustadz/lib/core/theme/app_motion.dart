import 'package:flutter/material.dart';

/// Material 3 Expressive (M3E) Motion Tokens & Easing Curves
/// Based on Google's latest Android 14/15 & Material Design 3 Expressive Motion System.
class AppMotion {
  AppMotion._();

  // === M3E OFFICIAL EASING CURVES ===
  /// Emphasized: Standard M3E curve for expressive movement and spatial transitions
  static const Curve emphasized = Cubic(0.2, 0.0, 0.0, 1.0);

  /// Emphasized Decelerate: Used for elements entering the viewport (organic braking)
  static const Curve emphasizedDecelerate = Cubic(0.05, 0.7, 0.1, 1.0);

  /// Emphasized Accelerate: Used for elements exiting the viewport
  static const Curve emphasizedAccelerate = Cubic(0.3, 0.0, 0.8, 0.15);

  /// Expressive Spring Rebound: Elastic bounce for micro-interactions & release
  static const Curve expressiveSpring = Curves.easeOutBack;

  /// Fluid Ease: Soft organic easing for morphing indicators
  static const Curve fluidEase = Curves.easeInOutCubic;

  // === M3E DURATION TOKENS ===
  static const Duration durationShort1 = Duration(milliseconds: 100);
  static const Duration durationShort2 = Duration(milliseconds: 150);
  static const Duration durationMedium1 = Duration(milliseconds: 220);
  static const Duration durationMedium2 = Duration(milliseconds: 320);
  static const Duration durationLong1 = Duration(milliseconds: 450);
  static const Duration durationLong2 = Duration(milliseconds: 600);
}

/// Reusable M3E Micro-Scale Press & Bounce Rebound Interaction
/// Gives cards, buttons, and tiles a tactile physical feel when tapped.
class M3ScaleOnPress extends StatefulWidget {
  final Widget child;
  final VoidCallback? onTap;
  final VoidCallback? onLongPress;
  final double pressedScale;
  final BorderRadius? borderRadius;
  final bool enableFeedback;

  const M3ScaleOnPress({
    super.key,
    required this.child,
    this.onTap,
    this.onLongPress,
    this.pressedScale = 0.965,
    this.borderRadius,
    this.enableFeedback = true,
  });

  @override
  State<M3ScaleOnPress> createState() => _M3ScaleOnPressState();
}

class _M3ScaleOnPressState extends State<M3ScaleOnPress>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: AppMotion.durationShort2,
      reverseDuration: AppMotion.durationMedium1,
    );
    _scaleAnimation = Tween<double>(
      begin: 1.0,
      end: widget.pressedScale,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: Curves.easeInOutCubic,
      reverseCurve: AppMotion.expressiveSpring,
    ));
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onTapDown(TapDownDetails details) {
    if (widget.onTap != null || widget.onLongPress != null) {
      _controller.forward();
    }
  }

  void _onTapUp(TapUpDetails details) {
    if (widget.onTap != null || widget.onLongPress != null) {
      _controller.reverse();
    }
  }

  void _onTapCancel() {
    if (widget.onTap != null || widget.onLongPress != null) {
      _controller.reverse();
    }
  }

  @override
  Widget build(BuildContext context) {
    if (widget.onTap == null && widget.onLongPress == null) {
      return widget.child;
    }

    return GestureDetector(
      onTapDown: _onTapDown,
      onTapUp: _onTapUp,
      onTapCancel: _onTapCancel,
      onTap: widget.onTap,
      onLongPress: widget.onLongPress,
      behavior: HitTestBehavior.opaque,
      child: AnimatedBuilder(
        animation: _scaleAnimation,
        builder: (context, child) => Transform.scale(
          scale: _scaleAnimation.value,
          alignment: Alignment.center,
          child: child,
        ),
        child: widget.child,
      ),
    );
  }
}

/// Reusable M3E Staggered Cascade Entrance Animation
/// Smoothly slides elements up and fades them in with an index-based delay.
class M3StaggeredFadeSlide extends StatefulWidget {
  final Widget child;
  final int index;
  final Duration delayStep;
  final double slideOffset;

  const M3StaggeredFadeSlide({
    super.key,
    required this.child,
    this.index = 0,
    this.delayStep = const Duration(milliseconds: 40),
    this.slideOffset = 20.0,
  });

  @override
  State<M3StaggeredFadeSlide> createState() => _M3StaggeredFadeSlideState();
}

class _M3StaggeredFadeSlideState extends State<M3StaggeredFadeSlide>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: AppMotion.durationMedium2,
    );

    _fadeAnimation = CurvedAnimation(
      parent: _controller,
      curve: AppMotion.emphasizedDecelerate,
    );

    _slideAnimation = Tween<Offset>(
      begin: Offset(0, widget.slideOffset),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: AppMotion.emphasizedDecelerate,
    ));

    // Stagger delay based on index (capped at max 8 items to prevent delay lag)
    final effectiveIndex = widget.index.clamp(0, 8);
    final delay = widget.delayStep * effectiveIndex;

    Future.delayed(delay, () {
      if (mounted) {
        _controller.forward();
      }
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return Opacity(
          opacity: _fadeAnimation.value,
          child: Transform.translate(
            offset: _slideAnimation.value,
            child: child,
          ),
        );
      },
      child: widget.child,
    );
  }
}

