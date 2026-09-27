import 'dart:async';
import 'dart:convert';
import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart' show rootBundle;
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_timezone/flutter_timezone.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:timezone/data/latest_all.dart' as tz;
import 'package:timezone/timezone.dart' as tz;
import '../../data/models/akademik_model.dart';
import '../utils/haptic_helper.dart';
import 'system_settings_service.dart';

/// Model payload untuk event bel masuk
class BellEvent {
  final int jam; // 1 = Jam Pertama, 2 = Jam Kedua
  final String title;
  final String description;
  final String waktu;
  final DateTime timestamp;

  const BellEvent({
    required this.jam,
    required this.title,
    required this.description,
    required this.waktu,
    required this.timestamp,
  });
}

/// Service terpadu pengingat bel masuk KBM, alarm presisi, audio stream alarm, dan perizinan sistem.
class BellService {
  static final BellService instance = BellService._internal();
  BellService._internal();

  final AudioPlayer _player = AudioPlayer();
  final FlutterLocalNotificationsPlugin _notificationsPlugin =
      FlutterLocalNotificationsPlugin();
  Timer? _timer;

  // Preference Keys
  static const String _keyEnabled = 'bell_enabled';
  static const String _keyJam1 = 'bell_jam1';
  static const String _keyJam2 = 'bell_jam2';
  static const String _keyVolume = 'bell_volume';
  static const String _keyActiveDays = 'bell_active_days';
  static const String _keyOnlyTeachingDays = 'bell_only_teaching_days';
  static const String _keyTeachingSchedule = 'bell_teaching_schedule';

  // Android Notification Channel
  static const String _channelId = 'mdths_kbm_bell_channel_v6';
  static const String _channelName = 'Bel Masuk KBM MDTHS';
  static const String _channelDesc =
      'Notifikasi jadwal bel masuk jam pertama dan jam kedua';

  static const List<String> _dayNames = [
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
    'Ahad',
  ];

  // State
  bool _isEnabled = true;
  bool _onlyOnTeachingDays = true;
  TimeOfDay _jam1Time = const TimeOfDay(hour: 13, minute: 45);
  TimeOfDay _jam2Time = const TimeOfDay(hour: 15, minute: 30);
  double _volume = 1.0;
  List<int> _activeDays = [1, 2, 3, 4, 6, 7]; // Senin-Kamis, Sabtu-Ahad
  Map<int, List<int>> _teachingSchedule = {};

  bool _isPlaying = false;
  int _currentSession = 0;
  String? _lastTriggeredKey;
  bool _isNotificationInitialized = false;

  final _bellEventController = StreamController<BellEvent>.broadcast();
  Stream<BellEvent> get onBellEvent => _bellEventController.stream;

  final _playingStateController = StreamController<bool>.broadcast();
  Stream<bool> get onPlayingStateChanged => _playingStateController.stream;

  // Getters
  bool get isEnabled => _isEnabled;
  bool get onlyOnTeachingDays => _onlyOnTeachingDays;
  TimeOfDay get jam1Time => _jam1Time;
  TimeOfDay get jam2Time => _jam2Time;
  double get volume => _volume;
  List<int> get activeDays => List.unmodifiable(_activeDays);
  Map<int, List<int>> get teachingSchedule =>
      Map.unmodifiable(_teachingSchedule);
  bool get isPlaying => _isPlaying;

  bool hasTeachingScheduleOn(int weekday) =>
      _teachingSchedule[weekday]?.isNotEmpty ?? false;

  bool hasTeachingSessionOn(int weekday, int jam) =>
      _teachingSchedule[weekday]?.contains(jam) ?? false;

  // ===========================================================================
  // 1. PERIZINAN SISTEM (SEAMLESS 1-TAP APPROVAL)
  // ===========================================================================

  Future<AppPermissionsStatus> checkPermissionsStatus() =>
      SystemSettingsService.checkPermissionsStatus();

  Future<AppPermissionsStatus> requestAllPermissionsSeamlessly() async {
    if (kIsWeb) {
      return const AppPermissionsStatus(
        notifications: true,
        exactAlarm: true,
        batteryIgnored: true,
      );
    }

    try {
      final android = _notificationsPlugin
          .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin
          >();
      if (android != null) {
        await android.requestNotificationsPermission();
        await android.requestExactAlarmsPermission();
      }

      await SystemSettingsService.requestBatteryExemption();
      final status = await SystemSettingsService.checkPermissionsStatus();
      if (!status.exactAlarm) {
        await SystemSettingsService.openExactAlarmSettings();
      }
      return await SystemSettingsService.checkPermissionsStatus();
    } catch (e) {
      debugPrint('Error requestAllPermissionsSeamlessly: $e');
      return await SystemSettingsService.checkPermissionsStatus();
    }
  }

