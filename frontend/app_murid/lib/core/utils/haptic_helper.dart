import 'package:flutter/services.dart';

/// Pixel LRA Actuator High-Precision Haptics Helper
class HapticHelper {
  HapticHelper._();

  // Segment Tick: crisp click when tapping chips (H/S/I/A/D) or switching tabs
  static void segmentTick() {
    HapticFeedback.selectionClick();
  }

  static void selection() {
    HapticFeedback.selectionClick();
  }

  // Light Tap
  static void light() {
    HapticFeedback.lightImpact();
  }

  // Medium Tap for Buttons
  static void medium() {
    HapticFeedback.mediumImpact();
  }

  // Heavy Tap
  static void heavy() {
    HapticFeedback.heavyImpact();
  }

  // Confirm / Save Success: Double crisp tap
  static void confirmSuccess() async {
    HapticFeedback.mediumImpact();
    await Future.delayed(const Duration(milliseconds: 100));
    HapticFeedback.lightImpact();
  }

  // Warning / Reject: Strong buzz
  static void warning() {
    HapticFeedback.heavyImpact();
  }

  static void error() {
    HapticFeedback.vibrate();
  }
}
