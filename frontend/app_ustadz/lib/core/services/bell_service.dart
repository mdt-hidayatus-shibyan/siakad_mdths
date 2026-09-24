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

class BellService {
  static final BellService instance = BellService._internal();
  BellService._internal();

  final AudioPlayer _player = AudioPlayer();
  final FlutterLocalNotificationsPlugin _notificationsPlugin =
      FlutterLocalNotificationsPlugin();
  Timer? _timer;

  static const String _keyEnabled = 'bell_enabled';
  static const String _keyJam1 = 'bell_jam1';
  static const String _keyJam2 = 'bell_jam2';
  static const String _keyVolume = 'bell_volume';
  static const String _keyActiveDays = 'bell_active_days';
  static const String _keyOnlyTeachingDays = 'bell_only_teaching_days';
  static const String _keyTeachingSchedule = 'bell_teaching_schedule';

  static const String _channelId = 'mdths_kbm_bell_channel';
  static const String _channelName = 'Bel Masuk KBM MDTHS';
  static const String _channelDesc =
      'Notifikasi jadwal bel masuk jam pertama dan jam kedua';

  bool _isEnabled = true;
  bool _onlyOnTeachingDays = true; // Default: Hanya berbunyi jika ustadz mengajar
  TimeOfDay _jam1Time = const TimeOfDay(hour: 13, minute: 45);
  TimeOfDay _jam2Time = const TimeOfDay(hour: 15, minute: 30);
  double _volume = 1.0;
  List<int> _activeDays = [
    1,
    2,
    3,
    4,
    6,
    7,
  ]; // 1=Senin..4=Kamis, 6=Sabtu, 7=Ahad

  // Map jadwal mengajar ustadz: key = weekday (1..7), value = list sesi [1, 2]
  Map<int, List<int>> _teachingSchedule = {};

  bool _isPlaying = false;
  int _currentSession = 0;
  String? _lastTriggeredKey;
  bool _isNotificationInitialized = false;

  // Stream Controller for bell events to broadcast to UI
  final _bellEventController = StreamController<BellEvent>.broadcast();
  Stream<BellEvent> get onBellEvent => _bellEventController.stream;

  // Stream Controller for playing state changes
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
      _teachingSchedule.containsKey(weekday) &&
      (_teachingSchedule[weekday]?.isNotEmpty ?? false);

  bool hasTeachingSessionOn(int weekday, int jam) =>
      _teachingSchedule[weekday]?.contains(jam) ?? false;

  /// Inisialisasi awal BellService & muat konfigurasi tersimpan
  Future<void> init() async {
    await _loadSettings();
    if (!kIsWeb) {
      await _initLocalNotifications();
      await _syncScheduledAlarms();
    }
    startMonitoring();
  }

  /// Inisialisasi Notifikasi Sistem Lokal & Timezone
  Future<void> _initLocalNotifications() async {
    if (_isNotificationInitialized) return;

    try {
      tz.initializeTimeZones();
      try {
        final timezoneInfo = await FlutterTimezone.getLocalTimezone();
        tz.setLocalLocation(tz.getLocation(timezoneInfo.identifier));
      } catch (e) {
        debugPrint(
          'Gagal mendapatkan local timezone, fallback ke Asia/Jakarta: $e',
        );
        tz.setLocalLocation(tz.getLocation('Asia/Jakarta'));
      }

      const androidInit = AndroidInitializationSettings('@mipmap/ic_launcher');
      const darwinInit = DarwinInitializationSettings(
        requestAlertPermission: true,
        requestBadgePermission: true,
        requestSoundPermission: true,
      );

      const initSettings = InitializationSettings(
        android: androidInit,
        iOS: darwinInit,
      );

      await _notificationsPlugin.initialize(
        settings: initSettings,
        onDidReceiveNotificationResponse: (response) {
          debugPrint('Notifikasi bel diklik: ${response.payload}');
        },
      );

      final androidImplementation = _notificationsPlugin
          .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin
          >();

      if (androidImplementation != null) {
        const androidChannel = AndroidNotificationChannel(
          _channelId,
          _channelName,
          description: _channelDesc,
          importance: Importance.max,
          sound: RawResourceAndroidNotificationSound('school_bell'),
          playSound: true,
          enableVibration: true,
        );
        await androidImplementation.createNotificationChannel(androidChannel);
        await androidImplementation.requestNotificationsPermission();
        await androidImplementation.requestExactAlarmsPermission();
      }

      _isNotificationInitialized = true;
    } catch (e) {
      debugPrint('Error inisialisasi local notifications: $e');
    }
  }