  Future<bool> checkNotificationPermission() async {
    if (kIsWeb) return true;
    final android = _notificationsPlugin
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >();
    return (await android?.areNotificationsEnabled()) ?? true;
  }

  Future<void> requestBatteryExemption() =>
      SystemSettingsService.requestBatteryExemption();
  Future<void> openExactAlarmSettings() =>
      SystemSettingsService.openExactAlarmSettings();
  Future<void> openNotificationSettings() =>
      SystemSettingsService.openNotificationSettings();
  Future<void> openBatterySettings() =>
      SystemSettingsService.openBatterySettings();
  Future<void> openAppSettings() => SystemSettingsService.openAppSettings();

  // ===========================================================================
  // 2. INISIALISASI & NOTIFIKASI
  // ===========================================================================

  Future<void> init() async {
    await _loadSettings();
    _configureAudioContext();
    if (!kIsWeb) {
      await _initLocalNotifications();
      await _syncScheduledAlarms();
    }
    startMonitoring();
  }

  void _configureAudioContext() {
    try {
      _player.setAudioContext(
        AudioContext(
          android: const AudioContextAndroid(
            isSpeakerphoneOn: true,
            stayAwake: true,
            contentType: AndroidContentType.sonification,
            usageType: AndroidUsageType.alarm,
            audioFocus: AndroidAudioFocus.gainTransientMayDuck,
          ),
          iOS: AudioContextIOS(
            category: AVAudioSessionCategory.playback,
            options: {
              AVAudioSessionOptions.mixWithOthers,
              AVAudioSessionOptions.duckOthers,
            },
          ),
        ),
      );
    } catch (e) {
      debugPrint('AudioContext setup warning: $e');
    }
  }

  Future<void> _initLocalNotifications() async {
    if (_isNotificationInitialized) return;

    try {
      tz.initializeTimeZones();
      try {
        final timezoneInfo = await FlutterTimezone.getLocalTimezone();
        tz.setLocalLocation(tz.getLocation(timezoneInfo.identifier));
      } catch (_) {
        tz.setLocalLocation(tz.getLocation('Asia/Jakarta'));
      }

      const initSettings = InitializationSettings(
        android: AndroidInitializationSettings('@mipmap/ic_launcher'),
        iOS: DarwinInitializationSettings(
          requestAlertPermission: true,
          requestBadgePermission: true,
          requestSoundPermission: true,
        ),
      );

      await _notificationsPlugin.initialize(
        settings: initSettings,
        onDidReceiveNotificationResponse: (response) {
          debugPrint('Notifikasi bel diklik: ${response.payload}');
        },
      );

      final android = _notificationsPlugin
          .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin
          >();

      if (android != null) {
        // Hapus channel versi lama
        for (final v in ['', '_v2', '_v3', '_v4', '_v5']) {
          try {
            await android.deleteNotificationChannel(
              channelId: 'mdths_kbm_bell_channel$v',
            );
          } catch (_) {}
        }

        const channel = AndroidNotificationChannel(
          _channelId,
          _channelName,
          description: _channelDesc,
          importance: Importance.max,
          sound: RawResourceAndroidNotificationSound('school_bell'),
          playSound: true,
          enableVibration: true,
          audioAttributesUsage: AudioAttributesUsage.alarm,
        );
        await android.createNotificationChannel(channel);
        await android.requestNotificationsPermission();
        await android.requestExactAlarmsPermission();
      }

      _isNotificationInitialized = true;
    } catch (e) {
      debugPrint('Error init local notifications: $e');
    }
  }

