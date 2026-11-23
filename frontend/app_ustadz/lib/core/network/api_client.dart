import 'package:dio/dio.dart';
import '../constants/api_constants.dart';
import '../storage/storage_service.dart';
import '../utils/session_helper.dart';

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
        onError: (DioException e, handler) async {
          if (e.response?.statusCode == 401) {
            final requestPath = e.requestOptions.path;
            final isPublicOrAuthEndpoint =
                requestPath.contains(ApiConstants.login) ||
                requestPath.contains(ApiConstants.logout) ||
                requestPath.contains(ApiConstants.forgotPassword) ||
                requestPath.contains(ApiConstants.verifyOtp) ||
                requestPath.contains(ApiConstants.resetPassword) ||
                requestPath.contains('/ping') ||
                requestPath.contains('/app-version');

            final token =
                StorageService.getCachedToken() ??
                await StorageService.getToken();
            final hasToken = token != null && token.isNotEmpty;

            if (!isPublicOrAuthEndpoint && hasToken) {
              String? serverMessage;
              if (e.response?.data is Map) {
                final rawMsg = e.response?.data['message']?.toString();
                if (rawMsg != null &&
                    rawMsg.isNotEmpty &&
                    rawMsg.toLowerCase() != 'unauthenticated.') {
                  serverMessage = rawMsg;
                }
              }

              SessionHelper.handleSessionExpired(
                message: serverMessage != null
                    ? 'Sesi Anda telah berakhir: $serverMessage. Silakan masuk kembali.'
                    : 'Masa berlaku sesi login Anda telah berakhir demi keamanan. Silakan masuk kembali ke akun Ustadz Anda.',
              );
            }
          }
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