  /// Sinkronisasi Jadwal Alarm Presisi ke Sistem Operasi (Hemat Baterai 0% Idle CPU)
  Future<void> _syncScheduledAlarms() async {
    if (kIsWeb || !_isNotificationInitialized) return;

    try {
      await _notificationsPlugin.cancelAll();

      if (!_isEnabled) return;

      if (_onlyOnTeachingDays) {
        // Hanya jadwalkan alarm di hari & jam ustadz mengajar
        for (final entry in _teachingSchedule.entries) {
          final day = entry.key;
          final sessions = entry.value;

          if (!_activeDays.contains(day)) continue;

          if (sessions.contains(1)) {
            final idJam1 = day * 10 + 1;
            final time1 = _nextInstanceOfWeekdayAndTime(
              day,
              _jam1Time.hour,
              _jam1Time.minute,
            );
            await _scheduleSingleBell(
              id: idJam1,
              title: '🔔 Bel Masuk Jam Ke-1 (${_formatTime(_jam1Time)})',
              body:
                  'Saatnya memulai Kegiatan Belajar Mengajar (KBM) Jam Pertama.',
              scheduledDate: time1,
            );
          }

          if (sessions.contains(2)) {
            final idJam2 = day * 10 + 2;
            final time2 = _nextInstanceOfWeekdayAndTime(
              day,
              _jam2Time.hour,
              _jam2Time.minute,
            );
            await _scheduleSingleBell(
              id: idJam2,
              title: '🔔 Bel Masuk Jam Ke-2 (${_formatTime(_jam2Time)})',
              body: 'Saatnya memulai Kegiatan Belajar Mengajar (KBM) Jam Kedua.',
              scheduledDate: time2,
            );
          }
        }
      } else {
        // Mode Manual: Jadwalkan untuk semua hari aktif yang dipilih
        for (final day in _activeDays) {
          final idJam1 = day * 10 + 1;
          final time1 = _nextInstanceOfWeekdayAndTime(
            day,
            _jam1Time.hour,
            _jam1Time.minute,
          );
          await _scheduleSingleBell(
            id: idJam1,
            title: '🔔 Bel Masuk Jam Ke-1 (${_formatTime(_jam1Time)})',
            body:
                'Saatnya memulai Kegiatan Belajar Mengajar (KBM) Jam Pertama.',
            scheduledDate: time1,
          );

          final idJam2 = day * 10 + 2;
          final time2 = _nextInstanceOfWeekdayAndTime(
            day,
            _jam2Time.hour,
            _jam2Time.minute,
          );
          await _scheduleSingleBell(
            id: idJam2,
            title: '🔔 Bel Masuk Jam Ke-2 (${_formatTime(_jam2Time)})',
            body: 'Saatnya memulai Kegiatan Belajar Mengajar (KBM) Jam Kedua.',
            scheduledDate: time2,
          );
        }
      }
    } catch (e) {
      debugPrint('Gagal sinkronisasi alarm bel sistem: $e');
    }
  }

  Future<void> _scheduleSingleBell({
    required int id,
    required String title,
    required String body,
    required tz.TZDateTime scheduledDate,
  }) async {
    const androidDetails = AndroidNotificationDetails(
      _channelId,
      _channelName,
      channelDescription: _channelDesc,
      importance: Importance.max,
      priority: Priority.max,
      sound: RawResourceAndroidNotificationSound('school_bell'),
      playSound: true,
      enableVibration: true,
      fullScreenIntent: true,
      category: AndroidNotificationCategory.alarm,
      audioAttributesUsage: AudioAttributesUsage.alarm,
    );

    const darwinDetails = DarwinNotificationDetails(
      sound: 'school_bell.wav',
      presentSound: true,
      presentAlert: true,
      presentBanner: true,
    );

    const details = NotificationDetails(
      android: androidDetails,
      iOS: darwinDetails,
    );

    await _notificationsPlugin.zonedSchedule(
      id: id,
      title: title,
      body: body,
      scheduledDate: scheduledDate,
      notificationDetails: details,
      androidScheduleMode: AndroidScheduleMode.exactAllowWhileIdle,
      matchDateTimeComponents: DateTimeComponents.dayOfWeekAndTime,
    );
  }

  tz.TZDateTime _nextInstanceOfWeekdayAndTime(
    int weekday,
    int hour,
    int minute,
  ) {
    tz.TZDateTime now = tz.TZDateTime.now(tz.local);
    tz.TZDateTime scheduledDate = tz.TZDateTime(
      tz.local,
      now.year,
      now.month,
      now.day,
      hour,
      minute,
      0,
    );

    while (scheduledDate.weekday != weekday || scheduledDate.isBefore(now)) {
      scheduledDate = scheduledDate.add(const Duration(days: 1));
    }
    return scheduledDate;
  }

