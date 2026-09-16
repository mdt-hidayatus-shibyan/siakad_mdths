import 'package:flutter/material.dart';
import '../core/constants/app_constants.dart';
import '../core/storage/storage_service.dart';
import '../data/models/wali_model.dart';
import '../data/repositories/auth_repository.dart';

class AuthProvider extends ChangeNotifier {
  final AuthRepository _authRepo = AuthRepository();

  bool _isLoading = false;
  String? _errorMessage;
  WaliModel? _currentWali;
  final String _baseUrl = AppConstants.baseUrl;

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  WaliModel? get currentWali => _currentWali;
  bool get isAuthenticated => StorageService.getToken() != null;
  String get baseUrl => _baseUrl;

  AuthProvider() {
    _loadSavedSession();
  }

  void _loadSavedSession() {
    final waliJson = StorageService.getWaliData();
    if (waliJson != null) {
      _currentWali = WaliModel.fromJson(waliJson);
    }
  }

  /// Step 1: Validasi keberadaan No. KK / NISM
  Future<Map<String, dynamic>> checkIdentifier(String identifier) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _authRepo.checkIdentifier(identifier);

    _isLoading = false;
    if (result['success'] != true) {
      _errorMessage = result['message'] as String?;
    }
    notifyListeners();
    return result;
  }

  /// Step 2: Login dengan PIN
  Future<bool> login(String identifier, String pin) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _authRepo.loginWali(identifier, pin);

    _isLoading = false;
    if (result['success'] == true) {
      _currentWali = result['wali'] as WaliModel;
      _errorMessage = null;
      notifyListeners();
      return true;
    } else {
      _errorMessage = result['message'] as String?;
      notifyListeners();
      return false;
    }
  }

  /// Ganti PIN
  Future<Map<String, dynamic>> updatePin(String pinLama, String pinBaru) async {
    _isLoading = true;
    notifyListeners();

    final result = await _authRepo.updatePin(pinLama, pinBaru);

    _isLoading = false;
    if (result['success'] == true && _currentWali != null) {
      _currentWali = _currentWali!.copyWith(
        isFirstLogin: false,
        isPinChanged: true,
      );
      await StorageService.setWaliData(_currentWali!.toJson());
    }
    notifyListeners();
    return result;
  }

  Future<void> logout() async {
    await _authRepo.logout();
    _currentWali = null;
    notifyListeners();
  }
}
