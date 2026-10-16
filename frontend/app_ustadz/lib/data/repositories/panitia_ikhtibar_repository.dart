import 'dart:io';
import 'package:dio/dio.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../models/panitia_ikhtibar_model.dart';

class PanitiaIkhtibarRepository {
  final ApiClient _client = ApiClient();

  // =========================================================================
  // 1. KASIR PEMBAYARAN TAGIHAN KK IKHTIBAR
  // =========================================================================

  Future<KasirIkhtibarRingkasanModel> getKasirRingkasan({int? tahunId}) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;

      final response = await _client.dio.get(
        ApiConstants.panitiaIkhtibarKasirRingkasan,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return KasirIkhtibarRingkasanModel.fromJson(response.data['data']);
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat ringkasan kasir');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat ringkasan kasir: ${e.message}');
    }
  }

  Future<List<TagihanKkIkhtibarItem>> getKasirList({
    int? tahunId,
    int? kampungId,
    String? statusBayar,
    String? search,
    int? limit,
    int? page,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;
      if (kampungId != null) queryParams['kampung_id'] = kampungId;
      if (statusBayar != null && statusBayar.isNotEmpty && statusBayar != 'Semua') {
        queryParams['status_bayar'] = statusBayar;
      }
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      if (limit != null) queryParams['limit'] = limit;
      if (page != null) queryParams['page'] = page;

      final response = await _client.dio.get(
        ApiConstants.panitiaIkhtibarKasirList,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        final list = response.data['data']['items'] as List? ?? [];
        return list.map((e) => TagihanKkIkhtibarItem.fromJson(e)).toList();
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat daftar tagihan');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat daftar tagihan: ${e.message}');
    }
  }

  Future<Map<String, dynamic>> simpanPembayaran({
    required int tagihanId,
    required double nominalBayar,
    required String metodePembayaran,
    String? tanggalBayar,
    String? namaPenyetor,
    String? catatan,
    String? nomorTabungan,
    String? namaNasabah,
    String? ruanganNasabah,
    bool? isVerified,
  }) async {
    try {
      final payload = <String, dynamic>{
        'tagihan_id': tagihanId,
        'nominal_bayar': nominalBayar,
        'metode_pembayaran': metodePembayaran,
      };
      if (tanggalBayar != null) payload['tanggal_bayar'] = tanggalBayar;
      if (namaPenyetor != null) payload['nama_penyetor'] = namaPenyetor;
      if (catatan != null) payload['catatan'] = catatan;
      if (nomorTabungan != null) payload['nomor_tabungan'] = nomorTabungan;
      if (namaNasabah != null) payload['nama_nasabah'] = namaNasabah;
      if (ruanganNasabah != null) payload['ruangan_nasabah'] = ruanganNasabah;
      if (isVerified != null) payload['is_verified'] = isVerified;

      final response = await _client.dio.post(
        ApiConstants.panitiaIkhtibarKasirBayar,
        data: payload,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data;
      } else {
        throw Exception(response.data['message'] ?? 'Gagal menyimpan pembayaran');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memproses pembayaran: ${e.message}');
    }
  }

  Future<Map<String, dynamic>> toggleVerifikasiTabungan(int pembayaranId) async {
    try {
      final response = await _client.dio.post(
        '${ApiConstants.panitiaIkhtibarKasirVerifikasiTabungan}/$pembayaranId',
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data;
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memverifikasi potongan tabungan');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memverifikasi potongan tabungan: ${e.message}');
    }
  }

  // =========================================================================
  // 2. LAPORAN PEMBAYARAN TAGIHAN IKHTIBAR
  // =========================================================================

  Future<LaporanIkhtibarRingkasanModel> getLaporanRingkasan({int? tahunId}) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;

      final response = await _client.dio.get(
        ApiConstants.panitiaIkhtibarLaporanRingkasan,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return LaporanIkhtibarRingkasanModel.fromJson(response.data['data']);
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat ringkasan laporan');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat ringkasan laporan: ${e.message}');
    }
  }

  Future<List<RiwayatPembayaranIkhtibarItem>> getLaporanRiwayat({
    int? tahunId,
    String? metodePembayaran,
    String? tanggal,
    int? kampungId,
    String? search,
    int? limit,
    int? page,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;
      if (metodePembayaran != null && metodePembayaran.isNotEmpty && metodePembayaran != 'Semua') {
        queryParams['metode_pembayaran'] = metodePembayaran;
      }
      if (tanggal != null && tanggal.isNotEmpty) queryParams['tanggal'] = tanggal;
      if (kampungId != null) queryParams['kampung_id'] = kampungId;
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      if (limit != null) queryParams['limit'] = limit;
      if (page != null) queryParams['page'] = page;

      final response = await _client.dio.get(
        ApiConstants.panitiaIkhtibarLaporanRiwayat,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        final list = response.data['data']['items'] as List? ?? [];
        return list.map((e) => RiwayatPembayaranIkhtibarItem.fromJson(e)).toList();
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat riwayat pembayaran');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat riwayat pembayaran: ${e.message}');
    }
  }

  // =========================================================================
  // 3. PAGU & REALISASI SPJ (BPH & DIVISI)
  // =========================================================================

  Future<PaguSpjRingkasanModel> getPaguSpjRingkasan({int? tahunId}) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;

      final response = await _client.dio.get(
        ApiConstants.panitiaIkhtibarPaguSpjRingkasan,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return PaguSpjRingkasanModel.fromJson(response.data['data']);
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat ringkasan pagu & SPJ');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat ringkasan pagu & SPJ: ${e.message}');
    }
  }

  Future<List<SpjIkhtibarItem>> getSpjList({
    int? tahunId,
    int? divisiId,
    String? statusVerifikasi,
    String? search,
    int? limit,
    int? page,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (tahunId != null) queryParams['tahun_id'] = tahunId;
      if (divisiId != null) queryParams['divisi_id'] = divisiId;
      if (statusVerifikasi != null && statusVerifikasi.isNotEmpty && statusVerifikasi != 'Semua') {
        queryParams['status_verifikasi'] = statusVerifikasi;
      }
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      if (limit != null) queryParams['limit'] = limit;
      if (page != null) queryParams['page'] = page;

      final response = await _client.dio.get(
        ApiConstants.panitiaIkhtibarSpjList,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        final list = response.data['data']['items'] as List? ?? [];
        return list.map((e) => SpjIkhtibarItem.fromJson(e)).toList();
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat daftar SPJ');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat daftar SPJ: ${e.message}');
    }
  }

  Future<Map<String, dynamic>> simpanSpj({
    int? divisiId,
    required String judulPengeluaran,
    required double nominal,
    required String tanggalNota,
    String? namaTokoPenerima,
    String metodePembayaran = 'Tunai',
    String? rincianItem,
    File? buktiNotaFile,
  }) async {
    try {
      final formDataMap = <String, dynamic>{
        'judul_pengeluaran': judulPengeluaran,
        'nominal': nominal,
        'tanggal_nota': tanggalNota,
        'metode_pembayaran': metodePembayaran,
      };

      if (divisiId != null) formDataMap['divisi_id'] = divisiId;
      if (namaTokoPenerima != null) formDataMap['nama_toko_penerima'] = namaTokoPenerima;
      if (rincianItem != null) formDataMap['rincian_item'] = rincianItem;

      if (buktiNotaFile != null) {
        formDataMap['bukti_nota'] = await MultipartFile.fromFile(
          buktiNotaFile.path,
          filename: buktiNotaFile.path.split('/').last.split('\\').last,
        );
      }

      final formData = FormData.fromMap(formDataMap);

      final response = await _client.dio.post(
        ApiConstants.panitiaIkhtibarSpjSimpan,
        data: formData,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data;
      } else {
        throw Exception(response.data['message'] ?? 'Gagal menyimpan SPJ');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal menyimpan SPJ: ${e.message}');
    }
  }
}

