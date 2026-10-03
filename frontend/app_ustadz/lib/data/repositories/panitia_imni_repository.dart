import 'package:dio/dio.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../models/panitia_imni_model.dart';

class PanitiaImniRepository {
  final ApiClient _client = ApiClient();

  // =========================================================================
  // 1. PEMBAYARAN IMNI
  // =========================================================================

  Future<PembayaranImniRingkasanModel> getPembayaranRingkasan({
    int? tahunId,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;

      final response = await _client.dio.get(
        ApiConstants.panitiaImniPembayaranRingkasan,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return PembayaranImniRingkasanModel.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat ringkasan pembayaran',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat ringkasan pembayaran: ${e.message}');
    }
  }

  Future<List<PesertaImniItem>> getPembayaranPesertaList({
    int? tahunId,
    int? tingkatId,
    int? ruanganId,
    String? statusPembayaran,
    String? search,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;
      if (tingkatId != null) queryParams['tingkat_id'] = tingkatId;
      if (ruanganId != null) queryParams['ruangan_id'] = ruanganId;
      if (statusPembayaran != null && statusPembayaran.isNotEmpty) {
        queryParams['status_pembayaran'] = statusPembayaran;
      }
      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
      }

      final response = await _client.dio.get(
        ApiConstants.panitiaImniPembayaranPesertaList,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        final list = response.data['data'] as List? ?? [];
        return list.map((e) => PesertaImniItem.fromJson(e)).toList();
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat daftar peserta',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat daftar peserta: ${e.message}');
    }
  }

  Future<Map<String, dynamic>> simpanPembayaran({
    required int pesertaId,
    required double nominalBayar,
    String? tanggalBayar,
    String? namaPenyetor,
    String? metodePembayaran,
    String? keterangan,
  }) async {
    try {
      final payload = <String, dynamic>{
        'peserta_id': pesertaId,
        'nominal_bayar': nominalBayar,
      };
      if (tanggalBayar != null) payload['tanggal_bayar'] = tanggalBayar;
      if (namaPenyetor != null) payload['nama_penyetor'] = namaPenyetor;
      if (metodePembayaran != null) {
        payload['metode_pembayaran'] = metodePembayaran;
      }
      if (keterangan != null) payload['keterangan'] = keterangan;

      final response = await _client.dio.post(
        ApiConstants.panitiaImniPembayaranSimpanBayar,
        data: payload,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return {
          'message': response.data['message'] ?? 'Pembayaran berhasil disimpan',
          'data': response.data['data'],
        };
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memproses pembayaran',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memproses pembayaran: ${e.message}');
    }
  }

  Future<bool> batalPembayaran(int pembayaranId) async {
    try {
      final response = await _client.dio.post(
        '${ApiConstants.panitiaImniPembayaranBatalBayar}/$pembayaranId',
      );

      return response.statusCode == 200 && response.data['success'] == true;
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal membatalkan pembayaran: ${e.message}');
    }
  }

  // =========================================================================
  // 2. PENGELUARAN IMNI
  // =========================================================================

  Future<PengeluaranImniRingkasanModel> getPengeluaranRingkasan({
    int? tahunId,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;

      final response = await _client.dio.get(
        ApiConstants.panitiaImniPengeluaranRingkasan,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return PengeluaranImniRingkasanModel.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat ringkasan pengeluaran',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat ringkasan pengeluaran: ${e.message}');
    }
  }

  Future<List<PengeluaranImniItem>> getPengeluaranList({
    int? tahunId,
    String? kategori,
    String? search,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;
      if (kategori != null && kategori.isNotEmpty) {
        queryParams['kategori'] = kategori;
      }
      if (search != null && search.isNotEmpty) queryParams['search'] = search;

      final response = await _client.dio.get(
        ApiConstants.panitiaImniPengeluaranList,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        final list = response.data['data'] as List? ?? [];
        return list.map((e) => PengeluaranImniItem.fromJson(e)).toList();
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat daftar pengeluaran',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat daftar pengeluaran: ${e.message}');
    }
  }

  Future<PengeluaranImniItem> simpanPengeluaran({
    required String kategori,
    required String judulPengeluaran,
    required double nominal,
    required String tanggalPengeluaran,
    String? penerimaDana,
    String? metodePembayaran,
    String? keterangan,
    String? buktiNotaPath,
  }) async {
    try {
      final mapData = <String, dynamic>{
        'kategori': kategori,
        'judul_pengeluaran': judulPengeluaran,
        'nominal': nominal,
        'tanggal_pengeluaran': tanggalPengeluaran,
        'metode_pembayaran': metodePembayaran ?? 'Tunai',
      };
      if (penerimaDana != null) mapData['penerima_dana'] = penerimaDana;
      if (keterangan != null) mapData['keterangan'] = keterangan;

      dynamic body;
      if (buktiNotaPath != null && buktiNotaPath.isNotEmpty) {
        mapData['bukti_nota'] = await MultipartFile.fromFile(
          buktiNotaPath,
          filename: buktiNotaPath.split('/').last.split('\\').last,
        );
        body = FormData.fromMap(mapData);
      } else {
        body = mapData;
      }

      final response = await _client.dio.post(
        ApiConstants.panitiaImniPengeluaranSimpan,
        data: body,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return PengeluaranImniItem.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal menyimpan pengeluaran',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal menyimpan pengeluaran: ${e.message}');
    }
  }

  Future<PengeluaranImniItem> updatePengeluaran({
    required int id,
    required String kategori,
    required String judulPengeluaran,
    required double nominal,
    required String tanggalPengeluaran,
    String? penerimaDana,
    String? metodePembayaran,
    String? keterangan,
    String? buktiNotaPath,
  }) async {
    try {
      final mapData = <String, dynamic>{
        'kategori': kategori,
        'judul_pengeluaran': judulPengeluaran,
        'nominal': nominal,
        'tanggal_pengeluaran': tanggalPengeluaran,
        'metode_pembayaran': metodePembayaran ?? 'Tunai',
      };
      if (penerimaDana != null) mapData['penerima_dana'] = penerimaDana;
      if (keterangan != null) mapData['keterangan'] = keterangan;

      dynamic body;
      if (buktiNotaPath != null && buktiNotaPath.isNotEmpty) {
        mapData['bukti_nota'] = await MultipartFile.fromFile(
          buktiNotaPath,
          filename: buktiNotaPath.split('/').last.split('\\').last,
        );
        body = FormData.fromMap(mapData);
      } else {
        body = mapData;
      }

      final response = await _client.dio.post(
        '${ApiConstants.panitiaImniPengeluaranUpdate}/$id',
        data: body,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return PengeluaranImniItem.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memperbarui pengeluaran',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memperbarui pengeluaran: ${e.message}');
    }
  }

  Future<bool> hapusPengeluaran(int id) async {
    try {
      final response = await _client.dio.delete(
        '${ApiConstants.panitiaImniPengeluaranHapus}/$id',
      );

      return response.statusCode == 200 && response.data['success'] == true;
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal menghapus pengeluaran: ${e.message}');
    }
  }

  // =========================================================================
  // 3. PRESENSI UJIAN IMNI
  // =========================================================================

  Future<PresensiImniDataResponse> getPresensiData({
    String kategori = 'imni',
    int? tahunId,
    int? ruanganId,
    int? jadwalUjianId,
    String? tanggalUjian,
    int? ruanganImniId,
  }) async {
    try {
      final queryParams = <String, dynamic>{'kategori': kategori};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;
      if (ruanganId != null) queryParams['ruangan_id'] = ruanganId;
      if (jadwalUjianId != null) queryParams['jadwal_ujian_id'] = jadwalUjianId;
      if (tanggalUjian != null) queryParams['tanggal_ujian'] = tanggalUjian;
      if (ruanganImniId != null) queryParams['ruangan_imni_id'] = ruanganImniId;

      final response = await _client.dio.get(
        ApiConstants.panitiaImniPresensiData,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return PresensiImniDataResponse.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat presensi ujian IMNI',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat presensi ujian IMNI: ${e.message}');
    }
  }

  Future<bool> simpanPresensi({
    String kategori = 'imni',
    int? ujianId,
    int? ruanganId,
    int? jadwalUjianId,
    int? ruanganImniId,
    String? tanggalUjian,
    required Map<String, dynamic> presensi,
    Map<String, dynamic>? pengawas,
  }) async {
    try {
      final payload = <String, dynamic>{
        'kategori': kategori,
        'presensi': presensi,
      };
      if (ujianId != null) payload['ujian_id'] = ujianId;
      if (ruanganId != null) payload['ruangan_id'] = ruanganId;
      if (jadwalUjianId != null) payload['jadwal_ujian_id'] = jadwalUjianId;
      if (ruanganImniId != null) payload['ruangan_imni_id'] = ruanganImniId;
      if (tanggalUjian != null) payload['tanggal_ujian'] = tanggalUjian;
      if (pengawas != null) payload['pengawas'] = pengawas;

      final response = await _client.dio.post(
        ApiConstants.panitiaImniPresensiSimpan,
        data: payload,
      );

      return response.statusCode == 200 && response.data['success'] == true;
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal menyimpan presensi ujian IMNI: ${e.message}');
    }
  }

  // =========================================================================
  // 4. NILAI & LEGER IMNI
  // =========================================================================

  Future<NilaiImniDataResponse> getNilaiData({
    int? tahunId,
    int? ruanganId,
    int? jadwalUjianId,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;
      if (ruanganId != null) queryParams['ruangan_id'] = ruanganId;
      if (jadwalUjianId != null) queryParams['jadwal_ujian_id'] = jadwalUjianId;

      final response = await _client.dio.get(
        ApiConstants.panitiaImniNilaiData,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return NilaiImniDataResponse.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat data nilai IMNI',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat data nilai IMNI: ${e.message}');
    }
  }

  Future<String> simpanNilai({
    required int ujianId,
    required int ruanganId,
    required int jadwalUjianId,
    required String action, // 'draft' or 'publish'
    required Map<String, dynamic> nilai,
  }) async {
    try {
      final payload = <String, dynamic>{
        'ujian_id': ujianId,
        'ruangan_id': ruanganId,
        'jadwal_ujian_id': jadwalUjianId,
        'action': action,
        'nilai': nilai,
      };

      final response = await _client.dio.post(
        ApiConstants.panitiaImniNilaiSimpan,
        data: payload,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data['message'] ?? 'Nilai berhasil disimpan';
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal menyimpan nilai IMNI',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal menyimpan nilai IMNI: ${e.message}');
    }
  }

  Future<LegerImniDataResponse> getLegerNilai({
    int? tahunId,
    int? ruanganId,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;
      if (ruanganId != null) queryParams['ruangan_id'] = ruanganId;

      final response = await _client.dio.get(
        ApiConstants.panitiaImniNilaiLeger,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return LegerImniDataResponse.fromJson(response.data['data']);
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat leger nilai IMNI',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat leger nilai IMNI: ${e.message}');
    }
  }
}
