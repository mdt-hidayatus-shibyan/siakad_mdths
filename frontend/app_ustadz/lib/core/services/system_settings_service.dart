import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';

class AppPermissionsStatus {
  final bool notifications;
  final bool exactAlarm;
  final bool batteryIgnored;

  const AppPermissionsStatus({
    required this.notifications,
    required this.exactAlarm,
    required this.batteryIgnored,
  });

  bool get allGranted => notifications && exactAlarm && batteryIgnored;
}

/// Service untuk membuka layar pengaturan sistem dan request dialog izin native
class SystemSettingsService {
  SystemSettingsService._();

  static const MethodChannel _channel = MethodChannel(
    'com.mdthidayatusshibyan.app/settings',
  );

  /// Cek status izin sistem (Notifikasi, Exact Alarm, Battery Optimization)
  static Future<AppPermissionsStatus> checkPermissionsStatus() async {
    if (kIsWeb) {
      return const AppPermissionsStatus(
        notifications: true,
        exactAlarm: true,
        batteryIgnored: true,
      );
    }
    try {
      final res = await _channel.invokeMapMethod<String, dynamic>(
        'checkPermissionsStatus',
      );
      if (res != null) {
        return AppPermissionsStatus(
          notifications: (res['notifications'] as bool?) ?? true,
          exactAlarm: (res['exactAlarm'] as bool?) ?? true,
          batteryIgnored: (res['batteryIgnored'] as bool?) ?? true,
        );
      }
    } catch (e) {
      debugPrint('Error checkPermissionsStatus: $e');
    }
    return const AppPermissionsStatus(
      notifications: true,
      exactAlarm: true,
      batteryIgnored: true,
    );
  }

  /// Memunculkan POPUP DIALOG NATIVE sistem untuk membebaskan baterai (1-Tap Approve)
  static Future<bool> requestBatteryExemption() async {
    if (kIsWeb) return true;
    try {
      final result = await _channel.invokeMethod<bool>(
        'requestBatteryExemption',
      );
      return result ?? false;
    } catch (e) {
      debugPrint('Error requestBatteryExemption: $e');
      return await openBatterySettings();
    }
  }

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

  /// Putar suara bel sekolah menggunakan Native Android MediaPlayer (Alarm Stream Audio)
  static Future<bool> playNativeAlarmSound({int repeats = 3}) async {
    if (kIsWeb) return false;
    try {
      final result = await _channel.invokeMethod<bool>('playNativeAlarmSound', {
        'repeats': repeats,
      });
      return result ?? false;
    } catch (e) {
      debugPrint('Error playNativeAlarmSound: $e');
      return false;
    }
  }

  /// Hentikan pemutaran suara bel native
  static Future<bool> stopNativeAlarmSound() async {
    if (kIsWeb) return false;
    try {
      final result = await _channel.invokeMethod<bool>('stopNativeAlarmSound');
      return result ?? false;
    } catch (e) {
      debugPrint('Error stopNativeAlarmSound: $e');
      return false;
    }
  }
}
