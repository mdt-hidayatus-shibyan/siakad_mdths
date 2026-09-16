import 'package:dio/dio.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../models/laporan_model.dart';

class LaporanRepository {
  final ApiClient _client = ApiClient();

  /// Mengambil data laporan presensi murid (khusus kelas binaan wali)
  Future<LaporanPresensiMuridData> getLaporanPresensiMurid({
    int? ruanganId,
    int? semesterId,
    int? bulanHijriyahId,
    String? semester,
    String? startDate,
    String? endDate,
    int? jadwalId,
  }) async {
    try {
      final Map<String, dynamic> params = {};
      if (ruanganId != null) params['ruangan_id'] = ruanganId;
      if (semesterId != null) params['semester_id'] = semesterId;
      if (bulanHijriyahId != null) {
        params['bulan_hijriyah_id'] = bulanHijriyahId;
      }
      if (semester != null && semester.isNotEmpty && semester != 'Semua') {
        params['semester'] = semester;
      }
      if (startDate != null && startDate.isNotEmpty) {
        params['start_date'] = startDate;
      }
      if (endDate != null && endDate.isNotEmpty) {
        params['end_date'] = endDate;
      }
      if (jadwalId != null) params['jadwal_pelajaran_id'] = jadwalId;

      final response = await _client.dio.get(
        ApiConstants.laporanPresensiMurid,
        queryParameters: params,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return LaporanPresensiMuridData.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat laporan presensi murid',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat laporan presensi murid: ${e.message}');
    }
  }

  /// Mengambil data laporan buku kasus & pelanggaran murid (khusus kelas binaan wali)
  Future<LaporanPelanggaranData> getLaporanPelanggaranMurid({
    int? ruanganId,
    int? bulanHijriyahId,
    String? startDate,
    String? endDate,
    String? kategori,
  }) async {
    try {
      final Map<String, dynamic> params = {};
      if (ruanganId != null) params['ruangan_id'] = ruanganId;
      if (bulanHijriyahId != null) {
        params['bulan_hijriyah_id'] = bulanHijriyahId;
      }
      if (startDate != null && startDate.isNotEmpty) {
        params['start_date'] = startDate;
      }
      if (endDate != null && endDate.isNotEmpty) {
        params['end_date'] = endDate;
      }
      if (kategori != null && kategori.isNotEmpty && kategori != 'Semua') {
        params['kategori'] = kategori;
      }

      final response = await _client.dio.get(
        ApiConstants.laporanPelanggaranMurid,
        queryParameters: params,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return LaporanPelanggaranData.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat laporan pelanggaran murid',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat laporan pelanggaran murid: ${e.message}');
    }
  }

  /// Mengambil data laporan presensi ustadz (khusus kelas binaan wali)
  Future<LaporanPresensiUstadzData> getLaporanPresensiUstadz({
    int? ruanganId,
    int? ustadzId,
    int? semesterId,
    int? bulanHijriyahId,
    String? status,
    String? startDate,
    String? endDate,
    bool? isPribadi,
  }) async {
    try {
      final Map<String, dynamic> params = {};
      if (ruanganId != null) params['ruangan_id'] = ruanganId;
      if (ustadzId != null) params['ustadz_id'] = ustadzId;
      if (semesterId != null) params['semester_id'] = semesterId;
      if (bulanHijriyahId != null) {
        params['bulan_hijriyah_id'] = bulanHijriyahId;
      }
      if (status != null && status.isNotEmpty && status != 'Semua') {
        params['status'] = status;
      }
      if (startDate != null && startDate.isNotEmpty) {
        params['start_date'] = startDate;
      }
      if (endDate != null && endDate.isNotEmpty) {
        params['end_date'] = endDate;
      }
      if (isPribadi != null) {
        params['is_pribadi'] = isPribadi ? 1 : 0;
      }

      final response = await _client.dio.get(
        ApiConstants.laporanPresensiUstadz,
        queryParameters: params,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return LaporanPresensiUstadzData.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat laporan presensi ustadz',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat laporan presensi ustadz: ${e.message}');
    }
  }
}
