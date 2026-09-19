import 'package:flutter/material.dart';
import '../core/network/api_client.dart';
import '../data/models/user_model.dart';
import '../data/repositories/auth_repository.dart';

class AuthProvider extends ChangeNotifier {
  final AuthRepository _authRepo = AuthRepository();

  UserModel? _user;
  bool _isLoading = false;
  String? _errorMessage;

  UserModel? get user => _user;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _user != null;

  Future<void> checkAuth() async {
    _isLoading = true;
    notifyListeners();
    try {
      _user = await _authRepo.getCachedUser();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> login(String loginId, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _user = await _authRepo.login(loginId, password);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> fetchProfile() async {
    _isLoading = true;
    notifyListeners();
    try {
      _user = await _authRepo.getProfile();
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> updatePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final success = await _authRepo.updatePassword(
        currentPassword: currentPassword,
        newPassword: newPassword,
        newPasswordConfirmation: newPasswordConfirmation,
      );
      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<Map<String, dynamic>?> forgotPassword(String loginId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _authRepo.forgotPassword(loginId);
      _isLoading = false;
      notifyListeners();
      return res;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return null;
    }
  }

  Future<String?> verifyOtp({
    required String loginId,
    required String otpCode,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final resetToken = await _authRepo.verifyOtp(
        loginId: loginId,
        otpCode: otpCode,
      );
      _isLoading = false;
      notifyListeners();
      return resetToken;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return null;
    }
  }

  Future<bool> resetPassword({
    required String loginId,
    required String resetToken,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final success = await _authRepo.resetPassword(
        loginId: loginId,
        resetToken: resetToken,
        newPassword: newPassword,
        newPasswordConfirmation: newPasswordConfirmation,
      );
      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateAccount({
    required String username,
    required String email,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final success = await _authRepo.updateAccount(
        username: username,
        email: email,
      );
      if (success && _user != null) {
        _user = _user!.copyWith(username: username, email: email);
      }
      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateBiodata(Map<String, dynamic> data) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final success = await _authRepo.updateBiodata(data);
      if (success) {
        await fetchProfile();
      }
      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateFoto({
    String? filePath,
    String? base64Image,
    bool deleteFoto = false,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final photoUrl = await _authRepo.updateFoto(
        filePath: filePath,
        base64Image: base64Image,
        deleteFoto: deleteFoto,
      );
      if (_user != null) {
        _user = _user!.copyWith(
          photo: deleteFoto
              ? null
              : (ApiClient.resolveImageUrl(photoUrl) ?? photoUrl),
          setPhotoNull: deleteFoto,
        );
      }
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateTandaTangan({
    String? filePath,
    String? base64Image,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final ttdUrl = await _authRepo.updateTandaTangan(
        filePath: filePath,
        base64Image: base64Image,
      );
      if (_user != null) {
        _user = _user!.copyWith(
          tandaTangan: ApiClient.resolveImageUrl(ttdUrl) ?? ttdUrl,
        );
      }
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();
    await _authRepo.logout();
    _user = null;
    _isLoading = false;
    notifyListeners();
  }

  void reset() {
    _user = null;
    _isLoading = false;
    _errorMessage = null;
    notifyListeners();
  }
}
