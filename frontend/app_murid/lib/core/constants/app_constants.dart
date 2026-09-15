class AppConstants {
  AppConstants._();

  static const String appName = 'Wali Murid - MDTHS';
  static const String appVersion = '1.0.0';
  static const String appTagline = 'Portal Monitoring Wali Murid';

  // Storage Keys
  static const String keyToken = 'auth_token';
  static const String keyWaliData = 'wali_data';
  static const String keySelectedAnakId = 'selected_anak_id';
  static const String keyCustomBaseUrl = 'custom_base_url';
  static const String keyThemeMode = 'theme_mode';

  // Default Backend URL
  static const String defaultBaseUrl = 'http://10.0.2.2:8000/api';
  static const String productionBaseUrl =
      'https://mdthidayatusshibyan.sch.id/api';
}