  NotificationDetails _buildNotificationDetails({
    required String title,
    required String body,
    required int jam,
    String? subText,
  }) {
    final androidDetails = AndroidNotificationDetails(
      _channelId,
      _channelName,
      channelDescription: _channelDesc,
      importance: Importance.max,
      priority: Priority.max,
      sound: const RawResourceAndroidNotificationSound('school_bell'),
      playSound: true,
      enableVibration: true,
      vibrationPattern: Int64List.fromList([0, 500, 250, 500, 250, 500]),
      styleInformation: BigTextStyleInformation(
        body,
        contentTitle: title,
        summaryText: subText ?? 'MDTHS • Jam Ke-$jam KBM',
      ),
      fullScreenIntent: true,
      visibility: NotificationVisibility.public,
      category: AndroidNotificationCategory.alarm,
      audioAttributesUsage: AudioAttributesUsage.alarm,
      color: const Color(0xFF146C2E),
      ledColor: const Color(0xFF10B981),
      ledOnMs: 1000,
      ledOffMs: 500,
      enableLights: true,
      autoCancel: true,
      ticker: '🔔 $title',
      actions: const [
        AndroidNotificationAction(
          'action_presensi',
          '📝 Masuk & Presensi',
          showsUserInterface: true,
          cancelNotification: true,
        ),
        AndroidNotificationAction(
          'action_dismiss',
          '🔕 Tutup',
          cancelNotification: true,
        ),
      ],
    );

    const darwinDetails = DarwinNotificationDetails(
      sound: 'school_bell.wav',
      presentSound: true,
      presentAlert: true,
      presentBanner: true,
      presentBadge: true,
      interruptionLevel: InterruptionLevel.timeSensitive,
      subtitle: 'Pengingat KBM & Presensi',
    );

    return NotificationDetails(android: androidDetails, iOS: darwinDetails);
  }

  Future<void> _zonedScheduleSafe({
    required int id,
    required String title,
    required String body,
    required tz.TZDateTime scheduledDate,
    required NotificationDetails details,
    DateTimeComponents? matchDateTimeComponents,
    String? payload,
  }) async {
    final modes = [
      AndroidScheduleMode.alarmClock,
      AndroidScheduleMode.exactAllowWhileIdle,
      AndroidScheduleMode.inexactAllowWhileIdle,
    ];

    for (final mode in modes) {
      try {
        await _notificationsPlugin.zonedSchedule(
          id: id,
          title: title,
          body: body,
          scheduledDate: scheduledDate,
          notificationDetails: details,
          androidScheduleMode: mode,
          matchDateTimeComponents: matchDateTimeComponents,
          payload: payload,
        );
        return;
      } catch (_) {}
    }
  }

  // ===========================================================================
  // 3. SINKRONISASI ALARM SISTEM
  // ===========================================================================

  Future<void> _syncScheduledAlarms() async {
    if (kIsWeb || !_isNotificationInitialized) return;

    try {
      await _notificationsPlugin.cancelAll();
      if (!_isEnabled) return;

      final targetSchedule = <int, List<int>>{};
      if (_onlyOnTeachingDays && _teachingSchedule.isNotEmpty) {
        for (final entry in _teachingSchedule.entries) {
          if (_activeDays.contains(entry.key)) {
            targetSchedule[entry.key] = entry.value;
          }
        }
      } else {
        for (final day in _activeDays) {
          targetSchedule[day] = [1, 2];
        }
      }

      for (final entry in targetSchedule.entries) {
        final day = entry.key;
        final sessions = entry.value;

        if (sessions.contains(1)) {
          await _scheduleSingleBell(
            id: day * 10 + 1,
            jam: 1,
            time: _jam1Time,
            day: day,
          );
        }
        if (sessions.contains(2)) {
          await _scheduleSingleBell(
            id: day * 10 + 2,
            jam: 2,
            time: _jam2Time,
            day: day,
          );
        }
      }
    } catch (e) {
      debugPrint('Gagal sinkronisasi alarm bel sistem: $e');
    }
  }

  Future<void> _scheduleSingleBell({
    required int id,
    required int jam,
    required TimeOfDay time,
    required int day,
  }) async {
    final scheduledDate = _nextInstanceOfWeekdayAndTime(
      day,
      time.hour,
      time.minute,
    );
    final title = '🔔 Bel Masuk Jam Ke-$jam (${_formatTime(time)})';
    final body =
        'Bel jam ${jam == 1 ? "pertama" : "kedua"} sudah berbunyi! Silahkan masuk ke dalam ruangan kelas dan jangan lupa lakukan presensi Ustadz dan Murid.';
    final details = _buildNotificationDetails(
      title: title,
      body: body,
      jam: jam,
    );

    await _zonedScheduleSafe(
      id: id,
      title: title,
      body: body,
      scheduledDate: scheduledDate,
      details: details,
      matchDateTimeComponents: DateTimeComponents.dayOfWeekAndTime,
      payload: 'bell_jam_$jam',
    );
  }

