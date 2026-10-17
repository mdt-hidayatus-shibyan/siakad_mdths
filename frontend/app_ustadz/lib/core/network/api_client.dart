import 'package:dio/dio.dart';
import '../constants/api_constants.dart';
import '../storage/storage_service.dart';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  late Dio dio;
  String baseUrl = ApiConstants.defaultBaseUrl;

  ApiClient._internal() {
    dio = Dio(
      BaseOptions(
        baseUrl: baseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 15),
        sendTimeout: const Duration(seconds: 15),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    _initBaseUrl();

    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Instant synchronous read from in-memory cache
          final customUrl =
              StorageService.getCachedBaseUrl() ??
              await StorageService.getBaseUrl();
          if (customUrl != null && customUrl.isNotEmpty) {
            options.baseUrl = customUrl;
            baseUrl = customUrl;
          }

          final token =
              StorageService.getCachedToken() ??
              await StorageService.getToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onError: (DioException e, handler) {
          // Normalisasi pesan error agar seragam
          return handler.next(e);
        },
      ),
    );
  }

  Future<void> _initBaseUrl() async {
    final customUrl =
        StorageService.getCachedBaseUrl() ?? await StorageService.getBaseUrl();
    if (customUrl != null && customUrl.isNotEmpty) {
      baseUrl = customUrl;
      dio.options.baseUrl = customUrl;
    }
  }

  Future<void> updateBaseUrl(String newUrl) async {
    baseUrl = newUrl;
    dio.options.baseUrl = newUrl;
    await StorageService.saveBaseUrl(newUrl);
  }

  /// Format URL gambar relatif atau localhost menggunakan base URL yang sedang aktif
  static String? resolveImageUrl(String? rawUrl) {
    return ApiConstants.formatImageUrl(rawUrl, _instance.baseUrl);
  }

  /// Format URL web / dokumen / kwitansi menggunakan base URL yang sedang aktif
  static String? resolveWebUrl(String? rawUrl) {
    return ApiConstants.formatWebUrl(rawUrl, _instance.baseUrl);
  }
}
