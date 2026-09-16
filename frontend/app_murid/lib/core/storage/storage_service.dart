import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_constants.dart';

class StorageService {
  static SharedPreferences? _prefs;
  static String? _cachedToken;
  static Map<String, dynamic>? _cachedWaliData;
  static int? _cachedSelectedAnakId;
  static String? _cachedThemeMode;
  static String? _cachedColorPreset;

  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
    _cachedToken = _prefs?.getString(AppConstants.keyToken);
    _cachedThemeMode = _prefs?.getString(AppConstants.keyThemeMode);
    _cachedColorPreset = _prefs?.getString(AppConstants.keyColorPreset);
    _cachedSelectedAnakId = _prefs?.getInt(AppConstants.keySelectedAnakId);

    final str = _prefs?.getString(AppConstants.keyWaliData);
    if (str != null) {
      try {
        _cachedWaliData = jsonDecode(str) as Map<String, dynamic>;
      } catch (_) {
        _cachedWaliData = null;
      }
    }
  }

  static Future<SharedPreferences> _getPrefs() async {
    return _prefs ??= await SharedPreferences.getInstance();
  }

  // Token
  static Future<void> setToken(String token) async {
    _cachedToken = token;
    final prefs = await _getPrefs();
    await prefs.setString(AppConstants.keyToken, token);
  }

  static String? getToken() {
    return _cachedToken ?? _prefs?.getString(AppConstants.keyToken);
  }

  static Future<void> removeToken() async {
    _cachedToken = null;
    final prefs = await _getPrefs();
    await prefs.remove(AppConstants.keyToken);
  }

  // Wali User Data
  static Future<void> setWaliData(Map<String, dynamic> data) async {
    _cachedWaliData = data;
    final prefs = await _getPrefs();
    await prefs.setString(AppConstants.keyWaliData, jsonEncode(data));
  }

  static Map<String, dynamic>? getWaliData() {
    if (_cachedWaliData != null) return _cachedWaliData;
    final str = _prefs?.getString(AppConstants.keyWaliData);
    if (str == null) return null;
    try {
      _cachedWaliData = jsonDecode(str) as Map<String, dynamic>;
      return _cachedWaliData;
    } catch (_) {
      return null;
    }
  }

  // Active Child ID
  static Future<void> setSelectedAnakId(int id) async {
    _cachedSelectedAnakId = id;
    final prefs = await _getPrefs();
    await prefs.setInt(AppConstants.keySelectedAnakId, id);
  }

  static int? getSelectedAnakId() {
    return _cachedSelectedAnakId ??
        _prefs?.getInt(AppConstants.keySelectedAnakId);
  }

  // Base URL (Defined directly in AppConstants)
  static String getBaseUrl() {
    return AppConstants.baseUrl;
  }

  // Theme Mode
  static Future<void> setThemeMode(String mode) async {
    _cachedThemeMode = mode;
    final prefs = await _getPrefs();
    await prefs.setString(AppConstants.keyThemeMode, mode);
  }

  static String? getThemeMode() {
    return _cachedThemeMode ?? _prefs?.getString(AppConstants.keyThemeMode);
  }

  // Color Preset
  static Future<void> setColorPreset(String presetKey) async {
    _cachedColorPreset = presetKey;
    final prefs = await _getPrefs();
    await prefs.setString(AppConstants.keyColorPreset, presetKey);
  }

  static String? getColorPreset() {
    return _cachedColorPreset ?? _prefs?.getString(AppConstants.keyColorPreset);
  }

  static String? getCachedColorPreset() => _cachedColorPreset;

  // Clear All on Logout
  static Future<void> clearAll() async {
    _cachedToken = null;
    _cachedWaliData = null;
    _cachedSelectedAnakId = null;
    final prefs = await _getPrefs();
    await prefs.remove(AppConstants.keyToken);
    await prefs.remove(AppConstants.keyWaliData);
    await prefs.remove(AppConstants.keySelectedAnakId);
  }
}