  Future<void> showImmediateNotification({
    required int id,
    required String title,
    required String body,
    required int jam,
    String? subText,
  }) async {
    if (kIsWeb || !_isNotificationInitialized) return;
    try {
      final details = _buildNotificationDetails(
        title: title,
        body: body,
        jam: jam,
        subText: subText,
      );
      await _notificationsPlugin.show(
        id: id,
        title: title,
        body: body,
        notificationDetails: details,
        payload: 'bell_jam_$jam',
      );
    } catch (e) {
      debugPrint('Error showing immediate notification: $e');
    }
  }

  Future<void> showTestNotificationInstant() async {
    await showImmediateNotification(
      id: 9998,
      title: '🔔 Uji Coba Bel Masuk (Seketika)',
      body:
          'Bel jam pertama sudah berbunyi! Silahkan masuk ke dalam ruangan kelas dan jangan lupa lakukan presensi Ustadz dan Murid.',
      jam: 1,
      subText: 'Uji Notifikasi Instan MDTHS',
    );
  }

  Future<void> scheduleTestNotification({int delaySeconds = 5}) async {
    if (kIsWeb || !_isNotificationInitialized) {
      await playBellSound(repeats: 2);
      return;
    }

    try {
      await _notificationsPlugin.cancel(id: 9999);
    } catch (_) {}

    final testTime = tz.TZDateTime.now(
      tz.local,
    ).add(Duration(seconds: delaySeconds));
    final title = '🔔 Uji Coba Bel Masuk ($delaySeconds Detik)';
    const body =
        'Bel jam pertama sudah berbunyi! Silahkan masuk ke dalam ruangan kelas dan jangan lupa lakukan presensi Ustadz dan Murid.';
    final details = _buildNotificationDetails(
      title: title,
      body: body,
      jam: 1,
      subText: 'Uji Coba Sistem Notifikasi MDTHS',
    );

    await _zonedScheduleSafe(
      id: 9999,
      title: title,
      body: body,
      scheduledDate: testTime,
      details: details,
      payload: 'bell_test',
    );
  }

  tz.TZDateTime _nextInstanceOfWeekdayAndTime(
    int weekday,
    int hour,
    int minute,
  ) {
    tz.TZDateTime now = tz.TZDateTime.now(tz.local);
    tz.TZDateTime scheduled = tz.TZDateTime(
      tz.local,
      now.year,
      now.month,
      now.day,
      hour,
      minute,
    );
    while (scheduled.weekday != weekday || scheduled.isBefore(now)) {
      scheduled = scheduled.add(const Duration(days: 1));
    }
    return scheduled;
  }

  // ===========================================================================
  // 4. JADWAL MENGAJAR & PERSISTENSI SETTINGS
  // ===========================================================================

  Future<void> updateTeachingScheduleFromJadwal(
    List<HariJadwalItem> jadwalPerHari,
  ) async {
    final Map<int, List<int>> scheduleMap = {};

    for (final item in jadwalPerHari) {
      final dayInt = _mapDayNameToWeekday(item.hari);
      if (dayInt == null) continue;

      final sessions = <int>{};
      for (final s in item.sesi) {
        final jk = s.jamKe.trim();
        final isJam1 = jk.contains('1') || s.jam.contains('13:');
        final isJam2 = jk.contains('2') || s.jam.contains('15:');
        if (isJam1) sessions.add(1);
        if (isJam2) sessions.add(2);
        if (!isJam1 && !isJam2) sessions.addAll([1, 2]);
      }

      if (sessions.isNotEmpty) {
        scheduleMap[dayInt] = sessions.toList()..sort();
      }
    }

    _teachingSchedule = scheduleMap;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(
      _keyTeachingSchedule,
      jsonEncode(_teachingSchedule.map((k, v) => MapEntry(k.toString(), v))),
    );
    await _syncScheduledAlarms();
  }

