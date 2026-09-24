import 'dart:async';
import 'package:flutter/material.dart';
import '../core/services/bell_service.dart';

class BellProvider extends ChangeNotifier {
  final BellService _service = BellService.instance;
  StreamSubscription<BellEvent>? _sub;
  StreamSubscription<bool>? _playingSub;

  BellEvent? _lastEvent;
  BellEvent? get lastEvent => _lastEvent;

  bool get isEnabled => _service.isEnabled;
  bool get onlyOnTeachingDays => _service.onlyOnTeachingDays;
  TimeOfDay get jam1Time => _service.jam1Time;
  TimeOfDay get jam2Time => _service.jam2Time;
  double get volume => _service.volume;
  List<int> get activeDays => _service.activeDays;
  Map<int, List<int>> get teachingSchedule => _service.teachingSchedule;
  bool get isPlaying => _service.isPlaying;

  String get jam1Formatted => _formatTime(jam1Time);
  String get jam2Formatted => _formatTime(jam2Time);
  Map<String, dynamic> get nextBellInfo => _service.getNextBellInfo();

  BellProvider() {
    _init();
  }

  Future<void> _init() async {
    await _service.init();
    _sub = _service.onBellEvent.listen((event) {
      _lastEvent = event;
      notifyListeners();
    });
    _playingSub = _service.onPlayingStateChanged.listen((_) {
      notifyListeners();
    });
    notifyListeners();
  }

  String _formatTime(TimeOfDay t) {
    final h = t.hour.toString().padLeft(2, '0');
    final m = t.minute.toString().padLeft(2, '0');
    return '$h:$m WIB';
  }

  Future<void> setEnabled(bool val) async {
    await _service.setEnabled(val);
    notifyListeners();
  }

  Future<void> setOnlyOnTeachingDays(bool val) async {
    await _service.setOnlyOnTeachingDays(val);
    notifyListeners();
  }

  Future<void> setJam1Time(TimeOfDay time) async {
    await _service.setJam1Time(time);
    notifyListeners();
  }

  Future<void> setJam2Time(TimeOfDay time) async {
    await _service.setJam2Time(time);
    notifyListeners();
  }

  Future<void> setVolume(double val) async {
    await _service.setVolume(val);
    notifyListeners();
  }

  Future<void> toggleActiveDay(int day) async {
    await _service.toggleActiveDay(day);
    notifyListeners();
  }

  Future<void> resetToDefault() async {
    await _service.resetToDefault();
    notifyListeners();
  }

  Future<void> testPlay() async {
    await _service.playBellSound(repeats: 3);
  }

  Future<void> stopSound() async {
    await _service.stopSound();
    notifyListeners();
  }

  void clearLastEvent() {
    _lastEvent = null;
    notifyListeners();
  }

  @override
  void dispose() {
    _sub?.cancel();
    _playingSub?.cancel();
    super.dispose();
  }
}
