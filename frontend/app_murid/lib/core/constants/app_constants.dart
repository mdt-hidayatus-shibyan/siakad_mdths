class AppConstants {
  AppConstants._();

  static const String appName = 'Wali Murid - MDTHS';
  static const String appVersion = '1.0.0';
  static const String appTagline = 'Portal Monitoring Wali Murid';

  // Storage Keys
  static const String keyToken = 'auth_token';
  static const String keyWaliData = 'wali_data';
  static const String keySelectedAnakId = 'selected_anak_id';
  static const String keyThemeMode = 'app_theme_mode';
  static const String keyColorPreset = 'app_color_preset';

  // Backend API Base URL (Edit alamat server Anda di sini)
  // static const String baseUrl = 'http://127.0.0.1:8000/api';
  static const String baseUrl = 'https://mdt-hidayatus-shibyan.sch.id/api';
  static const String defaultBaseUrl = baseUrl;
}
