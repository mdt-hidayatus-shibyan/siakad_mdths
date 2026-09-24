import 'package:dio/dio.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../models/akademik_model.dart';

class AkademikRepository {
  final ApiClient _client = ApiClient();

  /// Ambil Kalender Pendidikan, Hijriyah & Events
  Future<KalendarPendidikanResponse> getKalendar({int? tahunId}) async {
    try {
      final response = await _client.dio.get(
        ApiConstants.kalender,
        queryParameters: {if (tahunId != null) 'tahun_id': tahunId},
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return KalendarPendidikanResponse.fromJson(response.data['data']);
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat kalender');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat kalender pendidikan: ${e.message}');
    }
  }

  /// Ambil Master Referensi Pelanggaran
  Future<ReferensiPelanggaranResponse> getReferensiPelanggaran({
    String? kategori,
    String? search,
  }) async {
    try {
      final response = await _client.dio.get(
        ApiConstants.referensiPelanggaranMaster,
        queryParameters: {
          if (kategori != null && kategori.isNotEmpty && kategori != 'Semua')
            'kategori': kategori,
          if (search != null && search.isNotEmpty) 'search': search,
        },
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return ReferensiPelanggaranResponse.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat referensi pelanggaran',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat referensi pelanggaran: ${e.message}');
    }
  }

  /// Ambil Master Katalog Mata Pelajaran
  Future<MataPelajaranResponse> getMataPelajaran({
    int? levelId,
    String? search,
  }) async {
    try {
      final response = await _client.dio.get(
        ApiConstants.mataPelajaran,
        queryParameters: {
          if (levelId != null && levelId > 0) 'level_id': levelId,
          if (search != null && search.isNotEmpty) 'search': search,
        },
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return MataPelajaranResponse.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat mata pelajaran',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat mata pelajaran: ${e.message}');
    }
  }

  /// Ambil Jadwal Mengajar Mingguan Ustadz Login
  Future<JadwalPelajaranResponse> getJadwalPelajaran() async {
    try {
      final response = await _client.dio.get(ApiConstants.jadwalPelajaran);

      if (response.statusCode == 200 && response.data['success'] == true) {
        return JadwalPelajaranResponse.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat jadwal pelajaran',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat jadwal pelajaran: ${e.message}');
    }
  }

  /// Ambil Jadwal Ujian Madrasah (Filter Ruangan Kelas & Agenda Ujian)
  Future<JadwalUjianResponse> getJadwalUjian({
    int? tahunId,
    int? ruanganId,
    int? ujianId,
    bool? onlyMe,
  }) async {
    try {
      final response = await _client.dio.get(
        ApiConstants.jadwalUjian,
        queryParameters: {
          if (tahunId != null) 'tahun_id': tahunId,
          if (ruanganId != null) 'ruangan_id': ruanganId,
          if (ujianId != null && ujianId > 0) 'ujian_id': ujianId,
          if (onlyMe == true) 'only_me': 1,
        },
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return JadwalUjianResponse.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat jadwal ujian',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat jadwal ujian: ${e.message}');
    }
  }
}
