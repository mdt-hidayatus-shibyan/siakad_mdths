import 'package:flutter/material.dart';
import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../data/models/app_version_model.dart';

class AppVersionProvider extends ChangeNotifier {
  final ApiClient _client = ApiClient();

  AppVersionModel _appVersion = AppVersionModel.fromConfigFallback();
  bool _isLoading = false;
  String? _errorMessage;

  AppVersionModel get appVersion => _appVersion;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  /// Ambil data versi & changelog aplikasi dari Backend API
  Future<void> fetchAppVersion({bool refresh = false}) async {
    if (_isLoading) return;

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _client.dio.get(
        ApiConstants.appVersion,
        queryParameters: {'app': 'ustadz'},
      );

      if (response.data != null && response.data['success'] == true) {
        final data = response.data['data'];
        if (data is Map<String, dynamic>) {
          _appVersion = AppVersionModel.fromJson(data);
        }
      }
    } catch (e) {
      // Jika terjadi kendala jaringan / offline, tetap gunakan data fallback
      _errorMessage = 'Gagal menyinkronkan data versi terbaru dari server.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
