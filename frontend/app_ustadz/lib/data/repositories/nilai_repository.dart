import 'package:dio/dio.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../models/nilai_ujian_model.dart';

class NilaiRepository {
  final ApiClient _client = ApiClient();

  Future<MapelJadwalNilaiResponse> getMapelJadwal({
    int? ruanganId,
    int? ujianId,
  }) async {
    try {
      final query = <String, dynamic>{};
      if (ruanganId != null) query['ruangan_id'] = ruanganId;
      if (ujianId != null) query['ujian_id'] = ujianId;

      final response = await _client.dio.get(
        ApiConstants.ujianMapelJadwal,
        queryParameters: query,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return MapelJadwalNilaiResponse.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat jadwal mapel ujian',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat jadwal mapel ujian: ${e.message}');
    }
  }

  Future<List<UjianItem>> getUjianList({int? ruanganId}) async {
    try {
      final query = <String, dynamic>{};
      if (ruanganId != null) query['ruangan_id'] = ruanganId;
      final response = await _client.dio.get(
        ApiConstants.ujianList,
        queryParameters: query,
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        final list = response.data['data'] as List;
        return list.map((e) => UjianItem.fromJson(e)).toList();
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat daftar ujian',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat daftar ujian: ${e.message}');
    }
  }

  Future<List<MuridNilaiItem>> getInputData({
    required int ujianId,
    required int ruanganId,
    int? jadwalUjianId,
  }) async {
    try {
      final query = <String, dynamic>{
        'ujian_id': ujianId,
        'ruangan_id': ruanganId,
      };
      if (jadwalUjianId != null) {
        query['jadwal_ujian_id'] = jadwalUjianId;
      }

      final response = await _client.dio.get(
        ApiConstants.ujianInputData,
        queryParameters: query,
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        final rawList = response.data['data']['murids'] as List;
        return rawList.map((e) => MuridNilaiItem.fromJson(e)).toList();
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat data input nilai',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat data nilai santri: ${e.message}');
    }
  }

  Future<bool> simpanNilai({
    required int ujianId,
    required int ruanganId,
    int? jadwalUjianId,
    required String action, // draft / publish
    required Map<String, double> nilaiMap,
  }) async {
    try {
      final data = <String, dynamic>{
        'ujian_id': ujianId,
        'ruangan_id': ruanganId,
        'action': action,
        'nilai': nilaiMap,
      };
      if (jadwalUjianId != null) {
        data['jadwal_ujian_id'] = jadwalUjianId;
      }

      final response = await _client.dio.post(
        ApiConstants.ujianSimpanNilai,
        data: data,
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal menyimpan nilai santri',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal menyimpan nilai santri: ${e.message}');
    }
  }

  Future<LegerResponse> getLeger({
    required int ujianId,
    required int ruanganId,
  }) async {
    try {
      final response = await _client.dio.get(
        ApiConstants.ujianLeger,
        queryParameters: {'ujian_id': ujianId, 'ruangan_id': ruanganId},
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return LegerResponse.fromJson(response.data['data']);
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat leger nilai');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat leger nilai: ${e.message}');
    }
  }

  Future<bool> beriDispensasi({
    required int ujianId,
    required int muridId,
    String? alasanIzin,
  }) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.ujianBeriDispensasi,
        data: {
          'ujian_id': ujianId,
          'murid_id': muridId,
          'alasan_izin': alasanIzin,
        },
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memberikan dispensasi ujian',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memberikan dispensasi ujian: ${e.message}');
    }
  }

  Future<bool> batalkanDispensasi({
    required int ujianId,
    required int muridId,
  }) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.ujianBatalDispensasi,
        data: {'ujian_id': ujianId, 'murid_id': muridId},
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal membatalkan dispensasi ujian',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal membatalkan dispensasi ujian: ${e.message}');
    }
  }
}
