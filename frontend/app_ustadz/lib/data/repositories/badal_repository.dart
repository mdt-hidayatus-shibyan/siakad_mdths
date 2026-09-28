import 'package:dio/dio.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../models/badal_model.dart';

class BadalRepository {
  final ApiClient _client = ApiClient();

  /// Ambil daftar semua ruangan aktif untuk pilihan Guru Pengganti (Badal)
  Future<List<BadalRuanganItem>> getRuanganList({String? query}) async {
    try {
      final Map<String, dynamic> params = {};
      if (query != null && query.trim().isNotEmpty) {
        params['query'] = query.trim();
      }

      final response = await _client.dio.get(
        ApiConstants.badalRuanganList,
        queryParameters: params,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        final list = response.data['data'] as List? ?? [];
        return list.map((e) => BadalRuanganItem.fromJson(e)).toList();
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat daftar ruangan',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat daftar ruangan: ${e.message}');
    }
  }

  /// Ambil jadwal pelajaran ruangan dan status presensi pada tanggal tertentu
  Future<BadalJadwalResponse> getJadwalRuangan(
    int ruanganId,
    String tanggal,
  ) async {
    try {
      final response = await _client.dio.get(
        ApiConstants.badalJadwalRuangan,
        queryParameters: {'ruangan_id': ruanganId, 'tanggal': tanggal},
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return BadalJadwalResponse.fromJson(response.data);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat jadwal ruangan',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat jadwal ruangan: ${e.message}');
    }
  }
}