  /// Sinkronkan jadwal mengajar ustadz yang login dari data API
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
        if (jk.contains('1') && !jk.contains('2')) {
          sessions.add(1);
        } else if (jk.contains('2') && !jk.contains('1')) {
          sessions.add(2);
        } else {
          if (s.jam.contains('13:') || jk.contains('1')) sessions.add(1);
          if (s.jam.contains('15:') || jk.contains('2')) sessions.add(2);
          if (sessions.isEmpty) {
            sessions.addAll([1, 2]);
          }
        }
      }

      if (sessions.isNotEmpty) {
        scheduleMap[dayInt] = sessions.toList()..sort();
      }
    }

    _teachingSchedule = scheduleMap;
    final prefs = await SharedPreferences.getInstance();
    final jsonMap = _teachingSchedule.map(
      (k, v) => MapEntry(k.toString(), v),
    );
    await prefs.setString(_keyTeachingSchedule, jsonEncode(jsonMap));
    await _syncScheduledAlarms();
  }

  /// Bersihkan jadwal mengajar saat logout
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

  String _mapWeekdayToDayName(int weekday) {
    switch (weekday) {
      case 1:
        return 'Senin';
      case 2:
        return 'Selasa';
      case 3:
        return 'Rabu';
      case 4:
        return 'Kamis';
      case 5:
        return 'Jumat';
      case 6:
        return 'Sabtu';
      case 7:
        return 'Ahad';
      default:
        return 'Hari';
    }
  }

  Future<void> _loadSettings() async {
    final prefs = await SharedPreferences.getInstance();
    _isEnabled = prefs.getBool(_keyEnabled) ?? true;
    _onlyOnTeachingDays = prefs.getBool(_keyOnlyTeachingDays) ?? true;

    final jam1Str = prefs.getString(_keyJam1);
    if (jam1Str != null) {
      final parts = jam1Str.split(':');
      if (parts.length == 2) {
        _jam1Time = TimeOfDay(
          hour: int.tryParse(parts[0]) ?? 13,
          minute: int.tryParse(parts[1]) ?? 45,
        );
      }
    }

    final jam2Str = prefs.getString(_keyJam2);
    if (jam2Str != null) {
      final parts = jam2Str.split(':');
      if (parts.length == 2) {
        _jam2Time = TimeOfDay(
          hour: int.tryParse(parts[0]) ?? 15,
          minute: int.tryParse(parts[1]) ?? 30,
        );
      }
    }

    _volume = prefs.getDouble(_keyVolume) ?? 1.0;

    final daysStr = prefs.getStringList(_keyActiveDays);
    if (daysStr != null && daysStr.isNotEmpty) {
      _activeDays = daysStr.map((e) => int.tryParse(e) ?? 1).toList();
    }

    final scheduleStr = prefs.getString(_keyTeachingSchedule);
    if (scheduleStr != null) {
      try {
        final decoded = jsonDecode(scheduleStr) as Map<String, dynamic>;
        _teachingSchedule = decoded.map((k, v) {
          final list = (v as List).map((e) => e as int).toList();
          return MapEntry(int.parse(k), list);
        });
      } catch (e) {
        debugPrint('Gagal parse cached teaching schedule: $e');
      }
    }
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
      if (_activeDays.length > 1) {
        _activeDays.remove(day);
      }
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
    await prefs.remove(_keyEnabled);
    await prefs.remove(_keyOnlyTeachingDays);
    await prefs.remove(_keyJam1);
    await prefs.remove(_keyJam2);
    await prefs.remove(_keyVolume);
    await prefs.remove('bell_sound_type');
    await prefs.remove(_keyActiveDays);
    await _syncScheduledAlarms();
  }

  /// Monitoring timer saat aplikasi aktif di foreground
  void startMonitoring() {
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 10), (_) {
      _checkSchedule();
    });
  }

  void _checkSchedule() {
    if (!_isEnabled) return;

    final now = DateTime.now();
    final weekday = now.weekday; // 1 = Senin ... 7 = Ahad

    if (!_activeDays.contains(weekday)) return;

    // Jika fitur 'Hanya saat ada jadwal mengajar' aktif dan hari ini ustadz tidak mengajar -> Lewati
    if (_onlyOnTeachingDays && !hasTeachingScheduleOn(weekday)) return;

    final currentHour = now.hour;
    final currentMinute = now.minute;

    // Check Jam 1 (Default 13:45)
    if (currentHour == _jam1Time.hour && currentMinute == _jam1Time.minute) {
      if (!_onlyOnTeachingDays || hasTeachingSessionOn(weekday, 1)) {
        final triggerKey =
            '${now.year}-${now.month}-${now.day}_jam1_${_jam1Time.hour}:${_jam1Time.minute}';
        if (_lastTriggeredKey != triggerKey) {
          _lastTriggeredKey = triggerKey;
          _triggerBell(
            1,
            '🔔 Bel Masuk Jam Ke-1 (${_formatTime(_jam1Time)})',
            'Saatnya memulai Kegiatan Belajar Mengajar (KBM) Jam Pertama.',
            _formatTime(_jam1Time),
          );
        }
      }
    }

    // Check Jam 2 (Default 15:30)
    if (currentHour == _jam2Time.hour && currentMinute == _jam2Time.minute) {
      if (!_onlyOnTeachingDays || hasTeachingSessionOn(weekday, 2)) {
        final triggerKey =
            '${now.year}-${now.month}-${now.day}_jam2_${_jam2Time.hour}:${_jam2Time.minute}';
        if (_lastTriggeredKey != triggerKey) {
          _lastTriggeredKey = triggerKey;
          _triggerBell(
            2,
            '🔔 Bel Masuk Jam Ke-2 (${_formatTime(_jam2Time)})',
            'Saatnya memulai Kegiatan Belajar Mengajar (KBM) Jam Kedua.',
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
    unawaited(playBellSound(repeats: 3));

    final event = BellEvent(
      jam: jam,
      title: title,
      description: desc,
      waktu: waktu,
      timestamp: DateTime.now(),
    );
    _bellEventController.add(event);
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

  /// Putar suara bel sekolah (diulang 3x)
  Future<void> playBellSound({int repeats = 3}) async {
    final session = ++_currentSession;
    _setPlayingState(true);

    try {
      await _player.stop();
      await _player.setVolume(_volume);

      Uint8List? audioBytes;
      if (kIsWeb) {
        final byteData = await rootBundle.load('assets/audio/school-bell.wav');
        audioBytes = byteData.buffer.asUint8List();
      }

      for (int i = 0; i < repeats; i++) {
        if (_currentSession != session) break;

        if (kIsWeb && audioBytes != null) {
          await _player.play(BytesSource(audioBytes, mimeType: 'audio/wav'));
        } else {
          try {
            await _player.play(AssetSource('audio/school-bell.wav'));
          } catch (_) {
            audioBytes ??= (await rootBundle.load('assets/audio/school-bell.wav'))
                .buffer
                .asUint8List();
            await _player.play(BytesSource(audioBytes, mimeType: 'audio/wav'));
          }
        }

        try {
          await _player.onPlayerComplete.first.timeout(
            const Duration(seconds: 6),
          );
        } catch (_) {
          // Timeout fallback
        }

        if (_currentSession != session) break;

        if (i < repeats - 1) {
          await Future.delayed(const Duration(milliseconds: 400));
        }
      }
    } catch (e) {
      debugPrint('Gagal memutar suara bel: $e');
    } finally {
      if (_currentSession == session) {
        _setPlayingState(false);
      }
    }
  }

  /// Hentikan suara bel yang sedang berputar
  Future<void> stopSound() async {
    _currentSession++;
    _setPlayingState(false);
    try {
      await _player.stop();
    } catch (e) {
      debugPrint('Gagal menghentikan audio bel: $e');
    }
  }

  /// Menghitung info jadwal bel berikutnya dengan memperhitungkan jadwal mengajar
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

    final bool hasTeachingToday =
        !_onlyOnTeachingDays || hasTeachingScheduleOn(todayWeekday);

    if (_activeDays.contains(todayWeekday) && hasTeachingToday) {
      final sessionsToday = _onlyOnTeachingDays
          ? (_teachingSchedule[todayWeekday] ?? [1, 2])
          : [1, 2];

      if (sessionsToday.contains(1) && nowMinutes < jam1Minutes) {
        final diff = jam1Minutes - nowMinutes;
        return {
          'active': true,
          'jam': 1,
          'time': _formatTime(_jam1Time),
          'text':
              'Hari Ini: Jam Ke-1 (${_formatTime(_jam1Time)}) • ${_formatDuration(diff)} lagi',
        };
      } else if (sessionsToday.contains(2) && nowMinutes < jam2Minutes) {
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

    // Cari hari mengajar berikutnya dalam 7 hari ke depan
    for (int offset = 1; offset <= 7; offset++) {
      final targetDate = now.add(Duration(days: offset));
      final targetWeekday = targetDate.weekday;

      if (!_activeDays.contains(targetWeekday)) continue;
      if (_onlyOnTeachingDays && !hasTeachingScheduleOn(targetWeekday)) continue;

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
    if (minutes < 60) {
      return '$minutes menit';
    }
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