  Future<void> clearTeachingSchedule() async {
    _teachingSchedule.clear();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyTeachingSchedule);
    await _syncScheduledAlarms();
  }

  int? _mapDayNameToWeekday(String dayName) {
    switch (dayName.trim().toLowerCase()) {
      case 'senin':
        return 1;
      case 'selasa':
        return 2;
      case 'rabu':
        return 3;
      case 'kamis':
        return 4;
      case 'jumat':
        return 5;
      case 'sabtu':
        return 6;
      case 'ahad':
      case 'minggu':
        return 7;
      default:
        return null;
    }
  }

  String _mapWeekdayToDayName(int weekday) =>
      (weekday >= 1 && weekday <= 7) ? _dayNames[weekday - 1] : 'Hari';

  Future<void> _loadSettings() async {
    final prefs = await SharedPreferences.getInstance();
    _isEnabled = prefs.getBool(_keyEnabled) ?? true;
    _onlyOnTeachingDays = prefs.getBool(_keyOnlyTeachingDays) ?? true;

    _jam1Time = _parseTimeOfDay(
      prefs.getString(_keyJam1),
      const TimeOfDay(hour: 13, minute: 45),
    );
    _jam2Time = _parseTimeOfDay(
      prefs.getString(_keyJam2),
      const TimeOfDay(hour: 15, minute: 30),
    );
    _volume = prefs.getDouble(_keyVolume) ?? 1.0;

    final days = prefs.getStringList(_keyActiveDays);
    if (days != null && days.isNotEmpty) {
      _activeDays = days.map((e) => int.tryParse(e) ?? 1).toList();
    }

    final scheduleStr = prefs.getString(_keyTeachingSchedule);
    if (scheduleStr != null) {
      try {
        final decoded = jsonDecode(scheduleStr) as Map<String, dynamic>;
        _teachingSchedule = decoded.map(
          (k, v) =>
              MapEntry(int.parse(k), (v as List).map((e) => e as int).toList()),
        );
      } catch (_) {}
    }
  }

  TimeOfDay _parseTimeOfDay(String? str, TimeOfDay fallback) {
    if (str == null) return fallback;
    final parts = str.split(':');
    if (parts.length != 2) return fallback;
    return TimeOfDay(
      hour: int.tryParse(parts[0]) ?? fallback.hour,
      minute: int.tryParse(parts[1]) ?? fallback.minute,
    );
  }

  Future<void> setEnabled(bool val) async {
    _isEnabled = val;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_keyEnabled, val);
    await _syncScheduledAlarms();
  }

  Future<void> setOnlyOnTeachingDays(bool val) async {
    _onlyOnTeachingDays = val;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_keyOnlyTeachingDays, val);
    await _syncScheduledAlarms();
  }

  Future<void> setJam1Time(TimeOfDay time) async {
    _jam1Time = time;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyJam1, '${time.hour}:${time.minute}');
    await _syncScheduledAlarms();
  }

  Future<void> setJam2Time(TimeOfDay time) async {
    _jam2Time = time;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyJam2, '${time.hour}:${time.minute}');
    await _syncScheduledAlarms();
  }

  Future<void> setVolume(double val) async {
    _volume = val;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setDouble(_keyVolume, val);
  }

  Future<void> toggleActiveDay(int day) async {
    if (_activeDays.contains(day)) {
      if (_activeDays.length > 1) _activeDays.remove(day);
    } else {
      _activeDays.add(day);
      _activeDays.sort();
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList(
      _keyActiveDays,
      _activeDays.map((e) => e.toString()).toList(),
    );
    await _syncScheduledAlarms();
  }

  Future<void> resetToDefault() async {
    _isEnabled = true;
    _onlyOnTeachingDays = true;
    _jam1Time = const TimeOfDay(hour: 13, minute: 45);
    _jam2Time = const TimeOfDay(hour: 15, minute: 30);
    _volume = 1.0;
    _activeDays = [1, 2, 3, 4, 6, 7];

    final prefs = await SharedPreferences.getInstance();
    await Future.wait([
      prefs.remove(_keyEnabled),
      prefs.remove(_keyOnlyTeachingDays),
      prefs.remove(_keyJam1),
      prefs.remove(_keyJam2),
      prefs.remove(_keyVolume),
      prefs.remove(_keyActiveDays),
    ]);
    await _syncScheduledAlarms();
  }

  // ===========================================================================
  // 5. FOREGROUND MONITORING & AUDIO PLAYBACK
  // ===========================================================================

  void startMonitoring() {
    _timer?.cancel();
    _timer = Timer.periodic(
      const Duration(seconds: 10),
      (_) => _checkSchedule(),
    );
  }

  void _checkSchedule() {
    if (!_isEnabled) return;
    final now = DateTime.now();
    final weekday = now.weekday;

    if (!_activeDays.contains(weekday)) return;
    if (_onlyOnTeachingDays &&
        _teachingSchedule.isNotEmpty &&
        !hasTeachingScheduleOn(weekday)) {
      return;
    }

    final currentH = now.hour;
    final currentM = now.minute;

    // Check Jam 1
    if (currentH == _jam1Time.hour && currentM == _jam1Time.minute) {
      if (!_onlyOnTeachingDays ||
          _teachingSchedule.isEmpty ||
          hasTeachingSessionOn(weekday, 1)) {
        final key =
            '${now.year}-${now.month}-${now.day}_jam1_${_jam1Time.hour}:${_jam1Time.minute}';
        if (_lastTriggeredKey != key) {
          _lastTriggeredKey = key;
          _triggerBell(
            1,
            '🔔 Bel Masuk Jam Ke-1 (${_formatTime(_jam1Time)})',
            'Bel jam pertama sudah berbunyi! Silahkan masuk ke dalam ruangan kelas dan jangan lupa lakukan presensi Ustadz dan Murid.',
            _formatTime(_jam1Time),
          );
        }
      }
    }

    // Check Jam 2
    if (currentH == _jam2Time.hour && currentM == _jam2Time.minute) {
      if (!_onlyOnTeachingDays ||
          _teachingSchedule.isEmpty ||
          hasTeachingSessionOn(weekday, 2)) {
        final key =
            '${now.year}-${now.month}-${now.day}_jam2_${_jam2Time.hour}:${_jam2Time.minute}';
        if (_lastTriggeredKey != key) {
          _lastTriggeredKey = key;
          _triggerBell(
            2,
            '🔔 Bel Masuk Jam Ke-2 (${_formatTime(_jam2Time)})',
            'Bel jam kedua sudah berbunyi! Silahkan masuk ke dalam ruangan kelas dan jangan lupa lakukan presensi Ustadz dan Murid.',
            _formatTime(_jam2Time),
          );
        }
      }
    }
  }

  Future<void> _triggerBell(
    int jam,
    String title,
    String desc,
    String waktu,
  ) async {
    HapticHelper.warning();
    _bellEventController.add(
      BellEvent(
        jam: jam,
        title: title,
        description: desc,
        waktu: waktu,
        timestamp: DateTime.now(),
      ),
    );
  }

  String _formatTime(TimeOfDay t) {
    final h = t.hour.toString().padLeft(2, '0');
    final m = t.minute.toString().padLeft(2, '0');
    return '$h:$m WIB';
  }

  void _setPlayingState(bool playing) {
    _isPlaying = playing;
    _playingStateController.add(playing);
  }

  /// Putar suara bel sekolah (diulang n kali)
  Future<void> playBellSound({int repeats = 3}) async {
    final session = ++_currentSession;
    _setPlayingState(true);

    try {
      // 1. Prioritaskan Native Android MediaPlayer (Stream Alarm 100% Kencang)
      if (!kIsWeb) {
        final nativePlayed = await SystemSettingsService.playNativeAlarmSound(
          repeats: repeats,
        );
        if (nativePlayed) {
          for (int s = 0; s < repeats * 35; s++) {
            if (_currentSession != session) {
              await SystemSettingsService.stopNativeAlarmSound();
              break;
            }
            await Future.delayed(const Duration(milliseconds: 100));
          }
          return;
        }
      }

      // 2. Fallback audioplayers
      await _player.stop();
      await _player.setVolume(_volume);

      Uint8List? audioBytes;
      try {
        final byteData = await rootBundle.load('assets/audio/school-bell.wav');
        audioBytes = byteData.buffer.asUint8List();
      } catch (_) {}

      for (int i = 0; i < repeats; i++) {
        if (_currentSession != session) break;

        try {
          if (kIsWeb && audioBytes != null) {
            await _player.play(BytesSource(audioBytes, mimeType: 'audio/wav'));
          } else {
            try {
              await _player.play(AssetSource('audio/school-bell.wav'));
            } catch (_) {
              if (audioBytes != null) {
                await _player.play(
                  BytesSource(audioBytes, mimeType: 'audio/wav'),
                );
              }
            }
          }
        } catch (e) {
          debugPrint('Error play audio fallback: $e');
        }

        await Future.any([
          _player.onPlayerComplete.first,
          Future.delayed(const Duration(milliseconds: 3200)),
        ]);

        if (_currentSession != session) break;
        if (i < repeats - 1) {
          await Future.delayed(const Duration(milliseconds: 300));
        }
      }
    } catch (e) {
      debugPrint('Gagal memutar audio bel: $e');
    } finally {
      if (_currentSession == session) {
        _setPlayingState(false);
      }
    }
  }

  Future<void> stopSound() async {
    _currentSession++;
    _setPlayingState(false);
    try {
      await SystemSettingsService.stopNativeAlarmSound();
      await _player.stop();
    } catch (_) {}
  }

  // ===========================================================================
  // 6. PERHITUNGAN JADWAL BERIKUTNYA
  // ===========================================================================

  Map<String, dynamic> getNextBellInfo() {
    if (!_isEnabled) {
      return {'active': false, 'text': 'Notifikasi bel dinonaktifkan'};
    }

    if (_onlyOnTeachingDays && _teachingSchedule.isEmpty) {
      return {
        'active': false,
        'text': 'Bel standby (Menunggu sinkronisasi jadwal mengajar)',
      };
    }

    final now = DateTime.now();
    final todayWeekday = now.weekday;
    final nowMinutes = now.hour * 60 + now.minute;
    final jam1Minutes = _jam1Time.hour * 60 + _jam1Time.minute;
    final jam2Minutes = _jam2Time.hour * 60 + _jam2Time.minute;

    final hasTeachingToday =
        !_onlyOnTeachingDays || hasTeachingScheduleOn(todayWeekday);

    if (_activeDays.contains(todayWeekday) && hasTeachingToday) {
      final sessions = _onlyOnTeachingDays
          ? (_teachingSchedule[todayWeekday] ?? [1, 2])
          : [1, 2];

      if (sessions.contains(1) && nowMinutes < jam1Minutes) {
        final diff = jam1Minutes - nowMinutes;
        return {
          'active': true,
          'jam': 1,
          'time': _formatTime(_jam1Time),
          'text':
              'Hari Ini: Jam Ke-1 (${_formatTime(_jam1Time)}) • ${_formatDuration(diff)} lagi',
        };
      } else if (sessions.contains(2) && nowMinutes < jam2Minutes) {
        final diff = jam2Minutes - nowMinutes;
        return {
          'active': true,
          'jam': 2,
          'time': _formatTime(_jam2Time),
          'text':
              'Hari Ini: Jam Ke-2 (${_formatTime(_jam2Time)}) • ${_formatDuration(diff)} lagi',
        };
      }
    }

    // Cari jadwal hari mendatang dalam 7 hari
    for (int offset = 1; offset <= 7; offset++) {
      final targetDate = now.add(Duration(days: offset));
      final targetWeekday = targetDate.weekday;

      if (!_activeDays.contains(targetWeekday)) continue;
      if (_onlyOnTeachingDays && !hasTeachingScheduleOn(targetWeekday)) {
        continue;
      }

      final sessions = _onlyOnTeachingDays
          ? (_teachingSchedule[targetWeekday] ?? [1, 2])
          : [1, 2];
      final dayName = _mapWeekdayToDayName(targetWeekday);

      if (sessions.contains(1)) {
        return {
          'active': true,
          'jam': 1,
          'time': _formatTime(_jam1Time),
          'text':
              'Bel berikutnya: $dayName, Jam Ke-1 (${_formatTime(_jam1Time)})',
        };
      } else if (sessions.contains(2)) {
        return {
          'active': true,
          'jam': 2,
          'time': _formatTime(_jam2Time),
          'text':
              'Bel berikutnya: $dayName, Jam Ke-2 (${_formatTime(_jam2Time)})',
        };
      }
    }

    return {
      'active': false,
      'text': 'Tidak ada jadwal mengajar aktif untuk bel',
    };
  }

  String _formatDuration(int minutes) {
    if (minutes < 60) return '$minutes menit';
    final h = minutes ~/ 60;
    final m = minutes % 60;
    return m > 0 ? '$h jam $m mnt' : '$h jam';
  }

  void dispose() {
    _timer?.cancel();
    _player.dispose();
    _bellEventController.close();
    _playingStateController.close();
  }
}
