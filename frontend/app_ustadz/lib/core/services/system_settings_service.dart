import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';

/// Service untuk membuka layar pengaturan sistem Android secara native
class SystemSettingsService {
  SystemSettingsService._();

  static const MethodChannel _channel = MethodChannel(
    'com.mdthidayatusshibyan.app/settings',
  );

  /// Buka Layar Pengaturan Info Aplikasi Utama (App Info / Detail Aplikasi)
  static Future<bool> openAppSettings() async {
    if (kIsWeb) return false;
    try {
      final result = await _channel.invokeMethod<bool>('openAppSettings');
      return result ?? false;
    } catch (e) {
      debugPrint('Error openAppSettings: $e');
      return false;
    }
  }

  /// Buka Layar Izin Alarm Presisi (Exact Alarms & Reminders Setting)
  static Future<bool> openExactAlarmSettings() async {
    if (kIsWeb) return false;
    try {
      final result = await _channel.invokeMethod<bool>(
        'openExactAlarmSettings',
      );
      return result ?? false;
    } catch (e) {
      debugPrint('Error openExactAlarmSettings: $e');
      return await openAppSettings();
    }
  }

  /// Buka Layar Pengaturan Notifikasi Aplikasi
  static Future<bool> openNotificationSettings() async {
    if (kIsWeb) return false;
    try {
      final result = await _channel.invokeMethod<bool>(
        'openNotificationSettings',
      );
      return result ?? false;
    } catch (e) {
      debugPrint('Error openNotificationSettings: $e');
      return await openAppSettings();
    }
  }

  /// Buka Layar Pengaturan Penghemat Baterai (Battery Optimization)
  static Future<bool> openBatterySettings() async {
    if (kIsWeb) return false;
    try {
      final result = await _channel.invokeMethod<bool>('openBatterySettings');
      return result ?? false;
    } catch (e) {
      debugPrint('Error openBatterySettings: $e');
      return await openAppSettings();
    }
  }
}
