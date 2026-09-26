import 'package:dio/dio.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/network/api_client.dart';
import '../../core/storage/storage_service.dart';
import '../models/wali_model.dart';

class AuthRepository {
  final Dio _dio = ApiClient.instance.dio;

  /// Step 1: Validasi keberadaan No. Registrasi / NISM Anak
  Future<Map<String, dynamic>> checkIdentifier(String identifier) async {
    try {
      final response = await _dio.post(
        ApiEndpoints.loginWali,
        data: {'identifier': identifier, 'check_only': true},
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return {
          'success': true,
          'wali_name': response.data['wali_name']?.toString() ?? 'Wali Murid',
          'no_reg': response.data['no_reg']?.toString() ?? '-',
          'total_anak': response.data['total_anak'] ?? 1,
          'kampung': response.data['kampung']?.toString() ?? '-',
        };
      }

      return {
        'success': false,
        'message': response.data['message'] ?? 'Data tidak ditemukan.',
      };
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Koneksi ke server gagal.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': 'Terjadi kesalahan: $e'};
    }
  }

  /// Step 2: Verifikasi PIN & Login
  Future<Map<String, dynamic>> loginWali(String identifier, String pin) async {
    try {
      final response = await _dio.post(
        ApiEndpoints.loginWali,
        data: {'identifier': identifier, 'pin': pin},
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        final token = response.data['token'] as String;
        final waliJson = response.data['wali'] as Map<String, dynamic>;

        await StorageService.setToken(token);
        await StorageService.setWaliData(waliJson);

        return {
          'success': true,
          'token': token,
          'wali': WaliModel.fromJson(waliJson),
        };
      }

      return {
        'success': false,
        'message': response.data['message'] ?? 'Login gagal.',
      };
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Koneksi ke server gagal.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': 'Terjadi kesalahan: $e'};
    }
  }

  /// Login via QR Code (Kartu Santri / Akses Wali Murid)
  Future<Map<String, dynamic>> loginQr(String qrData) async {
    try {
      final response = await _dio.post(
        ApiEndpoints.loginQrWali,
        data: {'qr_data': qrData},
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        final token = response.data['token'] as String;
        final waliJson = response.data['wali'] as Map<String, dynamic>;

        await StorageService.setToken(token);
        await StorageService.setWaliData(waliJson);

        return {
          'success': true,
          'token': token,
          'wali': WaliModel.fromJson(waliJson),
        };
      }

      return {
        'success': false,
        'message': response.data['message'] ?? 'Login via QR Code gagal.',
      };
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Koneksi ke server gagal.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': 'Terjadi kesalahan: $e'};
    }
  }

  /// Update PIN Keamanan
  Future<Map<String, dynamic>> updatePin(String pinLama, String pinBaru) async {
    try {
      final response = await _dio.post(
        '/wali/update-pin',
        data: {'pin_lama': pinLama, 'pin_baru': pinBaru},
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return {
          'success': true,
          'message': response.data['message'] ?? 'PIN berhasil diperbarui.',
        };
      }

      return {
        'success': false,
        'message': response.data['message'] ?? 'Gagal mengubah PIN.',
      };
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Gagal mengubah PIN.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': 'Terjadi kesalahan: $e'};
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post(ApiEndpoints.logout);
    } catch (_) {}
    await StorageService.clearAll();
  }
}
