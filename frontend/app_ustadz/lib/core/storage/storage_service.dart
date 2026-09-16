import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'auth_user';
  static const String _keyTheme = 'app_theme_mode';
  static const String _keyColorPreset = 'app_color_preset';
  static const String _keyBaseUrl = 'custom_base_url';

  static SharedPreferences? _prefs;
  static String? _cachedToken;
  static Map<String, dynamic>? _cachedUser;
  static String? _cachedTheme;
  static String? _cachedColorPreset;
  static String? _cachedBaseUrl;

  /// Inisialisasi awal SharedPreferences & populate in-memory cache saat app start
  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
    _cachedToken = _prefs?.getString(_keyToken);
    _cachedTheme = _prefs?.getString(_keyTheme);
    _cachedColorPreset = _prefs?.getString(_keyColorPreset);
    _cachedBaseUrl = _prefs?.getString(_keyBaseUrl);

    final userStr = _prefs?.getString(_keyUser);
    if (userStr != null) {
      try {
        _cachedUser = jsonDecode(userStr) as Map<String, dynamic>;
      } catch (_) {
        _cachedUser = null;
      }
    }
  }

  static Future<SharedPreferences> _getPrefs() async {
    return _prefs ??= await SharedPreferences.getInstance();
  }

  // --- TOKEN ---
  static Future<void> saveToken(String token) async {
    _cachedToken = token;
    final prefs = await _getPrefs();
    await prefs.setString(_keyToken, token);
  }

  static Future<String?> getToken() async {
    if (_cachedToken != null) return _cachedToken;
    final prefs = await _getPrefs();
    _cachedToken = prefs.getString(_keyToken);
    return _cachedToken;
  }

  static String? getCachedToken() => _cachedToken;

  // --- USER DATA ---
  static Future<void> saveUser(Map<String, dynamic> userMap) async {
    _cachedUser = userMap;
    final prefs = await _getPrefs();
    await prefs.setString(_keyUser, jsonEncode(userMap));
  }

  static Future<Map<String, dynamic>?> getUser() async {
    if (_cachedUser != null) return _cachedUser;
    final prefs = await _getPrefs();
    final userStr = prefs.getString(_keyUser);
    if (userStr == null) return null;
    try {
      _cachedUser = jsonDecode(userStr) as Map<String, dynamic>;
      return _cachedUser;
    } catch (_) {
      return null;
    }
  }

  static Map<String, dynamic>? getCachedUser() => _cachedUser;

  static Future<void> clearAuth() async {
    _cachedToken = null;
    _cachedUser = null;
    final prefs = await _getPrefs();
    await prefs.remove(_keyToken);
    await prefs.remove(_keyUser);
  }

  // --- THEME ---
  static Future<void> saveThemeMode(String mode) async {
    _cachedTheme = mode;
    final prefs = await _getPrefs();
    await prefs.setString(_keyTheme, mode);
  }

  static Future<String?> getThemeMode() async {
    if (_cachedTheme != null) return _cachedTheme;
    final prefs = await _getPrefs();
    _cachedTheme = prefs.getString(_keyTheme);
    return _cachedTheme;
  }

  // --- COLOR PRESET ---
  static Future<void> saveColorPreset(String presetKey) async {
    _cachedColorPreset = presetKey;
    final prefs = await _getPrefs();
    await prefs.setString(_keyColorPreset, presetKey);
  }

  static Future<String?> getColorPreset() async {
    if (_cachedColorPreset != null) return _cachedColorPreset;
    final prefs = await _getPrefs();
    _cachedColorPreset = prefs.getString(_keyColorPreset);
    return _cachedColorPreset;
  }

  static String? getCachedColorPreset() => _cachedColorPreset;

  // --- BASE URL ---
  static Future<void> saveBaseUrl(String url) async {
    _cachedBaseUrl = url;
    final prefs = await _getPrefs();
    await prefs.setString(_keyBaseUrl, url);
  }

  static Future<String?> getBaseUrl() async {
    if (_cachedBaseUrl != null) return _cachedBaseUrl;
    final prefs = await _getPrefs();
    _cachedBaseUrl = prefs.getString(_keyBaseUrl);
    return _cachedBaseUrl;
  }

  static String? getCachedBaseUrl() => _cachedBaseUrl;
}
