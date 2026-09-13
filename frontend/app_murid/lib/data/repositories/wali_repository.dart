import 'package:dio/dio.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/network/api_client.dart';
import '../models/anak_model.dart';
import '../models/dashboard_model.dart';
import '../models/dokumen_model.dart';
import '../models/jadwal_model.dart';
import '../models/nilai_model.dart';
import '../models/pelanggaran_model.dart';
import '../models/tagihan_model.dart';
import '../models/presensi_model.dart';
import '../models/tabungan_model.dart';
import '../models/koperasi_model.dart';
import '../models/kenaikan_model.dart';
import '../models/kas_ruangan_model.dart';

class WaliRepository {
  final Dio _dio = ApiClient.instance.dio;

  Future<DashboardDataModel?> getDashboard() async {
    try {
      final res = await _dio.get(ApiEndpoints.dashboard);
      if (res.statusCode == 200 && res.data['success'] == true) {
        return DashboardDataModel.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<AnakModel?> getDetailAnak(int anakId) async {
    try {
      final res = await _dio.get('${ApiEndpoints.detailAnak}/$anakId');
      if (res.statusCode == 200 && res.data['success'] == true) {
        return AnakModel.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<RekapTagihanAnakModel?> getTagihanAnak(int anakId) async {
    try {
      final res = await _dio.get('${ApiEndpoints.tagihanAnak}/$anakId');
      if (res.statusCode == 200 && res.data['success'] == true) {
        return RekapTagihanAnakModel.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<RekapTagihanWaliModel?> getTagihanWali() async {
    try {
      final res = await _dio.get(ApiEndpoints.tagihanWali);
      if (res.statusCode == 200 && res.data['success'] == true) {
        return RekapTagihanWaliModel.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<RekapPresensiAnakModel?> getPresensiAnak(
    int anakId, {
    String? tanggal,
  }) async {
    try {
      final res = await _dio.get(
        '${ApiEndpoints.presensiAnak}/$anakId',
        queryParameters: tanggal != null ? {'tanggal': tanggal} : null,
      );
      if (res.statusCode == 200 && res.data['success'] == true) {
        return RekapPresensiAnakModel.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<RekapPelanggaranAnakModel?> getPelanggaranAnak(int anakId) async {
    try {
      final res = await _dio.get('${ApiEndpoints.pelanggaranAnak}/$anakId');
      if (res.statusCode == 200 && res.data['success'] == true) {
        return RekapPelanggaranAnakModel.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<RekapNilaiAnakModel?> getNilaiAnak(int anakId) async {
    try {
      final res = await _dio.get('${ApiEndpoints.nilaiAnak}/$anakId');
      if (res.statusCode == 200 && res.data['success'] == true) {
        return RekapNilaiAnakModel.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<JadwalDetailAnakModel?> getJadwalAnak(int anakId) async {
    try {
      final res = await _dio.get('${ApiEndpoints.jadwalAnak}/$anakId');
      if (res.statusCode == 200 &&
          res.data['success'] == true &&
          res.data['data'] != null) {
        return JadwalDetailAnakModel.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<KenaikanAnakModel?> getKenaikanAnak(int anakId) async {
    try {
      final res = await _dio.get('${ApiEndpoints.kenaikanAnak}/$anakId');
      if (res.statusCode == 200 &&
          res.data['success'] == true &&
          res.data['data'] != null) {
        return KenaikanAnakModel.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<DokumenGroupModel?> getDokumenAnak(int anakId) async {
    try {
      final res = await _dio.get('${ApiEndpoints.dokumenAnak}/$anakId');
      if (res.statusCode == 200 && res.data['success'] == true) {
        return DokumenGroupModel.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<TabunganAnakData?> getTabunganAnak(
    int anakId, {
    String? bulan,
    int? tabunganId,
  }) async {
    try {
      final res = await _dio.get(
        '${ApiEndpoints.tabunganAnak}/$anakId',
        queryParameters: {
          if (bulan != null) 'bulan': bulan,
          if (tabunganId != null) 'tabungan_id': tabunganId,
        },
      );
      if (res.statusCode == 200 && res.data['success'] == true) {
        return TabunganAnakData.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<KoperasiAnakData?> getKoperasiAnak(int anakId) async {
    try {
      final res = await _dio.get('${ApiEndpoints.koperasiAnak}/$anakId');
      if (res.statusCode == 200 && res.data['success'] == true) {
        return KoperasiAnakData.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<KasRuanganAnakData?> getKasRuanganAnak(int anakId) async {
    try {
      final res = await _dio.get('${ApiEndpoints.kasRuanganAnak}/$anakId');
      if (res.statusCode == 200 &&
          res.data['success'] == true &&
          res.data['data'] != null) {
        return KasRuanganAnakData.fromJson(res.data['data']);
      }
    } catch (_) {}
    return null;
  }

  Future<Map<String, dynamic>> ajukanKomplainTabungan({
    required int transaksiId,
    required int nominalKlaim,
    required String alasan,
    String? fotoBukti,
  }) async {
    try {
      final res = await _dio.post(
        ApiEndpoints.tabunganKomplain,
        data: {
          'transaksi_id': transaksiId,
          'nominal_klaim': nominalKlaim,
          'alasan': alasan,
          if (fotoBukti != null) 'foto_bukti': fotoBukti,
        },
      );
      if (res.statusCode == 200 && res.data['success'] == true) {
        return {
          'success': true,
          'message': res.data['message'] ?? 'Komplain berhasil diajukan.',
          'data': res.data['data'],
        };
      }
      return {
        'success': false,
        'message': res.data['message'] ?? 'Gagal mengajukan komplain.',
      };
    } on DioException catch (e) {
      final msg =
          e.response?.data?['message'] ??
          e.message ??
          'Terjadi kesalahan jaringan.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {
        'success': false,
        'message': e.toString().replaceAll('Exception: ', ''),
      };
    }
  }

  Future<List<TabunganKomplainModel>> getRiwayatKomplainTabungan(
    int anakId,
  ) async {
    try {
      final res = await _dio.get('${ApiEndpoints.tabunganKomplain}/$anakId');
      if (res.statusCode == 200 && res.data['success'] == true) {
        final list = res.data['data']?['komplains'] as List<dynamic>? ?? [];
        return list.map((e) => TabunganKomplainModel.fromJson(e)).toList();
      }
    } catch (_) {}
    return [];
  }

  Future<Map<String, dynamic>> batalkanKomplainTabungan(int komplainId) async {
    try {
      final res = await _dio.post(
        '${ApiEndpoints.tabunganKomplain}/$komplainId/batal',
      );
      if (res.statusCode == 200 && res.data['success'] == true) {
        return {
          'success': true,
          'message': res.data['message'] ?? 'Komplain berhasil dibatalkan.',
        };
      }
      return {
        'success': false,
        'message': res.data['message'] ?? 'Gagal membatalkan komplain.',
      };
    } on DioException catch (e) {
      final msg =
          e.response?.data?['message'] ??
          e.message ??
          'Terjadi kesalahan jaringan.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {
        'success': false,
        'message': e.toString().replaceAll('Exception: ', ''),
      };
    }
  }

  Future<Map<String, dynamic>?> getBantuanKontak() async {
    try {
      final res = await _dio.get(ApiEndpoints.bantuanKontak);
      if (res.statusCode == 200 && res.data['success'] == true) {
        return res.data['data'] as Map<String, dynamic>;
      }
    } catch (_) {}
    return null;
  }

  Future<List<Map<String, dynamic>>> getPengumuman() async {
    try {
      final res = await _dio.get(ApiEndpoints.pengumuman);
      if (res.statusCode == 200 && res.data['success'] == true) {
        final list = res.data['data'] as List<dynamic>? ?? [];
        return list.map((e) => e as Map<String, dynamic>).toList();
      }
    } catch (_) {}
    return [];
  }
}
