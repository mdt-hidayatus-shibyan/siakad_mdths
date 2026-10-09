import 'dart:async';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/network/api_client.dart';

enum SignalQuality {
  checking,
  strong, // < 250 ms (Sangat Stabil / Aman Input Data)
  moderate, // 250 - 600 ms (Cukup Stabil / Aman Input Data)
  weak, // > 600 ms (Kurang Stabil / Lambat)
  offline, // Terputus / Tidak ada koneksi (Jangan Input Data)
}

class SignalProvider extends ChangeNotifier {
  SignalQuality _quality = SignalQuality.checking;
  int? _latencyMs;
  DateTime? _lastChecked;
  bool _isChecking = false;
  Timer? _periodicTimer;

  SignalQuality get quality => _quality;
  int? get latencyMs => _latencyMs;
  DateTime? get lastChecked => _lastChecked;
  bool get isChecking => _isChecking;

  SignalProvider() {
    // Jalankan pengecekan pertama kali
    checkSignal();
    // Pengecekan otomatis berkala setiap 90 detik secara background (hemat kuota & baterai)
    _periodicTimer = Timer.periodic(const Duration(seconds: 90), (_) {
      checkSignal();
    });
  }

  @override
  void dispose() {
    _periodicTimer?.cancel();
    super.dispose();
  }

  /// Rekomendasi apakah aman bagi ustadz untuk input presensi / data
  bool get isSafeToInput =>
      _quality == SignalQuality.strong || _quality == SignalQuality.moderate;

  /// Label singkat status sinyal
  String get label {
    switch (_quality) {
      case SignalQuality.strong:
        return 'Sinyal Kuat';
      case SignalQuality.moderate:
        return 'Sinyal Sedang';
      case SignalQuality.weak:
        return 'Sinyal Lemah';
      case SignalQuality.offline:
        return 'Offline';
      case SignalQuality.checking:
        return 'Memeriksa...';
    }
  }

  /// Keterangan detail & panduan tindakan untuk ustadz
  String get advice {
    switch (_quality) {
      case SignalQuality.strong:
        return 'Koneksi sangat lancar & stabil (${_latencyMs ?? 0} ms). Sangat aman untuk input presensi murid, ustadz, dan nilai.';
      case SignalQuality.moderate:
        return 'Koneksi cukup stabil (${_latencyMs ?? 0} ms). Aman untuk input data, namun pastikan proses simpan selesai.';
      case SignalQuality.weak:
        return 'Koneksi internet lambat (${_latencyMs ?? 0} ms). Penginputan data mungkin butuh waktu lebih lama, hindari keluar aplikasi sebelum selesai.';
      case SignalQuality.offline:
        return 'Koneksi terputus atau server tidak terjangkau. JANGAN input data saat ini karena data berpotensi gagal tersimpan.';
      case SignalQuality.checking:
        return 'Sedang menguji latensi dan kestabilan koneksi ke server...';
    }
  }

  /// Warna visual indikator sinyal
  Color get color {
    switch (_quality) {
      case SignalQuality.strong:
        return const Color(0xFF10B981); // Emerald Green
      case SignalQuality.moderate:
        return const Color(0xFFF59E0B); // Amber / Yellow
      case SignalQuality.weak:
        return const Color(0xFFF97316); // Orange
      case SignalQuality.offline:
        return const Color(0xFFEF4444); // Rose Red
      case SignalQuality.checking:
        return const Color(0xFF94A3B8); // Slate Grey
    }
  }

  /// Icon sinyal
  IconData get icon {
    switch (_quality) {
      case SignalQuality.strong:
        return Icons.signal_cellular_4_bar_rounded;
      case SignalQuality.moderate:
        return Icons.signal_cellular_alt_rounded;
      case SignalQuality.weak:
        return Icons.signal_cellular_alt_1_bar_rounded;
      case SignalQuality.offline:
        return Icons.signal_cellular_connected_no_internet_0_bar_rounded;
      case SignalQuality.checking:
        return Icons.signal_cellular_null_rounded;
    }
  }

  /// Eksekusi ping & ukur latensi ke endpoint /ping
  Future<void> checkSignal() async {
    if (_isChecking) return;
    _isChecking = true;
    notifyListeners();

    final stopwatch = Stopwatch()..start();
    try {
      // Menggunakan timeout singkat 4 detik agar tidak menggantung UI
      final dio = ApiClient().dio;
      final response = await dio.get(
        '/ping',
        options: Options(
          sendTimeout: const Duration(seconds: 4),
          receiveTimeout: const Duration(seconds: 4),
          responseType: ResponseType.json,
        ),
      );

      stopwatch.stop();
      final ms = stopwatch.elapsedMilliseconds;
      _latencyMs = ms;
      _lastChecked = DateTime.now();

      if (response.statusCode == 200) {
        if (ms < 250) {
          _quality = SignalQuality.strong;
        } else if (ms <= 600) {
          _quality = SignalQuality.moderate;
        } else {
          _quality = SignalQuality.weak;
        }
      } else {
        _quality = SignalQuality.weak;
      }
    } on DioException catch (e) {
      stopwatch.stop();
      _lastChecked = DateTime.now();
      if (e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.receiveTimeout ||
          e.type == DioExceptionType.sendTimeout) {
        _latencyMs = 4000;
        _quality = SignalQuality.weak;
      } else {
        _latencyMs = null;
        _quality = SignalQuality.offline;
      }
    } catch (_) {
      stopwatch.stop();
      _lastChecked = DateTime.now();
      _latencyMs = null;
      _quality = SignalQuality.offline;
    } finally {
      _isChecking = false;
      notifyListeners();
    }
  }
}
