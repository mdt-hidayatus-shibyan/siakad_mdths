import 'package:dio/dio.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../models/syarat_ujian_model.dart';

class SyaratUjianRepository {
  final ApiClient _apiClient = ApiClient();

  Future<SyaratUjianDataResponse> getSyaratUjianData({
    int? ruanganId,
    int? ujianId,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (ruanganId != null) queryParams['ruangan_id'] = ruanganId;
      if (ujianId != null) queryParams['ujian_id'] = ujianId;

      final response = await _apiClient.dio.get(
        ApiConstants.ujianSyaratUjian,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return SyaratUjianDataResponse.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat syarat ujian.',
        );
      }
    } on DioException catch (e) {
      throw Exception(
        e.response?.data?['message'] ??
            'Terjadi kesalahan jaringan saat memuat syarat ujian.',
      );
    } catch (e) {
      throw Exception('Gagal memuat syarat ujian: $e');
    }
  }

  Future<bool> beriDispensasi({
    required int ujianId,
    required int muridId,
    required int ruanganId,
    String? alasanIzin,
  }) async {
    try {
      final response = await _apiClient.dio.post(
        ApiConstants.ujianBeriDispensasi,
        data: {
          'ujian_id': ujianId,
          'murid_id': muridId,
          'ruangan_id': ruanganId,
          'alasan_izin': alasanIzin,
        },
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memberikan dispensasi.',
        );
      }
    } on DioException catch (e) {
      throw Exception(
        e.response?.data?['message'] ??
            'Gagal memberikan dispensasi: terjadi kesalahan.',
      );
    } catch (e) {
      throw Exception('Gagal memberikan dispensasi: $e');
    }
  }

  Future<bool> batalkanDispensasi({
    required int ujianId,
    required int muridId,
    required int ruanganId,
  }) async {
    try {
      final response = await _apiClient.dio.post(
        ApiConstants.ujianBatalDispensasi,
        data: {
          'ujian_id': ujianId,
          'murid_id': muridId,
          'ruangan_id': ruanganId,
        },
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal membatalkan dispensasi.',
        );
      }
    } on DioException catch (e) {
      throw Exception(
        e.response?.data?['message'] ??
            'Gagal membatalkan dispensasi: terjadi kesalahan.',
      );
    } catch (e) {
      throw Exception('Gagal membatalkan dispensasi: $e');
    }
  }
}
