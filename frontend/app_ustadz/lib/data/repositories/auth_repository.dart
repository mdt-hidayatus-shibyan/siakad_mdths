import 'package:dio/dio.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../../core/storage/storage_service.dart';
import '../models/user_model.dart';

class AuthRepository {
  final ApiClient _client = ApiClient();

  Future<UserModel> login(String loginId, String password) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.login,
        data: {'login_id': loginId, 'password': password},
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        final token = response.data['token'];
        final userJson = response.data['user'];
        await StorageService.saveToken(token);
        await StorageService.saveUser(userJson);
        return UserModel.fromJson(userJson);
      } else {
        throw Exception(response.data['message'] ?? 'Login gagal.');
      }
    } on DioException catch (e) {
      if (e.response != null && e.response?.data != null) {
        final msg = e.response?.data['message'] ?? 'Login ditolak oleh server.';
        throw Exception(msg);
      }
      throw Exception(
        'Gagal terhubung ke backend server ($e). Periksa URL API dan pastikan server aktif.',
      );
    }
  }

  Future<UserModel> getProfile() async {
    try {
      final response = await _client.dio.get(ApiConstants.profile);
      if (response.statusCode == 200 && response.data['success'] == true) {
        final userJson = response.data['user'];
        await StorageService.saveUser(userJson);
        return UserModel.fromJson(userJson);
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat profil.');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      final cached = await getCachedUser();
      if (cached != null) return cached;
      throw Exception('Gagal terhubung ke server: ${e.message}');
    }
  }

  Future<bool> updatePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.updatePassword,
        data: {
          'current_password': currentPassword,
          'new_password': newPassword,
          'new_password_confirmation': newPasswordConfirmation,
        },
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memperbarui kata sandi.',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null) {
        final data = e.response!.data;
        if (data['errors'] != null) {
          final errors = data['errors'] as Map<String, dynamic>;
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            throw Exception(firstError.first.toString());
          }
        }
        if (data['message'] != null) {
          throw Exception(data['message']);
        }
      }
      throw Exception('Gagal memperbarui kata sandi: ${e.message}');
    }
  }

  Future<Map<String, dynamic>> forgotPassword(String loginId) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.forgotPassword,
        data: {'login_id': loginId},
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return {
          'message':
              response.data['message'] ??
              'Kode OTP telah dikirim ke email Anda.',
          'email_masked': response.data['data']?['email_masked'] ?? '',
        };
      } else {
        throw Exception(
          response.data['message'] ??
              'Gagal memproses permintaan reset kata sandi.',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal mengirim kode pemulihan: ${e.message}');
    }
  }

  Future<String> verifyOtp({
    required String loginId,
    required String otpCode,
  }) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.verifyOtp,
        data: {'login_id': loginId, 'otp_code': otpCode},
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        final resetToken = response.data['data']?['reset_token'] ?? '';
        return resetToken;
      } else {
        throw Exception(response.data['message'] ?? 'Kode OTP tidak valid.');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memverifikasi kode OTP: ${e.message}');
    }
  }

  Future<bool> resetPassword({
    required String loginId,
    required String resetToken,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.resetPassword,
        data: {
          'login_id': loginId,
          'reset_token': resetToken,
          'password': newPassword,
          'password_confirmation': newPasswordConfirmation,
        },
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal mengatur ulang kata sandi.',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null) {
        final data = e.response!.data;
        if (data['errors'] != null) {
          final errors = data['errors'] as Map<String, dynamic>;
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            throw Exception(firstError.first.toString());
          }
        }
        if (data['message'] != null) {
          throw Exception(data['message']);
        }
      }
      throw Exception('Gagal mengatur ulang kata sandi: ${e.message}');
    }
  }

  Future<UserModel> getBiodata() async {
    try {
      final response = await _client.dio.get(ApiConstants.ustadzBiodata);
      if (response.statusCode == 200 && response.data['success'] == true) {
        final data = response.data['data'];
        return UserModel.fromJson(data);
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat biodata.');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal terhubung ke server: ${e.message}');
    }
  }

  Future<bool> updateAccount({
    required String username,
    required String email,
  }) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.updateAccount,
        data: {'username': username, 'email': email},
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memperbarui akun.');
      }
    } on DioException catch (e) {
      if (e.response?.data != null) {
        final data = e.response!.data;
        if (data['errors'] != null) {
          final errors = data['errors'] as Map<String, dynamic>;
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            throw Exception(firstError.first.toString());
          }
        }
        if (data['message'] != null) {
          throw Exception(data['message']);
        }
      }
      throw Exception('Gagal memperbarui akun: ${e.message}');
    }
  }

  Future<bool> updateBiodata(Map<String, dynamic> data) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.ustadzUpdateBiodata,
        data: data,
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memperbarui biodata.',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null) {
        final data = e.response!.data;
        if (data['errors'] != null) {
          final errors = data['errors'] as Map<String, dynamic>;
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            throw Exception(firstError.first.toString());
          }
        }
        if (data['message'] != null) {
          throw Exception(data['message']);
        }
      }
      throw Exception('Gagal memperbarui biodata: ${e.message}');
    }
  }

  Future<String?> updateFoto({
    String? filePath,
    String? base64Image,
    bool deleteFoto = false,
  }) async {
    try {
      dynamic postData;
      if (deleteFoto) {
        postData = {'hapus_foto': true};
      } else if (filePath != null) {
        postData = FormData.fromMap({
          'foto': await MultipartFile.fromFile(filePath),
        });
      } else if (base64Image != null) {
        postData = {'foto_base64': base64Image};
      } else {
        throw Exception('Berkas foto tidak ditemukan.');
      }

      final response = await _client.dio.post(
        ApiConstants.ustadzUpdateFoto,
        data: postData,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data['data']?['foto_url'];
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memperbarui foto.');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memperbarui foto: ${e.message}');
    }
  }

  Future<String> updateTandaTangan({
    String? filePath,
    String? base64Image,
  }) async {
    try {
      dynamic postData;
      if (filePath != null) {
        postData = FormData.fromMap({
          'tanda_tangan': await MultipartFile.fromFile(filePath),
        });
      } else if (base64Image != null) {
        postData = {'tanda_tangan_base64': base64Image};
      } else {
        throw Exception('Data tanda tangan tidak ditemukan.');
      }

      final response = await _client.dio.post(
        ApiConstants.ustadzUpdateTandaTangan,
        data: postData,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data['data']['tanda_tangan_url'] ?? '';
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal menyimpan tanda tangan.',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal menyimpan tanda tangan: ${e.message}');
    }
  }

  Future<void> logout() async {
    try {
      await _client.dio.post(ApiConstants.logout);
    } catch (_) {}
    await StorageService.clearAuth();
  }

  Future<UserModel?> getCachedUser() async {
    final userMap = await StorageService.getUser();
    if (userMap != null) {
      return UserModel.fromJson(userMap);
    }
    return null;
  }
}
