import 'package:flutter/material.dart';
import '../data/models/app_version_model.dart';

/// Provider versi aplikasi mandiri (local source of truth)
/// Tidak melakukan request ke API server agar versi yang ditampilkan
/// selalu sesuai 100% dengan build aplikasi yang sedang terpasang di HP.
class AppVersionProvider extends ChangeNotifier {
  AppVersionModel _appVersion = AppVersionModel.fromConfig();

  AppVersionModel get appVersion => _appVersion;
  bool get isLoading => false;
  String? get errorMessage => null;

  /// Memuat ulang data konfigurasi versi lokal jika dipanggil
  Future<void> fetchAppVersion({bool refresh = false}) async {
    _appVersion = AppVersionModel.fromConfig();
    notifyListeners();
  }
}
