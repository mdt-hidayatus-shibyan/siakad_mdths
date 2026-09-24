import 'package:dio/dio.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../models/presensi_model.dart';

class PresensiRepository {
  final ApiClient _client = ApiClient();

  Future<SesiPresensiResponse> getSesiHarian(String tanggal) async {
    try {
      final response = await _client.dio.get(
        ApiConstants.presensiSesi,
        queryParameters: {'tanggal': tanggal},
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return SesiPresensiResponse.fromJson(response.data);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat sesi presensi',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat sesi presensi: ${e.message}');
    }
  }

  Future<List<MuridPresensiItem>> getMuridPerJadwal(
    int jadwalId,
    String tanggal,
  ) async {
    try {
      final response = await _client.dio.get(
        ApiConstants.presensiMurid,
        queryParameters: {'jadwal_id': jadwalId, 'tanggal': tanggal},
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        final list = response.data['data'] as List;
        return list.map((e) => MuridPresensiItem.fromJson(e)).toList();
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat data santri');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat data santri: ${e.message}');
    }
  }

  Future<bool> simpanPresensiMassal(
    int jadwalId,
    String tanggal,
    List<MuridPresensiItem> items,
  ) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.presensiSimpan,
        data: {
          'jadwal_id': jadwalId,
          'tanggal': tanggal,
          'presensi': items.map((e) => e.toJson()).toList(),
        },
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(response.data['message'] ?? 'Gagal menyimpan presensi');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal menyimpan presensi: ${e.message}');
    }
  }

  Future<SesiPresensiUstadzResponse> getSesiUstadzHarian(String tanggal) async {
    try {
      final response = await _client.dio.get(
        ApiConstants.presensiUstadzSesi,
        queryParameters: {'tanggal': tanggal},
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return SesiPresensiUstadzResponse.fromJson(response.data);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat jadwal mengajar ustadz',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat jadwal mengajar ustadz: ${e.message}');
    }
  }

  Future<bool> checkinUstadz({
    required int jadwalId,
    required String tanggal,
    required String status,
    int? ustadzId,
    int? ustadzPenggantiId,
    String? keterangan,
  }) async {
    try {
      final response = await _client.dio.post(
        ApiConstants.presensiUstadzCheckin,
        data: {
          'jadwal_id': jadwalId,
          'tanggal': tanggal,
          'status': status,
          if (ustadzId != null) 'ustadz_id': ustadzId,
          if (ustadzPenggantiId != null)
            'ustadz_pengganti_id': ustadzPenggantiId,
          if (keterangan != null && keterangan.isNotEmpty)
            'keterangan': keterangan,
        },
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(response.data['message'] ?? 'Gagal melakukan check-in');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal melakukan check-in: ${e.message}');
    }
  }

  Future<List<UstadzBadalItem>> getDaftarUstadzBadal() async {
    try {
      final response = await _client.dio.get(ApiConstants.presensiUstadzDaftar);
      if (response.statusCode == 200 && response.data['success'] == true) {
        final rawList = response.data['data'] as List? ?? [];
        return rawList.map((e) => UstadzBadalItem.fromJson(e)).toList();
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat daftar ustadz',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat daftar ustadz: ${e.message}');
    }
  }

  Future<RiwayatPresensiUstadz> getRiwayatUstadz() async {
    try {
      final response = await _client.dio.get(
        ApiConstants.presensiUstadzRiwayat,
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return RiwayatPresensiUstadz.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat riwayat presensi',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat riwayat presensi ustadz: ${e.message}');
    }
  }
}
