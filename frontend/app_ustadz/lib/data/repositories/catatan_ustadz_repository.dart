import 'package:dio/dio.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../models/catatan_ustadz_model.dart';

class CatatanUstadzRepository {
  final ApiClient _client = ApiClient();

  /// Mengambil daftar catatan milik Ustadz
  Future<List<CatatanUstadzItem>> getCatatanList({
    String? kategori,
    String? targetTipe,
    String? search,
    int page = 1,
  }) async {
    try {
      final queryParams = <String, dynamic>{'page': page};
      if (kategori != null && kategori.isNotEmpty && kategori != 'Semua') {
        queryParams['kategori'] = kategori;
      }
      if (targetTipe != null &&
          targetTipe.isNotEmpty &&
          targetTipe != 'Semua') {
        queryParams['target_tipe'] = targetTipe;
      }
      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
      }

      final response = await _client.dio.get(
        ApiConstants.catatanUstadz,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        final list = response.data['data'] as List? ?? [];
        return list
            .map((e) => CatatanUstadzItem.fromJson(e as Map<String, dynamic>))
            .toList();
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat daftar catatan',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat daftar catatan: ${e.message}');
    }
  }

  /// Mengambil detail satu catatan
  Future<CatatanUstadzItem> getDetailCatatan(int id) async {
    try {
      final response = await _client.dio.get(
        '${ApiConstants.catatanUstadz}/$id',
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return CatatanUstadzItem.fromJson(
          response.data['data'] as Map<String, dynamic>,
        );
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memuat detail catatan',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat detail catatan: ${e.message}');
    }
  }

  /// Mengambil opsi referensi (Murid, Ruangan, Kategori, Urgensi)
  Future<CatatanOptionsData> getOptions() async {
    try {
      final response = await _client.dio.get(ApiConstants.catatanUstadzOptions);
      if (response.statusCode == 200 && response.data['success'] == true) {
        return CatatanOptionsData.fromJson(
          response.data['data'] as Map<String, dynamic>,
        );
      } else {
        throw Exception(response.data['message'] ?? 'Gagal memuat opsi form');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memuat opsi form: ${e.message}');
    }
  }

  /// Simpan catatan ustadz baru
  Future<CatatanUstadzItem> simpanCatatan({
    required String judul,
    required String kategori,
    required String targetTipe,
    required String isiCatatan,
    required String tingkatUrgensi,
    int? muridId,
    int? ruanganId,
    String? fotoFilePath,
  }) async {
    try {
      final Map<String, dynamic> dataMap = {
        'judul': judul,
        'kategori': kategori,
        'target_tipe': targetTipe,
        'isi_catatan': isiCatatan,
        'tingkat_urgensi': tingkatUrgensi,
        if (muridId != null) 'murid_id': muridId,
        if (ruanganId != null) 'ruangan_id': ruanganId,
      };

      dynamic payload;
      if (fotoFilePath != null && fotoFilePath.isNotEmpty) {
        final formData = FormData.fromMap(dataMap);
        formData.files.add(
          MapEntry(
            'foto',
            await MultipartFile.fromFile(
              fotoFilePath,
              filename: fotoFilePath.split('/').last.split('\\').last,
            ),
          ),
        );
        payload = formData;
      } else {
        payload = FormData.fromMap(dataMap);
      }

      final response = await _client.dio.post(
        ApiConstants.catatanUstadz,
        data: payload,
      );

      if ((response.statusCode == 200 || response.statusCode == 201) &&
          response.data['success'] == true) {
        return CatatanUstadzItem.fromJson(
          response.data['data'] as Map<String, dynamic>,
        );
      } else {
        throw Exception(response.data['message'] ?? 'Gagal menyimpan catatan');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal menyimpan catatan: ${e.message}');
    }
  }

  /// Update catatan ustadz
  Future<CatatanUstadzItem> updateCatatan(
    int id, {
    required String judul,
    required String kategori,
    required String targetTipe,
    required String isiCatatan,
    required String tingkatUrgensi,
    int? muridId,
    int? ruanganId,
    String? fotoFilePath,
  }) async {
    try {
      final Map<String, dynamic> dataMap = {
        'judul': judul,
        'kategori': kategori,
        'target_tipe': targetTipe,
        'isi_catatan': isiCatatan,
        'tingkat_urgensi': tingkatUrgensi,
        if (muridId != null) 'murid_id': muridId,
        if (ruanganId != null) 'ruangan_id': ruanganId,
      };

      dynamic payload;
      if (fotoFilePath != null && fotoFilePath.isNotEmpty) {
        final formData = FormData.fromMap(dataMap);
        formData.files.add(
          MapEntry(
            'foto',
            await MultipartFile.fromFile(
              fotoFilePath,
              filename: fotoFilePath.split('/').last.split('\\').last,
            ),
          ),
        );
        payload = formData;
      } else {
        payload = FormData.fromMap(dataMap);
      }

      final response = await _client.dio.post(
        '${ApiConstants.catatanUstadz}/$id',
        data: payload,
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        return CatatanUstadzItem.fromJson(
          response.data['data'] as Map<String, dynamic>,
        );
      } else {
        throw Exception(
          response.data['message'] ?? 'Gagal memperbarui catatan',
        );
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal memperbarui catatan: ${e.message}');
    }
  }

  /// Hapus catatan ustadz
  Future<bool> hapusCatatan(int id) async {
    try {
      final response = await _client.dio.delete(
        '${ApiConstants.catatanUstadz}/$id',
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return true;
      } else {
        throw Exception(response.data['message'] ?? 'Gagal menghapus catatan');
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Gagal menghapus catatan: ${e.message}');
    }
  }
}
